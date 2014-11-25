<?php
/*
Plugin Name: Tweetomatic Basic
Plugin URI: http://tweetomatic.co.uk/
Description: A simple Wordpress Twitter feed widget and shortcode compatible with the new Twitter 1.1 API.
Version: 1.3
Author: Creatomatic Ltd
Author URI: http://www.creatomatic.co.uk/
License: Copyright 2014  Creatomatic Ltd (email : info@creatomatic.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(defined("TWEETOMATIC_VERSION"))
	return false;

define("TWEETOMATIC_VERSION", "basic_1.3");

include dirname(__FILE__) . "/tweetomatic_widget.php";
include dirname(__FILE__) . "/tweetomatic_settings.php";

if(!class_exists("TwitterAPIExchange"))
	include_once dirname(__FILE__) . "/TwitterAPIExchange.php";

function tweetomatic ( $atts, $content = null ) {

	$tweetomatic_config = array(
		"user"                  => get_option("tweetomatic_user"),

		"consumer_key"          => get_option("tweetomatic_consumer_key"),
		"consumer_secret"       => get_option("tweetomatic_consumer_secret"),
		"access_token"          => get_option("tweetomatic_access_token"),
		"access_token_secret"   => get_option("tweetomatic_access_token_secret"),

		"title"                 => "",
		"show_powered_by"       => "true",
	);

	foreach($tweetomatic_config as $field => $default) {
		if(isset($atts[$field]))
			$tweetomatic_config[$field] = $atts[$field];
	}

	$html = "\n<div class=\"tweetomatic\">\n";
	$html .= $tweetomatic_config["title"]. "\n";

	$cache_key = "tweetomatic_cache_" . substr( sha1( serialize($tweetomatic_config) ) , 0, 10);
	//$cache_file = dirname(__FILE__) . "/cache/" . substr( sha1( serialize($tweetomatic_config) ) , 0, 16) .".cache";

	//if(file_exists($cache_file) && (time() - filemtime($cache_file)) < 60*5 && ($cache_version = @file_get_contents($cache_file)) ) {
	if( false !== ( $cache_version = get_transient( $cache_key ) ) ) {
		return $cache_version; // . "\n loaded from cache!\n";
	}

	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";

	$getfield = "?screen_name=" . $tweetomatic_config["user"] . "&count=1";
	$requestMethod = "GET";

	if(!class_exists("TwitterAPIExchange"))
		return "Tweetomatic error:  Could not start Twitter feed\n";

	$twitter = new TwitterAPIExchange(array(
		"consumer_key"               => $tweetomatic_config["consumer_key"],
		"consumer_secret"            => $tweetomatic_config["consumer_secret"],
		"oauth_access_token"         => $tweetomatic_config["access_token"],
		"oauth_access_token_secret"  => $tweetomatic_config["access_token_secret"],
	));

	$arr = $twitter->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
			->performRequest();

	if(!$arr)
		return "Tweetomatic error:  Could not load Twitter feed\n";

	$arr = json_decode($arr);

	if(!$arr)
		return "Tweetomatic error:  Could not parse Twitter feed\n";

	if(!empty($arr->errors)) {
		$err = "";

		foreach($arr->errors as $error)
			$err .= $error->message . ", ";

		return "Tweetomatic error:  " . substr($err, 0, -2) . "\n";
	}

	// for each tweet
	foreach($arr as $tweet) {
		//print_r($tweet);

		// if retweet - use that data instead
		$retweet = false;

		if(!empty($tweet->retweeted_status)) {
			$retweet = true;
			$orig_entities = $tweet->retweeted_status->entities;
			$tweet_formatted = $tweet->retweeted_status->text;

		} else {
			$orig_entities = $tweet->entities;
			$tweet_formatted = $tweet->text;
		}

		// we need to preload all entities and process biggest index first
		$entities_arr = array();
		foreach($orig_entities as $entity_type => $entities) {
			foreach($entities as $entity) {
				//print_r($entity);
				$entities_arr[$entity->indices[0]] = array(
					"type"        => $entity_type,
					"indice_stop" => $entity->indices[1],
					// for mentions
					"screen_name" => @$entity->screen_name,
					// for links
					"url"         => @$entity->url,
					"display_url" => @$entity->display_url,
					// for hashtags
					"text"        => @$entity->text,
				);
			}
		}

		krsort($entities_arr, SORT_NUMERIC);

		foreach($entities_arr as $indice_start => $entity) {
			if(!in_array($entity["type"], array("user_mentions", "urls", "media", "hashtags") ))
				continue;

			// text before entity
			$tmp = mb_substr($tweet_formatted, 0, $indice_start);

			if($entity["type"] == "user_mentions")
				$tmp .= "<a target=\"_blank\" href=\"https://twitter.com/" . $entity["screen_name"] . "\">@" . $entity["screen_name"] . "</a>";

			else if($entity["type"] == "urls" || $entity["type"] == "media")
				$tmp .= "<a target=\"_blank\" href=\"" . $entity["url"] . "\">" . $entity["display_url"] . "</a>";

			else if($entity["type"] == "hashtags")
				$tmp .= "<a target=\"_blank\" href=\"https://twitter.com/search/realtime?q=%23" . $entity["text"] . "&src=hash\">#" . $entity["text"] . "</a>";

			// text after entity
			$tweet_formatted = $tmp . mb_substr($tweet_formatted, $entity["indice_stop"]);

		}

		if($retweet === true)
			$tweet_formatted = "RT: " . $tweet_formatted;

		$html .= "	" . $tweetomatic_config["before_tweet"] . "\n		" . $tweet_formatted . "\n	" . $tweetomatic_config["after_tweet"] . "\n";
	}


	if($tweetomatic_config["show_powered_by"] == "false")
		$final =  $html . "	\n</div>\n\n";
	else
		$final = $html . "	<div style=\"font-size: 12px;\">Powered by <a target=\"_blank\" href=\"http://tweetomatic.co.uk/\">Tweetomatic</a></div>\n</div>\n\n";

	// cache our tweet
	//file_put_contents($cache_file, $final);
	//chmod($cache_file, 0600);

	set_transient($cache_key, $final, 60*5);

	return $final;
}


add_shortcode("tweetomatic", "tweetomatic");
?>
