<?php
/**
 * Nestor Reporting page
 *
 * @category   Admin
 * @package    Nestor_reports
 * @author     Dayton <daytonra@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */


class Nestor_reports extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Reports',
			'description' => 'OLD Development version of a universal Nestor reporting tool',
			'category' => 'nestor_reports'
		);
	}
	

	public function to_do($each_component, $vce) {
		/*

		*/
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {

		$content_hook = array(
			'site_hook_initiation' => 'Nestor_reports::require_once_nestor_reportstype',
			'page_requested_url' => 'Nestor_reports::add_reports_summaries',
		);

		return $content_hook;
	}

		/**
	 * loads the NotificationsType parent class before the children classes are loaded
	 */
	public static function require_once_nestor_reportstype($site) {
	
		// path to mediatype.php
		require_once(dirname(__FILE__) . '/nestor_reportstype/nestor_reportstype.php');

	}

	public static function add_reports_summaries($vce) {

		global $vce;

		$summaries = new stdClass();
		$custom_summaries = json_decode($vce->site->enabled_nestor_reportstype, true);
		if (isset($custom_summaries)) {
			foreach ($custom_summaries as $name=>$path) {
					if (file_exists(BASEPATH . $path)) {
					require_once(BASEPATH . $path);
					$summary_class = $name;
				
					$class_methods = get_class_methods($summary_class);
					$summaries->$name = array('class_name' => $summary_class, 'methods'=>array());
					foreach ($class_methods as $method_name) {
					
						if (substr($method_name, 0, 8) == 'summary_') {
							$display_name = str_replace('summary_', '', $method_name);
							$summaries->$name['methods'][] = array(
								'method_name'=>$method_name,
								'display_name'=>$display_name
							);
						}
					}
				}
			}
		}
		// $vce->site->summaries = $summaries;
		$vce->site->summaries = json_decode('{"nestor_report_summaries_hscc":{"class_name":"nestor_report_summaries_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);


	}



	/**
	 * All presets for the reports are stored in datalists which belong to each individual user.
	 * There is a parent datalist whos children are the individual users' datalists
	 * This function finds all the user datalists which are in any user's jurisdictions and returns the presets
	 */

	public function get_report_presets_datalists ($component_id, $user_id, $vce) {

		// This is the overall structure of the Nestor_reports presets
		// 
		$preset_attributes = array (
			'name'=>'nestor_reports_presets',
			'component_id' => $component_id,
			'initialization' => TRUE,
			'personal_reports_presets' => array (
				'name'=>'personal_reports_presets',
				'component_id' => $component_id,
				'user_id'=> $vce->user->user_id,
				'initialization' => TRUE
			),
			'personal_analytics_presets' => array (
				'name'=>'personal_analytics_presets',
				'component_id' => $component_id,
				'user_id'=> $vce->user->user_id,
				'initialization' => TRUE
			),
			'summary_preset' => array (
				'name'=>'summary_preset',
				'component_id' => $component_id,
				'user_id'=> $vce->user->user_id,
				'initialization' => TRUE
			),
			'last_personal_preset' => array (
				'name'=>'last_personal_preset',
				'component_id' => $component_id,
				'user_id'=> $vce->user->user_id,
				'initialization' => TRUE
			),
			'last_analytics_preset' => array (
				'name'=>'last_analytics_preset',
				'component_id' => $component_id,
				'user_id'=> $vce->user->user_id,
				'initialization' => TRUE
			)
		);

		// find out if the presets datalist structure already exists. 
		// return if it does, create whole presets structure if it doesn't
		$vce->set_datapoint($preset_attributes);
		$names_of_core_datalists = array (
			'nestor_reports_presets','personal_reports_presets','personal_analytics_presets','summary_preset','last_personal_preset', 'last_analytics_preset'
		);
		foreach ($names_of_core_datalists as $key => $this_name) { 
			$attr = array (
				'name'=>$this_name,
				'component_id' => $component_id,
			);
			$data = $vce->get_datapoint_datalist($attr);
			// $vce->dump($data);
			if (!empty($data)) {
				// $vce->dump($report_presets_datalist[0]->datalist_id);
				foreach ($data as $k=>$v) {
					if (isset($v['datalist']) && $v['datalist'] == $attr['name']) {
						$variable_name = $v['datalist'].'_id';
						$$variable_name = $k;
					}
				}
			}
		}

		// $vce->dump($nestor_reports_presets_id);
		// $vce->dump($personal_reports_presets_id);
		// $vce->dump($summary_preset_id);
		// $vce->dump($last_personal_preset_id);
		// exit;
// find out if a personal presets datalist already exists
// find out if the datapoints datalist already exist (this datalist is the parent for the datapoints datalists)
// find out if a personal_datapoints_datalist already exists


		return array('personal_report_presets_datalist_id' => $personal_reports_presets_id, 'personal_analytics_presets_datalist_id' => $personal_analytics_presets_id, 'summary_preset_id'=>$summary_preset_id, 'last_personal_preset_id'=>$last_personal_preset_id, 'last_analytics_preset_id'=>$last_analytics_preset_id);

	}




		/**
		 * as_content contains all forms which spawn reports. Since these reports result in downloading a .csv file,
		 * I have used as_content as the method for assembling the data as well. This can be farmed out to individual methods, but must
		 * be called from as_content to create the headers necessary for downloads.
		 */



public function as_content($each_component, $vce) {

	$content = NULL;
	// $vce->dump($vce->user);
		
		//get the id of the datalist where presets are stored
		// $preset_datalist_ids = self::get_report_presets_datalists ($each_component->component_id, $vce->user->user_id, $vce);
		// $vce->dump($preset_datalist_ids);
		// exit;
		// extract($preset_datalist_ids);


		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter jquery-ui');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		// $vce->site->add_style(dirname(__FILE__) . '/css/progress_bar.css');
		$component_id = $each_component->component_id;
		$each_component_url = $each_component->url;
		$each_component_created_at = $each_component->created_at;

		$vce->site->summaries = json_decode('{"nestor_report_summaries_hscc":{"class_name":"nestor_report_summaries_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);


		// load hooks for title bar
		// if (isset($vce->site->hooks['titleBar'])) {
		// 	foreach ($vce->site->hooks['titleBar'] as $hook) {
		// 		$title = call_user_func($hook, 'Reports', 'reports');
		// 	}
		// }

		// this loading gif will appear until the page is done loading, and then be replaced by download submit button <span class="download-button"><input type="submit" value="Download Report: $query_title"></span>
		$download_loading_gif = $vce->site->site_url.'/vce-content/components/nestor_reports/img/loading_38.gif';

		// // output title
		// if (isset($title)){
		// 	$vce->content->add('title', $title);
		// }


		
		// for date picker
		$id_key = uniqid();
			
		// 
		$user_name = $vce->user->first_name . ' ' . $vce->user->last_name;

// 		// This scope of jurisdiction is used for all reports (based on role_id)
// 		switch ($vce->user->role_id) {
// 			case 1:
// 				$report_scope = 'all users on the site';
// 				break;
// 			case 2:
// 				$report_scope = 'all users on the site';
// 				break;
// 			case 5:
// 				$report_scope = 'all users in your organization';
// 				break;
// 			case 6:
// 				$report_scope = 'all users in your group';
// 				break;

// 			default:
// 				$report_scope = 'all users for whom you are the admin';
// 				break;
// 		}

 
// 	$content .= <<<EOF
// 	<p class="reports-message">
// 	By submitting the forms below, reports pertaining to $report_scope will be generated and can be copied to your clipboard.
// EOF;

// 	$content .= <<<EOF
// 	<br><br>
// EOF;


		// div containing select summary points to show
		
		// the instructions to pass through the form
		$summary_preset_id = (isset($summary_preset_id)) ? $summary_preset_id : NULL;
		$dossier = array(
			'type' => 'Nestor_reports',
			'procedure' => 'edit_summary',
			'component_id' => $each_component->component_id,
			'datalist_id' => $summary_preset_id
		);

		// add dossier for report_queries
		$dossier_for_edit_summary = $vce->generate_dossier($dossier);


		//create drop down of available summary datapoints
		$configure_summary_content = <<<EOF
		<form class="asynchronous-form" method="post" action="$vce->input_path">
		<input type="hidden" name="dossier" value="$dossier_for_edit_summary">
		<input type="hidden" schema="json" id="datapoints_list" name="datapoints_list" value="">
		<label>
		<select id="datapoints" name="datapoints"  multiple>
<option id="option-label" value="">--Select which summaries to include.--</option>
EOF;



	// create array of summary fields
	$summary_array = array();

		// add all methods which start with "summary_" to the $summary_array so they can be called

		// routine for getting the summary_methods (nestorreportstype) minions
		// they are converted to an array of class names and method names
		$vce->site->summaries = json_decode('{"nestor_report_summaries_hscc":{"class_name":"nestor_report_summaries_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);

		foreach ($vce->site->summaries as $key=>$value) {
			$summary_class = new $key;
			foreach ($value['methods'] as $k=>$v){
				$summary_array[] = array(
					'name' => $v['method_name'],
					'display_name' => $v['display_name'],
				);
			}
		}

		// get preset datapoints as array
		$logged_in_user_id = $vce->user->user_id;

		// create select menu of available reports
		foreach ($summary_array as $summary_datapoint) {
			$configure_summary_content .= '<option value="' . $summary_datapoint['name'] . '"';
			$configure_summary_content .= '>' . $summary_datapoint['display_name'] . '</option>';
		}

		
		$configure_summary_content .= <<<EOF
		</select>
		</label>
		<!-- <button id="save-summary-config" class="button__primary data-download-button" type="submit">Save Summary Configuration</button> -->
		</form>
EOF;

		// $summary_table_content = $vce->content->accordion('Summary Configuration', $configure_summary_content, $accordion_expanded = false, $accordion_disabled = false, $accordion_class = 'coaching-companion-summary');
		$summary_table_content = null;
		$summary_table_content .= <<<EOF
		<br>
EOF;
$summary_preset_id = (isset($summary_preset_id)) ? $summary_preset_id : NULL;
if ((isset($each_component->show_summary) && $each_component->show_summary == 'on')  || (isset($this->configuration['show_summary']) && $this->configuration['show_summary'] == 'on')) {
	$dossier_for_summary = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => 'create_summary_table',  'user_id' => $vce->user->user_id, 'component_id' => $each_component->component_id, 'datalist' => 'summary_preset', 'datalist_id'=>$summary_preset_id, 'created_at' => $each_component->created_at, 'url' => $each_component->url)),$vce->user->session_vector);


// start date input for summary
$input = array(
	'type' => 'text',
	'name' => 'start_date',
	'required' => 'true',
	'class' => 'datepicker refresh-summary',
	'id' => "start-date-summary",
	'data' => array (
		'autocapitalize' => 'none',
		'tag' => 'required',
	)
);
$form_start_date = $vce->content->create_input($input,'Start Date');
// end date input for summary
$input = array(
'type' => 'text',
'name' => 'end_date',
'required' => 'true',
'class' => 'datepicker refresh-summary',
'id' => "end-date-summary",
'data' => array (
	'autocapitalize' => 'none',
	'tag' => 'required',
)
);

$form_end_date = $vce->content->create_input($input,'End Date');


	$input = array(
		'type' => 'checkbox',
		'name' => 'filter_by_user',
		'selected' => explode('|', '1|2|3|4|5|6|7'),
		'flags' => array(
		'label_tag_wrap' => true
		)
		);

		// add site roles as options
		// only show the roles equal or subordinate to the user's own
		$user_role_hierarchy = $vce->user->role_hierarchy;
		foreach (json_decode($vce->site->site_roles) as $each_role) {
				foreach ($each_role as $key=>$value) {
					if($value->role_hierarchy >= $user_role_hierarchy) {
						$input['options'][] = array(
								'value' => $key,
								'label' => $value->role_name
						);
					}
				}
		}

		// only allow site and global admins to filter out test users
		if($user_role_hierarchy <= 2) {
			$input['options'][] = array(
				'value' => 'test_users',
				'label' => 'Test Users'
			);
		}

		$filter_by_user = $vce->content->create_input($input, 'Filter by user roles (default is all roles except test users).');

		$summary_table_content .= <<<EOF
		<form class="asynchronous-form background-form summary-date-form" method="post" action="$vce->input_path">
		<input type="hidden" name="dossier" value="$dossier_for_summary">
		<div class="summary-datepickers">
		$form_start_date
		$form_end_date
		$filter_by_user

		<div><button id="refresh-summary-date-button" class="button__primary" type="submit">Refresh Date Range</button> </div>
		</div>
		</form>
		<button id="download-summary-button" class="button__primary" type="submit">Copy Summary to Clipboard</button>
		<button target="summary-content" class="print-summary-button button__primary" type="submit">Print Summary as PDF</button><br>




		<div id="summary-content">
EOF;
		$summary_preset_id = (isset($summary_preset_id)) ? $summary_preset_id : NULL;
		$summary_table_content .= self::create_summary_table (array('user_id'=>$vce->user->user_id, 'component_id'=>$each_component->component_id, 'datalist'=>'summary_preset', 'datalist_id'=>$summary_preset_id));

		$summary_table_content .= <<<EOF
		</div>
		

EOF;



				// create summary accordion box
				$content .= $vce->content->accordion('Coaching Companion Summary', $summary_table_content, $accordion_expanded = TRUE, $accordion_disabled = false, $accordion_class = 'coaching-companion-summary');
			}

			$personal_analytics_presets_datalist_id = NULL;
			$personal_report_presets_datalist_id = NULL;

			if ((isset($each_component->show_data_report) && $each_component->show_data_report == 'on') || (isset($this->configuration['show_data_report']) && $this->configuration['show_data_report'] == 'on')) {
				$data_report_content_input = array(
					'component_id'=>$component_id,
					'each_component_created_at'=>$each_component_created_at,
					'each_component_url'=>$each_component_url,
					'personal_report_presets_datalist_id'=>$personal_report_presets_datalist_id
				);
			

			
				// $data_report_content = $this->data_report_content($data_report_content_input, $each_component, $last_personal_preset_id);
				// $content .= $vce->content->accordion('Data Report', $data_report_content);
			}


			
			$last_analytics_preset_id = NULL;

			if ((isset($each_component->show_analytics_report) && $each_component->show_analytics_report == 'on') || (isset($this->configuration['show_analytics_report']) && $this->configuration['show_analytics_report'] == 'on'))  {
				$analytics_report_content_input = array(
					'component_id'=>$component_id,
					'each_component_created_at'=>$each_component_created_at,
					'each_component_url'=>$each_component_url,
					'personal_analytics_presets_datalist_id'=>$personal_analytics_presets_datalist_id
				);


				// $analytics_report_content = $this->analytics_report_content($each_component, $last_analytics_preset_id, $analytics_report_content_input);
				// $content .= $vce->content->accordion('Analytics Report', $analytics_report_content);
			}


				if (isset($this->configuration['show_configuration_button']) ||  $vce->user->role_hierarchy < 3) {

					$advanced_options_content = NULL;

					$show_summary_input = array(
						'type' => 'checkbox',
						'name' => 'show_summary',
						'options' => array(
							'value' => 'on',
							'selected' => ((isset($each_component->show_summary) && $each_component->show_summary == 'on') ? true :  false),
							'label' => 'Show Summary for Just Me'
							)
						);
						$show_data_report_input = array(
							'type' => 'checkbox',
							'name' => 'show_data_report',
							'options' => array(
								'value' => 'on',
								'selected' => ((isset($each_component->show_data_report) && $each_component->show_data_report == 'on') ? true :  false),
								'label' => 'Show Data Report for Just Me'
								)
							);
						$show_analytics_report_input = array(
							'type' => 'checkbox',
							'name' => 'show_analytics_report',
							'options' => array(
								'value' => 'on',
								'selected' => ((isset($each_component->show_analytics_report) && $each_component->show_analytics_report == 'on') ? true :  false),
								'label' => 'Show Analytics Report for Just Me'
								)
							);


						$dossier = array('type' => 'Nestor_reports',
							'procedure' => 'save_advanced_options',
							'user_id' => $vce->user->user_id,
							'component_id' => $each_component->component_id,
							'created_at' => $each_component->created_at,
							'url' => $each_component->url
						);
						$dossier_for_save_advanced_options = $vce->user->encryption(json_encode($dossier),$vce->user->session_vector);
						$advanced_options_content .= <<<EOF
							<form class="asynchronous-form" method="post" action="$vce->input_path">
							<input type="hidden" name="dossier" value="$dossier_for_save_advanced_options">
							These options are seen only by role hierarchies 1 and 2, other settings are available in the Manage Components configuration.
							<br>
							<br>
							<br>
EOF;
						$advanced_options_content .= $vce->content->create_input($show_summary_input,'Show Summary');
						$advanced_options_content .= $vce->content->create_input($show_data_report_input,'Show Data Report');
						$advanced_options_content .= $vce->content->create_input($show_analytics_report_input,'Show Analytics Report');
						$advanced_options_content .= <<<EOF
							<button id="save-advanced-options" class="button__primary data-download-button" type="submit">Save Advanced Options</button>

							</form>
EOF;

				$content .= $vce->content->accordion('Advanced Options', $advanced_options_content);

				}

		$content .= <<<EOF
</p>
EOF;

		$vce->content->add('main', $content);

}

		public function data_report_content($each_component, $last_personal_preset_id, $data_report_content_input = array()) {

			global $vce;
			extract($data_report_content_input);
// $vce->log("DATAREPORTCONTENT");
		// Component Report (main report form)

		// This is the list to be used in a query which shows what data (component meta_keys) to show
		$order_by_list = "('type','action_plan_goal','comments','created_at','created_by','cycle_participants','group','media_type','organization','pbccycle_begins','pbccycle_review','pbccycle_status','recipe_name','start_date')";

		//create form
		$component_data_content = '';



			// save query as preset

			// name of preset
			$input = array(
				'type' => 'text',
				'name' => 'preset_name',
				'required' => 'true',
				'class' => 'save-preset',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			
			$preset_name_input = $vce->content->create_input($input,'Preset Name');

			$dossier = array('type' => 'Nestor_reports',
				'procedure' => 'save_preset',
				'user_id' => $vce->user->user_id,
				'component_id' => $component_id,
				'parent_id' => $personal_report_presets_datalist_id,
				'created_at' => $each_component->created_at,
				'url' => $each_component_url
			);
			$dossier_for_save_preset = $vce->user->encryption(json_encode($dossier),$vce->user->session_vector);
			$component_data_content .= <<<EOF
			<p>
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_save_preset">
			<input type="hidden" class="report-subject-save" name="report_subject" value="">
			<input type="hidden" class="perspective-save" name="perspective" value="">
			<input schema="json" type="hidden" class="columns_to_show_list-save" name="columns_to_show_list" value="">
			<input type="hidden" class="start-date-component-data-save" name="component_start_date" value="">
			<input type="hidden" class="end-date-component-data-save" name="component_end_date" value="">
			


			$preset_name_input
			<button id="save-as-reports-preset" class="button__primary data-download-button" type="submit">Save This Report as a Preset</button>
			</form>
			</p>

EOF;


		
		// choose category to show
		// here is a list of all the meta_keys we are filtering out:
		// ('alias_id','ap_id','ap_step_id','aps_assignee','assignment_category','content_create','content_delete','content_edit','date','description','duration','email','end_date','first_name','fo_id','focus','goal_achievement_evidence','guid','last_name','link','list_order','mediaAmp_id','name','not_saved_directly_aps_assignee','not_saved_directly_cycle_participants','not_saved_directly_observed','not_saved_directly_observers','observed','observers','original_id','original_taxonomy','originator','originator_id','password','path','pbc_cycles_id','pbccycle_name','preparation_notes','progress','published','recipe','redirect_url','review_sibling_id','rf_id','role_access','role_id','step_comments','sub_roles','taxonomy','taxonomy2','template','text','thumbnail_url','timestamp','title','type','updated_at','user_access','user_id','user_ids_aps_assignee','user_ids_cycle_participants','user_ids_observed','user_ids_observers','user_oldids_aps_assignee','user_oldids_cycle_participants','user_oldids_observed','user_oldids_observers')
		//  here is what we are keeping:
		// ('action_plan_goal','comments','created_at','created_by','cycle_participants','group','media_type','organization','pbccycle_begins','pbccycle_review','pbccycle_status','recipe_name','start_date')

		$query = "SELECT DISTINCT meta_key AS data_category FROM " . TABLE_PREFIX . "components_meta WHERE meta_key IN $order_by_list";
		$data = $vce->db->get_data_object($query);
		$query = "SELECT DISTINCT meta_key AS data_category FROM " . TABLE_PREFIX . "components_meta WHERE meta_key NOT IN $order_by_list";
		$extra_data = $vce->db->get_data_object($query);


			$data_categories = array(
				array(
					'name' => 'Select an Option',
					'value' => '',
					// 'selected' => $not_selected,
					'disabled' => true,
					'selected' => true,
				),
				array(
					'name' => 'year created',
					'value' => 'year',
					'selected' => false,
				),
				array(
					'name' => 'month created',
					'value' => 'month',
					'selected' => false,
				),
				array(
					'name' => 'day created',
					'value' => 'day',
					'selected' => false,
				)
			);

			foreach ($data as $each_data) {
				$selected = false;
				if ($each_data->data_category == $vce->data_category) {
					$selected = true;
				}
				$data_categories[] = array('name' => $each_data->data_category, 'value' => $each_data->data_category);
			}
			$extra_data_categories = array();
			foreach ($extra_data as $each_data) {
				$selected = false;
				if ($each_data->data_category == $vce->data_category) {
					$selected = true;
				}
				$extra_data_categories[] = array('name' => $each_data->data_category, 'value' => $each_data->data_category);
			}

			if ($vce->user->role_hierarchy < 3) {
				$show_all_keys_checkbox = '<input type="checkbox" name="show_all_keys" id="show-all-keys-checkbox" class="config-checkbox" autocomplete="off">&nbsp;Show All Keys?';
			}	

			// perspective input
			$input = array(
				'type' => 'select',
				'name' => 'perspective',
				'required' => 'true',
				'class' => 'perspective key-toggle',
				'id' => 'data-category-component-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			$categories_component_data = $vce->content->create_input($input,"Order by: ");

			// Subject input
			// this needs to be updated to reflect other than the meta_keys
			$input = array(
				'type' => 'select',
				'name' => 'report_subject',
				'required' => 'true',
				'class' => 'report-subject key-toggle',
				'id' => 'report-subject-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			$report_subject = $vce->content->create_input($input, "Subject: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$show_all_keys_checkbox");

		

			// fields to view input
			$input = array(
				'type' => 'select',
				'name' => 'columns_to_show',
				'required' => 'true',
				'class' => 'columns-to-show key-toggle',
				'id' => 'columns-to-show-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
					'multiple' => TRUE,
				)
			);
			$columns_to_show = $vce->content->create_input($input, "Columns to show: (select multiple; selected are highlighted)");
			
				// primary, hidden fields to view input
				$primary_columns_to_show = <<<EOF
				<div id="primary-columns-to-show-data">
EOF;
				foreach ($data_categories as $k=>$v){
					$value = $v['value'];
					$primary_columns_to_show .= <<<EOF
<option value="$value">$value</option>
EOF;
				}
				$primary_columns_to_show .= <<<EOF
				</div>
EOF;

				// extra, hidden fields to view input
				$extra_columns_to_show = <<<EOF
				<div id="extra-columns-to-show-data">
EOF;
				foreach ($extra_data_categories as $k=>$v){
					$value = $v['value'];
					$extra_columns_to_show .= <<<EOF
<option value="$value">$value</option>
EOF;
				}
				$extra_columns_to_show .= <<<EOF
				</div>
EOF;

// start date input
			$input = array(
				'type' => 'text',
				'name' => 'component_start_date',
				'required' => 'true',
				'class' => 'datepicker',
				'id' => 'start-date-component-data',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
		
		$start_date_component_data = $vce->content->create_input($input,'Start Date');

		// end date input
		$input = array(
			'type' => 'text',
			'name' => 'component_end_date',
			'required' => 'true',
			'class' => 'datepicker',
			'id' => 'end-date-component-data',
			'data' => array (
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);
				
		$end_date_component_data = $vce->content->create_input($input,'End Date');
			
		


	
		$dossier_for_component_data_report = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => 'component_data_report', 'report'=>'component_data_report', 'user_id' => $vce->user->user_id, 'component_id' => $component_id, 'last_personal_preset_id' => $last_personal_preset_id, 'created_at' => $each_component_created_at, 'url' => $each_component_url, 'order_by_list' => $order_by_list)),$vce->user->session_vector);


		/*
			Main Form
			Entitled "Configuration" at present
			This is the form which defines what to query, and it also is the form which is altered in script.js
			Script.js updates current_page, head_has_been_sent and any other page-dependent inputs.
		*/
			$component_data_content .= <<<EOF
			<form id="data-report-form" class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_component_data_report">
			<input type="hidden" name="current_page" value="0">
			<input type="hidden" name="head_has_been_sent" value="0">
			<input schema="json" id="columns_to_show_list" class="selections" type="hidden" name="columns_to_show_list" value="">
			
			$report_subject
			$categories_component_data
			$columns_to_show
EOF;

			$component_data_content .= <<<EOF
			$primary_columns_to_show
			$extra_columns_to_show
			$start_date_component_data
			$end_date_component_data

EOF;


			
			$component_data_content .= <<<EOF
			<button id="data-download-button" class="button__primary data-download-button" type="submit">View Report</button>
			</form>
		
EOF;


		
		// this is where the settings can be fine tuned for the data section
		$config_section = $vce->content->accordion('Data Report Configuration', $component_data_content);
		$data_report_content = NULL;


		$dossier_for_get_preset = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => 'get_report_preset', 'user_id' => $vce->user->user_id, 'component_id' => $component_id)),$vce->user->session_vector);

		// initialize $preset_content 
		$preset_content = NULL;

		$preset_name = 'last_query';
		$preset_content .= <<<EOF
		<div class="divTable ">
		<div class="divTableBody input-label-style">
		<div class="divTableRow">
			<div class="divTableCell input-padding">
				<form class="asynchronous-form" method="post" action="$vce->input_path">
				<input type="hidden" name="dossier" value="$dossier_for_get_preset">
				<input type="hidden" name="preset_name" value="$preset_name">
					<button  class="button__primary preset-button" id="last-report-button">Load Last Report</button>
			</form>
			</div>
		
