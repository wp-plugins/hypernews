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
        
        $result = '';
        
        $hypernews_settings = get_option( 'hypernews-settings');
        $cat = $hypernews_settings['category'];
        
        $bookmarks = get_bookmarks( array(
            'category_name'  => $cat
            ));

        $reload = false;
        
        // Loop through each bookmark and print formatted output
        foreach ( $bookmarks as $bm ) 
        { 
            //printf( '<a class="relatedlink" href="%s">%s</a><br />', $bm->link_url, __($bm->link_name) );
            $rss = fetch_feed($bm->link_url);
            if (!is_wp_error( $rss ) )
            {
                $maxitems = $rss->get_item_quantity(999); 
                $rss_items = $rss->get_items(0, $maxitems); 
                foreach ( $rss_items as $item )
                {
                    $sql = 'SELECT * FROM '.$this->tablename().' WHERE guid="'.$item->get_id().'"';
                    $find_item = $wpdb->get_row($sql);
                    if (!$find_item)
                    {
                        $wpdb->insert( 
                            $this->tablename(), 
                            array( 
                                'title' => $item->get_title(), 
                                'link' => $item->get_link(),
                                'source' => $bm->link_name,
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
                                '%s' 
                            ) 
                        );
                    }
                    //esc_url( $item->get_permalink() );
                    //$item->get_date('j F Y | g:i a');
                    //esc_html( $item->get_title() );
                }

                $reload = true;
                
                $result.= $bm->link_name.' => '.__('Loaded', 'hypernews').'<br/>';
            }
            else
            {
                $result .= $bm->link_name.' => '.$rss->get_error_message() .'<br/>';
            }
        }

        if ($reload)
        {
            //echo "<meta http-equiv='refresh' content='0'>";
            //exit;
        }
        
        echo '<div id="message" class="updated">' . $result . '</div>';
        
        
    }
}

?>