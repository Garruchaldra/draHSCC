<?php

require_once __DIR__ . '/../aws/aws_support.php';

class AWSFile_ohscc_custom extends Component
{
    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            'name' => 'AWSFile_ohscc_custom',
            'description' => 'Custom Copy of Component to allow checking if file exists only on remote ECLKC site.',
            'category' => 'file',
        );
    }

    /**
     * things to do when this component is preloaded
     */
    public function preload_component()
    {

        $content_hook = array(
            'site_media_link' => 'AWSFile_ohscc_custom::site_media_link',
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
        
		$config = AWSFile_ohscc_custom::get_config($vce);
		$sdk = AWSFile_ohscc_custom::get_sdk($config);

		$aws_path = $config['prefix'] . '/' . $path;
		
        // The non-video files are first uploaded to the source bucket, then copied to the dest bucket (cloudfront).
        // This process is different from video files, in that video files are automatically copied to the the dest
        // bucket by the elastic transcoder (aws).

		// check if file exists in the destination bucket
		$dest_exists = AWSFile_ohscc_custom::file_exists(AWSFile_ohscc_custom::dest_bucket($config), $aws_path, $sdk, $config);
 
        // check if file exists in the source bucket
		$source_exists = AWSFile_ohscc_custom::file_exists(AWSFile_ohscc_custom::source_bucket($config), $path, $sdk, $config);
 
        if ($dest_exists && !$source_exists) {

			try {
		
				$url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);
			
			} catch (Exception $e) {
		
                // This should be very rare.  File is on dest bucket but sigined url not created.
				$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' File on AWS dest_bucket did not create a signed url.   This will be attempted again, since the file is still on dest_bucket.</div>');
		
			}
        
        } elseif (!$dest_exists && $source_exists) {
        
			$s3 = $sdk->createS3();
			
			try {

				// Copy the file to dest bucket.
				$s3->copyObject([
					'Bucket'     => AWSFile_ohscc_custom::dest_bucket($config),
					'Key'        => $aws_path,
					'CopySource' => AWSFile_ohscc_custom::source_bucket($config) .  '/' . $path,
				]);
				
				$copy_successful = AWSFile_ohscc_custom::file_exists(AWSFile::dest_bucket($config), $aws_path, $sdk, $config);
			
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

					$url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);
				
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

				$url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);
				
			} catch (Exception $e) {
		
				$vce->content->add('main', '<div class="vce-error-message">Error: ' . $path . ' Delete file on AWS source_bucket failed.   This will be attempted again.</div>');

            }

        } elseif (!$dest_exists && !$source_exists) {

            $component = $fileinfo['component'];

            $file_existence_test = self::remote_file_exists($component->path);
            $vce->log('check: ');
             $vce->log($file_existence_test);
            if ($file_existence_test != FALSE) {
                $url = $file_existence_test;
            } else {
                // just a random sample PDF
                $url = 'http://www.africau.edu/images/default/sample.pdf';
            }
        }
        
        return $url;

    }

    /**
     * method that is hooked to site_media_link
     */
    public static function site_media_link_old($fileinfo, $site)
    {

        global $vce;

        $url = '';
        
        $component = $fileinfo['component'];
        
		$config = AWSFile_ohscc_custom::get_config($vce);
		$sdk = AWSFile_ohscc_custom::get_sdk($config);

		$aws_path = $config['prefix'] . '/' . $component->path;
		
		// check if file exists in the destination bucket
		$exists = AWSFile_ohscc_custom::file_exists(AWSFile_ohscc_custom::dest_bucket($config), $aws_path, $sdk, $config);
        
        if ($exists) {

            $vce->log('exists ');
			// try to get file
			try {
		
				$url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);
			
			} catch (Exception $e) {
		
				die($e);
		
			}
        
        } else {

            $file_existence_test = self::remote_file_exists($component->path);
            $vce->log('check: ');
             $vce->log($file_existence_test);
            if ($file_existence_test != FALSE) {
                return $file_existence_test;
            } else {
                // just a random sample PDF
                return 'http://www.africau.edu/images/default/sample.pdf';
            }

        	// We did not find the file on the destination bucket
        
			$s3 = $sdk->createS3();

			// Copy the file to dest bucket.
			$s3->copyObject([
				'Bucket'     => AWSFile_ohscc_custom::dest_bucket($config),
				'Key'        => $aws_path,
				'CopySource' => AWSFile_ohscc_custom::source_bucket($config) . '/' . $component->path,
			]);

			// Delete the file from source bucket.
			AWSFile_ohscc_custom::delete_file(AWSVideo::source_bucket($config), $component->path, $sdk, $config);

			$records[] = array(
				'component_id' => $component->component_id,
				'meta_key' => 'on_cloudfront',
				'meta_value' => true,
				'minutia' => null,
			);

			$vce->db->insert('components_meta', $records);
        
			$url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);

        }
        
        return $url;

/*
        try {

            $config = AWSFile_ohscc_custom::get_config($vce);
            $sdk = AWSFile_ohscc_custom::get_sdk($config);

            $component = $fileinfo['component'];
            
            if (!isset($component->on_cloudfront)) {

                $s3 = $sdk->createS3();

                // Copy the file to dest bucket.
                $s3->copyObject([
                    'Bucket'     => AWSFile_ohscc_custom::dest_bucket($config),
                    'Key'        => $config['prefix'] . '/' . $component->path,
                    'CopySource' => AWSFile_ohscc_custom::source_bucket($config) . '/' . $component->path,
                ]);

                // Delete the file from source bucket.
                AWSFile_ohscc_custom::delete_file(AWSVideo::source_bucket($config), $component->path, $sdk, $config);

                $records[] = array(
                    'component_id' => $component->component_id,
                    'meta_key' => 'on_cloudfront',
                    'meta_value' => true,
                    'minutia' => null,
                );

                $vce->db->insert('components_meta', $records);
            }

            $aws_path = $config['prefix'] . '/' . $component->path;
            $url = AWSFile_ohscc_custom::create_signed_url($aws_path, $sdk, $config);
            
        } catch (Exception $e) {
        
        	die($e);
        }

        return $url;
        
*/

    }



    public static function remote_file_exists($path) {
        global $vce;
        
        $user_folder = explode('_', $path);
        $user_folder = $user_folder[0];
        $url = "https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/" . $user_folder . "/$path";
		$a_url = parse_url($url);
// $vce->dump($a_url);
// $vce->dump(gethostbyname($a_url['host']));
		if (!isset($a_url['port'])) $a_url['port'] = 80;
		$errno = 0;
		$errstr = '';
		$timeout = 300;
		if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host'])){
			$fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
			if (!$fid) return false;
			$page = isset($a_url['path'])  ?$a_url['path']:'';
			$page .= isset($a_url['query'])?'?'.$a_url['query']:'';
			fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
			$head = fread($fid, 4096);
			$head = substr($head,0,strpos($head, 'Connection: close'));
			fclose($fid);
			if (preg_match('#^HTTP/.*\s+[200|302]+\s#i', $head)) {
			 $pos = strpos($head, 'Content-Type');
			 return $url;
			} elseif (preg_match('#^HTTP/.*\s+[301]+\s#i', $head)) {
				return $url;
			}
		} else {
			return FALSE;
		}
    }
    


    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe)
    {
        return false;
    }
}