EOF;



		// Find All Saved Presets, from the user and everyone in their jurisdiction
		// This involves getting the list of people within the user's jurisdiction, then getting their datalists,
		// then looping through the personal presets datalist to get all the presets and adding them to the complete list of presets
		
		$component_id = $component_id;
		$user_id = $vce->user->user_id;

		// get all users (filtered by jurisdiction)
		if (!isset($user_array)) {
			$user_array = self::assemble_user_info($vce);
		}

		$list_of_user_datalist_ids = $vce->read_datapoint_structure($personal_report_presets_datalist_id);

		// make an array with the name, id and content of each preset
		$personal_presets = array();
		foreach ($list_of_user_datalist_ids[$personal_report_presets_datalist_id] as $key=>$value) {
			// $attributes = array (
			// 	'component_id' => $each_component->component_id,
			// 	'user_id' => $key,
			// 	'datalist_id' => $value
			// );

			// $this_user_personal_presets = $vce->get_datalist_items($attributes);
			// $vce->dump($this_user_personal_presets);
			// foreach ($this_user_personal_presets['items'] as $key2=>$value2) {
			if (is_array($value)) {
				$personal_presets[$key] = array('name'=>$value['datalist'],'datalist_id'=>$key);
			}
			// }
		}

		// $vce->dump($personal_presets);

		// find longest preset name
		// $longest_string = 0;
		// foreach ($personal_presets as $key=>$value) {
		// 	if (iconv_strlen($value['name']) > $longest_string) {
		// 		$longest_string = iconv_strlen($value['name']);
		// 		$longest_name = $value['name'];
		// 	}
		// }

		$i = 0;
		// $longest_flag = NULL;
		foreach ($personal_presets as $key=>$value) {
			// if ($value['name'] == $longest_name) {
			// 	$longest_flag = 'longest-name';
			// }
			$i++;
			if ($i > 6) {
				$preset_content .= <<<EOF
				</div>
				<div class="divTableRow input-label-style">
EOF;
				$i = 0;
			}				
			
			$this_preset_id = $key;
			// $displayed_preset_name = str_pad($value['name'], $longest_string, " ");
			$this_preset_name = $value['name'];
			$displayed_preset_name = $value['name'];
			// $displayed_preset_name = str_replace('*', '&nbsp;', $displayed_preset_name);
			$preset_button_name = $value['name'].'-'.$key.'-button';
			$this_preset_nice_name = ucwords(str_replace('_', ' ', $displayed_preset_name));
			// the instructions to pass through the delete form
			$dossier = array(
				'type' => 'Nestor_reports',
				'procedure' => 'delete_preset',
				'preset_id' => $key,
				'datalist_id' => $value['datalist_id'],
				'component_id' => $component_id,
				'user_id' => $vce->user->user_id,
			);
			$dossier_for_delete = $vce->generate_dossier($dossier);

			// $this_preset_config = base64_decode($preset->meta_value);
			$preset_content .= <<<EOF
			<div class="divTableCell input-padding">
				<form class="asynchronous-form input-padding" method="post" action="$vce->input_path">
					<input type="hidden" name="dossier" value="$dossier_for_get_preset">
					<input type="hidden" name="preset_name" value="$this_preset_name">
					<input type="hidden" name="preset_id" value="$this_preset_id">
						<button  class="button__primary preset-button" id="$preset_button_name">$this_preset_nice_name
				</form>
				<div class="preset-delete" preset-name="$this_preset_nice_name" dossier="$dossier_for_delete" action="$vce->input_path" title="Delete this preset.">X</div>
						</button>
			</div>

EOF;
				$longest_flag = NULL;	
			}

			$preset_content .= <<<EOF
			</div>
			</div>
			</div>
