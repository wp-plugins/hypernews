<?php
/*
Plugin Name: Hypernews
Plugin URI: http://wordpress.org/extend/plugins/hypernews
Description: Editorial support, very fast user interface to manually select and publish RSS streams to your WordPress site/blog.
Version: 0.6
Author: Hypernode AB
Author URI: http://www.hypernode.se
License: MIT AND ...
*/

/**
 * Localize plugin
*/
load_plugin_textdomain( 'hypernews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    
include_once 'hypernews_settings.php';
include_once(ABSPATH . WPINC . '/feed.php');
include_once('hypernews_installer.php');
include_once('hypernews_fetcher.php');
include_once('hypernews_list.php');
include_once('hypernews_ajax.php');
include_once('hypernews_links.php');
include_once('hypernews_browse.php');
include_once('functions.php');
include_once 'hypernews_metabox.php';

register_activation_hook( __FILE__, array( 'HypernewsInstall', 'install' ) );

$metabox = new Hypernews_Metabox();

add_action('admin_menu', 'hn_add_menu');
function hn_add_menu()
{
    global $current_user;

    $unread = hypernews_getunread_news();
    if ($unread>0){
        $unread_text = '&nbsp;<span style="background-color:black;color:white;margin:2px;padding:2px;font-weight:normal;-moz-border-radius:15px;-webkit-border-radius:15px;">&nbsp;'.$unread.'&nbsp;</span>';
    }
    add_menu_page( 'Hypernews', 'Hypernews'.$unread_text, 'edit_posts', 'hypernews', 'hypernews_main', WP_PLUGIN_URL.'/hypernews/img/feed_add_16.png' );
    add_submenu_page( 'hypernews', 'Hypernews Links', __('RSS Feeds', 'hypernews'), 'edit_posts', 'hypernews_links', 'hypernews_links' );
    add_submenu_page( 'hypernews', 'Hypernews Browse', __('Browse List', 'hypernews'), 'edit_posts', 'hypernews_browse', 'hypernews_browse' );
    //add_submenu_page( 'hypernews', 'Hypernews Settings', __('Settings', 'hypernews'), 'edit_posts', 'hypernews_settings', 'hypernews_settings' );
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

add_action('admin_enqueue_scripts', 'hypernews_script');
function hypernews_script($hook) {
    
    if (strpos($hook, 'page_hypernews')){
        wp_enqueue_script("jquery");
        wp_enqueue_script("jquery-ui-core");

        //wp_enqueue_script("jquery-ui-effects");
        //We really need the effects in jquery-ui!
        wp_enqueue_script("jquery-ui","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js");

        $src = WP_PLUGIN_URL . '/hypernews/js/hypernews.js';
        wp_deregister_script('hypernewsAjax');
        wp_register_script('hypernewsAjax', $src);
        wp_enqueue_script('hypernewsAjax');
        wp_localize_script('hypernewsAjax','hypernewsAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}

function hypernews_main() 
{
    echo '<div class="wrap">';
    echo '<div id="" class="icon32"><img src="'.WP_PLUGIN_URL . '/hypernews/img/feed_add_32.png" alt="hypernews_icon" /><br/></div>';
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