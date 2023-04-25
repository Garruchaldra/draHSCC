<?php

class Cas_logout extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Cas Logout',
			'description' => 'Component for creating a CAS logout link',
			'category' => 'site'
		);
	}

	/**
	 * calls to logout function and then forwards to site url
	 */
	public function as_content($each_component, $page) {

		global $site;
		global $user;

		// call to logout function
		$user->logout();
		
		require_once BASEPATH .'/vce-content/components/cas_login/CAS/cas_config.php';
		// Load the CAS lib
		require_once $phpcas_path . '/CAS.php';

		// Enable debugging
		// phpCAS::setDebug('debug.txt');
		// Enable verbose error messages. Disable in production!
		phpCAS::setVerbose(false);
		// Initialize phpCAS
		phpCAS::client(CAS_VERSION_3_0, $cas_host, $cas_port, $cas_context, false);
		phpCAS::setNoCasServerValidation();
		//phpCAS::logout();
		phpCAS::logoutWithRedirectService('https://eclkc.ohs.acf.hhs.gov/cc');
		return;	
		// to front of site
		//header('location: https://eclkc.ohs.acf.hhs.gov/cas/logout');
		/*header('location: https://eclkc.ohs.acf.hhs.gov/hslc;internal&action=hslclogout.action');*/
		
	}

	/**
	 * fileds to display when this is created
	 */
	function recipe_fields($recipe) {
	
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