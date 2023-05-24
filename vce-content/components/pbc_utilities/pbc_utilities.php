<?php
class Pbc_utilities  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Utilities',
			'description' => 'A collection of utilities for the OHSCC',
			'category' => 'pbc'
		);
	}
	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
	public function preload_component() {
		$content_hook = array (
			'site_hook_initiation' => 'Pbc_utilities::instantiate_self',
			'manage_users_attributes_filter_by' => 'Pbc_utilities::user_attributes_filter_by',
			'manage_users_attributes2' => 'Pbc_utilities::user_attributes_router',
			'manage_users_attributes' => 'Pbc_utilities::add_user_attributes',
			'get_organizations_and_groups' => 'Pbc_utilities::get_organizations_and_groups',
			'manage_users_attributes_list' => 'Pbc_utilities::list_user_attributes',
			'manage_users_attributes_filter' => 'Pbc_utilities::user_attributes_filter',
			'manage_users_attributes_search' => 'Pbc_utilities::user_attributes_search',
			'get_sub_components' => 'Pbc_utilities::sort_sub_components',
			'page_requested_url' => 'Pbc_utilities::load_utility_js',
			'step_resource_library_view' => 'Pbc_utilities::step_resource_library_view',
			'usermedia_resource_library_view' => 'Pbc_utilities::usermedia_resource_library_view',
			'orgmedia_resource_library_view' => 'Pbc_utilities::orgmedia_resource_library_view',
			'template_resource_library_view' => 'Pbc_utilities::template_resource_library_view',
			'vce_call_add_functions' => 'Pbc_utilities::vce_call_add_functions'
		);
		return $content_hook;
	}


	/**
	 * This is the hook method which is used to alter the search results
	 */	
	
	public static function user_attributes_search($all_users) {

		global $site;
		global $user;
		global $vce;
		
		$new_user_id = self::new_user_id();
		
		foreach ($all_users as $this_user) {
			if (isset($this_user['organization']) && $this_user['organization'] == $new_user_id['organization']) {
				$all_users[$this_user['user_id']]['organization'] = $vce->user->organization;
				$all_users[$this_user['user_id']]['group'] = $vce->user->group;
				$all_users[$this_user['user_id']]['role_id'] = $vce->user->role_id;
			}
		
		}
		return $all_users;
	}
	
	public static function instantiate_self() {

	}

	//     /**
    //  * add utility functions to VCE
    //  *
    //  * @param [VCE] $vce
    //  */
    public static function vce_call_add_functions($vce) {

		/**
		 * Writes input into log file with file and line info
		 *
		 * @param string $var
		 * @return file_write of print_r(object)
		 * if $erase is not present or is set to 0, each new plog entry erases the last. Set to one to collect all entries
		 * when the plog is larger than 50KB, it is erased befor plogging the next entry.
		 */
		$vce->plog = function ($var, $erase = 0, $file = "plog.txt") {

			$raw_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			$file_size = filesize($basepath . $file);

			if (!$erase || $file_size > 50000) {
				file_put_contents(BASEPATH . $file, '');
			}

			$backtrace = NULL;

			for ($x = 1;$x < 6; $x++) {
				
				if (!empty($raw_backtrace[$x]) && isset($raw_backtrace[$x]['file']) && isset($raw_backtrace[$x]['line'])) {
					$backtrace .=  $raw_backtrace[$x]['file'] . ' at line ' . $raw_backtrace[$x]['line'] . '
';
				} else {
					break;
				}

			}

			$var = print_r($var, TRUE);
			
			$var = $backtrace . $var;
			$var = '~~'. $file_size . $var . '##';
			
			file_put_contents($basepath . $file, $var . PHP_EOL, FILE_APPEND);
		};

	}

	/**
	 * This is the hook method which is used to set the $filter_by variable by adding ->filter_by_ attributes to the page
	 */	
	public static function user_attributes_filter_by($filter_by, $vce) {
		require(dirname(__FILE__).'/includes/user_attributes_filter_by.php');
		return $filter_by;
	}

	public static function user_attributes_router($user_info) {
		require(dirname(__FILE__).'/includes/user_attributes_router.php');
		return $insert;
	}

	public static function add_user_attributes($user_info) {
		require(dirname(__FILE__).'/includes/add_user_attributes.php');
		return $insert;
	}

	public static function get_organizations_and_groups($user_info) {
		require(dirname(__FILE__).'/includes/get_organizations_and_groups.php');
		return $insert;
	}

	public static function create_tab($input) {
		require(dirname(__FILE__).'/includes/create_tab.php');
		return $insert;
	}

	public static function get_new_notification_count($input) {
		require(dirname(__FILE__).'/includes/get_new_notification_count.php');
		return $new_notification_count;
	}




	/**
	 * This is the hook method which defines what the fileds look like in the Filter bar. 
	 * It is also used to set the $filter_by variable by adding ->filter_by_ attributes to the page
	 */		
	public static function user_attributes_filter($filter_by, $content, $page) {
		require(dirname(__FILE__).'/includes/user_attributes_filter.php');
		return $insert;
	}


	/**
	 * This is the hook method which is used to set the $filter_by variable by adding ->filter_by_ attributes to the page
	 */	
	public static function load_utility_js($vce) {
		// die('hello');
		global $vce;
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/multiple_select.js', 'jquery-ui select2');
	}

	/**
	 * list user attributes
	 */
	public static function list_user_attributes($user_attributes_list) {
		// 	
		// 		array_push($user_attributes_list, 'organization', 'group');
		// 			
		// 		return $user_attributes_list;
				$attributes['organization'] = array(
					'title' => 'Organization',
					'type' => 'select',
					'datalist' => array('name' => 'organization'),	
					'sortable' => 1
				);
		
				$attributes['group'] = array(
					'title' => 'Group',
					'type' => 'select',
					'datalist' => array('name' => 'group'),	
					'sortable' => 1
				);

				
				$user_attributes_list = array_merge($user_attributes_list, $attributes);
				// global $vce;
				// $vce->dump($user_attributes_list);
				return $user_attributes_list;
		}
	
	
	/**
	 * get the id numbers of organization and group named New Users
	 */
	public static function new_user_id() {
		global $vce;

		//find default organization and group id's based on name
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users'";
		$result = $vce->db->get_data_object($query);
		$new_user_id['organization'] = $result[0]->item_id;
		
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users Default'";
		$result = $vce->db->get_data_object($query);
		if (isset($result)) {
			$new_user_id['group'] = $result[0]->item_id;
		}
		
		return $new_user_id;
	}
	
	

		/**
	 * fetch options for groups
	 */
	public function groups($input) {
	
		global $site;
		global $vce;
		$vce->log($input);
// $orglist = json_decode($input['org_list'], true);
// foreach ($orglist as $org) {
// }
		$attributes = array(
		'parent_id' => $input['datalist_id'],
		'item_id' => $input['item_id']
		);
		
		$options = $vce->get_datalist_items($attributes);
// 		if ($input['native_org'] !== $input['datalist_id']) {
// 			foreach ($options['items'] as $option) {
// // 				if ($option['item_id'] == ) {
// // 				}
// 			}
// 		}
		//limit the groups shown when the user is a group admin or lower
		// if ($role_hierarchy > 4) {
		// 	foreach ($options['items'] as $option) {
		// 		if($option == $)
		// 	}

		// }
		$vce->log($options);
		echo json_encode($options['items']);
		return;
	
	}
	
	// should we get the sub component to make the page object?
	public function find_sub_components($each_component, $page, $components, $sub_components) {
		
		if (!empty($sub_components) && $sub_components[0]->type == "Pbccycles") {
			return false;
		}
		
		return true;
		
	}


	
	public static function find_component_id($caller_component_parent_id, $type) {
		
		global $vce;
		
		$query = 'SELECT ' . TABLE_PREFIX . 'components.parent_id,' . TABLE_PREFIX . 'components_meta.component_id, ' . TABLE_PREFIX . 'components_meta.meta_value  FROM ' . TABLE_PREFIX . 'components_meta JOIN ' . TABLE_PREFIX . 'components on ' . TABLE_PREFIX . 'components.component_id = ' . TABLE_PREFIX . 'components_meta.component_id WHERE ' . TABLE_PREFIX . 'components.component_id = '.$caller_component_parent_id.' AND ' . TABLE_PREFIX . 'components_meta.meta_key = "type"';
		$component = $vce->db->get_data_object($query);
		
		if ($component) {
		
			if ($component[0]->meta_value == $type) {
				return $component[0]->component_id;
			} else {
				$r = self::find_component_id($component[0]->parent_id, $type);
				return $r;
			}
		
		}
		

		
		
		return 'none';
	}


	/**
	 * sort pbccycles by pbccycle_begins
	 */
	public static function sort_sub_components($requested_components,$sub_components,$vce) {

		if ($requested_components[0]->type == "Pbccycles") {
		
			$meta_key = 'pbccycle_begins';
			$order = 'asc';
	
 			usort($requested_components, function($a, $b) use ($meta_key, $order) {
				if (isset($a->$meta_key) && isset($b->$meta_key)) {
 					if ($order == "desc") {
 						return strtotime($a->$meta_key) > strtotime($b->$meta_key) ? -1 : 1;
 					} else {
 						return strtotime($a->$meta_key) < strtotime($b->$meta_key) ? 1 : -1;
 					}
 				} else {
 					return 1;
 				}
 			});
		}		

		return $requested_components;
		
	}
	
	

	
	
	public static function get_user_data($user_id) {
		global $vce;

		if (!isset($user_id) || $user_id == 0) {
			return;
		}
		
		// initialize array to store users
		$site_users = array();

		//get info about the user just updated
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
		$updated_user = $vce->db->get_data_object($query);
		foreach ($updated_user as $each_user) {
	
			// create array
			$user_object = array();
	
			// add the values into the user object	
			$user_object['user_id'] = $each_user->user_id;
			$user_object['role_id'] = $each_user->role_id;
		
			$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
			$metadata = $vce->db->get_data_object($query);
		
			// look through metadata
			foreach ($metadata as $each_metadata) {

				//decrypt the values
				$value = user::decryption($each_metadata->meta_value, $each_user->vector);

				// add the values into the user object	
				$user_object[$each_metadata->meta_key] = $vce->db->clean($value);		
			}
		
			// save into site_users array
			$site_users[$each_user->user_id] = (object) $user_object;
			
			//get role name
			$site_roles = json_decode($vce->site->roles, true);
			$site_users[$each_user->user_id]->role_name = is_array($site_roles[$each_user->role_id]) ? $site_roles[$each_user->role_id]['role_name'] : $site_roles[$each_user->role_id];
			
			
			//get organization name
			$organization = isset($site_users[$each_user->user_id]->organization) ? $site_users[$each_user->user_id]->organization : 0;
	
			$attributes = array(
			'name' => 'organization'
			);
	
			$options = $vce->get_datalist_items($attributes);

			// set datalist var
			$datalist_id = $options['datalist_id'];
	
			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $organization) {
						$organization_name = $each_option['name'];
					}
				}
			}
			$site_users[$each_user->user_id]->organization_name = (isset($organization_name))? $organization_name : NULL ;
			
			//get group name
			$group = isset($site_users[$each_user->user_id]->group) ? $site_users[$each_user->user_id]->group : 0;

			$attributes = array(
			'parent_id' => $datalist_id,
			'item_id' => $organization
			);
	
			$options = $vce->get_datalist_items($attributes);
	
			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $group) {
						$group_name = $each_option['name'];
					}
				}
			}
			$site_users[$each_user->user_id]->group_name = (isset($group_name)) ? $group_name : NULL;
			$site_users[$each_user->user_id]->vector = $each_user->vector;

		}
		$user_data = $site_users[$user_id];

		
		
		return $user_data;
	}


	
	/**
	 * creates the JS necessary to have a drag and drop field for a datalist
	 * must have matching input as the methods creating the HTML. Use for each separate datalist you want to create, and name them differently
	 * $input = array('dl_name' => $dl_name, 'dl_id' => $dl_id)
	 *	$dl_name = 'TEST' ;
	 *	$dl_id = 22222;
	 *	$js = Pbc_utilities::datalist_add_js(array('dl' => '$dl', 'dl_name' => $dl_name, 'dl_id' => $dl_id));
	 */
	public static function datalist_add_js($input) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);

