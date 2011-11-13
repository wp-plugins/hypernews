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


function hypernews_settings() {
    // GLOBALS
    global $wpdb;
    global $current_user; get_currentuserinfo(); // get current user info
    
    $hypernews_settings = array('category' => 'Hypernews',
        'interval' => '30', 'maxchars' => '255' );

    $hypernews_settings = get_option( 'hypernews-settings', $hypernews_settings );
    
    if ( isset( $_REQUEST['hypernews-update'] ) )
    {
        $hypernews_settings['category'] = $_REQUEST['hypernews-category'];
        $hypernews_settings['interval'] = $_REQUEST['hypernews-interval'];
        $hypernews_settings['maxchars'] = $_REQUEST['hypernews-maxchars'];        
        $hypernews_settings['posttypes'] = $_REQUEST['hypernews-posttypes'];
        update_option('hypernews-settings', $hypernews_settings);
    }
    
    
?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div><h2><?php _e('Settings', 'hypernews'); ?></h2>
        
        <form method="post" >
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Link category name:', 'hypernews'); ?></th>
                    <td><input type="text" name="hypernews-category" value="<?php echo $hypernews_settings['category']; ?>" /> <?php _e('Match this with "Link Category" !', 'hypernews'); ?></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Interval:'); ?></th>
                    <td><input type="text" name="hypernews-interval" value="<?php echo $hypernews_settings['interval']; ?>" /> <?php _e('seconds', 'hypernews'); ?></td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e('Show max chars:', 'hypernews'); ?></th>
                    <td><input type="text" name="hypernews-maxchars" value="<?php echo $hypernews_settings['maxchars']; ?>" /></td>
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