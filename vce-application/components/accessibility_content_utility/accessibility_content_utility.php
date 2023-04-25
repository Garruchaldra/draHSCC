<?php

class AccessibilityContentUtility extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Accessibility Content Utility',
			'description' => 'Add utility functions to Content for Accessibility',
			'category' => 'accessibility',
			'recipe_fields' => false
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {

		$content_hook = array(
			'content_call_add_functions' => 'AccessibilityContentUtility::content_call_add_functions'
		);

		return $content_hook;
	}

	public static function content_call_add_functions($vce) {

		$vce->content->default_input_array = function ($type = '', $name = '', $value = '', $required = false, $placeholder = null, $class = null) use ($vce) {

			$data = array(
				'autocapitalize' => 'none',
			);

			if ($required) {
				$data['tag'] = 'required';
			};

			if (isset($placeholder)) {
				$data['placeholder'] = $placeholder;
			};

			if (isset($class)) {
				$data['class'] = $class;
			};

			$input = array(
				'type' => $type,
				'name' => $name,
				'value' => $value,
				'data' => $data
			);

			return $input;
		};

		$vce->content->create_text_input = function ($name, $value = null, $title = null, $required = true, $placeholder = null, $class = null) use ($vce) {

			$input = $vce->content->default_input_array('text', $name, $value, $required, $placeholder, $class);
			
			return $vce->content->create_input($input, $title, $title, $class, $placeholder);

		};

		$vce->content->create_textarea_input = function ($name, $value, $title = null, $required = true, $placeholder = null, $rows = 20) use ($vce) {

			$input = $vce->content->default_input_array('textarea', $name, $value, $required, $placeholder);
			$input['data']['class'] = 'textarea-wysiwyg';
			$input['data']['rows'] = $rows;

			return $vce->content->create_input($input, $title, $placeholder, 'top-padding', $placeholder);

		};

		$vce->content->create_checkbox_input = function ($name, $selected, $title = null) use ($vce) {

			$input = array(
				'type' => 'checkbox',
				'name' => $name,
				'options' => array(
					'value' => 'on',
					'selected' => $selected,
					'label' => $title
				)
			);
			
			return $vce->content->create_input($input, $title);


		};

		$vce->content->create_radio_input = function ($name, $title = null, $options = array()) use ($vce) {

			// make sure names match
			foreach($options as $o) {
				$o['name'] = $name;
			}

			$input = array(
				'type' => 'radio',
				'name' => $name,
				'options' => $options
			);
			
			return $vce->content->create_input($input, $title);

		};

		$vce->content->create_datepicker_input = function ($name, $value, $title = null, $required = true, $placeholder = null) use ($vce) {

			$value = isset($value) ? $value : date('m/d/Y', time());
			$input = $vce->content->default_input_array('text', $name, $value, $required, $placeholder);
			$input['data']['class'] = 'datepicker';
			return $vce->content->create_input($input, $title, $placeholder, null, $placeholder);
		};

		$vce->content->create_colorpicker_input = function ($name, $value, $title = null, $required = true, $placeholder = null) use ($vce) {

			$input = $vce->content->default_input_array('hidden', $name, $value, $required, $placeholder);
			$input['data']['class'] = 'color-picker';
			return $vce->content->create_input($input, $title, $placeholder, 'top-padding', $placeholder);
		};

		$vce->content->create_select_input = function ($name, $selected, $values, $title, $required = true, $placeholder = null) use ($vce) {

			$input = $vce->content->default_input_array('select', $name, $values, $required, $placeholder);
			$input['options'] = array();
			foreach ($values as $key => $v) {
				$a = [];
				$a['name'] = $v;
				$a['value'] = $key;
				if ($key == $selected) {
					$a['selected'] = true;
				}
				array_push($input['options'], $a);
			}
			return $vce->content->create_input($input, $title, $placeholder, null, $placeholder);
		};

		$vce->content->create_hidden_field = function ($name, $value) use ($vce) {
			return "<input type='hidden' name='$name' value='$value'>";
		};

		$vce->content->create_text_field = function ($value) use ($vce) {
			return "<div class='input-padding'><p>$value</p></div>";
		};

		$vce->content->create_form = function ($content, $component = null, $dossier = null, $create_text = 'Create', $cancel_text = 'Cancel', $class = 'asynchronous-form', $button_class = 'create-button') use ($vce) {

			if (!isset($dossier) && isset($component)) {
				$dossier = $vce->generate_dossier($component->dossier);
			} else {
				$dossier = $vce->generate_dossier($dossier);
			}

			$form = "<form class='$class' method='post' action='$vce->input_path' autocomplete='off'>";
			$form .= "<input type='hidden' name='dossier' value='$dossier'>";

			if (isset($component->template)) {
				$form .= "<input type='hidden' name='template' value='$component->template'>";
			}
			$form .= $content;

			$form .= "<input type='submit' value='$create_text', class='$button_class'>";
			if (isset($cancel_text)) {
				$form .= "<button class='link-button cancel-button'>$cancel_text</button>";
			}

			$form .= "</form>";

			return $form;
		};

		$vce->content->update_form = function ($content, $component = null, $dossier = null, $update_text = 'Update', $cancel_text = 'Cancel', $class = 'asynchronous-form', $button_class = 'update-button') use ($vce) {

			if (!isset($dossier) && isset($component)) {

				$created_at = isset($component->created_at) ? $component->created_at : null;

				// the instructions to pass through the form
				$dossier = array(
					'type' => $component->type,
					'procedure' => 'update',
					'component_id' => $component->component_id,
					'created_at' => $created_at
				);

				// generate dossier
				$dossier = $vce->generate_dossier($dossier);
			} else {
				$dossier = $vce->generate_dossier($dossier);
			}

			// this is the current template, but the recipe will control this.
			$template = isset($vce->template) ? $vce->template : null;

			$form = "<form class='$class' method='post' action='$vce->input_path' autocomplete='off'>";
			$form .= "<input type='hidden' name='dossier' value='$dossier'>";

			if ($template) {
				$form .= "<input type='hidden' name='template' value='$template'>";
			}

			$form .= $content;

			$form .= "<input type='submit' value='$update_text' class='$button_class'>";
			if (isset($cancel_text)) {
				$form .= "<button class='link-button cancel-button'>$cancel_text</button>";
			}

			$form .= "</form>";

			return $form;
		};

		$vce->content->delete_form = function ($content, $component = null, $dossier = null, $class = 'delete-form float-right-form asynchronous-form') use ($vce) {

			if (!isset($dossier) && isset($component)) {

				// the instructions to pass through the form
				$dossier = array(
					'type' => $component->type,
					'procedure' => 'delete',
					'component_id' => $component->component_id,
					'created_at' => $component->created_at,
					'parent_url' => $vce->requested_url
				);

				// generate dossier
				$dossier = $vce->generate_dossier($dossier);
			} else {
				$dossier = $vce->generate_dossier($dossier);
			}

			$form = "<form class='$class' method='post' action='$vce->input_path'>";
			$form .= "<input type='hidden' name='dossier' value='$dossier'>";
			$form .= "<input type='submit' value='Delete'></form>";
			$form = $content . $form;
			return $form;
		};

	}
		
}