$content = <<<EOF
<script>
$(document).ready(function() {
$(function() {
$('#default-members-$dl').sortable({
connectWith: ".connected-sortable",
cursor: "move"
}).disableSelection();
$('#group-members-$dl').sortable({
receive: function( event, ui ) {
update_users_$dl();
},
update:function(ev, ui) {
var widget = $(this);
var removeButton = $('<span class="remove-current-members-$dl" title="remove">x</span>').click(function() {
var parentLi = $(this).parent();
$(this).remove();
parentLi.appendTo($('#default-members-$dl'))
$('#default-members-$dl_name li').sort(asc_sort).appendTo($('#default-members-$dl'));
update_users_$dl();
});
$(ui.item).prepend(removeButton);
}
}).disableSelection();

function asc_sort(a, b){
return ($(b).text().toUpperCase()) < ($(a).text().toUpperCase());    
}

function update_users_$dl() {
var selected = $('#group-members-$dl li');
var ids = new Array();
$.each(selected, function (index, value) {
var thisId = $(value).attr('user_id');
ids.push(thisId);
});
//create json object which includes datalist identifier, dl id and selected user ids in one hidden input
$('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + ids.join('|') + '"}');
}


$('.remove-current-members-$dl').on('click', function() {
var parentLi = $(this).parent();
$(this).remove();
parentLi.removeClass('invited-members');
parentLi.remove();
parentLi.appendTo($('#default-members-$dl'))
update_users_$dl();
});

//create json object from selected users when page has loaded
update_users_$dl();

});


$('.cancel-button').on('click', function() {
window.location.reload(1);
});


});
</script>	
EOF;

		return $content;
	}
	

	public static function datalist_add_css($input) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);

$content = <<<EOF
<style>
.corps-block {
width: 100%;
background: #fff;
border-radius: 5px;
}

.corps-item {
display: inline-block;
vertical-align: middle;
margin: 2%;
width: 29%;
}

.corps-item button {
display: block;
width: 30%;
margin: 10px auto;
}


/* groups */

.connected-sortable {
min-height: 35px;
}

#default-members-$dl {
position: relative;
display: block;
width: 100%;
min-height: 60px;
height: auto;
padding: 10px;
margin: 0;
}



.ui-sortable {}

.ui-state-default {
position: relative;
display: inline-block;
background: #d3d3d3;
border-width: 1px;
border-style: solid;
border-color: #d3d3d3;
border-radius: 5px;
padding: 5px;
margin: 5px;
}



