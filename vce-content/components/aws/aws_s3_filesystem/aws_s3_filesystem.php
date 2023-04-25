<?php

require_once __DIR__ . '/../aws_support.php';

class AWSS3Filesystem extends Component
{

    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            'name' => 'AWSS3Filesystem',
            'description' => 'Amazon s3 filesystem',
            'category' => 'admin',
        );
    }


    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe)
    {
        return false;
    }
}
