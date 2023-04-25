<?php

/**
 * Amazon aws support trait
 *
 * @category   Admin
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require 'aws_library/aws-autoloader.php';


/**
 * Amazon aws trait.
 */
trait AWSSupport
{

    /**
     * get the top level aws sdk object
     *
     * @param array $config aws configuration array
     * @return Aws/Sdk
     */
    public static function get_sdk($config)
    {

        return new Aws\Sdk($config);
    }

    /**
     * Retrieves configs that were set by the admin in the AWSDashboard component.
     *
     * @param VCE $vce
     * @return array the config
     */
    public static function get_config($vce)
    {

        $config = array();
        $config['prefix'] = '';
        $config['region'] = '';
        $config['mail_region'] = '';
        $config['mail_sender'] = '';
        $config['version'] = '';
        $config['credentials'] = array();
        $config['credentials']['key'] = '';
        $config['credentials']['secret'] = '';
        $config['cloud_front']['cf_key_id'] = '';
        $config['cloud_front']['cf_key'] = '';
        $config['elastic_file_system']['FileSystemId'] = '';
        $config['elastic_file_system']['AccessPointId'] = '';

        if (isset($vce->site->AWSDashboard)) {

            $value = $vce->site->AWSDashboard;
            $vector = $vce->site->AWSDashboard_minutia;
            $values = json_decode($vce->site->decryption($value, $vector), true);

            $config['create_captions_activated'] = isset($values['create_captions_activated']) ? $values['create_captions_activated'] : null;
            $config['captions_on_activated'] = isset($values['captions_on_activated']) ? $values['captions_on_activated'] : null;
            $config['use_media_convert'] = isset($values['use_media_convert']) ? $values['use_media_convert'] : null;
            $config['prefix'] = isset($values['prefix']) ? $values['prefix'] : '';
            $config['region'] = isset($values['region']) ? $values['region'] : 'us-west-2';
            $config['mail_region'] = isset($values['mail_region']) ? $values['mail_region'] : 'us-west-2';
            $config['mail_sender'] = isset($values['mail_sender']) ? $values['mail_sender'] : '';
            $config['version'] = isset($values['version']) ? $values['version'] : 'latest';
            $config['credentials']['key'] = isset($values['key']) ? $values['key'] : '';
            $config['credentials']['secret'] = isset($values['secret']) ? $values['secret'] : '';
            $config['credentials']['identity_pool_id'] = isset($values['identity_pool_id']) ? $values['identity_pool_id'] : '';
            $config['cloud_front']['cf_key_id'] = isset($values['cf_key_id']) ? $values['cf_key_id'] : '';
            $config['cloud_front']['cf_key'] = isset($values['cf_key']) ? $values['cf_key'] : '';

            $config['cloud_front']['cf_key'] = stripcslashes($config['cloud_front']['cf_key']);
            $config['elastic_file_system']['FileSystemId'] = isset($values['FileSystemId']) ? $values['FileSystemId'] : '';
            $config['elastic_file_system']['AccessPointId'] = isset($values['AccessPointId']) ? $values['AccessPointId'] : '';
        }

        return $config;
    }

    public static function update_config($config, $vce)
    {

        $save_config = array();

        $save_config['create_captions_activated'] = $config['create_captions_activated'];
        $save_config['captions_on_activated'] = $config['captions_on_activated'];
        $save_config['use_media_convert'] = $config['use_media_convert'];
        $save_config['prefix'] = $config['prefix'];
        $save_config['region'] = $config['region'];
        $save_config['mail_region'] = $config['mail_region'];
        $save_config['mail_sender'] = $config['mail_sender'];
        $save_config['version'] = $config['version'];
        $save_config['key'] = $config['credentials']['key'];
        $save_config['secret'] = $config['credentials']['secret'];
        $save_config['identity_pool_id'] = $config['credentials']['identity_pool_id'];
        $save_config['cf_key_id'] = $config['cloud_front']['cf_key_id'];
        $save_config['cf_key'] = $config['cloud_front']['cf_key'];
        $save_config['FileSystemId'] = $config['elastic_file_system']['FileSystemId'];
        $save_config['AccessPointId'] = $config['elastic_file_system']['AccessPointId'];

        $save_config = $vce->user->encryption(json_encode($save_config), $vce->site->AWSDashboard_minutia);

        $update = array('meta_value' => $save_config, 'minutia' => $vce->site->AWSDashboard_minutia);
        $update_where = array('meta_key' => 'AWSDashboard');
        $vce->db->update('site_meta', $update, $update_where);
        $vce->site->AWSDashboard = $save_config;
    }

