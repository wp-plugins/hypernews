<?php
/*
 * INSERT CUSTOM TABLE FOR PLUGIN
 */

class HypernewsInstall 
{
     static function install() 
    {
        global $wpdb;

        //set table structure version
        $hn_db_version = "0.5";
        
        if (get_option('hn_db_version')!=$hn_db_version)
        {
            $table_name = $wpdb->prefix . "hypernews_store";
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                $sql = "CREATE TABLE ".$table_name." (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        title text NOT NULL,
                        url text NOT NULL,
                        link_id VARCHAR(255),
                        channel VARCHAR(255),
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
                        UNIQUE KEY id (id),
                        PRIMARY KEY (id) );";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
            $table_name = $wpdb->prefix . "hypernews_links";
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
//            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
//                $sql = "CREATE TABLE ".$table_name." (
//                        id mediumint(9) NOT NULL AUTO_INCREMENT,
//                        source VARCHAR(255),
//                        channel VARCHAR(255),
//                        type VARCHAR(255),
//                        url VARCHAR(255) NOT NULL,
//                        description text,
//                        search text,
//                        sort_order INT,
//                        updated timestamp,
//                        UNIQUE KEY id (id),
//                        PRIMARY KEY (id) );";
//                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//                dbDelta($sql);
//            }
            //Save the table structure version number
            add_option('hn_db_version', $hn_db_version);
        }
    }
}


?>