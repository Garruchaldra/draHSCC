<?php
/*
* Create Tab
*/
global $vce;

// add javascript to page
$vce->site->add_script(dirname(__FILE__) . '/../js/create_tab.js');
// add css to page
$vce->site->add_style(dirname(__FILE__) . '/../css/create_tab.css');

/*
$tab_input = array (
    'tabs__container1' => array(
        'tabs' => array(
            'tab1' => array(
                'id' => 'tab-one',
                'label' => 'Tab One',
                'content' => '<div>This is content for Tab1</div>'
            ),
            'tab2' => array(
                'id' => 'tab-two',
                'label' => 'Tab Two',
                'content' => '<div>This is content for Tab2</div>'
            ),
            'tab3' => array(
                'id' => 'tab-three',
                'label' => 'Tab Three',
                'content' => '<div>This is content for Tab3</div>'
            ),
        ),
    ),
);

$tab_content1 = Pbc_utilities::create_tab($tab_input);
$content .= $tab_content1;
*/
// $vce->dump($input);
$insert = '';
if (count($input) > 0) {
    foreach($input as $tabs__container_key => $tabs__container_value) {
        $insert .= <<<EOF
        <section role="tablist" aria-label="content-tabs" class="tabs__container $tabs__container_key">
        <div class="tabs">
EOF;
        $i = 0;
        foreach($tabs__container_value['tabs'] as $tabs_key => $tabs_value) {
            $id = $tabs_value['id'];
            $label = $tabs_value['label'];
            $activeTab = '';
            $tab_visibility = (isset($tabs_value['visibility']) ? $tabs_value['visibility'] : '');

            if ($i == 0 && isset($tabs_value['visibility']) && $tabs_value['visibility'] !== false) {
                $activeTab = 'tabs__active';
            }
            if (isset($tabs_value['visibility']) && $tabs_value['visibility'] === true) {
                $activeTab = 'tabs__active';
            }

            $added_attributes = array();
            $reload = '';
            if (isset($tabs_value['reload']) && $tabs_value['reload'] === true) {
                $added_attributes[] = "reload='true'";
            }
            $tab_target = '';
            if (isset($tabs_value['tab_target'])) {
                $tt = $tabs_value['tab_target'];
                $added_attributes[] = "tab_target='$tt'";
            }

            if (isset($tabs_value['existing_query_string'])) {
                // $vce->dump($tabs_value['existing_query_string']);
                foreach ($tabs_value['existing_query_string'] as $k => $v) {
					$added_attributes[] = $k . "='" . $v ."'";
				}
				// $vce->dump($tabs_value['existing_query_string']);
            }

            				
            $attribute_string = '';
            foreach ($added_attributes as $attribute) {
                $attribute_string .= $attribute . ' ';
            }
            // $vce->dump($attribute_string);

            // the attributes in the attribute string are read by the create_tab.js in order to add them to the query string of the link for the button
            $insert .= <<<EOF
                <button role="tab" aria-controls="$id-tab" $attribute_string class="tabs__view tabs__tab shadow $activeTab" id="$tabs_key">$label</button>
EOF;

            $i++;
        }

        $insert .= <<<EOF
        <div class="tab-content">
EOF;

        $i = 0;
        foreach($tabs__container_value['tabs'] as $tabs_key => $tabs_value) {
            // $vce->dump($tabs_value);
            $content = $tabs_value['content'];
            $id = $tabs_value['id'];
            $hide = 'hide';

            $tab_visibility = (isset($tabs_value['visibility']) ? $tabs_value['visibility'] : '');
            if ($i == 0 && $tab_visibility !== false) {
                $hide = '';
            }
            if ($tab_visibility === true) {
                $hide = '';
            }

            $insert .= <<<EOF
                <div role="tabpanel" id="$id-tab" class="$tabs_key tabs__content-wrapper $tabs__container_key shadow $hide" aria-labelledby="$tabs_key"> 
                $content
                </div>
EOF;
            $i++;
        }

        $insert .= <<<EOF
        </div>
        </div>
        </section>
EOF;
    }

}
