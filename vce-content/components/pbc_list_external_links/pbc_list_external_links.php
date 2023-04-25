<?php

class Pbc_list_external_links extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc List External Links',
			'description' => 'list all of the external links in the site',
			'category' => 'pbc',
			'recipe_fields' => array('auto_create','title','url')
		);
	}

	
	/**
	 * adding assignment specific meta to page object
	 */
	public function check_access($each_component, $vce) {

		return true;
	}




	/**
	 *
	 */
	public function as_content($each_component, $vce) {


		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		

		if (isset($vce->query_string)) {
			$query_string = json_decode($vce->query_string, true);
		}

		$content = NULL;

		$content .= <<<EOF
		Query String possibilities: list_external_links, test_external_links list_uploaded_media, show_user_data (start_id=x, end_id=x).
		<br>
EOF;

		if (isset($query_string['list_external_links']) && $query_string['list_external_links'] == TRUE) {
			$content .= $this->list_external_links($vce);
		}

		if (isset($query_string['list_uploaded_media']) && $query_string['list_uploaded_media'] == TRUE) {
			$upload_type = (isset($query_string['upload_type']))?$query_string['upload_type'] : 'PDF';
			$content .= $this->list_uploaded_media($vce, $upload_type);
		}
		

		if (isset($query_string['show_user_data']) && $query_string['show_user_data'] == TRUE) {
			$content .= $this->show_user_data($vce, $query_string['start_id'], $query_string['end_id']);
		}

		$vce->content->add('main',$content);


	}

	public function show_user_data($vce, $start_id = 0, $end_id = 300) {

		$users_info = array();
		for ($i = $start_id; $i < $end_id; $i++) {
			$users_info['user_ids'][] = $i;
		}
		$users_info['user_ids'] = implode('|', $users_info['user_ids']);
		$all_users = $vce->user->find_users($users_info);
		foreach ($all_users as $this_user) {
			$vce->dump($this_user);
		}

	}


	public function list_uploaded_media($vce, $upload_type = 'PDF') {

		$content = '<div>Here is a list of all the Uploaded Media on this site.</div><br>';
		$content .= <<<EOF
		<table> 
		<tr>
			<th>Does it exist?</th>
			<th>Title</th>
			<th>Path</th>
			<th>Status</th>
			<th>Created At</th>
		</tr>
EOF;




$query = "SELECT a.component_id as component_id, c.meta_value as title, b.meta_value as path, d.meta_value AS user_id, e.meta_value AS created_at FROM vce_components_meta AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id JOIN vce_components_meta AS c ON a.component_id = c.component_id JOIN vce_components_meta AS d ON a.component_id = d.component_id JOIN vce_components_meta AS e ON a.component_id = e.component_id WHERE a.meta_key = 'media_type' AND a.meta_value = '$upload_type' AND b.meta_key = 'path' AND c.meta_key = 'title' AND d.meta_key = 'created_by' AND e.meta_key = 'created_at'";

		// $vce->dump($query);
		// exit;
		
		$data = $vce->db->get_data_object($query);

		$i = 0;
        foreach($data as $this_data) {
			// if($i > 5) {
			// 	break;
			// }
			// $i++;
			$path = $this_data->path;
			$component_id = $this_data->component_id;
			$title = $this_data->title;
			$user_id = $this_data->user_id;
			$created_at = date(DATE_RFC2822, $this_data->created_at);
			$relative_path = __DIR__.'/../../uploads/'.$user_id.'/'.$path;
			$file_exists = '--';
			$positive = NULL;
			if (file_exists($relative_path)) {
				$file_exists = 'Exists!';
				$positive = 'Yes';
			}
			$url = $vce->site->site_url.'/vce-content/uploads/'.$user_id.'/'.$path;
			$content .= <<<EOF
			<tr>
EOF;
			$content .= <<<EOF
			<td>$positive</td>
			<td>$title</td>
			<td>$url</td>
			<td>$file_exists</td>
			<td>$created_at</td>
EOF;
$content .= <<<EOF
<tr>
EOF;
		}
		$content .= <<<EOF
		</table>
EOF;

		return  $content;

	}


	public function list_external_links($vce) {

		if (isset($vce->query_string)) {
			$query_string = json_decode($vce->query_string, true);
		}

		$content = '<div>Here is a list of all the External Links on this site.</div><br>';
		$content .= <<<EOF
		<ol type = "1">
EOF;

		$query = "SELECT a.component_id as component_id, c.meta_value as title, b.meta_value as url FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id WHERE a.meta_key = 'media_type' AND a.meta_value = 'ExternalLink' AND b.meta_key = 'link' AND c.meta_key = 'title'";
		// $vce->dump($query);
		// exit;
		
		$data = $vce->db->get_data_object($query);

		$i = 0;
        foreach($data as $this_data) {
			// if($i > 5) {
			// 	break;
			// }
			// $i++;
			$url = $this_data->url;
			$component_id = $this_data->component_id;
			$title = $this_data->title;
			$header_response = NULL;
			if (isset($query_string['test_external_links']) && $query_string['test_external_links'] == TRUE) {
				$header_response = $this->url_exists($url);
			}
			
			$content .= <<<EOF
			<li><a href="$url"  target="_blank">$title</a> $header_response </li>
EOF;
		}
		$content .= <<<EOF
		</ol>
EOF;

		return  $content;

	}

	public function url_exists($url) {
		global $vce;

		$a_url = parse_url($url);
// $vce->dump($a_url);
// $vce->dump(gethostbyname($a_url['host']));
		if (!isset($a_url['port'])) $a_url['port'] = 80;
		$errno = 0;
		$errstr = '';
		$timeout = 300;
		if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host'])){
			$fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
			if (!$fid) return false;
			$page = isset($a_url['path'])  ?$a_url['path']:'';
			$page .= isset($a_url['query'])?'?'.$a_url['query']:'';
			fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
			$head = fread($fid, 4096);
			$head = substr($head,0,strpos($head, 'Connection: close'));
			fclose($fid);
			if (preg_match('#^HTTP/.*\s+[200|302]+\s#i', $head)) {
			 $pos = strpos($head, 'Content-Type');
			 return "200 or 302: exists";
			} elseif (preg_match('#^HTTP/.*\s+[301]+\s#i', $head)) {
				return "301: exists";
			}
		} else {
			return $head." doesn't exist";
		}
	}




}