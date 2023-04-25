<?php

require_once __DIR__ . '/../aws_support.php';

class AWSMediaCopy extends Component {

    use AWSSupport;

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'AWS Media Copy',
			'description' => 'Specific for CECI EA Functionality and Will make a copy within S3 of the parent AWS Media Item',
			'category' => 'media',
			'recipe_fields' => array('title')
		);
	}
	
	public function recipe_manifestation($each_recipe_component, $vce) {
	
		// okay, the logic
		// if this is an alias, change ownership
		// or 
		// if this is a new file, then create a copy within my_resources
	
		if ($each_recipe_component->parent->type == "Media" && !empty($each_recipe_component->parent->path)) {
		 
		 	// prevent this from being copied if not published
			if ($each_recipe_component->parent->media_type == 'AWSVideo' && empty($each_recipe_component->parent->published)) {
				return;
			}
			
			// if we have already made this copy, do not do again
			if (!empty($each_recipe_component->parent->video_hightlight_submission)) {
				return;
			}
			
			// first, copy within AWS S3
			// second, we modifiy the current $each_recipe_component->parent meta_data to reflect the copy
			// third, we add back to my_resourses, but not if it is an alias
			
			// Make Copy
			
			// So much repeated code, I know, but trying to debug is getting crazy
			
			// if this is an alias from my resources
			if (!empty($each_recipe_component->parent->alias_id)) {
			
				list($file_name,$file_extention) = explode('.', $each_recipe_component->parent->path);
			
				$now = time();
				
				$new_path = '0_' . $now . '.' . $file_extention;
				
				$config = AWSMediaCopy::get_config($vce);
				$sdk = AWSMediaCopy::get_sdk($config);
				// destination bucket is the one we want, NOT source which is where the file goes first
				$bucket = AWSSupport::dest_bucket($config);
		
				// need to add the bucket prefix store in config, which is 'washingtoncc/'
				$sourceName = $config['prefix'] . '/' . $each_recipe_component->parent->path;
				$targetName = $config['prefix'] . '/' . $new_path;
		
				AWSSupport::move_file($bucket, $targetName, $bucket, $sourceName, $sdk, $config);
			
				// first things first, need to change created_by and delete alias_id
			
				$video_hightlight_created_by = $each_recipe_component->parent->created_by;
			
				$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='0' WHERE component_id='" . $each_recipe_component->parent->component_id . "' AND meta_key='created_by'";
				$vce->db->query($query);
				
				$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='" . $now . "' WHERE component_id='" . $each_recipe_component->parent->component_id . "' AND meta_key='created_at'";
				$vce->db->query($query);

				$query = "DELETE FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $each_recipe_component->parent->component_id . "' AND meta_key='alias_id'";
				$vce->db->query($query);

				$attributes = array(
					'component_id' => $each_recipe_component->parent->component_id,
					'created_by' => 0,
					'created_at' => $now,
					'type' => $each_recipe_component->parent->type,
					'media_type' => $each_recipe_component->parent->media_type,
					'path' => $new_path,
					'title' => $each_recipe_component->parent->title,
					'video_hightlight_created_by' => $video_hightlight_created_by,
					'video_hightlight_submission' => time()
				);
				
			
				if (isset($each_recipe_component->parent->alias_id)) {
					$attributes['video_hightlight_alias_id'] = $each_recipe_component->parent->alias_id;
				}
			
				if (isset($each_recipe_component->parent->published)) {
					$attributes['published'] = $each_recipe_component->parent->published;
				}

				$this->update_component($attributes);

			} else {
				// not an alias.
			
				$video_hightlight_created_by = $each_recipe_component->parent->created_by;
			
				list($file_name,$file_extention) = explode('.', $each_recipe_component->parent->path);
			
				$now = time();
				
				$new_path = $video_hightlight_created_by . '_' . $now . '.' . $file_extention;

				$config = AWSMediaCopy::get_config($vce);
				$sdk = AWSMediaCopy::get_sdk($config);
				// destination bucket is the one we want, NOT source which is where the file goes first
				$bucket = AWSSupport::dest_bucket($config);
		
				// need to add the bucket prefix store in config, which is 'washingtoncc/'
				$sourceName = $config['prefix'] . '/' . $each_recipe_component->parent->path;
				$targetName = $config['prefix'] . '/' . $new_path;
		
				AWSSupport::move_file($bucket, $targetName, $bucket, $sourceName, $sdk, $config);
			
				$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='0' WHERE component_id='" . $each_recipe_component->parent->component_id . "' AND meta_key='created_by'";
				$vce->db->query($query);
				
				// find component_id for 'my resoureces'
				$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='type' AND meta_value='UnassignedAssets'";
				$unassigned_assets_data = $vce->db->get_data_object($query);
		
				$unassigned_assets = $unassigned_assets_data[0]->component_id;
			
				$attributes = array(
					'type' => $each_recipe_component->parent->type,
					'media_type' => $each_recipe_component->parent->media_type,
					'sequence' => '1',
					'parent_id' => $unassigned_assets,
					// 'created_by' => $video_hightlight_created_by,
					// 'created_at' => $each_recipe_component->parent->created_at,
					'path' => $new_path,
					'title' => $each_recipe_component->parent->title,
					'video_hightlight_submission_copy' => time(),
					'vide_hightlight_location' => $vce->site->path_routing_requested_url,
					'auto_create' => array(
						array(
							'title' => 'Assets',
							'auto_create' => 'reverse',
							'url' => 'my-resources/' . $video_hightlight_created_by . '/' . time(),
							'type' => 'Assets',
							'title' => $each_recipe_component->parent->title,
							// 'created_by' => $video_hightlight_created_by,
							// 'created_at' => $each_recipe_component->parent->created_at,
							'components' => array(
								array(
									'type' => 'Media'
								)
							)
						)
					)
				);
				
				if (isset($each_recipe_component->parent->published)) {
					$attributes['published'] = $each_recipe_component->parent->published;
				}
				
				$component_id = $this->create_component($attributes);
				
				// check to see value of permissions
				
				// default here
				$permissions['view_video_submission'] = 'on';
	
				// find site permissions
				foreach ($vce->page->components as $each_component) {
		
					if ($each_component->type == 'AssessmentsSite') {
		
						$permissions = $each_component->data->permissions;
				
						break;
		
					}
		
				}
		
				// if $permissions['view_video_submission'] == 'off' then don't set to actual user
				if ($permissions['view_video_submission'] == 'on') {
				
					// update the created_by record
					$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='" . $video_hightlight_created_by . "' WHERE component_id='" . $component_id . "' AND meta_key='created_by'";
					$vce->db->query($query);
				
					// find asset and update the created_by record
					// update the created_by record
					$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='" . $video_hightlight_created_by . "' WHERE meta_key='created_by' AND component_id IN (select parent_id FROM " . TABLE_PREFIX . "components WHERE component_id='" . $component_id . "')";
					$vce->db->query($query);
				
				}

				// this updates the video highlight video with the video_hightlight_submission tag
				$attributes = array(
					'component_id' => $each_recipe_component->parent->component_id,
					'created_by' => 0,
					'created_at' => $each_recipe_component->parent->created_at,
					'video_hightlight_submission_at' => time()
				);
			
				$this->update_component($attributes);
			
			}
			
			
		}
		
	}
	
	public function add_component($each_recipe_component, $vce) {
	//	$vce->dump($each_recipe_component);
	}
	
}