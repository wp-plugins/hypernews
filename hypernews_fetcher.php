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

        $settings = new Hypernews_Settings();
        $links = $settings->links();

        //Remove if channel choosen
        if (strlen($channel)>0){
            foreach ($links as $key => $value) {
                if ($value['channel']!=$channel){
                    unset($links[$key]);
                }
            }
        }
        
        $reload = false;
        
        // Loop through each bookmark and print formatted output
        foreach ( $links as $bm ) 
        { 
            $items = $this->get_items($bm);

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
                            'link_id' => $bm['id'],
                            'channel' => $bm['channel'],
                            'source' => $bm['source'],
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
    
    public function get_items($link){

        //initial
        $result = array(
            'match'=>array(),
            'mismatch'=>array(),
            'original'=>array()
        );
        
        $rss = fetch_feed($link['url']);
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
                if ($link['maxage']>0){
                    $date = date('Y-m-d H:i:s', strtotime($today . " -".$link['maxage']." hours"));
                    if ($item->get_date('Y-m-d H:i:s') < $date){
                        $found_search = false;
                    }
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