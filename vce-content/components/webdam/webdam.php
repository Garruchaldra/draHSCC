<?php

class WebDam extends MediaType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'WebDam (Media Type)',
			'description' => 'Adds WebDam embedding to Media',
			'category' => 'media',
			'typename' => 'video'
		);
	}
	
	/**
	 * 
	 */
	public function display($each_component, $vce) {

    	// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','webdam-style');
		
		// 12/6/21 removing crossorigin="anonymous" from <video> tag to hopefully prevent video from being blocked by chrome with "Referrer Policy: strict-origin-when-cross-origin"
	
		$contents = <<<EOF
<div class="vidbox" player="player-$each_component->component_id">
<button class="vidbox-click-control"></button>
<div class="vidbox-content">
<button class="vidbox-content-close">X</button>
<div class="vidbox-content-area"></div>
</div>
<video class="player" id="player-$each_component->component_id" width="100%" height="auto" controls controlslist="nodownload" playsinline="" preload="auto" referrerpolicy="origin">
<source src="https://cdn2.webdamdb.com/md_$each_component->code.mp4">
EOF;

        // webdam_mediatype_display hook
        if (isset($vce->site->hooks['webdam_mediatype_display'])) {
            foreach ($vce->site->hooks['webdam_mediatype_display'] as $hook) {
               $contents .= call_user_func($hook, $each_component, $vce);
            }
        }
        
		$contents .= <<<EOF
</video>
</div>
EOF;

// preload="auto"
// preload="metadata"

         		
    	$vce->content->add('main',$contents);
    
    }
    
    
    
    
    /**
	 * 
	 */    
    public static function add($recipe_component, $vce) {
    
       	// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/search.js', 'jquery-ui');
    
    	// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/search.css','webdam-style');
		
		// the instructions to pass through the form
		$dossier = array(
		'type' => 'WebDam',
		'procedure' => 'search',
		);

		// generate dossier
		$dossier_for_search = $vce->generate_dossier($dossier);
		
		// action
		$action = $vce->input_path;

		// dossier for create
		$dossier_for_create = $recipe_component->dossier_for_create;
		
// $content_mediatype = <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content">

$content_mediatype = <<<EOF
<div class="webdam-container">
<div class="webdam-popover"><div class="webdam-popover-content"></div></div>
<div class="webdam-left-content">
<div>

<div class="explore-block">

<div class="explore-list">
EOF;

		// get config for component, and and default.
		// $config = self::get_config($vce);
		
		$taxonomy = !empty($config) ? $config['taxonomy'] : 'taxonomy';

		// bring in taxonomy from external file
		include($taxonomy . '.php');
		
		$base_name = isset($base_name) ? $base_name : $base_filter;	
		
$content_mediatype .= <<<EOF
<div class="explore-block-title">
$base_name
</div>
EOF;
		
		if (isset($search)) {
			
			$search_counter = 1;
			
			foreach ($search as $title=>$topics) {
			
				if ($search_counter == 1) {
					$title_classes = "search-title explore-title container-open";
					$block_classes = "explore-list-block explore-list-open";
				} else {
					$title_classes = "search-title explore-title";
					$block_classes = "explore-list-block";
				}
				
				$search_counter++;

$content_mediatype .= <<<EOF
<div class="$title_classes">
$title
</div>
<div class="$block_classes">
EOF;
		
				foreach ($topics as $each_explore_item=>$each_explore_subitem) {
				
$content_mediatype .= <<<EOF
<div class="explore-title-list">
EOF;
			
					$parent_code = 'e-' . crc32(strtolower(trim($each_explore_item)));
					$sub_explore[$parent_code] = '';
					
					$each_explore_title = isset($search_titles[$each_explore_item]) ? $search_titles[$each_explore_item] : $each_explore_item;
					
					$content_mediatype .= '<button class="explore-keywords" code="' . $parent_code . '" value="' . $each_explore_item . '" dossier="' . $dossier_for_search . '" action="' . $action . '"><div class="explore-keywords-cancel">x</div>'  . $each_explore_title . '</button>';
				
					foreach($each_explore_subitem as $each_subitem) {
				
						$sub_title = isset($search_titles[$each_subitem]) ? $search_titles[$each_subitem] : $each_subitem;
				
						$code = 'k-' . crc32(strtolower(trim($each_subitem)));

						$sub_explore[$parent_code] .=  '<label class="keyword-title display">' . $sub_title . ' <input class="keyword-checkbox" type="checkbox" keyword="' . $code . '" name="' . $code . '" value="' . $sub_title . '"></label>';

					}
					
					$each_value = $sub_explore[$parent_code];
					
$content_mediatype .= <<<EOF
<div class="sub-explore $parent_code">
$each_value
</div>
EOF;
					
// $content_mediatype .= $sub_explore[$parent_code];
					
$content_mediatype .= <<<EOF
</div>
EOF;
				
				}
				
$content_mediatype .= <<<EOF
</div>
EOF;

			}
				
		}


$content_mediatype .= <<<EOF
</div>
</div>
EOF;

$input = array(
	'type' => 'text',
	'name' => 'query',
	'data' => array(
	'class' => 'search',
	'placeholder' => 'To find videos, type a keyword here, or select a topic from the side menu', 
	'dossier' => $dossier_for_search,
	'action' => $action
	)
	);
	
	$styled = $vce->content->form_input($input)['input'] . '<button class="no-style search-icon"></button>';
	
	$search = $vce->content->create_input($styled, 'Search', null, 'no-padding top-padding');
	
	$view = "https://download.earlyeducoach.org/view/";
	$site_url = urlencode($vce->site_url);
	$user_id = $vce->user->user_id;
	$filter_by_title = $title;

	$content_mediatype .= <<<EOF
</div>
</div>
<div class="webdam-right-content">
<div class="right-top-block">
<div class="main-search-form">
$search
</div>
<div class="main-search-count">
RESULTS <div class="total-results">0</div> ITEMS<div class="filter-count"></div> | <button class="no-style clear-results">CLEAR RESULTS</button>
<div class="webdam-progressbar-container"><div class="loading-icon"><div class="loading-icon-text"></div></div><div class="loading-cancel">x</div><div class="webdam-progressbar"></div></div>
</div>
</div>
EOF;

	$content_mediatype .= <<<EOF
<div class="filter-by-block">

<div class="filter-title">
<div class="filter-title-group">FILTER BY</div>
</div>

<div class="sub-explore-list">

<div class="keyword-block">
<button class="search-title keyword-container"><div class="keyword-count"></div>$filter_by_title</button>
<div class="keyword-list type-filter">
</div>
</div>
EOF;
		

		if (isset($additional_menus)) {
		
			foreach ($additional_menus as $each_menu=>$all_menu_items) {
			
			$each_menu_title = strtoupper($each_menu);

$content_mediatype .= <<<EOF
<div class="keyword-block">
<button class="search-title keyword-container"><div class="keyword-count"></div> $each_menu_title</button>
<div class="keyword-list">
EOF;


				foreach ($all_menu_items as $each_menu_item) {
			
					$title = $each_menu_item;
					$code = 'k-' . crc32(strtolower(trim($each_menu_item)));

					$content_mediatype .=  '<label class="keyword-title display">' . $title . ' <input class="keyword-checkbox" type="checkbox" keyword="' . $code . '" name="' . $code . '" value="' . $title . '"></label>';
			
				}

$content_mediatype .= <<<EOF
<button class="close-filter">done</button>
</div>
</div>
EOF;
			
			}
		
		}

$content_mediatype .= <<<EOF
</div>
</div>
<div class="results splash"></div>
</div>
</div>
</div>
<!-- templates start -->
<div class="webdam-item-template" style="display:none;">
<div class="webdam-item-container">
<div class="{classes}" code="{code}" video="{video}" name="{title}" description="{description}" filesize="{filesize}" product="{product}" keyword_list="{keyword_list}" download="{download}" tabindex="0">
<div class="webdam-item-thumbnail">
<div class="thumbnail-src" style="background:url('{thumbnail}');background-size:contain;"></div>
</div>
<div class="webdam-item-name">{title}</div>
</div>
</div>
<div class="webdam-item-description-template" style="display:none;">
<div id="view-{code}" class="webdam-item-details">
<div class="download-prompt" style="display:none;">Download is in progress</div>
<div class="details-left">
<video width="100%" height="auto" controls controlsList="nodownload">{video}</video>
</div>
<div class="details-right">
<button class="close">x</button>
<div class="webdam-item-title">
{title}
</div>
<div class="webdam-item-line"></div>
<div class="webdam-item-description">
{description}
</div>

<div class="webdam-item-embed">
<button class="link-button embed-link" type="WebDam" title="{title}" code="{code}" dossier="$dossier_for_create" action="$vce->input_path">Select This Video</button>
</div>

</div>
</div>
</div>
<!-- templates end -->
EOF;

		return $vce->content->accordion('Cultivate Learning Media Library', $content_mediatype);

// </div>
// <div class="clickbar-title clickbar-closed"><span>Cultivate Learning Media Library</span></div>
// </div>
// EOF;


		// return $content_mediatype;
    
    
    }

	/**
	 * Deals with asynchronous form input 
	 * This is called from input portal forward onto class and function of component
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function form_input($input) {

		// save these two, so we can unset to clean up $input before sending it onward
		//$type = trim($input['type']);
		$procedure = trim($input['procedure']);
		
		// unset component and procedure
		unset($input['procedure']);
		
		// check that protected function exists
		if (method_exists($this, $procedure)) {
			// call to class and function
			$this->$procedure($input);	
			return;
		}
		
		echo json_encode(array('response' => 'error','message' => 'Unknown Procedure'));
		return;
	}
	
	/**
	 * 
	 */
	public static function search($input) {
	
		global $vce;

		//retrieve_attributes for webdam restful api
		$webdam_access_token = $vce->site->retrieve_attributes('webdam_access_token');
		$webdam_token_expires = $vce->site->retrieve_attributes('webdam_token_expires');
		$webdam_refresh_token = $vce->site->retrieve_attributes('webdam_refresh_token');
	
		$_refresh_token = 'b9bbcbc2eafa2fd4461454c8f5c48c621bfedc3e';

		$download = 'http://earlyeducoach.org/webdam/download.php';
		$limit_value = 20;
		$current_limit = isset($input['current_limit']) ? $input['current_limit'] : 0;
		$current_offset = ($current_limit == 0) ? 0 : $current_limit;

		$curlCounter = 0;

		if (!isset($webdam_access_token) || (isset($webdam_token_expires) && $webdam_token_expires < time())) {

			if (!isset($webdam_refresh_token)) {

				// Getting the access_token using grant type "password"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'password',
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'username' => 'eedulib',
				'password' => 'h9$#hKuQ'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);

				// add to page object
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);

				$webdam_refresh_token = $results->refresh_token;
				$vce->site->add_attributes('webdam_refresh_token', $webdam_refresh_token, true);
		

			} else {
	
				// Getting the access_token using grant type "refresh_token"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $webdam_refresh_token,
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'redirect_uri' => 'http://earlyeducoach.org/webdam/'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);
				
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);
			}

		}
		
		
		// create the list of keywords
		$submitted_keyword_array = array();
		$submitted_keyword_list = null;
		foreach ($_POST as $key=>$value) {
			if (strpos($key, 'keyword_') !== false) {
				$submitted_keyword_array[] = $value;
				$submitted_keyword_list .= trim($value) . ' ';
			}
		}

		// echo '<pre style="background:#ffc;">';
		// print_r($submitted_keyword_list);
		// echo '</pre>';

		// variable for search
		$query = null;

		// add query input 
		if (isset($_POST['query']) && $_POST['query'] != "") {
			$query .= $_POST['query'] . ' ';
		}
		// add explore value
		if (isset($_POST['explore']) && $_POST['explore'] != "") {
			$query .= $_POST['explore'] . ' ';
		}
		// add any submitted keywords
		if (isset($submitted_keyword_list)) {
			$query .= $submitted_keyword_list;
		}


		// query has value
		if (isset($query)) {
		
			// get config for component, and and default.
			// $config = self::get_config($vce);

			$taxonomy = !empty($config) ? $config['taxonomy'] : 'taxonomy';

			// bring in taxonomy from external file
			include($taxonomy . '.php');

			// include config file for 
			// include('taxonomy.php');
			$folder_id = isset($_POST['folder_id']) ? $_POST['folder_id'] : $folders_to_search[0];

			// add a base filter if set in the taxonomy config file
			if (isset($base_filter)) {
				$query .= ' ' . $base_filter;
			}

			// variables
			$media = array();
			$media_ids = array();
			$results_counter = 0;

			$headers = array(
			"Authorization: Bearer " . $webdam_access_token
			);

			// start of our search string
			$url = 'https://apiv2.webdamdb.com/search?&query=' . urlencode(trim($query));
$vce->log($url);
$folder_id = '889e5ee383630509a6af14e163690805';
			$folder_url = $url . '&lightboxes=' . $folder_id;

			$chunk_url = $folder_url . '&limit=' . $limit_value . '&offset=' . $current_limit;

			$curlCounter++;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $chunk_url);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$results = json_decode(curl_exec($ch));
			curl_close($ch);

			$total_count = $results->total_count;

			// variable to keep track of how many total records found
			$results_counter += count($results->items);
	
			foreach ($results->items as $each_item) {

				// a switch to create groups of 50
				$switch = ceil(((count($media) + 1) / 50) - 1);
		
				if ($each_item->filetype != 'mp4') {
		
					// deduct one form total_count
					// $total_count--;
			
					// skip to next foreach item
					// continue;
				}

				//file_put_contents(dirname(__FILE__) . '/' . 'log.txt', print_r($each_item->filetype, true) . PHP_EOL, FILE_APPEND);

				// add to array
				$media_ids[$switch][] = $each_item->id;
		
				$media[$each_item->id]['id'] = $each_item->id;
				$media[$each_item->id]['filename'] = $each_item->name;
				$media[$each_item->id]['title'] = str_replace('_',' ',preg_replace('/\.\w{3}$/','',$each_item->name));
				$media[$each_item->id]['description'] = $each_item->description;
				$media[$each_item->id]['filesize'] = $each_item->filesize;

				if (isset($each_item->thumbnailurls))  {

					// $media[$each_item->id]['thumbnail'] = $each_item->thumbnailurls[4]->url;

					if (isset($each_item->videourls)) {

						$link = $each_item->videourls[0]->url;

					} else {

						$link = end($each_item->thumbnailurls)->url;

					}

					// $media[$each_item->id]['link'] = $link;

					// http://php.net/manual/en/function.preg-match.php
					//
					// take the thumbnail url
					// https://cdn2.webdamdb.com/100th_sm_3v298AcU5rEY.jpg?1484766017
					// or
					// https://cdn2.webdamdb.com/a7c16f20876cde55feb662891fbc84f0/custom_thumbnails/100_0hWUr2xDTjA4.png?cache=1512528017
					// looking for this [a-zA-Z0-9]+
					// 3v298AcU5rEY
					// in string
					// using regular expression
			
					preg_match('/^.*_([a-zA-Z0-9]+)\.\w{3}\?.*$/', $each_item->thumbnailurls[0]->url, $matches);

					// set code match and empty as a back up
					$code_match = isset($matches[1]) ? $matches[1] : 'empty';

					$media[$each_item->id]['code'] = $code_match;
					$media[$each_item->id]['thumbnail'] = 'https://cdn2.webdamdb.com/md_' . $code_match . '.jpg';
					$media[$each_item->id]['video'] = 'https://cdn2.webdamdb.com/md_' . $code_match . '.mp4';

					// http://earlyeducoach.org/webdam/download.php?code=md_H3agBaKraiGw&filename=test.mp4
					$media[$each_item->id]['download'] = $download . '?code=' . $code_match . '&filename=' . urlencode($each_item->name);

				}

			}


			if (!empty($results->items)) {
	
				// bring in taxonomy from external file as $search = array();
				// include('taxonomy.php');
	
				// create a list of the keywords used in the taxonomy
				$existing_keywords = array();

				// anonymous function
				$build_existing_list = function($current_explore) use (&$build_existing_list, &$existing_keywords) {

					foreach ($current_explore as $explore_key=>$explore_value) {
						// check if value is an array, and if so use the key and then do a recursive call
						if (is_array($explore_value)) {
							$clean_keyword = strtolower(trim($explore_key));
							$coded_keyword = 'k-' . crc32($clean_keyword);
							$existing_keywords[$coded_keyword] = $clean_keyword;
							// recursive call
							$build_existing_list($explore_value);
						} else {
							$clean_keyword = strtolower(trim($explore_value));
							$coded_keyword = 'k-' . crc32($clean_keyword);
							$existing_keywords[$coded_keyword] = $clean_keyword;
						}
					}

				};

				// call to anonymous function
				$build_existing_list($search);

				// set this before
				$keyword_list = array();

				$headers = array(
				"Authorization: Bearer " . $webdam_access_token
				);

				$url = 'https://apiv2.webdamdb.com/assets/' . implode($media_ids[0],',') . '/metadatas/xmp';

				$curlCounter++;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);

				// echo '<pre>';
				// print_r($results);
				// echo '</pre>';

				// array or arrays or not
				if (!isset($results->type)) {

					foreach ($results as $result_key=>$result_value) {

						//echo '<pre>';
						//print_r($result_value);
						//echo '</pre>';

						$media[$result_key]['keyword_list'] = isset($result_value->keyword) ? $result_value->keyword : null;

						$new_list = array();
						$classes ='webdam-item';

						if (isset($result_value->keyword)) {
							foreach (explode(',',$result_value->keyword) as $each_keyword) {
								// clean up keywoard
								$clean_keyword = strtolower(trim($each_keyword));
								$coded_keyword = 'k-' . crc32($clean_keyword);
								if (!isset($existing_keywords[$coded_keyword])) {
									$new_list[$coded_keyword] = $clean_keyword;
								}
								$classes .= ' ' . $coded_keyword;
							}
						}


						$media[$result_key]['classes'] = $classes;

						$keyword_list += $new_list;

						// $keyword_list = array_merge($keyword_list, $new_list);
				
						// file_put_contents(dirname(__FILE__) . '/' . 'log.txt', print_r($result_value->active_fields, true) . PHP_EOL, FILE_APPEND);

						foreach ($result_value->active_fields as $each_field) {

							if ($each_field->field_name == "Product") {
								$media[$result_key]['product'] = $each_field->value;
							}
					
							if ($each_field->field_name == "Description") {
								$media[$result_key]['description'] = $each_field->value;
							}

						}

					}

				} else {

					if (isset($result_value->keyword)) {

						$media[$result_key]['keyword_list'] = $result_value->keyword;

						$new_list = array();
						$classes ='webdam-item';

						foreach (explode(',',$result_value->keyword) as $each_keyword) {
								$clean_keyword = strtolower(trim($each_keyword));
								$coded_keyword = 'k-' . crc32($clean_keyword);
								if (!isset($existing_keywords[$coded_keyword])) {
									$new_list[$coded_keyword] = $clean_keyword;
								}
								$classes .= ' ' . $coded_keyword;
						}

						$media[$media_ids[0]]['classes'] = $classes;

						$keyword_list += $new_list;

						//$keyword_list = array_merge($keyword_list, $new_list);

						foreach ($result_value->active_fields as $each_field) {

							if ($each_field->field_name == "Product") {
								$media[$result_key]['product'] = $each_field->value;
							}

						}

					}

				}

			}

			//$keyword_list = array_unique($keyword_list);
			if (isset($keyword_list)) {
				asort($keyword_list);
			}

		}

		$object['status'] = 'success';
		$object['total_count'] = $total_count;
		$object['current_limit'] = $current_limit + $limit_value;
		$object['results_folder'] = $folder_id;
		if ($object['current_limit'] > $object['total_count']) {
			$current_folder_key = array_search($folder_id, $folders_to_search); 
			if ($current_folder_key == (count($folders_to_search)-1)) {
				$object['folder_id'] = 0;
			} else {
				$object['folder_id'] = $folders_to_search[($current_folder_key + 1)];
			}
		} else {
			$object['folder_id'] = $folder_id;
		}

		$object['media'] = $media;
		$object['keywords'] = isset($keyword_list) ? $keyword_list : null;
		$object['count'] = count($media);
		$object['folder_count'] = count($folders_to_search);
		$object['folder_position'] = array_search($object['folder_id'], $folders_to_search) + 1;

		echo json_encode($object);
		return;
	
	}

}