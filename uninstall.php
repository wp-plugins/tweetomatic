<?php
/*
  Tweetomatic - Uninstall
  Copyright 2013  Creatomatic Ltd
*/

if(!defined( 'WP_UNINSTALL_PLUGIN'))
	return false;

if(!defined('TWEETOMATIC_DEV')) {
	delete_option('tweetomatic_user');
	delete_option('tweetomatic_consumer_key');
	delete_option('tweetomatic_consumer_secret');
	delete_option('tweetomatic_access_token');
	delete_option('tweetomatic_access_token_secret');
}
?>
