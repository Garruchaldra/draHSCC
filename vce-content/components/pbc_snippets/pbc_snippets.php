<?php
class Pbc_snippets  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Snippets',
			'description' => 'A collection of include-able snippets for use in other components',
			'category' => 'pbc'
		);
	}
	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
	public function preload_component() {
		$content_hook = array (
			'arrows' => 'Pbc_snippets::arrows',
			'titleBar' => 'Pbc_snippets::titleBar',
			'sidebar' => 'Pbc_snippets::sidebar'
		);
		return $content_hook;
	}

			/**
	 * progress arrows for various pbc pages
	 */
	public static function arrows($pageLinks) {
		global $vce;

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

		if (isset($pageLinks['homeLink'])) {
			$homeLink = $pageLinks['homeLink'];
			$goalLink = $pageLinks['goalLink'];
			$stepsLink = $pageLinks['stepsLink'];
			$foLink = $pageLinks['foLink'];
		}
		$arrowContainer = isset($arrowContainer) ? $arrowContainer : NULL;
		$content = <<<EOF
<div id="cycle-arrows" class="progress-arrows__container progress-arrows__show">
<a $homeLink>PBC Cycle</a>
<a $goalLink>Action Plan Goal</a>
<a $stepsLink>Action Plan Steps</a>
<a $foLink>Focused Observations</a>
</div>
EOF;
		return $content;
	}

		/**
	 * titlebar for various pbc pages
	 */
	public static function titleBar($title, $id) {
		global $vce;

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

		$content = <<<EOF
<div class="title-bar">
	<div class="title-bar__icon $id-icon"></div>
	<h1 class="title-bar__header">$title</h1>
</div>
EOF;
		return $content;
	}

	 /**
	 * instructions for various pbc pages
	 */
	public static function sidebar($sidebarContent) {
		global $vce;

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

		if ($sidebarContent['instructionsText'] !== null || $sidebarContent['infoboxTitle'] !== null) {
		$content = <<<EOF
<div class="instructions__container" id="sidebar__container">
EOF;

			if (isset($sidebarContent['instructionsText'])) {
				$instructionsTitle = $sidebarContent['instructionsTitle'];
				$instructionsText = $sidebarContent['instructionsText'];

				$content .= <<<EOF
	<div class="instructions__label-container" aria-label="show/hide instructions" tabindex="0">
		<p class="instructions__label instructions__label-hide hide">Hide Instructions &#x25B2;</p>
		<p class="instructions__label instructions__label-show hide">Show Instructions &#x25BC;</p>
	</div>
	<div class="instructions__content shadow sidebar__content-box hide">
		<p class="instructions__title">$instructionsTitle</p>
		<p class="instructions__text">$instructionsText</p>
	</div>
EOF;
			}

			if(isset($sidebarContent['infoboxTitle'])) {
				$infoboxTitle = $sidebarContent['infoboxTitle'];
				$infoboxIcon = $sidebarContent['infoboxIcon'];
				$infoboxText = $sidebarContent['infoboxText'];

				$content .= <<<EOF
	<div class="infobox__content shadow sidebar__content-box hide">
		<div class="infobox__header-container">
			<div class="infobox__header-icon"></div>
			<p class="infobox__header">Where am I in the PBC Cycle?</p>
		</div>
			<p class="infobox__title">$infoboxTitle</p>
		<div class="infobox__icon $infoboxIcon-icon"></div>
		<p class="infobox__text">$infoboxText</p>
		<a class="infobox__pbc-link" href="https://eclkc.ohs.acf.hhs.gov/professional-development/article/practice-based-coaching-pbc" target="_blank">Learn more about the PBC Cycle</a>
	</div>
EOF;
			}

			$content .= <<<EOF
</div> 
EOF;
	}
		return $content;
	}

	 /**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
			return false;
	}

}
