<?php

require_once __DIR__ . '/../aws_support.php';

class AWSFile extends Component
{
    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            'name' => 'AwsFile',
            'description' => 'Component to allow an AWS file to be displayed securely.',
            'category' => 'file',
        );
    }

    /**
     * things to do when this component is preloaded
     */
    public function preload_component()
    {

        $content_hook = array(
            'site_media_link' => 'AWSFile::site_media_link',
        );

        return $content_hook;
    }

    /**
     * method that is hooked to site_media_link
     */
    public static function site_media_link($fileinfo, $site)
    {

        global $vce;

        $url = '';
        
        $path = $fileinfo['path'];
        if (strpos($path, '/') === false) {
            // nothing
        } else {
            $path = explode('/', $path)[1];
        }
        
		$config = AWSFile::get_config($vce);
		$sdk = AWSFile::get_sdk($config);

		$aws_path = $config['prefix'] . '/' . $path;
		
        // The non-video files are first uploaded to the source bucket, then copied to the dest bucket (cloudfront).
        // This process is different from video files, in that video files are automatically copied to the the dest
        // bucket by the elastic transcoder (aws).

		// check if file exists in the destination bucket
		$dest_exists = AWSFile::file_exists(AWSFile::dest_bucket($config), $aws_path, $sdk, $config);
 
        // check if file exists in the source bucket
		$source_exists = AWSFile::file_exists(AWSFile::source_bucket($config), $path, $sdk, $config);
 
        if ($dest_exists && !$source_exists) {

			try {
		
				$url = AWSFile::create_signed_url($aws_path, $sdk, $config);
			
			} catch (Exception $e) {
		
                // This should be very rare.  File is on dest bucket but sigined url not created.
				$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' File on AWS dest_bucket did not create a signed url.   This will be attempted again, since the file is still on dest_bucket.</div>');
		
			}
        
        } elseif (!$dest_exists && $source_exists) {
        
			$s3 = $sdk->createS3();
			
			try {

				// Copy the file to dest bucket.
				$s3->copyObject([
					'Bucket'     => AWSFile::dest_bucket($config),
					'Key'        => $aws_path,
					'CopySource' => AWSFile::source_bucket($config) .  '/' . $path,
				]);
				
				$copy_successful = AWSFile::file_exists(AWSFile::dest_bucket($config), $aws_path, $sdk, $config);
			
				if ($copy_successful) {

                    /* not needed anymore ?
					$records[] = array(
						'component_id' => $component->component_id,
						'meta_key' => 'on_cloudfront',
						'meta_value' => true,
						'minutia' => null,
					);

					$vce->db->insert('components_meta', $records);
                    */

					$url = AWSFile::create_signed_url($aws_path, $sdk, $config);
				
				} else {
				
					$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' was not copied to AWS dest_bucket.  This will be attempted again, since the file is still on the source_bucket.</div>');

				}
			
			} catch (Exception $e) {
		
				$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' File on AWS source_bucket not found</div>');

            }

		} elseif  ($dest_exists && $source_exists) {

            // file is both on source and dest bucket.  We need to delete from source bucket now to save space.

            $s3 = $sdk->createS3();
			
			try {

				// Delete the file from source bucket.
                // This temp commented out until we are sure the copy is working correctly
//				AWSFile::delete_file(AWSVideo::source_bucket($config), $component->path, $sdk, $config);

				$url = AWSFile::create_signed_url($aws_path, $sdk, $config);
				
			} catch (Exception $e) {
		
				$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' Delete file on AWS source_bucket failed.   This will be attempted again.</div>');

            }

        } elseif (!$dest_exists && !$source_exists) {

            // file not on either dest or source.  This is bad.

			$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' File on AWS not found on source_bucket or dest_bucket.</div>');
        }
        
        return $url;

    }

    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe)
    {
        return false;
    }
}