#group-members-$dl .ui-state-default {
background: #fbaf41;
border-color: #fbaf41;
}

#default-members-$dl .ui-state-default, #group-members-$dl .accepted-members {
background: #d3d3d3;
border-color: #d3d3d3;
}

#default-members-$dl .none-found {
display: none;
width: 98%;
color: #999999;
background: #fff;
border-color: #fff;
text-align: center;
list-style-type: none; 
}



#group-members-$dl .invited-members-$dl {
background: #fbaf41;
border-color: #fbaf41;
}


.remove-members-$dl, .remove-current-members-$dl {
padding: 5px;
cursor: pointer;
color: red;
}

.hide-element {
display: none;
}

#between-ul {
position: relative;
display: block;
width: 32px;
height: 70px;
text-align: center;
font-size: 12px;
margin: 5px auto;
}

#between-ul #small-arrow {
display: block;
}

#between-ul #large-arrow {
position: absolute;
display: block;
top: -85px;
left: -66px;
z-index: 10;
}


#group-members-$dl {
position: relative;
display: block;
width: 100%;
min-height: 60px;
height: auto;
padding: 10px;
margin: 0;
}


#under-group-members-$dl {
position: relative;
display: block;
height: 60px;
width: 100%;
border-width: 0px 1px 1px 1px;
border-style: solid;
border-color: #333;
border-radius: 0px 0px 5px 5px;
background: #d3d3d3;
}


#under-group-members-$dl form {
display: block;
margin: 0;
padding: 0 20px;
}


</style>
EOF;

		return $content;
	
	}


public static function datalist_add_js_multiple_select($input) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);
		$content = <<<EOF
		<script>
		$(document).ready(function() {
	
			function update_users_$dl() {
				var selected = $('#group-members-$dl');
				var ids = new Array();
				$.each(selected, function (index, value) {
					var thisId = $(value).attr('user_id');
					ids.push(thisId);
				});
				//create json object which includes datalist identifier, dl id and selected user ids in one hidden input
				$('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + ids.join('|') + '"}');
			}
			
	
			//create json object from selected users when page has loaded
			var this_select_input = document.getElementById('$dl');
			getSelectedOptions_$dl(this_select_input);
	 
	
			// arguments: reference to select list, callback function (optional)
	function getSelectedOptions_$dl(sel) {
		var opts = [], opt;
		
		// loop through options in select list
		for (var i=0, len=sel.options.length; i<len; i++) {
			opt = sel.options[i];
			
			// check if selected
			if ( opt.selected ) {
				// add to array of option elements to return from this function
				opts.push(opt.value);
				
			}
		}

		// var jason = '{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + opts.join('|') + '"}';
		$('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + opts.join('|') + '"}');
		
	}
	
	

	
	// anonymous function onchange for select list with id demoSel
	
	$('#$dl').on('change', function(e) {
		// var select_list = document.getElementById('$dl');
		// console.log(select_list);
		getSelectedOptions_$dl(document.getElementById('$dl'));

	});

		
	
	
		$('.cancel-button').on('click', function() {
			window.location.reload(1);
		});
		
	
	
		});
	</script>	
EOF;


return $content;

}
	


	/**  CLICKLIST: new version of Drag and Drop
	 * creates the JS necessary to have a clicklist field for a datalist
	 * must have matching input as the methods creating the HTML. Use for each separate datalist you want to create, and name them differently
	 * $input = array('dl_name' => $dl_name, 'dl_id' => $dl_id)
	 *	$dl_name = 'TEST' ;
	 *	$dl_id = 22222;
	 *	$js = Pbc_utilities::datalist_add_js(array('dl' => '$dl', 'dl_name' => $dl_name, 'dl_id' => $dl_id));
	 */
	public static function datalist_add_js_clicklist($input) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);

$content = <<<EOF
<script>
$(document).ready(function() {

   // $(function() {
        // $('#default-members-$dl').sortable({
        //     connectWith: ".connected-sortable",
        //     cursor: "move"
        // }).disableSelection();
        // $('#group-members-$dl').sortable({
        //     receive: function( event, ui ) {
        //       update_users_$dl();
        //     },
        //     update:function(ev, ui) {
        //         var widget = $(this);
        //         var removeButton = $('<span class="remove-current-members-$dl" title="remove">x</span>').click(function() {
        //             var parentLi = $(this).parent();
        //             $(this).remove();
        //             parentLi.appendTo($('#default-members-$dl'))
        //             $('#default-members-$dl_name li').sort(asc_sort).appendTo($('#default-members-$dl'));
        //             update_users_$dl();
        //         });
        //         $(ui.item).prepend(removeButton);
        //      }
        // }).disableSelection();

        // function asc_sort(a, b){
        //     return ($(b).text().toUpperCase()) < ($(a).text().toUpperCase());    
        // }

	

        
                

		$(document).on('click', '.remove-current-members-$dl', function() {
			var parentLi = $(this).parent();
			$(this).remove();
			parentLi.removeClass( "clicklist-user-accepted" ).addClass( "clicklist-user-notaccepted" );
            parentLi.appendTo($('#default-members-$dl'))
           $('#default-members-$dl_name li').appendTo($('#default-members-$dl'));
            update_users_$dl();
        });


		$(document).on('click', '.clicklist-user-notaccepted', function() {
			$(this).remove();
			$(this).removeClass( "clicklist-user-notaccepted" ).addClass( "clicklist-user-accepted" );
			$(this).appendTo($('#group-members-$dl'));
			var removeButton = '<span class="remove-current-members-$dl" title="remove">x</span>';
            $(this).prepend(removeButton);
            update_users_$dl();
        });

        
        function update_users_$dl() {
            var selected = $('#group-members-$dl li');
            var ids = new Array();
            $.each(selected, function (index, value) {
                var thisId = $(value).attr('user_id');
                ids.push(thisId);
            });
            //create json object which includes datalist identifier, dl id and selected user ids in one hidden input
            $('#selected-users-$dl').val('{"dl":"$dl", "dl_id":"$dl_id","dl_name":"$dl_name", "user_ids":"' + ids.join('|') + '"}');
        }




        // $('.remove-current-members-$dl').on('click', function() {
        //     var parentLi = $(this).parent();
        //     $(this).remove();
        //   //  parentLi.removeClass('invited-members');
        //     parentLi.remove();
        //     parentLi.appendTo($('#default-members-$dl'))
        //     update_users_$dl();
        // });

        //create json object from selected users when page has loaded
        update_users_$dl();

   // });
		

    $('.cancel-button').on('click', function() {
        window.location.reload(1);
    });


});
</script>	
EOF;

		return $content;
	}
	

/**  CLICKLIST: new version of Drag and Drop
 */
	public static function datalist_add_css_clicklist($input) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);

$content = <<<EOF
<style>
.corps-block {
width: 100%;
background: #fff;
border-radius: 5px;
}

.corps-item {
display: inline-block;
vertical-align: middle;
margin: 2%;
width: 29%;
}

.corps-item button {
display: block;
width: 30%;
margin: 10px auto;
}


/* groups */

.connected-sortable {
min-height: 35px;
}

#default-members-$dl {
position: relative;
display: block;
width: 100%;
min-height: 60px;
height: auto;
padding: 10px;
margin: 0;
}



.ui-sortable {}

