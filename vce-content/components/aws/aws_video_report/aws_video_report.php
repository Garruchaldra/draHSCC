<?php

/**
 * Amazon aws video report
 *
 * @category   Media
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';

/**
 * Amazon aws video report.
 */
class AWSVideoReport extends Component
{

	use AWSSupport;

	/**
	 * basic info about the component
	 */
	public function component_info()
	{
		return array(
			//basic component properties are required
			'name' => 'AWSVideoReport',
			'description' => 'Amazon aws video report',
			'category' => 'media',
			'recipe_fields' => array('auto_create', 'title', 'url')
		);
	}

	/**
	 * Edit corp
	 */
	public function search($input)
	{
		global $vce;

		$vce->site->add_attributes('assignment', $input['assignment']);

		echo json_encode(array('response' => 'success', 'action' => 'reload', 'delay' => '0', 'message' => ''));
		return;
	}

	/**
	 * output to page
	 */
	public function as_content($each_component, $vce)
	{
		$query_string = isset($vce->query_string) ? json_decode($vce->query_string, true) : null;
		$assignment = isset($vce->assignment) ? $vce->assignment : null;
		$csv_download = !empty($query_string['csvdownload']) ? $query_string['csvdownload'] : null;
		$xsl_download = !empty($query_string['xsldownload']) ? $query_string['xsldownload'] : null;

		$form = $vce->content->create_text_input('assignment', $assignment, 'Assignment Id', true, 'Enter an Assignment Id');
		$form = $vce->content->create_form($form, null, array(
			'type' => 'AWSVideoReport',
			'procedure' => 'search'
		), 'search', null);
		$form .= '</br></br>';

		$vce->content->add('main', $form);

		if (!isset($assignment)) {
			return;
		}

		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui');
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

		$sql = "
			select distinct
			user_meta.meta_value as user_id, 
			user.vector as vector,
			user_first.meta_value as first_name, 
			user_last.meta_value as last_name, 
			assignment.meta_value as assignment_title, 
			assignment.component_id as assignment_id, 
			media_path.meta_value as path 
			from {$vce->db->prefix}components_meta submission_meta 
			join {$vce->db->prefix}components submission on submission.component_id = submission_meta.component_id
			join {$vce->db->prefix}components media on media.parent_id = submission.component_id
			join {$vce->db->prefix}components_meta media_path on media.component_id = media_path.component_id
			join {$vce->db->prefix}components_meta media_type on media.component_id = media_type.component_id    
			join {$vce->db->prefix}components_meta assignment on submission.parent_id = assignment.component_id
			join {$vce->db->prefix}components_meta user_meta on submission.component_id = user_meta.component_id    
			join {$vce->db->prefix}users_meta user_first on user_meta.meta_value = user_first.user_id    
			join {$vce->db->prefix}users_meta user_last on user_meta.meta_value = user_last.user_id    
			join {$vce->db->prefix}users user on user_meta.meta_value = user.user_id    
			where submission_meta.meta_value = 'StudentSubmissions' 
			and media_path.meta_key = 'path' 
			and media_type.meta_value = 'AWSVideo'
			and user_meta.meta_key = 'created_by'
			and user_first.meta_key = 'first_name'
			and user_last.meta_key = 'last_name'
			and assignment.meta_key = 'title'
			and assignment.component_id = $assignment			
			order by assignment.component_id desc
		";

		$data = $vce->db->get_data_object($sql);

		if ($data && sizeof($data) > 0) {
			$title = $data[0]->assignment_title;
			$data = $this->convert_data($data, $vce);
			if ($csv_download) {
				$this->download_csv($title, $data, $vce);
			} elseif ($xsl_download) {
				$this->download_xsl($title, $data, $vce);
			} else {
				$this->show_data($title, $assignment, $data, $vce);
			}
		} else {
			$vce->content->add('main', 'no data');
		}

		return;
	}

