<?php

/**
 Plugin Name: GIT Followers WP Plugin
 Description: Show GIT followers count and list with avatars.
 Author: MIA
 Version: 1.0.0
 Author URI: http://datumsquare.com/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'GITFWPP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-git-followers-wp-plugin-activator.php
 */
function activate_GIT_Followers_WP_Plugin() {
		add_option('gitfwpp_account_name','');
		
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-git-followers-wp-plugin-deactivator.php
 */
function deactivate_GIT_Followers_WP_Plugin() {
		delete_option('gitfwpp_account_name');
}
register_activation_hook( __FILE__, 'activate_GIT_Followers_WP_Plugin' );
register_deactivation_hook( __FILE__, 'deactivate_GIT_Followers_WP_Plugin' );

 
function gitfwpp_init(){ 
	add_filter( 'widget_title', 'do_shortcode' );
	add_filter('widget_text','do_shortcode');
	add_shortcode( 'gitfwpp_counter', 'gitfwpp_counter_func' ); 
	add_shortcode( 'gitfwpp_list', 'gitfwpp_list_func' ); 
}
add_action("init","gitfwpp_init");

function gitfwpp_options_page() {
 // add top level menu page
 add_menu_page(
 'GIT Followers',
 'GIT Followers',
 'manage_options',
 'gitfwpp',
 'gitfwpp_options_page_html'
 );
}
add_action( 'admin_menu', 'gitfwpp_options_page' );

function gitfwpp_options_page_html() {
	 // check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
	return;
	}
	?>
	<div class="wrap">
	<h1>GIT Followers</h1>
	<?php
		if($_POST)
		{
			$acc_n = trim($_POST['git_account_id']);
			if(empty($acc_n))
			{
				echo '<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible"> 
<p><strong>Unable to save settings.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
			else
			{
				if(update_option('gitfwpp_account_name' , $acc_n))
					echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
<p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}
		}
		$options = get_option( 'gitfwpp_account_name' );
	?>
	<form action="" method="post">
		<table class="form-table">
			<tbody><tr>
			<th scope="row">How to use?</th><td>You can use two different shortcodes<br>[gitfwpp_counter]<br>[gitfwpp_list avatar="yes" include_styles="no"]</td></tr>
			<tr><tr>
			<th scope="row">&nbsp;</th><td>&nbsp;</td></tr>
			<tr>
			<th scope="row"><label for="git_account_id">GIT Account</label></th>
			<td><input name="git_account_id" id="git_account_id" required="" value="<?php echo $options; ?>" class="regular-text" type="text"></td>
			</tr>
			</tbody>
		</table>
		<p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit"></p>
	</form>
	</div>
	<?php
} 

function gitfwpp_counter_func( $atts ) {
	$gitacc = get_option( 'gitfwpp_account_name' );
   	$response = wp_remote_get( 'https://api.github.com/users/'.$gitacc );
   	$body = wp_remote_retrieve_body( $response );
   	$body_array = json_decode($body, TRUE); 
   	return $body_array['followers'];
   //	var_dump($body);
 }
function gitfwpp_list_func( $atts ) {
	 
	extract(shortcode_atts(array(
	  'avatar' => 'yes',	
	  'include_styles' => 'yes',	
	), $atts));
	//echo $avatar;
	//var_dump($atts);
   $gitacc = get_option( 'gitfwpp_account_name' );
   $response = wp_remote_get( 'https://api.github.com/users/'.$gitacc.'/followers' );
   $body = wp_remote_retrieve_body( $response );
   	$body_array = json_decode($body, TRUE); 
   //echo '<pre>';
   //var_dump($body_array);

   $followes_list='';

   foreach ($body_array as $follower) {
   	$followes_list .= '<li id="'.$follower['id'].'"><a href="'.$follower['html_url'].'" target="_blank">';
   	if($avatar == 'yes') 
   		$followes_list .= '<span class="follower_avatar"><img src="'.$follower['avatar_url'].'" alt="No-Avatar" width="50" height="auto"></span>';
   	$followes_list .='<span class="follower_title">'.$follower['login'].'</span></a></li>';
   }
    $followes_list='<ul id="gitfwpp">'. $followes_list.'</ul>';
    if($include_styles == 'yes')
    {
    	$followes_list .= '
    	<style>
    		#gitfwpp{list-style-type: none;margin: 0px;padding: 0px;}
    		#gitfwpp li{padding: 0.5em 0;float: left;display: block;width: 100%;}
    		#gitfwpp li a:hover{text-decoration:none;}
    		#gitfwpp li .follower_avatar{float:left}
    		#gitfwpp li .follower_avatar img{border-radius: 50%;}
    		#gitfwpp li .follower_title{margin-left:1em;line-height: 50px;}
    	</style>';
    }
    return $followes_list;
 }
?>