.ui-state-default {
position: relative;
display: inline-block;
background: #d3d3d3;
border-width: 1px;
border-style: solid;
border-color: #d3d3d3;
border-radius: 5px;
padding: 5px;
margin: 5px;
}



#group-members-$dl .ui-state-default {
background: #fbaf41;
border-color: #fbaf41;
}

#default-members-$dl .ui-state-default, #group-members-$dl .accepted-members {
background: #d3d3d3;
border-color: #d3d3d3;
}

#default-members-$dl .none-found {
display: none;
width: 98%;
color: #999999;
background: #fff;
border-color: #fff;
text-align: center;
list-style-type: none; 
}



#group-members-$dl .invited-members-$dl {
background: #fbaf41;
border-color: #fbaf41;
}


.remove-members-$dl, .remove-current-members-$dl {
padding: 5px;
cursor: pointer;
color: red;
}

.hide-element {
display: none;
}

#between-ul {
position: relative;
display: block;
width: 32px;
height: 70px;
text-align: center;
font-size: 12px;
margin: 5px auto;
}

#between-ul #small-arrow {
display: block;
}

#between-ul #large-arrow {
position: absolute;
display: block;
top: -85px;
left: -66px;
z-index: 10;
}


#group-members-$dl {
position: relative;
display: block;
width: 100%;
min-height: 60px;
height: auto;
padding: 10px;
margin: 0;
}


#under-group-members-$dl {
position: relative;
display: block;
height: 60px;
width: 100%;
border-width: 0px 1px 1px 1px;
border-style: solid;
border-color: #333;
border-radius: 0px 0px 5px 5px;
background: #d3d3d3;
}


#under-group-members-$dl form {
display: block;
margin: 0;
padding: 0 20px;
}


</style>
EOF;

		return $content;
	
	}


	/**
	 * creates the HTML drag and drop field for a datalist
	 * must have matching input as the methods creating the HTML. 
	 * $input = array('dl' => '$dl', 'dl_name' => $dl_name, 'dl_id' => $dl_id, 'component_id' => $each_component->component_id, 'component_method' => __FUNCTION__, 'get_user_array'=>$get_user_array)
	 * required: $page
	 */
	public static function datalist_add_drag_and_drop($input, $vce) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);


//start drag and drop
		$image_path = $vce->site->path_to_url(dirname(__FILE__));
		$clickbar_title = '';
		// get current datalists for this component
		switch ($component_method) {
			case 'add_component' :
				$current_datalists = false;
				break;
			case 'edit_component' :
				$clickbar_title = 'Edit';
				$current_datalists = $vce->get_datalist(array('component_id' => $component_id, 'datalist' => $dl));
// $site->dump($current_datalists);
			case 'as_content' :
				$current_datalists = $vce->get_datalist(array('component_id' => $component_id, 'datalist' => $dl));
				break;	
		}

		if ($current_datalists != false) {
			// create arrays of user_ids that have been added to both observer and observed lists for this component	
			foreach ($current_datalists as $each_datalist_key=>$each_datalist_value) {
				$dl_name = $each_datalist_value['name'];
				$users  = $each_datalist_value['members'];
				if (isset($each_datalist_value['datalist_id'])) {
					$dl_id = $each_datalist_value['datalist_id'];
				}
			}
		}
		$selected_users_to_display_array = (isset($users)) ? explode("|", $users) : array();
		$selected_users_to_display_object = (isset($users)) ? user::get_users(array('user_ids' => $users)) : NULL;
		$selected_user_ids = (isset($users)) ? $users : null;


		if (!isset($get_user_array['user_ids'])) {
			$get_user_array = array('user_ids' => $user->user_id);
		}

		$all_users = (isset($get_user_array)) ? user::get_users($get_user_array) : new stdClass();


$input['dl_id'] = $dl_id;			
$content = self::datalist_add_js($input);
$content .= self::datalist_add_css($input);
$content .= <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle dd">
<div class="clickbar-content">
EOF;
		if ($component_method == 'as_content') {
		
$content .= <<<EOF
<label>
<ul id="default-members-$dl" class="connected-sortable">
EOF;
			if($selected_users_to_display_object != NULL){
					foreach ($selected_users_to_display_object as $each_user) {
					// $site->log('users: '.$each_user->first_name);

								if (isset($each_user->first_name)) {
									$name = $each_user->first_name . ' ' . $each_user->last_name;
									
								} else {
									$name = $each_user->email;
								}
								$content .= '<li class="ui-state-default" user_id="' .  $each_user->user_id . '">' . $name . '</li>';
					}
				}
		
$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Selected $dl_name</div>
</div>
</label>
EOF;
		

		} else {
$content .= <<<EOF
<label>
<ul id="default-members-$dl" class="connected-sortable">
EOF;
		
					foreach ($all_users as $each_user) {
						if (!in_array($each_user->user_id, $selected_users_to_display_array)) {
							if (isset($each_user->first_name)) {
								$name = $each_user->first_name . ' ' . $each_user->last_name;
							} else {
								$name = $each_user->email;
							}
							$content .= '<li class="ui-state-default"  user_id="' .  $each_user->user_id . '">' . $name . '</li>';
						}
					}

$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Available Users for $dl_name</div>
<div class="label-error">Edit Available Users for $dl_name</div>
</div>
</label>

<label>
<ul id="group-members-$dl" class="connected-sortable">
EOF;
				if($selected_users_to_display_object != NULL){
					foreach ($selected_users_to_display_object as $each_user) {						
						if (isset($each_user->first_name)) {
							$name = $each_user->first_name . ' ' . $each_user->last_name;
						} else {
							$name = $each_user->email;
						}
						$content .= '<li class="ui-state-default accepted-members" user_id="'.$each_user->user_id.'"><span class="remove-current-members-'.$dl.'" title="remove">x</span>'.$name.'</li>';
					}

		}
					
$content .= <<<EOF
</ul>
<input schema="json" id="selected-users-$dl" type="hidden" name="user_ids_$dl" value="$selected_user_ids">
<input type="hidden" name="user_oldids_$dl" value="$selected_user_ids">


<div class="label-text">
<div class="label-message">Selected $dl_name</div>
<div class="label-error">Add</div>
</div>
</label>
EOF;
}

$content .= <<<EOF
</div>
<div class="clickbar-title clickbar-closed light_green"><span>$clickbar_title $dl_name</span>


		<div class="tooltip-icon">
			<div class="tooltip-content">
				Click to Open
			</div>
		</div>

</div>
</div>
EOF;
	return $content;	
		
	}









	/**
	 * New version of "datalist_add_drag_and_drop" to work for Molly's layout
	 * creates the HTML for a multiple select field for a datalist
	 * must have matching input as the methods creating the HTML. 
	 * $input = array('dl' => '$dl', 'dl_name' => $dl_name, 'dl_id' => $dl_id, 'component_id' => $each_component->component_id, 'component_method' => __FUNCTION__, 'get_user_array'=>$get_user_array)
	 * required: $page
	 */
	public static function datalist_add_clicklist($input, $vce) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);

