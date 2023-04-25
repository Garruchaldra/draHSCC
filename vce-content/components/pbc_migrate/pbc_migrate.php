<?php
	/**
	 * Goals of this component:
	 * Create User
	 */

class PbcMigrate extends Component {
	//these are the current defaults for the Vimeo API, but are overridden by the settings in the $site object. Should be removed for production

    protected $_curl_opts = array();
    protected $CURL_DEFAULTS = array();

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Migrate',
			'description' => 'Migration Utility',
			'category' => 'pbc'
		);
	}
	
	/**
	 *
	 */
	public function as_content($each_component, $page) {
	
	//allyson's group: HSICC Test Group
// 		$this->create_user_list_for_one_group($page->user->user_id, $page->user->group, 1755);

$create_user_lists_per_group = 1;

if (isset($create_user_lists_per_group)) {
		$this->create_user_lists_per_group();
		exit;
}
		
	
// ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

	//only a super-admin can use this page
	if ($page->user->role_id != 1) {
		exit;
	}
	
// 		add stylesheet to page
		$page->site->add_style(dirname(__FILE__) . '/css/style.css','manageusers-style');
	

		global $site;
		global $user;
		global $db;
		$db2 = $this->migrate_db();

// $site->dump($page->user->user_id.' '.$page->user->group.' 1729');


		$content = '';
		$participants = array();
		
		// need to add user_attributes
		
		// check if value is in page object
		$user_id = isset($page->user_id) ? $page->user_id : null;
		
		$roles = json_decode($page->site->roles, true);	

		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
	




		
		$content .= 'Migration <br>';	
		
// 		$this->show_migration_taxonomy('empty');
/**
*	Auto create resource categories
* This is for creating video taxonomy:  defunct
*
**/

// $query = "SELECT * FROM resource_cats";
// $result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
// $correction = array();
// foreach ($result as $r) {
// $correction[] = array($r['cat_id'], $r['cat_name']);
// }
// // $site->dump($correction);
// foreach ($correction as $r) {
// 	// $site->dump($r);
// 	$text = trim($r[1]);
// 	$text = mysqli_real_escape_string($db2, $text);
// 	$query = "UPDATE resource_cats SET cat_name = '".$text."' WHERE cat_id = ".$r[0];
// 	echo $query;
// 	echo '<br>';
// 	mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
// }
// global $db;
// $query = "SELECT * FROM vce_datalists_items_meta";
// $result = $db->query($query);
// $correction = array();
// foreach ($result as $r) {
// $correction[] = array($r['id'], $r['meta_value']);
// }
// // $site->dump($correction);
// // exit;
// foreach ($correction as $r) {
// 	// $site->dump($r);
// 	$text = trim($r[1]);
// 	$text = $db->mysqli_escape($text);
// 	$query = "UPDATE vce_datalists_items_meta SET meta_value = '".$text."' WHERE id = ".$r[0];
// // 	echo $query;
// // 	echo '<br>';
//  $db->query($query);
// }
// exit;



//get vid categories from vce DB
$form_name = 'show_vce_taxonomy';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);
$addCategory = Array(
'dossier' => $dossier
);	
	
$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addCategory as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
$content .= <<<EOF
<input id="category-creation" type="submit" value="$form_name">
</form>
<br>
EOF;




// $this->show_migration_taxonomy('empty');

//get vid categories from migrate DB
$form_name = 'show_migration_taxonomy';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);
$addCategory = Array(
'dossier' => $dossier
);	
	
$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addCategory as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}

$content .= <<<EOF
<input id="$form_name " type="submit" value="$form_name">
</form>
EOF;




/*
$find_cat = $this->get_category_id('sub1', $page);
$site->dump($find_cat);
exit;
$found_cat_id = current($find_cat)['datalist_id'];

$category_name = 'newtest1sub6';


$addCategoryInput = Array(
"datalist_id" => $datalist_id,
"category_name" => $category_name
);

$item_id = $found_cat_id;

if (isset($item_id)) {
	$addCategoryInput["item_id"] = $item_id;
}


$this->add_category($addCategoryInput);


*/

$taxonomy = $page->site->get_datalist(array('datalist' => 'resource_library_taxonomy'));
		
$datalist_id = current($taxonomy)['datalist_id'];

$dossier = Array(
'type' => 'ResourceLibrary',
'procedure'  => 'add_category',
'datalist_id' => $datalist_id,
'item_id' => 40
);
$dossier_add_category = $page->generate_dossier($dossier);



$category_name = "randomname";

$addCategory = Array(
'dossier' => $dossier_add_category,
'category_name' => $category_name,
'inputtypes' => '[{"name":"dossier","type":"hidden"},{"name":"category_name","type":"text"}]'
);	


	
$content.= <<<EOF
<form id="create_items" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_add_category">
EOF;
foreach ($addCategory as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}

$content .= <<<EOF
<input id="category-creation" type="submit" value="Create Category">
</form>
EOF;




/*
//create SUB organizations

$form_name = 'create_sub_organizations';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

//check last migrated user
$last_migrated_organization = $this->check_last_migrated_sub_organization();

print_r($last_migrated_organization);

$last_migrated_organization_id = $last_migrated_organization[0];

$query = "SELECT * FROM organizations WHERE org_id > ".$last_migrated_organization_id." AND org_parent_id IS NOT NULL ORDER BY org_id ASC LIMIT 1";

$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
if (!empty($result)) {
	foreach ($result as $r) {
		$organizationdata = $r;
	}
}

//get actual dl ID from org_id
	$query = "SELECT b.datalist_id AS dl_id FROM vce_datalists_items_meta AS a LEFT JOIN vce_datalists AS b ON a.item_id = b.item_id WHERE a.meta_key = 'org_id' AND a.meta_value = ".$organizationdata['org_parent_id']." LIMIT 1";
	$result = $db->get_data_object($query);

	if (!empty($result)) {
		foreach ($result as $r) {
			$suborganizationdata = $r;
		}
	}
	
// 	$site->dump($suborganizationdata);
$parent_datalist_id = $suborganizationdata->dl_id;
$parent_datalist_id = ($organizationdata['org_parent_id'] == 61 ? 64 : $parent_datalist_id);


$addInput = Array(
'dossier' => $dossier,
'org_id' => $organizationdata['org_id'],
'org_name' => $organizationdata['org_name'],
'sequence' => $organizationdata['org_id'],
'name' => $organizationdata['org_name'],
'parent_id' => $organizationdata['org_parent_id'],
'datalist_id' => $parent_datalist_id
);	

	$site->dump($addInput);
$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
$input_id = $form_name.'_input';
$content .= <<<EOF
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;


/*  This is the JavaScript for create organizations 

// if () {
$content .= <<<EOF
<script>
$(document).ready(function() {
		setTimeout(function(){
			if (document.getElementById("create_sub_organizations_input")) {
				document.getElementById("create_sub_organizations_input").click();
			} else {
				location.reload();
			}
		}, 1000);
	
});
</script>
EOF;

// }
 */


 
 
 
// $create_organizations = 1;

if (isset($create_organizations)) {

//create organizations

$form_name = 'create_organizations';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

//check last migrated organization
$last_migrated_organization = $this->check_last_migrated_organization();

$last_migrated_organization_id = $last_migrated_organization[0];



$query = "SELECT * FROM organizations WHERE org_id > ".$last_migrated_organization_id." AND org_parent_id IS NULL LIMIT 1";

$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
if (!empty($result)) {
	foreach ($result as $r) {
		$organizationdata = $r;
	}
}



$addInput = Array(
'dossier' => $dossier,
'org_id' => $organizationdata['org_id'],
'org_name' => $organizationdata['org_name'],
'sequence' => $organizationdata['org_id'],
'name' => $organizationdata['org_name'],
'datalist_id' => 1
);	
$site->dump($addInput);

$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
$input_id = $form_name.'_input';
$content .= <<<EOF
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;


/*  This is the JavaScript for create organizations 

// if () {
$content .= <<<EOF
<script>
$(document).ready(function() {
		setTimeout(function(){
			if (document.getElementById("create_organizations_input")) {
				document.getElementById("create_organizations_input").click();
			} else {
				location.reload();
			}
		}, 1000);
	
});
</script>
EOF;

// }
*/
}
 

$send_test_email = 1;

if (isset($send_test_email)) {
//send_test_email

$form_name = 'send_test_email';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);



$addInput = Array(
'dossier' => $dossier,
);	
// $site->dump($addInput);

$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
$input_id = $form_name.'_input';
$content .= <<<EOF
send to: <input type="text" name="address" value="daytonra@uw.edu">
recipient name: <input type="text" name="name" value="test user">
subject: <input type="text" name="subject" value="test email">
message: <input type="text" name="message" value="test message">
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;


}


$edit_site_email = 1;

if (isset($edit_site_email)) {

$form_name = 'edit_site_email';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

$site_mail_value = $this->check_config_value('SITE_MAIL', BASEPATH.'vce-config.php');



$addInput = Array(
'dossier' => $dossier,
);	
// $site->dump($addInput);

$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}

$input_id = $form_name.'_input';
$content .= <<<EOF
<input type="text" name="site_mail" value="$site_mail_value">
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;
}




$assign_org_admins = 1;

if (isset($assign_org_admins)) {

$form_name = 'assign_org_admins';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

$addInput = Array(
'dossier' => $dossier,
);	
// $site->dump($addInput);

$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}

$input_id = $form_name.'_input';
$content .= <<<EOF
<input type="text" name="filename" value="orgadmins.csv">
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;
}






// $match_videos = 1;

if (isset($match_videos)) {
	//match vid categories to migrate DB
	$query = "SELECT cat_id, cat_name FROM resource_cats ";
	$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
	$cats_migrate = array();
	if (!empty($result)) {
		foreach ($result as $r) {
			$cats_migrate[] = array(0 => trim($r['cat_name']), 1 => $r['cat_id']);
		}
	}


	$cats_migrate2 = array();
	foreach ($cats_migrate as $key => $value) {
			$cat_name = $value[0];
			$query = 'SELECT * FROM vce_datalists_items_meta WHERE meta_value like "%'.$cat_name.'%"';
			$result = $db->query($query);

			if (!empty($result)) {
				foreach ($result as $r) {
						$cats_migrate2[$key][1] = $r['meta_value'];
						$cats_migrate2[$key][2] = $r['item_id'];
						$cats_migrate2[$key][3] = $value[1];
				}
			}
		}
		
		foreach ($cats_migrate2 as $cat) {
			$cat_name = str_replace("'", "", $cat[1]);
			$new_id = $cat[2];
			$old_id = $cat[3];
			$query = "INSERT INTO vid_categories_migration (cat_name, new_id, old_id) VALUES ('$cat_name', $new_id, $old_id)";
			$result = $db->query($query);
		}
		
		
// 			$site->dump($cats_migrate2);
	exit;
}





// $generate_video_array = 1;

if (isset($generate_video_array)) {
		//match videos to downloaded vids
		//original query:
// 		$query = "SELECT video_id, video_name, media_amp_id, video_owner FROM shared_videos WHERE file_type = 'VIDEO' ORDER BY video_id ASC";
		//query to generate new list of id's
		$query = "SELECT a.video_id as video_id FROM ncqtl_coaching.shared_videos AS a right JOIN ncqtl_coaching.shared_videos_categories AS b ON a.video_id = b.video_id
		INNER JOIN ncqtlcoachingdev.video_migration as c on a.video_id = c.video_id
		WHERE a.file_type = 'VIDEO' AND a.video_id > 0 AND c.guid = '' GROUP BY a.video_id ORDER BY a.video_id ASC";
		$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
		if (!empty($result)) {
			$vids = array();
			foreach ($result as $r) {
				$vids[] = $r['video_id'];		
			}
		}		
		$vids_to_migrate = implode(',', $vids);
		
		
		
		
		$query = "SELECT video_id, video_name, media_amp_id, video_owner FROM shared_videos WHERE video_id IN ($vids_to_migrate) ORDER BY video_id ASC";
		$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');

// 		$site->dump($query);
// 		exit;
		$vids_migrate = array();
		if (!empty($result)) {
			foreach ($result as $r) {
// 			$site->dump($r);
// 			continue;
				$vid_path = '/Applications/MAMP/htdocs/mediaAmp/downloads/';
				$db_filename = trim($r['video_name']);		
				$info = pathinfo($db_filename);
				$db_filename = $info['filename'];
				$exists = glob ($vid_path.$db_filename.".*");
// 				$exists = file_exists($vid_path.$db_filename);
				if (isset($exists[0])) {
					$path = $exists[0];
					$info = pathinfo($exists[0]);
					$filename = $info['filename'];
					$vids_migrate[$r['video_id']] = array('video_id' => $r['video_id'], 'vid_name' => $filename, 'media_amp_id' => $r['media_amp_id'], 'video_owner' => $r['video_owner'],'path' => $path);
				}
			}
		}
$site->dump($vids_migrate);

		foreach ($vids_migrate as $key => $value) {
			$owner = $value['video_owner'];
			$owner  = $owner == 1 ?  1634 : $owner;

			$query = "SELECT email FROM user_migration WHERE user_id = $owner";
			$result = $db->query($query);
			if (!empty($result)) {
				foreach ($result as $r) {
					$id = $user->email_to_id($r['email']);
					$vids_migrate[$key]['email'] = $r['email'];
					$vids_migrate[$key]['old_id'] = $owner;
					$vids_migrate[$key]['video_owner'] = $id;
				}
			}

		}

				$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
				file_put_contents($basepath . 'vid_migration_array.txt', serialize($vids_migrate));
				exit;
}