EOF;


			$data_report_content .= $vce->content->accordion('Presets', $preset_content, TRUE);

			$data_report_content .= $config_section;

			$table_contents = NULL;
			$table_contents .= <<<EOF
			<div id = "progressbar-component-data" class="progressbar">
			<div class = "progress-label" >Loading...</div>
			</div>


			<div class="table-container all-data-table">
			<button id="download-report-button" class="button__primary" type="submit">Copy This Report to Clipboard</button><br>
			<div id="data-message" style="display:none; background-color: #9DF2A3;">This table has been copied to the clipboard.</div>

				<table id="data-table" class="tablesorter" border=1> 
					<thead id="data-table-head"> 
					</thead> 
					<tbody id="data-table-body">
					</tbody>
				</table> 
			</div>

EOF;



		$report_results_accordion = $vce->content->accordion('Report Results', $table_contents, TRUE);

		$report_results_accordion_content = <<<EOF
			<div id="report-results-accordion-container" class="accordion-container hidden-on-load"> $report_results_accordion </div>
EOF;

			$data_report_content .= $report_results_accordion_content;

			return $data_report_content;


		}





		public function analytics_report_content($each_component, $last_analytics_preset_id, $data_report_content_input = array()) {

			global $vce;
			extract($data_report_content_input);

		// Component Report (main report form)

		// This is the list to be used in a query which shows what data (component meta_keys) to show
		$order_by_list = "('type','action_plan_goal','comments','created_at','created_by','cycle_participants','group','media_type','organization','pbccycle_begins','pbccycle_review','pbccycle_status','recipe_name','start_date')";

		//create form
		$component_data_content = '';



			// save query as preset

			// name of preset
			$input = array(
				'type' => 'text',
				'name' => 'preset_name',
				'required' => 'true',
				'class' => 'save-preset',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			
			$preset_name_input = $vce->content->create_input($input,'Analytics Preset Name');

			$dossier = array('type' => 'Nestor_reports',
				'procedure' => 'save_preset',
				'user_id' => $vce->user->user_id,
				'component_id' => $component_id,
				'parent_id' => $personal_analytics_presets_datalist_id,
				'created_at' => $each_component->created_at,
				'url' => $each_component_url
			);
			$dossier_for_save_preset = $vce->user->encryption(json_encode($dossier),$vce->user->session_vector);
			$component_data_content .= <<<EOF
			<p>
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_save_preset">
			<input type="hidden" class="analytics-subject-save" name="report_subject" value="">
			<input type="hidden" class="analytics-perspective-save" name="perspective" value="">
			<input schema="json" type="hidden" class="analytics-columns_to_show_list-save" name="columns_to_show_list" value="">
			<input type="hidden" class="start-date-analytics-data-save" name="analytics_start_date" value="">
			<input type="hidden" class="end-date-analytics-data-save" name="analytics_end_date" value="">
			


			$preset_name_input
			<button id="save-as-analytics-preset" class="button__primary data-download-button" type="submit">Save This Analytics Report as a Preset</button>
			</form>
			</p>

EOF;


		
		// choose action to show
		$query = "SELECT DISTINCT action AS data_category FROM " . TABLE_PREFIX . "analytics";
		$data = $vce->db->get_data_object($query);


			$data_categories = array(
				array(
					'name' => 'Select an Option',
					'value' => '',
					// 'selected' => $not_selected,
					'disabled' => true,
					'selected' => true,
				),
				array(
					'name' => 'year created',
					'value' => 'year',
					'selected' => false,
				),
				array(
					'name' => 'month created',
					'value' => 'month',
					'selected' => false,
				),
				array(
					'name' => 'day created',
					'value' => 'day',
					'selected' => false,
				)
			);

			foreach ($data as $each_data) {
				$selected = false;
				if ($each_data->data_category == $vce->data_category) {
					$selected = true;
				}
				$data_categories[] = array('name' => $each_data->data_category, 'value' => $each_data->data_category);
			}


			// if ($vce->user->role_hierarchy < 3) {
			// 	$show_all_keys_checkbox = '<input type="checkbox" name="show_all_keys" id="show-all-keys-checkbox" class="config-checkbox" autocomplete="off">&nbsp;Show All Keys?';
			// }	

			// perspective input
			$input = array(
				'type' => 'select',
				'name' => 'perspective',
				'required' => 'true',
				'class' => 'analytics-perspective key-toggle',
				'id' => 'data-category-analytics-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			$categories_component_data = $vce->content->create_input($input,"Order by: ");

			// Subject input
			// this needs to be updated to reflect other than the meta_keys
			$input = array(
				'type' => 'select',
				'name' => 'report_subject',
				'required' => 'true',
				'class' => 'analytics-subject key-toggle',
				'id' => 'analytics-subject-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			$report_subject = $vce->content->create_input($input, "Subject: ");

		

			// fields to view input
			$input = array(
				'type' => 'select',
				'name' => 'columns_to_show',
				'required' => 'true',
				'class' => 'analytics-columns-to-show key-toggle',
				'id' => 'analytics-columns-to-show-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
					'multiple' => TRUE,
				)
			);
			$columns_to_show = $vce->content->create_input($input, "Columns to show: (select multiple; selected are highlighted)");
			
				// primary, hidden fields to view input
				$primary_columns_to_show = <<<EOF
				<div id="primary-columns-to-show-data">
EOF;
				foreach ($data_categories as $k=>$v){
					$value = $v['value'];
					$primary_columns_to_show .= <<<EOF
<option value="$value">$value</option>
EOF;
				}
				$primary_columns_to_show .= <<<EOF
				</div>
EOF;



// start date input
			$input = array(
				'type' => 'text',
				'name' => 'analytics_start_date',
				'required' => 'true',
				'class' => 'datepicker',
				'id' => 'start-date-analytics-data',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
		
		$start_date_component_data = $vce->content->create_input($input,'Start Date');

		// end date input
		$input = array(
			'type' => 'text',
			'name' => 'analytics_end_date',
			'required' => 'true',
			'class' => 'datepicker',
			'id' => 'end-date-analytics-data',
			'data' => array (
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);
				
		$end_date_component_data = $vce->content->create_input($input,'End Date');
			
		


	
		$dossier_for_component_data_report = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => 'analytics_data_report', 'report'=>'analytics_data_report', 'user_id' => $vce->user->user_id, 'component_id' => $component_id, 'last_analytics_preset_id' => $last_analytics_preset_id, 'created_at' => $each_component_created_at, 'url' => $each_component_url, 'order_by_list' => $order_by_list)),$vce->user->session_vector);


		/*
			Main Form
			Entitled "Configuration" at present
			This is the form which defines what to query, and it also is the form which is altered in script.js
			Script.js updates current_page, head_has_been_sent and any other page-dependent inputs.
		*/
			$component_data_content .= <<<EOF
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_component_data_report">
			<input type="hidden" name="current_page" value="0">
			<input type="hidden" name="head_has_been_sent" value="0">
			<input schema="json" id="analytics_columns_to_show_list" class="selections" type="hidden" name="analytics_columns_to_show_list" value="">
			
			$report_subject
			$categories_component_data
			$columns_to_show
EOF;

			$component_data_content .= <<<EOF
			$primary_columns_to_show
			$start_date_component_data
			$end_date_component_data

EOF;


			
			$component_data_content .= <<<EOF
			<button id="analytics-data-download-button"  class="button__primary data-download-button" type="submit">View Analytics Report</button>
			</form>
		
EOF;


		
		// this is where the settings can be fine tuned for the data section
		$config_section = $vce->content->accordion('Data Report Configuration', $component_data_content);
		$data_report_content = NULL;


		$dossier_for_get_preset = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => 'get_report_preset', 'user_id' => $vce->user->user_id, 'component_id' => $component_id)),$vce->user->session_vector);

		// initialize $preset_content 
		$preset_content = NULL;

		$preset_name = 'last_analytics_query';
		$preset_content .= <<<EOF
		<div class="divTable ">
		<div class="divTableBody input-label-style">
		<div class="divTableRow">
			<div class="divTableCell input-padding">
				<form class="asynchronous-form" method="post" action="$vce->input_path">
				<input type="hidden" name="dossier" value="$dossier_for_get_preset">
				<input type="hidden" name="preset_name" value="$preset_name">
					<button  class="button__primary analytics-preset-button" id="last-analytics-report-button">Load Last Report</button>
			</form>
			</div>
		
EOF;



		// Find All Saved Presets, from the user and everyone in their jurisdiction
		// This involves getting the list of people within the user's jurisdiction, then getting their datalists,
		// then looping through the personal presets datalist to get all the presets and adding them to the complete list of presets
		
		$component_id = $component_id;
		$user_id = $vce->user->user_id;

		// get all users (filtered by jurisdiction)
		if (!isset($user_array)) {
			$user_array = self::assemble_user_info($vce);
		}

		$list_of_user_datalist_ids = $vce->read_datapoint_structure($personal_analytics_presets_datalist_id);

		// make an array with the name, id and content of each preset
		$personal_presets = array();
		foreach ($list_of_user_datalist_ids[$personal_analytics_presets_datalist_id] as $key=>$value) {
			if (is_array($value)) {
				$personal_presets[$key] = array('name'=>$value['datalist'],'datalist_id'=>$key);
			}
		}

		// $vce->dump($personal_presets);

		$i = 0;
		// $longest_flag = NULL;
		foreach ($personal_presets as $key=>$value) {
			$i++;
			if ($i > 6) {
				$preset_content .= <<<EOF
				</div>
				<div class="divTableRow input-label-style">
EOF;
				$i = 0;
			}				
			
			$this_preset_id = $key;
			// $displayed_preset_name = str_pad($value['name'], $longest_string, " ");
			$this_preset_name = $value['name'];
			$displayed_preset_name = $value['name'];
			// $displayed_preset_name = str_replace('*', '&nbsp;', $displayed_preset_name);
			$preset_button_name = $value['name'].'-'.$key.'-button';
			$this_preset_nice_name = ucwords(str_replace('_', ' ', $displayed_preset_name));
			// the instructions to pass through the delete form
			$dossier = array(
				'type' => 'Nestor_reports',
				'procedure' => 'delete_preset',
				'preset_id' => $key,
				'datalist_id' => $value['datalist_id'],
				'component_id' => $component_id,
				'user_id' => $vce->user->user_id,
			);
			$dossier_for_delete = $vce->generate_dossier($dossier);

			// $this_preset_config = base64_decode($preset->meta_value);
			$preset_content .= <<<EOF
			<div class="divTableCell input-padding">
				<form class="asynchronous-form input-padding" method="post" action="$vce->input_path">
					<input type="hidden" name="dossier" value="$dossier_for_get_preset">
					<input type="hidden" name="preset_name" value="$this_preset_name">
					<input type="hidden" name="preset_id" value="$this_preset_id">
						<button  class="button__primary preset-button" id="$preset_button_name">$this_preset_nice_name
				</form>
				<div class="preset-delete" preset-name="$this_preset_nice_name" dossier="$dossier_for_delete" action="$vce->input_path" title="Delete this Analytics preset.">X</div>
						</button>
			</div>

EOF;
				$longest_flag = NULL;	
			}

			$preset_content .= <<<EOF
			</div>
			</div>
			</div>