//start drag and drop
		$image_path = $vce->site->path_to_url(dirname(__FILE__));
		$clickbar_title = '';
		// get current datalists for this component
		switch ($component_method) {
			case 'add_component' :
				$current_datalists = false;
				break;
			case 'edit_component' :
				$clickbar_title = 'Edit';
				$current_datalists = $vce->get_datalist(array('component_id' => $component_id, 'datalist' => $dl));
			case 'as_content' :
				$current_datalists = $vce->get_datalist(array('component_id' => $component_id, 'datalist' => $dl));
				break;	
		}

		if (isset($current_datalists) && $current_datalists != false) {
			// create arrays of user_ids that have been added to both observer and observed lists for this component	
			foreach ($current_datalists as $each_datalist_key=>$each_datalist_value) {
				$dl_name = $each_datalist_value['name'];
				$users  = $each_datalist_value['members'];
				if (isset($each_datalist_value['datalist_id'])) {
					$dl_id = $each_datalist_value['datalist_id'];
				}
			}
		}
		$selected_users_to_display_array = (isset($users)) ? explode("|", $users) : array();
		$selected_users_to_display_object = (isset($users)) ? user::get_users(array('user_ids' => $users)) : NULL;
		$selected_user_ids = (isset($users)) ? $users : null;


		if (!isset($get_user_array['user_ids'])) {
			$get_user_array = array('user_ids' => $user->user_id);
		}

		$all_users = (isset($get_user_array)) ? user::get_users($get_user_array) : new stdClass();


$input['dl_id'] = $dl_id;			
$content = self::datalist_add_js_clicklist($input);
$content .= self::datalist_add_css_clicklist($input);
$content .= <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle dd">
<div class="clickbar-content">
EOF;
		if ($component_method == 'as_content') {
		
$content .= <<<EOF
<label>
<ul id="default-members-$dl" class="connected-sortable">
EOF;
			if($selected_users_to_display_object != NULL){
					foreach ($selected_users_to_display_object as $each_user) {

								if (isset($each_user->first_name)) {
									$name = $each_user->first_name . ' ' . $each_user->last_name;
									
								} else {
									$name = $each_user->email;
								}
								$content .= '<li class="ui-state-default" user_id="' .  $each_user->user_id . '">' . $name . '</li>';
					}
				}
		
$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Selected $dl_name</div>
</div>
</label>
EOF;
		

		} else {
$content .= <<<EOF
<label>
<ul id="default-members-$dl" class="connected-sortable">
EOF;
		
					foreach ($all_users as $each_user) {
						if (!in_array($each_user->user_id, $selected_users_to_display_array)) {
							if (isset($each_user->first_name)) {
								$name = $each_user->first_name . ' ' . $each_user->last_name;
							} else {
								$name = $each_user->email;
							}
							$content .= '<li class="clicklist-user-notaccepted"  user_id="' .  $each_user->user_id . '">' . $name . '</li>';
						}
					}

$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Available Users for $dl_name</div>
<div class="label-error">Edit Available Users for $dl_name</div>
</div>
</label>

<label>
<ul id="group-members-$dl" class="connected-sortable">
EOF;
				if($selected_users_to_display_object != NULL){
					foreach ($selected_users_to_display_object as $each_user) {						
						if (isset($each_user->first_name)) {
							$name = $each_user->first_name . ' ' . $each_user->last_name;
						} else {
							$name = $each_user->email;
						}
						$content .= '<li class="clicklist-user-accepted" user_id="'.$each_user->user_id.'"><span class="remove-current-members-'.$dl.'" title="remove">x</span>'.$name.'</li>';
					}

		}
					
$content .= <<<EOF
</ul>
<input schema="json" id="selected-users-$dl" type="hidden" name="user_ids_$dl" value="$selected_user_ids">
<input type="hidden" name="user_oldids_$dl" value="$selected_user_ids">


<div class="label-text">
<div class="label-message">Selected $dl_name</div>
<div class="label-error">Add</div>
</div>
</label>
EOF;
}

$content .= <<<EOF
</div>
<div class="clickbar-title clickbar-closed light_green"><span>$clickbar_title $dl_name</span>


		<div class="tooltip-icon">
			<div class="tooltip-content">
				Click to Open
			</div>
		</div>