// $rename_files = 1;
if (isset($rename_files)) {
		$path ='/Applications/MAMP/htdocs/mediaAmp/downloads/';
		$dir = scandir($path);
		foreach ($dir as $d => $v) {
			$info = pathinfo($v);
			if ($info['extension'] == 'MP4') {
				$new_filename = $this::replace_extension($info['filename'], 'mp4');
				$oldname = $v;
				
				$rn = rename($path.$oldname, $path.$new_filename);
				if ($rn == 1){
					$site->dump($oldname);
				}
			}
// 			$dir[$d] = array('path' => $path, 'file' => $v);
		}
		// $site->dump($dir);
		exit;
}


// $vids = $this::list_vimeo_videos();
// 
// $site->dump($vids);
// 		
// 		exit;



/* create components from videos and assign cat names*/
// $create_video_components = 1;
if (isset($create_video_components)) {
// 	{"type":"Media",
// "parent_id":"113",
// "sequence":1,
// "name":"2.28.mov.mp4",
// "created_by":"13",
// "title":"2",
// "description":"asdf",
// "taxonomy":"|1746|",
// "media_type":"VimeoVideo",
// "path":"13_1509492137.mp4",
// "guid":"240756413"}
		// add created by and created at time_stamp
// 		$input['created_by'] = $user->user_id;
$form_name = 'custom_create_component';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

//check last migrated video 
// $last_migrated = $this->check_last_migrated_video();
// $last_migrated_id = $last_migrated[0];
	
	
		$query = "SELECT * FROM video_migration WHERE guid != '' AND created != 'yes'  ORDER BY video_id ASC LIMIT 1";
		$result = $db->query($query);
		

		
		if (!empty($result)) {
				$vids = array();
			foreach ($result as $r) {
				$vids[$r['video_id']]['name'] = $r['vid_name'];	
				$vids[$r['video_id']]['created_by'] = $r['owner'];	
				$vids[$r['video_id']]['title'] = $r['vid_name'];
				$vids[$r['video_id']]['path'] = $r['path'];
				$vids[$r['video_id']]['guid'] = $r['guid'];
				$vids[$r['video_id']]['original_id'] = $r['video_id'];
				$vids[$r['video_id']]['mediaAmp_id'] = $r['mediaAmp_id'];

				$vids[$r['video_id']]['type'] = 'Media';
				$vids[$r['video_id']]['parent_id'] = 113;
				$vids[$r['video_id']]['sequence'] = 1;
				$vids[$r['video_id']]['description'] = '';
				$vids[$r['video_id']]['taxonomy'] = '1';
				$vids[$r['video_id']]['media_type'] = 'VimeoVideo';

			}
		}		
		
		foreach ($vids as $key=>$value) {
			$original_id = $value['original_id'];
			$query = "SELECT b.video_name, c.cat_name as cat_name, c.cat_id as cat_id FROM shared_videos_categories as a right join shared_videos as b on a.video_id = b.video_id left join resource_cats as c on a.cat_id = c.cat_id WHERE b.video_id = $original_id";
			$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
			foreach ($result as $r) {
				$vids[$key]['taxonomy'] .= "|".$r['cat_id'];
			}
			$vids[$key]['original_taxonomy'] = $vids[$key]['taxonomy'];

		}

		
		foreach ($vids as $key => $value) {
			$cats = explode ('|', $value['taxonomy']);
			if($cats[1] < 1){
			$vids[$key]['taxonomy'] = '|1746|';
// 			$site->dump($cats[1]);
				break;
			}
			$new_cats = array();
			foreach ($cats as $cat) {
				$query = "SELECT new_id FROM vid_categories_migration WHERE old_id = $cat";
				$result = $db->query($query);
				
					foreach ($result as $r) {
						$new_cats[] = $r['new_id'];

					}
				
			}

		$vids[$key]['taxonomy'] = '|'.implode('|', $new_cats).'|';
		}
		$site->dump($vids);
// 		exit;
// 		$addInput = $vids;
		
$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($vids as $addInput) {
$addInput['dossier'] = $dossier;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
}
$input_id = $form_name.'_input';
$content .= <<<EOF
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;

$stop = 'no';
/*  This is the JavaScript for create video components   */
if (isset($addInput['guid']) && isset($stop)) {
$content .= <<<EOF
<script>
$(document).ready(function() {
		setTimeout(function(){
			if (document.getElementById("custom_create_component_input")) {
				document.getElementById("custom_create_component_input").click();
			} else {
				location.reload();
			}
		}, 900);
	
});
</script>
EOF;

}		
		
		
		
		
// exit;

}



// $migrate_all_vids = 1;
if (isset($migrate_all_vids)) {

			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			$serialized_array = file_get_contents($basepath . 'vid_migration_array.txt');
			$vids_migrate = unserialize($serialized_array);
// 		$site->dump($vids_migrate);
// 		exit;

//migrate videos

$form_name = 'migrate_videos';
$dossier = Array(
'type' => 'PbcMigrate',
'procedure'  => $form_name
);
$dossier = $page->generate_dossier($dossier);

//check last migrated video 
$last_migrated = $this->check_last_migrated_video();
$last_migrated_id = $last_migrated[0];

foreach($vids_migrate as $key => $value) {
	if ($key > $last_migrated_id) {
		$next_vid = $key;
		break;
	}
}

// $site->dump($vids_migrate[$next_vid]);
// exit;

		
// {"type":"Media","parent_id":"113","sequence":1,"name":"2.28.mov.mp4","created_by":"13","title":"2","description":"asdf","taxonomy":"|1746|","media_type":"VimeoVideo","path":"13_1509492137.mp4","guid":"240756413"}


$vids_migrate[$next_vid]['media_amp_id'] = isset($vids_migrate[$next_vid]['media_amp_id']) ? $vids_migrate[$next_vid]['media_amp_id'] : 'no_mediaAmp_id';
$vids_migrate[$next_vid]['video_owner'] = isset($vids_migrate[$next_vid]['video_owner']) ? $vids_migrate[$next_vid]['video_owner'] : 1634;




$addInput = Array(
'dossier' => $dossier,
'video_id' => $vids_migrate[$next_vid]['video_id'],
'mediaAmp_id' => $vids_migrate[$next_vid]['media_amp_id'],
'vid_name' => $vids_migrate[$next_vid]['vid_name'],
'video_owner' => $vids_migrate[$next_vid]['video_owner'],
'path' => $vids_migrate[$next_vid]['path']
);	

// 		$site->dump($addInput);
// exit;
$content .= <<<EOF
<form id="$form_name" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;
foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}
$input_id = $form_name.'_input';
$content .= <<<EOF
<input id="$input_id" type="submit" value="$form_name">
</form>
<br>
EOF;


/*  This is the JavaScript for create videos    
// if () {
$content .= <<<EOF
<script>
$(document).ready(function() {
		setTimeout(function(){
			if (document.getElementById("migrate_videos_input")) {
				document.getElementById("migrate_videos_input").click();
			} else {
				location.reload();
			}
		}, 1000);
	
});
</script>
EOF;
*/
// }

}


 
 
 
 
 
 
 
 
 







			
				// php script for jQuery-File-Upload

			// upload_max_filesize = 30M
			// post_max_size = 30M
			// max_execution_time = 260
			// max_input_time = -1
			// memory_limit = 256M
			// max_file_uploads = 100		
			// This is here in case you need to write out to the log.txt file for debugging purposes
// 			file_put_contents(BASEPATH . 'log.txt', 'upload_max_filesize: ' . ini_get("upload_max_filesize") . PHP_EOL, FILE_APPEND);
// 			file_put_contents(BASEPATH . 'log.txt', 'post_max_size: ' . ini_get("post_max_size") . PHP_EOL, FILE_APPEND);
// 			file_put_contents(BASEPATH . 'log.txt', 'max_execution_time: ' . ini_get("max_execution_time") . PHP_EOL, FILE_APPEND);
// 			file_put_contents(BASEPATH . 'log.txt', 'max_input_time: ' . ini_get("max_input_time") . PHP_EOL, FILE_APPEND);
// 			file_put_contents(BASEPATH . 'log.txt', 'max_file_uploads: ' . ini_get("max_file_uploads") . PHP_EOL, FILE_APPEND);


// 	global $site;
// 	$site->dump($result);
// exit;

/**
This is the process for adding users automatically from the database dump
**/

// $migrate_users = 1;
if (isset($migrate_users)) {

//check last migrated user
$last_migrated_user = $this->check_last_migrated_user();

$last_migrated_user_id = $last_migrated_user[0];

// $query = "SELECT * FROM users WHERE user_id > $last_migrated_user_id ORDER BY user_id ASC LIMIT 20";
$query = "SELECT a.*,c.org_parent_id, c.org_id, c.org_name, d.authority_id FROM users AS a LEFT JOIN user_organizations_memberships AS b ON a.user_id = b.user_id  LEFT JOIN organizations AS c on b.org_id = c.org_id LEFT JOIN user_authorities AS d on a.user_id = d.user_id WHERE a.user_id > $last_migrated_user_id AND a.user_id != 1 ORDER BY user_id ASC LIMIT 20";
$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
$site->dump($query);


if (!empty($result)) {
	$create_user_button_id = 'email_already_in_use';
	foreach ($result as $r) {
// 	echo $r['email'];
	// 	check vce db for email, skip create if it exists.
		$lookup = user::lookup($r['email']);
		
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			continue;
		} 

		$create_user_button_id = 'auto_create_user_submit';
		$userdata = $r;
		break;
	}
}


//find organization and group datalist ids based on the original org_id
if ($userdata['org_parent_id'] == NULL) {
		$organization = array(
			'org_id' => $userdata['org_id']
		);
		$attributes = array(
			'name' => 'organization'
		);
		
		$options = $site->get_datalist_items($attributes);

		if (isset($options['items'])) {
			foreach ($options['items'] as $each_option) {
				if (isset($each_option['org_id'])){
					if ($each_option['org_id'] == $userdata['org_id']) {
						$organization['dl_id'] = $each_option['item_id'];
						$organization['dl_name'] = $each_option['name'];
					}
				}
			}
		}	


		$attributes = array(
			'item_id' => $organization['dl_id']
		);
		$options = $site->get_datalist($attributes);
// 		$site->dump($options);
		if (isset($options)) {
			foreach ($options as $each_option) {
					$group_datalist_id = $each_option['datalist_id'];
			}
		}

		$attributes = array(
			'datalist_id' => $group_datalist_id
		);
		$options = $site->get_datalist_items($attributes);	
		$group = array();
		if (isset($options['items'])) {
			foreach ($options['items'] as $each_option) {
				if ($each_option['name'] == 'default') {
					$group['dl_id'] = $each_option['item_id'];
					$group['dl_name'] = $each_option['name'];
				}
			}
		}
		
// 		$attributes = array(
// 			'datalist_id' => $organization['dl_id']
// 		);
// 		$group = array();
// 		$options = $site->get_datalist_items($attributes);
// 		if (isset($options['items'])) {
// 			foreach ($options['items'] as $each_option) {
// 				if ($each_option['name'] == 'default') {
// 					$group['dl_id'] = $each_option['item_id'];
// 					$group['dl_name'] = $each_option['name'];
// 				}
// 			}
// 		}


} else {

		$organization = array(
			'org_id' => $userdata['org_id']
		);

		$attributes = array(
			'name' => 'organization'
		);
		
		$options = $site->get_datalist_items($attributes);
// 		$site->dump($options);
		if (isset($options['items'])) {
			foreach ($options['items'] as $each_option) {
				if ($each_option['org_id'] == $userdata['org_parent_id']) {
					$organization['dl_id'] = $each_option['item_id'];
					$organization['dl_name'] = $each_option['name'];
				}
			}
		}	

		
		$attributes = array(
			'item_id' => $organization['dl_id']
		);
		$options = $site->get_datalist($attributes);
// 		$site->dump($options);
		if (isset($options)) {
			foreach ($options as $each_option) {
					$group_datalist_id = $each_option['datalist_id'];
			}
		}

		$attributes = array(
			'datalist_id' => $group_datalist_id
		);
		$options = $site->get_datalist_items($attributes);	
// 		$site->dump($options);

		$group = array();
		if (isset($options['items'])) {
			foreach ($options['items'] as $each_option) {
				if ($each_option['org_id'] == $userdata['org_id']) {
					$group['dl_id'] = $each_option['item_id'];
					$group['dl_name'] = $each_option['name'];
				}
			}
		}

}