EOF;


			$data_report_content .= $vce->content->accordion('Presets', $preset_content, TRUE);

			$data_report_content .= $config_section;

			$table_contents = NULL;
			$table_contents .= <<<EOF
			<div id = "progressbar-analytics-data" class="progressbar">
			<div class = "progress-analytics-label" >Loading...</div>
			</div>


			<div class="table-container all-data-table">
			<button id="download-analytics-report-button" class="button__primary" type="submit">Download This Analytics Report</button><br>
			<div id="data-message" style="display:none; background-color: #9DF2A3;">This table has been copied to the clipboard.</div>

				<table id="analytics-data-table" class="tablesorter" border=1> 
					<thead id="analytics-table-head"> 
					</thead> 
					<tbody id="analytics-table-body">
					</tbody>
				</table> 
			</div>

EOF;


// old, unused analytics report accordion
//		$report_results_accordion = $vce->content->accordion('Analytics Report Results', $table_contents, TRUE);

// 		$report_results_accordion_content = <<<EOF
// 			<div id="analytics-report-results-accordion-container" class="accordion-container hidden-on-load"> $report_results_accordion </div>
// EOF;

			$data_report_content .= $report_results_accordion_content;

			return $data_report_content;


		}



/**
 * component_data_report collects all information about the chosen type of component
 * The data is converted from vertical key-value pairs which are joined by component_id to a horizontal 1-dim array.
 * Each array member contains all the data available for each component
 * Then the contents of the table are created so they can be inserted into the accordion showing the results of each query. ()
 **/


public function component_data_report($input) {

	global $vce;
	// $vce->log($input); exit;

	// encrypt this list of meta_keys


		$encrypt_this = array (
			0 => 'comments',
			1 => 'action_plan_goal',
			2 => 'created_by',
			3 => 'cycle_participants',
			4 => 'group',
			5 => 'organization',
			6 => 'pbc_cycle_review',
			7 => 'aps_assignee',
			8 => 'description',
			9 => 'email',
			10 => 'first_name',
			11 => 'last_name',
			12 => 'focus',
			13 => 'goal_achievement_evidence',
			14 => 'name',
			15 => 'password',
			16 => 'pbccycle_name',
			17 => 'preparation_notes',
			18 => 'step_comments',
		);


	if (isset($this->configuration['show_encrypted_data']) && $vce->user->role_hierarchy < 3) {
		$encrypt_this = array();
	}
	// perspective is the chosen meta_key from the form which governs how the data is ordered
	if (isset($input['perspective'])) {
		// pagination of results
		$current_page = (isset($input['current_page']))? $input['current_page'] : 0;
		$component_start_date = strtotime($input['component_start_date']);
		$component_end_date = strtotime($input['component_end_date']);
		
		//if this is the first page, save as last query
		if ($current_page == 0) {
			
			// keep the old value to reinsert later
			$temp_columns_to_show_list = $input['columns_to_show_list'];
			// decode json and make it a 2nd array dimension
			$input['columns_to_show_list'] = json_decode($input['columns_to_show_list'], TRUE);
			// foreach ($input as $k=>$v) {
			// 	$vce->log('input: '.$k.': '. $v);
			// }
			//ecode the present query in JSON
			$last_query = json_encode($input);
			// encode the JSON object in base64 so it can be saved in MySQL
			$last_query = base64_encode(json_encode($input));

			$input['columns_to_show_list'] = $temp_columns_to_show_list;
			// $vce->log($last_query);
			// $test_decode = base64_decode($last_query);
			// $test_decode = json_decode($test_decode);
			// foreach ($input as $k=>$v) {
			// 	$vce->log('output: '.$k.': '. $v);
			// }

			$attributes = array (
				'component_id' => $input['component_id'],
				'user_id' => $vce->user->user_id,
				'parent_id'=>$input['last_personal_preset_id'],
				'name'=>'last_query',
				'value' => $last_query
			);
			$vce->set_datapoint ($attributes);

		}
		
		// get array from the multi-select in the form which was sent as JSON hidden input
		// after being converted to that by the javascript from this page
		$columns_to_show_list = json_decode($input['columns_to_show_list'], true);
		$temp_list = explode("|", $columns_to_show_list['list_of_values']);
		$columns_to_show_list = "('";
		$columns_to_show_list .= implode("','", $temp_list);
		$columns_to_show_list .= "')";


		// $vce->log($columns_to_show_list);
		// $vce->log($input['order_by_list']);
		// exit;

		// query component data between dates
		// these two dates are for testing purposes only 
		// $input['component_start_date'] = "10 September 2018";
		// $input['component_end_date'] = "30 November 2018";

		// send out head of table if not sent already
		$head_has_been_sent = ($input['head_has_been_sent'] == TRUE)? $input['head_has_been_sent'] : FALSE;



		$show_all_keys = (isset($input['show_all_keys']))? $input['show_all_keys'] : 'off';
		// $order_by_list = (isset($input['order_by_list']))? $input['order_by_list'] : null;
		$order_by_list = (isset($columns_to_show_list))? $columns_to_show_list : $input['order_by_list'];

		$page_size = 2592000; // this is roughly one month (in seconds)
		$page_size = $page_size * 4; 
		$number_of_pages = ($component_end_date - $component_start_date) / $page_size;

		$component_data = array();

		// this calls the query to get data
		// it will cycle through pagination ranges until data is encountered, and then output
		// all query activity is in the method "get_component_data()"
		for ($i = 0; $i == count($component_data);){
			$component_data = $this->get_component_data($vce, $current_page, $page_size, $component_start_date, $component_end_date, $show_all_keys, $order_by_list);
			$current_page++;
			// echo json_encode(array('response' => 'report','current_page' => $current_page, 'table_section' => 'advance_progressbar', 'message' => '','form' => 'report'));
			if ($current_page > $number_of_pages ) {
				echo json_encode(array('response' => 'success','current_page' => $current_page, 'table_section' => 'done', 'message' => '','form' => 'report','action' => ''));
				return;
			}
		}


		// get component data 
		// this puts the entire content of the components_meta table (filtered by date) into an array. First dim is the component_id, second is the meta_key
		// each component may have its own set of meta_keys; those are not standardized for the table view
		$data_array = array();
		foreach ($component_data as $each_data) {
			// $vce->dump($each_data);
			if(isset($each_data->mk)) {
				// format the date 
				$formatted_date = date('Y-F-d',$each_data->ts);
				$d = explode('-',$formatted_date);
				$data_array[$each_data->component_id]['year'] = $d[0];
				$data_array[$each_data->component_id]['month'] = $d[1];
				$data_array[$each_data->component_id]['day'] = $d[2];

				// if chosen to encrypt a meta_key:
				if (in_array($each_data->mk, $encrypt_this)) {
					// creating a simple 14 digit unique id from the email address
					// this is an "ilkyo" id and is from a previous non-uw project I worked on.
					// the crc32 collision is worked around by reversing the string and and adding that onto the id.
					// the argument is treated as an integer, and presented as an unsigned decimal number.
					sscanf(crc32($each_data->mv), "%u", $front);
					sscanf(crc32(strrev($each_data->mv)), "%u", $back);
					// ilkyo id
					$each_data->mv = 'Encrypted data: ' . $front . substr($back, 0, (14-strlen($front)));					
				}
			$data_array[$each_data->component_id][$each_data->mk] = $each_data->mv;
			}
		}

// $vce->log($input);
		// output order by for date, type, role
		// sum by order by
		// $perspective = 'title';
		$perspective = $input['perspective'];
		$subject = $input['report_subject'];
		$final_array = array();
// $vce->log($subject);
		foreach ($data_array as $k=>$v) { // get each data array from the query result, after parsing the date
			$v2 = $v; // make a copy of the data array; we are going to use it as iteration twice
			foreach ($v as $kk=>$vv) { // go through data array to get keys
				if ($kk != $perspective) {
					continue;
				}
				$end_data = array();
				$include_this_row = FALSE;
				foreach ($v2 as $kkk=>$vvv) { // go through copy of data array to get values
					$end_data[$kkk] = $vvv;
					if ($kkk == $subject) {
						$include_this_row = TRUE;
					}
				}
				if ($include_this_row == TRUE) {
					$final_array[$kk][$vv][] = $end_data; // for instance: if the order_by (perspective) is Month, then $final_array['January']['created_by'[0] = <user_id>;
				}
			}
		}
		// $vce->dump($final_array);
		// exit;
		$dimensions = array_keys($final_array);
		// $vce->dump($final_array);
		// exit;
		// foreach ($dimension1 as $dim1) {
		// 	$dimensions[] = array_keys($final_array[$dim1]);
		// }
		// convert to 1 dimensional array
		// this gives the key-value (vertical) information organized as a horizontal, one-dim array
		// for example: each comment is one component, so each meta_key becomes a column and each corresponding meta_value is put into that column
		$one_dim_array_prep = array();  // this is the main array
		$order_by = 'Order By: '.ucfirst($perspective);
		$one_dim_array_prep[0][$order_by] = $order_by; // set the order_by info as first column in the header element
		$header = array(); // gather header info as the array is filled, then copy to the first element later
		foreach($final_array as $k=>$v){
			$i = 0;
			foreach($v as $kk=>$vv){
				foreach($vv as $kkk=>$vvv){
					$gather_data = array($order_by=>array($order_by=>$kk)); // set the order_by info as first column in each element
					foreach($vvv as $kkkk=>$vvvv){
						if (!array_key_exists($kkkk, $header)) {
							$header[$kkkk] = $i;
							$i++;
						}
						$key = $header[$kkkk];
						$gather_data[$key] = array($kkkk=>$vvvv);
						unset($key);
					}
					$one_dim_array_prep[] = $gather_data;
				}
			}
		}
		// add header to display array
		foreach ($header as $k=>$v) {
			$one_dim_array_prep[0][$v] = $k;
		}

		// add spacing so that every array element has the same number of sub-elements
		$one_dim_array = array();
			foreach($one_dim_array_prep as $k=>$v) {
				if ($k == 0) {
					$one_dim_array[$k] = array();
					foreach($v as $kk=>$vv) {
						$one_dim_array[$k][] = (isset($vv)) ? $vv : null;
					}
				} elseif ($k != 0) {
					$one_dim_array[$k] = array();
					foreach($one_dim_array_prep[0] as $k2=>$v2) {
						$one_dim_array[$k][] = (isset($v[$k2][$v2])) ? $v[$k2][$v2] : null;
					}

				}
			}

		// $display is what to use for showing or downloading
		// $one_dim_array is an array formatted for table view and csv view
		$display = $one_dim_array_prep;
// // 			$vce->dump($header);
// 			$vce->dump($display);

// exit;

	//if the direct_download checkbox is not checked, output as table
	if (!isset($vce->direct_download)) {
			//save contents of table into separate string
			$table_contents = NULL;
			$table_section = NULL;
			// $vce->log($display);
			$headers = array();
			foreach($display as $k=>$v) {
				foreach($v as $kk=>$vv) {
					if(!is_array($vv)) {
						// $vce->log($vv);
						$headers[$vv]=$kk;
					}
				}
			}
			// $vce->log($headers);
			// exit;
			// this is to keep the JS resubmitting the form; there must be a value for $table_section
			$table_section = 'body';
			foreach($display as $k=>$v) {
				if ($k == 0 && $head_has_been_sent == FALSE) {
					$head_has_been_sent = TRUE;
					$table_section = 'head';
					// $vce->log($v);
					$table_contents .= <<<EOF
<tr>
EOF;

					foreach($v as $kk=>$vv) {
						$table_contents .= <<<EOF
<th>$vv</th>
EOF;
					}
					$table_contents .= <<<EOF
</tr>
EOF;


				} elseif ($k != 0) {
			$table_section = 'body';
			$table_contents .= <<<EOF
<tr>
EOF;
				foreach($display[0] as $k2=>$v2) {
						$field = (isset($v[$k2][$v2]))?$v[$k2][$v2] : null;
						$table_contents .= <<<EOF
<td>$field</td>
EOF;

					}

				$table_contents .= <<<EOF
				</tr>
EOF;
			}
		}
		// $vce->log($table_contents); exit;
		echo json_encode(array('response' => 'success','current_page' => $current_page, 'number_of_pages' => $number_of_pages, 'table_section' => $table_section, 'head_has_been_sent' => $head_has_been_sent, 'message' => $table_contents,'form' => 'report','action' => ''));
		return;

	} else {
		//download directly
		exit;
		// convert cycles array to csv and output
		$now = date("Y-m-d_h_i_sa");
		$filename = 'component_data_ordered_by_'.$perspective.'_'.$now.'-'.'.csv';
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');

		// open the "output" stream
		// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
		$fp = fopen('php://output', 'w');
		foreach ($display as $line) {
			fputcsv($fp, $line);
			
		}
		fclose($fp);

		exit;

		}
	}
				


return $content;

}


/**
 * analytics_data_report collects all information about the chosen type of analytics
 * The data is converted from vertical key-value pairs which are joined by component_id to a horizontal 1-dim array.
 * Each array member contains all the data available for each analytics
 * Then the contents of the table are created so they can be inserted into the accordion showing the results of each query. ()
 **/

