<?php

if(!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * 
 */
class Hypernews_List extends WP_List_Table 
{
     function __construct() 
    {
         $this->hidden = false;
         
         parent::__construct( array(
        'singular'=> 'News', //Singular label
        'plural' => 'News', //plural label, also this well be one of the table css class
        'ajax'	=> true //We won't support Ajax for this table
        ) );
     }
     
     public function reload_page(){
        set_transient( 'hypernews_cache_unread', NULL);
        echo __('Fetching items from RSS-feeds...','hypernews').'<p><img src="'.WP_PLUGIN_URL.'/hypernews/img/ajax-loader.gif"></p><script> setTimeout(\'document.location="?page=hypernews";\',10);</script>'; 
        wp_die(__('...please wait while loading news and reloading page!','hypernews'));
     }
     
     function extra_tablenav( $which ) 
     {
        global $current_user;
        get_currentuserinfo();
        if ( $which == "top" )
            {
                $channel = get_user_meta($current_user->ID, "hypernews_channel");
                if (sizeof($channel)>0) $channel = $channel[0];
                if (strlen($channel)>0){
                    echo '<div style="display:inline-block; padding-top:7px">'.__('Filter news on channel:','hypernews').' <strong>'.$channel.'</strong></div>';
                }
            
                if ( isset( $_REQUEST['fetch'] ) )
                {
                    $this->reload_page();
                }
            }
            if ( $which == "bottom" ){
                    //The code that goes after the table is there
                    echo " ";
            }
    }
    
    function get_bulk_actions() {
        $actions = array(
            'reload' => __('Fetch latest news', 'hypernews'),
            'hide'    => __('Hide selected news','hypernews'),
            'hidden' => __('Show hidden', 'hypernews'),
            'only_show' => __('Show content in all channels','hypernews')
        );
        
        foreach ($this->channels() as $key => $value) {
            $actions[$value] = __('Only show content in channel: ','hypernews').$value;
        }
        
        return $actions;
    }
    
    function process_bulk_action() {
        global $wpdb, $current_user;
        get_currentuserinfo();
        
        if ($this->current_action() === 'reload'){
            $this->reload_page();
            return;
        }
        
        if ($this->current_action() === 'hide'){
            $table_name = $wpdb->prefix . "hypernews_store";            
            foreach ($_POST['news'] as $key => $value) {
                $wpdb->query( $wpdb->prepare( 
                "
                UPDATE $table_name
                SET status = %s
                WHERE id = %d;
                ", 
                'HIDE', 
                $value 
                ) );
            }
            return;
        }
        
        if ($this->current_action() === 'hidden'){
            $this->hidden = true;
            return;
        }

        if ($this->current_action() === 'only_show'){
            update_user_meta( $current_user->ID, "hypernews_channel", NULL);
            $this->reload_page();
            return;
        }

        foreach ($this->channels() as $key => $value) {
            $current = $this->current_action();
            if ($current == $value){
                update_user_meta( $current_user->ID, "hypernews_channel", $value);
                $this->reload_page();
                return;
            }
        }
        
    }    
    
    function channels()
    {
        $result = array();
        $settings = new Hypernews_Settings();
        $links = $settings->links();
        foreach ($links as $key => $value) {
            if (!in_array($value['channel'], $result)) $result[] = $value['channel'];
        }
        return $result;
    }

/**
 * Define the columns that are going to be used in the table
 * @return array $columns, the array of columns to use with the table
 */
function get_columns() {

    return $columns= array(
        'cb' => '<input type="checkbox" onclick="jQuery(\'.hypernews_checkbox\').toggleCheckbox();" />',
        'status'=>'<img src="'.WP_PLUGIN_URL.'/hypernews/img/tag.png" />',
        'title'=>__('Headline', 'hypernews'),
        'pubdate'=>__('Published', 'hypernews'),
        'channel'=>__('Channel', 'hypernews'),
        'source'=>__('Source', 'hypernews'),
        'notes'=>__('Note', 'hypernews')
    );
}
    
    /**
 * Decide which columns to activate the sorting functionality on
 * @return array $sortable, the array of columns that can be sorted by the user
 */
public function get_sortable_columns() {
	return $sortable = array(
		'channel' => array('channel',false),
		'source' => array('source',false),
		'status' => array('status',false),
		'title' => array('title',false),
                'pubdate' => array('pubdate',false)
	);
}

function get_hidden_columns(){
    return array();
}


/**
 * Prepare the table with different parameters, pagination, columns and table elements
 */
function prepare_items() {
        global $wpdb, $_wp_column_headers;
        global $current_user;
        get_currentuserinfo();
       
        $screen = get_current_screen();

        $table_name = $wpdb->prefix . "hypernews_store";
        
        $this->process_bulk_action();
        
	/* -- Preparing your query -- */
        $query = "SELECT * FROM ".$table_name;
        
        $channel = get_user_meta($current_user->ID, "hypernews_channel");
        if (sizeof($channel)>0) $channel = $channel[0];
                    
        $where = "";
        if (!$this->hidden) 
        {
            if (strlen($where)==0) $where = ' WHERE ';
            $where.='status!="HIDE" ';
        }
        if (strlen($channel)>0) 
        {
            if (strlen($where)==0) 
                $where = ' WHERE ';
            else
                $where.=' AND ';
            $where.='channel="'.$channel.'" ';
        }
        $query.= $where;
        
        
        
    /* -- Ordering parameters -- */
        
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'pubdate';
        $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'desc';
        if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage = 100;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);
        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage)){
            $offset=($paged-1)*$perpage;
        $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query);
        
}

