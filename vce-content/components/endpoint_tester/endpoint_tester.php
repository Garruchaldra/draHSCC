<?php

/**
 * Test script to see output from HSCC when responding to the HSCC Mobile App
 *
 * @category   Endpoint_tester
 * @author     Dayton Allemann <daytonra@uw.edu>
 * @copyright  2022 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

/**
 * Endpoint_tester for RESTFul functions
 */
class Endpoint_tester extends Component {


	/**
	 * basic info about the component
	 */
	public function component_info()
	{
		return array(
			// basic component properties are required
			'name' => 'Endpoint_tester',
			'description' => 'Endpoint tester for HSCC Mobile App',
			'category' => 'admin',
			'recipe_fields' => array(
				'auto_create',
				'title',
                array('url' => 'required'),
            )
		);
	}
	


	/**
*/
//  Array
// (
//     [Host] => localhost:8888
//     [Accept] => */*
//     [Cookie] => _dbs=nKPNt5QXEVbMgbpvt/3aBw==
//     [User-Agent] => hsoccMobile/1 CFNetwork/1312 Darwin/20.6.0
//     [Accept-Language] => en-US,en;q=0.9
//     [Accept-Encoding] => gzip, deflate
//     [Connection] => keep-alive
// )

	public function as_content($each_component, $vce) {


		// $vce->dump($each_component);
		// $vce->plog($vce->user);

		
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','endpoint-tester-style');

		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui');

		// convert query_sting
		if (isset($vce->query_string)) {
			$vce->query_string = json_decode($vce->query_string);
		}


		$content = NULL;

			// if no media was selected, show list of media
			$user_media = array();
		



			$vce->content->add('title', 'Endpoint Tester');


	$dossier = array(
		'type' => 'Endpoint_tester',
		'procedure' => 'return_html',
		'component_id' => $ach_component->component_id,
		'component_title' => $each_component->title
		);
// $vce->dump($dossier);
		// add dossier for requesting a resource
		$dossier_for_return_html = $vce->generate_dossier($dossier);

		$endpoint_url = $vce->site->site_url . '/endpoint';

		$content .= <<<EOF
<form id="endpoint-tester-form" class="asynchronous-form" method="post" action="$endpoint_url" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_add_resource_requester_id">

EOF;

   // page input
   $input = array(
    'type' => 'text',
    'name' => 'requested_page',
    'data' => array(
        'autocapitalize' => 'none',
        'tag' => 'required',
    )
);

$requested_page_input = $vce->content->create_input($input,'Requested Page','Enter a Requested Page');


$content .= <<<EOF
<pre>
<div id="answer-69684198" class="answer js-answer" data-answerid="69684198" data-parentid="69312343" data-score="61" data-position-on-page="1" data-highest-scored="1" data-question-has-accepted-highest-score="0" itemprop="suggestedAnswer" itemscope="" itemtype="https://schema.org/Answer">
    <div class="post-layout">
        <div class="votecell post-layout--left">
            <div class="js-voting-container d-flex jc-center fd-column ai-stretch gs4 fc-black-200" data-post-id="69684198">
        <button class="js-vote-up-btn flex--item s-btn s-btn__unset c-pointer " data-controller="s-tooltip" data-s-tooltip-placement="right" aria-pressed="false" aria-label="Up vote" data-selected-classes="fc-theme-primary" data-unselected-classes="" aria-describedby="--stacks-s-tooltip-hj67l6j6">
            <svg aria-hidden="true" class="svg-icon iconArrowUpLg" width="36" height="36" viewBox="0 0 36 36"><path d="M2 25h32L18 9 2 25Z"></path></svg>
        </button><div id="--stacks-s-tooltip-hj67l6j6" class="s-popover s-popover__tooltip pe-none" aria-hidden="true" role="tooltip">This answer is useful<div class="s-popover--arrow"></div></div>
        <div class="js-vote-count flex--item d-flex fd-column ai-center fc-black-500 fs-title" itemprop="upvoteCount" data-value="61">
            61
        </div>
        <button class="js-vote-down-btn flex--item s-btn s-btn__unset c-pointer " data-controller="s-tooltip" data-s-tooltip-placement="right" aria-pressed="false" aria-label="Down vote" data-selected-classes="fc-theme-primary" data-unselected-classes="" aria-describedby="--stacks-s-tooltip-p99ilbfw">
            <svg aria-hidden="true" class="svg-icon iconArrowDownLg" width="36" height="36" viewBox="0 0 36 36"><path d="M2 11h32L18 27 2 11Z"></path></svg>
        </button><div id="--stacks-s-tooltip-p99ilbfw" class="s-popover s-popover__tooltip pe-none" aria-hidden="true" role="tooltip">This answer is not useful<div class="s-popover--arrow"></div></div>

    
            <div class="js-accepted-answer-indicator flex--item fc-green-500 py6 mtn8 d-none" data-s-tooltip-placement="right" title="Loading when this answer was accepted…" tabindex="0" role="note" aria-label="Accepted">
                <div class="ta-center">
                    <svg aria-hidden="true" class="svg-icon iconCheckmarkLg" width="36" height="36" viewBox="0 0 36 36"><path d="m6 14 8 8L30 6v8L14 30l-8-8v-8Z"></path></svg>
                </div>
            </div>

    
        <a class="js-post-issue flex--item s-btn s-btn__unset c-pointer py6 mx-auto" href="/posts/69684198/timeline" data-shortcut="T" data-ks-title="timeline" data-controller="s-tooltip" data-s-tooltip-placement="right" aria-label="Timeline" aria-describedby="--stacks-s-tooltip-93qzsk8j"><svg aria-hidden="true" class="mln2 mr0 svg-icon iconHistory" width="19" height="18" viewBox="0 0 19 18"><path d="M3 9a8 8 0 1 1 3.73 6.77L8.2 14.3A6 6 0 1 0 5 9l3.01-.01-4 4-4-4h3L3 9Zm7-4h1.01L11 9.36l3.22 2.1-.6.93L10 10V5Z"></path></svg></a><div id="--stacks-s-tooltip-93qzsk8j" class="s-popover s-popover__tooltip pe-none" aria-hidden="true" role="tooltip">Show activity on this post.<div class="s-popover--arrow"></div></div>

</div>

        </div>

        

<div class="answercell post-layout--right">
    
    <div class="s-prose js-post-body" itemprop="text">
<p>I got this error when the simulator is already open while I ran <code>react-native run-ios</code>. Closed the simulator all the way. Reran the command and it worked.</p>
    </div>
    <div class="mt24">
        <div class="d-flex fw-wrap ai-start jc-end gs8 gsy">
            <time itemprop="dateCreated" datetime="2021-10-23T00:18:21"></time>
            <div class="flex--item mr16" style="flex: 1 1 100px;">
                


<div class="js-post-menu pt2" data-post-id="69684198">
    <div class="d-flex gs8 s-anchors s-anchors__muted fw-wrap">

            <div class="flex--item">
                <a href="/a/69684198" rel="nofollow" itemprop="url" class="js-share-link js-gps-track" title="Short permalink to this answer" data-gps-track="post.click({ item: 2, priv: 0, post_type: 2 })" data-controller="se-share-sheet s-popover" data-se-share-sheet-title="Share a link to this answer" data-se-share-sheet-subtitle="" data-se-share-sheet-post-type="answer" data-se-share-sheet-social="facebook twitter " data-se-share-sheet-location="2" data-se-share-sheet-license-url="https%3a%2f%2fcreativecommons.org%2flicenses%2fby-sa%2f4.0%2f" data-se-share-sheet-license-name="CC BY-SA 4.0" data-s-popover-placement="bottom-start" aria-controls="se-share-sheet-1" data-action=" s-popover#toggle se-share-sheet#preventNavigation s-popover:show->se-share-sheet#willShow s-popover:shown->se-share-sheet#didShow">Share</a><div class="s-popover z-dropdown s-anchors s-anchors__default" style="width: unset; max-width: 28em;" id="se-share-sheet-1"><div class="s-popover--arrow"></div><div><label class="js-title fw-bold" for="share-sheet-input-se-share-sheet-1">Share a link to this answer</label> <span class="js-subtitle"></span></div><div class="my8"><input type="text" id="share-sheet-input-se-share-sheet-1" class="js-input s-input wmn3 sm:wmn-initial" readonly=""></div><div class="d-flex jc-space-between ai-center mbn4"><button class="js-copy-link-btn s-btn s-btn__link js-gps-track" data-gps-track="">Copy link</button><a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="license" class="js-license s-block-link w-auto" target="_blank" title="The current license for this post: CC BY-SA 4.0">CC BY-SA 4.0</a><div class="js-social-container d-none"></div></div></div>
            </div>


                    <div class="flex--item">
                        <a href="/posts/69684198/edit" class="js-suggest-edit-post js-gps-track" data-gps-track="post.click({ item: 6, priv: 0, post_type: 2 })" title="">Improve this answer</a>
                    </div>

            <div class="flex--item">
                <button type="button" id="btnFollowPost-69684198" class="s-btn s-btn__link js-follow-post js-follow-answer js-gps-track" data-gps-track="post.click({ item: 14, priv: 0, post_type: 2 })" data-controller="s-tooltip " data-s-tooltip-placement="bottom" data-s-popover-placement="bottom" aria-controls="" aria-describedby="--stacks-s-tooltip-mu3trcfw">
                    Follow
                </button><div id="--stacks-s-tooltip-mu3trcfw" class="s-popover s-popover__tooltip pe-none" aria-hidden="true" role="tooltip">Follow this answer to receive notifications<div class="s-popover--arrow"></div></div>
            </div>






    </div>
    <div class="js-menu-popup-container"></div>
</div>
            </div>


            <div class="post-signature flex--item fl0">
                <div class="user-info user-hover">
    <div class="user-action-time">
        answered <span title="2021-10-23 00:18:21Z" class="relativetime">Oct 23, 2021 at 0:18</span>
    </div>
    <div class="user-gravatar32">
        <a href="/users/5811874/hanchen-jiang"><div class="gravatar-wrapper-32"><img src="https://www.gravatar.com/avatar/0edd4a65c8ce858140484f10c64661e4?s=64&amp;d=identicon&amp;r=PG&amp;f=1" alt="user avatar" width="32" height="32" class="bar-sm"></div></a>
    </div>
    <div class="user-details" itemprop="author" itemscope="" itemtype="http://schema.org/Person">
        <a href="/users/5811874/hanchen-jiang">Hanchen Jiang</a><span class="d-none" itemprop="name">Hanchen Jiang</span>
        <div class="-flair">
            <span class="reputation-score" title="reputation score " dir="ltr">2,139</span><span title="2 gold badges" aria-hidden="true"><span class="badge1"></span><span class="badgecount">2</span></span><span class="v-visible-sr">2 gold badges</span><span title="8 silver badges" aria-hidden="true"><span class="badge2"></span><span class="badgecount">8</span></span><span class="v-visible-sr">8 silver badges</span><span title="18 bronze badges" aria-hidden="true"><span class="badge3"></span><span class="badgecount">18</span></span><span class="v-visible-sr">18 bronze badges</span>
        </div>
    </div>
</div>


            </div>
        </div>
        
    
    </div>
    
</div>




            <span class="d-none" itemprop="commentCount">6</span> 
    <div class="post-layout--right js-post-comments-component">
        <div id="comments-69684198" class="comments js-comments-container bt bc-black-075 mt12 " data-post-id="69684198" data-min-length="15">
            <ul class="comments-list js-comments-list" data-remaining-comments-count="1" data-canpost="false" data-cansee="true" data-comments-unavailable="false" data-addlink-disabled="true">

                        <li id="comment-124428687" class="comment js-comment " data-comment-id="124428687" data-comment-owner-id="9834660" data-comment-score="1">
        <div class="js-comment-actions comment-actions">
            <div class="comment-score js-comment-edit-hide">
                    <span title="number of 'useful comment' votes received" class="cool">1</span>
            </div>
        </div>
        <div class="comment-text  js-comment-text-and-form">
            <div class="comment-body js-comment-edit-hide">
                
                <span class="comment-copy">Work for me to! TNX!</span>
                
              <div class="d-inline-flex ai-center">
–&nbsp;<a href="/users/9834660/shay-elbaz" title="51 reputation" class="comment-user">Shay Elbaz</a>
                </div>
                <span class="comment-date" dir="ltr"><a class="comment-link" href="#comment124428687_69684198"><span title="2021-12-17 09:03:47Z, License: CC BY-SA 4.0" class="relativetime-clean">Dec 17, 2021 at 9:03</span></a></span>
            </div>
        </div>
    </li>
    <li id="comment-124545864" class="comment js-comment " data-comment-id="124545864" data-comment-owner-id="1187878" data-comment-score="1">
        <div class="js-comment-actions comment-actions">
            <div class="comment-score js-comment-edit-hide">
                    <span title="number of 'useful comment' votes received" class="cool">1</span>
            </div>
        </div>
        <div class="comment-text  js-comment-text-and-form">
            <div class="comment-body js-comment-edit-hide">
                
                <span class="comment-copy">Worked for me as well !!</span>
                
              <div class="d-inline-flex ai-center">
–&nbsp;<a href="/users/1187878/pcsaunak" title="160 reputation" class="comment-user">pcsaunak</a>
                </div>
                <span class="comment-date" dir="ltr"><a class="comment-link" href="#comment124545864_69684198"><span title="2021-12-22 23:52:27Z, License: CC BY-SA 4.0" class="relativetime-clean">Dec 22, 2021 at 23:52</span></a></span>
            </div>
        </div>
    </li>
    <li id="comment-125450265" class="comment js-comment " data-comment-id="125450265" data-comment-owner-id="986779" data-comment-score="0">
        <div class="js-comment-actions comment-actions">
            <div class="comment-score js-comment-edit-hide">
            </div>
        </div>
        <div class="comment-text  js-comment-text-and-form">
            <div class="comment-body js-comment-edit-hide">
                
                <span class="comment-copy">Worked for me! thank you so much</span>
                
              <div class="d-inline-flex ai-center">
–&nbsp;<a href="/users/986779/thenuke" title="51 reputation" class="comment-user">TheNuke</a>
                </div>
                <span class="comment-date" dir="ltr"><a class="comment-link" href="#comment125450265_69684198"><span title="2022-02-02 22:53:39Z, License: CC BY-SA 4.0" class="relativetime-clean">Feb 2 at 22:53</span></a></span>
            </div>
        </div>
    </li>
    <li id="comment-125515802" class="comment js-comment " data-comment-id="125515802" data-comment-owner-id="7610903" data-comment-score="1">
        <div class="js-comment-actions comment-actions">
            <div class="comment-score js-comment-edit-hide">
                    <span title="number of 'useful comment' votes received" class="cool">1</span>
            </div>
        </div>
        <div class="comment-text  js-comment-text-and-form">
            <div class="comment-body js-comment-edit-hide">
                
                <span class="comment-copy">Worked also for me. Thank you so much</span>
                
              <div class="d-inline-flex ai-center">
–&nbsp;<a href="/users/7610903/jeremiah" title="95 reputation" class="comment-user">Jeremiah</a>
                </div>
                <span class="comment-date" dir="ltr"><a class="comment-link" href="#comment125515802_69684198"><span title="2022-02-05 20:35:57Z, License: CC BY-SA 4.0" class="relativetime-clean">Feb 5 at 20:35</span></a></span>
            </div>
        </div>
    </li>
    <li id="comment-125560787" class="comment js-comment " data-comment-id="125560787" data-comment-owner-id="8396562" data-comment-score="0">
        <div class="js-comment-actions comment-actions">
            <div class="comment-score js-comment-edit-hide">
            </div>
        </div>
        <div class="comment-text  js-comment-text-and-form">
            <div class="comment-body js-comment-edit-hide">
                
                <span class="comment-copy">worked for me! ty</span>
                
              <div class="d-inline-flex ai-center">
–&nbsp;<a href="/users/8396562/samantha" title="594 reputation" class="comment-user">Samantha</a>
                </div>
                <span class="comment-date" dir="ltr"><a class="comment-link" href="#comment125560787_69684198"><span title="2022-02-08 02:36:43Z, License: CC BY-SA 4.0" class="relativetime-clean">Feb 8 at 2:36</span></a></span>
            </div>
        </div>
    </li>

            </ul>
	    </div>

        <div id="comments-link-69684198" data-rep="50" data-anon="true">
                    <a class="js-add-link comments-link dno" title="Use comments to ask for more information or suggest improvements. Avoid comments like “+1” or “thanks”." href="#" role="button"></a>
                <span class="js-link-separator dno">&nbsp;|&nbsp;</span>
            <a class="js-show-link comments-link " title="Expand to show all comments on this post" href="#" onclick="" role="button">Show <b>1</b> more comment</a>
        </div>         
    </div>
    </div>
</div>
</pre>
$requested_page_input
<button class="endpoint-tester-btn button__primary">Get HTML from the requested page</button>
</form>


EOF;
		




		
		
		$vce->content->add('main', $content, array('place' => 'last'));

	
	}
	

	
		
	/**
	 *
	 */
	public function as_content_finish($each_component, $vce) {
	
	
	}



	/**
	 * fields for ManageRecipe
	 */
	public function recipe_fields($recipe) {
	
		global $vce;
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
        $endpoint_url = $vce->site->site_url . '/endpoint';

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
<input type="text" name="url" value="$endpoint_url" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">URL</div>
<div class="label-error">Enter a URL</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}