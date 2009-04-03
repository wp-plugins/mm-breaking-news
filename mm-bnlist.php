<?php
/*
Plugin Name: MM-Breaking News
Plugin URI: http://www.mmilan.com/mm-breaking-news
Description: Displays lists of posts from selected categories whereever you like. You can select how many different lists you want, sort posts by date or random, select which categories to include or exclude from specific list.
Author: Milan Milosevic
Author URI: http://www.mmilan.com/
Version: 0.5.2
License: GPL v3 - http://www.gnu.org/licenses/

Installation: You have to add <?php if (function_exists('mm_bnlist')) mm_bnlist() ?> to your theme file.

    Copyright 2009  Milan Milosevic  (email : mm@mmilan.com)

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

function mm_bnlist_css() {

	$pluginURL = 'wp-content/plugins/';
	$pluginName = 'mm-breaking-news';
	
	$cssUrl = get_bloginfo('url')."/".$pluginURL.$pluginName.'/mm-bnlist.css';	
	echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />';
}

add_action('wp_head', 'mm_bnlist_css');


function mm_bnlist () {

	if ( (is_front_page()) or (get_option('mm_bnlist_front') == "NO")) {
// Read in existing option value from database
	$opt_name = array(
		'n' =>'mm_bnlist_n',
		'in' => 'mm_bnlist_in',
		'out' => 'mm_bnlist_out',
		'num' => 'mm_bnlist_num',
		'title' => 'mm_bnlist_title',
	);
		$opt_val = array(
			'n' => get_option( $opt_name['n'] ),
			'in' => get_option( $opt_name['in'] ),
			'out' => get_option( $opt_name['out'] ),
			'num' => get_option( $opt_name['num'] ),
			'title' =>  get_option( $opt_name['title'] ),
		);
		if ($opt_val['n'] < 1) $opt_val['n'] = 1;

		$opt_tmp['title'] = unserialize($opt_val['title']);
		$opt_tmp['num'] = unserialize($opt_val['num']);
		$opt_tmp['in'] = unserialize($opt_val['in']);
		$opt_tmp['out'] = unserialize($opt_val['out']);
		$show_comments = unserialize(get_option('mm_bnlist_comments'));
		$show_date = unserialize(get_option('mm_bnlist_date'));
		$show_rand = unserialize(get_option('mm_bnlist_rand'));

		echo "<div  id=\"mm_bnlist\">";
		for ( $i = 0; $i < $opt_val['n']; $i+=1 ) {
			echo "<b>".$opt_tmp['title'][$i]."</b>";
			echo "<ul>";
				global $post;
				$catid = Array();
				if (!empty($opt_tmp['in'][$i])) foreach ($opt_tmp['in'][$i] as $tmp) $catid[] = $tmp;
				if (!empty($opt_tmp['out'][$i])) foreach ($opt_tmp['out'][$i] as $tmp) $catid[] = -$tmp;
				$catids = implode(',', $catid);

				$num = $opt_tmp['num'][$i];
				if ($show_rand[$i] == "YES") $myposts = get_posts("numberposts=$num&category=$catids&orderby=rand");
					else $myposts = get_posts("numberposts=$num&category=$catids");
				foreach($myposts as $post) :
					setup_postdata($post);
					if ($show_date[$i] == "YES") $sh_date = the_date('M dS, Y', ' (', '', FALSE);
						else $sh_date = '';
					if ($show_comments[$i] == "YES") $no_com = "".$post->comment_count." comments)";
						else $no_com = '';
					if (($show_date[$i] == "YES") and ($show_comments[$i] == "YES")) $sep = "; ";
						else if ($show_date[$i] == "YES") $sep = ")";
							else $sep = " (";
					if (($show_date[$i] != "YES") and ($show_comments[$i] != "YES")) $sep = "";
					print "<li><a href=\"".get_permalink()."\">".$post->post_title."</a><span class=\"date_com\">".$sh_date.$sep.$no_com."</span></li>";
				endforeach;
			echo "</ul>";
		}

		if (get_option('mm_bnlist_credits') != "NO")
			echo "<p class=\"credits\">Plugin by <a href=\"http://www.mmilan.com\">mmilan.com</a></p>";
		echo "</div>";
	}
}

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
	);
	$hidden_field_name = 'mm_bnlist_submit';

// Read in existing option value from database
	$opt_val = array(
		'n' => get_option( $opt_name['n'] ),
		'in' => get_option( $opt_name['in'] ),
		'out' => get_option( $opt_name['out'] ),
		'num' => get_option( $opt_name['num'] ),
		'title' =>  get_option( $opt_name['title'] ),
	);
	if ($opt_val['n'] < 1) $opt_val['n'] = 1;
	$only_front = get_option('mm_bnlist_front');
	$show_comments = unserialize(get_option('mm_bnlist_comments'));
	$show_date = unserialize(get_option('mm_bnlist_date'));
	$show_rand = unserialize(get_option('mm_bnlist_rand'));
	$show_credits = get_option('mm_bnlist_credits');

// See if the user has posted us some information
// If they did, this hidden field will be set to 'Y'
	if(isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
	// Read their posted value
        	$opt_val = array(
			'n' => $_POST[ $opt_name['n'] ],
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

        // Save the posted value in the database
		if ($_POST['submit'] == "Add") $opt_val['n'] += 1;
		if ($_POST['submit'] == "Remove") $opt_val['n'] -= 1;
        update_option( $opt_name['n'], $opt_val['n'] );
	update_option( $opt_name['in'], $opt_val['in'] );
	update_option( $opt_name['out'], $opt_val['out'] );
	update_option( $opt_name['num'], $opt_val['num'] );
	update_option( $opt_name['title'], $opt_val['title'] );
	update_option( 'mm_bnlist_front', $only_front);
	update_option('mm_bnlist_comments', serialize($show_comments));
	update_option('mm_bnlist_date', serialize($show_date));
	update_option('mm_bnlist_rand', serialize($show_rand));
	update_option('mm_bnlist_credits', $show_credits);
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

				<th scope="row">Show "Plugin by <a href="http://www.mmilan.com">mmilan.com</a>"</th>
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
