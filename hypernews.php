<?php
/*
Plugin Name: Hypernews
Plugin URI: http://wordpress.org/extend/plugins/hypernews
Description: Feedreader
Version: 0.2
Author: Hypernode AB
Author URI: http://www.hypernode.se
License: MIT
*/
include_once(ABSPATH . WPINC . '/feed.php');

include_once('settings.php');
include_once('installer.php');
include_once('fetcher.php');
include_once('list.php');
include_once('ajax.php');

register_activation_hook( __FILE__, array( 'HypernewsInstall', 'install' ) );

add_action('admin_menu', 'hn_add_menu');
function hn_add_menu(){
    add_menu_page( 'Hypernews', 'Hypernews', 'edit_posts', 'hypernews', 'hypernews_main', WP_PLUGIN_URL.'/hypernews/img/feed.png' );
    add_submenu_page( 'hypernews', 'Hypernews Settings', 'Inställningar', 'edit_posts', 'hypernews_settings', 'hypernews_settings' );
    
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
#add_action('wp_ajax_hypernews_set_group', ajax_set_group);
#add_action('wp_ajax_hypernews_get_group', ajax_get_group);

/*
 *  Add Ajax into our setting page
 */
function hypernews_script() {

    $src = WP_PLUGIN_URL . '/hypernews/js/hypernews.js';
    wp_deregister_script('hypernewsAjax');
    wp_register_script('hypernewsAjax', $src);
    wp_enqueue_script('hypernewsAjax');
    wp_localize_script('hypernewsAjax','hypernewsAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    
    wp_enqueue_script("myUi","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js");
    //wp_enqueue_script('jquery-ui-core');
    
}



function hypernews_main() 
{
    echo '<div class="wrap">';
    
    
    echo '<form method="post">';
    
    //Prepare Table of elements
    $rss_list = new Hypernews_List();
    $rss_list->prepare_items();
    $rss_list->display();
    
    echo '</form>';
 
    
    echo '</div>';
}

?>