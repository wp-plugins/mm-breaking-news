<?php
/*
Plugin Name: MM-Breaking News
Plugin URI: http://www.svetnauke.org/mm-breaking-news
Description: Displays lists of posts from selected categories whereever you like. You can select how many different lists you want, sort posts by date or random, select which categories to include or exclude from specific list.
Author: Milan Milosevic
Author URI: http://www.svetnauke.org/
Version: 0.7.7
License: GPL v3 - http://www.gnu.org/licenses/

Installation: You have to add <?php if (function_exists('mm_bnlist')) mm_bnlist() ?> or <?php if (function_exists('mm_bnlist_multi')) mm_bnlist_multi(2) ?> to your theme file. Also you can use widget or shortcode.

    Copyright 2009-2010  Milan Milosevic  (email : mm@mmilan.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Display function
function mm_bnlist_print ($case, $title, $cats_in, $cats_out, $num, $show_rand, $show_date, $show_comments, $bnlist_time, $css_class = "") {

	switch ($case) {
		case 'mm_bnlist_main':
			$mm_string = '<span class="mm_bnlist_title">'.$title.'</span>';
			break;
		case 'mm_bnlist_multi':
			$mm_string = '<span class="mm_bnlist_title">'.$title.'</span>';
			break;
		case 'mm_bnlist_post':
			$mm_string = '<h3 class="mm_bnlist_title">'.$title.'</h3>';
			break;	
		default:
 			$mm_string = '';
	}
	
	if ($bnlist_time == "YES") $ul_style = ' style="list-style-type: none;"'; else $ul_style = '';

	if (strlen($css_class) == 0) $mm_string .= '<ul'.$ul_style.'>'; else $mm_string .= '<ul'.$ul_style.' class="'.$css_class.'">';

		$catid = Array();
		if (!empty($cats_in)) foreach ($cats_in as $tmp) $catid[] = $tmp;
		if (!empty($cats_out)) foreach ($cats_out as $tmp) $catid[] = -$tmp;
		$catids = implode(',', $catid);

		if ($show_rand == "YES") $myposts = get_posts("numberposts=$num&category=$catids&orderby=rand");
			else $myposts = get_posts("numberposts=$num&category=$catids");

		foreach($myposts as $show_post) :
			setup_postdata($show_post);
			if ($show_date == "YES") {
				$mm_time_format = get_option( 'mm_bnlist_time_format');
				$sh_date = mysql2date($mm_time_format, $show_post->post_date);
			}
			if ($show_comments == "YES") {
				$no_com = $show_post->comment_count;
				if ($no_com < 2) $no_com .= " comment";
					else $no_com .= " comments";
			}
			
			$show_title = __($show_post->post_title);
//			$show_title = strtoupper(__($show_post->post_title));

			if ($bnlist_time == "YES")
				$print_time = '('.get_post_time('h:ia', false, $show_post->ID).') ';
			else $print_time = '';
			
			if (($show_date == "YES") and ($show_comments != "YES")) 
				$mm_string .= '<li class="mm_bnlist_li">'.$print_time.'<a href="'.get_permalink($show_post->ID).'">'.$show_title.'</a><span class="mm_bnlist_date_com"> ('.$sh_date.')</span></li>';

			if (($show_date != "YES") and ($show_comments == "YES")) 
				$mm_string .= '<li class="mm_bnlist_li">'.$print_time.'<a href="'.get_permalink($show_post->ID).'">'.$show_title.'</a><span class="mm_bnlist_date_com"> ('.$no_com.')</span></li>';

			if (($show_date == "YES") and ($show_comments == "YES"))
				$mm_string .= '<li class="mm_bnlist_li">'.$print_time.'<a href="'.get_permalink($show_post->ID).'">'.$show_title.'</a><span class="mm_bnlist_date_com"> ('.$sh_date.', '.$no_com.')</span></li>';

			if (($show_date != "YES") and ($show_comments != "YES"))
				$mm_string .= '<li class="mm_bnlist_li">'.$print_time.'<a href="'.get_permalink($show_post->ID).'">'.$show_title.'</a></li>';

		endforeach;
	$mm_string .= "</ul>";
	
	return $mm_string;
}

function mm_bnlist_credits($case, $show_c) {
	if ($show_c != "NO")
		return '<p class="mm_credits">Plugin by <a href="http://www.svetnauke.org">Svet nauke</a></p>';
	else return ''; 
}

// Add Widget
class WP_Widget_bnlist extends WP_Widget {

	function WP_Widget_bnlist() {

		parent::WP_Widget(false, $name = 'Breaking News');
	}

	function widget($args, $instance) {

		global $wpdb;

		extract($args);

		$option_title = apply_filters('widget_title', empty($instance['title']) ? 'Breaking News' : $instance['title']);
		if ($instance['bnlist_rnd'] == 'on') $bnlist_rnd = "YES"; else $bnlist_rnd = "NO";
		if ($instance['bnlist_date'] == 'on') $bnlist_date = "YES"; else $bnlist_date = "NO";
		if ($instance['bnlist_com'] == 'on') $bnlist_com = "YES"; else $bnlist_com = "NO";
		if ($instance['bnlist_credits'] == 'on') $bnlist_credits = "NO"; else $bnlist_credits = "YES";
		if ($instance['bnlist_time'] == 'on') $bnlist_time = "YES"; else $bnlist_time = "NO";

		// Create the widget
		echo $before_widget;
		echo $before_title . $option_title . $after_title;
	
		// Widget code goes here
		echo mm_bnlist_print ('mm_bnlist_widget', 'no title', $instance['cat_in'], $instance['cat_out'], $instance['bnlist_num'], $bnlist_rnd, $bnlist_date, $bnlist_com, $bnlist_time, $instance['bnlist_css_id']);
		echo mm_bnlist_credits('mm_bnlist_widget', $bnlist_credits);

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {

		$instance = $new_instance;
		
		return $instance;
	}

	function form($instance) {

		$instance = wp_parse_args((array)$instance, array('title' => 'Breaking News'));
		$option_title = strip_tags($instance['title']);
		$cat_in = $instance['cat_in'];
		$cat_out = $instance['cat_out'];
		$option_num = $instance['bnlist_num'];
		$option_css_id = $instance['bnlist_css_id'];
		
		if (!is_array($cat_in)) $cat_in = array();
		if (!is_array($cat_out)) $cat_out = array();

		echo '<p>';
		echo 	'<label for="' . $this->get_field_id('title') . '">Title:</label>';
		echo 	'<input class="widefat" type="text" value="' . $option_title . '" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" />';
		echo '</p>';
?>
		<p>
			<?php $cats = get_categories(); ?>
			<p><label for="<?php echo $this->get_field_name( 'cat_in' ); ?>">Include:</label>
			<SELECT NAME="<?php echo $this->get_field_name( 'cat_in' ); ?>[]" MULTIPLE SIZE=3 style="height: auto">
				<?php 	foreach ($cats as $cat) {
						if (in_array($cat->cat_ID, $cat_in)) $selected = 'selected="selected"';
							else $selected = '';
						echo '<option value="'.$cat->cat_ID.'" '.$selected.'>'.$cat->cat_name."</option>";
					}
				?>
			</SELECT></p>
			
			<p><label for="<?php echo $this->get_field_name( 'cat_out' ); ?>">Exclude:</label>
			<SELECT NAME="<?php echo $this->get_field_name( 'cat_out' ); ?>[]" MULTIPLE SIZE=3 style="height: auto">
				<?php 	foreach ($cats as $cat) {
						if (in_array($cat->cat_ID, $cat_out)) $selected = 'selected="selected"';
							else $selected = '';
						echo '<option value="'.$cat->cat_ID.'" '.$selected.'>'.$cat->cat_name."</option>";
					}
				?>
			</SELECT></p>
			
			<?php 
			echo '<p>';
			echo '	<label for="' . $this->get_field_id('bnlist_num') . '">Number of posts:</label>';
			echo '	<input class="widefat" type="text" value="' . $option_num . '" id="' . $this->get_field_id('bnlist_num') . '" name="' . $this->get_field_name('bnlist_num') . '" />';
			echo '</p>';
			?>

			<?php 
			echo '<p>';
			echo '	<label for="' . $this->get_field_id('bnlist_css_id') . '">Custom CSS class:</label>';
			echo '	<input class="widefat" type="text" value="' . $option_css_id . '" id="' . $this->get_field_id('bnlist_css_id') . '" name="' . $this->get_field_name('bnlist_css_id') . '" />';
			echo '</p>';
			?>
			
			<p><input class="checkbox" type="checkbox" <?php checked( (bool)  $instance['bnlist_com'], true ); ?> id="<?php echo $this->get_field_id( 'bnlist_com' ); ?>" name="<?php echo $this->get_field_name( 'bnlist_com' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bnlist_com' ); ?>">Show number of comments</label></p>

			<p><input class="checkbox" type="checkbox" <?php checked( (bool)  $instance['bnlist_date'], true ); ?> id="<?php echo $this->get_field_id( 'bnlist_date' ); ?>" name="<?php echo $this->get_field_name( 'bnlist_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bnlist_date' ); ?>">Show post date</label></p>
			
			<p><input class="checkbox" type="checkbox" <?php checked( (bool)  $instance['bnlist_time'], true ); ?> id="<?php echo $this->get_field_id( 'bnlist_time' ); ?>" name="<?php echo $this->get_field_name( 'bnlist_time' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bnlist_time' ); ?>">Show post time (replace bullet list)</label></p>

			<p><input class="checkbox" type="checkbox" <?php checked( (bool)  $instance['bnlist_rnd'], true ); ?> id="<?php echo $this->get_field_id( 'bnlist_rnd' ); ?>" name="<?php echo $this->get_field_name( 'bnlist_rnd' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bnlist_rnd' ); ?>">Randomize posts</label></p>
			
			<p><input class="checkbox" type="checkbox" <?php checked( (bool)  $instance['bnlist_credits'], true ); ?> id="<?php echo $this->get_field_id( 'bnlist_credits' ); ?>" name="<?php echo $this->get_field_name( 'bnlist_credits' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bnlist_credits' ); ?>">Don't show credits</label></p>
		</p>
<?php	}
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_bnlist");'));


// Add custom CSS style for box
function mm_bnlist_css() {

	$pluginURL = 'wp-content/plugins/';
	$pluginName = 'mm-breaking-news';
	
	$cssUrl = get_bloginfo('url')."/".$pluginURL.$pluginName.'/mm-bnlist.css';	
	echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />';
}

add_action('wp_head', 'mm_bnlist_css');


// Function for template
function mm_bnlist () {
	if ( (is_front_page()) or (get_option('mm_bnlist_front') == "NO")) {

		// Read in existing option value from database
		$title = unserialize(get_option('mm_bnlist_title'));
		$num = unserialize(get_option('mm_bnlist_num'));
		$cats_in = unserialize(get_option('mm_bnlist_in'));
		$cats_out = unserialize(get_option('mm_bnlist_out'));

		$show_comments = unserialize(get_option('mm_bnlist_comments'));
		$show_date = unserialize(get_option('mm_bnlist_date'));
		$show_rand = unserialize(get_option('mm_bnlist_rand'));
		$show_time = unserialize(get_option('mm_bnlist_time'));

		echo '<div id="mm_bnlist_main">';
			for ( $i = 0; $i < get_option('mm_bnlist_n'); $i+=1 ) {
				echo mm_bnlist_print ('mm_bnlist_main', $title[$i], $cats_in[$i], $cats_out[$i], $num[$i], $show_rand[$i], $show_date[$i], $show_comments[$i], $show_time[$i]);
			}

			echo mm_bnlist_credits('mm_bnlist_main', get_option('mm_bnlist_credits'));
		echo "</div>";
	}
}

// Multi- columns function for template
function mm_bnlist_multi ($no) {
	if ( (is_front_page()) or (get_option('mm_bnlist_front') == "NO")) {

		// Read in existing option value from database
		$title = unserialize(get_option('mm_bnlist_title'));
		$num = unserialize(get_option('mm_bnlist_num'));
		$cats_in = unserialize(get_option('mm_bnlist_in'));
		$cats_out = unserialize(get_option('mm_bnlist_out'));

		$show_comments = unserialize(get_option('mm_bnlist_comments'));
		$show_date = unserialize(get_option('mm_bnlist_date'));
		$show_rand = unserialize(get_option('mm_bnlist_rand'));
		$show_time = unserialize(get_option('mm_bnlist_time'));

		echo '<div id="mm_bnlist_multi">';
			echo '<div id="mm_bnlist_multi_wrap">';
			for ( $j = 1; $j <= $no; $j+=1 ) {
				echo '<div id="mm_bnlist_multi_'.$j.'">';
					for ( $i = $j-1; $i < get_option('mm_bnlist_n'); $i+=$no ) {
						echo mm_bnlist_print ('mm_bnlist_multi', $title[$i], $cats_in[$i], $cats_out[$i], $num[$i], $show_rand[$i], $show_date[$i], $show_comments[$i], $show_time[$i]);
					}
				echo '</div>';
			}
			echo "</div>";

			echo mm_bnlist_credits('mm_bnlist_multi', get_option('mm_bnlist_credits'));
		echo "</div>";
	}
}


// Add shortcode
function mm_bnlist_code ($attr) {
	// Read in existing option value from database
	$title = unserialize(get_option('mm_bnlist_title'));
	$num = unserialize(get_option('mm_bnlist_num'));
	$cats_in = unserialize(get_option('mm_bnlist_in'));
	$cats_out = unserialize(get_option('mm_bnlist_out'));

	$show_comments = unserialize(get_option('mm_bnlist_comments'));
	$show_date = unserialize(get_option('mm_bnlist_date'));
	$show_rand = unserialize(get_option('mm_bnlist_rand'));
	$show_time = unserialize(get_option('mm_bnlist_time'));

	$mm_echo = '';
	$mm_echo .= '<div id="mm_bnlist_post">';
		for ( $i = 0; $i < get_option('mm_bnlist_n'); $i+=1 ) {
			$mm_echo .= mm_bnlist_print ('mm_bnlist_post', $title[$i], $cats_in[$i], $cats_out[$i], $num[$i], $show_rand[$i], $show_date[$i], $show_comments[$i], $show_time[$i]);
		}

	$mm_echo .= mm_bnlist_credits('mm_bnlist_post', get_option('mm_bnlist_credits'));
	$mm_echo .= '</div>';

	return $mm_echo;
}

add_shortcode('mm-breaking-news', 'mm_bnlist_code');

// Admin menu

add_action('admin_menu', 'mm_bnlist_menu');

function mm_bnlist_menu() {
	add_options_page('MM Breaking News Lists', 'MM Breaking News', 10, __FILE__, 'mm_bnlist_opt');
}

function mm_bnlist_opt() { 

// variables for the field and option names 
	$opt_name = array(
		'n' =>'mm_bnlist_n',
		'in' => 'mm_bnlist_in',
		'out' => 'mm_bnlist_out',
		'num' => 'mm_bnlist_num',
		'title' => 'mm_bnlist_title',
		'time_format' => 'mm_bnlist_time_format'
	);
	$hidden_field_name = 'mm_bnlist_submit';

// Read in existing option value from database
	$opt_val = array(
		'n' => get_option( $opt_name['n'] ),
		'in' => get_option( $opt_name['in'] ),
		'out' => get_option( $opt_name['out'] ),
		'num' => get_option( $opt_name['num'] ),
		'title' =>  get_option( $opt_name['title'] ),
		'time_format' =>  get_option( $opt_name['time_format'] ),
	);
	if ($opt_val['n'] < 1) $opt_val['n'] = 1;
	if (empty($opt_val['time_format'])) $opt_val['time_format'] = 'M j, Y';
	$only_front = get_option('mm_bnlist_front');
	$show_comments = unserialize(get_option('mm_bnlist_comments'));
	$show_date = unserialize(get_option('mm_bnlist_date'));
	$show_rand = unserialize(get_option('mm_bnlist_rand'));
	$show_credits = get_option('mm_bnlist_credits');
	$show_time = unserialize(get_option('mm_bnlist_time'));

// See if the user has posted us some information
// If they did, this hidden field will be set to 'Y'
	if(isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
	// Read their posted value
        	$opt_val = array(
			'n' => $_POST[ $opt_name['n'] ],
			'time_format' => $_POST[ $opt_name['time_format'] ]
		);
		
// Create array for each field
	$opt_val['title'] = serialize( $_POST[$opt_name['title']] );
	$opt_val['num'] = serialize( $_POST[ $opt_name['num']] );
	$opt_val['in'] = serialize( $_POST[ $opt_name['in']] );
	$opt_val['out'] = serialize( $_POST[ $opt_name['out']] );
	$only_front = $_POST['mm_bnlist_front'];
	$show_comments = $_POST['mm_bnlist_comments'];
	$show_date = $_POST['mm_bnlist_date'];
	$show_rand = $_POST['mm_bnlist_rand'];
	$show_credits = $_POST['mm_bnlist_credits'];
	$show_time = $_POST['mm_bnlist_time'];

        // Save the posted value in the database
		if ($_POST['submit'] == "Add") $opt_val['n'] += 1;
		if ($_POST['submit'] == "Remove") $opt_val['n'] -= 1;
        update_option( $opt_name['n'], $opt_val['n'] );
	update_option( $opt_name['in'], $opt_val['in'] );
	update_option( $opt_name['out'], $opt_val['out'] );
	update_option( $opt_name['num'], $opt_val['num'] );
	update_option( $opt_name['title'], $opt_val['title'] );
	update_option( $opt_name['time_format'], $opt_val['time_format'] );
	update_option('mm_bnlist_front', $only_front);
	update_option('mm_bnlist_comments', serialize($show_comments));
	update_option('mm_bnlist_date', serialize($show_date));
	update_option('mm_bnlist_rand', serialize($show_rand));
	update_option('mm_bnlist_credits', $show_credits);
	update_option('mm_bnlist_time', serialize($show_time));
        // Put an options updated message on the screen
?>
	<div id="message" class="updated fade">
  		<p><strong>Options saved.</strong></p>
	</div>
<?php	} ?>

	<div class="wrap">
	<h2>MM Breaking News Lists</h2>
	<form name="mm_bnlist_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
  		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

		<table class="form-table" style="border-bottom: 1px solid #aaa">
<?php 		
	$opt_tmp['title'] = unserialize($opt_val['title']);
	$opt_tmp['num'] = unserialize($opt_val['num']);
	$opt_tmp['in'] = unserialize($opt_val['in']);
	$opt_tmp['out'] = unserialize($opt_val['out']);	
?> 
			<tr valign="top">
				<th scope="row">Number of lists:</th>
				<td><input type="text" name="<?php echo $opt_name['n']; ?>" value="<?php echo $opt_val['n']; ?>" /></td>

				<th scope="row">Show only on Front page:</th>
				<td><SELECT name="mm_bnlist_front" />
					<?php if ($only_front == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes</option>
					<?php if ($only_front == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No</option>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">Date/time format:</th>
				<td><input type="text" name="<?php echo $opt_name['time_format']; ?>" value="<?php echo $opt_val['time_format']; ?>" /> 
				<br/>Documentation on <a href="http://codex.wordpress.org/Formatting_Date_and_Time">Formating Date and Time</a></td>

				<th scope="row">Show "Plugin by <a href="http://www.svetnauke.org">Svet nauke</a>"</th>
				<td><SELECT name="mm_bnlist_credits" />
					<?php if ($show_credits == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes :)</option>
					<?php if ($show_credits == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No :(</option>
				</td>
			</tr>

<?php $cats = get_categories(); ?>
<?php for ( $i = 0; $i < $opt_val['n']; $i+=1 ) { ?>
			<tr valign="top" style="border-top: 1px solid #aaa">
				<th scope="row">Title <?php echo $i+1 ?>:</th>
				<td><input type="text" name="<?php echo $opt_name['title'] ?>[]" value="<?php echo $opt_tmp['title'][$i]; ?>" /></td>

				<th scope="row">Posts number:</th>
				<td><input type="text" name="<?php echo $opt_name['num'] ?>[]" value="<?php echo $opt_tmp['num'][$i]; ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row">Select categories to include</th>
				<td>
					<SELECT NAME="<?php echo $opt_name['in']."[$i]"; ?>[]" MULTIPLE SIZE=5 style="height: auto">
						<?php 	foreach ($cats as $cat) {
								if (!is_array($opt_tmp['in'][$i])) $opt_tmp['in'][$i] = array();
								if (in_array($cat->cat_ID, $opt_tmp['in'][$i])) $selected = 'selected="selected"';
									else $selected = '';
								echo '<option value="'.$cat->cat_ID.'" '.$selected.'>'.$cat->cat_name."</option>";
							}
						?>
					</SELECT>
				</td>

				<th scope="row">Select categories to exclude</th>
				<td>
					<SELECT NAME="<?php echo $opt_name['out']."[$i]"; ?>[]" MULTIPLE SIZE=5 style="height: auto">
						<?php 	foreach ($cats as $cat) {
								if (!is_array($opt_tmp['out'][$i])) $opt_tmp['out'][$i] = array('');
								if (in_array($cat->cat_ID, $opt_tmp['out'][$i])) $selected = 'selected="selected"';
									else $selected = '';
								echo '<option value="'.$cat->cat_ID.'" '.$selected.'>'.$cat->cat_name."</option>";
							}
						?>
					</SELECT>
				</td>
			</tr>

			<tr>
				<th scope="row">Show number of comments:</th>
				<td><SELECT name="mm_bnlist_comments[]" />
					<?php if ($show_comments[$i] == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes</option>
					<?php if ($show_comments[$i] == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No</option>
				</td>

				<th scope="row">Show post date:</th>
				<td><SELECT name="mm_bnlist_date[]" />
					<?php if ($show_date[$i] == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes</option>
					<?php if ($show_date[$i] == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No</option>
				</td>

				<th scope="row">Randomize posts:</th>
				<td><SELECT name="mm_bnlist_rand[]" />
					<?php if ($show_rand[$i] == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes</option>
					<?php if ($show_rand[$i] == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No</option>
				</td>

				<th scope="row">Show post time (replace bullet list):</th>
				<td><SELECT name="mm_bnlist_time[]" />
					<?php if ($show_time[$i] == "YES") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="YES" <?php echo $opt_select ?> >Yes</option>
					<?php if ($show_time[$i] == "NO") $opt_select = 'selected="selected"'; else $opt_select =""; ?>
					<option value="NO" <?php echo $opt_select ?> >No</option>
				</td>
			</tr>
<?php } ?>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<input type="submit" name="submit" value="Add" />
			<input type="submit" name="submit" value="Remove" />
		</p>
	</form>
	</div>

<?php } ?>