public function analytics_data_report($input) {

	global $vce;
	// $vce->log($input); exit;

	// encrypt this list of meta_keys


		$encrypt_this = array (
			0 => 'comments',
			1 => 'action_plan_goal',
			2 => 'created_by',
			3 => 'cycle_participants',
			4 => 'group',
			5 => 'organization',
			6 => 'pbc_cycle_review',
			7 => 'aps_assignee',
			8 => 'description',
			9 => 'email',
			10 => 'first_name',
			11 => 'last_name',
			12 => 'focus',
			13 => 'goal_achievement_evidence',
			14 => 'name',
			15 => 'password',
			16 => 'pbccycle_name',
			17 => 'preparation_notes',
			18 => 'step_comments',
		);


	if (isset($this->configuration['show_encrypted_data']) && $vce->user->role_hierarchy < 3) {
		$encrypt_this = array();
	}
	// perspective is the chosen meta_key from the form which governs how the data is ordered
	if (isset($input['perspective'])) {
		// pagination of results
		$current_page = (isset($input['current_page']))? $input['current_page'] : 0;
		$analytics_start_date = strtotime($input['analytics_start_date']);
		$analytics_end_date = strtotime($input['analytics_end_date']);
		
		//if this is the first page, save as last query
		if ($current_page == 0) {
			
			// keep the old value to reinsert later
			$temp_columns_to_show_list = $input['analytics_columns_to_show_list'];
			// decode json and make it a 2nd array dimension
			$input['analytics_columns_to_show_list'] = json_decode($input['analytics_columns_to_show_list'], TRUE);
			// foreach ($input as $k=>$v) {
			// 	$vce->log('input: '.$k.': '. $v);
			// }
			//ecode the present query in JSON
			$last_query = json_encode($input);
			// encode the JSON object in base64 so it can be saved in MySQL
			$last_query = base64_encode(json_encode($input));

			$input['analytics_columns_to_show_list'] = $temp_columns_to_show_list;


			$attributes = array (
				'component_id' => $input['component_id'],
				'user_id' => $vce->user->user_id,
				'parent_id'=>$input['last_analytics_preset_id'],
				'name'=>'last_analytics_query',
				'value' => $last_query
			);
			$vce->set_datapoint ($attributes);

		}
		
		// get array from the multi-select in the form which was sent as JSON hidden input
		// after being converted to that by the javascript from this page
		$columns_to_show_list = json_decode($input['analytics_columns_to_show_list'], true);
		$temp_list = explode("|", $columns_to_show_list['list_of_values']);
		$columns_to_show_list = "('";
		$columns_to_show_list .= implode("','", $temp_list);
		$columns_to_show_list .= "')";


		// send out head of table if not sent already
		$head_has_been_sent = ($input['head_has_been_sent'] == TRUE)? $input['head_has_been_sent'] : FALSE;



		$show_all_keys = (isset($input['show_all_keys']))? $input['show_all_keys'] : 'off';
		// $order_by_list = (isset($input['order_by_list']))? $input['order_by_list'] : null;
		$order_by_list = (isset($columns_to_show_list))? $columns_to_show_list : $input['order_by_list'];
		// $vce->log($order_by_list);

		$page_size = 2592000; // this is roughly one month (in seconds)
		$page_size = $page_size * 4; 
		$number_of_pages = ($analytics_end_date - $analytics_start_date) / $page_size;
		// $vce->log($analytics_start_date);
		// $vce->log($analytics_end_date);
		$analytics_data = array();

		// this calls the query to get data
		// it will cycle through pagination ranges until data is encountered, and then output
		// all query activity is in the method "get_analytics_data()"
		for ($i = 0; $i == count($analytics_data);){
			// $vce->log($current_page);
			$analytics_data = $this->get_analytics_data($vce, $current_page, $page_size, $analytics_start_date, $analytics_end_date, $show_all_keys, $order_by_list);
			$current_page++;
			// echo json_encode(array('response' => 'report','current_page' => $current_page, 'table_section' => 'advance_progressbar', 'message' => '','form' => 'report'));
			if ($current_page > $number_of_pages ) {
				echo json_encode(array('response' => 'success','current_page' => $current_page, 'table_section' => 'done', 'message' => '','form' => 'report','action' => ''));
				return;
			}
		}


		// get analytics data 
		// this puts the entire content of the components_meta table (filtered by date) into an array. First dim is the component_id, second is the meta_key
		// each component may have its own set of meta_keys; those are not standardized for the table view
		$data_array = array();
		foreach ($analytics_data as $each_data) {
			// $vce->log($each_data);
			// exit;
			if(isset($each_data->mk)) {
				$phpdate = strtotime( $each_data->ts );
				$formatted_date = date('Y-F-d', $phpdate);

				$d = explode('-',$formatted_date);
				$data_array[$each_data->id]['year'] = $d[0];
				$data_array[$each_data->id]['month'] = $d[1];
				$data_array[$each_data->id]['day'] = $d[2];

				if (in_array($each_data->mk, $encrypt_this)) { // not in use for analytics
					// creating a simple 14 digit unique id from the email address
					// this is an "ilkyo" id and is from a previous non-uw project I worked on.
					// the crc32 collision is worked around by reversing the string and and adding that onto the id.
					// the argument is treated as an integer, and presented as an unsigned decimal number.
					sscanf(crc32($each_data->mv), "%u", $front);
					sscanf(crc32(strrev($each_data->mv)), "%u", $back);
					// ilkyo id
					$each_data->mv = 'Encrypted data: ' . $front . substr($back, 0, (14-strlen($front)));					
				}
			$data_array[$each_data->id][$each_data->mk] = $each_data->mv;
			}
		}

		// $vce->log($data_array);
		// exit;
		// output order by for date, type, role
		// sum by order by
		// $perspective = 'title';
		$perspective = $input['perspective'];
		$final_array = array();

		foreach ($data_array as $k=>$v) { // get each data array from the query result, after parsing the date
			$v2 = $v; // make a copy of the data array; we are going to use it as iteration twice
			foreach ($v as $kk=>$vv) { // go through data array to get keys
				if ($kk != $perspective) {
					continue;
				}
				$end_data = array();
				foreach ($v2 as $kkk=>$vvv) { // go through copy of data array to get values
					$end_data[$kkk] = $vvv;
				}
				$final_array[$kk][$vv][] = $end_data;
			}
		}
		// $vce->dump($final_array);
		// exit;
		$dimensions = array_keys($final_array);
		// $vce->log($final_array);
		exit;
		// foreach ($dimension1 as $dim1) {
		// 	$dimensions[] = array_keys($final_array[$dim1]);
		// }
		// convert to 1 dimensional array
		// this gives the key-value (vertical) information organized as a horizontal, one-dim array
		// for example: each comment is one component, so each meta_key becomes a column and each corresponding meta_value is put into that column
		$one_dim_array_prep = array();  // this is the main array
		$order_by = 'Order By: '.ucfirst($perspective);
		$one_dim_array_prep[0][$order_by] = $order_by; // set the order_by info as first column in the header element
		$header = array(); // gather header info as the array is filled, then copy to the first element later
		foreach($final_array as $k=>$v){
			$i = 0;
			foreach($v as $kk=>$vv){
				foreach($vv as $kkk=>$vvv){
					$gather_data = array($order_by=>array($order_by=>$kk)); // set the order_by info as first column in each element
					foreach($vvv as $kkkk=>$vvvv){
						if (!array_key_exists($kkkk, $header)) {
							$header[$kkkk] = $i;
							$i++;
						}
						$key = $header[$kkkk];
						$gather_data[$key] = array($kkkk=>$vvvv);
						unset($key);
					}
					$one_dim_array_prep[] = $gather_data;
				}
			}
		}
		// add header to display array
		foreach ($header as $k=>$v) {
			$one_dim_array_prep[0][$v] = $k;
		}

		// add spacing so that every array element has the same number of sub-elements
		$one_dim_array = array();
			foreach($one_dim_array_prep as $k=>$v) {
				if ($k == 0) {
					$one_dim_array[$k] = array();
					foreach($v as $kk=>$vv) {
						$one_dim_array[$k][] = (isset($vv)) ? $vv : null;
					}
				} elseif ($k != 0) {
					$one_dim_array[$k] = array();
					foreach($one_dim_array_prep[0] as $k2=>$v2) {
						$one_dim_array[$k][] = (isset($v[$k2][$v2])) ? $v[$k2][$v2] : null;
					}

				}
			}

		// $display is what to use for showing or downloading
		// $one_dim_array is an array formatted for table view and csv view
		$display = $one_dim_array_prep;
// 			$vce->dump($header);
// 			$vce->log($display);

// exit;

	//if the direct_download checkbox is not checked, output as table (direct download is not currently an option)
	if (!isset($vce->direct_download)) {
			//save contents of table into separate string
			$table_contents = NULL;
			$table_section = NULL;
			// $vce->log($display);
			$headers = array();
			foreach($display as $k=>$v) {
				foreach($v as $kk=>$vv) {
					if(!is_array($vv)) {
						// $vce->log($vv);
						$headers[$vv]=$kk;
					}
				}
			}
			// $vce->log($headers);
			// exit;
			foreach($display as $k=>$v) {
				if ($k == 0 && $head_has_been_sent == FALSE) {
					$head_has_been_sent = TRUE;
					$table_section = 'head';
					// $vce->log($v);
					$table_contents .= <<<EOF
<tr>
EOF;

					foreach($v as $kk=>$vv) {
						$table_contents .= <<<EOF
<th>$vv</th>
EOF;
					}
					$table_contents .= <<<EOF
</tr>
EOF;


				} elseif ($k != 0) {
			$table_section = 'body';
			$table_contents .= <<<EOF
<tr>
EOF;
				foreach($display[0] as $k2=>$v2) {
						$field = (isset($v[$k2][$v2]))?$v[$k2][$v2] : null;
						$table_contents .= <<<EOF
<td>$field</td>
EOF;

					}

				$table_contents .= <<<EOF
				</tr>
EOF;
			}
		}
		// $vce->log($table_contents); exit;
		echo json_encode(array('response' => 'success','current_page' => $current_page, 'number_of_pages' => $number_of_pages, 'table_section' => $table_section, 'head_has_been_sent' => $head_has_been_sent, 'message' => $table_contents,'form' => 'analytics_report','action' => ''));
		return;

	} else {
		//download directly (currently not used)
		exit;
		// convert cycles array to csv and output
		$now = date("Y-m-d_h_i_sa");
		$filename = 'component_data_ordered_by_'.$perspective.'_'.$now.'-'.'.csv';
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');

		// open the "output" stream
		// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
		$fp = fopen('php://output', 'w');
		foreach ($display as $line) {
			fputcsv($fp, $line);
			
		}
		fclose($fp);

		exit;

		}
	}
				


return $content;

}
		

/**
 * get_component_data
 * This is a query that gets all the data from the components_meta table within the given (paginated) date-range
 * Sorting takes place in component_data_report
 */


public function get_component_data($vce, $current_page, $page_size, $component_start_date, $component_end_date, $order_by_list, $show_all_keys = 'off') {

	$calculated_start_date = $component_start_date + ($current_page * $page_size);
	$paginated_component_start_date = ($calculated_start_date <= $component_end_date)? $calculated_start_date : null;
	$one_page_ahead = $paginated_component_start_date + $page_size;
	$paginated_component_end_date = ($one_page_ahead <= $component_end_date) ? $paginated_component_start_date + $page_size : $component_end_date;



	if ($vce->user->role_hierarchy > 2) {  // if the user doesn't have jurisdiction over the whole user-base
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE);
		$user_jurisdiction_condition = " AND d.meta_key = 'created_by' AND d.meta_value IN ($users_in_jurisdiction)";
	} else {
		$user_jurisdiction_condition = '';
	}

	if (isset($order_by_list) &&  $order_by_list != '') {
		$order_by_list_condition = " AND b.meta_key IN $order_by_list";
	} else {
		$order_by_list_condition = '';
	}

	$query = "SELECT a.meta_value AS ts, b.component_id AS component_id, b.meta_key AS mk, b.meta_value AS mv FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $paginated_component_start_date AND c.meta_value < $paginated_component_end_date $order_by_list_condition $user_jurisdiction_condition";
	// $vce->log($query);
	// exit;
	$component_data = $vce->db->get_data_object($query);
	// $vce->log($component_data);

	return $component_data;
}