//get role and assign it to current role structure
	$roles = json_decode($page->site->roles, true);	
	foreach ($roles as $key => $value) {
		$role_name = is_array($value) ? $value['role_name'] : $value;

		if ($userdata['authority_id'] != 2 AND $role_name == 'Coachee') {
			$user_role = $key;
		} elseif ($userdata['authority_id'] == 2 AND $role_name == 'Coachee') {
			$user_role = $key;
		}
	}
	
	$site->dump($organization);
	$site->dump($group);


$dossier_for_create_user = $page->user->encryption(json_encode(array('type' => 'PbcMigrate','procedure' => 'create_user')),$page->user->session_vector);

$addInput = array(
'dossier' => $dossier_for_create_user,
'user_id' => $userdata['user_id'],
'email' => $userdata['email'],
'password' => '12345',
'first_name' => $userdata['firstname'],
'last_name' => $userdata['lastname'],
'organization' => $organization['dl_id'],
'group' => $group['dl_id'],
'role_id' => $user_role
);


global $site;
 $site->dump($userdata);
 $site->dump($addInput);


$content .= <<<EOF
<form id="auto_create_user" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
EOF;

foreach ($addInput as $key=>$value) {
$content .= <<<EOF
<input type="hidden" name="$key" value="$value">
EOF;
}

$content .= <<<EOF
Create Users from DB Automatically:
<input id="$create_user_button_id" type="submit" value="$create_user_button_id">
</form>
EOF;


//button for clearing user migration table
$dossier_for_empty_user_migration_table = $page->user->encryption(json_encode(array('type' => 'PbcMigrate','procedure' => 'empty_user_migration_table')),$page->user->session_vector);

$content .= <<<EOF
<form id="empty_user_migration_table" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_empty_user_migration_table">
<input type="hidden" name="placeholder" value="placeholder">
<input type="submit" value="Empty user_migration Table">
</form>
EOF;


/*  This is the JavaScript which sends the form in to create a user: 



$content .= <<<EOF
<script>
$(document).ready(function() {
		setTimeout(function(){
			if (document.getElementById("auto_create_user_submit")) {
				document.getElementById("auto_create_user_submit").click();
			} else {
				location.reload();
			}
		}, 1900);
	
});
</script>
EOF;
*/


}



/**
*	manual create user form
*
*
**/


// create user form (this is a copy from manageusers.php)
$dossier_for_create = $page->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'create')),$page->user->session_vector);

$content .= <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content">
<form id="form" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">

<label>
<input type="text" name="email" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Email</div>
<div class="label-error">Enter Email</div>
</div>
</label>

<label>
<input type="text" name="password" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Password</div>
<div class="label-error">Enter your Password</div>
</div>
</label>

<label>
<input type="text" name="first_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">First Name</div>
<div class="label-error">Enter a First Name</div>
</div>
</label>

<label>
<input type="text" name="last_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Last Name</div>
<div class="label-error">Enter a Last Name</div>
</div>
</label>
EOF;

			// load hooks
			if (isset($page->site->hooks['user_attributes'])) {
				foreach($page->site->hooks['user_attributes'] as $hook) {
					$content .= call_user_func($hook, $content);
				}
			}

$content .= <<<EOF
<label>
<select name="role_id" tag="required">
<option value=""></option>
EOF;

			foreach ($roles as $key => $value) {
				// allow both simple and complex role definitions
				$role_name = is_array($value) ? $value['role_name'] : $value;
				$content .= '<label for=""><option value="' . $key . '">' . $role_name . '</option>';
				
			}
		
$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
<input type="submit" value="Create User">
<div id="generate-password" class="link-button">Generate Password</div>
</form>
</div>
<div class="clickbar-title clickbar-closed"><span>Create A New User</span></div>
</div>
</p>
EOF;





$dossier_for_define_value = $page->user->encryption(json_encode(array('type' => 'PbcMigrate','procedure' => 'define_config_value')),$page->user->session_vector);


$phpcas_path = BASEPATH .'vce-content/components/cas_login/CAS/cas_config.php';
$constant_name = 'CAS_HOST';
$cas_server = $this->check_config_value($constant_name, $phpcas_path);

$content .= <<<EOF
<form id="auto_create_user" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_define_value">
File to Edit: <input type="text" name="filename" value="$phpcas_path">
Constant Variable to Change: <input type="text" name="constant_name" value="$constant_name">
Value to use: <input type="text" name="constant_value" value="$cas_server">

<input type="submit" value="Set CAS server">
</form>
EOF;





// list the first x users then delete everyone after a given id and everything they created
global $db;
$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users LIMIT 13";
$all_users = $db->get_data_object($query);

foreach ($all_users as $each_user) {
// create array
			$user_object = array();
		
			// add the values into the user object	
			$user_object['user_id'] = $each_user->user_id;
			$user_object['role_id'] = $each_user->role_id;
			
			$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
			$metadata = $db->get_data_object($query);
			
			// look through metadata
			foreach ($metadata as $each_metadata) {

				//decrypt the values
				$value = user::decryption($each_metadata->meta_value, $each_user->vector);

				// add the values into the user object	
				$user_object[$each_metadata->meta_key] = $db->clean($value);		
			}
			
			// save into site_users array
			$site_users[$each_user->user_id] = (object) $user_object;

		}
		

		$user_attributes_list = array('user_id','last_name','first_name','email');

		// load hooks
		if (isset($page->site->hooks['user_attributes_list'])) {
			foreach($page->site->hooks['user_attributes_list'] as $hook) {
				$user_attributes_list = call_user_func($hook, $user_attributes_list);
			}
		}

// list site users
$content .= <<<EOF

<table id="users" class="tablesorter">
<thead>
<tr>
<th></th>
<th></th>
<th></th>
<th>Site Role</th>
EOF;


$content .= <<<EOF
</tr>
</thead>
EOF;




foreach ($site_users as $each_site_user) {
		
				
			foreach ($user_attributes_list as $each_user_attribute) {

				$content .= '<td>';
				if (isset($each_site_user->$each_user_attribute)) {
					$content .= $each_site_user->$each_user_attribute;
				}
				$content .= '</td>';

			}

$content .= <<<EOF
</tr>
EOF;

		}

$content .= <<<EOF
</table>

EOF;

$dossier_for_delete_users = $page->user->encryption(json_encode(array('type' => 'PbcMigrate','procedure' => 'delete_users')),$page->user->session_vector);

$content .= <<<EOF
<form id="auto_create_user" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_delete_users">
Delete after this ID: <input type="text" name="first_user_to_delete" value="19">
Number of Users to Delete: <input type="text" name="number_users_to_delete" value="3000">

<input type="submit" value="Delete Users">
</form>
EOF;


if (isset($_GET['permission']) && $_GET['permission'] == 'highlight_file') {
	highlight_file(BASEPATH.'vce-config.php');
}



