<?php

require_once __DIR__ . '/../aws_support.php';

class AWSUpload extends Component {

    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'AWSUpload',
            'description' => 'Extends Upload Component to use Asynchronous Amazon s3 upload portal',
            'category' => 'uploaders',
        );
    }

    /**
     * things to do when this component is preloaded
     */
    public function preload_component() {

        $content_hook = array(
            'upload_file_upload_method' => ['function' => 'AWSUpload::file_upload_method', 'priority' => 5000],
            'file_file_path_method' => 'AWSUpload::file_file_path_method',
            'media_delete_component' => 'AWSUpload::media_delete_component',
            'media_file_uploader' => 'AWSUpload::add_transcript_checkbox',
        );

        return $content_hook;

    }

    /**
     * add create transcript checkbox to file upload form
     */
    public static function add_transcript_checkbox($recipe_component, $vce) {

        $config = AWSUpload::get_config($vce);

        if ($config['create_captions_activated']) {
            return $vce->content->create_checkbox_input('disable_captions', false, 'Disable captions for this file.') . '<br>';
        }

    }

    /**
     * Delete file on s3
     *
     * @param [type] $input
     * @return void
     */
    public static function media_delete_component($input) {

        global $vce;

        if (isset($input['media_type']) && $input['media_type'] != "AWSVideo") {
            $query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='path'";
            $path_data = $vce->db->get_data_object($query);
            if (isset($path_data[0]->meta_value)) {

                $path = $path_data[0]->meta_value;

                $config = AWSUpload::get_config($vce);
                $sdk = AWSUpload::get_sdk($config);
                $s3 = $sdk->createS3();
                $result = $s3->deleteObject([
                    'Key' => $path,
                    'Bucket' => AWSUpload::source_bucket($config),
                ]);
            }

        }

        return $input;
    }

    /**
     * alter file_path for s3.  Note this is deprecated in favor of the new aws_file.php component.
     *
     * @param [string] $requested_url
     * @param [string] $file_path
     * @param [VCE] $vce
     * @return file_path
     */
    public static function file_file_path_method($requested_url, $file_path, $vce) {

        $config = AWSUpload::get_config($vce);
        $sdk = AWSUpload::get_sdk($config);
        $bucket = AWSUpload::source_bucket($config);
        $file_path = basename($file_path);
        return AWSUpload::get_file_stream($bucket, $file_path, $sdk);

    }

    /**
     * file_upload_method routes all file uploads to amazon s3
     */
    public static function file_upload_method($requested_url, $vce) {

        // php script for jQuery-File-Upload
        // upload_max_filesize = 30M
        // post_max_size = 30M
        // max_execution_time = 260
        // max_input_time = -1
        // memory_limit = 256M
        // max_file_uploads = 100

        // This is here in case you need to write out to the log.txt file for debugging purposes
        //file_put_contents(BASEPATH . 'log.txt', 'upload_max_filesize: ' . ini_get("upload_max_filesize") . PHP_EOL, FILE_APPEND);
        //file_put_contents(BASEPATH . 'log.txt', 'post_max_size: ' . ini_get("post_max_size") . PHP_EOL, FILE_APPEND);
        //file_put_contents(BASEPATH . 'log.txt', 'max_execution_time: ' . ini_get("max_execution_time") . PHP_EOL, FILE_APPEND);
        //file_put_contents(BASEPATH . 'log.txt', 'max_input_time: ' . ini_get("max_input_time") . PHP_EOL, FILE_APPEND);
        //file_put_contents(BASEPATH . 'log.txt', 'max_file_uploads: ' . ini_get("max_file_uploads") . PHP_EOL, FILE_APPEND);

        try {
            AWSUpload::check_aws_objects($vce);

            AWSUpload::set_header();

            $chunks = isset($_SERVER['HTTP_CONTENT_RANGE']) ? true : false;

            if ($chunks) {
                // Parse the Content-Range header, which has the following form:
                // Content-Range: bytes 0-524287/2000000
                $content_range = preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']);

                $start_range = $content_range ? $content_range[1] : null;
                $end_range = $content_range ? $content_range[2] : null;
                $size_range = $content_range ? $content_range[3] : null;

                // is this the first chunk?
                $first_chunk = ($start_range == 0) ? true : false;

                // is this the last chunk?
                $last_chunk = (($end_range + 1) == $size_range) ? true : false;
            }

            // first time through upload
            if (!$chunks || $first_chunk) {

                // if no dossier is set, forward to homepage
                if (!isset($_REQUEST['dossier'])) {
                    // echo json_encode(array('response' => 'error','message' => 'File Uploader Error: Dossier does not exist <div class="link-button cancel-button">Try Again</div>','action' => ''));
                    header("Location: " . $vce->site->site_url);
                    exit();
                }

                // decryption of dossier
                $dossier = json_decode($vce->user->decryption($_REQUEST['dossier'], $vce->user->session_vector));

                // check that component is a property of $dossier, json object test
                if (!isset($dossier->type) || !isset($dossier->procedure)) {
                    echo json_encode(array('response' => 'error', 'message' => 'File Uploader Error: Dossier is not valid <div class="link-button cancel-button">Try Again</div>', 'action' => ''));
                    exit();
                }

            }

            // 15 minutes execution time
            @set_time_limit(15 * 60);

            ini_set('memory_limit', '256M');

            if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
                // error can mean that the UPLOAD_SIZE_LIMIT is set too high, or that upload_max_filesize and post_max_size are too high
                die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: File size exceeds upload_max_filesize / post_max_size in php.ini  <div class="link-button cancel-button">Try Again</div>')));
            }

            // Get a file name
            if (isset($_REQUEST["name"])) {
            	// making sure this is not causing any issues
           	 	$file_name = $_REQUEST["created_by"] . '_' . $_REQUEST["timestamp"] . '.' . pathinfo($_REQUEST["name"])['extension'];
                // $file_name = $_REQUEST["created_by"] . '_' . $_REQUEST["timestamp"] . '_' . preg_replace("/[^A-Za-z0-9_]/", '', strtolower(str_replace(' ', '_', $_REQUEST["name"])));
            } else {
                die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: File name not set.  <div class="link-button cancel-button">Try Again</div>')));
            }

            if (!empty($_FILES)) {

                // error thrown by php
                if ($_FILES["file"]["error"]) {
                    $message = array(
                        0 => 'There is no error, the file uploaded with success',
                        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                        3 => 'The uploaded file was only partially uploaded',
                        4 => 'No file was uploaded',
                        6 => 'Missing a temporary folder',
                        7 => 'Failed to write file to disk.',
                        8 => 'A PHP extension stopped the file upload.',
                    );
                    die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: ' . $message[$_FILES["file"]["error"]] . ' <div class="link-button cancel-button">Try Again</div>')));
                }

                // Tells whether the file was uploaded via HTTP POST
                if (!is_uploaded_file($_FILES["file"]["tmp_name"])) {
                    die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: Failed to move uploaded file. <div class="link-button cancel-button">Try Again</div>')));
                }

                // Read binary input stream and append it to temp file
                if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                    die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: Failed to open output stream. <div class="link-button cancel-button">Try Again</div>')));
                }

            } else {
                if (!$in = @fopen("php://input", "rb")) {
                    die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: Failed to open input stream. <div class="link-button cancel-button">Try Again</div>')));
                }
            }

            // put file on s3 source bucket
            $config = AWSUpload::get_config($vce);
            $sdk = AWSUpload::get_sdk($config);
            $s3 = $sdk->createS3();

            // First chunk, set up multipart upload
            if (!$chunks || $first_chunk) {
                $result = $s3->createMultipartUpload([
                    'Bucket' => AWSUpload::source_bucket($config),
                    'Key' => $file_name,
                ]);
                $etags = [];
                $upload_id = $result['UploadId'];
                $part_number = 1;
            } else {
                // get from session
                $etags = unserialize($vce->site->retrieve_attributes('etags'));
                $part_number = $vce->site->retrieve_attributes('part_number');
                $upload_id = $vce->site->retrieve_attributes('upload_id');
            }

            $result = $s3->uploadPart([
                'Bucket' => AWSUpload::source_bucket($config),
                'Key' => $file_name,
                'UploadId' => $upload_id,
                'PartNumber' => $part_number,
                'Body' => $in,
            ]);

            // keep track of etags
            $etags[$part_number] = $result['ETag'];

            $part_number++;

            // write vars to session
            $vce->site->add_attributes('upload_id', $upload_id, true);
            $vce->site->add_attributes('part_number', $part_number, true);
            $vce->site->add_attributes('etags', serialize($etags), true);

            // Check if file has been uploaded
            if (!$chunks || $last_chunk) {

                for ($i = 1; $i <= count($etags); $i++) {
                    $parts['Parts'][$i] = ['PartNumber' => $i, 'ETag' => $etags[$i]];
                }

                $result = $s3->completeMultipartUpload([
                    'Bucket' => AWSUpload::source_bucket($config),
                    'Key' => $file_name,
                    'UploadId' => $upload_id,
                    'MultipartUpload' => $parts,
                ]);

                // remove attributes
                $vce->site->remove_attributes('upload_id');
                $vce->site->remove_attributes('part_number');
                $vce->site->remove_attributes('etags');

                // post variables to pass to create
                $post = [];

                // unset what is not passed on
                unset($_POST['extention'], $_POST['mimetype'], $_POST['timestamp'], $_POST['mediatypes']);

                // rekey $_POST key=>value to $post
                foreach ($_POST as $post_key => $post_value) {
                    $post[$post_key] = $post_value;
                }

                $post['media_type'] = AWSUpload::get_mime_name();
                $post['path'] = $file_name;

            }
        } catch (Exception $e) {
           	// $vce->log($e);
            die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: AWS problem: ' . $e->getMessage() . ' <div class="link-button cancel-button">Try Again</div>')));
        }

        if (isset($post)) {
            die(json_encode($post));
        }

        // Return Success JSON-RPC response
        // $vce->log('done!');
        die(json_encode(array('status' => 'success', 'message' => 'File has uploaded.')));

    }

    public static function set_header() {

        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }

        // No cache
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        header("Access-Control-Allow-Headers: Content-Type,Content-Range,Content-Disposition");
    }

    public static function get_mime_name() {

        $mimetype = !empty($_REQUEST['mimetype']) ? $_REQUEST['mimetype'] : 'application/' . $_REQUEST['extention'];

        // cycle through mediatypes that were passed through from functions media_type()
        foreach (json_decode($_REQUEST['mediatypes']) as $each_mediatype) {

            // check for subtype wildcard
            if (preg_match('/\.\*$/', $each_mediatype->mimetype)) {
                // match primaray type
                if (explode('/', $each_mediatype->mimetype)[0] == explode('/', $mimetype)[0]) {
                    return $each_mediatype->mimename;
                }
            } else {
                // match full
                if ($each_mediatype->mimetype == $mimetype) {
                    return $each_mediatype->mimename;
                }
            }
        }

        return ''; // not found
    }

    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe) {
        return false;
    }

}