</div>
</div>
EOF;
	return $content;	
		
	}





	/**
	 * New version of "datalist_add_drag_and_drop" to work for Molly's layout
	 * creates the HTML for a multiple select field for a datalist
	 * must have matching input as the methods creating the HTML. 
	 * $input = array('dl' => '$dl', 'dl_name' => $dl_name, 'dl_id' => $dl_id, 'component_id' => $each_component->component_id, 'component_method' => __FUNCTION__, 'get_user_array'=>$get_user_array)
	 * required: $page
	 */
	public static function datalist_add_multiple_select($input, $vce) {
		if (!isset($input['dl_id']))  {
			$input['dl_id'] = false;
		}
		extract($input);
// $vce->dump($get_user_array);
		// get current datalists for this component
		switch ($component_method) {
			case 'add_component' :
				if (isset($selected_users)) {
					$users = $selected_users;
				}
				break;
			case 'edit_component' :
				$clickbar_title = 'Edit';
				$users = $selected_users;
				// $vce->dump('edit_comp');
			case 'as_content' :
				$users = $selected_users;
				break;	
		}

// $vce->dump($users);
		$selected_users_to_display_array = (isset($users)) ? explode("|", trim($users, '|')) : array();
// $vce->dump($selected_users_to_display_array);
		$selected_users_to_display_object = (isset($users)) ? user::get_users(array('user_ids' => $users)) : NULL;
		$selected_user_ids = (isset($users)) ? $users : null;


		if (!isset($get_user_array['user_ids'])) {
			$get_user_array = array('user_ids' => $user->user_id);
		}
		if (strpos($get_user_array['user_ids'], '|') !== FALSE) {
			$get_user_array['user_ids'] = str_replace('|', ',', trim($get_user_array['user_ids'], '|'));
		}
		$all_users = (isset($get_user_array)) ? user::get_users($get_user_array) : new stdClass();


		// $input['dl_id'] = $dl_id;	
			
		if (isset($required) && $required == true){
			$required = 'required=""';
		} else {
			$required = '';	
		}
// create hidden input with json object for list data; create multiple select
$content = <<<EOF
<div id="multiple-select-data-$dl" style="display:none" data-dl_id="$dl_id" data-dl_name="$dl_name"></div>
<input schema="json" id="selected-users-$dl" class="selections" type="hidden" name="user_ids_$dl" value="$selected_user_ids">
<select id="$dl" class="users_select coaches multiple-select-users" name="not_saved_directly_$dl" multiple="multiple" $required>
EOF;

		if ($component_method == 'as_content' || $component_method == 'as_content_finish') {

			if($selected_users_to_display_object != NULL){
					foreach ($selected_users_to_display_object as $each_user) {
						$editor = false;
						if (isset($each_user->first_name)) {
							$name = $each_user->first_name . ' ' . $each_user->last_name;
							
						} else {
							$name = $each_user->email;
						}
						// $content .= '<li class="ui-state-default" user_id="' .  $each_user->user_id . '">' . $name . '</li>';


						$content .= '<option value="' . $each_user->user_id . '"';
						$content .= ' selected';
						$content .= '>' . $name . '</option>';

					}
				}

		

		} else {
					foreach ($all_users as $each_user) {
							if (isset($each_user->first_name)) {
								$name = $each_user->first_name . ' ' . $each_user->last_name;
							} else {
								$name = $each_user->email;
							}							
							
							$content .= '<option value="' . $each_user->user_id . '"';
							if (isset($selected_users_to_display_array) && in_array($each_user->user_id, $selected_users_to_display_array)) {
								$content .= ' selected';
							}

							$content .= '>' . $name . '</option>';

					}
				}

				$content .= <<<EOF
				</select>
EOF;

		return $content;	
		
	}

	// convert pipeline delineated list of users into names
	public static function userlist_to_names($userlist, $vce) {

		$userlist = trim($userlist, '|');
		$ids =	explode('|', $userlist);
		foreach($ids as $id) {
			if ($id !== '') {
				$user_ids[] = $id;
			}
		}

		$user_ids_formatted = NULL;
		if (isset($user_ids)) {
			$user_ids = array_unique($user_ids);
			$user_ids_formatted = implode(',', $user_ids);
		}

		$user_data = $vce->user->get_users($user_ids_formatted);
		$assigned_to = '';

		if (!empty($user_data)) {
			foreach($user_data as $user) {
				if (isset($user->first_name) && isset($user->last_name)) {
					$assigned_to .= ', '. $user->first_name . ' ' . $user->last_name;
				}
			}
		}
		$name_list = trim($assigned_to, ', ');

		return $name_list;

	}



	public static function datalist_to_component_converter() {
		global $vce;
		

		$vce->get_datalist(array('component_id' => $component_id, 'datalist' => $dl));

	}


	/**
	 * This creates a complete array of components and the users which have been assigned to them
	 *
	 */

	public static function get_assignees($component_id, $vce) {

		if (!isset($vce->assignees)) {
			// This query looks through all the sub-components of this component and looks for lists of users
			// This allows an array to be built which has the cycle id as key and all assigned users as value.
			// The array can then be searched or used in the Cycle info
			$query = "SELECT e.meta_value AS step_users, d.url, b.component_id AS cycle_id FROM " . TABLE_PREFIX . "components AS a
			JOIN " . TABLE_PREFIX . "components AS b ON b.parent_id = a.component_id
			JOIN " . TABLE_PREFIX . "components AS c ON c.parent_id = b.component_id
			JOIN " . TABLE_PREFIX . "components AS d ON d.parent_id = c.component_id
			JOIN " . TABLE_PREFIX . "components_meta AS e ON e.component_id = d.component_id
			WHERE a.component_id = '$component_id'
			AND e.meta_key IN ('aps_assignee','observer','observed')";

			// $vce->dump($query);
			$result = $vce->db->get_data_object($query);
			// $vce->dump($result);
			$assignees = array();
			foreach ($result as $r) {
				$step_users = trim($r->step_users, '|');
				$step_users = explode('|', $step_users);
				if(isset($assignees[$r->cycle_id]) && is_array($assignees[$r->cycle_id])) {
					$assignees[$r->cycle_id] = array_merge($step_users, $assignees[$r->cycle_id]);
				} else {
					$assignees[$r->cycle_id] = $step_users;	
				}
				$assignees[$r->cycle_id] = array_unique($assignees[$r->cycle_id]);
			}

			// do the same at the cycle level
			$query = "SELECT  c.meta_value AS cycle_users, b.url, b.component_id AS cycle_id FROM " . TABLE_PREFIX . "components AS a
			JOIN " . TABLE_PREFIX . "components AS b ON b.parent_id = a.component_id
			JOIN " . TABLE_PREFIX . "components_meta AS c ON c.component_id = b.component_id
			WHERE a.component_id = '$component_id'
			AND c.meta_key IN ('cycle_participants')";

			// $vce->dump($query);
			$result = $vce->db->get_data_object($query);
			// $vce->dump($result);
			foreach ($result as $r) {
				$cycle_users = trim($r->cycle_users, '|');
				$cycle_users = explode('|', $cycle_users);
				if(isset($assignees[$r->cycle_id]) && is_array($assignees[$r->cycle_id])) {
					$assignees[$r->cycle_id] = array_merge($cycle_users, $assignees[$r->cycle_id]);
				} else {
					$assignees[$r->cycle_id] = $cycle_users;	
				}
				$assignees[$r->cycle_id] = array_unique(array_filter($assignees[$r->cycle_id]));
			}
			$vce->assignees = $assignees;
		}
		return $vce->assignees;
	}



	/**
	 * This creates a complete array of users and their meta-data.
	 * The resulting array of objects is then assigned to the $vce object as $vce->all_users
	 * The use for this is to be able to filter users only once and use throughout a page build
	 */

	public static function create_user_array($vce) {
		
		if (!isset($vce->all_users)) {
			// get all user id's and put in an array
			$query = "SELECT user_id  FROM " . TABLE_PREFIX . "users";
			$complete_list = $vce->db->get_data_object($query);
			$user_ids = array();
			foreach ($complete_list as $u) {
				$user_ids[] = $u->user_id;
			}
			// get all info about all users
			$all_users = $vce->user->get_users($user_ids);
			$all_users_array = array();
			foreach ($all_users as $k => $v) {
				$v->organization = isset($v->organization) ? $v->organization : 0;
				$v->group = isset($v->group) ? $v->group : 0;
				$all_users_array[$v->organization][$v->group][$v->user_id] = $v;
			}
			$vce->all_users = $all_users_array;
		}
	}

	public static function get_all_users($vce) {
		if (!isset($vce->all_users)) {
			self::create_user_array($vce);
		}
		return $vce->all_users;
	}



	/**
	 * procedure that is called via javascript by clicking on the "Add Resource" button within any component
	 */
	public function add_as_resource_requester_id($input) {
		
		global $vce;

		// remove any former as_resource_requester_id to start clean
		// $vce->site->remove_attributes('as_resource_requester_id');
		// set a value and forward to the resource library which is stipulated in $input
		$vce->site->add_attributes('as_resource_requester_id', $input['component_id'],  true);
		$vce->site->add_attributes('redirect_url', $input['redirect_url'],  true);
		$vce->site->add_attributes('as_resource_requester_title', $input['component_title'],  true);



		if (!empty($input['component_id'])) {
				$url = $input['url_of_resource_library'];

				echo json_encode(array('response' => 'success','url' => $url, 'procedure' => 'get_resource','message' => "Redirecting to Media Library."));
				return;
		}

		echo json_encode(array('response' => 'error','procedure' => 'add','message' => "Error while trying to forward."));
		return;

	}


	public static function remove_as_resource_requester_id() {
		
		global $vce;

		// remove any former as_resource_requester_id to start clean
		$vce->site->remove_attributes('as_resource_requester_id');
		// set a value and forward to the resource library which is stipulated in $input
		// $vce->site->add_attributes('as_resource_requester_id', $input['component_id'],  true);
		$vce->site->remove_attributes('redirect_url');
		$vce->site->remove_attributes('as_resource_requester_title');

		return;

	}




	/**
	 * 
	 */
	public static function step_resource_library_view($each_component, $vce) {
		// $vce->log('step');
		if (isset($vce->as_resource_requester_id)) {
		
			
			
			$as_resource_requester_id = $vce->as_resource_requester_id;
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->as_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
// 			
// 			$vce->site->dump($each_component);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
		


			$dossier = array(
			'type' => 'Pbc_step',
			'procedure' => 'create_alias',
			'org_id' => $vce->user->organization,
			'parent_id' => $vce->as_resource_requester_id,
			'alias_id' => $each_component->component_id,
			'created_by' => $vce->user->user_id,
			'redirect_url' => $vce->redirect_url
			);
		
			// <td class="table-icon"><button class="plus-minus-icon">+</button><div class="menu-container"></div></td>

			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
			$alias_id = $each_component->component_id;
			$inputtypes = json_encode(array());
			// $vce->site->dump($each_component->title);
			$content = <<<EOF
			<br><br>
			<form class="asynchronous-form add-resource-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" name="inputtypes" value="$inputtypes">
			<input type="hidden" class="add-button-info" name="alias_id" value="$alias_id" resource_requester_title="$resource_requester_title">
			<input type="submit" class="button__primary" value="Add to: $resource_requester_title">
			</form>
EOF;

			if (isset($vce->link_layout) && $vce->link_layout == 'inline') {

				return $content;
			
			} else {


				$vce->content->add('associate_resource',$content);
			}
		}
	}



	/**
	 * 
	 */
	public static function usermedia_resource_library_view($each_component, $vce) {
		// $vce->log('step');
		if (isset($vce->as_resource_requester_id)) {
		
			
			
			$as_resource_requester_id = $vce->as_resource_requester_id;
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->as_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
// 			
// 			$vce->site->dump($each_component);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
		


			$dossier = array(
			'type' => 'Pbc_step',
			'procedure' => 'create_alias',
			'org_id' => $vce->user->organization,
			'parent_id' => $vce->as_resource_requester_id,
			'alias_id' => $each_component->component_id,
			'created_by' => $vce->user->user_id,
			'redirect_url' => $vce->redirect_url
			);
		
			// <td class="table-icon"><button class="plus-minus-icon">+</button><div class="menu-container"></div></td>

			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
			$alias_id = $each_component->component_id;
			$inputtypes = json_encode(array());
			// $vce->site->dump($each_component->title);
			$content = <<<EOF
			<br><br>
			<form class="asynchronous-form add-resource-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" name="inputtypes" value="$inputtypes">
			<input type="hidden" class="add-button-info" name="alias_id" value="$alias_id" resource_requester_title="$resource_requester_title">
			<input type="submit" class="button__primary" value="Add to: $resource_requester_title">
			</form>
EOF;

			if (isset($vce->link_layout) && $vce->link_layout == 'inline') {

				return $content;
			
			} else {


				$vce->content->add('associate_resource',$content);
			}
		}
	}


	/**
	 * 
	 */
	public static function orgmedia_resource_library_view($each_component, $vce) {
		// $vce->log('step');
		if (isset($vce->as_resource_requester_id)) {
			
			$as_resource_requester_id = $vce->as_resource_requester_id;
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->as_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
// 			
// 			$vce->site->dump($each_component);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
		


			$dossier = array(
			'type' => 'Pbc_step',
			'procedure' => 'create_alias',
			'parent_id' => $vce->as_resource_requester_id,
			'alias_id' => $each_component->component_id,
			'org_id' => $vce->user->organization,
			'created_by' => $vce->user->user_id,
			'redirect_url' => $vce->redirect_url
			);
		
			// <td class="table-icon"><button class="plus-minus-icon">+</button><div class="menu-container"></div></td>

			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
			$alias_id = $each_component->component_id;
			$inputtypes = json_encode(array());
			// $vce->site->dump($each_component->title);
			$content = <<<EOF
			<br><br>
			<form class="asynchronous-form add-resource-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" name="inputtypes" value="$inputtypes">
			<input type="hidden" class="add-button-info" name="alias_id" value="$alias_id" resource_requester_title="$resource_requester_title">
			<input type="submit" class="button__primary" value="Add to: $resource_requester_title">
			</form>
EOF;

			if (isset($vce->link_layout) && $vce->link_layout == 'inline') {

				return $content;
			
			} else {


				$vce->content->add('associate_resource',$content);
			}
		}
	}
	
	/**
	 * 
	 */
	public static function template_resource_library_view($vce) {
		// $vce->log('template');
		if (isset($vce->as_resource_requester_id)) {
		
			$as_resource_requester_id = $vce->as_resource_requester_id;		
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->as_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
// 			
// 			$vce->site->dump($each_component);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
	
			$dossier = array(
			'type' => 'Pbc_step',
			'procedure' => 'create_alias',
			'org_id' => $vce->user->organization,
			'parent_id' => $vce->as_resource_requester_id,
			'created_by' => $vce->user->user_id,
			'redirect_url' => $vce->redirect_url
			);
		
		
			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
			$inputtypes = json_encode(array());

			// $vce->site->dump($each_component->title);
			$content = <<<EOF
			<td class="table-icon">
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" name="inputtypes" value="$inputtypes">
			<input type="hidden" class="add-button-info" title="{template_title}" name="alias_id" value="{template_component_id}" resource_requester_title="$resource_requester_title">
			
			<button class="plus-minus-icon">+</button><div class="menu-container"></div>
			</form>
			</td>

			
EOF;
			if (isset($vce->link_layout) && $vce->link_layout == 'inline') {

				return $content;
			
			} else {


				$vce->content->add('associate_resource',$content);
			}
		}
	}



	
	
	/**
	 * This takes any filter critereon contained in $filter_by and returns a pipeline delineated list of users which fit that filter
	 */

	public static function filter_users($filter_by, $vce) {

		//if no complete user-list has been created during this page-build, create one now.
		if (!isset($vce->all_users)) {
			self::create_user_array($vce);
		}

		// add users to other specifications in $filter_by array
		$users_filtered_array = array();
			foreach ($vce->all_users as $org) {
				foreach ($org as $group) {
					foreach ($group as $user) {
						$test = TRUE;
						foreach ($filter_by as $k => $v) {
							if ($user->$k != $v) {
								$test = FALSE;
							}
							if ($k == 'user_id' && $v == '00') {
								$test = TRUE;
							}
						}
						if ($test == TRUE) {
							$users_filtered_array[] = $user->user_id;
						}
					}
				}
			}


		// return pipeline delineated list of filtered users
		// pipeline delineated strings need to start and end with a pipeline in order to allow this syntax:
		// "   LIKE '%|62|%'"
		// so using implode and explode need to reflect this.
		$users_filtered_array = array_unique($users_filtered_array);
		$users_filtered_string = implode('|', $users_filtered_array);
		$start_character = '';
		$end_character = '';
		if (substr($users_filtered_string, 0) != '|'){
			$start_character = '|';
		}
		if (substr($users_filtered_string, -1) != '|'){
			$end_character = '|';
		}
	
	return $start_character.$users_filtered_string.$end_character; 


			// generate array of users in specified organization or group
		// if (isset($filter_by['organization']) || isset($filter_by['group'])) {
		// 	$users_in_org = array();
		// 	$users_in_group = array();
		// 	$filter_organization = (isset($filter_by['organization']) ? $filter_by['organization'] : $vce->user->organization);
		// 	$filter_group = (isset($filter_by['group']) ? $filter_by['group'] : $vce->user->group);
		// 	foreach ($vce->all_users[$filter_organization] as $group => $users) {
		// 		foreach ($users as $k => $v) {
		// 			$users_in_org[] = $k;
		// 			if ($group == $filter_group) {
		// 				$users_in_group[] = $k;
		// 			}
		// 		}
		// 	}
		// }


			// // get roles
			// $roles = json_decode($vce->site->roles, true);

			// // get roles in hierarchical order
			// $roles_hierarchical = json_decode($vce->site->site_roles, true);


			// $query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id";
			
			
			
			// $query = "SELECT *  FROM " . TABLE_PREFIX . "users";

			// $current_list = $vce->db->get_data_object($query);
	
	
			// // rekey data into array for user_id and vectors
			// foreach ($current_list as $each_list) {
			// 	$users_list[] = $each_list->user_id;
			// 	$users[$each_list->user_id]['user_id'] = $each_list->user_id;
			// 	$users[$each_list->user_id]['role_id'] = $each_list->role_id;
			// 	$users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
			// 	$vectors[$each_list->user_id] = $each_list->vector;
			// }
		
			// // Second we query the user_meta table for user_ids
		
			// if (isset($users_list) ) {
		
			// 	// get meta data for the list of user_ids
			// 	$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode(',',$users_list) . ")";

			// } else {

			// 	// get all meta data for all users because of filtering
			// 	$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta";

			// }

			// $meta_data = $vce->db->get_data_object($query);
		
			// // rekey data
			// foreach ($meta_data as $each_meta_data) {
		
			// 	// skip lookup
			// 	if ($each_meta_data->meta_key == 'lookup') {
			// 		continue;
			// 	}
			
			// 	// add
			// 	$users[$each_meta_data->user_id][$each_meta_data->meta_key] = User::decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
			// }
			
			// 			// prepare for filtering of roles limited by hierarchy
			// if (!empty($filter_by)) {
			// 	$role_hierarchy = array();
			// 	// create a lookup array from role_name to role_hierarchy
			// 	foreach ($roles as $roles_key=>$roles_value) {
			// 		$role_hierarchy[$roles_key] = $roles_value['role_hierarchy'];
			// 	}
			// }

			// // loop through users
			// foreach ($users_list as $each_user) {

			
			// 	// check if filtering is happening
			// 	if (!empty($filter_by)) {
			// 		//set organization and group filter back to single for the next user
			// 		if (isset($filter_by['organization_s'])) {
			// 			$filter_by['organization'] = $filter_by['organization_s'];
			// 			unset($filter_by['organization_s']);
			// 		}
			// 		if (isset($filter_by['group_s'])) {
			// 			$filter_by['group'] = $filter_by['group_s'];
			// 			unset($filter_by['group_s']);
			// 		}
			// 		// $site->log($filter_by);
			// 		// special instructions if filtering by org/group
			// 		if (array_key_exists('organization', $filter_by) || array_key_exists('group', $filter_by)) {

			// 			//if a user is in more than one organization/group, set their organization and group properties to arrays of those orgs and groups
			// 			if (isset($users[$each_user]['native_org_group'])) {
			// 				// $site->log($users[$each_user]);
			// 				// add native organization and group to org_group_list
			// 				$org_group_list = json_decode($users[$each_user]['org_group_list'], true);
			// 				$native_org_group = json_decode($users[$each_user]['native_org_group'], true);
			// 				foreach ($native_org_group as $key=>$value) {
			// 					$org_group_list[$key] = $value;
			// 				}
			// 				// $site->dump($org_group_list);
			// 				$users[$each_user]['organization_s'] = array_keys($org_group_list);
			// 				$users[$each_user]['group_s'] = array();
			// 				foreach ($org_group_list as $key=>$value) {
			// 					if (is_array($value)) {
			// 						foreach ($value as $key2=>$value2) {
			// 							$users[$each_user]['group_s'][] = $key2;
									
			// 						}
			// 					}
			// 				}
							
							
			// 				// $site->dump($users[$each_user]);
			// 				//create special filter_by entries for multiple org coaches
			// 				if (isset($filter_by['organization'])) {
			// 					$filter_by['organization_s'] = $filter_by['organization'];
			// 				}
			// 				if (isset($filter_by['group'])) {
			// 					$filter_by['group_s'] = $filter_by['group'];
			// 				}
			// 				//unset single org and group filters so the multiple coaches can get through
			// 				// (this is reset to standard at the end of the filtering)
			// 				unset($filter_by['organization']);
			// 				unset($filter_by['group']);
						
			// 			}
			

			// 		}

			// 		// $site->dump($filter_by);
			// 		// loop through filters and check if any user fields are a match
			// 		foreach ($filter_by as $filter_key=>$filter_value) {
			// 			// prevent roles hierarchy above this from displaying
			// 			if ($role_hierarchy[$users[$each_user]['role_id']] < $role_hierarchy[$vce->user->role_id]) {
			// 				continue 2;
			// 			}

			// 			if ($filter_key == "role_id") {
			// 				// make title of role
			// 				//	$filter_value = $roles[$filter_value]['role_name'];
			// 				if ($users[$each_user]['role_id'] != $filter_value) {
			// 					continue 2;
			// 				}
							
			// 				continue;
			// 			}
			// 			// check if $filter_value is an array
			// 			if (is_array($filter_value)) {
			// 				// $site->dump($filter_value);
			// 				// check that meta_key exists for this user
			// 				if (!isset($users[$each_user][$filter_key])) {
			// 					continue 2;
			// 				}
			// 				// cycle through user attribute array, and allow user to pass through if one of the values
			// 				// equals the filter_by value
			// 				if (is_array($users[$each_user][$filter_key])) {
			// 					// $site->dump($users[$each_user]);
			// 					$continue = true;
			// 					foreach($users[$each_user][$filter_key] as $user_key => $user_value) {
			// 						if (in_array($user_value, $filter_value)) {
			// 							// continue foreach before this foreach
			// 							$continue = false;
			// 						}
			// 					}
			// 					if ($continue !== false) {
			// 						// continue foreach before this foreach
			// 						continue 2;
			// 					}

			// 				} else {
			// 					// check if not in the array
			// 					if (!in_array($users[$each_user][$filter_key],$filter_value)) {
			// 						// continue foreach before this foreach
			// 						continue 2;
			// 					}
			// 				}
			// 			} else {
			// 				// check that meta_key exists for this user
			// 				if (!isset($users[$each_user][$filter_key])) {
			// 					continue 2;
			// 				}
			// 				// cycle through user attribute array, and allow user to pass through if one of the values
			// 				// equals the filter_by value
			// 				if (is_array($users[$each_user][$filter_key])) {
			// 					// $site->dump($users[$each_user]);
			// 					$continue = true;
			// 					foreach($users[$each_user][$filter_key] as $user_key => $user_value) {
			// 						if ($user_value == $filter_value) {
			// 							// continue foreach before this foreach
			// 							$continue = false;
			// 						}
			// 					}
			// 					if ($continue !== false) {
			// 						// continue foreach before this foreach
			// 						continue 2;
			// 					}

			// 				} else {
			// 					// doesn't match so continue
			// 					if (!array_key_exists($filter_key, $users[$each_user]) || $users[$each_user][$filter_key] != $filter_value) {
			// 						// continue foreach before this foreach
			// 						continue 2;	
			// 					}
			// 				}
			// 			}
			// 		}
			// 	}
			// 	// create array of filtered users
			// 	$users_filtered_array[] = $users[$each_user]['user_id'];
			// }
			

			// // return pipeline delineated list of filtered users
			// // pipeline delineated strings need to start and end with a pipeline in order to allow this syntax:
			// // "   LIKE '%|62|%'"
			// // so using implode and explode need to reflect this.
			// $users_filtered_array = array_unique($users_filtered_array);
			// $users_filtered_string = implode('|', $users_filtered_array);
			// $start_character = '';
			// $end_character = '';
			// if (substr($users_filtered_string, 0) != '|'){
			// 	$start_character = '|';
			// }
			// if (substr($users_filtered_string, -1) != '|'){
			// 	$end_character = '|';
			// }
			// return $start_character.$users_filtered_string.$end_character; 

			
		}






	
	 /**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
			return false;
	}

}