if (isset($_GET['permission']) && $_GET['permission'] == 'adjust_roles') {
	// $org_admins = array('flossy.calderon@bostonabcd.org', 'vkittleman@abshs.org', 'mbeaver@accordcorp.org', 'strappyng@gm.sbac.edu', 'lwright@abcinfo.org', 'tpeeples@abcd.org', 'k.hill@aemt.org', 'tara.skiles@ece.alabama.gov', 'sabrinae@albinaheadstart.org', 'tthomas@acheadstart.org', 'kelly.hill@aeoa.org', 'dgood@accaa.org', 'smarvel@atcaa.org', 'mmusunoi.rgv@avance.org', 'bneff@aware-inc.org', 'heather@babytalkehs.org', 'julie.parmley@baldwin.k12.ga.us', 'pbushjones@unionbaptistheadstart.com', 'cherie_mortenson@bismarckschools.org', 'jessica.kraft@alsm.org', 'foxcasalek@bsecdc.org', 'joglesby@bm-cap.org', 'chastityg@bfhs.net', 'smitchell@bethlehemcenter.org', 'peg.millar@bicap.org', 'marjory.antoine@birchfamilyservices.org', 'susie.cisneros@bishoppaiute.org', 'broten@brocinc.com', 'jklein@bvca.net', 'mjohnson@bwcaa.org', 'rebecca.agnew@bgcap.org', 'jlewis@bonavista.org', 'lpaiva@btwchild.org', 'ferguson-farley_amy@bah.com', 'racquel.hayes@boystown.org', 'sandybutton@bradfordtiogahs.org', 'peppert@cars-services.org', 'valeria.hicks@breckgrayson.com', 'andyj@bbfkc.org', 'carol.breeding@buchanancounty-va.gov', 'khillebrand@bcccinc.org', 'lhindman@cacbelmont.org', 'mdelph@cacehr.org', 'mcoates@caldwelledu.org', 'bookert@calvertnet.k12.md.us', 'jodaigle@aol.com', 'jjahner@dpsnd.org', 'aherr@caprw.org', 'tmohn@capcil.org', 'jhubbard@capeheadstart.org', 'akelly@capfsc.org', 'kleuth@caprw.org', 'bhale@capstonevt.org', 'eedwards@cccdp.net', 'kmindt@cardcaa.org', 'athompson@casewv.info', 'ptaylor@fcmshs.org', 'lfaull@cdsoc.org', 'kelly.paiz@ceen.org', 'mcisneros@ctfhs.org', 'sgarcia@c4newcommunities.org', 'rose.bynum@ct4c.org', 'lorettac@cwvcaa.org', 'larbuckle@clackesd.org', 'aashley@cheahaheadstart.org', 'cynthiasc@cciu.org', 'jbinus@cabc-bchs.org', 'jervin@nworheadstart.org', 'rkurisu@cfs-hawaii.org', 'kellydownie@clcstamford.org', 'julieb@c2r2.org', 'jcuadra@ccrcca.org', 'sdrake@childcareresourcesinc.org', 'kgood@childdevelop.org', 'pweist@child-focus.org', 'ssmith@childstartinc.org', 'bdavis@childrenfirst.net', 'ahoard@childrenscoalition.org', 'bj@choctawnation.com', 'rruiz@cabq.gov', 'joann.jackson@phoenix.gov', 'patf@cccchs.org', 'tmensah@claytoncountycsa.org', 'jlabonte@clmcaa.com', 'lreyher@cars-services.org', 'lathania.santos@cnmipss.org', 'kbrowning@coalfieldcap.org', 'nicole.jachimiak@coastalca.org', 'srice@csisd.org', 'lfitzsimmons@ceoempowers.org', 'elizabeth.barrientes@cacost.org', 'rpelis@communityaction.us', 'hgrove@capcc.us', 'aphillips@endpov.com', 'aredick@caswg.org', 'mpittman@cawm.org', 'angela.johnson@ccs.spokane.edu', 'becky.zaleski@ccs.spokane.edu', 'kfrahn@community-concepts.org', 'laurieh@commlink.org', 'aylwards@crtct.org', 'dhenry@cscinc.org', 'lauffert@csinwmo.org', 'mbixby@commteam.org', 'lang2j@msn.com', 'kdemarco@ceogc.org', 'egarcia@spanishcenter-milw.org', 'shenry@cotraic.org', 'jtucker@crystalstairs.org', 'dsmith33@csraeoaheadstart.org', 'habbott@cullmancats.net', 'mfieschel@dsdmail.net', 'cilentidaniel@gmail.com', 'las2m@virginia.edu', 'sperry@ddivantage.com', 'aweatherforddelta@gmail.com', 'nchan@deniselouie.org', 'liane.martinez@denvergov.org', 'arleneradcliff@hotmail.com', 'monica.garner@drake.edu', 'jkeller@ecenter.org', 'meadem@person.k12.nc.us', 'kstrebig@capmail.org', 'ellenfrechette@earlyeducatorsupport.com', 'lesley.jacobs@sendit.nodak.edu', 'kristen@elncgr.org', 'tjohnson@earlylearningventures.org', 'dmcquade@eastconn.org', 'boyds2@uw.edu', 'kcorn@eckan.org', 'debora.jones@eoacwaco.org', 'shavonad@eoasga.org', 'cslade@escswa.org', 'nina.mckenzie@edcc.edu', 'ahwalker@uw.edu', 'alex@houseofwalker.net', 'fwilliams2@kumc.edu', 'lgould@yorkcpc.org', 'mmeaway@elnidofamilycenters.org', 'juanita.rogers@eoacwaco.org', 'bgriess@esd113.org', 'djenne@esu13.org', 'merdingerl@district65.net', 'cguthrie@ewu.edu', 'sdaniels@fact-inc.com', 'hpeasley@familybuildingblocks.org', 'cdunkerley@ocd.pitt.edu', 'smitchell@fragahs.com', 'tivers@cacfayettecounty.org', 'singley.danielle@gmail.com', 'lharrien@fivecountyhs.org', 'kgallipani@fchsweb.org', 'susan.swager@vistulahs.org', 'sharon_barnes@fcmi-ms.us', 'susan.fenstermacher@yahoo.com', 'ldeshong@fcfpinc.org', 'martina.roe@gcscap.org', 'bmortimer@gecac.org', 'mordaz@gphxul.org', 'wlittletree@puebloofacoma.org', 'nchown@hacap.org', 'karen.heyob@hcesc.org', 'gbowen@privateindustrycouncil.com', 'bnichols@smks-headstart.org', 'norenan@heartlandcaa.org', 'thowland12@hotmail.com', 'lourdes.plunkett@sdhc.k12.fl.us', 'meader@headstart.org', 'kgregory@thehrdc.org', 'andreap@hsicc.org', 'dcilenti@yahoo.com', 'tscott@hsi-headstart.com', 'tammyn@iliffhs.org', 'e.calvin@incacaa.org', 'djohnson@intercountycc.org', 'bsarabando@ironboundcc.org', 'poi24006@isletapueblo.com', 'tjones@jvcai.org', 'ljacobson@msehs.org', 'cyndy56@hotmail.com', 'jbarthelemy@jeffparish.net', 'jgeissman_1@yahoo.com', 'mschmader@jcheadstart.com', 'agriffin@kcsl.org', 'jsorensen@thekac.com', 'rwajdowicz@keyes.k12.ca.us', 'pmccoy@kidcoheadstart.org', 'tmullins@kidscentralinc.com', 'marcy.ash@kcialaska.org', 'fconklin@kotm.org', 'emilyrexroat@yahoo.com', 'monica@kirpc.net', 'jessica.mcauliffe@kfheadstart.org', 'suzanne.inman@knoxvilleheadstart.org', 'kathryn.fields@cardinalservices.org', 'bnull@lbjc.org', 'greene_patricia@lacoe.edu', 'dheald@lguhs.org', 'shartmann@faircaa.org', 'kjones@leadscaa.org', 'lcageao@leakeandwatts.org', 'lallittle@augusta.edu', 'mschuler@lvcap.com', 'hnorris@lucda.org', 'seardley@lucda.org', 'cbenson@lbcec.com', 'ssummersett@marion.k12.in.us', 'gwen.patrick@lklp.net', 'kpeterson@liheadstart.org', 'jbartlebaugh@lccaa.net', 'senekita.farmer@lowcountrycaa.org', 'barbara.scarsbrook@lsfnet.org', 'carmin.davis@macombgov.org', 'kelly.maines@makah.com', 'heidts@mail.maricopa.gov', 'kwortinger@marion.k12.in.us', 'reidr@martin.k12.fl.us', 'jtyler@mrdc.net', 'sweed@maturaia.org', 'anna.sjol@mayvillestate.edu', 'karey.dulaney@mcdowell.k12.nc.us', 'cclark@mchs-ehs.org', 'lrandolph@mecaa.net', 'acarson@mmcaa.org', 'mbhughes11@yahoo.com', 'laura.abbe@micaonline.org', 'jmartinez@ourkidzrock.com', 'jneilson@capslo.org', 'ryan.gress@millelacsband.com', 'sconrad@mnvac.org', 'dchandler@npgov.org', 'tharper@mocacaa.org', 'acox@mvcaa.com', 'debjones@k12.wv.us', 'dmckean@montereycoe.org', 'melissac@macaa.org', 'jwallace@mountainprojects.org', 'crollins@mountainlandheadstart.org', 'tarvig@mountainlandheadstart.org', 'debbie.steiner@murray.kyschools.us', 'jcihak@muskegonisd.org', 'carmeng@mchsok.org', 'lisas@excellencecenters.com', 'rolandawhite@navajohs.org', 'kbinderup@ncoinc.org', 'asidow@ncwvcaa.org', 'jturner@neighborhood-centers.org', 'clichtenheld@nhpdx.org', 'jets@nekcap.org', 'merrill-antcliffv@nemcsa.org', 'lkleader@udel.edu', 'tweber@newopp.org', 'alensch@newopp.org', 'afaris@nrcaa.org', 'danielle.taylor@newstpaul-hs.org', 'helen.frazier@cuny.edu', 'ncssc.mgr.ed@gmail.com', 'jlheinen@nextdoormil.org', 'mthomas@nextdoormke.org', 'ewehri@nocac.org', 'bansotegui@ncesd.k12.or.us', 'yrwyatt@nic.edu', 'mjohnston@neicac.org', 'amanda.stapleton-tuhy@nkcaa.net', 'kmoller@nencap.org', 'sbraun@nesdhs.org', 'jodi.guisto@ntcac.org', 'pwolfe@csiu.org', 'apd@nwaheadstart.org', 'tzimney@nwcaa.org', 'mamartinez@nlmusd.org', 'cbell@oac.ac', 'debbies@ohcac.org', 'vgood@occda.net', 'stefanie.hunter@onslowkids.org', 'llawrence@ofcinc.org', 'cburgess@wbco.net', 'kristine_wilson@oppco.org', 'kacie.fleming@gmail.com', 'lori.butler@ojc.edu', 'sgoebel@ovec.org', 'cfarris@oaiwp.org', 'mogas@pacela.org', 'hs-ehsjfremgen@pacthawaii.org', 'hs-ehseaina@pacthawaii.org', 'mchandler@picaheadstart.org', 'misty.wheeler@capna.org', 'nicole.kimble@passaicheadstart.org', 'rwright@ccgroup.org', 'smcdonald@prvoinc.org', 'pettengillacademy@roadrunner.com', 'tbooze@picca.info', 'shannons@pinoleville-nsn.us', 'vtreadway@pcac-inc.org', 'narinze@pdlr.org', 'mtabanera@pgst.nsn.us', 'mredlevske@promiseearlyeducation.org', 'brenda@rccaa.org', 'kristins@ravalliheadstart.org', 'ksmith@regionalcs.org', 'efinger@reachdane.org', 'mmackedanz@reachupinc.org', 'tcarpenter@esc14.net', 'rosita.ortega@esc15.net', 'melissa.shaver@esc16.net', 'dennis.sarine@esc16.net', 'karla.sprouse@icf.com', 'hjohnson@renewalunlimited.net', 'keelerc@usd308.com', 'cmorrison@rmdc.net', 'sgray@rucd.org', 'ktodriff@rmhsccn.org', 'skranz@cfsheadstart.org', 'skemerer@sisd.cc', 'strojanovich@salidaschools.org', 'msoter@slcap.org', 'ckeckler@sjcoe.net', 'sandy_mckeithan@sccoe.org', 'mkendall@savechildren.org', 'cobrown@savechildren.org', 'cmahnke@savechildren.org', 'nhall@savechildren.org', 'becky.drong@semcac.org', 'pammnaylor@gmail.com', 'kvtaylor@headstart.seta.net', 'lisas@shchildservices.org', 'baranik.ea@augusta.k12.va.us', 'mskirby@ship.edu', 'kthompson@sieda.org', 'jmcculley@headstart4u.org', 'jane.leite@k12.sd.us', 'ariffle@siu.edu', 'rebecca.richter@skagit.edu', 'educationmanager@skylinecap.org', 'daylehs@midstatesd.net', 'sjheadstart@gmail.com', 'jvanblaricum@southsanisd.net', 'jlswinhart@sscac.org', 'lindaw@sek-cap.com', 'jfrey@senca.org', 'jemond@snhs.org', 'gina.dusenbury@socfc.org', 'lundberg@suu.edu', 'jlupkes@smoc.us', 'cwoodard@spcaa.org', 'latoyaorr@centurylink.net', 'mtruiz@stancoe.org', 'sematteo@stepcorp.org', 'shirley.wells@stepincva.com', 'kramsey@sunbeamfamilyservices.org', 'nicole@sunrisechildren.org', 'mballer@tacoma.k12.wa.us', 'wendyco@tallatoonacap.org', 'tonina.rodriguez@tampaymca.org', 'cdennison@twhsp.org', 'mmartineau@teaminc.org', 'raaron@telamon.org', 'dblair@telamon.org', 'b.habibi@childrenscenterciceroberwyn.org', 'dmosley@dciu.org', 'pstamps@thefamilyconservancy.org', 'jbernard@ihsdinc.org', 'bfolarin80@gmail.com', 'marin.rodewald@threeriverscap.org', 'mluke@thrivalaska.com', 'mpickle@badlandshs.org', 'diane.boike@tccaction.com', 'tsolheim@tvoc.org', 'rmartin@triumphinc.org', 'tobyh@tulsaeducare.org', 'rlpecor@uams.edu', 'jprice@uethda.org', 'crequa@umchs.org', 'ltaylor@lccap.org', 'labas@maine.edu', 'joannawi@usc.edu', 'andyh@usd383.org', 'carmen.stewart@usd.edu', 'rwaxler@utaheadstart.com', 'maryw@utetribe.com', 'lcoffey@verneremail.org', 'pmaloney@wadi-inc.com', 'soniap@wacog.com', 'disability@warrencountyheadstart.org', 'bjackson@wcoihs.com', 'mvallee@wvcac.chickasha.ok.us', 'awhite@wvcac.chickasha.ok.us', 'theresa.rowe@washoetribe.us', 'dyantz@wbco.net', 'taplin@westcentralheadstart.org', 'lcoover@wccaheadstart.org', 'jimh@wcmca.org', 'kell@hitinc.org', 'susan.leopold@wcainc.org', 'heather.yates@wdeoc.org', 'vernonp@wicap.org', 'hnicely@westmorelandca.org', 'sherbournec@worc.k12.ma.us', 'marian.moats@wyomingchild.org', 'nicolejohnson@ymaryland.org', 'debrabarrett@ymaryland.org', 'ceinhorn@ydinm.org', 'tbrown@yeled.org', 'amoriarty@youthandfamilyservices.org', 'lpmiller@ymca-cba.org', 'ssanchez@ydinm.org', 'jane.harris@kerrvilleisd.net');
	$org_admins = array(1264, 1588, 2030, 2615, 2578, 1673, 2379, 2505, 1952, 2499, 1297, 556, 551, 1918, 1422, 1811, 1924, 2090, 1679, 2099, 917, 2104, 1836, 2528, 1064, 1973, 2251, 2220, 2198, 1049, 1350, 1416, 867, 2519, 1961, 2157, 1907, 2312, 151, 2093, 868, 2075, 2252, 1797, 979, 2139, 1827, 2444, 1336, 2196, 1880, 1056, 2020, 2496, 1934, 1599, 961, 2036, 2060, 1710, 863, 2254, 1801, 2130, 1637, 893, 2375, 1558, 462, 1789, 1299, 1393, 2165, 986, 1090, 1349, 2145, 1928, 2372, 2365, 839, 940, 2072, 2293, 2156, 1958, 1394, 2079, 772, 2432, 1376, 1072, 2188, 2070, 2053, 1798, 1788, 1781, 879, 2169 , 1689, 2080, 2257, 1450, 1607, 1600, 2345, 1743, 1940, 1912, 1913, 1361, 2115, 129, 933, 1936, 1228, 2194, 1404, 1269, 795, 2034, 1317, 1638, 1302, 2132, 1859, 1701, 561, 1866, 2450, 2058, 1777, 1778, 16, 809, 1784, 2176, 2679, 1693, 1664, 2608, 1906, 1965, 1192, 1828, 2623, 2273, 1826, 1658, 1343, 1587, 2057, 1515, 1578, 1691, 1313, 2131, 1259, 2098, 1316, 1699, 1323, 817, 1254, 1008, 1919, 2367, 2142, 1569, 1568, 2309, 2299, 2593, 2405, 1959, 1929, 1661, 1662, 2082, 1990, 1123, 1005, 2046, 2167, 1842, 2089, 844, 1356, 1751, 2461, 1921, 1806, 1950, 1399, 2451, 1935, 2376, 2709, 1396, 1942 , 1747, 1925, 2311, 2228, 2186, 1977, 1625, 2302, 2427, 1585, 2350, 2135, 2413, 2687, 1976, 1937, 2263, 1954, 1802, 1318, 1872, 1947, 1871, 2103, 2595, 2048, 1543, 2065, 1272, 1995, 2462, 2415, 2577, 1996, 2520, 2301, 1509, 1776, 2085, 1909, 1833, 2077, 2092, 1953, 1423, 301, 2406, 2714, 1608, 2091, 2449, 2341, 2284, 2596, 1755, 2697, 1078, 2295, 1931, 1713, 953, 1243, 2094, 2168, 1989, 2066, 2669, 1779, 2460, 2306, 1592, 256, 820, 2579, 2175, 1821, 249, 1796, 1186, 1618, 1815, 1875, 1300, 259, 1205, 2600, 2111, 2610, 1210, 2025, 1485, 2236, 1234, 1586, 2598, 1832, 2087, 1851, 2417, 1944, 1203, 2470, 2071, 1823, 1785, 1584, 2178, 2465, 1003, 2493, 2164, 1332, 1846, 1151, 1377, 1721, 236, 1911, 2419, 2409, 2395, 1274, 1307, 1193, 1816, 1301, 1249, 1066, 2371, 1158, 2230, 2203, 1991, 1082, 1634, 2100, 1694, 136, 1144, 2459, 807, 1369, 1137, 209, 2285, 2102, 1980, 2580, 1857, 2342, 395, 1703, 1963, 2247, 2812, 1992, 845, 1920, 2435, 2475, 2265, 918, 1126, 1640, 1589, 2078, 1081, 1117, 2128, 2674, 1927, 1814, 2429, 1700, 778, 2223, 1060, 2705, 2346, 1981, 2074, 920, 916, 1411, 1870, 2712, 1926, 776, 1794, 1984, 1032, 2356, 1024, 2123, 2037, 1447, 930, 1669, 1138, 1226, 1294, 929, 2095, 2129, 2270, 235);
	// $i = 1;
	// $start = 404;
	// $range = 100 + $start;
	// foreach ($org_admins as $admin) {
	// 	if ($i < $start) {
	// 		$i++;
	// 		continue;
	// 	}
	// 
	//    	if ($i > $range) {
	// 		break;
	// 	}
	// 	
	// 	$user_id = $page->user->email_to_id($admin);
	// 	
	// 
	// 	echo ', '.$user_id;
	// // 	echo '<br>';
	// 
	// 	$i++;
	// 
	// }

	global $db;
	foreach ($org_admins as $admin) {
		$query = "UPDATE " . TABLE_PREFIX . "users SET role_id = 5 WHERE user_id = $admin";
		$db->query($query);	
	}
				
}




		
// require_once(BASEPATH . 'vce-content/components/resource_library/resource_library.php');
// $rl = NEW ResourceLibrary();
// 
// 
// $input = array("type"=>"ResourceLibrary","datalist_id"=>"44","category_name"=>"dtest3");
// $rl::add_category($input);


		$page->content->add('main', $content);
	
	
	}
	
	
	
	

		

	
	
		/**	
	 * This is a utility which creates an array of groups and the users which belong to them.
     * Then that list is concatenated into a delineated list of users per group and added to
     * datalists_items_meta
	 *
	 */
	public function create_user_list_for_one_group($user_id, $old_group, $new_group) {	
			global $db;
			global $site;
	
// 			get old group list, remove user 
			$query = "SELECT item_id, meta_value FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $old_group";
			foreach ($db->get_data_object($query) as $user_list) {
				$user_list = $user_list->meta_value;
			}
			$user_list_array = explode('|', $user_list);
// 			$site->log($user_list_array);
			if (($key = array_search($user_id, $user_list_array)) !== false) {
   				 unset($user_list_array[$key]);
   				 $user_list = implode('|', $user_list_array);
				$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $old_group AND meta_key = 'user_list'";
				$db->query($query);	
				
			}
			
			if (isset($user_list)) {
				unset($user_list);
			}

// 			$site->log($user_list_array);
			
			
			
			// update new group list
			$query = "SELECT item_id, meta_value FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $new_group";
			foreach ($db->get_data_object($query) as $user_list) {
				$user_list = $user_list->meta_value;
			}
			
			

			if (isset($user_list) && $user_list != '') {
				$user_list_array = explode('|', $user_list);
				if (($key = array_search($user_id, $user_list_array)) == false) {
					$user_list_array[] = $user_id;
					$user_list = implode('|', $user_list_array);

					$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $new_group AND meta_key = 'user_list'";
					$db->query($query);
				}
			} else {
				$query = "INSERT INTO " . TABLE_PREFIX . "datalists_items_meta (item_id, meta_key, meta_value) VALUES ($new_group, 'user_list', '$user_id') ";
				$all_users = $db->query($query);
			}
			
	


}	



	
	/**	
	 * This is a utility which creates an array of groups and the users which belong to them.
     * Then that list is concatenated into a delineated list of users per group and added to
     * datalists_items_meta
	 * 
	 */
	public function create_user_lists_per_group() {	
			global $site;
			global $db;
			// initialize array to store users
			$site_users = array();

			$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";

			$all_users = $db->get_data_object($query);
			
			$all_users_total = $all_users;
		
		
			//array of groups
			$user_organization_group = array();

			foreach ($all_users as $each_user) {
		
				// create array
				$user_object = array();
		
				// add the values into the user object	
				$user_object['user_id'] = $each_user->user_id;
				$user_object['role_id'] = $each_user->role_id;
			
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
				$metadata = $db->get_data_object($query);
			
				// look through metadata
				foreach ($metadata as $each_metadata) {

					//decrypt the values
					$value = user::decryption($each_metadata->meta_value, $each_user->vector);

					// add the values into the user object	
					$user_object[$each_metadata->meta_key] = $db->clean($value);		
				}
				
				
				//Create array of organization, group, user_id
				if (!array_key_exists($user_object['organization'], $user_organization_group)) {
					$user_organization_group[$user_object['organization']] = array();
				}

				if (!array_key_exists($user_object['group'], $user_organization_group[$user_object['organization']])) {
					$user_organization_group[$user_object['organization']][$user_object['group']] = array('user_list' => '');
				}
// 				if (!array_key_exists($user_object['user_id'], $user_organization_group[$user_object['organization']][$user_object['group']])) {
// 					$user_organization_group[$user_object['organization']][$user_object['group']][] = $user_object['user_id'];
					$user_organization_group[$user_object['organization']][$user_object['group']]['user_list'] .= '|'.$user_object['user_id'];
// 				}
				$user_organization_group[$user_object['organization']][$user_object['group']]['user_list'] = trim($user_organization_group[$user_object['organization']][$user_object['group']]['user_list'], '|');

			}

// 	
// 	$site->dump($user_organization_group);
// exit;

	/* This process loops through the $user_organization_group variable and gets the ids of the datalists associated to the groups */
	
	//loop through organizations
	foreach ($user_organization_group as $orgkey => $orgvalue) {
		
			$organization = $orgkey;
			$attributes = array(
			'name' => 'organization'
			);
			$options = $site->get_datalist_items($attributes);
			$datalist_id = $options['datalist_id'];
		
			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $organization) {
						$orgname = $each_option['name'];
					}
				}
			}
		
		
		//loop through groups
		foreach ($user_organization_group[$orgkey] as $groupkey => $groupvalue) {
	
			$group = $groupkey;

			$attributes = array(
			'parent_id' => $datalist_id,
			'item_id' => $organization
			);
		
			$options = $site->get_datalist_items($attributes);

			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $group) {
						//this is where the group has been related to its user_id list
						$user_list = $groupvalue['user_list'];
						
						
						//check to see if list exists or needs to be created
						$query = "SELECT item_id FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $group AND meta_key  = 'user_list'";
						foreach ($db->get_data_object($query) as $group_id) {
							$user_list_id = $group_id->item_id;
						}

						
						if (isset($user_list_id) && $user_list_id > 0) {
							$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $user_list_id AND meta_key = 'user_list'";
							$all_users = $db->query($query);	
							$site->dump('UPDATED: '.$group.' '.$each_option['name'].' '.$orgkey.' '.$orgname.' '.$groupvalue['user_list'].' '.$user_list_id);
	
						} else {
							$query = "INSERT INTO " . TABLE_PREFIX . "datalists_items_meta (item_id, meta_key, meta_value) VALUES ($group, 'user_list', '".$user_list."') ";
							$all_users = $db->query($query);
							$site->dump('INSERTED: '.$group.' '.$each_option['name'].' '.$orgkey.' '.$orgname.' '.$groupvalue['user_list']);

						}
					}
				}
			}
		}
	}	
}		
		



	
	
