<?php


function hypernews_browse(){
    global $wpdb;
    global $current_user;
    get_currentuserinfo();
    
    $settings = new Hypernews_Settings();
    $current_browser = $settings->get_browser();
    
    $browser_id = 0;
    if (isset($_GET['id'])){
        
        if (isset($_GET['delete'])){
            $settings->delete_browser($_GET['id']);
            echo '<script>document.location="?page=hypernews_browse";</script>';
        }
        else{
            $current_browser = $settings->get_browser($_GET['id']);
            $browse_id = $current_browser['id'];
        }
        
    }
    
    if (isset($_POST['id'])){
        //SPARA
        $current_browser['source'] = esc_attr($_POST['source']);
        $current_browser['channel'] = esc_attr($_POST['channel']);
        $current_browser['url'] = $_POST['url'];
        $current_browser['sort_order'] = esc_attr($_POST['sort_order']);
        $settings->set_browser($current_browser);
    }
    
?>
    <div class="wrap">
        <div id="icon-link-manager" class="icon32"><br/></div><h2><?php _e('Browse List', 'hypernews'); ?></h2>
        <form method="post">
        <?php
        if (isset($_GET['id'])) {
            ?>
            <h3><?php _e('Edit browser source','hypernews'); ?></h3>
                <p>
                    <?php _e('Browser name','hypernews'); ?>:<br/>
                    <input type="text" name="source" value="<?php echo $current_browser['source']; ?>" id="hypernews_name" size="50" />
                </p>
                <p>
                    <?php _e('Channel name','hypernews'); ?>:<br/>
                    <input type="text" name="channel" value="<?php echo $current_browser['channel']; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Url','hypernews'); ?>:<br/>
                    <input type="text" name="url" value="<?php echo $current_browser['url']; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Sort order:','hypernews'); ?><br/>
                    <input type="text" name="sort_order" value="<?php echo $current_browser['sort_order']; ?>" size="5" />
                </p>
                <p>
                    <input type="hidden" name="save" value="true" />
                    <input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>" />
                    <input type="submit" name="save" class="button-primary" value="<?php _e('Save','hypernews') ?>" />
                    <input type="button" class="button-secondary" value="<?php _e('Cancel','hypernews') ?>" onclick="document.location='?page=hypernews_browse';" />
                </p>
        <?php

        }
        else 
        {
            $list = new Hypernews_Browse();
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

class Hypernews_Browse extends WP_List_Table {
    
    function __construct() 
    {
        global $status, $page;
         parent::__construct( array(
            'singular'=> 'Browser', //Singular label
            'plural' => 'Browsers', //plural label, also this well be one of the table css class
            'ajax'	=> false //We won't support Ajax for this table
            ) );
    }    
    
    function prepare_items() {
        global $_wp_column_headers;
       
        $screen = get_current_screen();

        $settings = new Hypernews_Settings();
        
        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        /* -- Fetch the items -- */
        $this->items = $settings->browsers();
        
    }
    
    function get_bulk_actions() {
        
        $actions['new_browser'] = __('Add new browser-url', 'hypernews');
        
        foreach ($this->channels() as $key => $value) {
            $actions[$value] = __('Start browsing: ','hypernews').$value;
        }
        
        return $actions;
    }
    
    function process_bulk_action() {

        if ($this->current_action() === 'new_browser'){
            echo '<SCRIPT> document.location="?page=hypernews_browse&id=0"; </SCRIPT>';
            return;
        }

        $settings = new Hypernews_Settings();
        $i = 25;
        foreach ($this->channels() as $key => $value) {
        
            $current = $this->current_action();
            if ($current == $value){
                $browsers = $settings->get_browsers_by_channel($value);
                foreach ($browsers as $key => $value) {
?>
<script>
    jQuery(document).ready(function() {
        window.open('<?php echo $value['url']; ?>','','top=<?php echo $i ?>,left=<?php echo $i ?>,width=800,height=600,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes');
    });
</script>

<?php
                    $i=$i+25;     
                }
            }
        }    

    }
    
    function channels()
    {
        $result= array();
        $settings = new Hypernews_Settings();
        $browsers = $settings->browsers();
        foreach ($browsers as $key => $value) {
            if (!in_array($value['channel'], $result)) $result[] = $value['channel'];
        }
        return $result;
    }    
    
    function get_columns(){
        $columns = array(
            'id' => 'Id',
            'source'     => __('Source','hypernews'),
            'channel'    => __('Channel','hypernews'),
            'url'  => __('RSS-Url','hypernews'),
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
        }
        if ( $which == "bottom" ){
        }
    }

    function column_source($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%1$s&id=%2$s">'.__('Edit','hypernews').'</a>',$_REQUEST['page'],$item['id']),
            'delete'      => sprintf('<a onclick="return confirm(\''.__('Confirm delete this source!','hypernews').'\');" href="?page=%s&id=%s&delete=true">'.__('Delete','hypernews').'</a>',$_REQUEST['page'],$item['id'])
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
    
    function column_id($item){
        return $item['id'];
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
