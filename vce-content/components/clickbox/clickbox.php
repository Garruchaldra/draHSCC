<?php

class Clickbox extends Component {

        /**
         * basic info about the component
         */
        public function component_info() {
                return array(
                        'name' => 'Clickbox',
                        'description' => 'Places sub components into a clickbox',
                        'category' => 'site'
                );
        }

        /**
         * start of clickbox
         */
        public function as_content($each_component, $page) {

$content = <<<EOF
<div class="clickbar-container">
<div class="clickbar-content">
EOF;

                $page->content->add('main',$content);


        }

        /**
         * end of clickbox
         */
        public function as_content_finish($each_component, $page) {

$content = <<<EOF
</div>
<div class="clickbar-title clickbar-closed"><span>$each_component->title</span></div>
</div>
EOF;

                $page->content->add('main',$content);

        }

        /**
         * fields for ManageRecipe
         */
        public function recipe_fields($recipe) {

                global $site;

                $title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
                $width = isset($recipe['width']) ? $recipe['width'] : 'default';
             

$elements = <<<EOF
<input type="hidden" name="auto_create" value="forward">
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>

<label>
<input type="text" name="width" value="$width"  autocomplete="off">
<div class="label-text">
<div class="label-message">Width</div>
<div class="label-error">Enter a Width</div>
</div>
</label>
EOF;

                return $elements;

        }

}