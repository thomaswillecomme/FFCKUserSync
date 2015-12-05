<?php 
  

/* 
 * Plugin Name: FFCK Users Sync
 * Description: Synchronize user list from a FFCK Organisation. New users are added as Contributors, Matching users are updated and set as Contributors if they where only Subscibers. Unexisting users are downgraded to Subscribers.
 * Author: Thomas Willecomme
 * License: LGPLv3
 * 
 */


class ffck_users_sync{
    static function activate(){

    }

    static function deactivate(){

    }

    static function register_settings() { // whitelist options
      register_setting( 'ffck', 'user' );
      register_setting( 'ffck', 'password' );
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

        </table>    
        <?php     submit_button(); ?>
        </form>
        <!-- we use plugins_url otherwise the url is from wp-admin directory -->
        <form method="post" action="<?php echo plugins_url("sync.php",__FILE__); ?>">
            <input type="submit" value="Sync Now" id="submit" />
            <input type="hidden" name="password" value="<?php echo esc_attr( get_option('password') ); ?>" />
            <input type="hidden" name="login" value="<?php echo esc_attr( get_option('user') ); ?>" />
        </form>
        
        </div>
        <?php
    }
}


register_activation_hook( __FILE__, array('ffck_users_sync','activate') );
register_deactivation_hook( __FILE__, array('ffck_users_sync','deactivate') );

if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', array('ffck_users_sync','settings_menu') );
    add_action( 'admin_init', array('ffck_users_sync','register_settings') );
} else {
  // non-admin enqueues, actions, and filters
}
