<?php
/*
Plugin Name: Hypernews
Plugin URI: http://wordpress.org/extend/plugins/hypernews
Description: Feedreader
Version: 0.2.4
Author: Hypernode AB
Author URI: http://www.hypernode.se
License: MIT
*/

/**
 * Localize plugin
*/
load_plugin_textdomain( 'hypernews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    
include_once(ABSPATH . WPINC . '/feed.php');
include_once('hypernews_settings.php');
include_once('hypernews_installer.php');
include_once('hypernews_fetcher.php');
include_once('hypernews_list.php');
include_once('hypernews_ajax.php');

register_activation_hook( __FILE__, array( 'HypernewsInstall', 'install' ) );
add_action('admin_menu', 'hn_add_menu');
function hn_add_menu()
{
    global $current_user;
    add_menu_page( 'Hypernews', 'Hypernews', 'edit_posts', 'hypernews', 'hypernews_main', WP_PLUGIN_URL.'/hypernews/img/feed.png' );
    add_submenu_page( 'hypernews', 'Hypernews Settings', __('Settings', 'hypernews'), 'edit_posts', 'hypernews_settings', 'hypernews_settings' );
}

/*
 * REGISTER HEADER
 * Javascript, CSS
 */
add_action('admin_head', 'admin_register_head');
function admin_register_head() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/css/hypernews.css';
    $js_url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/js/hypernews.js';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
//    echo "<script type='text/javascript' src='$js_url'></script>";
    
}

add_action('admin_init', 'hypernews_script');
function hypernews_script() {

    $src = WP_PLUGIN_URL . '/hypernews/js/hypernews.js';
    wp_deregister_script('hypernewsAjax');
    wp_register_script('hypernewsAjax', $src);
    wp_enqueue_script('hypernewsAjax');
    wp_localize_script('hypernewsAjax','hypernewsAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    
    wp_enqueue_script("jquery-ui","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js");
}

function hypernews_main() 
{
    echo '<div class="wrap">';
    
    echo '<h2>Hypernews</h2>';
    echo '<form method="post">';
    
    //Prepare Table of elements
    $rss_list = new Hypernews_List();
    $rss_list->prepare_items();
    $rss_list->display();
    
    echo '</form>';
 
    
    echo '</div>';
}

?>