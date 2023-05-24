<?php
class Pbc_git_log  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Git Log',
			'description' => 'A component to show information from the git log at the site baseurl',
			'category' => 'pbc'
		);
	}
	
	// needed for git log
	const MAJOR = 1;
    const MINOR = 2;
    const PATCH = 3;


    /**
     *
     */
    public function as_content($each_component, $vce) {

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');

		$content = null;





$git_info = 'HSCC web app ' . $this::get_git_info();
$content .= <<<EOF
<div>
<h2>Git Log Info from last (current) commit</h2>
<div class="input-label-style"> 
$git_info 
<br>
</div>
</div>
EOF;




		$vce->content->add('main', $content);
	
	}



    public static function get_git_info()
    {
        $f = fopen(BASEPATH . '/.git/logs/HEAD', 'r');
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") {
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}

		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") {
			/**
			 * Prepend the new char
			 */
			$line = '';
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}

		fclose($f);

		$line = explode(' ', $line);
		$time_stamp = $line[5];
		// $last_line = NULL;
		// foreach ($line as $k=>$v) {
		// 	$last_line .= '<br>' . $v;
		// }

        $commitDate = new \DateTime($time_stamp);
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        // return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:i:s'));
		return sprintf($commitDate->format('Y-m-d H:i:s'));

	}


	/** This only works on systems where PHP exec()  */

    public static function get_git_infoBAK()
    {
        // $commitHash = trim(exec('git -C vce-application rev-parse --short HEAD'));
        $commitHash = trim(exec('git -C ' . BASEPATH . ' log --pretty="%h" -n1 HEAD'));

        $commitDate = new \DateTime(trim(exec('git -C ' . BASEPATH . ' log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        // return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:i:s'));
		return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:i:s'));

	}

	 /**
	 * hide this component from being added to a recipe
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
