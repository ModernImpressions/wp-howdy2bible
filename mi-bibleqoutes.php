<?php
/**
 * @package mi-bibleqoutes
 * @version 1.0.0
 */
/*
Plugin Name: Modern Impressions - Bible Quote Welcome
Plugin URI: 
Description: <cite>Daily Bible Quotes</cite> in the upper right of your admin screen on every page.
Author: Patrick Barnhardt
Version: 1.0.0
Author URI: http://patrickbarnhardt.info
License: GPL2
*/

function mi_biblequote_get_quote($showlink, $language='en'){
	$languageAdd = '';
	$languageUrl = '';
	$language = 'en';
	$showlink = 'false';

	$bibleVerseOfTheDay_Date = get_option('bibleVerseOfTheDay_Date' . $languageAdd);
	$bibleVerseOfTheDay_bibleVerse = get_option('bibleVerseOfTheDay_Verse' . $languageAdd);
	$bibleVerseOfTheDay_lastAttempt = get_option('bibleVerseOfTheDay_LastAttempt' . $languageAdd);
				
	$bibleVerseOfTheDay_currentDate = date('Y-m-d');

	if($bibleVerseOfTheDay_Date != $bibleVerseOfTheDay_currentDate && $bibleVerseOfTheDay_lastAttempt < (date('U') - 3600))
	{
		$url = 'http://dailyverses.net/getdailyverse.ashx?language=' . $language . '&date=' . $bibleVerseOfTheDay_currentDate . '&url=' . $_SERVER['HTTP_HOST'] . '&type=daily2_1';
		$result = wp_remote_get($url);

		update_option('bibleVerseOfTheDay_LastAttempt' . $languageAdd, date('U'));
		
		if(!is_wp_error($result)) 
		{
			$bibleVerseOfTheDay_bibleVerse = str_replace(',', '&#44;', $result['body']);

			update_option('bibleVerseOfTheDay_Date' . $languageAdd, $bibleVerseOfTheDay_currentDate);
			update_option('bibleVerseOfTheDay_Verse' . $languageAdd, $bibleVerseOfTheDay_bibleVerse);
		}
	}

	if($bibleVerseOfTheDay_bibleVerse == "")
	{
		$bibleVerseOfTheDay_bibleVerse = '<div class="dailyVerses bibleText">For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.</div><div class="dailyVerses bibleVerse"><a href="http://dailyverses.net/john/3/16" target="_blank">John 3:16</a></div>';
	}

	if($showlink == 'true' || $showlink == '1')
	{
		$html =  $bibleVerseOfTheDay_bibleVerse . '<div class="dailyVerses linkToWebsite"><a href="http://dailyverses.net' . $languageUrl . '" target="_blank">DailyVerses.net</a></div>';
	}
	else
	{
		$html = $bibleVerseOfTheDay_bibleVerse;
	}
	
	return $html;
}

function random_bible_verse($showlink, $language='en') {
	$languageAdd = '';
	$languageUrl = '';
	$language = 'en';
	$showlink = 'false';

	$position = rand(0, 200);
	$randomBibleVerse = get_option('randomBibleVerse_' . $position . $languageAdd);
	$randomBibleVerse_lastAttempt = get_option('randomBibleVerse_LastAttempt' . $languageAdd);
	
	if($randomBibleVerse == "" && $randomBibleVerse_lastAttempt < (date('U') - 3600))
	{
		$url = 'http://dailyverses.net/getrandomverse.ashx?language=' . $language . '&position=' . $position . '&url=' . $_SERVER['HTTP_HOST'] . '&type=random2_1';
		$result = wp_remote_get($url);

		if(!is_wp_error($result)) 
		{
			$randomBibleVerse = str_replace(',', '&#44;', $result['body']);

			update_option('randomBibleVerse_' . $position . $languageAdd, $randomBibleVerse);
		}
		else
		{
			update_option('randomBibleVerse_LastAttempt' . $languageAdd, date('U'));
		}
	}
	if($randomBibleVerse == "")
	{
		$randomBibleVerse = '<div class="dailyVerses bibleText">For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.</div><div class="dailyVerses bibleVerse"><a href="http://dailyverses.net/john/3/16" target="_blank">John 3:16</a></div>';
	}

	if($showlink == 'true' || $showlink == '1')
	{
		$html = $randomBibleVerse . '<div class="dailyVerses linkToWebsite"><a href="http://dailyverses.net' . $languageUrl . '" target="_blank">DailyVerses.net</a></div>';
	}
	else
	{
		$html = $randomBibleVerse;
	}
	
	return $html;
}

// This just echoes the chosen line, we'll position it later.
function mi_bibleverse() {
	echo random_bible_verse($showlink, $language);
}

// Now we set that function up to execute when the admin_notices action is called.
add_action( 'admin_notices', 'mi_bibleverse' );

add_filter( 'admin_bar_menu', 'replace_wordpress_howdy', 25 );
function replace_wordpress_howdy( $wp_admin_bar ) {
$my_account = $wp_admin_bar->get_node('my-account');
$newtext = str_replace( 'Howdy,', 'Welcome,', $my_account->title );
$wp_admin_bar->add_node( array(
'id' => 'my-account',
'title' => $newtext,
) );
}

// We need some CSS to position the paragraph.
function bible_css() {
	echo "
	<style type='text/css'>
	#bible {
		float: right;
		padding: 5px 10px;
		margin: 0;
		font-size: 12px;
		line-height: 1.6666;
	}
	.rtl #bible {
		float: left;
	}
	.block-editor-page #bible {
		display: none;
	}
	@media screen and (max-width: 782px) {
		#bible,
		.rtl #bible {
			float: none;
			padding-left: 0;
			padding-right: 0;
		}
	}
	</style>
	";
}

add_action( 'admin_head', 'bible_css' );
