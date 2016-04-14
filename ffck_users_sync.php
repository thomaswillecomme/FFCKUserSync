<?php 
  

/* 
 * Plugin Name: FFCK Users Sync
 * Description: Synchronize user list from a FFCK Organisation. New users are added as Contributors, Matching users are updated and set as Contributors if they where only Subscibers. Unexisting users are downgraded to Subscribers.
 * Author: Thomas Willecomme
 * License: LGPLv3
 * 
 */
require_once ('sync.php');


class ffck_users_sync{
    
    static function user_info($user){
        $licence = get_user_meta($user->ID,'ffck_licence');
        $season = get_user_meta($user->ID,'ffck_season');
        if(sizeof($licence) == 1 && sizeof($season) == 1){
            echo '<h3>FFCK</h3>
                <table class="form-table">';
            
            echo '<tr><th><label for="ffck_licence">Numéro de licence</label></th><td><input type="text" name="ffck_licence" id="ffck_licence" value="'.esc_attr( get_the_author_meta( 'ffck_licence', $user->ID ) ).'" class="regular-text"/></td></tr>';
            echo '<tr><th><label for="ffck_season">Dernière saison</label></th><td><input type="number" name="ffck_season" id="ffck_season" value="'.esc_attr( get_the_author_meta( 'ffck_season', $user->ID) ).'" class="regular-text"/></td></tr>';
            
            echo '</table>';
        } 
    }
    
    static function user_info_save($user_id){
        if(!(sizeof(get_user_meta($user_id,'ffck_licence')) == 0 && strcmp($_POST['ffck_licence'],"") == 0))
            update_user_meta($user_id,'ffck_licence',$_POST['ffck_licence']);
        if(!(sizeof(get_user_meta($user_id,'ffck_season')) == 0 && $_POST['ffck_season'] != NULL))
            update_user_meta($user_id,'ffck_season',$_POST['ffck_season']);
    }
    
    static function user_list_info($val, $column_name,$user_id){
        $user = get_userdata( $user_id );
        switch ($column_name) {
            case 'ffck_licence' :
                return get_the_author_meta( 'ffck_licence', $user_id );
                break;
            case 'ffck_season' :
                return get_the_author_meta( 'ffck_season', $user_id );
                break;
            default:
        }
        return $return;
    }
    

    function new_modify_user_table( $column ) {
        $column['ffck_licence'] = 'Numéro de licence FFCK';
        $column['ffck_season'] = 'Dernière saison';
        return $column;
    }
    
    static function activate(){
        //wp_schedule_event(time(), 'daily', 'ffck_daily_sync');
    }

    static function deactivate(){
        //wp_clear_scheduled_hook('ffck_daily_sync');
    }

    static function register_settings() { // whitelist options
      register_setting( 'ffck', 'user' );
      register_setting( 'ffck', 'password' );
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
            <th scope="row">Season</th>
            <td><input type="number" name="season" value="<?php echo date("Y"); ?>" /></td>
            </tr>
        </table>    
        <?php     submit_button('Sync now'); ?>
        </form>
        <textarea rows=20 cols="100"><?php 
        if($_POST['submit']){
            ffck_sync($_POST['season']);
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
        </table>    
        <?php     submit_button(); ?>
        </div>
        <?php
    }
    
    
    static function skip_email_exist($user_email){
        define( 'WP_IMPORTING', 'SKIP_EMAIL_EXIST' );
        return $user_email;
    }
    


}


register_activation_hook( __FILE__, array('ffck_users_sync','activate') );
register_deactivation_hook( __FILE__, array('ffck_users_sync','deactivate') );


add_filter('pre_user_email', array('ffck_users_sync','skip_email_exist'));

add_filter( 'manage_users_columns', array('ffck_users_sync','new_modify_user_table') );
add_filter( 'manage_users_custom_column', array('ffck_users_sync','user_list_info'), 10, 3);

add_action( 'personal_options_update', array('ffck_users_sync','user_info_save' ));
add_action( 'edit_user_profile_update', array('ffck_users_sync','user_info_save' ));
add_action( 'show_user_profile', array('ffck_users_sync','user_info') );
add_action( 'edit_user_profile', array('ffck_users_sync','user_info') );
//add_action('ffck_daily_sync', 'ffck_sync');

if ( is_admin() ){ // admin actions
    add_action( 'admin_menu', array('ffck_users_sync','settings_menu') );
    add_action( 'admin_menu', array('ffck_users_sync','tools_menu') );
    add_action( 'admin_init', array('ffck_users_sync','register_settings') );
} else {
  // non-admin enqueues, actions, and filters
}