    /**
     * return the name of the aws s3 source file bucket
     *
     * @param array $config
     * @return string
     */
    public static function source_bucket($config)
    {
        return $config['prefix'] . '-source';
    }

    /**
     * return the name of the aws s3 destination file bucket
     *
     * @param array $config
     * @return string
     */
    public static function dest_bucket($config)
    {
        return $config['prefix'] . '-dest';
    }

    /**
     * Creates all the objects up on aws that we will need.
     * Only call this once after an install
     *
     * @param VCE $vce
     * @return void
     */
    public static function create_aws_objects($vce)
    {

        $results = array();

        $config = AWSSupport::get_config($vce);
        $sdk = AWSSupport::get_sdk($config);

        $msg = '';

        try {
            ini_set('max_execution_time', 1000);
            AWSSupport::create_s3($sdk, $config);
        } catch (Exception $e) {
            $msg .= $e->getMessage();
        }

        try {
            ini_set('max_execution_time', 1000);
            AWSSupport::create_transcoder($sdk, $config);
        } catch (Exception $e) {
            $msg .= $e->getMessage();
        }

        try {
            ini_set('max_execution_time', 1000);
            AWSSupport::create_cloud_front($sdk, $config);
        } catch (Exception $e) {
            $msg .= $e->getMessage();
        }

        try {
            AWSSupport::create_default_elastic_file_system($config, $vce);
        } catch (Exception $e) {
            $msg .= $e->getMessage();
        }

        return $msg;
    }

    /**
     * Check the aws setup.  Throw exception if something wrong or not set up.
     *
     * @param VCE $vce
     * @return void
     */
    public static function check_aws_objects($vce)
    {

        // maybe use this: site_hook_initiation

        $config = AWSSupport::get_config($vce);
        if ($config['prefix'] == null) {
            throw new Exception('Please configure the AWS dashboard component.');
        }
        $sdk = AWSSupport::get_sdk($config);
        $s3 = $sdk->createS3();
        $result = $s3->listBuckets();

        $source_bucket_exists = false;
        $dest_bucket_exists = false;
        foreach ($result['Buckets'] as $bucket) {
            $bucket_name = $bucket['Name'];
            if (AWSSupport::source_bucket($config) == $bucket_name) {
                $source_bucket_exists = true;
            }
            if (AWSSupport::dest_bucket($config) == $bucket_name) {
                $dest_bucket_exists = true;
            }
        }
        if (!$source_bucket_exists || !$dest_bucket_exists) {
            throw new Exception('S3 buckets do not exist.  Please reinitialize the AWS component.');
        }

        $cf = $sdk->createCloudFront();
        $distributions = $cf->listDistributions();
        $cf_domain = $distributions['DistributionList']['Items'][0]['DomainName'];
        if ($cf_domain == null) {
            throw new Exception('CloudFront distribution does not exist.  Please reinitialize the AWS component.');
        }

        $efs_client = AWSSupport::create_efs_client($config);
        $file_systems = AWSDashboard::describe_file_systems($efs_client, $config);
        if (sizeof($file_systems['FileSystems']) == 0) {
            throw new Exception('Elastic File System does not exist.  Please reinitialize the AWS component.');
        }
    }

    /**
     * create all s3 related objects on aws
     *
     * @param SDK $sdk
     * @param array $config
     * @return void
     */
    public static function create_s3($sdk, $config)
    {
        // Create our buckets
        $s3 = $sdk->createS3();
        $s3->createBucket(['Bucket' => AWSSupport::source_bucket($config)]);
        $s3->createBucket(['Bucket' => AWSSupport::dest_bucket($config)]);

        $s3->putBucketCors([
            'Bucket' => AWSSupport::dest_bucket($config),
            'CORSConfiguration' => [
                'CORSRules' => [
                    [
                        'AllowedHeaders' => ['Authorization'],
                        'AllowedMethods' => ['GET'],
                        'AllowedOrigins' => ['*'],
                        'ExposeHeaders' => [],
                        'MaxAgeSeconds' => 3000,
                    ],
                ],
            ],
        ]);
    }

