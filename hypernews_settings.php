<?php

function hypernews_maxchars()
{
   
    $hypernews_settings = get_option( 'hypernews-settings', $hypernews_settings );
    if (is_numeric($hypernews_settings['maxchars']))
    {
        return $hypernews_settings['maxchars'];
    }
    else
    {
        return 99;
    }
}

function hypernews_removechars()
{
    $hypernews_settings = get_option( 'hypernews-settings', $hypernews_settings );
    if (is_numeric($hypernews_settings['removechars']))
    {
        return $hypernews_settings['removechars'];
    }
    else
    {
        return 0;
    }
}

function hypernews_maxage()
{
   
    $hypernews_settings = get_option( 'hypernews-settings', $hypernews_settings );
    if (is_numeric($hypernews_settings['maxage']))
    {
        return $hypernews_settings['maxage'];
    }
    else
    {
        return 168;
    }
}

function hypernews_settings() {
    // GLOBALS
    global $wpdb;
    global $current_user; get_currentuserinfo(); // get current user info
    
    $hypernews_settings = array('maxchars' => '255', 'removechars' => '0', 'maxage' => '168' );

    $hypernews_settings = get_option( 'hypernews-settings', $hypernews_settings );
    
    if ( isset( $_REQUEST['hypernews-update'] ) )
    {
        $hypernews_settings['maxchars'] = $_REQUEST['hypernews-maxchars'];        
        $hypernews_settings['removechars'] = $_REQUEST['hypernews-removechars'];        
        $hypernews_settings['maxage'] = $_REQUEST['hypernews-maxage'];        
        $hypernews_settings['posttypes'] = $_REQUEST['hypernews-posttypes'];
        update_option('hypernews-settings', $hypernews_settings);
        
        if (isset($_REQUEST['hypernews-clear'])){
            $table_name = $wpdb->prefix . "hypernews_store";
            $sql = "DELETE FROM ".$table_name;
            $wpdb->query( $sql );
            set_transient( 'hypernews_cache_unread', NULL);
            echo "<script>document.location='?page=hypernews&fetch=true';</script>";
        }
        
    }
    
    
?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div><h2><?php _e('Settings', 'hypernews'); ?></h2>
        
        <form method="post" >
            
            <table class="form-table">

                <tr valign="top">
                    <th scope="row"><?php _e('Strikethrough text after n-characters:', 'hypernews'); ?></th>
                    <td><input size="5" type="text" name="hypernews-maxchars" value="<?php echo hypernews_maxchars() ?>" />&nbsp;
                    <?php _e('( 0 = disabled )', 'hypernews'); ?></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Remove text after n-characters:', 'hypernews'); ?></th>
                    <td><input size="5" type="text" name="hypernews-removechars" value="<?php echo hypernews_removechars() ?>" />&nbsp;
                    <?php _e('( 0 = disabled )', 'hypernews'); ?></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Delete RSS-items older than:', 'hypernews'); ?></th>
                    <td><input size="5" type="text" name="hypernews-maxage" value="<?php echo hypernews_maxage() ?>" /> <?php _e('hours', 'hypernews'); ?></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Publish to:', 'hypernews'); ?></th>
                    <td>
                        <?php
                            $post_types=get_post_types('','names'); 
                            foreach ($post_types as $post_type ) 
                            {
                                echo '<input type="checkbox" name="hypernews-posttypes[]" value="'.$post_type.'" ';
                                
                                if (in_array($post_type, $hypernews_settings['posttypes']))
                                {
                                    echo ' checked';
                                }
                                
                                echo '> '. $post_type. '<br/>';
                            }
                        ?>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Clear all items from store:', 'hypernews'); ?></th>
                    <td><input type="checkbox" name="hypernews-clear" /> <?php _e('Hypernews will fetch new items directly.', 'hypernews'); ?></td>
                </tr>
                
            </table>

            <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'hypernews') ?>" />
            <input type="hidden" name="hypernews-update" value="true" />
            </p>
            
        </form>
        
    </div>
<?php
} 


?>