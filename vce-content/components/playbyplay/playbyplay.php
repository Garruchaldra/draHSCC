<?php

class PlayByPlay extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Play By Play',
			'description' => 'Allows a user to enter interactions or teaching strategies',
			'category' => 'site'
		);
	}
	

	/**
	 * play by play container
	 */
	public function as_content($each_component, $page) {
	
		// add javascript to page
		// http://automattic.github.io/Iris/
		$page->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui iris');
	
		$page->site->add_style(dirname(__FILE__) . '/css/style.css', 'play-by-play-style');
	
		$created_at = date("F j, Y, g:i a", $each_component->created_at);

		$user_image = $page->site->site_url . '/vce-application/images/user_' . ($each_component->created_by % 5) . '.png';

		if (!empty($each_component->created_by)) {
		
			// send to function for user meta_data
			$user_info = $page->user->get_users(array('user_ids' => $each_component->created_by));
		
			$name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;
		
		} else {
		
			$name = "Anonymous";
		
		}

		$pbp_data = $page->site->get_datalist_options(array('item_id' => $each_component->item_id));
	
		$play_by_play = array_values($pbp_data['options'])[0];
		
		$color = $play_by_play['color'];
		$interaction = $play_by_play['interaction'];
		$timestamp = isset($each_component->timestamp) ? $each_component->timestamp : null;

	
$content = <<<EOF
<div class="play-container remote-ignore">
<div class="play-by-play" style="background:$color;">
<div class="play-created-by">$name</div>
<span>$interaction</span>
EOF;

		if ($timestamp) {

			$milliseconds = $timestamp;		
			$seconds_full = floor($milliseconds/1000);
			$seconds = sprintf("%02d", $seconds_full % 60);
			$minutes = str_replace('60','00', sprintf("%02d", floor($seconds_full / 60)));
			$hours = floor($seconds_full/(60*60));

			$nice_timestamp =  $hours . ':' . $minutes . ':' . $seconds;

$content .= <<<EOF
<div class="play-timestamp" timestamp="$timestamp">&#9654; $nice_timestamp</div>
EOF;

		}

		if ($page->can_delete($each_component)) {

			// the instructions to pass through the form with specifics
			$dossier = array(
			'type' => 'PlayByPlay',
			'procedure' => 'delete',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at,
			'parent_url' => $page->requested_url
			);

			// add dossier, which is an encrypted json object of details uses in the form
			$dossier_for_delete = $page->generate_dossier($dossier);


$content .= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form play-delete-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="x">
</form>
EOF;

		}


$content .= '</div>';

		$page->content->add('main', $content);


	}
	
	
	/**
	 * close play by play container
	 */
	public function as_content_finish($each_component, $page) {

$content = <<<EOF
</div>
EOF;

		$page->content->add('main', $content);


	}	
	

	/**
	 * creates a component
	 */
	public function add_component($each_recipe_component, $page) {

		// add javascript to page
		// http://automattic.github.io/Iris/
		$page->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui iris');
		
		$page->site->add_style(dirname(__FILE__) . '/css/style.css', 'play-by-play-style');
		
		$user_name = $page->user->first_name . ' ' . $page->user->last_name;

		// the instructions to pass through the form
		$dossier_add = array(
		'type' => 'PlayByPlay',
		'procedure' => 'add_interaction'
		);

		// generate dossier
		$dossier_for_add = $page->generate_dossier($dossier_add);
		
		$attributes = array (
		'user_id' => $page->user->user_id,
		'datalist' => 'playbyplay_datalist'
		);

		$datalist_option = $page->site->get_datalist_options($attributes);

		if (isset($page->interaction_id)) {

$admin = <<<EOF
<div id="pbp-$each_recipe_component->parent_id" class="clickbar-container add-container ignore-admin-toggle">
<div class="clickbar-content clickbar-open">
EOF;

		} else {

$admin = <<<EOF
<div id="pbp-$each_recipe_component->parent_id" class="clickbar-container add-container ignore-admin-toggle">
<div class="clickbar-content">
EOF;

		}
		
		// remote
		$remote = "";

		if (!empty($datalist_option)) {
			foreach ($datalist_option['options'] as $each_option) {

				$item_id = $each_option['item_id'];
				$color = $each_option['color'];
				$interaction = $each_option['interaction'];
				
				// 
				$dossier = (array) $each_recipe_component->dossier;
				
				$dossier['procedure'] = 'create';
				$dossier['item_id'] = $item_id;
				
				// generate dossier
				$dossier_for_insert = $page->generate_dossier($dossier);
				

				// the instructions to pass through the form with specifics
				$dossier_remove = array(
				'type' => 'PlayByPlay',
				'procedure' => 'remove_interaction',
				'item_id' => $item_id
				);

				// add dossier, which is an encrypted json object of details uses in the form
				$dossier_for_remove = $page->generate_dossier($dossier_remove);


				// the instructions to pass through the form with specifics
				$dossier_edit= array(
				'type' => 'PlayByPlay',
				'procedure' => 'edit_interaction',
				'interaction_id' => $item_id
				);

				// add dossier, which is an encrypted json object of details uses in the form
				$dossier_for_edit = $page->generate_dossier($dossier_edit);

$admin .= <<<EOF
<div class="play-each" pbp="pbp-$each_recipe_component->parent_id" asyncontid="asynchronous-content-$each_recipe_component->parent_id" dossier="$dossier_for_insert" action="$page->input_path" interaction="$interaction" style="background:$color;">
<div class="play-edit" dossier="$dossier_for_edit" action="$page->input_path">&#9998;</div>
<span>$interaction</span>
<div class="delete-form play-delete" dossier="$dossier_for_remove" action="$page->input_path">x</div>
</div>
EOF;

$remote .= <<<EOF
<div class="play-each" pbp="pbp-$each_recipe_component->parent_id" asyncontid="asynchronous-content-$each_recipe_component->parent_id" dossier="$dossier_for_insert" action="$page->input_path" interaction="$interaction" style="background:$color;">
<span>$interaction</span>
</div>
EOF;

			}
		}

		if (isset($page->interaction_id)) {

		$pbp_data = $page->site->get_datalist_options(array('item_id' => $page->interaction_id));
	
		$play_by_play = array_values($pbp_data['options'])[0];
		
		$color = $play_by_play['color'];
		$interaction = $play_by_play['interaction'];
		
			// the instructions to pass through the form with specifics
			$dossier_update = array(
			'type' => 'PlayByPlay',
			'procedure' => 'update_interaction',
			'item_id' => $page->interaction_id
			);

			// add dossier, which is an encrypted json object of details uses in the form
			$dossier_for_update = $page->generate_dossier($dossier_update);


$admin .= <<<EOF
<div class="clickbar-container add-container ignore-admin-toggle">
<div class="clickbar-content clickbar-open">

<form id="create_items" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
<label>
<input id="color-picker" type="text" name="color" value="$color" style="color:$color;background:$color;">
<div id="picker-box"></div>
<div class="label-text">
<div class="label-message">Color</div>
<div class="label-error">Enter a Color</div>
</div>
</label>

<label>
<input type="text" name="interaction" value="$interaction" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Interaction</div>
<div class="label-error">Enter an Interaction</div>
</div>
</label>

<input type="submit" value="Update">

<a href="" class="link-button">Cancel</a>

</form>

</div>
<div class="clickbar-title"><span>Edit Interaction</span></div>
</div>

<div id="asynchronous-content-$each_recipe_component->parent_id" class="asynchronous-content" style="display:none">
	<div class="play-container">
	<div class="play-by-play" style="background:{background}">
	<div class="play-created-by">$user_name</div>
	<span>{interaction}</span>
	<div class="play-timestamp" timestamp="{timestamp}">&#9654; {nice-timestamp}</div>
	<div class="play-reload">&#8635;</div>
	</div>
	</div>
</div>

<div type="play-by-play" class="play-remote-container remote-container">
	<div class="clickbar-container add-container ignore-admin-toggle">
	<div class="clickbar-content clickbar-open">
	$remote
	</div>
	<div class="play-clickbar clickbar-title disabled"><span>Play By Play</span></div>
	</div>
</div>

</div>
<div class="play-clickbar clickbar-title"><span>Play By Play</span></div>
</div>
EOF;

		} else {

$admin .= <<<EOF
<div class="clickbar-container add-container ignore-admin-toggle">
<div class="clickbar-content">

<form id="create_items" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_add">
<label>
<input id="color-picker" type="text" name="color" value="#9c3" style="color:#9c3;background:#9c3;">
<div id="picker-box"></div>
<div class="label-text">
<div class="label-message">Color</div>
<div class="label-error">Enter a Color</div>
</div>
</label>

<label>
<input type="text" name="interaction" value="" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Interaction</div>
<div class="label-error">Enter an Interaction</div>
</div>
</label>

<input type="submit" value="Add">

<div id="create-pbp-cancel" class="link-button">Cancel</div>

</form>

</div>
<div class="clickbar-title clickbar-closed"><span>Create A New Interaction</span></div>
</div>

<div id="asynchronous-content-$each_recipe_component->parent_id" class="asynchronous-content" style="display:none">
	<div class="play-container">
	<div class="play-by-play" style="background:{background}">
	<div class="play-created-by">$user_name</div>
	<span>{interaction}</span>
	<div class="play-timestamp" timestamp="{timestamp}">&#9654; {nice-timestamp}</div>
	<div class="play-reload">&#8635;</div>
	</div>
	</div>
</div>

<div type="play-by-play" class="play-remote-container remote-container">
	<div class="clickbar-container add-container ignore-admin-toggle">
	<div class="clickbar-content clickbar-open">
	$remote
	</div>
	<div class="play-clickbar clickbar-title disabled"><span>Play By Play</span></div>
	</div>
</div>

</div>
<div class="play-clickbar clickbar-title clickbar-closed"><span>Play By Play</span></div>
</div>
EOF;

		}

		$page->content->add('main', $admin);
		
	}
	
	/**
	 *
	 */
	protected function insert($input) {

	}
	
	/**
	 * edit an interaction created by user
	 */
	protected function edit_interaction($input) {
	
		global $site;
		
		$site->add_attributes('interaction_id',$input['interaction_id']);

		echo json_encode(array('response' => 'success','procedure' => 'edit_interaction'));
		return;	

	}
	
	
	/**
	 * edit an interaction created by user
	 */
	protected function update_interaction($input) {
	
		global $site;
		
		
		$attributes = array (
		'item_id' => $input['item_id'],
		'meta_data' => array (
		'interaction' => $input['interaction'],
		'color' => $input['color']
		)
		);
		
	
		$site->update_datalist($attributes);

		echo json_encode(array('response' => 'success','procedure' => 'update','action' => 'reload', 'message' => 'Interaction Updated'));
		return;	

	}
	
	/**
	 * remove an interaction created by user
	 */
	protected function remove_interaction($input) {
	
		global $db;
		global $site;
		
		$item_id = $input['item_id'];
		
		$attributes = array (
		'item_id' => $input['item_id']
		);
		
		$site->remove_datalist($attributes);
		
		// get level one components meta data
		$query = "SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='item_id' AND meta_value='" . $input['item_id'] . "'";
		$play_items = $db->get_data_object($query);
		
		if (!empty($play_items)) {
			foreach ($play_items as $each_play_item) {
			
				self::extirpate_component($each_play_item->component_id);
				
			}
		}
	
		echo json_encode(array('response' => 'success','procedure' => 'remove_interaction'));
		return;	
	}
	
	/**
	 *
	 */
	protected function add_interaction($input) {
	
		$play['interaction'] = $input['interaction'];
		$play['color'] = $input['color'];
	
		$items[] = $play;
	
		global $site;
		global $user;
		
		$attributes = array (
		'user_id' => $user->user_id,
		'datalist' => 'playbyplay_datalist'
		);
		
		$datalist = $site->get_datalist($attributes);

		if (empty($datalist)) {
		
			$attributes = array (
			'user_id' => $user->user_id,
			'datalist' => 'playbyplay_datalist',
			'aspects' => array ('type' => 'user_list'),
			'hierarchy' => array('interactions'),
			'items' => $items
			);
 
			$site->create_datalist($attributes);

			echo json_encode(array('response' => 'success','procedure' => 'create','message' => 'Interaction Created','action' => 'reload'));
			return;

		}
		
		$datalist_info = array_values($datalist)[0];
		
		$attributes = array (
	 	'datalist_id' => $datalist_info['datalist_id'],
		'items' => array(array('color' => $play['color'], 'interaction' => $play['interaction']))
	 	);
	 	
	 	$site->insert_datalist_items($attributes);
	 	
		echo json_encode(array('response' => 'success','procedure' => 'create','message' => 'Interaction Created','action' => 'reload'));
		return;

	}

}