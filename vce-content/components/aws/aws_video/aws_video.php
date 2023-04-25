<?php

/**
 * Amazon aws video media type
 *
 * @category   Media
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';
require_once __DIR__ . '/../aws_transcript_support.php';
require_once __DIR__ . '/../../../../vce-application/components/media/mediatype/mediatype.php';

/**
 * Amazon aws video media type.
 */
class AWSVideo extends MediaType
{

    use AWSSupport;
    use AWSTranscriptSupport;

    /**
     * basic info about the component
     */
    public function component_info() 
    {
        return array(
            //basic component properties are required
            'name' => 'AWSVideo (Media Type)',
            'description' => 'Amazon aws video support',
            'category' => 'media',
            'typename' => 'Video'
        );
    }   

    /**
     * things to do when this component is preloaded
     */
    public function preload_component()
    {
        $content_hook = null;

        $content_hook = array(
            'media_create_component' => 'AWSVideo::custom_create',
            'media_update_component' => 'AWSVideo::custom_update',
            'media_delete_component' => 'AWSVideo::custom_delete',
            'media_uploader_chunk_size' => 'AWSVideo::media_uploader_chunk_size'
        );

        return $content_hook;
    }

    public static function media_uploader_chunk_size($chunk_size)
    {
        $fiveMB = 5 * 1024 * 1024;
        if ($chunk_size < $fiveMB) {
            return $fiveMB;
        }

        return $chunk_size;
    }

    public static function media_upload_path($input)
    {

        global $vce;

        return $vce->site->site_url . '/AWSUpload';
    }

    /**
     * called by the function create() in Media Component.
     * allows for an alternative method to create component specific to media type.
     */
    public static function custom_create($input)
    {

        global $vce;

        if ($input['media_type'] == "AWSVideo") {

            try {
                AWSVideo::check_aws_objects($vce);

                $config = AWSVideo::get_config($vce);
                $sdk = AWSVideo::get_sdk($config);

                $file_uri = AWSVideo::get_file_uri(AWSVideo::source_bucket($config), $input['path'], $sdk);

                $job_id = AWSVideo::do_transcode($input['path'], $sdk, $config);
                $transcription_job_name = $input['path'] . '.vtt';
                $input['job_id'] = $job_id;

                if ($config['use_media_convert']) {

                    // Reset the path attribute to point to the transcribed file, which may have a different extension.
                    $info = pathinfo($input['path']);
                
                    $input['path'] = $info['filename'].'.mp4';
                }

                // Transcribe captions
                if (isset($config['create_captions_activated']) && !isset($input['disable_captions'])) {
                    try {

                        $transcribe = $sdk->createTranscribe();

                        $result = $transcribe->startTranscriptionJob([
                            'LanguageCode' => 'en-US',
                            'Media' => [
                                'MediaFileUri' => $file_uri,
                            ],
                            'OutputBucketName' => AWSVideo::source_bucket($config),
                            'TranscriptionJobName' => $input['path'],
                        ]);

                        //$result = $transcribe->startTranscriptionJob([
                        //    'Media' => [
                        //        'MediaFileUri' => $file_uri,
                        //    ],
                        //    'IdentifyMultipleLanguages' => true,
                        //    'OutputBucketName' => AWSVideo::dest_bucket($config),
                        //    'OutputKey' => $config['prefix'] . "/" . $input['path'],
                        //    'TranscriptionJobName' => $transcription_job_name,
                        //]);
                    } catch (Exception $e) {
                        // Something went wrong with trancribe, maybe file > 1gb.  Continue on with transcode.
                        $msg = '<div class="form-message form-error">AWS Error: ' . $e->getMessage() . '</div>';
                        $vce->content->add('main', $msg);
                    }
                }

            } catch (Exception $e) {

                $msg = '<div class="form-message form-error">AWS Error: ' . $e->getMessage() . '</div>';
                $vce->content->add('main', $msg);
                return;
            }
        }

        return $input;
    }

    /**
     * called by the function update() in Media Component.
     * allows for an alternative method to update componenet specific to media type.
     */
    public static function custom_update($input)
    {

        return $input;
    }

    /**
     * called by the function update() in Media Component.
     * allows for an alternative method to update componenet specific to media type.
     */
    public static function custom_delete($input)
    {

        global $vce;

        if (isset($input['type']) && $input['type'] == 'Media' && isset($input['media_type']) && $input['media_type'] == 'AWSVideo') {
            
            // the path is already being supplied within the input, so there is no need to do this query.
        	// $query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='path'";
            // $path_data = $vce->db->get_data_object($query);
            // if (!empty($path_data[0]->meta_value)) {
            if (!empty($input['path'])) {
            	
            	//$path = $path_data[0]->meta_value;
				$path = $input['path'];

                $config = AWSVideo::get_config($vce);
                $sdk = AWSVideo::get_sdk($config);
                $s3 = $sdk->createS3();
                $result = $s3->deleteObject([
                    'Key' => $config['prefix'] . '/' . $path,
                    'Bucket' => AWSVideo::dest_bucket($config),
                ]);
            }
        }

        return $input;
    }

