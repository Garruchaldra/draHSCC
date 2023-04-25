<?php

/**
 * Simple About page
 *
 * @category   Site
 * @package    site
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */


/**
 * Amazon aws dashboard
 */
class About extends Component
{

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            //basic component properties are required
            'name' => 'About',
            'description' => 'About page',
            'category' => 'site',
            'recipe_fields' => array('auto_create','title')
        );
    }

    /**
     * output to page
     */
    public function as_content($each_component, $vce)
    {
        $content = file_get_contents(dirname(__FILE__) . '/lang/ohscc_about_eng.html');
        $vce->content->add('main', $content);
    }

}