/**
 * Display the rows of records in the table
 * @return string, echo the markup of the rows
 */
function display_rows() {

        $settings = new Hypernews_Settings();
    
	//Get the records registered in the prepare_items method
	$records = $this->items;

	//Get the columns registered in the get_columns and get_sortable_columns methods
	list( $columns, $hidden ) = $this->get_column_info();

	//Loop for each record
	if(!empty($records)){foreach($records as $rec){
            //Open the line
            echo '<tr id="record_'.$rec->id.'">';
            foreach ( $columns as $column_name => $column_display_name ) 
            {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    //edit link
                    $editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

                    //Display the cell
                    switch ( $column_name ) {
                            case "cb": 
                                $cb_class = "hypernews_row_read";
                                if ($rec->status=='NEW'){
                                $cb_class = "hypernews_row_unread";
                                }
                                echo sprintf(
                                    '<td><input type="checkbox" class="hypernews_checkbox" name="%1$s[]" value="%2$s" /></td>',
                                    /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
                                    /*$2%s*/ $rec->id                //The value of the checkbox should be the record's id
                                );
                                break;
                            case "id": echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';	break;
                            case "title": 
                                $actions = array(
                                    'edit'      => sprintf('<a href="#" class="hypernews_edit_row" row_id="%3$s">'.__('Show', 'hypernews').'</a>',$_REQUEST['page'],'edit',$rec->id),
                                    'unread'      => sprintf('<a href="#" class="hypernews_unread_row" row_id="%3$s">'.__('Unread', 'hypernews').'</a>',$_REQUEST['page'],'unread',$rec->id),
                                    'star' => sprintf('<a href="#" class="hypernews_star_row" row_id="%3$s">'.__('Favorite', 'hypernews').'</a>',$_REQUEST['page'],'star',$rec->id),
                                    'hide'    => sprintf('<a href="#" class="hypernews_hide_row" row_id="%3$s">'.__('Hide', 'hypernews').'</a>',$_REQUEST['page'],'hide',$rec->id)
                                );

                                $title_class = "hypernews_title_row";
                                if ($rec->status=='NEW'){
                                    $title_class = "hypernews_title_unread";
                                }
                                
                                //Return the title contents
                                echo sprintf('<td><a class="%7$s" href="%6$s" target="_new">%1$s</a><br/><div class="hypernews_pre_row hypernews_row_pre_%4$s"><i>%2$s</i></div><div class="hypernews_hidden_row hypernews_row_%4$s">%5$s</div>%3$s</td>',
                                    /*$1%s*/ $rec->title,
                                    /*$2%s*/ substr(strip_tags($rec->description),0,150),
                                    /*$3%s*/ $this->row_actions($actions),
                                        $rec->id,
                                        strip_tags($rec->description),
                                        $rec->url,
                                        $title_class
                                );

                                //echo '<td '.$attributes.'><strong><a href="'.$editlink.'" title="Edit">'.stripslashes($rec->title).'</a></strong></td>'; 
                                break;
                            case "pubdate": 	
                                
                                //Channels:
                                $pb_result = "";
                                $link = $settings->get_link($rec->link_id);
                                $posttypes = $link['posttypes'];
                                if (!is_array($posttypes)) $posttypes = array();
                                foreach ($posttypes as $type)
                                {
                                    $posttype_object = get_post_type_object($type);
                                    if ($pb_result!='')
                                    {
                                        $pb_result.='&nbsp;&nbsp;&nbsp;&nbsp;';
                                    }

                                    $pb_result.='<a href="#" row_id="'.$rec->id.'" posttype="'.$type.'" class="hypernews_publish_row" title="'.__('Add as draft to', 'hypernews').' '.$type.'"><span class="hypernews_publish_add">'.$posttype_object->label.'</a>';
                                }
                                echo sprintf('<td %3$s>%1$s<br/>'.$pb_result.'<div style="clear:both;"></div></td>',
                                        stripslashes($rec->pubdate),
                                        $rec->id,
                                        $attributes
                                );
                                
                                break;
                            case "channel": echo '<td '.$attributes.'>'.stripslashes($rec->channel).'</td>';	break;
                            case "source": echo '<td '.$attributes.'>'.stripslashes($rec->source).'</td>';	break;
                            case "status":
                                echo '<td '.$attributes.'>';
                                if ($rec->status == 'NEW')
                                {
                                    echo '<img id="hypernews_row_icon_'.$rec->id.'" src="'.WP_PLUGIN_URL.'/hypernews/img/lightbulb.png" />';
                                }
                                else if ($rec->status == 'READ')
                                {
                                    echo '<img id="hypernews_row_icon_'.$rec->id.'" src="'.WP_PLUGIN_URL.'/hypernews/img/lightbulb_off.png" />';
                                }
                                else if ($rec->status == 'STAR')
                                {
                                    echo '<img id="hypernews_row_icon_'.$rec->id.'" src="'.WP_PLUGIN_URL.'/hypernews/img/star.png" />';
                                }                                
                                else if ($rec->status == 'HIDE')
                                {
                                    echo '<img id="hypernews_row_icon_'.$rec->id.'" src="'.WP_PLUGIN_URL.'/hypernews/img/cross.png" />';
                                }                                
                                else if ($rec->status == 'POST')
                                {
                                    echo '<a href="'.get_bloginfo('url').'/wp-admin/post.php?post='.$rec->post.'&action=edit" target="_blank"><img id="hypernews_row_icon_'.$rec->id.'" src="'.WP_PLUGIN_URL.'/hypernews/img/page_white_go.png" /></a>';
                                }
                                else
                                {
                                    echo stripslashes($rec->status);
                                }
                                echo '</td>';	
                                break;
                            case "notes": 	
                                
                                echo sprintf('<td %3$s><div id="hypernews_row_notetext_%2$s">%1$s</div><br/><div class="hypernews_hidden_row hypernews_row_%2$s"><textarea id="hypernews_row_notearea_%2$s">%1$s</textarea><br/><input type="button" value="'.__('Update', 'hypernews').'" row_id="%2$s" class="hypernews_row_note button-primary" /></div></td>',
                                        stripslashes($rec->notes),
                                        $rec->id,
                                        $attributes
                                );
                                break;
                    }
            }

            //Close the line
            echo'</tr>';
            }
        }
    }
}


?>