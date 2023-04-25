<?php

class Pbc_maintenance_mode extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Maintenance Mode',
			'description' => 'This is to set the whole site in maintenance mode, so only the Admin can enter and make changes.',
			'category' => 'pbc'
		);
	}
	
	

        /**
         *
         */
        public function as_content($each_component, $vce) {

                
			
                // add javascript to page
                $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');

                $vce->site->add_style(dirname(__FILE__) . '/css/style.css');

             

$content = 'By clicking the checkbox to "ON", the site will be set in maintenance mode';                              
$content .= '<br><br>';
$content .= 'Only users with Admin role can use the site. All others will see the Maintenance page'; 
$content .= '<br><br>';
                              

$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'maintenance_status'";
$result = $vce->db->get_data_object($query);
	// $vce->dump($result[0]->meta_value);

	$checked = '';
if (isset($result[0]->meta_value) && $result[0]->meta_value == 'in_maintenance') {
	$checked = 'checked="checked"';
}

$dossier_for_maintenance = $vce->user->encryption(json_encode(array('type' => 'Pbc_maintenance_mode','procedure' => 'maintenance_toggle','user_id' => $vce->user->user_id)),$vce->user->session_vector);


$content .= <<<EOF

<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_maintenance">
<input type="checkbox" name="maintenance_toggle" value="maintenance_on" $checked>Enable Maintenance Mode<br>
<input type="submit" value="Toggle Maintenance Mode">
</form>
EOF;

                                



                $vce->content->add('main', $content);


        }
   
		
		public function maintenance_toggle($input) {
			global $vce;

			$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'maintenance_status'";
			$result = $vce->db->get_data_object($query);
			$exists = FALSE;
			if (isset($result[0]->meta_value)) {
				$exists = TRUE;
			}
			if (isset($input['maintenance_toggle'])) {
				if ($exists === FALSE) {
					$query = "REPLACE INTO " . TABLE_PREFIX . "site_meta  SET meta_key='maintenance_status', meta_value='in_maintenance';";
				} 
				if ($exists === TRUE) {
					$query = "UPDATE " . TABLE_PREFIX . "site_meta  SET  meta_value='in_maintenance' WHERE meta_key='maintenance_status'";
				} 
				$vce->db->query($query);
				echo json_encode(array('response' => 'success', 'message' => 'Maintenance Mode is activated', 'form' => 'update', 'action' => ''));

			} else {
				if ($exists === TRUE) {
					$query = "UPDATE " . TABLE_PREFIX . "site_meta  SET  meta_value='off' WHERE meta_key='maintenance_status'";
				} 
				$vce->db->query($query);
				echo json_encode(array('response' => 'success', 'message' => 'Maintenance Mode has been de-activated', 'form' => 'update', 'action' => ''));
			}
			
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