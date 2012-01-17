<?php


/**
 * Returns the users unread items in Hypernews!
 * Using cache for 1 min.
 * @global type $current_user
 * @global type $wpdb
 * @return type 
 */
function hypernews_getunread_news(){
    global $current_user;
    global $wpdb;

    $cache = get_transient( 'hypernews_cache_unread' );
    if (!$cache){
        get_currentuserinfo();    

        $fetch = new Hypernews_Fetcher();
        $fetch->fetch();

        $cache = 0;
        $channel = get_user_meta($current_user->ID, "hypernews_channel");
        if (sizeof($channel)>0) $channel = $channel[0];    

        $table_name = $wpdb->prefix . "hypernews_store";
        $query = "SELECT count(*) FROM ".$table_name." WHERE status='NEW'";
        if (strlen($channel)>0){
            $query.=" AND channel='".$channel."'";
        }
        $cache = $wpdb->get_var( $wpdb->prepare( $query ) );

        set_transient( 'hypernews_cache_unread', $cache, 60 );
        
    }
    
    return $cache;
}


?>