    /**
     * create all media convert object on aws
     *
     * @param SDK $sdk
     * @param array $config
     * @return void
     */
    public static function create_media_convert_client($sdk, $config)
    {

        $mediaConvertClient = new \Aws\MediaConvert\MediaConvertClient([
            'version' => $config['version'],
            'region' => $config['region'],
            'profile' => 'default',
            'endpoint' => 'ACCOUNT_ENDPOINT'
        ]);
        
    }

    /**
     * create all media transcoder object on aws
     *
     * @param SDK $sdk
     * @param array $config
     * @return void
     */
    public static function create_transcoder($sdk, $config)
    {

        $result = null;

        if ($config['use_media_convert']) {

            $client = new Aws\MediaConvert\MediaConvertClient([
                'version' => $config['version'],
                'region' => $config['region'],
                'credentials' => [
                    'key'    => $config['credentials']['key'],
                    'secret' => $config['credentials']['secret'],
                ],
            ]);
            
            //retrieve endpoint
            try {
                $result = $client->describeEndpoints([]);
            } catch (Exception $e) {
                // output error message if fails
                echo $e->getMessage();
                echo "\n";
            }
            // snippet-end:[mediaconvert.php.get_endpoint.region]
            // snippet-start:[mediaconvert.php.get_endpoint.main]
            $single_endpoint_url = $result['Endpoints'][0]['Url'];
            
            //Create an AWSMediaConvert client object with the endpoint URL that you retrieved: 
            $result = new Aws\MediaConvert\MediaConvertClient([
                'version' => $config['version'],
                'region' => $config['region'],
                'endpoint' => $single_endpoint_url,
                'credentials' => [
                    'key'    => $config['credentials']['key'],
                    'secret' => $config['credentials']['secret'],
                ],
            ]);



        } else {

            // create transcoder pipeline
            $et = $sdk->createElasticTranscoder();

            $iam = $sdk->createIam();
            $user = $iam->getUser();
            $output = [];
            preg_match("/arn:aws:iam::(\d+)/", $user['User']['Arn'], $output);
            $role = $output[0] . ':role/Elastic_Transcoder_Default_Role';

            // Create the pipeline
            $result = $et->createPipeline(array(
                'Name' => $config['prefix'],
                'InputBucket' => AWSSupport::source_bucket($config),
                'OutputBucket' => AWSSupport::dest_bucket($config),
                'Role' => $role,
            ));
        }

        return $result;
    }

