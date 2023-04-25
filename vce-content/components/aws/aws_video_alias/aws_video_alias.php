<?php

require_once __DIR__ . '/../aws_support.php';

class AWSVideoAlias extends MediaType {

    use AWSSupport;

	/**
	 * basic info about the component
	 */
	public function component_info()
	{
		return array(
			'name' => 'AWSVideo Alias (Media Type)',
			'description' => 'An Alias for AWSVideo. This Component requires Alias to be activated',
			'category' => 'media'
		);
	}
	
	/**
	 * get all AWSVideo vidoes for this user
	 */
    public static function add($recipe_component, $vce)
    {

		$vce->site->add_style(dirname(__FILE__) . '/css/brokenimg.css', 'img-style');

		$query = "
SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id IN (SELECT a.component_id 
FROM " . TABLE_PREFIX . "components_meta AS a
JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id
WHERE a.meta_key='created_by' AND a.meta_value='" . $vce->user->user_id . "'
 AND b.meta_key='media_type' AND b.meta_value='AWSVideo')
";
		$user_videos = $vce->db->get_data_object($query);
		
		$video_list = array();
		
		if (!empty($user_videos)) {
			// compile search results
			foreach ($user_videos as $each_user_video) {
			$video_list[$each_user_video->component_id]['component_id'] = $each_user_video->component_id;
				$video_list[$each_user_video->component_id][$each_user_video->meta_key] = $each_user_video->meta_value;
			}
		} else {
		
			// if no videos, don't display this media_type option
			return;
		
		}
		
		// retrieve auto create value from dossier
		$auto_create = isset($recipe_component->dossier['auto_create']) ? $recipe_component->dossier['auto_create'] : null;

		// the instructions to pass through the form
		$dossier = array(
		'type' => 'Alias',
		'procedure' => 'create',
		'parent_id' => $recipe_component->parent_id,
		'auto_create' => $auto_create
		);

		// generate dossier
		$dossier_for_alias = $vce->generate_dossier($dossier);
		
		$form_content = null;
		
		$config = AWSVideoAlias::get_config($vce);
		$sdk = AWSVideoAlias::get_sdk($config);

		$form_content .= <<<EOF
<form id="aws-video-alias" class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_alias">
EOF;
		
        $key_file = AWSSupport::create_key_file($config);
        $cf = $sdk->createCloudFront();
        $cf_domain = AWSSupport::get_distribution_domain($sdk, $config);

		foreach ($video_list as $key=>$value) {
		
			$title = $video_list[$key]['title'];
			$original_file = $video_list[$key]['path'];
			$original_file_full = $config['prefix'] . '/' . $original_file;

			$thumbnail_file = AWSSupport::get_thumbnail_path($original_file, $sdk, $config);

            try {
				if ($thumbnail_file == null) {
					AWSSupport::move_file(AWSSupport::source_bucket($config), $original_file, AWSSupport::dest_bucket($config), $original_file_full, $sdk, $config);
					AWSSupport::delete_file(AWSSupport::dest_bucket($config), $original_file_full, $sdk, $config);
					AWSSupport::do_transcode($original_file, $sdk, $config);
				}
			} catch (Exception $e) {
				$msg = '<div class="form-message form-error">AWS Error: ' . $e->getMessage() . '</div>';
			}

			$thumbnail_file = AWSSupport::get_thumbnail_path($original_file, $sdk, $config);

			// path to thumbnail on aws
			$resource_key = 'https://' . $cf_domain . '/' . $thumbnail_file;
			$key_pair_id = $config['cloud_front']['cf_key_id'];
			$expires = time() + 1200;
	
			$thumbnail = AWSSupport::do_create_signed_url($cf, $resource_key, $key_file, $key_pair_id, $expires);

			$component_id = $video_list[$key]['component_id'];
		
			$form_content .= <<<EOF
<label for="button-$component_id" class="input-label-style input-padding-small">
<input type="radio" name="alias_id" value="$component_id" id="button-$component_id">
$title
<img src="$thumbnail" alt="$title">
</label>
EOF;

		}

        unlink($key_file);

		$form_content .= <<<EOF
<input type="submit" value="Add">
<button class="link-button cancel-button">Cancel</button>
</form>
EOF;

		$contents = $vce->content->accordion('Select From Your Current Videos', $form_content);
    
		return $contents;
 
	}
	
}