<?php


function hypernews_links(){
    global $wpdb;
    global $current_user;
    get_currentuserinfo();
    
    $settings = new Hypernews_Settings();
    $current_link = $settings->get_link();
    
//        echo '<script>document.location="?page=hypernews_links";</script>';

    $link_id = 0;
    if (isset($_GET['id'])){
        
        if (isset($_GET['delete'])){
            $settings->delete_link($_GET['id']);
            echo '<script>document.location="?page=hypernews_links";</script>';
        }
        else{
            $current_link = $settings->get_link($_GET['id']);
            $link_id = $current_link['id'];
        }
        
    }
    
    if (isset($_POST['id'])){
        //SPARA
        $current_link['source'] = esc_attr($_POST['source']);
        $current_link['channel'] = esc_attr($_POST['channel']);
        $current_link['url'] = $_POST['url'];
        $current_link['search'] = esc_attr($_POST['search']);
        $current_link['maxchars'] = esc_attr($_POST['maxchars']);
        $current_link['removechars'] = esc_attr($_POST['removechars']);
        $current_link['maxage'] = esc_attr($_POST['maxage']);
        $current_link['sort_order'] = esc_attr($_POST['sort_order']);
        $current_link['posttypes'] = $_POST['posttypes'];
        $settings->set_link($current_link);
    }
    
?>
    <div class="wrap">
        <div id="icon-link-manager" class="icon32"><br/></div><h2><?php _e('RSS Feeds', 'hypernews'); ?></h2>
        <form method="post">
        <?php
        if (isset($_GET['id'])) {
            ?>
            <h3><?php _e('Edit RSS source','hypernews'); ?></h3>
                <p>
                    <?php _e('Source name','hypernews'); ?>:<br/>
                    <input type="text" name="source" value="<?php echo $current_link['source']; ?>" id="hypernews_name" size="50" />
                </p>
                <p>
                    <?php _e('Channel name','hypernews'); ?>:<br/>
                    <input type="text" name="channel" value="<?php echo $current_link['channel']; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Url','hypernews'); ?>:<br/>
                    <input type="text" name="url" value="<?php echo $current_link['url']; ?>" size="50" />
                </p>
                <p>
                    <?php _e('Search','hypernews'); ?>:<br/>
                    <textarea name="search" cols="50" rows="5" scrollbars="1"><?php echo $current_link['search']; ?></textarea><br/>
                    <i><?php _e('Only collecting news with one of these comma separated words found','hypernews'); ?></i>
                </p>
                <p>
                    <?php _e('Strikethrough text after n-characters:', 'hypernews'); ?>
                    <input type="text" name="maxchars" value="<?php echo $current_link['maxchars']; ?>" size="5" />&nbsp;<?php _e('( 0 = feature disabled )', 'hypernews'); ?>
                </p>
                <p>
                    <?php _e('Remove text after n-characters:', 'hypernews'); ?>
                    <input type="text" name="removechars" value="<?php echo $current_link['removechars']; ?>" size="5" />&nbsp;<?php _e('( 0 = feature disabled )', 'hypernews'); ?>
                </p>
                <p>
                    <?php _e('Delete RSS-items older than:', 'hypernews'); ?>
                    <input type="text" name="maxage" value="<?php echo $current_link['maxage']; ?>" size="5" />&nbsp;<?php _e('( 0 = feature disabled )', 'hypernews'); ?>
                </p>
                <p>
                    <?php _e('Publish to:', 'hypernews'); ?>
                    <?php
                        $post_types=get_post_types(array('public'=>true),'objects'); 
                        foreach ($post_types as $post_type ) 
                        {
                            echo '&nbsp;&nbsp;&nbsp;<span style="background-color:#CCC;padding:4px;"><input type="checkbox" name="posttypes[]" value="'.$post_type->name.'" ';

                            $posttypes = $current_link['posttypes'];
                            if (!is_array($posttypes)) $posttypes = array();
                            
                            if (in_array($post_type->name, $posttypes))
                            {
                                echo ' checked';
                            }

                            echo '> '. $post_type->label. '</span>';
                        }
                    ?>
                </p>
                <p>
                    <?php _e('Sort order:','hypernews'); ?>
                    <input type="text" name="sort_order" value="<?php echo $current_link['sort_order']; ?>" size="5" />
                </p>
                <p>
                    <?php _e('Test this feed after save:','hypernews'); ?>&nbsp;
                    <input type="checkbox" name="test" />
                </p>
                <p>
                    <?php _e('Reset all items from this source:', 'hypernews'); ?>&nbsp;
                    <input type="checkbox" name="clear" />
                </p>
                <p>
                    <input type="hidden" name="save" value="true" />
                    <input type="hidden" name="id" value="<?php echo $_GET["id"]; ?>" />
                    <input type="submit" name="save" class="button-primary" value="<?php _e('Save','hypernews') ?>" />
                    <input type="button" class="button-secondary" value="<?php _e('Cancel','hypernews') ?>" onclick="document.location='?page=hypernews_links';" />
                </p>
        <?php

            if ($_POST["clear"]){
                echo '<h3>Clear...</h3>';
                $sql = "DELETE FROM ".$wpdb->prefix . "hypernews_store WHERE link_id=".$current_link['id'];
                $wpdb->query( $sql );
            }

            if ($_POST["test"]){
                echo '<h3>Testing feed "'.$current_link['source'].'":</h3>';
                $fetch = new Hypernews_Fetcher();
                $result = $fetch->get_items($current_link);
                
                echo '<p>Found: '.sizeof($result['match']).'</p>';
                foreach ($result['match'] as $key => $value) {
                    echo $key.'. '.$value->get_title().'<br/>';
                }
                echo '<p>Missmatch: '.sizeof($result['mismatch']).'</p>';
                
                if ($result['error']){
                    echo '<p>Error: '.$result['error'].'</p>';
                }
                
            }
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
        global $_wp_column_headers;
       
        $screen = get_current_screen();
                
        $this->process_bulk_action();

        $settings = new Hypernews_Settings();

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        /* -- Fetch the items -- */
        $this->items = $settings->links();
        
    }
    
    function get_columns(){
        $columns = array(
            'id' => 'Id',
            'source'     => __('Source','hypernews'),
            'channel'    => __('Channel','hypernews'),
            'url'  => __('RSS-Url','hypernews'),
            'posttypes' => __('Post types','hypernews')
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
    
    function get_bulk_actions() {
        
        $actions['new_link'] = __('Add new RSS Feed', 'hypernews');
        
        return $actions;
    }
    
    function process_bulk_action() {

        if ($this->current_action() === 'new_link'){
            echo '<SCRIPT> document.location="?page=hypernews_links&id=0"; </SCRIPT>';
            return;
        }
        
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
    
    function column_posttypes($item){
        $result = "";
        $posttypes = $item['posttypes'];
        if (is_array($posttypes)){
            foreach ($posttypes as $key => $value) {
                if (strlen($result)>0) { $result.=', '; }
                $result.=$value;
            }
        }
        return $result;
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
}


?>
   function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
}


?>