/**
 * add items to datalists, and create a new datalist for the item
 */
public function add_category($input) {
	
		global $site;
		
// 		$site->log($input);
		
		// get the datalist associated with this component

		$category_name = trim($input['category_name']);
		
		// check to see if item_id is already a datalist
		
		if (isset($input['item_id'])) {
		
			$attributes = array (
			'item_id' => $input['item_id']
			);
		
			$datalist =	$site->get_datalist($attributes);
		
			// if there is no datalist_id associated with the item_id, then make one!
			if (!empty($datalist)) {
		
				$datalist_id = current($datalist)['datalist_id'];
		
			} else {
		
				$attributes = array (
				'item_id' => $input['item_id'],
				'parent_id' => $input['datalist_id'],
				'datalist' => 'sub_category_' . $input['item_id'],
				);
	 
				$datalist_id = $site->create_datalist($attributes);

			}
		
		} else {
		
			// primaray level
			$datalist_id = $input['datalist_id'];
		
		}
		
		$attributes = array (
	 	'datalist_id' => $datalist_id,
	 	'items' => array ( array ('sequence' => 0, 'category_name' => $category_name) )
	 	);
		
	 	$site->insert_datalist_items($attributes);
	 	
		$site->add_attributes('taxonomy_update','true');

		echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','message' => 'Created'));
		return;
	}
	
	
	/**
	 * delete a category
	 */
	public function delete_category($input) {
		
		global $site;
		
		$attributes = array (
		'item_id' => $input
		);
		
		$site->remove_datalist($attributes);
		
		$site->add_attributes('taxonomy_update','true');
	
		echo json_encode(array('response' => 'success','procedure' => 'update','message' => 'Updated'));
		return;


}

public function migrate_db() {

//create connection to 2nd (ncqtl dump) db for migration
/* The name of the database */
$mysql_database = DB_NAME;
/* MySQL database username */
$mysql_username = DB_USER;
/* MySQL database password */
$mysql_password = DB_PASSWORD;
/* MySQL hostname */
$mysql_host = DB_HOST;
//get next user to migrate
$db2 = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database) or die('Error connecting to MySQL server: ' . mysql_error());

return $db2;

}

public function create_sub_organizations($input) {
		global $db;
		//explode input array
		foreach ($input as $key=>$value){
			$$key = $value;
		}

		
		// write to datalist
		global $site;
// 		$site->log($input);
		
		
		$site->add_attributes('datalist_id',$input['datalist_id']);
		$site->add_datalist_item($input);
		

		
		
		// write into organization_migration table 
		$query = "INSERT INTO organization_migration (org_id, org_name, parent_id) VALUES ('$org_id','$org_name', '$parent_id')";
		$db->query($query);
		
		echo json_encode(array('response' => 'success','message' => 'Sub Organization has been created','form' => 'create','action' => ''));
		return;
}


