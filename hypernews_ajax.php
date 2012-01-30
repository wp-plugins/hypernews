<?php

add_action('wp_ajax_hypernews_update_status', 'hypernews_update_status');

function hypernews_update_status()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "hypernews_store";
    
    // Error reporting
    error_reporting(E_ALL^E_NOTICE);
    // Validating the input data:
    if(!is_numeric($_GET['id']))
    {
        die("0");
    }
    
    // Escaping:
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    
    
    $wpdb->query( $wpdb->prepare( 
	"
		UPDATE $table_name
                                    SET status = %s
                                    WHERE id = %d;
	", 
	$status, 
	$id 
        ) );
    
    echo "1";
}


add_action('wp_ajax_hypernews_update_note', 'hypernews_update_note');
function hypernews_update_note()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "hypernews_store";
    
    // Error reporting
    error_reporting(E_ALL^E_NOTICE);
    // Validating the input data:
    if(!is_numeric($_GET['id']))
    {
        die("0");
    }
    
    // Escaping:
    $id = (int)$_GET['id'];
    $note = $_GET['note'];
    
    
    $wpdb->query( $wpdb->prepare( 
	"
		UPDATE $table_name
                                    SET notes = %s
                                    WHERE id = %d;
	", 
	$note, 
	$id 
        ) );
    
    echo "1";
}


add_action('wp_ajax_hypernews_publish', 'hypernews_publish');
function hypernews_publish()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "hypernews_store";
    
    // Error reporting
    error_reporting(E_ALL^E_NOTICE);
    // Validating the input data:
    if(!is_numeric($_GET['id']))
    {
        die("0");
    }
    
    // Escaping:
    $id = (int)$_GET['id'];
    $posttype = $_GET['posttype'];
    
    $row = $wpdb->get_row("SELECT * FROM $table_name WHERE id = ".$id);
    
    if (is_null($row))
    {
        die("0");
    }

    $settings = new Hypernews_Settings();
    $link = $settings->get_link($row->link_id);
    
    //Mark text with overflow!
    $text = strip_tags($row->description);
    
    $remove_chars = $link['removechars'];
    if ($remove_chars>0 && strlen($text) > $remove_chars){
        $text = substr($text, 0, $remove_chars);
    }
    
    $max_chars = $link['maxchars'];
    if ($max_chars>0 && strlen($text) > $max_chars)
    {
        $text = substr($text, 0, $max_chars) . '<del>' . substr($text, $max_chars) . '</del>';
    }
    
    //Create new post
    global $user_ID;
    $new_post = array(
    'post_title' => $row->title,
    'post_content' => $text,
    'post_status' => 'draft',
    'post_date' => $row->pubdate,
    'post_author' => $user_ID,
    'post_type' => $posttype
    );
    $post_id = wp_insert_post($new_post);
    
    $meta = array();
    $meta['url'] = $row->url;
    $meta['title'] = $row->title;
    $meta['date'] = $row->pubdate;
    $meta['author'] = $user_ID;

    //SLÅ UPP link_id och hämta namnet på källan!
    if ($row->link_id){
        $meta['link_id'] = $link['id'];
        $meta['source'] = $link['source'];
        $meta['feed'] = $link['url'];
        $meta['channel'] = $link['channel'];
        add_post_meta($post_id, 'hypernews_metabox', $meta, true);
    }
    
    $url = esc_url(get_permalink( $post_id ));

    $sql = sprintf("
                UPDATE $table_name
                                    SET post = %d,
                                    posturl = '%s',
                                    status = '%s'
                                    WHERE id = %d;
        ", 
        $post_id,
        $url,
        'POST', 
        $id 
    );

    $result = $wpdb->query($sql);
    
    if (!$result)
    {
        die('0');
    }
    
    echo "result 1 OK!";
}

?>
