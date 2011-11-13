<?php
/*
 * INSERT CUSTOM TABLE FOR PLUGIN
 */

class HypernewsInstall 
{
     static function install() 
    {
        global $wpdb;

        //define the custom table
        $table_name = $wpdb->prefix . "hypernews_store";

        //set table structure version
        $hn_db_version = "1.5";
        
        if (get_option('hn_db_version')!=$hn_db_version)
        {
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
        

        // verify the table doesn't already exist
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
            $sql = "CREATE TABLE ".$table_name." (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    title text NOT NULL,
                    link text NOT NULL,
                    source VARCHAR(255),
                    description text,
                    pubdate DATETIME NOT NULL,
                    guid VARCHAR(255),
                    status VARCHAR(15),
                    post mediumint(9),
                    posturl VARCHAR(255),
                    postedby mediumint(9),
                    notes text,
                    updated timestamp,
                    UNIQUE KEY id (id));";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            //Save the table structure version number
            add_option('hn_db_version', $hn_db_version);
        }
    }
}


?>