<?php

class Hypernews_Fetcher
{
    function tablename()
    {
        global $wpdb;
        return $wpdb->prefix . "hypernews_store";
    }

    function fetch()
    {
        global $wpdb;
        global $current_user; get_currentuserinfo(); // get current user info
        
        $channel = get_user_meta($current_user->ID, "hypernews_channel");
        if (sizeof($channel)>0) $channel = $channel[0];
            
        $table_name = $wpdb->prefix . "hypernews_links";
        $sql = 'SELECT * FROM '.$table_name.' WHERE type=\'RSS\''; 
        
        if (strlen($channel)>0){
            $sql.=" AND channel='".$channel."'";
        }

        $sql.= ' ORDER BY sort_order';
        $bookmarks = $wpdb->get_results($sql); 

        $reload = false;
        
        // Loop through each bookmark and print formatted output
        foreach ( $bookmarks as $bm ) 
        { 
            $items = $this->get_items($bm->url, $bm->search);

            foreach ($items['match'] as $key => $item) {
                $sql = 'SELECT * FROM '.$this->tablename().' WHERE guid="'.$item->get_id().'"';
                $find_item = $wpdb->get_row($sql);
                if (!$find_item)
                {
                    $wpdb->insert( 
                        $this->tablename(), 
                        array( 
                            'title' => $item->get_title(), 
                            'url' => $item->get_link(),
                            'link_id' => $bm->id,
                            'channel' => $bm->channel,
                            'description' => $item->get_description(),
                            'pubdate' => $item->get_date('Y-m-d H:i:s'),
                            'guid' => $item->get_id(),
                            'status' => 'NEW'
                        ), 
                        array( 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s' 
                        ) 
                    );
                }
            }
        }

        if ($reload)
        {
            $sql = "
            DELETE FROM ".$this->tablename()." 
            WHERE pubdate < DATE_SUB(NOW(), INTERVAL " .  hypernews_maxage() . " HOUR) 
            ; /*AND status!='NEW';*/";

            $wpdb->query( $sql );
        }
        
    }
    
    public function get_items($url, $arg_searchwords=""){

        $result = array();
        
        $rss = fetch_feed($url);
        if (!is_wp_error( $rss ) )
        {
            $maxitems = $rss->get_item_quantity(999); 
            $rss_items = $rss->get_items(0, $maxitems); 
            foreach ( $rss_items as $item )
            {
                $found_search = false;

                //Check with search words in links
                $search = $arg_searchwords;
                $search_words = array();
                if (strpos($search,',')){
                    $search_words = explode(',', $search);
                }
                else
                {
                    if (strlen($search)>0){
                        $search_words[] = trim($search);
                    }
                    else
                    {
                        $found_search = true; //Always add if no search words added!
                    }
                }

                $title = utf8_encode(strtolower($item->get_title()));
                $body = utf8_encode(strtolower($item->get_description()));

                foreach ($search_words as $key => $value) {
                    $value = trim(utf8_encode(strtolower($value)));
                    if (strpos($title,$value)){
                        $found_search = true;
                        break;
                    }

                    if (strpos($body,$value)){
                        $found_search = true;
                        break;
                    }
                }

                //Check if to old!
                $date = date('Y-m-d H:i:s', strtotime($today . " -".hypernews_maxage()." hours"));
                if ($item->get_date('Y-m-d H:i:s') < $date){
                    $found_search = false;
                }

                if ($found_search) {
                    $result['match'][] = $item;
                }
                else{
                    $result['mismatch'][] = $item;
                }
                $result['original'][] = $item;
            }
        }
        else
        {
            $result['error'] = $rss->get_error_message();
        }        
        
        return $result;
    }
    
    
}

?>