<?php
/*
	Tweetomatic - Settings page
	Copyright 2014  Creatomatic Ltd
*/

if(!defined("TWEETOMATIC_VERSION"))
	die();

function add_tweetomatic_settings_page_menu() {
	add_menu_page("Tweetomatic settings", "Tweetomatic", "manage_options", "tweetomatic-settings", "build_tweetomatic_settings_page", plugin_dir_url(__FILE__) . "/image/tweetomatic.png");
}


function build_tweetomatic_settings_page(){
	?>
<div class="wrap">
	<img src="<?php echo plugin_dir_url(__FILE__); ?>/image/tweetomatic_big.png" style="float: left; margin: 5px 10px 0px 0px;" />
	<h2>Tweetomatic settings</h2>
	<form method="post" action="options.php">
<?php
	settings_fields("option_group_one");
	do_settings_sections("theme-theme-settings");
	?>
<?php submit_button(); ?>
	</form>


	<div>
		<a target="_blank" href="http://tweetomatic.co.uk/#howto">How to get these settings</a> | 
		<a target="_blank" href="http://tweetomatic.co.uk/docs">How to configure Tweetomatic</a>
		<br /><br />
		<a target="_blank" href="http://tweetomatic.co.uk/">
			<img style="border-style: none;" src="<?php echo plugin_dir_url(__FILE__); ?>/image/support_us.jpg" alt="Support us" />
		</a>
	</div>
</div>
<?php
}



function init_tweetomatic_settings_page() {
	add_settings_section ("setting_group_one", "Twitter API Keys", false, "theme-theme-settings");

	$fields = array(
		// re-ordered to flow with twitter
		"tweetomatic_user"                 => array("Twitter username"),
		"tweetomatic_consumer_key"         => array("Consumer key"),
		"tweetomatic_consumer_secret"      => array("Consumer secret"),
		"tweetomatic_access_token"         => array("Access token"),
		"tweetomatic_access_token_secret"  => array("Access token secret"),
	);

	foreach($fields as $field => $arr) {
		$title = $arr[0];

		$type = !isset($arr[1]) ? "ts_text" : $arr[1];

		// register_setting( $option_group, $option_name, $sanitize_callback )
		register_setting("option_group_one", $field, "ts_save_string");

		add_settings_field (
			$field, 
			$title, 
			$type, 
			"theme-theme-settings",
			"setting_group_one",

			// callback args
			array(
				"field" => $field,
			)
		);
	}
}

function ts_text($args) {
	?>
	<input type="text" name="<?php echo $args['field']; ?>" value="<?php echo htmlentities(get_option($args['field'])); ?>" style="width: 420px;" />
<?php
}

function ts_textarea($args) {
	?>
	<textarea name="<?php echo $args['field']; ?>" style="width: 420px;" /><?php echo htmlentities(get_option($args['field'])); ?></textarea>
<?php
}


function ts_save_string ($input) {
	return $input;
}


if(is_admin()) {
	add_action("admin_menu", "add_tweetomatic_settings_page_menu");
	add_action("admin_init", "init_tweetomatic_settings_page");
}
?>