    /**
     * run a transcoder job on aws
     *
     * @param SDK $sdk
     * @param array $config
     * @return the job id
     */
    public static function do_transcode($filename, $sdk, $config)
    {

        if ($config['use_media_convert']) {

            //Create an AWSMediaConvert client object with your account specific endpoint.
            $mcc = AWSSupport::create_transcoder($sdk, $config);

            $source_bucket = AWSSupport::source_bucket($config);
            $dest_bucket = AWSSupport::dest_bucket($config);

            $input_file_path = "s3://{$source_bucket}/{$filename}";
            $output_file_path = "s3://{$dest_bucket}/{$config['prefix']}/";

            $jobSetting = [
                "Settings" => [
                    "TimecodeConfig" => [
                        "Source" => "ZEROBASED"
                    ]
                ],
                "OutputGroups" => [
                    [
                        "OutputGroupSettings" => [
                            "Type" => "FILE_GROUP_SETTINGS",
                            "FileGroupSettings" => [
                                "Destination" => $output_file_path
                            ]
                        ],

                        "Outputs" => [
                            [
                                "VideoDescription" => [
                                    "ScalingBehavior" => "DEFAULT",
                                    "TimecodeInsertion" => "DISABLED",
                                    "AntiAlias" => "ENABLED",
                                    "Sharpness" => 50,
                                    "CodecSettings" => [
                                        "Codec" => "H_264",
                                        "H264Settings" => [
                                            "InterlaceMode" => "PROGRESSIVE",
                                            "NumberReferenceFrames" => 3,
                                            "Syntax" => "DEFAULT",
                                            "Softness" => 0,
                                            "GopClosedCadence" => 1,
                                            "GopSize" => 90,
                                            "Slices" => 1,
                                            "GopBReference" => "DISABLED",
                                            "SlowPal" => "DISABLED",
                                            "SpatialAdaptiveQuantization" => "ENABLED",
                                            "TemporalAdaptiveQuantization" => "ENABLED",
                                            "FlickerAdaptiveQuantization" => "DISABLED",
                                            "EntropyEncoding" => "CABAC",
                                            "Bitrate" => 5000000,
                                            "FramerateControl" => "SPECIFIED",
                                            "RateControlMode" => "CBR",
                                            "CodecProfile" => "MAIN",
                                            "Telecine" => "NONE",
                                            "MinIInterval" => 0,
                                            "AdaptiveQuantization" => "HIGH",
                                            "CodecLevel" => "AUTO",
                                            "FieldEncoding" => "PAFF",
                                            "SceneChangeDetect" => "ENABLED",
                                            "QualityTuningLevel" => "SINGLE_PASS",
                                            "FramerateConversionAlgorithm" => "DUPLICATE_DROP",
                                            "UnregisteredSeiTimecode" => "DISABLED",
                                            "GopSizeUnits" => "FRAMES",
                                            "ParControl" => "SPECIFIED",
                                            "NumberBFramesBetweenReferenceFrames" => 2,
                                            "RepeatPps" => "DISABLED",
                                            "FramerateNumerator" => 30,
                                            "FramerateDenominator" => 1,
                                            "ParNumerator" => 1,
                                            "ParDenominator" => 1
                                        ]
                                    ],
                                    "AfdSignaling" => "NONE",
                                    "DropFrameTimecode" => "ENABLED",
                                    "RespondToAfd" => "NONE",
                                    "ColorMetadata" => "INSERT"
                                ],
                                "AudioDescriptions" => [
                                    [
                                        "CodecSettings" => [
                                            "Codec" => "AAC",
                                            "AacSettings" => [
                                                "Bitrate" => 96000,
                                                "CodingMode" => "CODING_MODE_2_0",
                                                "SampleRate" => 48000
                                            ]
                                        ]
                                    ]
                                ],
                                "ContainerSettings" => [
                                    "Container" => "MP4",
                                    "Mp4Settings" => [
                                        "CslgAtom" => "INCLUDE",
                                        "FreeSpaceBox" => "EXCLUDE",
                                        "MoovPlacement" => "PROGRESSIVE_DOWNLOAD"
                                    ]
                                ]
                            ],
                            [
                                "ContainerSettings" => [
                                    "Container" => "RAW"
                                ],
                                "VideoDescription" => [
                                    "CodecSettings" => [
                                        "Codec" => "FRAME_CAPTURE",
                                        "FrameCaptureSettings" => [
                                            "FramerateNumerator" => 30,
                                            "FramerateDenominator" => 88,
                                            "MaxCaptures" => 1,
                                            "Quality" => 80
                                        ]
                                    ]
                                ],
                                "Extension" => "jpg"
                            ]
                        ]
                    ]
                ],
                "Inputs" => [
                    [
                        "AudioSelectors" => [
                            "Audio Selector 1" => [
                                "DefaultSelection" => "DEFAULT"
                            ]
                        ],
                        "VideoSelector" => [
                            "Rotate" => "AUTO",
                            "ColorSpace" => "FOLLOW"
                        ],
                        "FilterEnable" => "AUTO",
                        "PsiControl" => "USE_PSI",
                        "FilterStrength" => 0,
                        "DeblockFilter" => "DISABLED",
                        "DenoiseFilter" => "DISABLED",
                        "TimecodeSource" => "EMBEDDED",
                        "FileInput" => $input_file_path
                    ]
                ]
            ];

            try {
                $iam = $sdk->createIam();
                $user = $iam->getUser();
                $output = [];
                preg_match("/arn:aws:iam::(\d+)/", $user['User']['Arn'], $output);
                $role = $output[0] . ':role/mediaconvert';

                $job = $mcc->createJob([
                    "Role" => $role,
                    "Settings" => $jobSetting,
                ]);
            } catch (Exception $e) {
                // output error message if fails
                echo $e->getMessage();
                echo "\n";
            }

        } else {

            // Create the job to convert to web format
            $pipeline_id = AWSSupport::get_pipeline_id($sdk, $config);
            $et = $sdk->createElasticTranscoder();
            $job = $et->createJob(array(

                'PipelineId' => $pipeline_id,

                'OutputKeyPrefix' => $config['prefix'] . '/',

                'Input' => array(
                    'Key' => $filename,
                    'FrameRate' => 'auto',
                    'Resolution' => 'auto',
                    'AspectRatio' => 'auto',
                    'Interlaced' => 'auto',
                    'Container' => 'auto',
                ),

                'Outputs' => array(
                    array(
                        'Key' => $filename,
                        'Rotate' => 'auto',
                        'PresetId' => '1351620000001-100070', // Web: Facebook, SmugMug, Vimeo, YouTube
                        'ThumbnailPattern' => $filename . '{count}',
                    ),
                )
            ));
        }

        return $job['Job']['Id'];
    }

