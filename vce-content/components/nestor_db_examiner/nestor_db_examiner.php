<?php
class Nestor_db_examiner  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor DB Examiner',
			'description' => 'A place to put methods which test DB use and/or quickly display values',
			'category' => 'utilities'
		);
	}
	


    /**
     *
     */
    public function as_content($each_component, $vce) {
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter jquery-ui');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');

		$content = '<div>Nestor DB Examiner</div>';
		

            // create the dossiers
            $dossier_for_add_to_query = $vce->generate_dossier(array('type' => get_class(), 'procedure' => 'add_to_query'));
            $dossier_for_run_query = $vce->generate_dossier(array('type' => get_class(), 'procedure' => 'run_query'));

			$default_table = 'vce_components_meta';
			$default_key = 'created_at';
			$default_operator = '=';
			$default_value = null;
			$query_constructed = "
			SELECT a.component_id, a.meta_value AS type, b.meta_value AS created_at, c.meta_value AS created_by, d.meta_value AS pbc_roles 
			FROM vce_components_meta AS a 
			JOIN vce_components_meta AS b ON a.component_id=b.component_id 
			JOIN vce_components_meta AS c ON a.component_id=c.component_id 
			JOIN vce_components_meta AS d ON a.component_id=d.component_id 
			WHERE a.meta_value='CoachingPartnership’ 
			AND b.meta_key='created_at’ 
			AND c.meta_key='created_by’ 
			AND d.meta_key='pbc_roles’ 
			AND CAST(b.meta_value AS UNSIGNED) > 1622530800 
			AND CAST(b.meta_value AS UNSIGNED) < 1624431600 
			GROUP BY a.component_id";

            $content .= <<<EOF
			<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$dossier_for_run_query">
EOF;

$content .= <<<EOF
<div>

<br>
<div>Query Constructed:<input type="text" value="$query_constructed" name="query_constructed"></div>

<input type="submit" value="Run Query">
<div class="link-button cancel-button">Start Over</div>
</form>
</div>
<br>
<br>
EOF;


            $content .= <<<EOF
<div>
Add a table join:
<br><br>
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_add_to_query">
<div>Table:<input type="text" value="$default_table" name="table"></div>
<br>
<div>Key:<input type="text" value="$default_key" name="key"></div>
<br>
<div>Operator:<input type="text" value="$default_operator" name="operator"></div>
<br>
<div>Value:<input type="text" value="$default_value" name="value"></div>
<br>
<div>Alternatively, write in the join:<input type="text" value="" name="join"></div>
</div>
EOF;





		$content .= <<<EOF
		<input type="submit" value="Add Join">
		</form>
EOF;

		$query = "SELECT DISTINCT a.meta_key, ANY_VALUE(a.meta_value) as meta_value FROM " . TABLE_PREFIX . "components_meta AS a
		GROUP BY a.meta_key
		";
		// $query = "SELECT DISTINCT a.meta_key FROM " . TABLE_PREFIX . "components_meta AS a ";
		$results = $vce->db->get_data_object($query, 1);


		$example_content = <<<EOF
	<div>
	$query
	<table class="tablesorter">
EOF;
		foreach($results as $r) {
			//create inputs from each field
			// $vce->dump($r);
			$example_content .= <<<EOF
			<tr>
				<th>$r->meta_key</th>
				<th>$r->meta_value</th>
		  	</tr>

EOF;
		}

$example_content .= <<<EOF
	</table>
	</div>
EOF;

$example_accordion = $vce->content->accordion('List of distinct keys and values', $example_content, TRUE);


$content .= $example_accordion;

		
		$vce->content->add('main', $content);
	
	}



    /**
     * add joins to query
	 */ 
    public function add_to_query($input) {
		/*
SELECT a.component_id, a.meta_value AS type, b.meta_value AS created_at, c.meta_value AS created_by, d.meta_value AS pbc_roles 
FROM vce_components_meta AS a 
JOIN vce_components_meta AS b ON a.component_id=b.component_id 
JOIN vce_components_meta AS c ON a.component_id=c.component_id 
JOIN vce_components_meta AS d ON a.component_id=d.component_id 
WHERE a.meta_value='CoachingPartnership’ 
AND b.meta_key='created_at’ 
AND c.meta_key='created_by’ 
AND d.meta_key='pbc_roles’ 
AND CAST(b.meta_value AS UNSIGNED) > 1622530800 
AND CAST(b.meta_value AS UNSIGNED) < 1624431600 
GROUP BY a.component_id
*/

		$vce = $this->vce;
		$vce->log($input);
		extract($input);
		$query = (isset($vce->constructed_query) && $vce->constructed_query!='')?$vce->constructed_query : 'in_construction';
		if ($vce->constructed_query == 'in_construction') {
			$vce->constructed_query = "SELECT a$join_index.id, a$join_index.meta_key, a$join_index.meta_value FROM $table AS a$join_index ";
			$where_clause = "WHERE a$join_index.meta_key='$key'";
		} else {
			$last_join_index = $join_index -1;
			$vce->constructed_query .= "JOIN $table AS a$join_index ON a$last_join_index.id = a$join_index.id";
		}

		echo json_encode(array('response' => 'success', 'message' => 'Site_Meta table Updated', 'form' => 'create', 'action' => ''));
		return;

    }

	/**
     * run the query which was constructed
	 */ 
    public function run_query($input) {

		$vce = $this->vce;

		echo json_encode(array('response' => 'success', 'message' => 'Site_Meta table Updated', 'form' => 'create', 'action' => ''));
		return;

    }
	
	 /**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		$title = isset($recipe['title']) ? $recipe['title'] : $this->component_info()['name'];
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
