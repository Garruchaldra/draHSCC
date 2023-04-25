<?php

	/**
	 * This is the hook method which defines what the fileds look like in the Filter bar. 
	 * It is also used to set the $filter_by variable by adding ->filter_by_ attributes to the page
	 */	
		
		global $user;
		global $site;
		global $vce;

		// $vce->log($filter_by);
		$new_user_org = self::new_user_id();

		// get organization datalist items
		$attributes = array(
			'name' => 'organization'
		);
		
		$options = $vce->get_datalist_items($attributes);
		$options_items = isset($options['items']) ? $options['items'] : null;
		

		// sort organizations by name
		$meta_key = 'name';
		$order = 'asc';

		usort($options_items, function($a, $b) use ($meta_key, $order, $page) {
			$a = (object) $a;
			$b = (object) $b;
			if (isset($a->$meta_key) && isset($b->$meta_key)) {
				if ($order == "desc") {
					return (strcmp($a->$meta_key, $b->$meta_key) > 0) ? -1 : 1;
				} else {
					return (strcmp($a->$meta_key, $b->$meta_key) > 0) ? 1 : -1;
				}
			} else {
				return 1;
			}
		});

		// set datalist var
		$datalist_id = $options['datalist_id'];
		// get groups when organization has been selected
		$dossier_for_organization = $vce->generate_dossier(array('type' => 'Pbc_utilities','procedure' => 'groups'));
		$role_hierarchy = (isset($role_hierarchy) ? $role_hierarchy : '');









// Org input
$options_array = array();
$options_array[] = array(
	'name' => '',
	'value' => ''
);
if (isset($options_items)) {
	foreach ($options_items as $each_option) {
		if ($vce->user->role_hierarchy > 2) {
			if (isset($filter_by['organization']) && !in_array($each_option['item_id'], $filter_by['organization']) && $each_option['item_id'] != $new_user_org['organization'] && $each_option['item_id'] != $vce->user->organization) {
				continue;
			}	
		}
		$this_option = array(
			'name' => $each_option['name'],
			'value' => $each_option['item_id'],
		);
		// if ($each_option['item_id'] == $user_info->organization) {
		if (isset($filter_by['organization']) && in_array($each_option['item_id'], $filter_by['organization']) && $each_option['item_id'] == $user->organization) {
				$this_option['selected'] = true; 
		}
		$options_array[] = $this_option;
	}
}
// $vce->dump($options_array);
$input = array(
	'type' => 'select',
	'name' => 'organization',
	'data' => array(
		'class' => 'filter-form',
		'tag' => 'required',
		'datalist_id' => $datalist_id,
		'dossier' => $dossier_for_organization,
		'role_hierarchy' => $vce->user->$role_hierarchy,
		'action' => $vce->input_path
	),
	'options' => $options_array
	);

	// add additional data to the select tag for JS use
	// if (isset($list_of_additional_groups) && strpos($list_of_additional_groups, '=') != false) {
	// 	$data_item = explode('=', $list_of_additional_groups);
	// 	$input['data'][trim($data_item[0])] = trim($data_item[1]);
	// }
	// if (isset($native_organization) && strpos($native_organization, '=') != false) {
	// 	$data_item = explode('=', $native_organization);
	// 	$input['data'][trim($data_item[0])] = trim($data_item[1]);
	// }

	$org_inputs = $vce->content->create_input($input,'Organization');
// 	$insert = <<<EOF
// 	$org_inputs
// EOF;


// Show Group dropdown
// Group input
$options_array = array();
$options_array[] = array(
	'name' => '',
	'value' => ''
);


if (isset($filter_by['organization'])) {
		
	$options = array();
	foreach ($filter_by['organization'] as $org) {
		$attributes = array(
		'parent_id' => $datalist_id,
		'item_id' => $org
		);
	
		$opt = $vce->get_datalist_items($attributes);
		if (isset($opt['items'])) {
			foreach ($opt['items'] as $item) {
				$options['items'][] = $item;
			}
		}
	}
}

if (isset($options['items'])) {
	foreach ($options['items'] as $each_group) {
		if ($vce->user->role_hierarchy > 3) {
			if (isset($filter_by['group']) && !in_array($each_group['item_id'],$filter_by['group'])) {
				continue;
			}	
		}
		$this_option = array(
			'name' => $each_group['name'],
			'value' => $each_group['item_id'],
		);
		if (isset($filter_by['group']) && in_array($each_group['item_id'],$filter_by['group']) && $each_group['item_id']) {
			
			$this_option['selected'] = true; 
		}
		$options_array[] = $this_option;
	}

// $vce->dump($filter_by);
$input = array(
	'type' => 'select',
	'name' => 'group',
	'required' => 'false',
	'data' => array(
		'class' => 'filter-form',
		'tag' => 'required',
		'datalist_id' => $datalist_id,
	),
	'options' => $options_array
	);

	$group_inputs = $vce->content->create_input($input,'Group','Select a Group');
// 	$insert .= <<<EOF
// 	$group_inputs
// EOF;

	}
// }




$insert = <<<EOF
$org_inputs
$group_inputs
EOF;
