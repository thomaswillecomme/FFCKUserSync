<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
        
class ffck_users_sync_db{
    var $table_name;
    
    function __construct(){
        global $wpdb;
        $this->table_name = $wpdb->prefix . "ffck_users"; 
    }
    
    function create(){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name ("
          ."licence_id mediumint(9) NOT NULL,"
          ."wordpress_user bigint(20) unsigned,"
          ."firstname tinytext NOT NULL,"
          ."lastname tinytext NOT NULL,"
          ."maidname tinytext,"
          ."gender tinytext NOT NULL,"
          ."birthdate date NOT NULL,"
          ."phone tinytext,"
          ."phone2 tinytext,"
          ."address text,"
          ."email tinytext NOT NULL,"
          ."certificate date,"
          ."competition_certificate date,"
          ."season mediumint(4) NOT NULL,"
          ."PRIMARY KEY  (licence_id),"
          ."FOREIGN KEY  (wordpress_user) REFERENCES wp_users(ID)"
        .") $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    function add_member($member_data){
        global $wpdb;
        return $wpdb->insert($this->table_name, $member_data);
    }
    
    function update_member($member_data){
        global $wpdb;
        return $wpdb->update($this->table_name, $member_data, array('licence_id' => $member_data['licence_id']));
    }  
    
    function get_member($licence_id){
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM $this->table_name WHERE licence_id=$licence_id", ARRAY_A);
    }  
    
    
    function find_match($member_data){
        global $wpdb;
        $id = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_email='".$member_data['email']."';");
        if($id != NULL){
            $member_data['wordpress_user']=$id;
            $this->update_member($member_data);
            return $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID=$id", ARRAY_A);
        }
        return NULL;
    }
    
    function find_wordpress_user($user_id){
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM $this->table_name WHERE wordpress_user=$user_id", ARRAY_A);
    }
    
    
    function get_old_members($season){
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $this->table_name WHERE season<>'$season';");
    }
}
