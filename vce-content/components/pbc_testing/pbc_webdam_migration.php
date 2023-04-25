<?php
class Pbc_webdam_migration  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Webdam Migration',
			'description' => 'A component to allow monitored migration of videos and metadata from WebDam to the Coaching Companion.',
			'category' => 'pbc'
		);
	}
	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
	// public function preload_component() {
		// $content_hook = array (
		// 	'instructions' => 'Pbc_testing::example',
		// );
		// return $content_hook;
	// }


    /**
     *
     */
    public function as_content($each_component, $vce) {

		$vce->dump('test component');
		$content = '<div>test content</div>';

		// for installed_components
		$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'installed_components'";
			
		$result = $vce->db->get_data_object($query);

		// rekey data into array for user_id and vectors
		foreach ($result as $r) {
			$this_config = $r->meta_value;
			$this_config = json_decode($this_config, TRUE);
		}

		if (isset($this_config['Datapoints'])) {
			if (preg_match('/vce-application/', $this_config['Datapoints'])) {
				$this_config['Datapoints'] = str_replace('vce-application', 'vce-content', $this_config['Datapoints']);
						
				$update = array('meta_value' => json_encode($this_config, JSON_UNESCAPED_SLASHES));
				$update_where = array('meta_key' => 'installed_components');
				$vce->db->update('site_meta', $update, $update_where);
				
				$vce->dump('Updated installed_components datapoints path to vce-content');
				// $vce->dump($query);
				// $vce->dump($this_config);
			}
		}

				// for preloaded_components
				$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'preloaded_components'";
			
				$result = $vce->db->get_data_object($query);
		
				// rekey data into array for user_id and vectors
				foreach ($result as $r) {
					$this_config = $r->meta_value;
					$this_config = json_decode($this_config, TRUE);
				}
		
				if (isset($this_config['Datapoints'])) {
					if (preg_match('/vce-application/', $this_config['Datapoints'])) {
						$this_config['Datapoints'] = str_replace('vce-application', 'vce-content', $this_config['Datapoints']);
						
						$update = array('meta_value' => json_encode($this_config, JSON_UNESCAPED_SLASHES));
						$update_where = array('meta_key' => 'preloaded_components');
						$vce->db->update('site_meta', $update, $update_where);
						
						$vce->dump('Updated preloaded_components datapoints path to vce-content');
						// $vce->dump($query);
						// $vce->dump($this_config);
					}
				}


		// for activated_components
		$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'activated_components'";
			
		$result = $vce->db->get_data_object($query);

		// rekey data into array for user_id and vectors
		foreach ($result as $r) {
			$this_config = $r->meta_value;
			$this_config = json_decode($this_config, TRUE);
		}

		if (isset($this_config['Datapoints'])) {
			if (preg_match('/vce-application/', $this_config['Datapoints'])) {
				$this_config['Datapoints'] = str_replace('vce-application', 'vce-content', $this_config['Datapoints']);

				$update = array('meta_value' => json_encode($this_config, JSON_UNESCAPED_SLASHES));
				$update_where = array('meta_key' => 'activated_components');
				$vce->db->update('site_meta', $update, $update_where);

				$vce->dump('Updated activated_components datapoints path to vce-content');
				// $vce->dump($query);
				// $vce->dump($this_config);
			}
		}


		$tab_input = array (
			'tabs__container1' => array(
				'tabs' => array(
					'tab1' => array(
						'label' => 'Tab One',
						'content' => '<div>This is content for Tab1</div>'
					),
					'tab2' => array(
						'label' => 'Tab Two',
						'content' => '<div>This is content for Tab2</div>'
					),
					'tab3' => array(
						'label' => 'Tab Three',
						'content' => '<div>This is content for Tab3</div>'
					),
				),
			),
		);

		$tab_content1 = Pbc_utilities::create_tab($tab_input);
		// $content .= $tab_content1;



		$tab_input = array (
			'tabs__container3' => array(
				'tabs' => array(
					'tabWA' => array(
						'label' => 'Tab Washington',
						'content' => '<div>This is content for Washington</div>'
					),
					'tabOR' => array(
						'label' => 'Tab Oregon',
						'content' => '<div>This is content for Oregon</div>'
					),
					'tabCA' => array(
						'label' => 'Tab California',
						'content' => '<div>This is content for California</div>'
					),
				),
			),
		);

		$tab_content3 = Pbc_utilities::create_tab($tab_input);
		// $content .= $tab_content1;




		$tab_input = array (
			'tabs__container2' => array(
				'tabs' => array(
					'tabA' => array(
						'label' => 'Tab A',
						'content' => '<div>This is content for TabA</div>'
					),
					'tabB' => array(
						'label' => 'Tab B',
						'content' => '<div>This is content for TabB</div>'.$tab_content1.$tab_content3
					),
					'tabC' => array(
						'label' => 'Tab C',
						'content' => '<div>This is content for TabC</div>'
					),
				),
			),
		);

		$tab_content2 = Pbc_utilities::create_tab($tab_input);
		$content .= $tab_content2;

		$vce->content->add('main', $content);
	
	}

	/**
	 * instructions for various pbc pages
	 */
	public function example() {
		$content = <<<EOF
		some content here
EOF;
		return $content;
	}
	
	 /**
	 * hide this component from being added to a recipe
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