public function get_analytics_data($vce, $current_page, $page_size, $analytics_start_date, $analytics_end_date, $order_by_list, $show_all_keys = 'off') {
	// $vce->log($analytics_start_date);
	// exit;
	$calculated_start_date = $analytics_start_date + ($current_page * $page_size);
	$paginated_component_start_date = ($calculated_start_date <= $analytics_end_date)? $calculated_start_date : null;
	$one_page_ahead = $paginated_component_start_date + $page_size;
	$paginated_component_end_date = ($one_page_ahead <= $analytics_end_date) ? $paginated_component_start_date + $page_size : $analytics_end_date;

	$paginated_component_start_date = 'FROM_UNIXTIME(' . $paginated_component_start_date . ')';
	$paginated_component_end_date = 'FROM_UNIXTIME(' . $paginated_component_end_date . ')';

	if (isset($show_all_keys) &&  $show_all_keys != 'on') {
		$order_by_list_condition = " AND a.action IN $order_by_list";
	} else {
		$order_by_list_condition = '';
	}

	// mk = meta_key, mv = meta_value ts = timestamp
	// $query = "SELECT a.meta_value AS ts, b.component_id AS component_id, b.meta_key AS mk, b.meta_value AS mv FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $paginated_component_start_date AND c.meta_value < $paginated_component_end_date $order_by_list_condition";
	// $query = "SELECT user_id AS ts, component_id AS component_id, action AS mk, object AS mv FROM `vce_analytics` WHERE action = 'login'";
	// $query = "SELECT a.timestamp AS ts, b.component_id AS component_id, b.meta_key AS mk, b.meta_value AS mv FROM " . TABLE_PREFIX . "analytics AS a RIGHT JOIN " . TABLE_PREFIX . "analytics AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "analytics as c on a.component_id = c.component_id WHERE a.timestamp > $paginated_component_start_date AND a.timestamp < $paginated_component_end_date $order_by_list_condition";
	$query = "SELECT a.timestamp AS ts, a.id AS id, a.action AS mk, a.object AS mv FROM " . TABLE_PREFIX . "analytics AS a WHERE a.timestamp > $paginated_component_start_date AND a.timestamp < $paginated_component_end_date  $order_by_list_condition";

	
	// $vce->log($query);
	// exit;
	$component_data = $vce->db->get_data_object($query);
	
	// $vce->log($component_data);
	// exit;

	return $component_data;
}




		/**
		 * admin configuration
		 */
		public function admin_configuration($input) {

			global $vce;

			// $vce->site->add_attributes('user_id',$input['user_id']);

			// $vce->site->add_attributes('pagination_current',$input['pagination_current']);


			echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
			return;

	}

		/**
		 * create summary table
		 * This creates an expandable table starting with start and end datepickers
		 * The individual fields are methods in this class which start with the word "summary_"
		 * If the user has configured a subset of these methods in the "Configure Summary" section, only these will appear.
		 **/

		public function create_summary_table($input) {

			global $vce;

			// $vce->log($input);

				// put all filter_by_user inputs into one list
	$input['users_filter'] = array();
	foreach ($input as $k=>$v) {
		if (strpos($k, 'filter_by_user') !== false) {
			if ($v == 'test_users') {
				$vce->site->testusers = 'on';
			} else {
				$input['users_filter'][] = $v;
			}
			unset($input[$k]);
		}
	}
	if (count($input['users_filter']) < 1) {
		$input['users_filter'] = array(1,4,5,6,2,3,7);
	}

	$input['users_filter'] = implode('|', $input['users_filter']);


// $vce->log($input['users_filter']);
			// $vce->log('create');
			extract($input);
			// get the saved summary list
			// it is saved as a json encoded array where the key is 'list_of_values' and the value is a pipeline delineated list of names of existing summary_<name> methods
			$data_to_show = $vce->read_datapoint(array('name'=>$datalist,'datalist_id'=>$datalist_id));
			// $vce->log($data_to_show);
			$data_to_show = base64_decode($data_to_show);
			$data_to_show = json_decode($data_to_show, TRUE);
			$data_to_show['list_of_values'] = (isset($data_to_show['list_of_values'])) ? $data_to_show['list_of_values'] : '';
			$data_to_show = explode('|', $data_to_show['list_of_values']);
			// $vce->dump($data_to_show);

			if (!isset($start_date) || !isset($end_date)) {
				// $start_date = strtotime($input['start_date']);

				$start_date = strtotime('-1 year');
				$start_date = date('m\/d\/Y', $start_date);

				$end_date = strtotime('+1 day');
				$end_date = date('m\/d\/Y', $end_date);

			}

			// create array of summary fields
			$summary_array = array(
				array(
					'name' => 'start_date',
					'display_name' => 'start date'
				),
				array(
					'name' => 'end_date',
					'display_name' => 'end date (Through End of Day)'
				),
			);

			// add all methods which start with "summary_" to the $summary_array so they can be called
			// if they are not in the $data_to_show list, do not add 
			// $class_methods = get_class_methods($this);
			// foreach ($class_methods as $method_name) {
			// 	if (substr($method_name, 0, 8) == 'summary_') {
			// 		$method_name = str_replace('summary_', '', $method_name);
			// 		if (!in_array($method_name, $data_to_show)) {
			// 			continue;
			// 		}
			// 		$summary_array[] = array(
			// 			'name' => $method_name,
			// 		);
			// 	}
			// }

	// add all methods which start with "summary_" to the $summary_array so they can be called	
	// if they are not in the $data_to_show list, do not add 

	// routine for getting the summary_methods (nestorreportstype) minions
	// they are converted to an array of class names and method names
	if (isset($test_users) && $test_users == 'on') {
		$vce->site->tester_toggle = 'on';
	}

	if (!isset($vce->site->summaries)) {
		require_once(dirname(__FILE__) . '/nestor_reportstype/nestor_reportstype.php');	
// $vce->dump($vce->site->summaries);
		$this->add_reports_summaries($vce);
	}

	// default summaries: this list used to be configurable, but now is hard coded HERE:
	$vce->site->summaries = json_decode('{"nestor_report_summaries_hscc":{"class_name":"nestor_report_summaries_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);

	// $vce->log($vce->site->summaries);
	// $summaries = json_decode('{"nestor_report_summaries_hscc":{"class_name":"nestor_report_summaries_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);
// $vce->dump($summaries);
	foreach ($vce->site->summaries as $key=>$value) {
		// $vce->dump($value);
		// $vce->dump($data_to_show);
		// $summary_class = new $key;
		foreach ($value['methods'] as $k=>$v){
			// if (!in_array($v['method_name'], $data_to_show)) {
			// 	continue;
			// }
			$summary_array[] = array(
				'name' => $v['method_name'],
				'display_name' => $v['display_name'],
				'summary_class' => $key
			);
			// $method_name = $v['method_name'];
			// $result = $summary_class->$method_name($input);
		}
	}

	//Create Coaching Companion Summary Section

		$summary_table_content = NULL;

		$loading_gif = $vce->site->site_url.'/vce-content/components/nestor_reports/images/loading_large.gif';

	
		$number_of_columns = 4;
		$total_number_of_rows = ceil(count($summary_array) / $number_of_columns);
		// $vce->dump($total_number_of_rows);
	
		$summary_table_content .= <<<EOF
		<div id="summary-message" style="display:none; background-color: #9DF2A3;">This table has been copied to the clipboard.</div>


	<table id="summary-table" class="tablesorter">
	<script>
		var loading_gif = '<img src="$loading_gif">';
	</script>
EOF;
	
		for ($ii = 0; $ii < $total_number_of_rows; $ii++ ) {
	
	
		$summary_table_content .= <<<EOF
	<thead>
	<tr>
EOF;
	
	
	
	// dynamic rows in head
	$i = 0;
	// $vce->dump($summary_array);
	foreach ($summary_array as $k => $v) {
		// $vce->dump($k);
		// $vce->dump($v);

		if ($i == $number_of_columns) {
			$i = 0;
			break;
		}
		if (isset($v['name'])){
			$v['display_name'] = (isset($v['display_name'])) ? $v['display_name'] : $v['name'];
			$column_title = ucwords(str_replace('_', ' ', $v['display_name']));
			$summary_table_content .= <<<EOF
	<th>$column_title</th>
EOF;
		}
		$i++;
	}
	
// 	$summary_table_content .= <<<EOF
// 	</tr>
// 	</thead>
// 	<thead>
// 	<tr>
// EOF;
	
// 	// checkboxes for dynamic rows in head
// 	$i = 0;
// 	foreach ($summary_array as $k => $v) {
// 		if ($i == $number_of_columns) {
// 			$i = 0;
// 			break;
// 		}
// 		$summary_table_content .= <<<EOF
// 	<th><input type="checkbox" checked="checked"></th>
// EOF;
// 		$i++;
// 	}
	
	$summary_table_content .= <<<EOF
	</tr>
	</thead>
	<tr>
EOF;
	
	// content after head
	// <script>
	// $('.thisone').is(":visible") == true, function(e) {
	//    alert('thisone');
	// }
	// </script>
	// <td class="align-center">$start_date</td>
	// <td class="align-center thisone">$end_date</td>
	$i = 0;
	foreach ($summary_array as $k => $v) {
		if ($i == $number_of_columns) {
			$i = 0;
			break;
		}
		
		// $dossier_for_field = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports','procedure' => $v['name'],  'user_id' => $vce->user->user_id)),$vce->user->session_vector);
	
		// special cases: start and end date inputs

		if (isset($v['name']) && $v['name'] == 'start_date') {

			$summary_table_content .= <<<EOF
			<td class="align-center">$start_date</td>
EOF;
				$i++;
				unset ($summary_array[$k]);
				continue;
			}

			if (isset($v['name']) && $v['name'] == 'end_date') {

		$summary_table_content .= <<<EOF
		<td class="align-center">$end_date</td>
EOF;
			$i++;
			unset ($summary_array[$k]);
			continue;
			}
	
		// for all other content:
				// foreach ($vce->site->summaries as $key=>$value) {
	// 	$summary_class = new $key;
	// 	foreach ($value['methods'] as $k=>$v){
	// 		$method_name = $v['method_name'];
	// 		$result = $summary_class->$method_name($input);
	// 		$vce->dump($result);
	// 	}
	// }

		// create input for report methods
		$input = array('start_date'=>$start_date, 'end_date'=>$end_date, 'users_filter'=>$users_filter);


		// $dossier_for_field = (isset($v['dossier']))? $v['dossier'] : NULL;
		$method_name = $v['name'];
		$summary_class = new $v['summary_class'];
		$data = NULL;
		if(isset($start_date) && isset($end_date)) {
			$data = $summary_class->$method_name($input);
			unset($summary_class);
		}
		$summary_table_content .= <<<EOF
	<td class="">
			<div class="loading-di">$data</div>
	</td>
EOF;
			// remove data already added to table
			unset ($summary_array[$k]);
			$i++;
		}
	
		$summary_table_content .= <<<EOF
	</tr>
EOF;
	
	
		}
		$summary_table_content .= <<<EOF
	</table>
	<br>
EOF;
		if (isset($url)) {
			echo json_encode(array('response' => 'success', 'message' => $summary_table_content, 'form' => 'summary','action' => ''));
			return;
		}

		return $summary_table_content;

	}


		/**
		 * save advanced options
		 * these are the options which show or hide the sections of the reports page
		 **/

		public function save_advanced_options($input) {
			global $vce;


			if (isset($input['show_summary']) && $input['show_summary'] == 'on') {
				$input['show_summary'] = 'on';
			} else {
				$input['show_summary'] = 'off';
			}
			if (isset($input['show_data_report']) && $input['show_data_report'] == 'on') {
				$input['show_data_report'] = 'on';
			} else {
				$input['show_data_report'] = 'off';
			}
			if (isset($input['show_analytics_report']) && $input['show_analytics_report'] == 'on') {
				$input['show_analytics_report'] = 'on';
			} else {
				$input['show_analytics_report'] = 'off';
			}


			if(self::update_component($input)) {
				echo json_encode(array('response' => 'success', 'message' => 'Advanced options have been saved.','form' => 'save-advanced-options','action' => ''));
				return;
			}
			echo json_encode(array('response' => 'error', 'message' => 'Advanced options could not be saved.','form' => 'save-advanced-options','action' => ''));
			return;
	


		}

		/**
		 * save preset 
		 **/

		public function save_preset($input) {

			global $vce;
// $vce->log($input);
// exit;
			if ($input['preset_name'] && $input['component_id']) {
				$component_id = $input['component_id'];
				$parent_id = $input['parent_id'];
				$user_id = $vce->user->user_id;
				$preset_name = $vce->db->sanitize(str_replace(' ', '_', $input['preset_name']));
			
				
					// keep the old value to reinsert later
					$temp_columns_to_show_list = $input['columns_to_show_list'];
					// decode json and make it a 2nd array dimension
					$input['columns_to_show_list'] = json_decode($input['columns_to_show_list'], TRUE);
					// foreach ($input as $k=>$v) {
					// 	$vce->log('input: '.$k.': '. $v);
					// }

					//encode the present query in JSON
					$last_query = json_encode($input);
					// encode the JSON object in base64 so it can be saved in MySQL
					$last_query = base64_encode(json_encode($input));
	
					$input['columns_to_show_list'] = $temp_columns_to_show_list;
					// $vce->log($last_query);
					// $test_decode = base64_decode($last_query);
					// $test_decode = json_decode($test_decode);
					// foreach ($input as $k=>$v) {
					// 	$vce->log('output: '.$k.': '. $v);
					// }
					// $vce->log($datalist_id);
					$attributes = array (
						'parent_id' => $parent_id,
						'component_id' => $component_id,
						'user_id' => $user_id,
						'name' => $preset_name, 
						'value' => $last_query
					);
			   
					$vce->set_datapoint($attributes);
					// $vce->insert_datalist_items($attributes);



				




				echo json_encode(array('response' => 'success', 'message' => 'Preset has been saved.','form' => 'save-preset','action' => ''));
				return;
	
			}
		
		
		}

		/**
		 * save presetBAK
		 **/

		public function save_presetBAK($input) {

			global $vce;

			if ($input['preset_name'] && $input['component_id'] && $input['datalist_id']) {
				$component_id = $input['component_id'];
				$datalist_id = $input['datalist_id'];
				$user_id = $vce->user->user_id;
				$preset_name = str_replace(' ', '_', $input['preset_name']);
			
				
					// keep the old value to reinsert later
					$temp_columns_to_show_list = $input['columns_to_show_list'];
					// decode json and make it a 2nd array dimension
					$input['columns_to_show_list'] = json_decode($input['columns_to_show_list'], TRUE);
					// foreach ($input as $k=>$v) {
					// 	$vce->log('input: '.$k.': '. $v);
					// }
					//ecode the present query in JSON
					$last_query = json_encode($input);
					// encode the JSON object in base64 so it can be saved in MySQL
					$last_query = base64_encode(json_encode($input));
	
					$input['columns_to_show_list'] = $temp_columns_to_show_list;
					// $vce->log($last_query);
					// $test_decode = base64_decode($last_query);
					// $test_decode = json_decode($test_decode);
					// foreach ($input as $k=>$v) {
					// 	$vce->log('output: '.$k.': '. $v);
					// }
					// $vce->log($datalist_id);
					$attributes = array (
						'component_id' => $component_id,
						'user_id' => $user_id,
						'datalist_id' => $datalist_id,
						'items' => array ( array ('name' => $preset_name, 'preset' => $last_query ) )
					);
			   
					$vce->insert_datalist_items($attributes);

					// $save_last_query = array('component_id'=>$input['component_id'], 'type'=>$input['type'], 'created_at'=>$input['created_at'], 'url'=>$input['url'], 'presetname_'.$preset_name=>$last_query);
					// Self::update_component($save_last_query);

				




				echo json_encode(array('response' => 'success', 'message' => 'Preset has been saved.','form' => 'save-preset','action' => ''));
				return;
	
			}
		
		
		}

		public function delete_preset($input) {

			global $vce;
// $vce->log($input);
// exit;

			if ($input['datalist_id'] && $input['component_id']) {
				$component_id = $input['component_id'];
				// $preset_name = $input['preset_name'];

				$attributes = array(
					'component_id' => $component_id,
					'user_id' => $user_id,
					'name' => $preset_name,
					'datalist_id' => $input['datalist_id']
				);
				$vce->delete_datapoint($attributes);



				echo json_encode(array('response' => 'success', 'message' => 'Preset has been deleted.','form' => 'delete','action' => ''));
				return;
	
			}
		
		
		}


		/**
		 * get saved report config 
		 **/

		public function get_report_preset($input) {

			global $vce;

			// $vce->log($input);
			// $vce->dump($input);
			// exit;
			if ($input['user_id'] && $input['component_id']) {
				$component_id = $input['component_id'];
				$preset_name = $input['preset_name'];
				// $preset_id = $input['preset_id'];
				$user_id = $input['user_id'];

				$attributes = array (
					'component_id' => $component_id,
					'user_id' => $vce->user->user_id,
					'name' => $preset_name
				);
				   
				$data = $vce->read_datapoint($attributes);


				$preset = base64_decode($data);
				$preset_as_array = json_decode($preset, TRUE);
				$parent_id = $preset_as_array['parent_id'];
				$query = "SELECT meta_value FROM " . TABLE_PREFIX . "datalists_meta WHERE datalist_id = $parent_id AND meta_key = 'datalist'";
				$result = $vce->db->get_data_object($query);
				foreach ($result as $r) {
					$parent_datalist_name = $r->meta_value;
				}
				$parent_datalist_name = (isset($parent_datalist_name))? $parent_datalist_name : NULL;
// 				$vce->log($parent_datalist_name);
// exit;
				echo json_encode(array('response' => 'success', 'message' => $preset, 'parent_datalist_name' => $parent_datalist_name, 'form' => 'preset','action' => ''));
				return;
	
			}
		
		
		}




	
	



	public function edit_summary($input) {
		global $vce;


		if (isset($input["component_id"]) && isset($input["datalist_id"])) {
			$component_id = $input['component_id'];
			$datalist_id = $input['datalist_id'];
			$user_id = $vce->user->user_id;
			$preset_name = 'summary_preset';


			$datapoints = base64_encode($input['datapoints_list']);
			$attributes = array (
				'component_id' => $component_id,
				'user_id' => $user_id,
				'datalist_id' => $datalist_id,
				'name' => $preset_name,
				'value' => $datapoints
			);
		
			$vce->set_datapoint ($attributes);

			echo json_encode(array('response' => 'success', 'message' => 'Configuration has been saved.','form' => 'edit-summary','action' => ''));
			return;
		}

		echo json_encode(array('response' => 'error', 'message' => 'Configuration could not be saved.','form' => 'save-datapoints','action' => ''));
		return;
	}


   
	public function assemble_user_info($vce) {
	

			// minimal user attributers
			$default_attributes = array(
				'user_id' => array(
					'title' => 'User Id',
					'sortable' => 1
				),
				'role_id' => array(
					'title' => 'Role Id',
					'sortable' => 1
				),
				'email' => array(
					'title' => 'Email',
					'required' => 1,
					'type' => 'text',
					'sortable' => 1
				)
			);
			
			// all other user attributes
			$user_attributes = json_decode($vce->site->user_attributes, true);
			// $vce->dump($user_attributes);
		
			$attributes = array_merge($default_attributes, $user_attributes);
			// $vce->dump($attributes);

			// look for filter_by values in the page object
			$filter_by = array();
			foreach ($vce->page as $key=>$value) {
				if (strpos($key, 'filter_by_') !== FALSE) {
					$filter_by[str_replace('filter_by_', '', $key)] = $value;
				}
			}
			
			// $vce->dump($filter_by);

			// filter by organization or group depending on role
			if ($vce->user->role_id == 5 || $vce->user->role_id == 6) {
				$filter_by['organization'] = $vce->user->organization;
			}
			if ($vce->user->role_id == 6) {
				$filter_by['group'] = $vce->user->group;
			}
			
			// manage_users_attributes_filter_by
			if (isset($vce->site->hooks['manage_users_attributes_filter_by'])) {
				foreach($vce->site->hooks['manage_users_attributes_filter_by'] as $hook) {
					$filter_by = call_user_func($hook, $filter_by, $vce);
				}
			}
			
			// get roles
			$roles = json_decode($vce->site->roles, true);
		
			// get roles in hierarchical order
			$roles_hierarchical = json_decode($vce->site->site_roles, true);

			// variables
			$sort_by = isset($vce->sort_by) ? $vce->sort_by : 'email';
			$sort_direction = isset($vce->sort_direction) ? $vce->sort_direction : 'ASC';
			$display_users = true;
	
	
			// create search in values
			$role_id_in = array();
			foreach ($roles_hierarchical as $roles_each) {
				foreach ($roles_each as $key => $value) {
					if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
						// add to role array
						$role_id_in[] = $key;
					}
				}
			}
			
	// $vce->dump($role_id_in);		
			// First we query the user table to get user_id and vector
				// towards the standard way
				// with role_id filter
				if (!empty($filter_by)) {
					$query = "SELECT * FROM " . TABLE_PREFIX . "users";
					$sort_by = null;
				} else if ($sort_by == 'user_id' || $sort_by == 'role_id') {
					// if user_id or role_id is the sort
					$query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',',$role_id_in) . ") ORDER BY $sort_by " . $sort_direction;
				} else {
					// the standard way
					$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id WHERE " . TABLE_PREFIX . "users.role_id IN (" . implode(',',$role_id_in) . ") AND " . TABLE_PREFIX . "users_meta.meta_key='" . $sort_by . "' ORDER BY " . TABLE_PREFIX . "users_meta.minutia " . $sort_direction ;
				}
			
			
			// if this is a report for notifications, use that user's list
			if(isset($vce->current_list_notifications)) {
				$current_list = $vce->current_list_notifications;
			} else {
				$current_list = $vce->db->get_data_object($query);
			}
			// this disregards the older uses of this method
			// it is only useful if older reports are not run
			return $current_list;
			// $vce->dump($current_list);

			// rekey data into array for user_id and vectors
			foreach ($current_list as $each_list) {
				$users_list[] = $each_list->user_id;
				$users[$each_list->user_id]['user_id'] = $each_list->user_id;
				$users[$each_list->user_id]['role_id'] = $each_list->role_id;
				$users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
				$vectors[$each_list->user_id] = $each_list->vector;
			}
	
			// Second we query the user_meta table for user_ids
			if (isset($users_list) ) {
				// get meta data for the list of user_ids
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode($users_list,',') . ")";
			} else {
				// get all meta data for all users because of filtering
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta";
			}
			
			$meta_data = $vce->db->get_data_object($query);
			
			// rekey data
			foreach ($meta_data as $each_meta_data) {
				// skip lookup
				if ($each_meta_data->meta_key == 'lookup') {
					continue;
				}
				// add
				$users[$each_meta_data->user_id][$each_meta_data->meta_key] = User::decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
			}
			// $vce->dump(count($users));



			//this is the solution to the time problem: find a way to get the max meta_value from each user's components without putting this in a foreach loop
			// then add it to the users array
			// get last activity of users
			$query = "SELECT a.component_id AS component_id, c.meta_value as type, b.meta_value as created_at, a.meta_value as user_id FROM " . TABLE_PREFIX . "components_meta a LEFT JOIN " . TABLE_PREFIX . "components_meta b on a.component_id = b.component_id LEFT JOIN " . TABLE_PREFIX . "components_meta c on a.component_id = c.component_id WHERE a.meta_key = 'created_by' AND  b.meta_key = 'created_at' AND c.meta_key = 'type' ORDER BY a.meta_value ASC";
			$created_at_data = $vce->db->get_data_object($query);
			$assets_array = array();
			foreach ($created_at_data as $this_created_at_data) {
				if ($this_created_at_data->user_id == 13 || $this_created_at_data->user_id == 1577 || $this_created_at_data->user_id ==  2921){
					continue;
				}
				$assets_array[$this_created_at_data->user_id]['assets'][$this_created_at_data->type][] = $this_created_at_data->created_at;
			}

			$query = "SELECT component_id, MAX(meta_value) AS last_created_at FROM " . TABLE_PREFIX . "components_meta WHERE meta_key ='created_at' GROUP BY component_id";
			$created_at_data = $vce->db->get_data_object($query);
			$created_at_array = array();
			foreach ($created_at_data as $this_created_at_data) {
				$created_at_array[$this_created_at_data->component_id] = $this_created_at_data->last_created_at;
			}

			$query = "SELECT component_id, meta_value AS user_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key ='created_by'";
			$created_by_data = $vce->db->get_data_object($query);

			$users_last_activities = array();
			foreach ($created_by_data as $this_created_by_data) {
				$users_last_activities[$this_created_by_data->user_id] = $created_at_array[$this_created_by_data->component_id];
			}
			foreach ($users_last_activities as $key=>$value) {
				if ($key == 13 || $key == 1577 || $key ==  2921){
					continue;
				}

				$assets_array[$key]['last_activity'] = $value;
			}

			
			// $date_based_assets = array();
			$date_based_assets[0] = array('raw'=>'raw', 'date'=>'date', 'Cycle'=>'Cycle', 'comment'=>'comment', 'asset'=>'asset', 'step'=>'step', 'library resource'=>'library resource','user_id'=>'user_id');

			foreach ($assets_array as $key=>$value) {
				foreach ($value['assets'] as $kk=>$vv) {
					foreach ($vv as $kkk=>$vvv) {
						$vvv = date('M/Y',$vvv);
						switch ($kk) {
							case 'alias':
								$library_resource = 1;
								break;
							case 'Media':
								$media = 1;
								break;
							case 'Pbc_step':
							case 'Pbc_review':
							case 'Pbc_focused_observation':
								$pbc_step = 1;
								break;
							case 'Comments':
								$comments = 1;
								break;
							case 'Pbccycles':
								$pbccycles = 1;
								break;
							default:
							   continue 3;
						}
						// if ($kk == 'alias') {
						// 	$library_resource = 1;
						// }
						// if ($kk == 'Media') {
						// 	$media = 1;
						// }
						// if ($kk == 'Pbc_step' || $kkk == 'Pbc_review' || $kkk == 'Pbc_focused_observation') {
						// 	$pbc_step = 1;
						// }
						// if ($kk == 'Comments') {
						// 	$comments = 1;
						// }
						// if ($kk == 'Pbccycles') {
						// 	$pbccycles = 1;
						// }
						
						$date_based_assets[] = array('raw'=>$kk, 'date'=>$vvv, 'Cycle'=>$pbccycles, 'comment'=>$comments, 'asset'=>$media, 'step'=>$pbc_step, 'library resource'=>$library_resource,'user_id'=>$key);
						unset($library_resource, $media, $pbc_step, $comments, $pbccycles);
					}
				}
			}
			// $vce->dump($query);
			// $vce->dump(count($created_at_array));
			// $vce->dump($assets_array);
			// $vce->dump($date_based_assets);
			// exit;

			// $vce->dump($users_last_activities);
			// return;

			// // create array with only the highest timestamp per user
			// $users_last_activities = array();
			// foreach ($meta_data as $this_meta_data) {
			// 	if (isset($this_meta_data->user_id)) {
			// 		if (isset($users_last_activities[$this_meta_data->user_id])){
			// 			if ($this_meta_data->last_activity > $users_last_activities[$this_meta_data->user_id]) {
			// 				$users_last_activities[$this_meta_data->user_id] = $this_meta_data->last_activity;
			// 			}
			// 		} else {
			// 			$users_last_activities[$this_meta_data->user_id] = $this_meta_data->last_activity;
			// 		}
			// 	}
			// }

			// return;


			// go through each user and add either the date-time value or 'none on record'
			foreach ($users as $key => $value) {
				if (isset($users_last_activities[$key])) {
					$created_at = $users_last_activities[$key];
					$created_at = date('Y-m-d H:i:s',$created_at);	
					$users[$key]['last_activity'] = $created_at;
				} else {
					$users[$key]['last_activity'] = 'none on record';
				}
			}
