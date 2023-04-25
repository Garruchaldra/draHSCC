<?php
/**
 * Nestor Reports table page
 *
 * @category   Admin
 * @package    Nestor_reports_table
 * @author     Dayton <daytonra@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */


class Nestor_reports_table extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Reports Table',
			'description' => 'Creates interpolated summaries tables for download',
			'category' => 'nestor_reports'
		);
	}

	public function to_do($each_component, $vce) {
		/*

Number of cycles created
Number of cycles marked as complete
Number of Action Plan Steps created
Number of Action Plan Steps marked as complete
Total number and type of media uploads to action plan steps
Number of focused observations created
Total number and type of media uploads to focused observations
Total number of users
Number of coaches
Number of coachees
Number of organization administrators
Number of group administrators
Total number of organizations (this would really only need to be for site admins)
		*/
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {

		$content_hook = array(
			'site_hook_initiation' => 'Nestor_reports_table::require_once_nestor_reports_tabletype',
			'page_requested_url' => 'Nestor_reports_table::add_reports_methods',
		);

		return $content_hook;
	}


	/**
	 * loads the reportz_tableType parent class before the children classes are loaded
	 */
	public static function require_once_nestor_reports_tabletype($site) {
	
		// path to report tablestype.php
		require_once(dirname(__DIR__, 1) . '/nestor_reports_tabletype/nestor_reports_tabletype.php');

	}

	public static function add_reports_methods($vce) {

		global $vce;

		$methods = new stdClass();
		$custom_methods = json_decode($vce->site->enabled_nestor_reports_tabletype, true);

		if (isset($custom_methods)) {
			foreach ($custom_methods as $name=>$path) {

					if (file_exists(BASEPATH . $path)) {
					require_once(BASEPATH . $path);
					$method_class = $name;
					
					$class_methods = get_class_methods($method_class);
					$methods->$name = array('class_name' => $method_class, 'methods'=>array());
					$methods->$name['path'] = $path; 
					foreach ($class_methods as $method_name) {
						
						if (substr($method_name, 0, 7) == 'method_') {
							$display_name = str_replace('method_', '', $method_name);
							$methods->$name['methods'][] = array(
								'method_name'=>$method_name,
								'display_name'=>$display_name
							);
						}
					}
				}
			}
			$vce->site->methods = $methods;


		}

		// $vce->site->methods = json_decode('{"nestor_report_methods_hscc":{"class_name":"nestor_report_methods_hscc","methods":[{"method_name":"summary_total_number_of_users","display_name":"total_number_of_users"},{"method_name":"summary_total_number_of_comments","display_name":"total_number_of_comments"},{"method_name":"summary_total_number_of_commenting_users","display_name":"total_number_of_commenting_users"},{"method_name":"summary_total_number_of_videos_uploaded","display_name":"total_number_of_videos_uploaded"},{"method_name":"summary_total_number_of_users_uploading_videos","display_name":"total_number_of_users_uploading_videos"},{"method_name":"summary_total_number_of_cycles_created","display_name":"total_number_of_cycles_created"},{"method_name":"summary_total_number_of_cycles_marked_as_complete","display_name":"total_number_of_cycles_marked_as_complete"},{"method_name":"summary_total_focused_observations_created","display_name":"total_focused_observations_created"},{"method_name":"summary_total_action_plan_steps_created","display_name":"total_action_plan_steps_created"},{"method_name":"summary_total_action_plan_steps_marked_as_complete","display_name":"total_action_plan_steps_marked_as_complete"},{"method_name":"summary_total_number_of_users_creating_cycles","display_name":"total_number_of_users_creating_cycles"},{"method_name":"summary_role_breakdown","display_name":"role_breakdown"},{"method_name":"summary_total_number_and_type_of_media_uploads","display_name":"total_number_and_type_of_media_uploads"},{"method_name":"summary_total_number_and_type_of_library_resources_used","display_name":"total_number_and_type_of_library_resources_used"}]}}', true);
	}





		/**
		 * as_content contains all forms which spawn reports. Since these reports result in downloading a .csv file,
		 * I have used as_content as the method for assembling the data as well. This can be farmed out to individual methods, but must
		 * be called from as_content to create the headers necessary for downloads.
		 */



public function as_content($each_component, $vce) {

	$content = NULL;

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter jquery-ui');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		// $vce->site->add_style(dirname(__FILE__) . '/css/progress_bar.css');
		$component_id = $each_component->component_id;
		$each_component_url = $each_component->url;
		$each_component_created_at = $each_component->created_at;

		// this loading gif will appear until the page is done loading, and then be replaced by download submit button <span class="download-button"><input type="submit" value="Download Report: $query_title"></span>
		$download_loading_gif = $vce->site->site_url.'/vce-content/components/nestor_reports/img/loading_38.gif';

		
		// for date picker
		$id_key = uniqid();
			
		// 
		$user_name = $vce->user->first_name . ' ' . $vce->user->last_name;


		// get preset datapoints as array
		$logged_in_user_id = $vce->user->user_id;



				$data_report_content_input = array(
					'component_id'=>$component_id,
					'each_component_created_at'=>$each_component_created_at,
					'each_component_url'=>$each_component_url,
				);
			

			
				$data_report_content = $this->data_report_content($each_component, $data_report_content_input);
				$content .= $vce->content->accordion('Data Report', $data_report_content, TRUE);

				if ($vce->user->role_hierarchy == 1) {
					$manage_queries_content = $this->manage_queries_content($each_component);
					$content .= $vce->content->accordion('Manage Queries', $manage_queries_content, False);
				}

		$content .= <<<EOF
</p>
EOF;


// hidden content for graph display
$content .= <<<EOF
<div id="graph-modal" class="modal" style="display:none">
	<div class="modal-content">
		<div class="close">&times;</div>
		<div class="graph-modal__arrows-container">
			<div class="progress-arrows__container progress-arrows__show">
EOF;

$content .= <<<EOF
 				<span id="the-graph"></span>
			</div>
		</div>
	
		<div class="tips-text">
		</div>
			<button type="submit" class="btn button__primary got-it">Ok, got it!</button>
	</div>
</div>
</form>
EOF;

		$vce->content->add('main', $content);

}

		public function data_report_content($each_component, $data_report_content_input = array()) {

			global $vce;
			extract($data_report_content_input);

		// Component Report (main report form)

		// This is the list to be used in a query which shows what data (component meta_keys) to show
		// $order_by_list = "('type','action_plan_goal','comments','created_at','created_by','cycle_participants','group','media_type','organization','pbccycle_begins','pbccycle_review','pbccycle_status','recipe_name','start_date')";

		//create form
		$component_data_content = '';


		
		// choose category to show
		// here is a list of all the meta_keys we are filtering out:
		// ('alias_id','ap_id','ap_step_id','aps_assignee','assignment_category','content_create','content_delete','content_edit','date','description','duration','email','end_date','first_name','fo_id','focus','goal_achievement_evidence','guid','last_name','link','list_order','mediaAmp_id','name','not_saved_directly_aps_assignee','not_saved_directly_cycle_participants','not_saved_directly_observed','not_saved_directly_observers','observed','observers','original_id','original_taxonomy','originator','originator_id','password','path','pbc_cycles_id','pbccycle_name','preparation_notes','progress','published','recipe','redirect_url','review_sibling_id','rf_id','role_access','role_id','step_comments','sub_roles','taxonomy','taxonomy2','template','text','thumbnail_url','timestamp','title','type','updated_at','user_access','user_id','user_ids_aps_assignee','user_ids_cycle_participants','user_ids_observed','user_ids_observers','user_oldids_aps_assignee','user_oldids_cycle_participants','user_oldids_observed','user_oldids_observers')
		//  here is what we are keeping:
		// ('action_plan_goal','comments','created_at','created_by','cycle_participants','group','media_type','organization','pbccycle_begins','pbccycle_review','pbccycle_status','recipe_name','start_date')

		// $query = "SELECT DISTINCT meta_key AS data_category FROM " . TABLE_PREFIX . "components_meta WHERE meta_key IN $order_by_list";
		// $data = $vce->db->get_data_object($query);


			$data_categories = array(
				array(
					'name' => 'Select an Option',
					'value' => '',
					// 'selected' => $not_selected,
					'disabled' => true,
					'selected' => false,
				),
				array(
					'name' => 'year',
					'value' => 'year',
					'selected' => false,
				),
				array(
					'name' => 'month',
					'value' => 'month',
					'selected' => true,
				),
				array(
					'name' => 'week',
					'value' => 'week',
					'selected' => false,
				),
				array(
					'name' => 'day',
					'value' => 'day',
					'selected' => false,
				)
			);



			if ($vce->user->role_hierarchy < 3) {
				$show_all_keys_checkbox = '<input type="checkbox" name="show_all_keys" id="show-all-keys-checkbox" class="config-checkbox" autocomplete="off">&nbsp;Show All Keys?';
			}	

			// perspective input
			$input = array(
				'type' => 'select',
				'name' => 'sum_interval',
				'required' => 'true',
				'class' => 'perspective key-toggle',
				'id' => 'data-category-component-data',
				'options' => $data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
			$categories_component_data = $vce->content->create_input($input,"Sum interval: ");

			// Subject input
			// this needs to be updated to reflect other than the meta_keys

			$report_subject_data_categories = array(
				array(
					'name' => 'Select an Option',
					'value' => '',
					// 'selected' => $not_selected,
					'disabled' => true,
					'selected' => true,
				)
			);
			// $vce->dump($vce->site->methods);
			foreach ($vce->site->methods as $k=>$v) {
				$reports_class = $k;
				if (isset ($v['methods'])) {
					foreach ($v['methods'] as $kk=>$vv) {
						$display_name = ucwords(str_replace('_', ' ', $vv['display_name']));
						$report_subject_data_categories[] = array(
							'name' => $display_name,
							'value' => $reports_class . '|' . $vv['method_name'],
							'selected' => false
						);


						// $vce->dump($vv['method_name']);
						// $vce->dump($vv['display_name']);
					}
				}
			}

			// $report_subject_data_categories = array(
			// 	array(
			// 		'name' => 'Select an Option',
			// 		'value' => '',
			// 		// 'selected' => $not_selected,
			// 		'disabled' => true,
			// 		'selected' => true,
			// 	),
			// 	array(
			// 		'name' => 'Number of New Users Created',
			// 		'value' => 'users_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Cycles Created',
			// 		'value' => 'cycles_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Cycles Marked as Complete',
			// 		'value' => 'cycles_completed',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Videos Uploaded',
			// 		'value' => 'videos_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of PDF Files Uploaded',
			// 		'value' => 'pdf_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Image Files Uploaded',
			// 		'value' => 'images_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Media Files (of any type) Uploaded',
			// 		'value' => 'media_created',
			// 		'selected' => false,
			// 	),
			// 	array(
			// 		'name' => 'Number of Comments Created',
			// 		'value' => 'comments_created',
			// 		'selected' => false,
			// 	)
			// );


			$input = array(
				'type' => 'select',
				'name' => 'report_subject',
				'required' => 'true',
				'class' => 'report-subject key-toggle',
				'id' => 'report-subject-data',
				'options' => $report_subject_data_categories,
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
					'multiple' => TRUE,
				)
			);
			$report_subject = $vce->content->create_input($input, "Report to Show: ");

			$configuration_input = array(
				'type' => 'checkbox',
				'name' => 'horizontal',
				'options' => array(
					'value' => 'on',
					'selected' => false,
					'label' => 'Horizontal vs vertical list?'
					)
				);
			$horizontal = $vce->content->create_input($configuration_input,'Horizontal');


			$input = array(
                'type' => 'checkbox',
                'name' => 'filter_by_user',
                'selected' => explode('|', '1|2|3|4|5|6|7'),
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
				$input['options'][] = array(
					'value' => 'test_users',
					'label' => 'Test Users'
				);


                $filter_by_user = $vce->content->create_input($input, 'Filter by user roles (default is all roles).');


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
			
		


	
		$dossier_for_component_data_report = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports_table','procedure' => 'component_data_report', 'report'=>'component_data_report', 'user_id' => $vce->user->user_id, 'component_id' => $component_id, 'created_at' => $each_component_created_at, 'url' => $each_component_url)),$vce->user->session_vector);


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
			<input schema="json" id="subjects_to_show_list" type="hidden" class="subjects_to_show_list-save" name="subjects_to_show_list" value="">
			
			$report_subject
			$categories_component_data
			$filter_by_user


EOF;

			$component_data_content .= <<<EOF
			$start_date_component_data
			$end_date_component_data

EOF;


			
			$component_data_content .= <<<EOF
			<button id="data-download-button" class="button__primary data-download-button" type="submit">View Report</button>
			</form>

		
EOF;


		
		// this is where the settings can be fine tuned for the data section
		$config_section = $vce->content->accordion('Data Report Configuration', $component_data_content, TRUE);


			$data_report_content = $config_section;

			$table_contents = NULL;
			$table_contents .= <<<EOF
			<div id = "progressbar-component-data" class="progressbar">
			<div class = "progress-label" >Loading...</div>
			</div>


			<div class="table-container all-data-table">
				<table id="total-table" class="tablesorter" border=1> 
				<thead id="total-table-head">
				</thead> 
				<tbody id="total-table-body">
				</tbody>
			</table> 
			<button id="download-report-button" class="button__primary" type="submit">Copy This Report to Clipboard</button>
			<button id="view-graph-button" class="button__primary view-graph-button" type="submit">View Graph</button><br>
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

		/**
		 * The Admin can add, view, edit and delete queries which spawn reports.
		**/

		public function manage_queries_content($each_component) {

			global $vce;
return false;
			$content = NULL;

			$dossier_for_manage_queries = $vce->user->encryption(json_encode(array('type' => 'Nestor_reports_table','procedure' => 'manage_queries_edit', 'user_id' => $vce->user->user_id, 'component_id' => $component_id, 'url' => $each_component_url)),$vce->user->session_vector);


			$content .= <<<EOF
			<h2>Edit Queries</h2>

<form id="data-report-form" class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_manage_queries">

<textarea name="main_query" rows="20" cols="100">
SELECT a.*, b.component_id FROM vce_components AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id AND b.meta_key = 'created_at';
</textarea>
  <br><br>
  <input type="submit" value="Submit">
</form>

EOF;
			return $content;

		}



	/**
	 * The Admin can add, view, edit and delete queries which spawn reports.
	**/

	public function manage_queries_edit($input) {

		global $vce;
		$vce->plog($input['main_query'], 0);
		$main_query = html_entity_decode($input['main_query'], ENT_QUOTES);
		$main_query = base64_encode($main_query);
		$vce->plog($main_query, 1);
		$main_query = base64_decode($main_query);


		// $a = "SELECT a.*, b.component_id FROM vce_components AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id AND b.meta_key = 'created_at';";

		$vce->plog($main_query, 1);

// test for endpoint in ohscc
	// 	$input = Array(
    // 'type' => 'Pbc_step',
    // 'parent_id' => 35577,
    // 'sequence' => 101,
    // 'pbc_cycles_id' => 35576,
    // 'comments' => '',
    // 'title' => 'ActionStepPost',
    // 'pbccycle_begins' => '9/15/2021',
    // 'user_ids_aps_assignee' => '{\&quot;dl\&quot;:\&quot;aps_asignee\&quot;,\&quot;dl_id\&quot;:\&quot;\&quot;,\&quot;dl_name\&quot;:\&quot;Action Plan Step Assignee\&quot;,\&quot;user_ids\&quot;:\&quot;13|2921\&quot;}'
	// 	);
	// 	$vce->plog($input, 0);
	// 	//save multi-select userlist to component	
	// 	foreach($input as $key => $value) {
	// 		if (strpos($key, 'user_ids') !== false) {
	// 			$vce->plog('line 592 '.$value , 1);
	// 			$value = stripslashes($value);
	// 			$vce->plog('line 594 '.$value , 1);
	// 			$value = html_entity_decode($value, ENT_QUOTES);
	// 			$input[$key] = $value;
	// 			$dl_input = json_decode($value);
	// 			$dl_user_ids = trim($dl_input->user_ids, '|');
	// 			$input[$dl_input->dl] = '|' . $dl_user_ids . '|';
	// 		}
	// 	}
	// 	$vce->plog($input, 1);

		return TRUE;
	}
	


/**
 * component_data_report collects all information about the chosen type of component
 * The data is converted from vertical key-value pairs which are joined by component_id to a horizontal 1-dim array.
 * Each array member contains all the data available for each component
 * Then the contents of the table are created so they can be inserted into the accordion showing the results of each query. ()
 **/



public function component_data_report($input) {

	global $vce;
	// $vce->log($input); 
	// exit;

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
	$input['users_filter'] = implode('|', $input['users_filter']);
	// $vce->log($input['users_filter']);
	

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
	//disable encryption ( may need it later)
	$encrypt_this = array();


		// get array from the multi-select in the form which was sent as JSON hidden input
		// after being converted to that by the javascript from the form page
		$subjects_to_show_list = json_decode($input['subjects_to_show_list'], true);
		$subjects_to_show_list = explode("^", $subjects_to_show_list['list_of_values']);


// $vce->log($subjects_to_show_list);

	// report_subject is the name of the hard-coded report to run
	if (isset($input['report_subject'])) {
		// pagination of results
		$current_page = (isset($input['current_page']))? $input['current_page'] : 0;
		$input['component_start_date'] = strtotime($input['component_start_date']);
		$input['component_end_date'] = strtotime($input['component_end_date']);
		// send out head of table if not sent already
		$head_has_been_sent = ($input['head_has_been_sent'] == TRUE)? $input['head_has_been_sent'] : FALSE;
		$page_size = 2592000; // this is roughly one month (in seconds)
		$page_size = $page_size * 4; 
		$number_of_pages = ($input['component_end_date'] - $input['component_start_date']) / $page_size;


		/**
		 * cycle through all selected report methods, get their results, and put the results into
		 * the display array.
		 */
		$component_data = array();
		$total_in_timerange = array();
		foreach ($subjects_to_show_list as $report_subject) {
			// $vce->log($report_subject);
			/** beginning of single method */
			$report_subject = explode('|', $report_subject);
			$report_class = $report_subject[0];
			$report_method = $report_subject[1];
			// $vce->log($report_subject);
			$single_method_data = array();
			// this calls the query to get data
			// $method_output = $this->$report_method($input);
			// get list of all minions
			$custom_methods = json_decode($vce->site->enabled_nestor_reports_tabletype, true);
			// get path from specific minion from input, and require that minion
			$path = $custom_methods[$report_class];
			if (file_exists(BASEPATH .  $path)) {
				require_once(BASEPATH . $path);
			}
			// instantiate the class needed for the requested method
			if (class_exists($report_class)) {
				$report_class = new $report_class;
				$method_output = NULL;
				// run the method
				$method_output = $report_class->$report_method($input);
				unset($report_class);
			}

			// get the total reported by the method
			$method_display_name = str_replace('method_', '', $report_method);
			$method_display_name = ucwords(str_replace('_', ' ', $method_display_name));
			$total_in_timerange[$method_display_name] = $method_output['total_in_timespan'];

			// get just the array which will be used to build the table
			$single_method_data = $method_output['interpolated_array'];
			/** end of single method */

			// add to multi-method array output
			foreach ($single_method_data as $k=>$v) {
				foreach ($v as $kk=>$vv) {
						$component_data[$kk][] = $vv;
				}
			}
		}

		/**
		 * // single method output:
		 * [{"Week":"Number of Videos Uploaded "},{"2020-36":8},
		 * 
		 * // convert to array for multi method output:
		 * $component_data = array(
		 * 	
		 * 		'Week' = array('Number of Videos Uploaded', 'Number of X')
		 * 		"2020-36" = array(8 , 32)
		 * 		etc.
		 * 	
		 * )
		 */

		// $vce->log($total_in_timerange);
// exit;
		$total_in_timespan = <<<EOF
		<tr>
			<th>Total in specified timerange:</th>
		</tr>
		<tr>
EOF;
		foreach ($total_in_timerange as $k=>$v) {
			// foreach ($v as $kk=>$vv) {
				$total_in_timespan .= <<<EOF
					<th>$k</th>
EOF;
		// }
	}
		$total_in_timespan .= <<<EOF
		</tr>
		<tr>
EOF;

foreach ($total_in_timerange as $k=>$v) {
	// foreach ($v as $kk=>$vv) {
		$total_in_timespan .= <<<EOF
			<td>$v</td>
EOF;
// }
}
$total_in_timespan .= <<<EOF
</tr>
EOF;

		// output order by for date, type, role
		// sum by order by
		// $perspective = 'title';
		$perspective = $input['sum_interval'];
		$subject = $input['report_subject'];

		// $dimensions = array_keys($final_array);
		// this gives the key-value (vertical) information organized as a horizontal, one-dim array
		// for example: each comment is one component, so each meta_key becomes a column and each corresponding meta_value is put into that column
		$one_dim_array_prep = array();  // this is the main array
		$order_by = 'Order By: '.ucfirst($sum_interval);




	//if the direct_download checkbox is not checked, output as table
	if (!isset($vce->direct_download)) {
			//save contents of table into separate string
			$table_contents = NULL;
			$table_section = NULL;
			// $vce->log($display);

			// $vce->log($headers);
			// exit;
			// this is to keep the JS resubmitting the form; there must be a value for $table_section
			$table_section = 'body';
			$i = 0;
			foreach($component_data as $k=>$v) {
				if ($i == 0 && $head_has_been_sent == FALSE) {
					$head_has_been_sent = TRUE;
					$table_section = 'head';
					$i++;
					// $vce->log($v);
					$table_contents .= <<<EOF
<tr>
EOF;

					$table_contents .= <<<EOF
<th>$k</th>
EOF;
					foreach($v as $kk=>$vv) {
						if (is_array($vv)) {
							foreach ($vv as $kk2=>$vv2) {
								// $out = print_r($v3, true);
								$table_contents .= <<<EOF
<th>$kk2</th>
EOF;
							}
					} else {
						$table_contents .= <<<EOF
<th>$vv</th>
EOF;
					}

					}
					$table_contents .= <<<EOF
</tr>
EOF;


				} elseif ($k != 0) {
			$table_section = 'body';
			$table_contents .= <<<EOF
<tr>
EOF;

		$table_contents .= <<<EOF
<td>$k</td>
EOF;
				foreach($v as $k2=>$v2) {
					if (is_array($v2)) {
						foreach ($v2[0] as $k3=>$v3) {
							// $out = print_r($v3, true);
							$table_contents .= <<<EOF
<td>$v3</td>
EOF;
						}
				} else {
					$table_contents .= <<<EOF
<td>$v2</td>
EOF;
				}
					}

				$table_contents .= <<<EOF
				</tr>
EOF;
			}
		}
		// $vce->log($table_contents); exit;
		if ($current_page == 'all_done' ) {
			echo json_encode(array('response' => 'success','current_page' => $current_page, 'table_section' => 'done', 'message' => '','form' => 'report','action' => ''));
			return;
		}



		// //Define some data
		// $graph_data2 = array(
		// array('a',3,4,2),
		// array('b',5,'',1), // here we have a missing data point, that's ok
		// array('c',7,2,6),
		// array('d',8,1,4),
		// array('e',2,4,6),
		// array('f',6,4,5),
		// array('g',7,2,3)
		// );

		$graph_data = array();
		$i = 0;
		foreach ($component_data as $k=>$v){
			if ($i == 0) {
				$y_title = 'Amount';
				$x_title = $k;
			} else {
				// $amounts = implode(',', $v);
				array_unshift($v, $k);
				$graph_data[] = $v;
			}
			$i++;
		}


		// create graph image using phplot
		require_once(dirname(__FILE__) . '/phplot-6.2.0/phplot.php');

		//Define the object
		$graph_width = count($graph_data) * 50;
		$graph_width = ($graph_width < 500) ? 500 : $graph_width;
		$plot = new PHPlot($graph_width, 400);
		$plot->SetPrintImage(False); 
		// $plot->SetDefaultTTFont('liberation/LiberationSans-Regular.ttf');

		//Define some data
		$plot->SetYTitle($y_title);
		$plot->SetXTitle($x_title);
		$plot->SetTitle('Nestor Table Report');

		# Build a legend from our data array.
		# Each call to SetLegend makes one line as "label: value".
		$legend_data = array();
		foreach ($total_in_timerange as $k=>$v) {
			$legend_data[] = array($k, $v);
		}
		foreach ($legend_data as $row) {
			$plot->SetLegend(implode(': ', $row));
		}
		# Place the legend in the upper left corner:
		$legend_x = ($graph_width < 1200) ? $graph_width - 200 : 60;
		$plot->SetLegendPixels($legend_x , 5);

		$plot->SetDataValues($graph_data);

		//Draw it
		$plot->DrawGraph(); // Make the plot but do not output it
		$the_graph = "<img src=\"" . $plot->EncodeImage() . "\"><br>";



		echo json_encode(array('response' => 'success','current_page' => "all_done", 'number_of_pages' => $number_of_pages, 'table_section' => $table_section, 'head_has_been_sent' => $head_has_been_sent, 'message' => $table_contents, 'the_graph' => $the_graph, 'total_in_timespan' => $total_in_timespan, 'form' => 'report','action' => ''));
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






		/**
		 * admin configuration
		 */
		public function admin_configuration($input) {

			global $vce;

			$vce->site->add_attributes('user_id',$input['user_id']);

			$vce->site->add_attributes('pagination_current',$input['pagination_current']);


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
			return;
			global $vce;

			extract($input);

			// get the saved summary list
			// it is saved as a json encoded array where the key is 'list_of_values' and the value is a pipeline delineated list of names of existing summary_<name> methods
			$data_to_show = $vce->read_datapoint(array('name'=>$datalist,'datalist_id'=>$datalist_id));
			$data_to_show = base64_decode($data_to_show);
			$data_to_show = json_decode($data_to_show, TRUE);
			$data_to_show = explode('|', $data_to_show['list_of_values']);
			// $vce->dump($data_to_show);

			if (!isset($start_date) || !isset($end_date)) {
				$start_date = '01/01/2018';
				$end_date = '01/01/2020';
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
		
		// $dossier_for_field = (isset($v['dossier']))? $v['dossier'] : NULL;
		$method_name = $v['name'];
		$summary_class = new $v['summary_class'];
		$data = NULL;
		$input = array('start_date'=>$start_date, 'end_date'=>$end_date);
		if(isset($start_date) && isset($end_date)) {
			$data = $summary_class->$method_name($input);
			unset($summary_class);
		}


		$summary_table_content .= <<<EOF
	<td class="">
			<div class="loading-di">not array</div>
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
		// $vce->dump($user_array);
		// exit;
		return $user_array;
	}
		
		public function array_to_csv_download($input) {
		
			global $vce;
			// $vce->log('array to csv');
			// set page attribute to which report to download. When the page reloads, the report will be compiled and a PHP file download header sent
			if (isset($input['report'])) {
				$vce->site->add_attributes('report', true);
				$vce->site->add_attributes($input['report'], true);
				if ($input['report'] == 'cycles_report') {
					$message = 'Cycles Report is Downloading (Please wait as the report is compiled.)';

					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					$vce->site->add_attributes(component_start_date, $start_date);
					$vce->site->add_attributes(component_end_date, $end_date);
				}
				if ($input['report'] == 'cycles_report2') {
					$message = 'Cycles Report2 is Downloading (Please wait as the report is compiled.)';

					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					$vce->site->add_attributes(component_start_date, $start_date);
					$vce->site->add_attributes(component_end_date, $end_date);
				}
				if ($input['report'] == 'component_data_report') {
					$message = 'Component Data Report is Downloading (Please wait as the report is compiled.)';
					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					$vce->site->add_attributes(component_start_date, $start_date);
					$vce->site->add_attributes(component_end_date, $end_date);
					$vce->site->add_attributes(perspective, $input['perspective']);
					$vce->site->add_attributes(direct_download, $input['direct_download']);
					$vce->site->add_attributes(show_all_keys, $input['show_all_keys']);
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

				$vce->site->add_attributes('user_id',$input['user_id']);

				$vce->site->add_attributes('pagination_current',$input['pagination_current']);


				echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
				return;

		}

		


	
	/**
	 * Filter
	 */
	public function filter($input) {
	
		global $vce;
		
		foreach ($input as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$vce->site->add_attributes($key,$value);
			}
		}
		
		$vce->site->add_attributes('pagination_current',$input['pagination_current']);
	
		echo json_encode(array('response' => 'success','message' =>'Filter'));
		return;
	
	}


	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false,  $users_filter = NULL) {
		// $vce->dump($user->role_hierarchy);
		// $user->role_hierarchy = 3;
		if (isset($users_filter)) {
			$users_info = array('roles' => $users_filter);
		} else {
			$users_info = array('roles' => 'all');
		}
		switch ($user->role_hierarchy) {
			case 1:
			case 2:
				// get all users
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
			// no other users
			case 5:
				$all_users = array();
				break;
			// no other users
			case 6:
				$all_users = array();
				break;
			// no other users
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

	public function is_valid_timestamp($timestamp) {
		// global $vce;
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}


	public function time_ranges($start_date, $end_date, $sum_interval) {

		$start_date_test = $this->is_valid_timestamp("$start_date");

		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		} else {
			$start_date = $start_date;
		}

		$end_date_test = $this->is_valid_timestamp("$end_date");
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		} else {
			$end_date = $end_date;
		}

			$start_datetime = new DateTime(date('Y-m-d', $start_date));
			$end_datetime = new DateTime(date('Y-m-d', $end_date));
			switch ($sum_interval) {
				case 'year':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = $difference->y;
					$interval = 'P1Y';
					$format = 'Y';
					break;
				case 'month':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = ($difference->y * 12) + $difference->m;
					$interval = 'P1M';
					$format = 'Y-m';
					break;
				case 'week':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = round($difference->days / 7);
					$interval = 'P1W';
					$format = 'Y-W';
					break;
				case 'day':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = $difference->days;
					$interval = 'P1D';
					$format = 'Y-m-d';
					break;
			}

			$working_date = $start_datetime;
			$time_info_output = array();
			for ($i=0; $i<=$number_of_dates; $i++){
				$time_info_output[$sum_interval][$working_date->format($format)] = array();
				$wdf = $working_date->format($format);
				$edf = $end_datetime->format($format);
				
				if ($i==$number_of_dates && $wdf != $edf) {
					$working_date->add(new DateInterval($interval));
					$time_info_output[$sum_interval][$working_date->format($format)] = array();
				} else {
					$working_date->add(new DateInterval($interval));
				}
			}

			$output = array(
				'start_datetime' => $start_datetime,
				'end_datetime' => $end_datetime,
				'format' => $format,
				'time_info_output' => $time_info_output,
				'start_date' => $start_date,
				'end_date' => $end_date
			);
			return $output;
	}


	public function users_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);

		$total_in_timespan = array();
		foreach ($users_in_jurisdiction AS $k => $v) {
			if ($v->created_at < $start_date || $v->created_at > $end_date ) {
				unset($users_in_jurisdiction[$k]);
				continue;
			}
			if(isset($v->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $v->created_at));
				$formatted_date = $component_datetime->format($format);
				$total_in_timespan[] = $v->user_id;;
				$time_info_output[$sum_interval][$formatted_date][] = $v->user_id;
			}
		}


		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Users Created"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}
		// $vce->log($method_output[0]);
		return $method_output;
	}



	/*
		Report
		This produces an array which the component_data_report turns into a table.

		cycles_created: creates an array of date-timespans with the number of cycles created during each.
	**/

	public function cycles_created($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Cycles Created"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}

	/*
		Report
		This produces an array which the component_data_report turns into a table.

		cycles_created: creates an array of date-timespans with the number of cycles created during each.
	**/

	public function cycles_completed($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS d ON a.component_id = d.component_id AND d.meta_key = 'pbccycle_status' AND d.meta_value = 'Complete'  JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Cycles Marked as Complete"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}

	public function videos_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'VimeoVideo' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Videos Uploaded "
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}


	public function pdf_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'PDF' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of PDFs Uploaded "
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}

	public function images_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'Image' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Images Uploaded "
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}

	public function media_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Media (of any type) Uploaded "
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
	}


		/*
		Report
		This produces an array which the component_data_report turns into a table.

		comments_created: creates an array of date-timespans with the number of comments created during each.
	**/

	public function comments_created($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'Comments' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Comments Created"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}

		return $method_output;
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