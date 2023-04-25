<?php

/**
 * AWS Media Controller
 *
 * @category   AWS
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';
require_once __DIR__ . '/../../../../vce-application/components/media/media.php';

/**
 * Controller for AWS Media
 *
 */
class AwsMediaController {

    use AWSSupport;

    /**
     * To get a list of all a user's media metadata:
     * 
     * GET
     * http://localhost:8888/washingtoncc/endpoint/aws_media?user_id=<user_id>&access_token=<token>
     * 
     * 
     * To tell coaching companion that a video has been uploaded:
     * 
     * POST
     * http://localhost:8888/washingtoncc/endpoint/aws_media
     * 
     * with json in the body, for example:
     * 
     * {"parent_id":"<parent_id>","name":"<name>","title":"<title>","path":"<path>","access_token":"<token>"}
     */
    public function create($data, $vce) {

		// Check args.  die if not correct.
        if (!isset($data->parent_id) || !isset($data->name) || !isset($data->title) || !isset($data->path)) {
			die();
        }
        
        $input = [];
        $input['type'] = 'Media';
        $input['parent_id'] = $data->parent_id;
        $input['name'] = $data->name;
        $input['title'] = $data->title;
        $input['media_type'] = 'AWSVideo';
        $input['path'] = $data->path;

        $auto_create = [];
        $auto_create['title'] = $data->title;
        $auto_create['auto_create'] = 'reverse';
        $auto_create['type'] = 'Assets';

        $components = [];
        $components['media_types'] = 'Image|AWSVideo|Powerpoint|Excel|Word|PDF|Audio';
        $components['description'] = 'Upload A Resource';
        $components['title'] = 'Media';
        $components['type'] = 'Media';
        $auto_create['components'] = [];
        $auto_create['components'][0] = $components;

        $input['auto_create'] = [];
        $input['auto_create'][0] = $auto_create;

        // This is a bit odd, but it works to create the component
        $input['procedure'] = 'create';
        $media = new Media();
        $media->vce = $vce;
        $media->form_input($input);
        echo "\n";

    }

    public function read($data, $vce) {

        $query = "SELECT component_id,parent_id,sequence,url FROM  " . TABLE_PREFIX . "components WHERE component_id='" . $data . "' LIMIT 1";
        $obj = $vce->db->get_data_object($query, $vce)[0];


        $this->add_meta($obj, "components_meta WHERE component_id='" . $obj->component_id . "'", $vce);

        // children (components)
        $query = "SELECT component_id FROM  " . TABLE_PREFIX . "components WHERE parent_id='" . $data . "'";
        $child_ids = $vce->db->get_data_object($query);

        if ($child_ids) {
            $obj->children = array();
            foreach ($child_ids as $key => $child_id) {
                $obj->children[] = $this->read($child_id->component_id, $vce);
            }
        }

        if (!empty($obj->path)) {
            $config = AwsMediaController::get_config($vce);
            $sdk = AwsMediaController::get_sdk($config);
            $aws_path = $config['prefix'] . '/' . $obj->path;
            $url = AwsMediaController::create_signed_url($aws_path, $sdk, $config);
            $url_class = new stdClass();
            $url_class->signed_url = $url;
            $obj->signed_url = $url_class->signed_url;
        }

        return $obj;
    }

    public function update($data, $vce) {

        // TODO
        die();
    }

    public function delete($data, $vce) {

        // TODO
        die();
    }

    public function exists($data, $vce) {

        // TODO
        die();
    }

    public function all_ids($data, $vce) {

        $c = TABLE_PREFIX . "components c";
        $m = TABLE_PREFIX . "components_meta m";
        $m2 = TABLE_PREFIX . "components_meta m2";

        $query = "
            select distinct c.component_id from {$c} 
            join {$m} on c.component_id = m.component_id
            join {$m2} on c.component_id = m2.component_id 
            where m.meta_key = 'created_by' and m.meta_value = {$data->user_id} and m2.meta_key = 'media_type'
        ";        

        $result = $vce->db->query($query);

        $ids = array();
        while ($row = $result->fetch_object()) {
            $ids[] = $row->component_id;
        }

        return $ids;
    }

    protected function add_meta($object, $sql, $vce) {

        $query = "SELECT meta_key,meta_value FROM  " . TABLE_PREFIX . $sql;
        $metadata = $vce->db->get_data_object($query);

        if ($metadata) {
            // look through metadata
            foreach ($metadata as $array_key => $each_metadata) {
                $value = json_decode($each_metadata->meta_value, true);
                if ($value == null) {
                    $value = $each_metadata->meta_value;
                }
                $object->{$each_metadata->meta_key} = $value;
            }
        }
    }

}
