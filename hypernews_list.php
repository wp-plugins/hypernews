<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


if(!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Hypernews_List extends WP_List_Table 
{

     function __construct() 
    {
         parent::__construct( array(
        'singular'=> 'News', //Singular label
        'plural' => 'News', //plural label, also this well be one of the table css class
        'ajax'	=> true //We won't support Ajax for this table
        ) );
     }
     
     function extra_tablenav( $which ) 
     {
        global $current_user;
        get_currentuserinfo();
        if ( $which == "top" )
            {
                if ( isset( $_REQUEST['fetch'] ) )
                {
                    set_transient( 'hypernews_cache_unread', NULL);
                    echo "<script>document.location='?page=hypernews';</script>";
                }
                ?>
                <input onclick="document.location='?page=hypernews&fetch=true';" type="button" class="button-secondary" value="<?php _e('Reload page', 'hypernews') ?>" />
                <input onclick="document.location='?page=hypernews&hidden=true';" type="button" class="button-secondary" value="<?php _e('Show hidden', 'hypernews') ?>" />
                &nbsp;
                <?php _e(' Your channel: ','hypernews'); ?>
                <select onchange="document.location='?page=hypernews&channel='+this.options[this.selectedIndex].value;">
                    <option value="">--- show all ---</option>
                    <?php
                    $channel = get_user_meta($current_user->ID, "hypernews_channel");
                    if (sizeof($channel)>0) $channel = $channel[0];
                    foreach ($this->channels() as $key => $value) {
                        echo '<option ';
                        if ($value == $channel) echo 'selected';
                        echo '>'.$value.'</option>\n';
                    }
                    ?>
                </select>
                &nbsp;

                 <?php
            }
            if ( $which == "bottom" ){
                    //The code that goes after the table is there
                    echo " ";
            }
    }
    
    function channels()
    {
        global $wpdb;
        $result = array();
        
        $table_name = $wpdb->prefix . "hypernews_links";
        
	/* -- Preparing your query -- */
        $query = "SELECT DISTINCT(channel) FROM ".$table_name;
        $rows = $wpdb->get_results($query);
        foreach ($rows as $key => $value) {
            $result[] = $value->channel;
        }
        return $result;
    }

    
/**
 * Define the columns that are going to be used in the table
 * @return array $columns, the array of columns to use with the table
 */
function get_columns() {

    return $columns= array(
        'status'=>'<img src="'.WP_PLUGIN_URL.'/hypernews/img/tag.png" />',
        'title'=>__('Headline', 'hypernews'),
        'pubdate'=>__('Published', 'hypernews'),
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
		'source'=>'source',
		'status'=>'status',
		'title'=>'title',
                'pubdate'=>'pubdate'
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
        $table_name2 = $wpdb->prefix . "hypernews_links";
        
	/* -- Preparing your query -- */
        $query = "SELECT s.*, l.source FROM ".$table_name." s INNER JOIN ".$table_name2." l ON s.link_id=l.id";
        
        if (isset($_GET['channel'])){
            update_user_meta( $current_user->ID, "hypernews_channel", $_GET['channel']);
        }
        $channel = get_user_meta($current_user->ID, "hypernews_channel");
        if (sizeof($channel)>0) $channel = $channel[0];
                    
        $where = "";
        if (!$_REQUEST["hidden"]) 
        {
            if (strlen($where)==0) $where = ' WHERE ';
            $where.='s.status!="HIDE" ';
        }
        if (strlen($channel)>0) 
        {
            if (strlen($where)==0) 
                $where = ' WHERE ';
            else
                $where.=' AND ';
            $where.='s.channel="'.$channel.'" ';
        }
        $query.= $where;
        
        
        
    /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
        $order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
        if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage = 20;
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

function publish_channel($id)
{
    //world_add.png
    $result = '';
    
    $hypernews_settings = get_option( 'hypernews-settings' );
    $posttypes = $hypernews_settings['posttypes'];
    
    //echo var_export($hypernews_settings);
    
    foreach ($posttypes as $type)
    {
        if ($result!='')
        {
            $result.='&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        
        $result.='<a href="#" row_id="'.$id.'" posttype="'.$type.'" class="hypernews_publish_row" title="'.__('Add as draft to', 'hypernews').' '.$type.'"><img src="'.WP_PLUGIN_URL.'/hypernews/img/page_white_add.png" /> '.$type.'</a>';
    }
    
    
    return $result;
}

/**
 * Display the rows of records in the table
 * @return string, echo the markup of the rows
 */
function display_rows() {

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
                            case "id": echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';	break;
                            case "title": 
                                $actions = array(
                                    'edit'      => sprintf('<a href="#" class="hypernews_edit_row" row_id="%3$s">'.__('Show', 'hypernews').'</a>',$_REQUEST['page'],'edit',$rec->id),
                                    'unread'      => sprintf('<a href="#" class="hypernews_unread_row" row_id="%3$s">'.__('Unread', 'hypernews').'</a>',$_REQUEST['page'],'unread',$rec->id),
                                    'star' => sprintf('<a href="#" class="hypernews_star_row" row_id="%3$s">'.__('Favorite', 'hypernews').'</a>',$_REQUEST['page'],'star',$rec->id),
                                    'hide'    => sprintf('<a href="#" class="hypernews_hide_row" row_id="%3$s">'.__('Hide', 'hypernews').'</a>',$_REQUEST['page'],'hide',$rec->id)
                                );

                                //Return the title contents
                                echo sprintf('<td><strong><a href="%6$s" target="_new">%1$s</a></strong><br/><div class="hypernews_row_pre_%4$s">%2$s</div><div class="hypernews_hidden_row hypernews_row_%4$s">%5$s</div>%3$s</td>',
                                    /*$1%s*/ $rec->title,
                                    /*$2%s*/ substr(strip_tags($rec->description),0,hypernews_maxchars()).'...',
                                    /*$3%s*/ $this->row_actions($actions),
                                        $rec->id,
                                        strip_tags($rec->description),
                                        $rec->link
                                );

                                //echo '<td '.$attributes.'><strong><a href="'.$editlink.'" title="Edit">'.stripslashes($rec->title).'</a></strong></td>'; 
                                break;
                            case "pubdate": 	
                                echo sprintf('<td %3$s>%1$s<br/>'.$this->publish_channel($rec->id).'</td>',
                                        stripslashes($rec->pubdate),
                                        $rec->id,
                                        $attributes
                                );
                                
                                break;
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