<?php


function hypernews_links(){
    global $wpdb;
    global $current_user;
    get_currentuserinfo();

    //process form post!
    if (isset($_GET["delete"]) && isset($_GET["id"])){
        $table_name = $wpdb->prefix . "hypernews_links";
        $sql = 'DELETE FROM '.$table_name.' WHERE id='.$_GET['id'];
        $wpdb->query($sql); 
        echo '<script>document.location="?page=hypernews_links";</script>';
    }
    
    if (isset($_POST["save"]) && isset($_POST["id"])){
        $table_name = $wpdb->prefix . "hypernews_links";
        if ($_POST["id"]==0){
            //INSERT
            $wpdb->insert( 
                    $table_name, 
                    array( 
                            'source' => esc_attr($_POST["source"]), 
                            'channel' => esc_attr($_POST["channel"]), 
                            'type' => 'RSS', 
                            'url' => esc_attr($_POST["url"]), 
                            'search' => esc_attr($_POST["search"]), 
                            'sort_order' => esc_attr($_POST["sort_order"]) 
                    ), 
                    array( 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%d' 
                    ) 
                );
            echo '<script>document.location="?page=hypernews_links";</script>';
        }
        else{ 
            //UPDATE
            $wpdb->update( 
                    $table_name, 
                    array( 
                            'source' => esc_attr($_POST["source"]), 
                            'channel' => esc_attr($_POST["channel"]), 
                            'type' => 'RSS', 
                            'url' => esc_attr($_POST["url"]), 
                            'search' => esc_attr($_POST["search"]), 
                            'sort_order' => esc_attr($_POST["sort_order"]) 
                    ), 
                    array( 'ID' => $_POST["id"] ), 
                    array( 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%s', 
                            '%d' 
                    ), 
                    array( '%d' ) 
            );
            echo '<script>document.location="?page=hypernews_links";</script>';
        }
    }

    $source = "";
    $channel = "";
    $url = "";
    $sort_order = 10;
    if (isset($_GET["id"])){
        $table_name = $wpdb->prefix . "hypernews_links";
        $sql = 'SELECT * FROM '.$table_name.' WHERE id='.$_GET['id'];
        $row = $wpdb->get_row($sql,ARRAY_A); 
        $source = $row['source'];
        $channel = $row['channel'];
        
        if (strlen($channel)==0){
            $channel = get_user_meta($current_user->ID, "hypernews_channel");
            if (sizeof($channel)>0) $channel = $channel[0];
        }
        
        $url = $row['url'];
        $search = $row['search'];
        $sort_order = $row['sort_order'];
    }
    
    
?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div><h2><?php _e('Links', 'hypernews'); ?></h2>
        <form method="post">
        <?php
        if (isset($_GET["id"])) {
            ?>
            <h3><?php _e('Edit RSS source','hypernews'); ?></h3>
            <fieldset>
                <p>
                    <?php _e('Source name','hypernews'); ?>:<br/>
                    <input type="text" name="source" value="<?php echo $source; ?>" id="hypernews_name" size="50" />
                </p>
                <p>
                    <?php _e('Channel name','hypernews'); ?>:<br/>
                    <input type="text" name="channel" value="<?php echo $channel; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Url','hypernews'); ?>:<br/>
                    <input type="text" name="url" value="<?php echo $url; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Search','hypernews'); ?>:<br/>
                    <textarea name="search" cols="50" rows="5" scrollbars="1"><?php echo $search; ?></textarea><br/>
                    <i><?php _e('Only collecting news with one of these comma separated words found','hypernews'); ?></i>
                </p>
                <p>
                    <?php _e('Sort order','hypernews'); ?>:<br/>
                    <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="5" />
                </p>
                <p>
                    <input type="hidden" name="save" value="true" />
                    <input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>" />
                    <input type="submit" class="button-primary" value="<?php _e('Save','hypernews') ?>" />
                </p>
            </fieldset>
        <?php
        }
        else 
        {
            $list = new Hypernews_Links();
            $list->prepare_items();
            $list->display();
        }
        
        ?>
            
        </form>        
        
    </div>
<?php
}

if(!class_exists('WP_List_Table'))
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Hypernews_Links extends WP_List_Table {
    
    function __construct() 
    {
        global $status, $page;
         parent::__construct( array(
            'singular'=> 'Link', //Singular label
            'plural' => 'Links', //plural label, also this well be one of the table css class
            'ajax'	=> false //We won't support Ajax for this table
            ) );
    }    
    
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
       
        $screen = get_current_screen();

        $table_name = $wpdb->prefix . "hypernews_links";
        
	/* -- Preparing your query -- */
        $query = "SELECT * FROM ".$table_name;
        $query.=' WHERE type="RSS" ';
        
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
        $this->items = $wpdb->get_results($query,ARRAY_A);
        
    }
    
    function get_columns(){
        $columns = array(
            'id' => 'Id',
            'source'     => __('Source','hypernews'),
            'channel'    => __('Channel','hypernews'),
            'url'  => __('RSS-Url','hypernews')
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        return array();
    }
    
    function get_hidden_columns() {
        $result = array();
        $result[] = "id";
        return $result;
    }
    
    function extra_tablenav( $which ) 
    {
        if ( $which == "top" )
        {
        ?>
            <input onclick="document.location='?page=hypernews_links&id=0';" type="button" class="button-secondary" value="<?php _e('Add new RSS-url', 'hypernews') ?>" />
        <?php
        }
        if ( $which == "bottom" ){
        }
    }

    function column_source($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&id=%d">'.__('Edit','hypernews').'</a>',$_REQUEST['page'],$item['id']),
            'delete'      => sprintf('<a onclick="return confirm(\''.__('Confirm delete this source!','hypernews').'\');" href="?page=%s&id=%d&delete=true">'.__('Delete','hypernews').'</a>',$_REQUEST['page'],$item['id'])
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['source'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    function column_channel($item){
        return $item['channel'];
    }
    
    function column_url($item){
        return $item['url'];
    }
    
    
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
}


?>
