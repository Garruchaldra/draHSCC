<?php

class Powerpoint extends MediaType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Powerpoint (Media Type)',
			'description' => 'Adds Microsoft Powerpoint to Media',
			'category' => 'media'
		);
	}
	

	/**
	 * 
	 */
	public function display($each_component, $vce) {
	
		$prevent_inline_media = false;
	
		// prevent inline media items if parent requests it
		if (isset($each_component->parent->prevent_inline_media) && $each_component->parent->prevent_inline_media == 'on') {
			$prevent_inline_media = true;
		}
		
		if (!$prevent_inline_media) {
		
			// expires = how many seconds from now?
			// path = $each_component->created_by . '/' . $each_component->path
			// name = the name given to the media item
			// user_id = $user->user_id check the user id of the current user. 
			// disposition=attachment/inline
			// here's a list of content disposition values
			// http://www.iana.org/assignments/cont-disp/cont-disp.xhtml
			$fileinfo = array(
			'name' => $each_component->title,
			'expires' => 300,
			'path' => $each_component->created_by . '/' . $each_component->path,
			'component' => $each_component
			);
		
			$media_viewer_link = $vce->site->media_viewer_link($fileinfo);
	
			if (!empty($media_viewer_link)) {

				// create contents
				$contents = '<mamediapowerpoint><div style="height:calc(100vw * .602);" id="container-id"><iframe id="media-iframe" src="' . $media_viewer_link . '" style="width:100%;height:100%;" frameborder="0"></iframe></div></mamediapowerpoint>';

				$vce->content->add('main', $contents);
				
				// display title
				if (isset($each_component->recipe['display_title']) && $each_component->recipe['display_title'] == 'on') {
					$vce->content->add('main','<div class="media-title">' . $each_component->title . '</div>');
				}
	
			}
			
		 } else {
    	
    		$contents = '<div class="media-title">' . $each_component->title . '</div>';
    	
    		$vce->content->add('main', $contents);
    	
    	}
    	
    	$fileinfo_for_download = array(
    	'name' => $each_component->title,
    	'expires' => 300,
    	'path' => $each_component->created_by . '/' . $each_component->path,
    	'disposition' => 'attachment',
		'component' => $each_component
    	);
    	
    	$contents_for_download = '<p><div class="download-button"><a class="link-button download-button-ppt" href="' . $vce->site->media_link($fileinfo_for_download) . '"><div class="download-text">' . $each_component->title . '</div>Download this PowerPoint file to your computer</a></div></p>';
    	
     	$vce->content->add('main', $contents_for_download);

    }
    
    
    /**
     * file uploader needed
     */
   	public static function file_upload() {
	 	return true;
	}


	/**
	 * a way to pass file extensions to the plupload to limit file selection
	 */
	 public static function file_extensions() {
	 	//{title:'Image files',extensions:'gif,png,jpg,jpeg'};
	 	return array('title' => 'Powerpoint Files','extensions' => 'ppt,pot,pps,ppa,pptx,potx,ppsx');
	 }
	 
	 
	 /**
	  * a way to pass the mimetype and mimename to vce-upload.php
	  * the minename is the class name of the mediaplayer.
	  * mimetype can have a wildcard for subtype, included after slash by adding .*
	  * https://www.sitepoint.com/mime-types-complete-list/
	  */
		public static function mime_info() {
	 	return array(
	 	'application/vnd.ms-powerpoint' => get_class(),
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => get_class(),
		'application/vnd.openxmlformats-officedocument.presentationml.template' => get_class(),
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => get_class()
	 	);
	 }
	 
	/**
	 * add config info for this component
	 */
	public function component_configuration() {
	
		global $vce;
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'display_inline',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['display_inline']) && $this->configuration['display_inline'] == 'on') ? true :  false),
		'label' => 'Display PowerPoint files inline'
		)
		);
		
		return $vce->content->create_input($input,'Display PowerPoint files inline');
	
	}

}