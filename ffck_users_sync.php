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


/**
 * Return current editing user_id
 *
 * @return int
 * @author Vadimk
 */
function get_user_id() {

    $get_user_id = empty( $_GET['user_id'] ) ? null : $_GET['user_id'];

    if ( ! isset( $get_user_id ) ) {
        $get_user_id = empty( $_POST['user_id'] ) ? null : $_POST['user_id'];
    }

    if ( ! isset( $get_user_id ) ) {
        global $current_user;
        get_currentuserinfo();
        $get_user_id = $current_user->ID;
    }

    return $get_user_id;
}

class ffck_users_sync{
    
    static function user_info(){
        $db = new ffck_users_sync_db();
        $meta_data = $db->find_wordpress_user(get_user_id());
        if($meta_data != NULL){
            echo '<h3>FFCK informations</h3>
                <table class="form-table">';
            foreach($meta_data as $key => $value){
                echo '<tr><th>'.$key.'</th><td>'.$value.'</td></tr>';
            }
            echo '</table>';
        } 
    }
    
    static function activate(){
        $db = new ffck_users_sync_db();
        $db->create();
        wp_schedule_event(time(), 'daily', 'ffck_daily_sync');
    }

    static function deactivate(){
        wp_clear_scheduled_hook('ffck_daily_sync');
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

add_action( 'edit_user_profile', array('ffck_users_sync','user_info') );
add_action('ffck_daily_sync', 'ffck_sync');

if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', array('ffck_users_sync','settings_menu') );
    add_action( 'admin_menu', array('ffck_users_sync','tools_menu') );
    add_action( 'admin_init', array('ffck_users_sync','register_settings') );
} else {
  // non-admin enqueues, actions, and filters
}