	private function convert_data($data, $vce)
	{
		$config = AWSVideoReport::get_config($vce);
		$sdk = AWSVideoReport::get_sdk($config);
		$key_file = AWSVideoReport::create_key_file($config);
		$cf = $sdk->createCloudFront();
		$cf_domain = AWSVideoReport::get_distribution_domain($sdk, $config);
		$key_pair_id = $config['cloud_front']['cf_key_id'];
		$expires = time() + 604800000; // 7 days from now

		foreach ($data as $row) {
			$row->first_name = User::decryption($row->first_name, $row->vector);
			$row->last_name = User::decryption($row->last_name, $row->vector);
			$resource_key = 'https://' . $cf_domain . '/' . $config['prefix'] . '/' . $row->path;
			$link = AWSSupport::do_create_signed_url($cf, $resource_key, $key_file, $key_pair_id, $expires);
			$row->path = "<a href='$link' target='_blank'>$row->path</a>";
			unset($row->vector);
			unset($row->assignment_id);
			unset($row->assignment_title);
		}
		unlink($key_file);

		return $data;
	}

	private function show_data($title, $assignment, $data, $vce)
	{
		$content = '';

		$link = $vce->site->site_url . '/' . $this->url . '?xsldownload=true&assignment=' . $assignment;
		$content .= "<a class='link-button' href='$link'>MS Excel Download</a>";
		$link = $vce->site->site_url . '/' . $this->url . '?csvdownload=true&assignment=' . $assignment;
		$content .= "<a class='link-button' href='$link'>CSV Download</a>";

		if (count($data) == 0) {
			$vce->content->add('main', 'no data');
			return;
		}

		$fields = array_keys((array) $data[0]);
		$accordion = "<table id='report' class='tablesorter'><thead><tr>";
		foreach ($fields as $field) {
			$accordion .= "<th>$field</th>";
		}
		$accordion .= "</tr></thead>";

		foreach ($data as $row) {

			$accordion .= "<tr>";
			foreach ($fields as $field) {
				$accordion .= "<td class='align-left'>{$row->{$field}}</td>";
			}
			$accordion .= "</tr>";
		}

		$accordion .= "</table>";
		$content .= $vce->content->accordion($title, $accordion, true);

		$vce->content->add('main', $content);
	}

	private function download_xsl($title, $data, $vce)
	{

		$fields = array_keys((array) $data[0]);

		$output = "<table border=1><thead><tr>";
		foreach ($fields as $field) {
			$output .= "<th>$field</th>";
		}
		$output .= "</tr></thead><tbody>";
		foreach ($data as $row) {
			$output .= "<tr>";
			foreach ($fields as $field) {
				$value = $row->{$field};
				$output .= "<td class='align-left'>$value</td>";
			}
			$output .= "</tr>";
		}
		$output .= "</tbody></table>";

		// convert to csv and output
		$name = $title;
		$now = date("Y-m-d_h_i_sa");
		$filename = $name . '_' . $now . '-' . '.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		header('Content-Transfer-Encoding: binary');
		$fp = fopen('php://output', 'w');
		fwrite($fp, $output);
		fclose($fp);
		exit;
	}

	private function download_csv($title, $data, $vce)
	{

		$fields = array_keys((array) $data[0]);
		$field_count = sizeof($fields);

		// header
		$output = implode(",", $fields) . "\n";

		foreach ($data as $row) {
			$i = 0;
			foreach ($fields as $field) {
				$value = $row->{$field};
				$output .= '"' . $value . '"';
				if (++$i === $field_count) {
					$output .= "\n";
				} else {
					$output .= ",";
				}
			}
		}

		// convert to csv and output
		$name = $title;
		$now = date("Y-m-d_h_i_sa");
		$filename = $name . '_' . $now . '-' . '.csv';
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		header('Content-Transfer-Encoding: binary');
		$fp = fopen('php://output', 'w');
		fwrite($fp, $output);
		fclose($fp);
		exit;
	}
}