    public function display($each_component, $vce)
    {

        // default error message (will be overwritten if successful)
        $contents = '<div class="form-message form-success">Video is processing</div>';

        try {

            $config = AWSVideo::get_config($vce);
            $aws_path = $config['prefix'] . '/' . $each_component->path;
            $sdk = AWSVideo::get_sdk($config);

            // If not published, check status and publish if successful.
            $status = 'Complete';
            
            // adding a check to see if this is an alias, which has already been published.
            if (!isset($each_component->published) && !isset($each_component->alias_id)) {

                $status = AWSVideo::get_transcode_job_status($each_component->job_id, $sdk, $config);
                $color = ($status == 'Error' || $status == 'ERROR') ? 'form-error' : 'form-success';

                $delete_button = null;

                if ($status == 'Error' && $each_component->created_by == $vce->user->user_id) {

                    // the instructions to pass through the form
                    $dossier = array(
                        'type' => $each_component->type,
                        'procedure' => 'delete',
                        'component_id' => $each_component->component_id,
                        'created_at' => $each_component->created_at,
                    );

                    // generate dossier
                    $dossier_for_delete = $vce->generate_dossier($dossier);

                    $delete_button = <<<EOF
<form id="delete_$each_component->component_id" class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete File">
</form>
EOF;
                } else if ($status == 'Complete' || $status == 'COMPLETE') {

                    // transcoding is successful, so delete source file and mark as published

                    // delete source video
                    if (AWSVideo::file_exists(AWSVideo::source_bucket($config), $each_component->path, $sdk, $config)) {
//                        AWSVideo::delete_file(AWSVideo::source_bucket($config), $each_component->path, $sdk, $config);
                    }

                    // set published
                    $records[] = array(
                        'component_id' => $each_component->component_id,
                        'meta_key' => 'published',
                        'meta_value' => $each_component->job_id,
                        'minutia' => null,
                    );

                    $vce->db->insert('components_meta', $records);
                }

                $contents = "<div class='form-message $color'>Video Transcoding Status:  $status  $delete_button</div>";
            }

            // If the video is published or status is Complete display it.
            if ($status == 'Complete' || $status == 'COMPLETE' || isset($each_component->published)) {

                // convert transcripts if not already done and enabled
                if (!isset($each_component->disable_captions) && isset($config['create_captions_activated']) && !isset($each_component->vtt)) {
                    if (AWSVideo::file_exists(AWSVideo::source_bucket($config), $each_component->path . ".json", $sdk, $config)) {
                        $file = AWSVideo::get_file_stream(AWSVideo::source_bucket($config), $each_component->path . ".json", $sdk);
                        $json = json_decode(file_get_contents($file));
                        $vtt = AWSVideo::transcript_to_vtt($json);
                        $result = AWSVideo::put_file($aws_path . ".vtt", $vtt, AWSVideo::dest_bucket($config), $sdk);

                        $vtt_db[] = array(
                            'component_id' => $each_component->component_id,
                            'meta_key' => 'vtt',
                            'meta_value' => $result['ObjectURL'],
                            'minutia' => null,
                        );

                        $vce->db->insert('components_meta', $vtt_db);

                        AWSVideo::delete_file(AWSVideo::source_bucket($config), $each_component->path . ".json", $sdk, $config);
                    
                    } else if (!isset($each_component->vtt) && AWSVideo::file_exists(AWSVideo::dest_bucket($config), $aws_path . ".vtt", $sdk, $config)) {

                        $vtt_db[] = array(
                            'component_id' => $each_component->component_id,
                            'meta_key' => 'vtt',
                            'meta_value' => $aws_path . ".vtt",
                            'minutia' => null,
                        );

                        $vce->db->insert('components_meta', $vtt_db); 
                        $each_component->vtt = $vtt_db;                       # code...
                    }
                }

                $vce->site->add_script(dirname(__FILE__) . '/js/script.js');
                $vce->site->add_style(dirname(__FILE__) . '/css/style.css', 'webdam-style');

                $url = AWSVideo::create_signed_url($aws_path, $sdk, $config);

                if (isset($each_component->vtt)) {
                    $vtt = AWSVideo::create_signed_url($aws_path . ".vtt", $sdk, $config);
                }

                $contents = <<<EOF
<div class="vidbox" player="player-$each_component->component_id">
<button class="vidbox-click-control"></button>
<div class="vidbox-content">
<button class="vidbox-content-close">X</button>
<div class="vidbox-content-area"></div>
</div>
<video crossorigin="anonymous" class="player" id="player-$each_component->component_id" width="100%" height="auto" controls controlslist="nodownload" playsinline="" preload="auto">
<source src="$url">
EOF;

                if (!isset($each_component->disable_captions) && isset($vtt)) {
                    if (isset($config['captions_on_activated'])) {
                        $contents .= <<<EOF
<track label="English" kind="subtitles" srclang="en" src="$vtt" default>
EOF;
                    } else {
                        $contents .= <<<EOF
<track label="English" kind="subtitles" srclang="en" src="$vtt">
EOF;
                    }
                }
                $contents .= <<<EOF
</video>
</div>
EOF;
            }
        } catch (Exception $e) {

            $msg = '<div class="form-message form-error">AWS Error: ' . $e->getMessage() . '</div>';
            $vce->content->add('main', $msg);
            return;
        }

        $vce->content->add('main', $contents);
        
		// display title
		if (isset($each_component->recipe['display_title']) && $each_component->recipe['display_title'] == 'on') {
			$vce->content->add('main','<div class="media-title">' . $each_component->title . '</div>');
		}     

    }

    /**
     * file uploader needed
     */
    public static function file_upload()
    {
        return true;
    }

    /**
     * a way to pass file extensions to the plupload to limit file selection
     */
    public static function file_extensions()
    {
        return array('title' => 'video files', 'extensions' => 'mpg,mpeg,mov,mp4,m4v,wmv,avi,asx,asf,m2ts');
    }

    /**
     * a way to pass the mimetype and mimename to vce-upload.php
     * the minename is the class name of the mediaplayer.
     * mimetype can have a wildcard for subtype, included after slash by adding .*
     * https://www.sitepoint.com/mime-types-complete-list/
     */
    public static function mime_info()
    {
        return array(
            'video/.*' => get_class(),
            'video/quicktime' => get_class(),
            'application/m2ts' => get_class(),
        );
    }
}
