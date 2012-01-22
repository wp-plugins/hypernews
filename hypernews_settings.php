<?php

function hypernews_settings(){
    $settings = new Hypernews_Settings();
    $settings->display();
}

class Hypernews_Settings{

    private function get(){
        $settings = array(
            'Links' => array(), //List of Links
            'Browsers' => array()
        );
        $settings = get_option( 'hypernews_settings', $settings );
        return $settings;
    }

    public function delete_link($id){
        $settings = $this->get();
        foreach ($settings['Links'] as $key => $value) {
            if ($id == $value['id']) {
                unset($settings['Links'][$key]);
                break;
                }
        }
        update_option('hypernews_settings', $settings);
    }
    
    public function links(){
        $settings = $this->get();
        return $settings['Links'];
    }
    
    public function get_link($id = 0){
        
        $settings = $this->get();
        foreach ($settings['Links'] as $key => $value) {
            if ($id == $value['id']) return $value;
        }

        $result = array(
            'id' => uniqid(),
            'source' => 'Your RSS-feed name',
            'channel' => 'My own channel / department',
            'url' => 'http://rss.cnn.com/rss/edition.rss',
            'search' => '',
            'maxchars' => '0',
            'removechars' => '0',
            'maxage' => '0',
            'sort_order' => '100',
            'posttypes' => array()
        );

        return $result;
    }
    
    public function set_link($link){
        $settings = $this->get();
        foreach ($settings['Links'] as $key => $value) {
            if ($link['id'] == $value['id']) {
                $settings['Links'][$key] = $link;
                $found = true;
                break;
                }
        }
        if (!$found){
            $settings['Links'][] = $link;
        }
        update_option('hypernews_settings', $settings);
    }

    
    
    
    
    public function display() {
        // GLOBALS
        global $wpdb;
        global $current_user; 
        get_currentuserinfo(); // get current user info

//                echo "<script>document.location='?page=hypernews&fetch=true';</script>";
        ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br/></div><h2><?php _e('Settings', 'hypernews'); ?></h2>
        <?php
        
        print_r($this->get());
        
        echo '</div>';
    } 

    
    
    public function delete_browser($id){
        $settings = $this->get();
        foreach ($settings['Browsers'] as $key => $value) {
            if ($id == $value['id']) {
                unset($settings['Browsers'][$key]);
                break;
                }
        }
        update_option('hypernews_settings', $settings);
    }
    
    public function browsers(){
        $settings = $this->get();
        return $settings['Browsers'];
    }
    
    public function get_browser($id = 0){
        
        $settings = $this->get();
        foreach ($settings['Browsers'] as $key => $value) {
            if ($id == $value['id']) return $value;
        }

        $result = array(
            'id' => uniqid(),
            'source' => 'Your browser name',
            'channel' => 'My own channel / department',
            'url' => 'http://www.cnn.com/',
            'sort_order' => '100',
        );

        return $result;
    }
    
    public function set_browser($link){
        $settings = $this->get();
        foreach ($settings['Browsers'] as $key => $value) {
            if ($link['id'] == $value['id']) {
                $settings['Browsers'][$key] = $link;
                $found = true;
                break;
                }
        }
        if (!$found){
            $settings['Browsers'][] = $link;
        }
        update_option('hypernews_settings', $settings);
    }
    
    public function get_browsers_by_channel($channel){

        $result = array();
        $settings = $this->get();
        foreach ($settings['Browsers'] as $key => $value) {
            if ($channel == $value['channel']) $result[] = $value;
        }

        return $result;
    }
    
    
} //end of class hypernews_settings


?>        <tr valign="top">
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