public function create_organizations($input) {
		global $db;
		//explode input array
		foreach ($input as $key=>$value){
			$$key = $value;
		}

		
		// write to datalist
		global $site;
// 		$site->log($input);
		
		
		$site->add_attributes('datalist_id',$input['datalist_id']);
		$new_dl_id = $site->add_datalist_item($input);
		
// 				get last datalist id 
// 		$query = "SELECT b.datalist_id as dl_id FROM vce_datalists_items_meta as a join vce_datalists as b on a.item_id = b.item_id WHERE a.meta_value = ".$input['org_name'];
// 		$result = $db->query($query);
// 		foreach($result as $r) {
// 			$dl_group_parent_id = $r['dl_id'];
// 		}
		
		$input['datalist_id'] = $new_dl_id;
		$input['name'] = 'default';
		
		$site->add_attributes('datalist_id',$input['datalist_id']);
		$site->add_datalist_item($input);
		
		// write into organization_migration table 
		$query = "INSERT INTO organization_migration (org_id, org_name) VALUES ('$org_id','$org_name')";
		$db->query($query);
		
		echo json_encode(array('response' => 'success','message' => 'Organization has been created','form' => 'create','action' => ''));
		return;
}

public function show_migration_taxonomy($input) {

	$db2 = $this->migrate_db();
	//get categories from migrate DB
	$tax = array();

	$level = 1;
	$query = "SELECT cat_name, cat_id, cat_parent_id FROM resource_cats WHERE cat_parent_id is null ORDER BY cat_name ASC";
	$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
	foreach ($result as $r) {
		$tax[]=array('id' => $r['cat_id'], 'name' =>$r['cat_name'], 'children'.$level => array());
	}

	// $tax['level1'] = array();
	$i = 0;
	$level = 2;
	foreach ($tax as $a) {
		$query = "SELECT cat_name, cat_id, cat_parent_id FROM resource_cats WHERE cat_parent_id = ".$a['id']." ORDER BY cat_name ASC";
		$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
		foreach ($result as $r) {
			$tax[$i]['children1'][] = array('id' => $r['cat_id'], 'name' =>$r['cat_name'], 'children'.$level => array());
		}
		$i++;
	}

	$i = 0;
	$level = 3;
	foreach ($tax as $a) {
		$ii = 0;
		foreach ($a['children1'] as $b) {
			$query = "SELECT cat_name, cat_id, cat_parent_id FROM resource_cats WHERE cat_parent_id = ".$b['id']." ORDER BY cat_name ASC";
			$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
			foreach ($result as $r) {
				$tax[$i]['children1'][$ii]['children2'][] = array('id' => $r['cat_id'], 'name' =>$r['cat_name'], 'children'.$level => array());
			}
			$ii++;
		}
		$i++;
	}


	$i = 0;
	$level = 4;
	foreach ($tax as $a) {
		$ii = 0;
		foreach ($a['children1'] as $b) {
			$iii = 0;
			foreach ($b['children2'] as $c) {
				$query = "SELECT cat_name, cat_id, cat_parent_id FROM resource_cats WHERE cat_parent_id = ".$c['id']." ORDER BY cat_name ASC";
				$result = mysqli_query($db2, $query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db2) . '<br /><br />');
				foreach ($result as $r) {
					$tax[$i]['children1'][$ii]['children2'][$iii]['children3'][] = array('id' => $r['cat_id'], 'name' =>$r['cat_name'], 'children'.$level => array());
				}
				$iii++;
			}
			$ii++;
		}
		$i++;
	}

$tax_output = '<table id = "show-data-table" style="width:100%; border:4px">';
	foreach ($tax as $a) {
		$tax_output .= "<tr>";
			$tax_output .= "<td>".$a['name']."</td><td>-</td><td>-</td><td>-</td>";
		$tax_output .= "</tr>";
		foreach ($a['children1'] as $b) {
			$tax_output .= "<tr>";
				$tax_output .= "<td>-</td><td>".$b['name'].$b['id']."</td><td>-</td><td>-</td>";
			$tax_output .= "</tr>";
// 			echo '&nbsp;&nbsp;_'.$b['name'].$b['id'].'<br>';
			foreach ($b['children2'] as $c) {
				$tax_output .= "<tr>";
					$tax_output .= "<td>-</td><td>-</td><td>".$c['name'].$c['id']."</td><td>-</td>";
				$tax_output .= "</tr>";
// 				echo '&nbsp;&nbsp;&nbsp;&nbsp;____'.$c['name'].$c['id'].'<br>';
				foreach ($c['children3'] as $d) {
					$tax_output .= "<tr>";
						$tax_output .= "<td>-</td><td>-</td><td>-</td><td>".$c['name'].$c['id']."</td>";
					$tax_output .= "</tr>";					
// 					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;________'.$d['name'].'<br>';
				}
			}
		}

	}	
$tax_output .= "</table>";

// $this->write_csv_from_array($tax_output, BASEPATH.'taxonomy.csv');


$action =  dirname(__FILE__) . '/js/script.js';

	echo json_encode(array('response' => 'success','message' => $tax_output,'form' => 'show_data','action' => ''));
	return;


}

// public function write_csv_from_array($array, $filename) {
//   $fp = fopen($filename, 'w');
//     foreach($array as $arr) {
//       foreach($arr as $a) {
//          if(is_array($a)) {
//            return $this->write_csv_from_array($arr, $filename);
//          } else {
//            fputcsv($fp, $arr);
//          }
//       }
//     }
//   fclose($fp);
// }

public function get_category_id($cat_name, $page) {
	$taxonomy = $page->site->get_datalist(array('datalist' => 'resource_library_taxonomy'));	
	$datalist_id = current($taxonomy)['datalist_id'];

	$taxonomy_items = $page->site->get_datalist_items(array('datalist_id' => $datalist_id));
	foreach ($taxonomy_items['items'] as $item) {
		if ($item['category_name'] == $cat_name) {
			return $item['item_id'];
		}
		return 0;
	}
	
	return $datalist_item_id;
}

public function delete_users($input) {

	$first_user_to_delete = $input['first_user_to_delete'];
	$number_users_to_delete = $input['number_users_to_delete'];
	
	
		//get set of id's to cycle through
		global $db;
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id >= ".$input['first_user_to_delete'];
		$users_to_delete = $db->get_data_object($query);
		
		$i = 1;
		//cycle through id's
		foreach ($users_to_delete as $each_user_to_delete) {
			//extirpate components
			$query = "SELECT a.component_id FROM " . TABLE_PREFIX . "vce_components AS a LEFT JOIN " . TABLE_PREFIX . "vce_components_meta as b ON a.component_id = b.component_id WHERE  b.meta_key = 'created_by' AND b.meta_value >= ".$input['first_user_to_delete'];
			$components_to_delete = $db->get_data_object($query);
			foreach ($components_to_delete as $each_component_to_delete) {
				$this->extirpate_component($each_component_to_delete);
			}
			
					// delete user from database
			$where = array('user_id' => $each_user_to_delete->user_id);
			$db->delete('users', $where);
		
			// delete user from database
			$where = array('user_id' => $each_user_to_delete->user_id);
			$db->delete('users_meta', $where);
			
			
			$i++;
			if ($i > $number_users_to_delete) {
				break;
			}

		}
					
			$query = "SELECT max(user_id) as max_id from vce_users";
			$max_id = $db->get_data_object($query);
			$next_id = $max_id[0]->max_id + 1;
			$query = "ALTER TABLE vce_users AUTO_INCREMENT = " . $next_id;
			$db->query($query);
			$this->empty_user_migration_table('no input necessary');
			
		echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
		return;
}

private function show_vce_taxonomy() {	
	$taxonomy = $page->site->get_datalist(array('datalist' => 'resource_library_taxonomy'));
		
	$datalist_id = current($taxonomy)['datalist_id'];

	// this will delete all categories by deleting the primary categories.
	$taxonomy_items = $site->get_datalist_items(array('datalist_id' => $datalist_id));
	foreach ($taxonomy_items['items'] as $item) {
// 		$site->dump($item);
	// 	$this->delete_category($item['item_id']);
	}	
	return $taxonomy_items['items'];
}



public function check_last_migrated_user() {
	global $db;
	$query = "SELECT user_id as last_user_id, email FROM user_migration ORDER BY last_user_id DESC LIMIT 1";
	$result = $db->get_data_object($query);

	if (!empty($result)) {
		$id = $result[0]->last_user_id;
		$id = (isset($id) ? $id : 0);
		$email = $result[0]->email;
		$last_migrated_user = array($id, $email);
		return $last_migrated_user;
	}
	return array(0, 'none');

}


public function check_last_migrated_video() {
	global $db;
	$query = "SELECT video_id as last_video_id, vid_name FROM video_migration2 ORDER BY last_video_id DESC LIMIT 1";
	$result = $db->get_data_object($query);

	if (!empty($result)) {
		$id = $result[0]->last_video_id;
		$id = (isset($id) ? $id : 0);
		$vid_name = $result[0]->vid_name;
		$last_migrated_video = array($id, $vid_name);
		return $last_migrated_video;
	}
	return array(0, 'none');

}


public function check_last_migrated_organization() {
	global $db;
	$query = "SELECT org_id as last_org_id, org_name FROM organization_migration ORDER BY last_org_id DESC LIMIT 1";
	$result = $db->get_data_object($query);

	if (!empty($result)) {
		$org_id = $result[0]->last_org_id;
		$org_id = (isset($org_id) ? $org_id : 0);
		$org_name = $result[0]->org_name;
		$last_migrated_organization = array($org_id, $org_name);
		return $last_migrated_organization;
	}
	return array(0, 'none');

}

public function check_last_migrated_sub_organization() {
	global $db;
	$query = "SELECT org_id as last_org_id, org_name FROM organization_migration WHERE parent_id IS NOT NULL ORDER BY last_org_id DESC LIMIT 1";
	$result = $db->get_data_object($query);

	if (!empty($result)) {
		$org_id = $result[0]->last_org_id;
		$org_id = (isset($org_id) ? $org_id : 0);
		$org_name = $result[0]->org_name;
		$last_migrated_organization = array($org_id, $org_name);
		return $last_migrated_organization;
	}
	return array(0, 'none');

}



public function define_config_value($input) {
	$constant_name = $input['constant_name'];
	$constant_value = $input['constant_value'];
	$filename = $input['filename'];
	$reading = fopen($filename, 'r');
	$writing = fopen($filename.'temp', 'w');
	$replaced = FALSE;

	while (!feof($reading)) {
  			$line = fgets($reading);
  		if (strstr($line, $constant_name) && strstr($line, 'define')) {
//   			echo '<br>cn: '.$constant_name.'<br>cv: '.$constant_value;

   			 $line = "define('".$constant_name."', '".$constant_value."');".PHP_EOL;
   			 $replaced = true;
 		}
 		fputs($writing, $line);
	}
	if ($replaced == FALSE && !empty($constant_name)) {

		 $line = "define('".$constant_name."', '".$constant_value."');".PHP_EOL;
		 $replaced = true;
		 fputs($writing, $line);
	}

	fclose($reading); fclose($writing);
	// might as well not overwrite the file if we didn't replace anything
	if ($replaced == true) {
// 		unlink(BASEPATH.'vce-config.php');
  		rename($filename.'temp', $filename);
	} else {
 		unlink($filename.'temp');
	}
	
// 	  echo json_encode(array('response' => 'success','message' => 'Value '.$constant_name.' has been edited.','form' => 'create','action' => ''));
		return $constant_value;
	
}



public function empty_user_migration_table($input) {
		global $db;
		$query = "TRUNCATE TABLE user_migration";
		$db->query($query);
	  echo json_encode(array('response' => 'success','message' => 'user_migration table has been emptied.','form' => 'create','action' => ''));
		return;

}






public function create_user($input) {
// global $site;
// $site->log($input);
// 	return;
		global $db;
		//explode input array
		foreach ($input as $key=>$value){
			$$key = $value;
		}
		
		// write into user_migration table 
		$query = "INSERT INTO user_migration (user_id, email, user_role, user_organization, user_group) VALUES ('$user_id','$email','$role_id','$organization','$group')";
		$db->query($query);
		// remove type so that it's not created for new user
		unset($input['type']);
		// remove user_id so that it's not created for new user
		unset($input['user_id']);
		
		
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = user::lookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = user::create_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = user::create_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $db->insert('users', $user_data);
		
		unset($input['procedure']);
		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = user::lookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = user::encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}		
		
		$db->insert('users_meta', $records);

		echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));
		return;
	}


/**
 * Checks constant values in the config.php file.
 * Uses PHP's "token_get_all" to look at all the defined constants in vce_config.php
 * @param string $constant_name
 * @return mixed $constant_value
 */