// return;
	
				// load hooks
				if (isset($vce->site->hooks['manage_users_attributes_list'])) {
					$user_attributes_list = array();
					foreach($vce->site->hooks['manage_users_attributes_list'] as $hook) {
						$user_attributes_list = call_user_func($hook, $user_attributes_list);
					}
					foreach ($user_attributes_list as $each_attribute_key=>$each_attribute_value) {
						if (!is_array($each_attribute_value)) {
							$attributes[$each_attribute_value] = array(
							'title' => $each_attribute_value,
							'sortable' => 1
							);
						} else {
							$attributes[$each_attribute_key] = $each_attribute_value;
						}
					}
				}
	
				// add notifications to attributes if this is the notifications report
				if($vce->notifications_report == true){
					foreach ($notification_titles as $key => $value) {
						$attr_key = strtolower(preg_replace('/ /', '_', $key));
						$attributes[$attr_key] = array(
							'type' => 'text',
							'title' => $key
						);
					}
				}
	
				// $vce->dump($attributes);

				// the array that goes into the csv
				$user_array = array();
				
				foreach ($attributes as $each_attribute_key=>$each_attribute_value) {
					// prepare the attributes
					// if conceal is set, as in the case of password, skip to next
					if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
						continue;
					}
					
					//create titles for the attributes which can be used as column headers
					$nice_attribute_title = ucwords(str_replace('_', ' ', $each_attribute_key));
				
					if ($each_attribute_key == $sort_by) {
						if ($sort_direction == 'ASC') {
							$sort_class = 'sort-icon sort-active sort-asc';
							$direction = 'DESC';
						} else {
							$sort_class = 'sort-icon sort-active sort-desc';
							$direction = 'ASC';
						}
						$th_class = 'current-sort';
					} else {
						$sort_class = 'sort-icon sort-inactive';
						$direction = 'ASC';
						$th_class = '';
					}
					$user_array[0][] = $nice_attribute_title;	
				}
				
				$user_array[0][] = 'Last Activity';

			//write the results into user array
			// $user_array = array(0=>array('id','email','first name','last name','role','organization','group'));
			// get role names
			$roles = json_decode($vce->site->roles, true);	

			// prepare for filtering of roles limited by hierarchy
			if (!empty($filter_by)) {
				$role_hierarchy = array();
				// create a lookup array from role_name to role_hierarchy
				foreach ($roles as $roles_key=>$roles_value) {
					$role_hierarchy[$roles_key] = $roles_value['role_hierarchy'];
				}
			}
