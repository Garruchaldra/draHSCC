<?php

class Pbc_review extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Review',
			'description' => 'Review of PBC Cycle',
			'category' => 'pbc'
		);
	}

	/**
	 * adding assignment specific mata to page object
	 */
	public function check_access($each_component, $page) {

		// adding assignment specific mata to page object
		$page->assignment_id = $each_component->component_id;
		$page->assignment_type = $each_component->type;


	
		return true;
	}

	/**
	 * last component was the requested id, so generate links for this component
	 * by default this is a simple html link
	 */
	public function as_link($each_component, $page) {
		return false;
	}


	/**
	 *
	 */
	public function as_content($each_component, $page) {

		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

		// add stylesheet to page
		$page->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');

$content = <<<EOF
<div class="pbc_goal-tabs">
<a class="active-tab tab click-tab" tab="assignment-tab" href="">Review</a>
</div>
<div id="assignment-tab" class="tab-content active-tab-content">
<h2>
Your goal at the start of this coaching cycle was:
<br><br>
<span style="font-weight: bold;">$each_component->action_plan_goal<span>
<br><br><br>
The way you will know that you have achieved this goal is:
<br><br>
<span style="font-weight: bold;">$each_component->goal_achievement_evidence<span>
<br><br><br>
Have you met your goal? If not click Add an Action Plan Step and try again. 
<br>If you have met your goal, click Complete to mark this coaching cycle complete. 
<br>You will be able to access it any time from your My PBC Cycles page.
</h2>
<br><br><br>
<input type="submit" onClick="location.href='$page->pbccycle_url'" value="Add an Action Plan Step" ><input type="submit"  onClick="location.href='index.html'" value="Complete">


EOF;
// global $site;
// $site->dump($page);
		
		// page content
		$page->content->add('main', $content);
		


	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $page) {
		
$content = <<<EOF
</div>
<div id="action_plan_goal-tab" class="tab-content">
</div>
<div id="goal_achievement_evidence-tab" class="tab-content">
</div>
EOF;

	
		$page->content->add('postmain',$content);
	
	}

	
	/**
	 *
	 */
	public function edit_component($each_component, $page) {

		if ($page->can_edit($each_component) || $page->user->sub_role == 1) {
		
			// prevent assignments from being edited
			if (isset($page->lock_assignments) && $page->user->role_id != 1) {
				return false;
			}
		
			// the instructions to pass through the form
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at
			);

			// generate dossier
			$dossier_for_update = $page->generate_dossier($dossier);
		
			// get course component info
			$course = $page->components[(count($page->components) - 1)];
		
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
			// this is the current template, but the recipe will control this.
			$template = isset($page->template) ? $page->template : null;
			
			// for date picker
			$id_key = uniqid();
			
			// required if not admin
			$required = ($page->user->role_id == 1) ? '' : 'tag="required"';

$content = <<<EOF
<div class="clickbar-container admin-container edit-container">
<div class="clickbar-content">
<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">

EOF;


		if ($template) {
			$content .= '<input type="hidden" name="template" value="' . $template . '">';
		}

$content .= <<<EOF
<label class="selectable">
<textarea name="action_plan_goal" class="textarea-input" $required></textarea>
<div class="label-text">
<div class="label-message"><h2>What is your goal for this coaching cycle?</h2></div>
<div class="label-error">Enter Goal</div>
</div>
</label>

<label class="selectable">
<textarea name="goal_achievement_evidence" class="textarea-input" $required></textarea>
<div class="label-text">
<div class="label-message"><h2>How will you know when you have achieved this goal?</h2></div>
<div class="label-error">Enter how you will know when you have achieved this goal.</div>
</div>
</label>

<input type="submit" value="Update">
</form>
EOF;

			if ($page->can_delete($each_component) || $page->user->sub_role == 1) {


				// the instructions to pass through the form
				$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'delete',
				'component_id' => $each_component->component_id,
				'created_at' => $each_component->created_at,
				'parent_url' => $page->requested_url
				);

				// generate dossier
				$dossier_for_delete = $page->generate_dossier($dossier);
			
$content .= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
EOF;

			}

$content .= <<<EOF
</div>
<!-- <div class="clickbar-title clickbar-closed"><span>Edit Goal</span></div> -->
</div>
EOF;

			$page->content->add('admin',$content);

		}

	
	}


	/**
	 *
	 */
	public function add_component($recipe_component, $page) {
		if ($page->goal_created == true) {
			return false;
		}
		// create dossier
		$dossier_for_create = $page->generate_dossier($recipe_component->dossier);
		
		// create dossier for checkurl functionality
		$dossier = array(
		'type' => $recipe_component->type,
		'procedure' => 'checkurl'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_checkurl = $page->generate_dossier($dossier);

		// get course component info
		$course = null;
		if (isset($page->components)) {
			$course = $page->components[(count($page->components) - 1)];
		}
		
		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
		// for date picker
		$id_key = uniqid();

		// required if not admin
		$required = ($page->user->role_id == 1) ? '' : 'tag="required"';

$content = <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle">
<div class="clickbar-content">
<form id="create_items" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
<input type="hidden" name="assignment_category" value="$recipe_component->title">
<input id="create-title" type="hidden" name="title" value="$recipe_component->title">
<input type="hidden" name="list_order" value="4">
EOF;

		if (isset($recipe_component->description)) {
		
			$description = nl2br($recipe_component->description);

$content .= <<<EOF
<label>
<div class="input-padding">$description</div>
<div class="label-text">
<div class="label-message">$recipe_component->title</div>
</div>
</label>
EOF;

		}

$content .= <<<EOF
<div class="data-display">
<div>$recipe_component->title</div>
</div>


<label class="selectable">
<div class="data-display">
The Review step will be created automatically when you define your goal. 
When you have completed the Action Plan Steps and Focused Observations you have configured, 
you can review the cycle to either add more 
Focused Observations or mark the cycle as complete.
</div>
<div class="label-text">
<div class="label-message">Review</div>
</div>
</label>

</div>
<div class="clickbar-title clickbar-closed clickbar-group"><span>$recipe_component->title</span></div>
</div>
EOF;

// 		$page->content->add('create_pbcstep',$content);

	}

	
	/**
	 * fields for ManageRecipes
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$description = isset($recipe['description']) ? $recipe['description'] : null;
				
$elements = <<<EOF
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
<label>
<textarea name="description">$description</textarea>
<div class="label-text">
<div class="label-message">Description</div>
<div class="label-error">Enter a Description</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}