public function check_config_value($constant_name, $filename) {
	$defines = array();
	$state = 0;
	$key = '';
	$value = '';

	$file = file_get_contents($filename);
	$tokens = token_get_all($file);
	$token = reset($tokens);
	while ($token) {
    	if (is_array($token)) {
       	 if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
           	 // do nothing
       	 } else if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
            $state = 1;
       	 } else if ($state == 2 && $this::is_constant($token[0])) {
       	     $key = $token[1];
       	     $state = 3;
      	  } else if ($state == 4 && $this::is_constant($token[0])) {
      	      $value = $token[1];
       	     $state = 5;
      	  }
   	 } else {
     	   $symbol = trim($token);
     	   if ($symbol == '(' && $state == 1) {
     	       $state = 2;
     	   } else if ($symbol == ',' && $state == 3) {
     	       $state = 4;
     	   } else if ($symbol == ')' && $state == 5) {
     	       $defines[$this::strip($key)] = $this::strip($value);
     	       $state = 0;
     	   }
   	 }
  	  $token = next($tokens);
	}
	//checks constant existance and returns value if exists
	foreach ($defines as $k => $v) {
		if ($constant_name == $k) {
//   	 	 	echo "'$k' => '$v'\n";
  	 	 	return $v;
  	 	 } 
	}
	return 'There is no constant with that name.';
}	

/**
 * Checks if token is constant.
 * Called from check_config_value().
 * @param mixed $token
 * @return mixed $token
 */
public function is_constant($token) {
    return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
        $token == T_LNUMBER || $token == T_DNUMBER;
}


/**
 * Strips constant value.
 * Called from check_config_value().
 * @param mixed $value
 * @return mixed $value
 */
public function strip($value) {
	  return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
}

	
    // Defining the basic cURL function
   public function curl($post_string, $post_array) {
		set_time_limit(0);
		//Here is the file we are downloading, replace spaces with %20
		$ch = curl_init('http://localhost:8888/vceOHSCCGIT/vce-input.php');
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch,CURLOPT_POST, count($post_array));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $post_string);
		
// 			curl_setopt($ch, CURLOPT_HEADER, true);
		// write curl response to file
// 		curl_setopt($ch, CURLOPT_FILE, $fp); 
// 		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// get curl response
		$result = curl_exec($ch); 
		curl_close($ch);
// 		fclose($fp);
		return $result;
		
	}

	
	/**
	 * Create a new user
	 */
	public function createBAK($input) {
	
		global $db;
	
		// remove type so that it's not created for new user
		unset($input['type']);
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = user::lookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = user::create_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = user::create_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $db->insert('users', $user_data);
		
		unset($input['procedure']);
		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = user::lookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = user::encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}		
		
		$db->insert('users_meta', $records);

		echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));
		return;
	}
	
	
		/**
	 * add participant to list of users who can be added to a cycle
	 */
	public function add_participant($input) {

		// add attributes to page object for next page load using session
		global $page;
		global $site;
		global $user;
		$participants  = array();
// 		$site->log($input);
// 		exit;
		$dl_name = 'participants_of_id_'.$user->user_id;

		$current_datalists = $site->get_datalist(array('datalist' => $dl_name));

		if ($current_datalists != false) {
			// create arrays of user_ids that have been added to both observer and observed lists for this component	
			foreach ($current_datalists as $each_datalist_key=>$each_datalist_value) {
				//implode()adds a delimiter if the value is set but equal to nothing. An empty datalist meta item is set, making this and the next check necessary.
				if (isset($each_datalist_value['participants']) && $each_datalist_value['participants'] != "") {
					$participants  = explode('|', $each_datalist_value['participants']);
// 					$site->log($participants);
				}
				if (isset($each_datalist_value['datalist_id'])) {
					$dl_id = $each_datalist_value['datalist_id'];
				}
			}

				if (!in_array($input['user_id'], $participants)) {
					
						$participants[] = $input['user_id'];
					if (count($participants) > 1) {
						$updated_participants =	implode('|' , $participants);
					} else {
						$updated_participants =	$participants[0];
					}
				}
				$attributes = array (
					'datalist_id' => $dl_id ,
					'meta_data' => array ('participants' => $updated_participants)				
				);	

				$site->update_datalist($attributes);
			

			
		} else {
			// create datalist
			$dl_ids = array();
			$attributes = array (
			'datalist' => $dl_name,
			'aspects' => array('type' => 'component_list','name' => $dl_name, 'participants' => $input['user_id'])
			);	

					$dl_ids[] = $site->create_datalist($attributes);
		
		}
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}
	
	/**
	 * remove participant to list of users who can be added to a cycle
	 */
	public function remove_participant($input) {

		// add attributes to page object for next page load using session
		global $page;
		global $site;
		global $user;

		$dl_name = 'participants_of_id_'.$user->user_id;

		$current_datalists = $site->get_datalist(array('datalist' => $dl_name));

		if ($current_datalists != false) {
			// create arrays of user_ids that have been added to both observer and observed lists for this component	
			foreach ($current_datalists as $this_datalist) {
				$participants  = explode('|', $this_datalist['participants']);
			}	

				$key = array_search($input['user_id'], $participants);
				unset($participants[$key]);
				$updated_participants =	implode('|', $participants);
				$attributes = array (
					'datalist_id' => $this_datalist['datalist_id'],
					'meta_data' => array ('participants' => $updated_participants)				
				);	

				$site->update_datalist($attributes);
			
			}
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
	}

	/**
	 * edit user
	 */
	public function edit($input) {

		// add attributes to page object for next page load using session
		global $site;
		
		$site->add_attributes('user_id',$input['user_id']);
	
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}

	/**
	 * update user
	 */
	public function update($input) {
	
		global $db;
	
		$user_id = $input['user_id'];
	
		$query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
		$user_info = $db->get_data_object($query);
		
		$role_id = $user_info[0]->role_id;
		$vector = $user_info[0]->vector;
		
		// has role_id been updated?
		if ($input['role_id'] != $role_id) {

			$update = array('role_id' => $input['role_id']);
			$update_where = array('user_id' => $user_id);
			$db->update('users', $update, $update_where );

		}
		
		// clean up
		unset($input['procedure'],$input['role_id'],$input['user_id']);
		
		// delete old meta data
		foreach ($input as $key => $value) {
				
			// delete user meta from database
			$where = array('user_id' => $user_id, 'meta_key' => $key);
			$db->delete('users_meta', $where);
		
		}
		
		// now add meta data
		
		$records = array();
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = user::encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}
		
		$db->insert('users_meta', $records);
				
		echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
		return;
	
	}	

	
	/**
	 * Masquerade as user
	 */
	public function masquerade($input) {
	
		global $user;
			
		// pass user id to masquerade as
		$user->make_user_object($input['user_id']);
		
		global $site;
		
		echo json_encode(array('response' => 'success','message' => 'User masquerade','form' => 'masquerade','action' => $site->site_url));
		return;
	
	}	
	
	
	/**
	 * Delete a user
	 */
	public function delete($input) {
	
		global $db;
	
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$db->delete('users', $where);
		
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$db->delete('users_meta', $where);
		
		echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
		return;
	
	}
	
	public function replace_extension($filename, $new_extension) {
		$info = pathinfo($filename);
		return $info['filename'] . '.' . $new_extension;
// 		return $info['dirname']."/".$info['filename'] . '.' . $new_extension;
// 		rename($oldname, $newname);
	}
	
	
	
	public static function instantiate_vimeo() {
		$VIMEO_CLIENT_ID = '248b3f99eaf0c84739ac0823a1e2b67e237cb2f4';
		$VIMEO_CLIENT_SECRET = 'Pmxyy0g2xXvL7z5PjrxRqnlgVnhDH2OGRKS8l8jmi7WzctJv91IgPbnCkrQTztf7IMX8RXWJF5gm7Ohm7c231OwAMFHFPBtjtQgn6WoXf4oi8q';
		$VIMEO_ACCESS_TOKEN = '2eb5c383d4f41340eaea648545522d21';
		$VIMEO_DOMAIN_PRIVACY_SETTING = 'localhost:8888';


		//All API methods are within the Vimeo.php file
        require_once(dirname(__FILE__) . '/Vimeo/Vimeo.php');
        $vimeo = new Vimeo($VIMEO_CLIENT_ID, $VIMEO_CLIENT_SECRET, $VIMEO_ACCESS_TOKEN);
		return $vimeo;
	}
	
	public static function list_vimeo_videos() {
		//instantiate API class
		$vimeo = self::instantiate_vimeo();
		$userId = '10814183';
// 		$videos = $vimeo->request("/users/$userId/videos", ['per_page' => 99]);
		$videos = $vimeo->request("/videos/", ['per_page' => 99]);
		
		return $videos;
		$v = array();
		foreach($videos['body']['data'] as $video) {
			$v[] = $video['uri'];
		}
		return $v;
		
		// loop through each video from the user
		foreach($videos['body']['data'] as $video) {

			// get the link to the video
			$link = $video['link'];

			// get the largest picture "thumb"
			$pictures = $video['pictures']['sizes'];
			$largestPicture = $pictures[count($pictures) - 1]['link'];
		}
	
	}
	public static function delete_video($input) {
	
		
			global $db;	

			$vimeo = self::instantiate_vimeo();
			
			$query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='guid'";
			$guid_data = $db->get_data_object($query);
			$guid = $guid_data[0]->meta_value;

        	$vimeo->request('/videos/' . $guid, array(), 'DELETE');
        	

		
		return $input;
	}
	
	/**
	 * create Vimeo Video
	 */
	public function migrate_videos($input) {
	
	global $site;
// 	$site->log($input);
// 	return;
		global $db;
		// write into video_migration table 
		$mediaAmp_id = "'".$input['mediaAmp_id']."'";
		$vid_name = "'".stripslashes($input['vid_name'])."'";
		$owner = $input['video_owner'];
		$path = "'".stripslashes($input['path'])."'";
		$video_id = $input['video_id'];

		$query = "INSERT INTO video_migration2 (mediaAmp_id, vid_name, owner, path, video_id) VALUES ($mediaAmp_id, $vid_name, $owner, $path, $video_id)";
// 		$site->log($path);



			//instantiate API class
			$vimeo = self::instantiate_vimeo();
			
			// check if this is a using a common install
			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			
		 	// construct full path to video
// 			$uploaded_video_file = $basepath . PATH_TO_UPLOADS . '/' . $input['created_by'] . '/' . $input['path'];		

			//  Send the files to the upload script.
			try {
				//  Send this to the API library.
				$path2 = stripslashes($input['path']);
// 				$site->log($path2);
				$uri = $vimeo->upload($path2);

				//  Now that we know where it is in the API, let's get the info about it so we can find the link.
				$video_data = $vimeo->request($uri);
				//  The script will pause until the upload is complete
				$link = '';
				if ($video_data['status'] == 200) {
					$link = $video_data['body']['link'];
					$videoID = str_replace('/videos/', '', $video_data['body']['uri']);
					// change name of video to title from input
 					// $vimeo->request('/videos/'.$videoID, array('name' => $input['title']), 'PATCH');
					// set privacy settings  
					// $vimeo->request('/videos/'.$videoID, array('privacy' => array('view' => 'unlisted')), 'PATCH');
					// $vimeo->request('/videos/'.$videoID, array('privacy' => array('download' => 'false')), 'PATCH');
					$vimeo->request('/videos/'.$videoID, array('name' => $vid_name, 'privacy' => array('download' => 'false', 'view' => 'unlisted'), 'embed' => array('buttons' => array('share' => 'false', 'watchlater' => 'false', 'like' => 'false', 'embed' => 'false'))), 'PATCH');

					// these two lines are for setting the privacy 'embed' settings. The first sets correctly to "whitelist".
					// the second doesn't work because I can't find the right syntax. When it does, it will be set to the specified
					// domain which is set in the admin section.
					// $vimeo->request('/videos/'.$videoID, array('privacy' => array('embed' => 'whitelist')), 'PATCH');
					// $vimeo->request('/videos/'.$videoID, array('privacy' => array('domains' => $vimeo_domain_privacy_setting)), 'PATCH');
				}
			}
			catch (Exception $e) {
			
				if (!isset($uri)){
					$errors = "'Error: VimeoVideo did not return a URI".$e->getMessage()."'";
					$query = "INSERT INTO video_migration2 (mediaAmp_id, vid_name, owner, path, video_id, errors) VALUES ($mediaAmp_id, $vid_name, $owner, $path, $video_id, $errors)";
					$db->query($query);
// 					echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: VimeoVideo did not return a URI"));
					echo json_encode(array('response' => 'error','message' => $errors,'form' => 'create','action' => ''));

					return;
				}
				$errors = "'Error: There has been an upload error. The server reports: ".$e->getMessage()."'";
				$query = "INSERT INTO video_migration2 (mediaAmp_id, vid_name, owner, path, video_id, errors) VALUES ($mediaAmp_id, $vid_name, $owner, $path, $video_id, $errors)";
				$db->query($query);
				//  We may have had an error.  We can't resolve it here necessarily, so report it to the user.
				echo json_encode(array('response' => 'error','message' => "Error: There has been an upload error. The server reports: ".$e->getMessage(),'form' => 'create','action' => ''));

				return;	
			}
		
			if (isset($videoID)) {

				$input['guid'] = $videoID;
					
					$db->query($query);
				
					$query = "UPDATE video_migration2 SET guid=$videoID WHERE video_id = $video_id";
					$db->query($query);
	
				
			} else {
					$errors = "'Error: VimeoVideo did not return a URI 2'";
					$query = "INSERT INTO video_migration2 (mediaAmp_id, vid_name, owner, path, video_id, errors) VALUES ($mediaAmp_id, $vid_name, $owner, $path, $video_id, $errors)";
					$db->query($query);
			
					echo json_encode(array('response' => 'error','message' => "Error: VimeoVideo did not return a URI 2",'form' => 'create','action' => ''));
				return;	
			
			}		
		

		
// 		return $input;

		
		echo json_encode(array('response' => 'success','message' => 'Uploaded video: '.$vid_name,'form' => 'create','action' => ''));
		return;
	
	}	
	
	public function custom_create_component($input) {
	
		global $db;
		global $site;
		global $user;
		
		$input['type'] = 'Media';
		

		$original_id = $input['original_id'];
		$query = "UPDATE video_migration SET created='yes' WHERE video_id = $original_id";
		$db->query($query);
// 	{"type":"Media","parent_id":"113","sequence":1,"name":"2.28.mov.mp4","created_by":"13","title":"2","description":"asdf","taxonomy":"|1746|","media_type":"VimeoVideo","path":"13_1509492137.mp4","guid":"240756413"}
		// add created by and created at time_stamp
// 		$input['created_by'] = $user->user_id;
		$input['created_at'] = time();
		
		// set $auto_create
		$auto_create = isset($input['auto_create']) ? $input['auto_create'] : null;
		unset($input['auto_create']);
		
		// anonymous function to create components
		$create_component = function($input) use (&$create_component, $db, $site, $user) {

		 	// local version of $input, which should not be confused with the $input fed to the create_component method
		
			// create_component_before hook
			if (isset($site->hooks['create_component_before'])) {
				foreach($site->hooks['create_component_before'] as $hook) {
					$input = call_user_func($hook, $input);
				}
			}
			
			// clean up url
			if (isset($input['url'])) {
				$input['url'] = $site->url_checker($input['url']);
			}
			
			// create component data
			$parent_id = isset($input['parent_id']) ? $input['parent_id'] : 0;
			$sequence = isset($input['sequence']) ? $input['sequence'] : 1;
			$url = isset($input['url']) ? stripslashes($input['url']) : '';
			// $current_url = isset($input['current_url']) ? $input['current_url'] : '';
		
			unset($input['parent_id'], $input['sequence'], $input['url'], $input['current_url']);
	
			$data = array(
			'parent_id' => $parent_id, 
			'sequence' => $sequence,
			'url' => $url
			);
		
			// insert into components table, which returns new component id
			$component_id = $db->insert('components', $data);

			// now add meta data
			$records = array();

			// loop through other meta data
			foreach ($input as $key=>$value) {
		
				// title
				$records[] = array(
				'component_id' => $component_id,
				'meta_key' => $key, 
				'meta_value' => $value,
				'minutia' => null
				);
		
			}

			$db->insert('components_meta', $records);
			
			
// 			$site->log('normal create');
		echo json_encode(array('response' => 'success','message' => 'Created video component: '.$input['name'],'form' => 'create','action' => ''));
		return;
// 			return $component_id;
			
		};
	
		// anonymous function to create auto_create components
		$auto_create_components = function($auto_create, $input, $direction) use (&$auto_create_components, $site, $create_component) {

			if (!empty($auto_create)) {
// 			$site->log('auto create');
				// set counter
				$counter = 0;
				foreach ($auto_create as $each_key=>$each_component) {
					
					if (!isset($each_component['auto_create'])) {
						continue;
					}
				
					if (isset($each_component['components'])) {
						$sub_auto_create = $each_component['components'];
					} else {
						$sub_auto_create = null;
					}
		
					if ($direction == "reverse" && $each_component['auto_create'] == "reverse") {
					
						// add to counter
						$counter++;
						
						// unset sub components and auto_create 
						unset($auto_create[$each_key]['components'],$auto_create[$each_key]['auto_create']);
						
						$new_component = array();
						
						// update input from recipe
						foreach ($auto_create[$each_key] as $meta_key=>$meta_value) {
							$new_component[$meta_key] = $meta_value;
						}
						
						// create separate sequence space in case
						$new_component['sequence'] = $counter;
						
						// add required fields
						$new_component['parent_id'] = $input['parent_id'];
						$new_component['created_by'] = $input['created_by'];
						$new_component['created_at'] = $input['created_at'];
						
						// call and then return the component_id
						$new_component_id = $create_component($new_component);
									
						// check that component has not been disabled
						$activated_components = json_decode($site->activated_components, true);

						// check that this component has been activated
						if (isset($activated_components[$new_component['type']])) {
							require_once(BASEPATH . $activated_components[$new_component['type']]);
						}
		
						// add component_id to new_component
						$new_component['component_id'] = $new_component_id;
		
						//  add auto_create to new_component
						$new_component['auto_create'] = $each_component['auto_create'];
		
						// call to auto_created
						$new_component['type']::auto_created($new_component);
		
						return $new_component_id;
					
					}
					
					if ($direction == "forward" && $each_component['auto_create'] == "forward") {
						
						// add to counter, for use with sequence
						$counter++;
						
						// clear array and start again
						$new_component = array();
						
						// keep track of how many instances of the same component occur at this level, so that a recipe_key can be added if needed
						if (!isset($recipe_type)) {
							// loop through the first time to find multiples
							foreach ($auto_create as $recipe_component) {
								$recipe_type[$recipe_component['type']] = isset($recipe_type[$recipe_component['type']]) ? ($recipe_type[$recipe_component['type']] + 1) : 0;
							}
						}

						// if multipes have been found, add $recipe_key
						if ($recipe_type[$each_component['type']] > 0) {
							if (!isset($recipe_key[$each_component['type']])) {
								$recipe_key[$each_component['type']] = 0;
							} else {
								$recipe_key[$each_component['type']] = $recipe_key[$each_component['type']] + 1;
							}
							// add meta_key to each_sub_components
							$new_component['recipe_key'] = $recipe_key[$each_component['type']];
						}
						
				
						// create separate sequence space in case
						$new_component['sequence'] = $counter;

						// unset sub components and auto_create 
						unset($auto_create[$each_key]['components'],$auto_create[$each_key]['auto_create']);
						
						// update input from recipe
						foreach ($auto_create[$each_key] as $meta_key=>$meta_value) {
							// prevent overwriting
							if (!isset($new_component[$meta_key])) {
								$new_component[$meta_key] = $meta_value;
							}
						}
						
						// add required fields
						$new_component['parent_id'] = $input['parent_id'];
						$new_component['created_by'] = $input['created_by'];
						$new_component['created_at'] = $input['created_at'];
						
						// create a sub url
						if (isset($each_component['url']) && $each_component['url'] != "") {
							if (isset($input['url'])) {
								$url = $input['url'] . '/' . $each_component['url'];
							} else {
								$url = $each_component['url'];													
							}
							// save new extended url
							$new_component['url'] = $url;
						}

						// call and then return the component_id
						$component_id = $create_component($new_component);
						
						// check that component has not been disabled
						$activated_components = json_decode($site->activated_components, true);

						// check that this component has been activated
						if (isset($activated_components[$new_component['type']])) {
							require_once(BASEPATH . $activated_components[$new_component['type']]);
						}
		
						// add component_id to new_component
						$new_component['component_id'] = $component_id;
		
						//  add auto_create to new_component
						$new_component['auto_create'] = $each_component['auto_create'];
		
						// call to auto_created
						$new_component['type']::auto_created($new_component);
						
						// recursive call
						if (isset($sub_auto_create)) {
							// create a copy of input to add parent_id and send recersively 
							$new_input = $input;
							// update parent_id with the newly created component_id
							$new_input['parent_id'] = $component_id;
							// make call
							$auto_create_components($sub_auto_create, $new_input, $direction);
						}
					
					}
				}
	
				// if there is an auto_create == reverse and auto_create == forward at the same level as the component.
				if (isset($auto_create[0]['auto_create']) && $auto_create[0]['auto_create'] == "reverse") {

					// update parent_id with the reverse_parent_id value from before
					$input['parent_id'] = $input['reverse_parent_id'];

					// recursive call
					$auto_create_components($sub_auto_create, $input, $direction);

				}
			}
			
			return $input['parent_id'];
		
		};

		// check for auto_create == reverse
		$input['parent_id'] = $auto_create_components($auto_create, $input, "reverse");
		
		// save the parent_id of the reverse auto_create component
		$reverse_parent_id = $input['parent_id'];
		
		// create component
		$input['parent_id'] = $create_component($input);
		$component_id = $input['parent_id'];
		
		// add this value back
		$input['reverse_parent_id'] = $reverse_parent_id;
		
		// check for auto_create == forward
		$auto_create_components($auto_create, $input, "forward");
		
		// return the current_id for the newly created component
		return $component_id;

	}

	/**
	 * change SITE_EMAIL in config file
	 */
