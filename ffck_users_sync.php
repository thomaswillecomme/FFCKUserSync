<?php 
  
require_once( 'database.php' );

/* 
 * Plugin Name: FFCK Users Sync
 * Description: Synchronize user list from a FFCK Organisation. New users are added as Contributors, Matching users are updated and set as Contributors if they where only Subscibers. Unexisting users are downgraded to Subscribers.
 * Author: Thomas Willecomme
 * License: LGPLv3
 * 
 */
require_once ('sync.php');

class ffck_users_sync{
    static function activate(){
        $db = new ffck_users_sync_db();
        $db->create();
    }

    static function deactivate(){

    }

    static function register_settings() { // whitelist options
      register_setting( 'ffck', 'user' );
      register_setting( 'ffck', 'password' );
      register_setting( 'ffck', 'season' );
    }

    
    static function tools_menu(){
        add_management_page( 'FFCK Users Sync script', 'FFCK', 'manage_options', 'ffck-users-sync-dashboard', array('ffck_users_sync','tools_page'));
    }
    
    
    static function tools_page(){
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        ?>
        <div class="wrap">
        <h2>FFCK Users Sync script</h2>
        <form method="post">
        <table class="form-table">
            <tr valign="top">
            <th scope="row">User</th>
            <td><input type="text" name="user" value="<?php echo esc_attr( get_option('user') ); ?>" /></td>
            </tr>

            <tr valign="top">
            <th scope="row">Password</th>
            <td><input type="text" name="password" value="<?php echo esc_attr( get_option('password') ); ?>" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Season (ie. 2015)</th>
            <td><input type="text" name="season" value="<?php echo esc_attr( get_option('season') ); ?>" /></td>
            </tr>

        </table>    
        <?php     submit_button('Sync now'); ?>
        </form>
        <textarea rows=20 cols="100"><?php 
        if($_POST['submit']){
            ffck_sync();
        }else{
            echo 'debug window';
        }
        ?></textarea>
        
        </div>
        <?php
    }
    
    
    
    static function settings_menu(){
        add_options_page('FFCK Users Sync settings', 'FFCK Users Sync', 'manage_options','ffck-users-sync-settings',array('ffck_users_sync','settings_page'));
    }

    static function settings_page(){
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        ?>
        <div class="wrap">
        <h2>FFCK Users Sync settings</h2>
        <form method="post" action="options.php">
        <?php 
            settings_fields( 'ffck' );
            do_settings_sections( 'ffck' );

        ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row">User</th>
            <td><input type="text" name="user" value="<?php echo esc_attr( get_option('user') ); ?>" /></td>
            </tr>

            <tr valign="top">
            <th scope="row">Password</th>
            <td><input type="text" name="password" value="<?php echo esc_attr( get_option('password') ); ?>" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Season (ie. 2015)</th>
            <td><input type="text" name="season" value="<?php echo esc_attr( get_option('season') ); ?>" /></td>
            </tr>

        </table>    
        <?php     submit_button(); ?>
        </div>
        <?php
    }
}


register_activation_hook( __FILE__, array('ffck_users_sync','activate') );
register_deactivation_hook( __FILE__, array('ffck_users_sync','deactivate') );

if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', array('ffck_users_sync','settings_menu') );
    add_action( 'admin_menu', array('ffck_users_sync','tools_menu') );
    add_action( 'admin_init', array('ffck_users_sync','register_settings') );
} else {
  // non-admin enqueues, actions, and filters
}