    /**
     * return the path of the thumbnail image
     *
     * @param SDK $sdk
     * @param array $config
     * @return string path or null.
     * 
     */
    public static function get_thumbnail_path($filename, $sdk, $config) {

		if (!AWSSupport::file_exists(AWSSupport::dest_bucket($config), $filename . '.0000000.jpg', $sdk, $config)) {
            return $filename . '.0000000.jpg';
        } else if (!AWSSupport::file_exists(AWSSupport::dest_bucket($config), $filename . '00001.png', $sdk, $config)) {
            return $filename . '00001.png';
        } else {
            return null;
        }
    }

    /**
     * return the id of the elastic transcoder pipeline
     *
     * @param string $job_id job id
     * @param SDK $sdk
     * @param array $config
     * @return string job status
     */
    public static function get_transcode_job_status($job_id, $sdk, $config)
    {

        if ($config['use_media_convert']) {

            $mc = AWSSupport::create_transcoder($sdk, $config);
            $results = $mc->getJob(['Id' => $job_id]);
            return $results['Job']['Status'];

        } else {

            try {

                $et = $sdk->createElasticTranscoder();
    
                $results = $et->readJob(['Id' => $job_id]);
                return $results->get('Job')['Status'];
    
            } catch (Exception $e) {
    
                // We assume everything is ok is the job no longer exists.
                return "Complete";
    
            }
            
        }
    }


    /**
     * create all CloudFront related objects on aws
     *
     * @param SDK $sdk
     * @param array $config
     * @return void
     */
    public static function create_cloud_front($sdk, $config)
    {
        $cf = $sdk->createCloudFront();

        // Create identity
        $oai = $cf->createCloudFrontOriginAccessIdentity([
            'CloudFrontOriginAccessIdentityConfig' => [
                'CallerReference' => $config['prefix'],
                'Comment' => 'OAI used for private distribution access to ' . AWSSupport::dest_bucket($config),
            ],
        ]);
        $oai_id = 'origin-access-identity/cloudfront/' . $oai['CloudFrontOriginAccessIdentity']['Id'];
        $oai_canonical_user_id = $oai['CloudFrontOriginAccessIdentity']['S3CanonicalUserId'];

        // get our account number
        $iam = $sdk->createIam();
        $user = $iam->getUser();
        $output = [];
        preg_match("/arn:aws:iam::([0-9]{12}):.*$/", $user['User']['Arn'], $output);
        $account_number = $output[1];

        // create distribution
        $bucket = AWSSupport::dest_bucket($config);
        $distribution = $cf->createDistribution([
            'DistributionConfig' => [
                "Comment" => 'private distribution access to ' . $bucket,
                "CacheBehaviors" => [
                    "Quantity" => 0,
                ],
                "Logging" => [
                    "Bucket" => $bucket,
                    "Prefix" => $config['prefix'],
                    "Enabled" => false,
                    "IncludeCookies" => false,
                ],
                "Origins" => [
                    "Items" => [
                        [
                            "S3OriginConfig" => [
                                "OriginAccessIdentity" => $oai_id,
                            ],
                            "Id" => $bucket,
                            "DomainName" => $bucket . '.s3.amazonaws.com',
                        ],
                    ],
                    "Quantity" => 1,
                ],
                "DefaultRootObject" => null,
                "PriceClass" => "PriceClass_All",
                "Enabled" => true,
                "DefaultCacheBehavior" => [
                    "TrustedSigners" => [
                        "Enabled" => true,
                        "Items" => [
                            $account_number,
                        ],
                        "Quantity" => 1,
                    ],
                    "TargetOriginId" => AWSSupport::dest_bucket($config),
                    "ViewerProtocolPolicy" => "allow-all",
                    "ForwardedValues" => [
                        "Headers" => [
                            "Quantity" => 0,
                        ],
                        "Cookies" => [
                            "Forward" => "none",
                        ],
                        "QueryString" => false,
                    ],
                    "SmoothStreaming" => false,
                    "AllowedMethods" => [
                        "Items" => [
                            "GET",
                            "HEAD",
                        ],
                        "Quantity" => 2,
                    ],
                    "MinTTL" => 0,
                ],
                "CallerReference" => $config['prefix'],
                "ViewerCertificate" => [
                    "CloudFrontDefaultCertificate" => true,
                ],
                "CustomErrorResponses" => [
                    "Quantity" => 0,
                ],
                "Restrictions" => [
                    "GeoRestriction" => [
                        "RestrictionType" => "none",
                        "Quantity" => 0,
                    ],
                ],
                "Aliases" => [
                    "Quantity" => 0,
                ],
            ],
        ]);

        // set bucket policy
        $s3 = $sdk->createS3();
        $policy = '{
                "Id": "' . $config['prefix'] . '-policy",
                "Statement":
                [
                  {
                    "Sid": "' . $config['prefix'] . '-statement",
                    "Action": ["s3:GetObject"],
                    "Effect": "Allow",
                    "Resource": "arn:aws:s3:::' . $bucket . '/*",
                    "Principal": {"CanonicalUser": ["' . $oai_canonical_user_id . '"]}
                  }
                ]
            }';

