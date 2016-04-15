<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



//update an user depending on provided data
function ffck_check_user($member_data){
    //look for a wordpress user with same licence id
    echo $member_data['firstname'].' '.$member_data['lastname'].' ';
    $existing_users = get_users( array('meta_key'=>'FFCK_licence', 'meta_value'=>$member_data['licence_id']));
    if(sizeof($existing_users) > 1){
        echo "ERROR multiple matches for licence ".$member_data['licence_id'];
    }elseif (sizeof($existing_users) == 1) {
        //licence id match. update account
        echo "Updating... ";
        ffck_update_user($existing_users[0],$member_data);
    }else{
        //look for a wordpress user with same email
        $existing_users = get_users( array('search'=>$member_data['email']));
        
        //for each match, 
        foreach($existing_users as $existing_user){
            
            //different licence_id, skip
            if($existing_user->has_prop('ffck_licence') && strcmp($existing_user->get('ffck_licence'),$member_data['licence_id']) != 0){
                echo "Skipping ".$existing_user->first_name.", ";
                continue;
            }else if(!$existing_user->has_prop('ffck_licence')){
                echo "Updating... ";
                ffck_update_user($existing_user,$member_data);
                echo PHP_EOL;
                return;
            }   
        }
        //no matching user, create new one
        echo "Creating... ";
        ffck_create_user($member_data);
   }
    
    echo PHP_EOL;
    
}

function ffck_update_user($wordpress_user,$ffck_member){
    $previous_season = get_user_meta( $wordpress_user->get('ID'), "ffck_season");
    
    $wordpress_user->first_name = ucfirst(strtolower($ffck_member['firstname']));
    $wordpress_user->last_name = ucfirst(strtolower($ffck_member['lastname']));
    $wordpress_user->user_email = $ffck_member['email'];
    
    wp_update_user($wordpress_user);

    update_user_meta( $wordpress_user->get('ID'), "ffck_season", $ffck_member['season'] );
    update_user_meta( $wordpress_user->get('ID'), "ffck_licence", $ffck_member['licence_id'] );
    
    //notify only after when the licence is updated
    if(sizeof($previous_season) == 0 || $previous_season[0] != $ffck_member['season']){
        ffck_notify_user_updated($wordpress_user);
    }
}

function ffck_create_user($ffck_member){
    //create a new user
    $password = wp_generate_password();
    $userid = wp_create_user($ffck_member['licence_id'],$password,$ffck_member['email']);
    if(is_wp_error($userid)){
        echo "ERROR: ".$userid->get_error_message();
        return;
    }
    $userdata = get_userdata($userid);

    $userdata->first_name = ucfirst(strtolower($ffck_member['firstname']));
    $userdata->last_name = ucfirst(strtolower($ffck_member['lastname']));
    $userdata->display_name = ucfirst(strtolower($ffck_member['firstname'])).' '.$ffck_member['lastname'][0].$ffck_member['lastname'][1];
    
    wp_update_user($userdata);
    
    update_user_meta( $userid, "ffck_season", (int)$ffck_member['season'] );
    update_user_meta( $userid, "ffck_licence", $ffck_member['licence_id'] );
    
    
    
    ffck_notify_user_created($userdata,$password);
}

function ffck_notify_user_created($wordpress_user, $password){
    echo "Created";
    wp_mail($wordpress_user->user_email,'Bienvenue au '.get_option('blogname'),'Bienvenue au '.get_option('blogname').' !<br>'
            . 'Votre inscription est maintenant effective auprès du Club et de la Fédération Francaise de Canoë Kayak.<br>'
            . 'Pour vous connecter sur le <a href="'.get_option('siteurl').'">site du club</a>, veuillez utiliser les identifiants suivants:<br>'
            . 'identifiant/numéro de licence:'.get_user_meta($wordpress_user->ID,'ffck_licence')[0].'<br>'
            . 'mot de passe:'.$password.'<br>'
            . 'Merci de changer rapidement votre mot de passe.<br><br>'
            
            . 'Vos informations personelles ont étés mises à jour.<br>'
            . 'Prénom :'.$wordpress_user->first_name.'.<br>'
            . 'Nom :'.$wordpress_user->last_name.'.<br>'
            . 'Courriel :'.$wordpress_user->user_email.'.<br>'
            . 'Dernière adhésion au club:'.get_user_meta($wordpress_user->ID,'ffck_season')[0].'.<br>'
            . 'Numéro de licence FFCK:'.get_user_meta($wordpress_user->ID,'ffck_licence')[0].'.<br>'
            . 'Si vous aviez déjà un compte sur le site du club, merci de communiquer votre numéro de licence FFCK et votre login en réponse à ce mail<br>'
            . 'Le Webmaster'
            ,array(
                'Content-Type: text/html; charset=UTF-8',
                'Reply-To: '.get_option('admin_email')
            ));
}

function ffck_notify_user_updated($wordpress_user){
    echo "Updated ";
    if(wp_mail($wordpress_user->user_email,'Compte '.get_option('blogname').' mis à jour','Compte '.get_option('blogname').' mis à jour !<br>'
            . 'Vos informations personelles ont étés mises à jour sur le <a href="'.get_option('siteurl').'">site du club</a>:</br>'
            . 'Prénom :'.$wordpress_user->first_name.'.<br>'
            . 'Nom :'.$wordpress_user->last_name.'.<br>'
            . 'Courriel :'.$wordpress_user->user_email.'.<br>'
            . 'Dernière adhésion au club:'.get_user_meta($wordpress_user->ID,'ffck_season')[0].'.<br>'
            . 'Numéro de licence FFCK:'.get_user_meta($wordpress_user->ID,'ffck_licence')[0].'.<br>'
            . 'En cas d erreur ou d information éronée, merci de contacter l administrateur en répondant à ce mail<br>'
            . 'Le Webmaster'
            ,array(
                'Content-Type: text/html; charset=UTF-8',
                'Reply-To: '.get_option('admin_email')
            )))
    {
        echo "mail sent";
    }else{
        echo "mail not sent";
    }

}






