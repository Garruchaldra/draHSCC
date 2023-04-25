<?php

class Pbc_fix_resource_library extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc_fix_resource_library',
			'description' => 'utilities to find deleted data and replace it',
			'category' => 'pbc',
			'recipe_fields' => array('auto_create','title','url')
		);
	}

	
	/**
	 * adding assignment specific meta to page object
	 */
	public function check_access($each_component, $vce) {

		return true;
	}




	/**
	 *
	 */
	public function as_content($each_component, $vce) {


		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		

		if (isset($vce->query_string)) {
			$query_string = json_decode($vce->query_string, true);
		}

		$content = NULL;

		$content .= <<<EOF
		Query String possibilities: list_external_links, test_external_links list_uploaded_media, show_user_data (start_id=x, end_id=x).
		<br>
EOF;

		if (isset($query_string['list_external_links']) && $query_string['list_external_links'] == TRUE) {
			$content .= $this->list_external_links($vce);
		}

		if (isset($query_string['list_uploaded_media']) && $query_string['list_uploaded_media'] == TRUE) {
			$upload_type = (isset($query_string['upload_type']))?$query_string['upload_type'] : 'PDF';
			$content .= $this->list_uploaded_media($vce, $upload_type);
		}

		if (isset($query_string['list_vimeo_media']) && $query_string['list_vimeo_media'] == TRUE) {
			$upload_type = (isset($query_string['upload_type']))?$query_string['upload_type'] : 'VimeoVideo';
			$content .= $this->list_vimeo_media($vce, $upload_type);
		}
		

		if (isset($query_string['show_user_data']) && $query_string['show_user_data'] == TRUE) {
			$content .= $this->show_user_data($vce, $query_string['start_id'], $query_string['end_id']);
		}

		$vce->content->add('main',$content);


	}

	public function show_user_data($vce, $start_id = 0, $end_id = 300) {

		$users_info = array();
		for ($i = $start_id; $i < $end_id; $i++) {
			$users_info['user_ids'][] = $i;
		}
		$users_info['user_ids'] = implode('|', $users_info['user_ids']);
		$all_users = $vce->user->find_users($users_info);
		foreach ($all_users as $this_user) {
			$vce->dump($this_user);
		}

	}


	public function list_uploaded_media($vce, $upload_type = 'PDF') {

		$content = '<div>Here is a list of all the Uploaded Media on this site.</div><br>';
		$content .= <<<EOF
		<table> 
		<tr>
			<th>Number</th>
			<th>Does it exist?</th>
			<th>Title</th>
			<th>URL</th>
			<th>Status</th>
			<th>Created At</th>
			<th>Taxonomy</th>
			<th>Path</th>
		</tr>
EOF;




$query = "SELECT a.component_id as component_id, c.meta_value as title, b.meta_value as path, d.meta_value AS user_id, e.meta_value AS created_at FROM vce_components_meta AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id JOIN vce_components_meta AS c ON a.component_id = c.component_id JOIN vce_components_meta AS d ON a.component_id = d.component_id JOIN vce_components_meta AS e ON a.component_id = e.component_id WHERE a.meta_key = 'media_type' AND a.meta_value = '$upload_type' AND b.meta_key = 'path' AND c.meta_key = 'title' AND d.meta_key = 'created_by' AND e.meta_key = 'created_at'";
$query = "select b.* from vce_components as a right join vce_components_meta as c on a.component_id = c.component_id and c.meta_key='media_type' and c.meta_value  not in ('ExternalLink', 'VimeoVideo') join vce_components_meta as b on a.component_id =b.component_id and a.parent_id in (select component_id from vce_components_meta where meta_key = 'type' and meta_value like '%resource%') ";
		// $vce->dump($query);
		// exit;
		
		$result = $vce->db->get_data_object($query);

		$data = array();
		foreach($result as $this_result) {
// $vce->dump($this_result);
			$data[$this_result->component_id][$this_result->meta_key] = $this_result->meta_value;


		}
// $vce->dump($data);
// 		exit;

		$i = 0;
        foreach($data as $this_data) {
			// if($i > 5) {
			// 	break;
			// }
			$i++;
			$path = $this_data['path'];
			$component_id = $this_data['component_id'];
			$title = $this_data['title'];
			//actual name of original file
			$name = $this_data['name'];
			$user_id = $this_data['created_by'];
			$taxonomy = NULL;
			if (isset($this_data['taxonomy']) && $this_data['taxonomy'] != '') {
				$taxonomy = trim($this_data['taxonomy'], '|');
				$taxonomy = explode( '|' , $taxonomy);
				// $vce->dump($this_data['taxonomy']);
				// $vce->dump($taxonomy);
				$taxonomy = implode(',' , $taxonomy);

			}
			$created_at = date(DATE_RFC2822, $this_data['created_at']);
			$relative_path = __DIR__.'/../../uploads/'.$user_id.'/'.$path;
			$relative_path = 'https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/'.$user_id.'/'.$path;
			$file_exists = '--';
			$positive = 'no';
			// if (file_exists($relative_path)) {
			// 	$file_exists = 'Exists!';
			// 	$positive = 'Yes';
			// }

			// $url = $vce->site->site_url.'/vce-content/uploads/'.$user_id.'/'.$path;
			$url = 'https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/'.$user_id.'/'.$path;
			// $url = 'https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/'.$user_id.'/13_1516637974.pdf';
			// $vce->dump($url);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, true);
$curl_result = curl_exec($ch);
$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// $vce->dump($responseCode);
// Check the response code
if($responseCode == 200){
    $file_exists = 'exists';
	$positive = 'Yes';
}


			$content .= <<<EOF
			<tr>
EOF;
			$content .= <<<EOF
			<td>$i</td>
			<td>$positive</td>
			<td>$title</td>
			<td>$url</td>
			<td>$file_exists</td>
			<td>$created_at</td>
			<td>$taxonomy</td>
			<td>$path</td>
EOF;
$content .= <<<EOF
<tr>
EOF;
		}



		$content .= <<<EOF
		</table>
EOF;

		return  $content;

	}







	public function list_vimeo_media($vce, $upload_type = 'VimeoVideo') {

		// $list_of_archives = scandir("/Applications⁩/⁨MAMPBAK2⁩/htdocs⁩/⁨mediaAmp⁩/downloads⁩/");
		// $list_of_archives = scandir("/Applications⁩/⁨");
		// $list_of_archives = scandir("/Applications/MAMP/htdocs/ohscc2/vce-content/components/pbc_fix_resource_library/");
		$list_of_archives = scandir((__dir__).'/../../../../MediaAmpScraper/downloads/downloads/');
		// $vce->dump($list_of_archives);

		// $test = in_array('Assignment 6 video Holly.mp4', $list_of_archives);

		// $vce->dump($test);
		// exit;

		$content = '<div>Here is a list of all the VimeoVideos on this site.</div><br>';
		$content .= <<<EOF
		<table> 
		<tr>
			<th>Number</th>
			<th>Does it exist?</th>
			<th>Title</th>
			<th>Name</th>
			<th>URL</th>
			<th>Status</th>
			<th>Created At</th>
			<th>Taxonomy</th>
			<th>Guid</th>

		</tr>
EOF;




// $query = "SELECT a.component_id as component_id, c.meta_value as title, b.meta_value as path, d.meta_value AS user_id, e.meta_value AS created_at FROM vce_components_meta AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id JOIN vce_components_meta AS c ON a.component_id = c.component_id JOIN vce_components_meta AS d ON a.component_id = d.component_id JOIN vce_components_meta AS e ON a.component_id = e.component_id WHERE a.meta_key = 'media_type' AND a.meta_value = '$upload_type' AND b.meta_key = 'path' AND c.meta_key = 'title' AND d.meta_key = 'created_by' AND e.meta_key = 'created_at'";
// $query = "select b.* from vce_components as a right join vce_components_meta as c on a.component_id = c.component_id and c.meta_key='media_type' and c.meta_value  in ('VimeoVideo') join vce_components_meta as b on a.component_id =b.component_id and a.parent_id in (select component_id from vce_components_meta where meta_key = 'type' and meta_value like '%resource%') ";
		
$query = "select b.* from vce_components as a right join vce_components_meta as c on a.component_id = c.component_id and c.meta_key='media_type' and c.meta_value  in ('VimeoVideo') join vce_components_meta as b on a.component_id =b.component_id and a.parent_id in (select component_id from vce_components_meta where meta_key = 'type' and meta_value like '%resource%') join vce_components_meta as d on d.component_id = a.component_id and d.meta_key='taxonomy' and d.meta_value != '|1746|';";
// $vce->dump($query);
		// exit;
		
		$result = $vce->db->get_data_object($query);

		$data = array();
		foreach($result as $this_result) {
// $vce->dump($this_result);
			$data[$this_result->component_id][$this_result->meta_key] = $this_result->meta_value;


		}
// $vce->dump($data);
// 		exit;

		$i = 0;
        foreach($data as $this_data) {
			// if($i > 5) {
			// 	break;
			// }
			$i++;
			$path = $this_data['path'];
			$component_id = $this_data['component_id'];
			$title = $this_data['title'];
			//actual name of original file
			$name = $this_data['name'];
			$user_id = $this_data['created_by'];
			$taxonomy = NULL;
			if (isset($this_data['taxonomy']) && $this_data['taxonomy'] != '') {
				$taxonomy = trim($this_data['taxonomy'], '|');
				$taxonomy = explode( '|' , $taxonomy);
				// $vce->dump($this_data['taxonomy']);
				// $vce->dump($taxonomy);
				$taxonomy = implode(',' , $taxonomy);

			}
			$created_at = date(DATE_RFC2822, $this_data['created_at']);
			$guid = $this_data['guid'];
			// $relative_path = __DIR__.'/../../uploads/'.$user_id.'/'.$path;
			// $relative_path = 'https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/'.$user_id.'/'.$path;
			$file_exists = '--';
			$positive = 'no';


			$test = in_array($name, $list_of_archives);
			if ($test == 1) {
				$file_exists = 'Exists!';
				$positive = 'Yes';
			}

			// $url = $vce->site->site_url.'/vce-content/uploads/'.$user_id.'/'.$path;
			$url = 'https://vimeo.com/'.$guid;
			// $url = 'https://eclkc.ohs.acf.hhs.gov/cc/vce-content/uploads/'.$user_id.'/13_1516637974.pdf';
			// $vce->dump($url);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, true);
$curl_result = curl_exec($ch);
$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
// $vce->dump($url);
// $vce->dump($responseCode);
// Check the response code
if($responseCode == 200){
    $file_exists = 'exists';
	$positive = 'Yes';
}


			$content .= <<<EOF
			<tr>
EOF;
			$content .= <<<EOF
			<td>$i</td>
			<td>$positive</td>
			<td>$title</td>
			<td>$name</td>
			<td>$url</td>
			<td>$file_exists</td>
			<td>$created_at</td>
			<td>$taxonomy</td>
			<td>$guid</td>
EOF;
$content .= <<<EOF
<tr>
EOF;
		}



		$content .= <<<EOF
		</table>
EOF;

		return  $content;

	}


	public function list_external_links($vce) {

		if (isset($vce->query_string)) {
			$query_string = json_decode($vce->query_string, true);
		}

		$content = '<div>Here is a list of all the External Links on this site.</div><br>';
		$content .= <<<EOF
		<ol type = "1">
EOF;

		$query = "SELECT a.component_id as component_id, c.meta_value as title, b.meta_value as url FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id WHERE a.meta_key = 'media_type' AND a.meta_value = 'ExternalLink' AND b.meta_key = 'link' AND c.meta_key = 'title'";
		// $vce->dump($query);
		// exit;
		
		$data = $vce->db->get_data_object($query);

		$i = 0;
        foreach($data as $this_data) {
			// if($i > 5) {
			// 	break;
			// }
			// $i++;
			$url = $this_data->url;
			$component_id = $this_data->component_id;
			$title = $this_data->title;
			$header_response = NULL;
			if (isset($query_string['test_external_links']) && $query_string['test_external_links'] == TRUE) {
				$header_response = $this->url_exists($url);
			}
			
			$content .= <<<EOF
			<li><a href="$url"  target="_blank">$title</a> $header_response </li>
EOF;
		}
		$content .= <<<EOF
		</ol>
EOF;

		return  $content;

	}

	public function url_exists($url) {
		global $vce;

		$a_url = parse_url($url);
// $vce->dump($a_url);
// $vce->dump(gethostbyname($a_url['host']));
		if (!isset($a_url['port'])) $a_url['port'] = 80;
		$errno = 0;
		$errstr = '';
		$timeout = 300;
		if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host'])){
			$fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
			if (!$fid) return false;
			$page = isset($a_url['path'])  ?$a_url['path']:'';
			$page .= isset($a_url['query'])?'?'.$a_url['query']:'';
			fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
			$head = fread($fid, 4096);
			$head = substr($head,0,strpos($head, 'Connection: close'));
			fclose($fid);
			if (preg_match('#^HTTP/.*\s+[200|302]+\s#i', $head)) {
			 $pos = strpos($head, 'Content-Type');
			 return "200 or 302: exists";
			} elseif (preg_match('#^HTTP/.*\s+[301]+\s#i', $head)) {
				return "301: exists";
			}
		} else {
			return $head." doesn't exist";
		}
	}




}