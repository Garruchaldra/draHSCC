<?php

/**
 * Amazon aws dashboard
 *
 * @category   Admin
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';

/**
 * Amazon aws dashboard
 */
class AWSDashboard extends Component
{

    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            //basic component properties are required
            'name' => 'AWSDashboard',
            'description' => 'Amazon aws dashboard',
            'category' => 'admin',
            'recipe_fields' => array('auto_create','title')
        );
    }

    /**
     * things to do when this component is preloaded
     */
    public function preload_component()
    {

        $content_hook = array(
            'vce_call_add_functions' => 'AWSDashboard::vce_call_add_functions',
        );

        return $content_hook;
    }

    /**
     * add utility functions to VCE
     *
     * @param [VCE] $vce
     */
    public static function vce_call_add_functions($vce)
    {

        $vce->aws_put_file_contents = function ($filename, $data) use ($vce) {
            try {
                $config = AWSDashboard::get_config($vce);
                $sdk = AWSDashboard::get_sdk($config);
                $s3 = $sdk->createS3();
                $s3->registerStreamWrapper();
                $source_bucket = AWSDashboard::source_bucket($config);
                file_put_contents("s3://" . $source_bucket . "/" . $filename, $data);
            } catch (Exception $e) {
                $msg = $e->getMessage();
            }
        };

        $vce->aws_get_file_contents = function ($filename) use ($vce) {
            $data = null;
            try {
                $config = AWSDashboard::get_config($vce);
                $sdk = AWSDashboard::get_sdk($config);
                $s3 = $sdk->createS3();
                $s3->registerStreamWrapper();
                $source_bucket = AWSDashboard::source_bucket($config);
                $data = file_get_contents("s3://" . $source_bucket . "/" . $filename);
            } catch (Exception $e) {
                $msg = $e->getMessage();
            }
            return $data;
        };
    }

    /**
     * output to page
     */
    public function as_content($each_component, $vce)
    {

        // add javascript to page
        $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');

        $content = '<h1>AWS Dashboard</h1><br><br>';

        try {

            $content .= <<<EOF
            <h2>{$this->lang('Cognito Identity')}</h2>
            <div class="clickbar-container">
                <div class="clickbar-content no-padding clickbar-open">
                    <table id="eclist" class="tablesorter">
                        <thead>
                            <tr>
                                <th>{$this->lang('cognito_identity')}</th>
                            </tr>
                        </thead>
EOF;
            
                        $config = AWSDashboard::get_config($vce);
                        $cognito_client = AWSDashboard::create_cognito_client($config);
                       
 //                       foreach ($file_systems['FileSystems'] as $fs) {
//                            $name = $fs['FileSystemId'];
//                            $content .= <<<EOF
//                            <tr>
//                                <td class="align-center">$name</td>
//                            </tr>
//EOF;
//                        }



                        $content .= <<<EOF
                    </table>


                </div>
            </div><br><br>
EOF;

            $content .= <<<EOF
            <h2>{$this->lang('Elastic File Systems')}</h2>
            <div class="clickbar-container">
                <div class="clickbar-content no-padding clickbar-open">
                    <table id="eclist" class="tablesorter">
                        <thead>
                            <tr>
                                <th>{$this->lang('file_system')}</th>
                            </tr>
                        </thead>
EOF;
            
                        $config = AWSDashboard::get_config($vce);
                        $efs_client = AWSDashboard::create_efs_client($config);
                        $file_systems = AWSDashboard::describe_file_systems($efs_client, $config);
                       
                        foreach ($file_systems['FileSystems'] as $fs) {
                            $name = $fs['FileSystemId'];
                            $content .= <<<EOF
                            <tr>
                                <td class="align-center">$name</td>
                            </tr>
EOF;
                        }



                        $content .= <<<EOF
                    </table>


                </div>
            </div><br><br>
EOF;

$content .= <<<EOF
<h2>{$this->lang('Elastic Access Points')}</h2>
<div class="clickbar-container">
    <div class="clickbar-content no-padding clickbar-open">
        <table id="eclist" class="tablesorter">
            <thead>
                <tr>
                    <th>{$this->lang('access_point')}</th>
                    <th>{$this->lang('file_system')}</th>
                </tr>
            </thead>
EOF;

            $config = AWSDashboard::get_config($vce);
            $efs_client = AWSDashboard::create_efs_client($config);
            $access_points = AWSDashboard::describe_efs_access_points($efs_client, $config);
           
            foreach ($access_points['AccessPoints'] as $ap) {
                $name = $ap['AccessPointId'];
                $fs = $ap['FileSystemId'];
                $content .= <<<EOF
                <tr>
                    <td class="align-center">$name</td>
                    <td class="align-center">$fs</td>
                </tr>
EOF;
            }



            $content .= <<<EOF
        </table>

        
    </div>
</div><br><br>
EOF;

$content .= <<<EOF
<h2>{$this->lang('Elastic Mount Targets')}</h2>
<div class="clickbar-container">
    <div class="clickbar-content no-padding clickbar-open">
        <table id="eclist" class="tablesorter">
            <thead>
                <tr>
                    <th>{$this->lang('mount_target')}</th>
                    <th>{$this->lang('subnet')}</th>
                    <th>{$this->lang('availability_zone_name')}</th>
                    <th>{$this->lang('file_system')}</th>
                </tr>
            </thead>
EOF;

            $config = AWSDashboard::get_config($vce);
            $efs_client = AWSDashboard::create_efs_client($config);
            $mount_targets = AWSDashboard::describe_efs_mount_targets($efs_client, $config['elastic_file_system']['FileSystemId'], $config);
           
            foreach ($mount_targets['MountTargets'] as $mt) {
                $name = $mt['MountTargetId'];
                $subnet = $mt['SubnetId'];
                $availability_zone_name = $mt['AvailabilityZoneName'];
                $fs = $mt['FileSystemId'];
                $content .= <<<EOF
                <tr>
                    <td class="align-center">$name</td>
                    <td class="align-center">$subnet</td>
                    <td class="align-center">$availability_zone_name</td>
                    <td class="align-center">$fs</td>
                </tr>
EOF;
            }

            $name = "/storage/lorum.txt";
            $myfile = fopen($name, "w") or die("Unable to open file!");
            $txt = "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\n";
            fwrite($myfile, $txt);
            fclose($myfile);

            $myfile = fopen($name, "r");
            $s = fgets($myfile);
            $size = filesize($name);

            $content .= <<<EOF

            <tr>
                <td class="align-center">Test of: " . $name . "</td>
                <td class="align-center">$size</td>
                <td class="align-center">$s</td>
                <td class="align-center"></td>
            </tr>

        </table>

        
    </div>
</div><br><br>
EOF;

            $content .= <<<EOF
<h2>S3 Buckets</h2>
<div class="clickbar-container">
    <div class="clickbar-content no-padding clickbar-open">
        <table id="s3list" class="tablesorter">
            <thead>
                <tr>
                    <th>{$this->lang('bucket')}</th>
                    <th>{$this->lang('file')}</th>
                </tr>
            </thead>
EOF;
            // Use an Aws\Sdk class to create the S3Client object.
            $config = AWSDashboard::get_config($vce);
            $sdk = AWSDashboard::get_sdk($config);
            $s3 = $sdk->createS3();
            $result = $s3->listBuckets();

            $source_bucket = AWSDashboard::source_bucket($config);
            $dest_bucket = AWSDashboard::dest_bucket($config);

            foreach ($result['Buckets'] as $bucket) {
                $bucket_name = $bucket['Name'];
                if ($bucket_name === $source_bucket || $bucket_name === $dest_bucket) {
                    $objects = $s3->listObjects([
                        'Bucket' => $bucket_name,
                    ]);

                    foreach ($objects['Contents'] as $object) {
                        $file_name = $object['Key'];

                        $content .= <<<EOF
            <tr>
                <td class="align-center">$bucket_name</td>
                <td class="align-center">$file_name</td>
            </tr>
EOF;
                    }
                }
            }

            $content .= <<<EOF
        </table>
    </div>
</div><br><br>
EOF;

            $content .= <<<EOF
<h2>{$this->lang('Elastic Transcoder Pipelines')}</h2>
<div class="clickbar-container">
    <div class="clickbar-content no-padding clickbar-open">
        <table id="eclist" class="tablesorter">
            <thead>
                <tr>
                    <th>{$this->lang('Pipeline')}</th>
                    <th>{$this->lang('File')}</th>
                    <th>{$this->lang('Status')}</th>
                </tr>
            </thead>
EOF;

            $et = $sdk->createElasticTranscoder();
            $result = $et->listPipelines();

            foreach ($result['Pipelines'] as $pipeline) {
                $name = $pipeline['Name'];
                $jobs = $et->listJobsByPipeline(['PipelineId' => $pipeline['Id']]);
                foreach ($jobs['Jobs'] as $job) {
                    $file = $job['Input']['Key'];
                    $status = $job['Status'];

                    $content .= <<<EOF
            <tr>
                <td class="align-center">$name</td>
                <td class="align-center">$file</td>
                <td class="align-center">$status</td>
            </tr>
EOF;
                }
            }

            $content .= <<<EOF
        </table>
    </div>
</div><br><br>
EOF;

            $content .= <<<EOF
<h2>{$this->lang('CloudFront Distrobutions')}</h2>
<div class="clickbar-container">
    <div class="clickbar-content no-padding clickbar-open">
        <table id="eclist" class="tablesorter">
            <thead>
                <tr>
                    <th>{$this->lang('Domain')}</th>
                </tr>
            </thead>
EOF;

            $cf = $sdk->createCloudFront();
            $result = $cf->listDistributions();

            foreach ($result['DistributionList']['Items'] as $dist) {
                $domain = $dist['DomainName'];

                $content .= <<<EOF
            <tr>
                <td class="align-center">$domain</td>
            </tr>
EOF;
            }

            $content .= <<<EOF
        </table>
    </div>
</div><br><br>
EOF;

        } catch (Exception $e) {
            $msg = $e->getMessage();
            echo $msg;
        }

        $vce->content->add('main', $content);
    }

    /**
     * return form fields
     */
    public function component_configuration()
    {

        global $vce;

        $config = AWSDashboard::get_config($vce);

        if ($config['prefix'] != null) {
            try {
                AWSDashboard::check_aws_objects($vce);
            } catch (Exception $e) {
                AWSDashboard::create_aws_objects($vce);
            }
        }

        $prefix = $config['prefix'];
        $region = $config['region'];
        $mail_region = $config['mail_region'];
        $mail_sender = $config['mail_sender'];
        $version = $config['version'];
        $key = $config['credentials']['key'];
        $secret = $config['credentials']['secret'];
        $identity_pool_id = isset($config['credentials']['identity_pool_id']) ? $config['credentials']['identity_pool_id'] : null;
        $cf_key_id = $config['cloud_front']['cf_key_id'];
        $cf_key = $config['cloud_front']['cf_key'];
        $create_captions_activated = isset($config['create_captions_activated']) ? true : false;
        $captions_on_activated = isset($config['captions_on_activated']) ? true : false;
        $use_media_convert = isset($config['use_media_convert']) ? true : false;

        $FileSystemId = $config['elastic_file_system']['FileSystemId'];
        $AccessPointId = $config['elastic_file_system']['AccessPointId'];

        $x = $vce->content;
        $elements = "";
        
        $elements .= $x->create_checkbox_input('create_captions_activated', $create_captions_activated, $this->lang('create_captions_activated'));
        $elements .= $x->create_checkbox_input('captions_on_activated', $captions_on_activated, $this->lang('captions_on_activated'));
        $elements .= $x->create_checkbox_input('use_media_convert', $use_media_convert, $this->lang('use_media_convert'));
        $elements .= $x->create_text_input('prefix', $prefix, $this->lang('prefix'));
        $elements .= $x->create_text_input('region', $region, $this->lang('region'));
        $elements .= $x->create_text_input('mail_region', $mail_region, $this->lang('mail_region'));
        $elements .= $x->create_text_input('mail_sender', $mail_sender, $this->lang('mail_sender'));
        $elements .= $x->create_text_input('version', $version, $this->lang('version'));
        $elements .= $x->create_text_input('key', $key, $this->lang('key'));
        $elements .= $x->create_text_input('secret', $secret, $this->lang('secret'));
        $elements .= $x->create_text_input('identity_pool_id', $identity_pool_id, $this->lang('identity_pool_id'));
        $elements .= $x->create_text_input('cf_key_id', $cf_key_id, $this->lang('cf_key_id'));
        $elements .= $x->create_textarea_input('cf_key', $cf_key, $this->lang('cf_key'), true, null, 30);
        $elements .= $x->create_text_input('FileSystemId', $FileSystemId, $this->lang('FileSystemId'));
        $elements .= $x->create_text_input('AccessPointId', $AccessPointId, $this->lang('AccessPointId'));

        return $elements;
    }
    
}
