<?php
/*
Plugin Name:    WP Citation
Plugin URI:     www.wpcitation.com
Description:    Provides readers a way to copy/paste citation of your articles, pages, or blog posts.
Version:        1.0
Author:         Matthew Mansfield
Author URI:     https://profiles.wordpress.org/mmansfi3/
*/

// Localization / Internationalization
// See:
// - https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
// - https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
load_plugin_textdomain('wpcitation', false, dirname(plugin_basename(__FILE__)) . '/languages/');


// Default settings
// See: 
// - https://developer.wordpress.org/reference/functions/apply_filters/
$wpcp_default = apply_filters('wpcp_default_setting', array(
    'setting' => __('Reference this work as:<br><br>
<!-- The text field -->
<div id="div1">{author}, &quot;{title},&quot; in <em>{sitename}</em>, {publication_date}, {permalink}.</div>')
        ));


// Pulls the default settings from DB + Fallback
// See: 
// - https://developer.wordpress.org/reference/functions/get_option/
// - https://codex.wordpress.org/Function_Reference/wp_parse_args
$wpcp_setting = wp_parse_args(get_option('wpcp_setting'), $wpcp_default);


// Registers the settings in DB
// See: https://developer.wordpress.org/reference/functions/register_setting/
add_action('admin_init', 'wpcp_register_setting');

function wpcp_register_setting() {
    register_setting('wpcp_setting', 'wpcp_setting');
}

// Adding settings page in wp menu
// See: 
// - https://developer.wordpress.org/reference/functions/add_action/
add_action('admin_menu', 'wpcp_setting_menu');

function wpcp_setting_menu() {
    add_menu_page(__('WP Citation Settings', 'wpcitation'), __('WP Citation', 'wpcitation'), 'manage_options', 'wp-citation', 'wpcp_setting_page', plugin_dir_url(__FILE__) . 'wpcitation-icon.png', 55);
}

// Check whether settings have been updated; if yes, display "updated" message.
function wpcp_setting_update_check() {
    global $wpcp_setting;
    if (isset($wpcp_setting['update'])) {
        echo '<div style="margin-top:20px;" class="updated fade" id="message"><p>' . __('WP Citation Settings', 'wpcitation') . ' <strong>' . $wpcp_setting['update'] . '</strong></p></div>';
        unset($wpcp_setting['update']);
        update_option('wpcp_setting', $wpcp_setting);
    }
}

// Display admin page
function wpcp_setting_page() {
    echo '<div class="wrap">';

    //	The wpcitation adding form
    wpcp_admin();

    echo '</div>';
}

// Admin page
function wpcp_admin() {
    ?>
    <?php wpcp_setting_update_check(); ?>
    <form method="post" action="options.php">
        <?php settings_fields('wpcp_setting'); ?>
        <?php global $wpcp_setting; ?>
        <div class="wpcp-admin">
            <h2><?php _e('WP Citation Settings', 'wpcitation') ?></h2>
            <p><?php _e('Provides readers a way to copy/paste citation of your articles, pages, or blog posts. Simply, insert the following tag at the bottom of your content:', 'wpcitation') ?> <br><code>[wpcitation]</code><br><br>The code in the textbox (below) is only a suggestion. You may change your citation area to whatever you wish. </p>
            <p><textarea cols="35" rows="28" name="wpcp_setting[setting]" id="wpcp_setting[setting]" class="wpcp-textarea"><?php echo $wpcp_setting['setting']; ?></textarea></p>
            <p class="wpcp-templates-info"><span><?php _e('Citation Tags Options:', 'wpcitation') ?></span><br>
              <strong>{author}</strong> - <?php _e('the post/page author','wpcitation') ?><br>
              <strong>{title}</strong> - <?php _e('the title of your post/page', 'wpcitation') ?><br>
              <strong>{sitename}</strong> - <?php _e('your site name taken from Settings > General', 'wpcitation') ?><br>
              <strong>{publication_date}</strong> - <?php _e('date the page/post was published', 'wpcitation') ?><br>
              <strong>{permalink}</strong> - <?php _e('the permalink of the page/post being accessed', 'wpcitation') ?><br>
              <strong>{date}</strong> - <?php _e('the current date, if "date accessed" is desired', 'wpcitation') ?><br>
              <?php _e('The content in the textbox (above) is what will be displayed in the WP Citation area of your website.', 'wpcitation') ?></p>
              <p class="wpcp-templates-info"><span><?php _e('Helpful Citation Ideas', 'wpcitation') ?></span> (<?php _e('similar to', 'wpcitation') ?> <a href="https://owl.purdue.edu/owl/purdue_owl.html" target="_blank"><?php _e('Owl Purdue Citation Format', 'wpcitation') ?></a>):<br>
              <?php _e('Blog post:', 'wpcitation') ?> {author}, "{title}," {sitename}, {publication_date}, {permalink}.<br>
              <?php _e('Book chapter:', 'wpcitation') ?> {author}, "{title}," in {sitename}, ed. Jack Dougherty (Ann Arbor: Michigan Publishing, 2014), {permalink}.</p>
            <input type="hidden" name="wpcp_setting[update]" value="<?php _e('UPDATED', 'wpcitation') ?>" />
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wpcitation') ?>" />
    </form>
    </div>
    <?php
}

// Registering shortcode [wpcitation]
add_shortcode('wpcitation', 'wpcitation_shortcode');

function wpcitation_shortcode() {
    global $wpcp_setting;

    // Getting admin preferred date format for current date
	if(!function_exists('displayTodaysDate')){
    function displayTodaysDate() {
        return date_i18n(get_option('date_format'));
	}
	}

    $find_string = array('{author}','{sitename}', '{title}', '{date}', '{publication_date}', '{permalink}');
    $replace_string = array(get_the_author(), get_bloginfo('name'), get_the_title(), displayTodaysDate(), get_the_date(), '<a href="' . get_permalink() . '">' . get_permalink() . '</a>');
    $edited_setting = str_replace($find_string, $replace_string, $wpcp_setting['setting']);
    return '<div class="wpcp">' . $edited_setting . '</div>';
}

// Adding formatting
add_action('wp_head', 'wpcp_head');

function wpcp_head() {
    ?>
    <style type="text/css">
        .wpcp {background: #f7f7f7; padding: 16px 20px; border-radius: 5px; line-height: 20px;}
    </style>
    <?php
}

add_action('admin_head', 'wpcp_admin_head');

function wpcp_admin_head() {
    ?>
    <style type="text/css">
        .wpcp-admin {width: 700px;}
        .wpcp-textarea {width: 100%; font-family: courier;}
        .wpcp-templates-info {margin: 0 0 40px;}
        .wpcp-templates-info span {display: inline-block; margin-bottom: 5px; font-weight: bold;}
    </style>
    <?php
}