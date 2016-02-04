<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function ffck_on_no_match($member_data){
    //do some stufs for unmatched members
    
    //create a new user
    $password = wp_generate_password();
    $userid = wp_create_user($member_data['licence_id'],$password,$member_data['email']);
    $member_data->wordpress_user = $userid;
    $userdata = get_userdata($userid);
    
    $userdata->first_name = ucfirst(strtolower($member_data['firstname']));
    $userdata->last_name = ucfirst(strtolower($member_data['lastname']));
    $userdata->display_name = ucfirst(strtolower($member_data['firstname'])).' '.$member_data['lastname'][0];
    $userdata->set_role('contributor');
    
    
    wp_update_user($userdata);
//    wp_mail($member_data['email'],'Bienvenue au GACK','Bienvenue au GACK !<br>'
//            . 'Votre inscription est maintenant effective auprès du Club et de la Fédération Francaise de Canoë Kayak.<br>'
//            . 'Pour vous connecter sur le site du club, veuillez utiliser les identifiants suivants:<br>'
//            . 'identifiant/numéro de licence:'.$member_data['licence_id'].'<br>'
//            . 'mot de passe:'.$password.'<br>'
//            . 'Merci de changer rapidement votre mot de passe.<br><br>'
//            . 'Le Bureau du GACK'
//            );
    

}

function ffck_on_new_match($member_data,$userdata){
    //do some stufs for newly matched users
    $userdata->first_name = ucfirst(strtolower($member_data['firstname']));
    $userdata->last_name = ucfirst(strtolower($member_data['lastname']));
    $userdata->set_role('contributor');
    
    wp_update_user($userdata);
}

function ffck_on_existing_match($member_data){
    //do some stufs for matched licencee
    
}


function ffck_on_revocated_match($member_data){
    //do some stufs for old members which have not an up to date licence
    
    //ungrant user member privileges to subscriber except if it is an admin
    $userdata = get_userdata($member_data['wordpress_user']);
    if(in_array('administrator',$userdata->roles)){
        return;
    }
    
    $userdata->set_role('subscriber');
    wp_update_user($userdata);
    
    //    wp_mail($member_data['email'],'Licence Grenoble Alpes Canoë Kayak expirée','Bonjour !<br>'
//            . 'Votre licence n'est plus à jour auprès du Club et de la Fédération Francaise de Canoë Kayak.<br>'
//            . 'Si vous souhaitez reprendre votre licence, merci de vous présenter à l'une de nos permanences ou de contacter un des membres du bureau.<br>'
//            . 'Sans licence valide, vous ne pouvez participer aux sorties organisés par le club.<br>'
//            . 'Le Bureau du GACK'
//            );
}