// $vce->dump($filter_by);
// return;
			// loop through users, applying filters
			foreach ($users_list as $each_user) {
			
				// apply filters
				if (!empty($filter_by)) {
					// loop through filters and check if any user fields are a match
					foreach ($filter_by as $filter_key=>$filter_value) {
						// prevent roles hierarchy above this from displaying
						if ($role_hierarchy[$users[$each_user]['role_id']] < $role_hierarchy[$vce->user->role_id]) {
							continue 2;
						}

						if ($filter_key == "role_id") {
							// make title of role
							//	$filter_value = $roles[$filter_value]['role_name'];
							if ($users[$each_user]['role_id'] != $filter_value) {
								continue 2;
							}
							
							continue;
						}
						// check if $filter_value is an array
						if (is_array($filter_value)) {
							// check that meta_key exists for this user
							if (!isset($users[$each_user][$filter_key])) {
								continue 2;
							}
							// check if not in the array
							if (!in_array($users[$each_user][$filter_key],$filter_value)) {
								// continue foreach before this foreach
								continue 2;
							}
						} else {
							// doesn't match so continue
							if ($users[$each_user][$filter_key] != $filter_value) {
								// continue foreach before this foreach
								continue 2;	
							}
						}
					}
				}

				// create an array entry for each user which includes all that user's attributes
				$user_attributes = array();
				foreach ($attributes as $each_attribute_key=>$each_attribute_value) {
				
					// exception for role_id, change to role_name
					if ($each_attribute_key == 'role_id') {
						$each_attribute_key = 'role_name';
					}
				
					// if conceal is set, as in the case of password, skip to next
					if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
						continue;
					}
					// prevent error if not set
					$attribute_value = isset($users[$each_user][$each_attribute_key]) ? $users[$each_user][$each_attribute_key] : null;

					if (isset($each_attribute_value['datalist'])) {
						if (!isset($datalist_cache)) {
							// example: Organization is saved in a datalist, and we can go directly to the name
							// which is saved in the datalist_items_meta table if we know the item_id, which is 
							// saved in the users_meta table
							$query = "SELECT item_id, meta_value FROM vce_datalists_items_meta WHERE meta_key = 'name'";
							$datalist_item_lookup = $vce->db->get_data_object($query);
							$datalist_cache = array();
							foreach($datalist_item_lookup as $this_lookup) {
								$datalist_cache[$this_lookup->item_id] = $this_lookup->meta_value;
							}
						}
						
						if (isset($datalist_cache[$attribute_value])) {
							// user saved value
							$attribute_name = $datalist_cache[$attribute_value];

						} else {
							$attribute_name = NULL;
								// $datalist = $vce->get_datalist_items(array('item_id' => $attribute_value));
							
								// $attribute_name = isset($datalist['items'][$attribute_value]['name']) ? $datalist['items'][$attribute_value]['name'] : null;
								// // save it so we dont need to look up again
								// $datalist_cache[$attribute_value] = $attribute_name;
							
						} 
						
						$attribute_value = $attribute_name;
						
					}

					$user_attributes[] = $attribute_value;

				}

				$user_attributes[] = $users[$each_user]['last_activity'];

				
				$user_array[] = $user_attributes;
			}	
		$user_array = $date_based_assets;

		return $user_array;
	}
		
		public function array_to_csv_download($input) {
		
			global $vce;
			// $vce->log('array to csv');
			// set page attribute to which report to download. When the page reloads, the report will be compiled and a PHP file download header sent
			if (isset($input['report'])) {
				// $vce->site->add_attributes('report', true);
				// $vce->site->add_attributes($input['report'], true);
				if ($input['report'] == 'cycles_report') {
					$message = 'Cycles Report is Downloading (Please wait as the report is compiled.)';

					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					// $vce->site->add_attributes(component_start_date, $start_date);
					// $vce->site->add_attributes(component_end_date, $end_date);
				}
				if ($input['report'] == 'cycles_report2') {
					$message = 'Cycles Report2 is Downloading (Please wait as the report is compiled.)';

					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					// $vce->site->add_attributes(component_start_date, $start_date);
					// $vce->site->add_attributes(component_end_date, $end_date);
				}
				if ($input['report'] == 'component_data_report') {
					$message = 'Component Data Report is Downloading (Please wait as the report is compiled.)';
					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					// $vce->site->add_attributes(component_start_date, $start_date);
					// $vce->site->add_attributes(component_end_date, $end_date);
					// $vce->site->add_attributes(perspective, $input['perspective']);
					// $vce->site->add_attributes(direct_download, $input['direct_download']);
					// $vce->site->add_attributes(show_all_keys, $input['show_all_keys']);
				}
				if ($input['report'] == 'users_report') {
					$message = 'Users Report is Downloading (Please wait as the report is compiled.)';
				}
				if ($input['report'] == 'notifications_report') {
					$message = 'Notifications Report is Downloading (Please wait as the report is compiled.)';
				}
			}

			echo json_encode(array('response' => 'success','message' => $message,'form' => 'create','action' => ''));
		    return;
		}		
		
		
	 /**
		 *
		 */
		/**
		 *
		 */


		/**
		 * edit user
		 */
		public function edit($input) {

				// add attributes to page object for next page load using session
				global $vce;

				// $vce->site->add_attributes('user_id',$input['user_id']);

				// $vce->site->add_attributes('pagination_current',$input['pagination_current']);


				echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
				return;

		}

		/**
		 * update user
		 */
		public function update($input) {

				global $vce;

				// load hooks (this hook adds the updated user group to the list of users in that group in the datalist
				if (isset($vce->site->hooks['manage_user_update'])) {
						foreach($vce->site->hooks['manage_user_update'] as $hook) {
								call_user_func($hook, $input);
						}
				}

				$user_id = $input['user_id'];

				$query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
				$user_info = $vce->db->get_data_object($query);

				$role_id = $user_info[0]->role_id;
				$vector = $user_info[0]->vector;

				// has role_id been updated?
				if ($input['role_id'] != $role_id) {

						$update = array('role_id' => $input['role_id']);
						$update_where = array('user_id' => $user_id);
						$vce->db->update('users', $update, $update_where );

				}

				// clean up
				unset($input['type'],$input['procedure'],$input['role_id'],$input['user_id']);

				// delete old meta data
				foreach ($input as $key => $value) {

						// delete user meta from database
						$where = array('user_id' => $user_id, 'meta_key' => $key);
						$vce->db->delete('users_meta', $where);

				}

				// now add meta data

				$records = array();

				foreach ($input as $key => $value) {

						// encode user data
						$encrypted = $vce->user->encryption($value, $vector);

						$records[] = array(
						'user_id' => $user_id,
						'meta_key' => $key,
						'meta_value' => $encrypted,
						'minutia' => null
						);

				}

				$vce->db->insert('users_meta', $records);

				echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
				return;

		}


	
	/**
	 * Filter
	 */
	public function filter($input) {
	
		global $vce;
		
		foreach ($input as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				// $vce->site->add_attributes($key,$value);
			}
		}
		
		// $vce->site->add_attributes('pagination_current',$input['pagination_current']);
	
		echo json_encode(array('response' => 'success','message' =>'Filter'));
		return;
	
	}


	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false) {
		// $vce->dump($user->role_hierarchy);
		// $user->role_hierarchy = 3;
		switch ($user->role_hierarchy) {
			case 1:
			case 2:
				// get all users
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				// foreach ($all_users as $key=>$value) {
				// 	if (!isset($value->organization) || $value->organization != $vce->user->organization) {
				// 		unset($all_users[$key]);
				// 	}
				// }
				break;
			case 3:
			//get users in same organization
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {

					if (!isset($value->organization) || $value->organization != $vce->user->organization) {
						unset($all_users[$key]);
					}
				}
				break;
			case 4:
			// get users in same group
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {
					if (!isset($value->group) || $value->group != $vce->user->group) {
						unset($all_users[$key]);
					}
				}
				break;
			case 5:
				$all_users = array();
				break;
			case 6:
				$all_users = array();
				break;
			default:
				$all_users = array();
		}

		// return user object array
		if ($get_user_metadata == true) {
			return $all_users;
		}

		// create comma-delineated list of users
		$user_list = array();
		foreach ($all_users as $this_user) {
			$user_list[] = $this_user->user_id;
		}
		if (empty($user_list)) {
			$user_list[] = $user->user_id;
		}
		$user_list = implode(',', $user_list);

		return $user_list;
	}




/*
 add config info for this component
*/
public function component_configuration() {
	global $vce;
	$content = NULL;

	$encrypted_data_input = array(
	'type' => 'checkbox',
	'name' => 'show_encrypted_data',
	'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['show_encrypted_data']) && $this->configuration['show_encrypted_data'] == 'on') ? true :  false),
		'label' => 'Show Encrypted Data?'
		)
	);
	$content .= $vce->content->create_input($encrypted_data_input,'Show Encrypted Data');

	$configuration_input = array(
		'type' => 'checkbox',
		'name' => 'show_configuration_button',
		'options' => array(
			'value' => 'on',
			'selected' => ((isset($this->configuration['show_configuration_button']) && $this->configuration['show_configuration_button'] == 'on') ? true :  false),
			'label' => 'Show Configuration Button? (allows user to choose which reports to see)'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Show Configuration');

	$configuration_input = array(
		'type' => 'checkbox',
		'name' => 'show_summary',
		'options' => array(
			'value' => 'on',
			'selected' => ((isset($this->configuration['show_summary']) && $this->configuration['show_summary'] == 'on') ? true :  false),
			'label' => 'Show Summary Globally?'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Show Summary');

	$configuration_input = array(
		'type' => 'checkbox',
		'name' => 'show_data_report',
		'options' => array(
			'value' => 'on',
			'selected' => ((isset($this->configuration['show_data_report']) && $this->configuration['show_data_report'] == 'on') ? true :  false),
			'label' => 'Show Data Report Globally?'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Show Data Report');

	$configuration_input = array(
		'type' => 'checkbox',
		'name' => 'show_analytics_report',
		'options' => array(
			'value' => 'on',
			'selected' => ((isset($this->configuration['show_analytics_report']) && $this->configuration['show_analytics_report'] == 'on') ? true :  false),
			'label' => 'Show Analytics Report Globally?'
			)
		);



	$content .= $vce->content->create_input($configuration_input,'Show Configuration');


	$elements = null;
		
		
	$elements .= '<div>These roles have jurisdiction over all users:</div>';
	
	$input = array(
	'type' => 'checkbox',
	'name' => 'see_all_users',
	'selected' => (isset($this->configuration['see_all_users']) ? explode('|', $this->configuration['see_all_users']) : null),
	'flags' => array(
	'label_tag_wrap' => true
	)
	);
	
	// add site roles as options
	foreach (json_decode($vce->site->site_roles) as $each_role) {
		foreach ($each_role as $key=>$value) {
			$input['options'][] = array(
				'value' => $key,
				'label' => $value->role_name
			);
		}
	}

	
	
	$elements .= $vce->content->input_element($input);
	
	$elements .= '<br><div>The unchecked roles see only users in the same Coaching Partnerships as themselves.</div>';

	$content .= $elements;

	return $content;
}

		




	/**
	 * fileds to display when this is created
	 */
	public function recipe_fields($recipe) {
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$url = isset($recipe['url']) ? $recipe['url'] : null;
	
$elements = <<<EOF
<input type="hidden" name="auto_create" value="forward">
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
<label>
<input type="text" name="url" value="$url" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">URL</div>
<div class="label-error">Enter a URL</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}