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
    
    //Mark text with overflow!
    $text = strip_tags($row->description);
    if (strlen($text) >  hypernews_maxchars())
    {
        $text = substr($text, 0, hypernews_maxchars()) . '<span style="BACKGROUND-COLOR: yellow">' . substr($text, hypernews_maxchars()) . '</span>';
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
    
    add_post_meta($post_id, 'source_link', $row->link, true);
    add_post_meta($post_id, 'source_name', $row->source, true);
    add_post_meta($post_id, 'hypernews_id', $row->id, true);
    
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
    
    $result = $wpdb->query( $wpdb->prepare( $sql ) );
    var_dump($sql);
        
    if (!$result)
    {
        die('0');
    }
    
    echo "result 1 OK!";
}

?>