public function edit_site_email($input) {

	
	$define_value = array (
		'constant_name' => 'SITE_MAIL',
		'constant_value' => $input["site_mail"],
		'filename' => BASEPATH.'vce-config.php'
	
	);
	$response = $this->define_config_value($define_value);

	$new_value = $input["site_mail"];
	echo json_encode(array('response' => 'success','message' => 'edited SITE_EMAIL to '.$response.' in config file ','form' => 'create','action' => ''));
		return;
}	
	/**
	 * sends a test email
	 */
public function send_test_email($input) {
	
	$now = date("Y/m/d-h:i:sa");
	$mail_attributes = array (
	  'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  'to' => array(
	  array($input['address'], $input['name'])
	    ),
	'subject' => $input['subject'],
	 'message' => $input['message'].' at '.$now,
	 'SMTPAuth' => false
	 );
	
	global $site;
	$site->mail($mail_attributes);	
	echo json_encode(array('response' => 'success','message' => 'Sent Test Email ','form' => 'create','action' => ''));
		return;
}	

	
	/**
	 * takes a .csv list of emails and organizations and automatically assigns those users
	 * to be org admins
	 */
public function assign_org_admins($input) {
global $site;
global $db;
         //get organizations datalist id
			$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";
			$org_info = $db->get_data_object($query);
			$datalist_id = $org_info[0]->datalist_id;


			$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $datalist_id . "' AND b.meta_key = 'name' ORDER BY name";
			$options = $db->get_data_object($query);
$org = array();
	foreach($options as $option) {
// 		$name = $option->name;
		$org[$option->name] = $option->item_id;
// 		$site->log($option);
// 		$site->log('<br>');
	}
// 	$site->log($org);
// 	$site->log('<br><br>');
$row = 1;
if (($handle = fopen(BASEPATH.$input['filename'], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
         $name = str_replace('|', ',', $data[1]);
unset($options);
			$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $datalist_id . "' AND b.meta_value LIKE 'name'";
			$options = $db->get_data_object($query);

if (isset($options)){
            $site->log($row.' '.$data[0]);
            $site->log(' --- '.str_replace('|', ',', $data[1]));
            $key = $data[1];
            $site->log(' --- '.$options[0]->name);
        	$site->log('<br>');
        	}
        $row++;
    }
    fclose($handle);
}

	echo json_encode(array('response' => 'success','message' => 'Assigned Org Admins','form' => 'create','action' => ''));
		return;
}	

	/**
	 * fileds to display when this is created
	 *
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