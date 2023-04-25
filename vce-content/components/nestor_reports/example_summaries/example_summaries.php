<?php

class Example_summaries  extends Nestor_reportstype {

    /**
	 * This is an example of how to add summary datapoints to the Nestor_reports page.
     * Each method in a class which extends nestorreportstype will show up in the Summary Configuration section.
     * The method must have a name which starts with "summary_"
     * The class name and all "summary_" methods are added to an array of available summaries, which can be added to the summary table by the user.
     * On page load, all the chosen summary classes are instantiated and the methods run. The results are displayed in the table.
     * Normal use of a nestorreportstype class would be to add it as a component in vce_content/components, enable it in Manage Components,
     * and Nestor_Reports will find it automatically
	 */

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Example Summaries',
			'description' => 'adds methods for the summary section of Nestor Reports',
			'category' => 'nestor_reports'
		);
	}

	public function is_valid_timestamp($timestamp) {
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}




	
	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summaryBAK_total_number_of_users($input){

		global $vce;
		extract($input);

        $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
        $data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
            $total_users = $this_data->total;
        }

		return $total_users;
	}



	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */

		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function random_datapoint($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		$query = "SELECT DISTINCT meta_key AS data_category FROM " . TABLE_PREFIX . "components_meta";
		$data = $vce->db->get_data_object($query);

		$data_categories = array();
		foreach ($data as $each_data) {
			$selected = false;
			if ($each_data->data_category == $vce->data_category) {
				$selected = true;
			}
			$data_categories[] = array('name' => $each_data->data_category, 'value' => $each_data->data_category);
		}


		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_value IN ('Pbccycles') AND d.meta_key IN ('created_by')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			$vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

}