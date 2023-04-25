<?php

class Datapoints extends Component {
    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'Datapoints',
            'description' => 'Add datapoint test method',
            'category' => 'utilities'
        );
    }

    /**
     * these tests are unordered, but included to give some idea about how to use the datapoint functions.
     */
    public function datapoint_tests($each_component, $vce) {

        // set some values for the test:
        $user_id = $vce->user->user_id;
        $name = 'grandchild_name';
        $value = $vce->user->first_name.date('l jS \of F Y h:i:s A');
        $value2 = $vce->user->first_name.' '.date(DATE_RFC2822);
        // $vce->dump($value2);
        // exit;
    
        // $data = $vce->get_datapoint_datalist('personal_reports_presets');
    
        $attributes = array (
            'name'=>'personal_reports_presets',
            'component_id' => $each_component->component_id,
            'user_id'=> $vce->user->user_id,
        );
    
        $attributes = array (
            'name'=>'parent'.$value2,
            'component_id' => $each_component->component_id,
            'user_id'=> $vce->user->user_id,
            'some_attribute' => array (
                    'name'=>'child'.$value2,
                    'component_id' => $each_component->component_id,
                    'user_id'=> $vce->user->user_id,
                    'value' => array (
                        'name'=>'grandchild'.$value2,
                        'component_id' => $each_component->component_id,
                        'user_id'=> $vce->user->user_id,
                        'value' => 'final_value'
                    )
                )
            );
        
        // $attributes = $vce->get_datapoint_datalist($attributes);
        $data = $vce->read_datapoint_structure($attributes);
        $vce->dump($data);
    	exit;
    
    
        // result of reading the datapoint should be empty the 1st time $name is set:
        // $data = $vce->read_datapoint($attributes);
        // $data = $vce->get_datapoint_datalist(array('name'=>$name, 'component_type'=>get_class($this)));
        // $data = $vce->read_datapoint(array('name'=>$name, 'component_type'=>get_class($this)));
        // $vce->dump('1st read: <br>'.$data);
        // $vce->dump($data);
    	// exit;
        // set the datapoint (set_datapoint will both create and update) then read what was set
        // if it was set with a component_id or component_type, these must be included in read_datapoint, or it will dump an error asking for that attribute
        // $data = $vce->set_datapoint($attributes = array('name'=>$name,'value'=>$value, 'component_type'=>get_class($this)));
        $data = $vce->set_datapoint($attributes);
        
    
        $att = array (
            'name'=>'grandchild'.$value2,
            'component_id' => $each_component->component_id,
            // 'user_id'=> $vce->user->user_id,
            );
        $data = $vce->read_datapoint($att);
        $vce->dump($data);
        $vce->delete_datapoint($att);
        $data = $vce->read_datapoint($att);
        $vce->dump($data);
    	// exit;
        // set the same datapoint again, which updates the value. 
        $data = $vce->set_datapoint($attributes = array('name'=>$name,'value'=>$value2, 'component_type'=>get_class($this)));
        $data = $vce->read_datapoint($attributes = array('name'=>$name, 'component_type'=>get_class($this)));
        $vce->dump('3rd read: <br>'.$data);
    
        // delete the datapoint
        // $data = $vce->delete_datapoint($name);
        // $vce->dump($data);
        // $data = $vce->read_datapoint($name);
        // $vce->dump($data);
    
		exit;
    
    
    }
    

    
    
    
    public function as_content($each_component, $vce) {
        
        // for testing
        $this->datapoint_tests($each_component, $vce);
    
    }


	/**
	 * fields to display when this is created
	 */
	public function recipe_fields($recipe) {
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$url = isset($recipe['url']) ? $recipe['url'] : null;
	
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
<input type="text" name="url" value="$url" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">URL</div>
<div class="label-error">Enter a URL</div>
</div>
</label>
EOF;

		return $elements;
		
	}


}