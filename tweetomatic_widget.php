<?php
/*
	Tweetomatic - Widget page
	Copyright 2014  Creatomatic Ltd
*/

if(!defined("TWEETOMATIC_VERSION"))
	die();

class tweetomatic_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		"tweetomatic_widget", // widget ID
			"Tweetomatic",        // name
			array(
				"description" => "A Twitter feed widget"
			)
		);
	}

	public function update($new_instance, $old_instance) {
		//$new_instances = array();
		//foreach($new_instance as $key => $new_instanc) {
		//	$new_instances[$key] = strip_tags( $new_instanc );
		//}

		foreach(array("show_powered_by") as $checkbox) {
			$new_instance[$checkbox] = (isset($new_instance[$checkbox]) ? "true" : "false");
		}

		return $new_instance;
	}


	// admin box
	public function form($instance) {

		$std_fields = array(
			// field => array(name, default, pro-only)
			"General",
			"title"                      => array("Title", ""),
			"user"                       => array("Twitter username"),
		);

		foreach($std_fields as $field => $values) {
			// assume title
			if( !is_array($values)) { ?>
		<p>
			<b><?php echo $values; ?></b>
		</p>
<?php
				continue;
			}

			?>
		<p>
			<label><?php echo $values[0]; ?></label><br />

			<input name="<?php echo $this->get_field_name($field); ?>" type="text" <?php
			if(isset($instance[$field])) {
				echo 'value="' .htmlentities($instance[$field]) . '"';
			// say default
			} else {
				echo 'value="' . htmlentities(@$values[1]) . '"';
			} ?> />
		</p>
<?php
		}

		// additional, non-standard fields
?>
		<p>
			<b>Advanced</b>
		</p>

		<p>
			<label>Show 'Powered by...'</label><br />
			<input name="<?php echo $this->get_field_name('show_powered_by'); ?>" type="checkbox" <?php
				if(@$instance["show_powered_by"] != "false") {
					echo "checked";
				} ?> />
		</p>

<?php
	}



	// client end
	public function widget($args, $instance) {
		extract( $args );

		echo $before_widget;

		$instance["title"] = $before_title . apply_filters("widget_title", @$instance["title"]) . $after_title;

		echo tweetomatic($instance);

		echo $after_widget;
	}

} // end of class

function register_tweetomatic_widget () {
	register_widget("tweetomatic_widget");
}

add_action("widgets_init", "register_tweetomatic_widget");
?>