        $result = $s3->putBucketPolicy([
            'Bucket' => $bucket,
            'Policy' => $policy,
        ]);
    }

    /**
     * Create a signed url for cloudfront secury delivery
     *
     * @param string $aws_path
     * @param sdk $sdk
     * @param array $config
     * @return string the signed url
     */
    public static function create_signed_url($aws_path, $sdk, $config, $timeout = 1200)
    {

        $key_file = AWSSupport::create_key_file($config);
        $cf = $sdk->createCloudFront();

        $cf_domain = AWSSupport::get_distribution_domain($sdk, $config);

        $resource_key = 'https://' . $cf_domain . '/' . $aws_path;
        $key_pair_id = $config['cloud_front']['cf_key_id'];
        $expires = time() + $timeout;

        $url = AWSSupport::do_create_signed_url($cf, $resource_key, $key_file, $key_pair_id, $expires);

        unlink($key_file);

        return $url;
    }

    public static function do_create_signed_url($cf, $resource_key, $key_file, $key_pair_id, $expires)
    {

        $custom_policy = <<<POLICY
{
    "Statement": [
        {
            "Resource": "{$resource_key}",
            "Condition": {
                "DateLessThan": {"AWS:EpochTime": {$expires}}
            }
        }
    ]
}
POLICY;

        $url = $cf->getSignedUrl([
            'url' => $resource_key,
            'policy' => $custom_policy,
            'private_key' => $key_file,
            'key_pair_id' => $key_pair_id,
        ]);

        return $url;
    }

    public static function create_key_file($config)
    {

        $key_file = tempnam(sys_get_temp_dir(), $config['cloud_front']['cf_key_id']);

        $handle = fopen($key_file, 'w');
        fwrite($handle, $config['cloud_front']['cf_key']);
        fclose($handle);

        return $key_file;
    }

    public static function put_file($file_name, $contents, $bucket, $sdk)
    {

        $s3 = $sdk->createS3();

        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $file_name,
            'Body' => $contents,
        ]);

        return $result;
    }

    /**
     * check if a file exists on s3
     *
     * @param string $bucket
     * @param string $aws_path
     * @param SDK $sdk
     * @param array $config
     * @return boolean true if file exists
     */
    public static function file_exists($bucket, $aws_path, $sdk, $config)
    {
        $s3 = $sdk->createS3();
        try {
            if ($s3->doesObjectExist($bucket, $aws_path)) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * get file stream from s3.
     * you can use this path with php function such as readfile
     *
     * @param string $bucket
     * @param string $aws_path
     * @param SDK $sdk
     * @return string
     */
    public static function get_file_stream($bucket, $aws_path, $sdk)
    {

        $s3 = $sdk->createS3();
        $s3->registerStreamWrapper();
        return 's3://' . $bucket . '/' . $aws_path;
    }

    /**
     * get file uri from s3.
     *
     * @param string $bucket
     * @param string $aws_path
     * @param SDK $sdk
     * @return string
     */
    public static function get_file_uri($bucket, $aws_path, $sdk)
    {

        $s3 = $sdk->createS3();
        return $s3->getObjectUrl($bucket, $aws_path);
    }

    /**
     * delete a file on s3
     *
     * @param string $bucket
     * @param string $aws_path
     * @param SDK $sdk
     * @param array $config
     * @return void
     */
    public static function delete_file($bucket, $aws_path, $sdk, $config)
    {
        $s3 = $sdk->createS3();
        $s3->deleteObject(['Bucket' => $bucket, 'Key' => $aws_path]);
    }

    public static function move_file($targetBucket, $targetName, $sourceBucket, $sourceName, $sdk, $config)
    {

        $s3 = $sdk->createS3();

        // Copy an object.
        $s3->copyObject([
            'Bucket'     => $targetBucket,
            'Key'        => $targetName,
            'CopySource' => "{$sourceBucket}/{$sourceName}",
        ]);
    }

    /**
     * return the domain of the CloudFront distribution
     *
     * @param SDK $sdk
     * @param array $config
     * @return string the domain
     */
    public static function get_distribution_domain($sdk, $config)
    {
        $cf = $sdk->createCloudFront();
        $results = $cf->listDistributions();
        foreach ($results['DistributionList']['Items'] as $dist) {
            if ($dist['Comment'] == 'private distribution access to ' . AWSSupport::dest_bucket($config)) {
                return $dist['DomainName'];
            }
        }

        throw new Exception('CloudFront distribution does not exist.  Please reinitialize the AWS component.');
    }

    /**
     * return the id of the elastic transcoder pipeline
     *
     * @param SDK $sdk
     * @param array $config
     * @return integer pipeline id
     */
    public static function get_pipeline_id($sdk, $config)
    {
        $et = $sdk->createElasticTranscoder();

        $results = $et->listPipelines();
        foreach ($results['Pipelines'] as $pipeline) {
            if ($pipeline['Name'] == $config['prefix']) {
                return $pipeline['Id'];
            }
        }

        throw new Exception('Transcoder pipeline does not exist.  Please reinitialize the AWS component.');
    }



    /**
     * create an s3 transfer manager
     *
     * @param SDK $sdk
     * @param array $config
     * @return string the domain
     */
    public static function create_transfer_manager($source, $dest, $sdk, $config)
    {
        $s3 = $sdk->createS3();
        return new \Aws\S3\Transfer($s3, $source, 's3://' . $dest);
    }

    public static function send_mail($from, $to, $cc, $bcc, $subject, $message, $sdk, $config)
    {

        // we need to use the mail_region for region here.
        $temp_config = $config;
        $temp_config['region'] = isset($config['mail_region']) ? $config['mail_region'] : $config['region'];
        $temp_sdk = new Aws\Sdk($temp_config);
        $ses = $temp_sdk->createSes();

        $char_set = 'UTF-8';

        $to_emails = AWSSupport::format_emails($to);
        $cc_emails = AWSSupport::format_emails($cc);
        $bcc_emails = AWSSupport::format_emails($bcc);

        try {
            $result = $ses->sendEmail([
                'Destination' => [
                    'ToAddresses' => $to_emails,
                    'CcAddresses' => $cc_emails,
                    'BccAddresses' => $bcc_emails,
                ],
                'Source' => AWSSupport::format_emails($from)[0],
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => $char_set,
                            'Data' => $message,
                        ],
                        'Text' => [
                            'Charset' => $char_set,
                            'Data' => $message,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => $char_set,
                        'Data' => $subject,
                    ],
                ],
            ]);

            return $result['MessageId'];
        } catch (Exception $e) {
            // output error message if fails
            $msg = $e->getMessage();

            return "ERROR: " . $msg;
        }
    }

    public static function format_emails($emails)
    {

        $formatted_emails = [];
        if (sizeof($emails) > 0) {
            $each_values = array_values($emails);
            if (is_array($each_values[0])) {
                foreach ($each_values as $sub_key => $sub_value) {
                    $address = isset($sub_value[0]) ? $sub_value[0] : null;
                    $name = isset($sub_value[1]) ? $sub_value[1] : null;
                    array_push($formatted_emails, trim($name) . ' <' . $address . '>');
                }
            } else {
                $address = isset($each_values[0]) ? $each_values[0] : null;
                $name = isset($each_values[1]) ? $each_values[1] : null;
                array_push($formatted_emails, trim($name) . ' <' . $address . '>');
            }
        }

        return $formatted_emails;
    }

    // Elastic File System

    public static function create_default_elastic_file_system($config, $vce)
    {

        $efs_client = AWSSupport::create_efs_client($config);
        $file_systems = AWSDashboard::describe_file_systems($efs_client, $config);
        if (sizeof($file_systems['FileSystems']) == 0) {

            // Create the file system
            $fs = AWSDashboard::create_efs_file_system($efs_client, $config);
            $config['elastic_file_system']['FileSystemId'] = $fs['FileSystemId'];

            // Simple way to sleep until the file system is available
            do {
                sleep(1);
            } while (AWSDashboard::efs_file_system_available($efs_client, $fs['FileSystemId'], $config) == false);

            // Create mount targets
            AWSDashboard::create_efs_mount_targets($efs_client, $fs['FileSystemId'], $config);

            // Create the access point for /
            $ap = AWSDashboard::create_efs_access_point($efs_client, $fs['FileSystemId'], "/", $config);
            $config['elastic_file_system']['AccessPointId'] = $ap['AccessPointId'];

            AWSDashboard::update_config($config, $vce);
        }
    }

    public static function efs_file_system_available($efs_client, $file_system_id, $config)
    {

        $result = $efs_client->describeFileSystems([
            'FileSystemId' => $file_system_id,
        ]);

        return $result['FileSystems'][0]['LifeCycleState'] == 'available';
    }

    /**
     * create an efs client
     *
     * @param SDK $sdk
     * @param array $config
     * @return object efs client
     */
    public static function create_efs_client($config)
    {

        $efs_client = Aws\Efs\EfsClient::factory($config);

        return $efs_client;
    }

    public static function create_efs_file_system($efs_client, $config)
    {

        $efs_drive = $efs_client->createFileSystem([
            'CreationToken' => $config['credentials']['secret']
        ]);

        return $efs_drive;
    }

    public static function create_efs_access_point($efs_client, $file_system_id, $path, $config)
    {

        $result = $efs_client->createAccessPoint([
            'ClientToken' =>  $config['credentials']['secret'],
            'FileSystemId' => $file_system_id,
            'RootDirectory' => [
                'Path' => $path,
            ],
        ]);

        return $result;
    }


    public static function describe_efs_access_points($efs_client, $config)
    {
        $result = $efs_client->describeAccessPoints([
            'FileSystemId' => $config['elastic_file_system']['FileSystemId'],
        ]);

        return $result;
    }

    public static function create_efs_mount_targets($efs_client, $file_system_id, $config)
    {

        $subnets = AWSSupport::describe_subnets($config);

        foreach ($subnets['Subnets'] as $az) {

            $efs_client->createMountTarget([
                'FileSystemId' => $file_system_id,
                'SubnetId' => $az['SubnetId'],
            ]);
        }
    }

    public static function describe_efs_mount_targets($efs_client, $file_system_id, $config)
    {

        $result = $efs_client->describeMountTargets([
            'FileSystemId' => $file_system_id,
        ]);

        return $result;
    }

    public static function describe_file_systems($efs_client, $config)
    {

        return $efs_client->describeFileSystems();
    }

    public static function describe_availability_zones($config)
    {

        $ec2_client = Aws\Ec2\Ec2Client::factory($config);

        return $ec2_client->describeAvailabilityZones();
    }

    public static function describe_subnets($config)
    {

        $ec2_client = Aws\Ec2\Ec2Client::factory($config);

        return $ec2_client->describeSubnets();
    }

    // Identity pool

    public static function create_cognito_client($config)
    {

        $identity_client = Aws\CognitoIdentity\CognitoIdentityClient::factory($config);
        return $identity_client;
    }

    public static function create_identity_pool($cognito_client, $config)
    {

        $result = $cognito_client->createIdentityPool([
            'AllowUnauthenticatedIdentities' => true,
            'IdentityPoolName' => $config['prefix'],
        ]);

        return $result;
    }
}
