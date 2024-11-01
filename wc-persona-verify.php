<?php
/** 
 * Plugin Name: Check With Persona Verified
 * Plugin URI: http://vladitour.com/wp-persona-verify/
 * Description: Add Persona verification to checkout page(sandbox mode) of Wordpress. So when clients load the checkout page first time, system will hide the checkout option and show the Persona verify button. After verified successfully, clients can continue to checkout. Pro version will save the verified status to the data table and provide the "production" mode of the Persona verify.
 * Version: 1.0.1
 * Author: Cristian Robert
 * Author URI: https://vladitour.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php

 * Check With Persona Verified (Wordpress Plugin)
 * Copyright (C) 2019-2020 Nickolas Bossinas

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
*/

DEFINE("_PERSONAVERIFY_PLUGINFILE_", __FILE__);

// require_once( plugin_dir_path( _PERSONAVERIFY_PLUGINFILE_ ) . 'admin/view.php' );
require_once( plugin_dir_path( _PERSONAVERIFY_PLUGINFILE_ ) . 'admin/setting.php' );

//add admin menu hook action
add_action( 'admin_menu', 'persona_verify_admin_menu' );
/**
* register our wporg_settings_init to the admin_init action hook
*/
add_action( 'admin_init', 'persona_settings_init' );

//add init function to action hook
add_action('init', 'persona_verify_init');


//admin menu add function
function persona_verify_admin_menu() {
    add_menu_page(
        'Check With Persona Verified',//page title
        'WP Persona Verify',       //menu title
        'manage_options',       //capability
        'persona_verify',       //menu slug
        'persona_verify_menu_page_html',//callable function
        plugin_dir_url(__FILE__) . 'images/persona-verify.svg',
        20
    );
}

//init function
function persona_verify_init(){ 

    //register css file
    wp_register_style('persona_style', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('persona_style');

    wp_enqueue_script( 'persona_api',
        'https://cdn.withpersona.com/dist/persona-v3.4.1.js'
    );

}
 
// ------------------------Ajax function declare---------------------------------------------
add_action('wp_enqueue_scripts', 'persona_ajax');

function persona_ajax() {
    //at first include myquery.js file
    wp_enqueue_script( 'ajax-persona-script',
        plugins_url( '/js/myjquery.js', __FILE__ ),
        array('jquery')
    );
    //need create the nonce to check if this is valid ajax request, but not bad request
    $title_nonce = wp_create_nonce('persona_nonce');

    //localize script from myjuery.js with nonce
    wp_localize_script('ajax-persona-script', 'persona_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => $title_nonce,
    ));
}
//create new project ajax action
add_action('wp_ajax_receive_result', 'persona_receive_result');

//ajax action to handle persona success inquiry
function persona_receive_result() {
    $user_id=get_current_user_id();

    //check if it is valid ajax request
    check_ajax_referer('persona_nonce');

    $result['success']=false;
    $result['string']='Data update was error';

    if($_POST['inquiryId']==''){
        $result['string']='Your inquiryID is not set yet, please fully pass the Persona Verify.';
        echo json_encode($result);
        wp_die();
    }else{
        $result['success']=true;
        $result['string']='Thank you for your verify.';
        echo json_encode($result);
        wp_die(); // all ajax handlers should die when finished
    }
}



add_filter( 'woocommerce_before_checkout_form' , 'check_persona_verify' );
function check_persona_verify() {
    if ( is_user_logged_in() ) {
        $user_id=get_current_user_id();
        $all_meta_for_user = get_user_meta( $user_id );
        $has_persona_value = isset($all_meta_for_user['persona_verified']);
        $persona_options = get_option( 'persona_options' );
        if(!$has_persona_value){
            echo "<div id='persona_div'>
                    <p>Persona Verify is needed to checkout.</p>
                    <p id='hidden_tempID'>".$persona_options['tempID']."</p>
                    <button id='start_persona_verify' style='background: #ec671a; padding: 5px 15px'>Start Verify</button>
                </div>";
            wc_enqueue_js("
                console.log('User is not verified yet');
                $('form.checkout').hide();
                $('div.woocommerce-form-coupon-toggle').hide();
            ");
        }else wc_enqueue_js("
                    console.log('User is already verified');
                ");
    } else {
        wc_enqueue_js( "
                console.log('User has not logged in yet');
                $('.woocommerce-checkout #site-content .woocommerce').hide();
                $('.woocommerce-checkout #site-content .woocommerce').after('You are not logged in user, please login first');
        ");
    }
}

function my_plugin_action_links( $links ) {
    $links = array_merge( 
        array('<a href="' . esc_url( admin_url( '/admin.php?page=persona_verify' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'), 
        $links, 
        array('<a href="http://vladitour.com/wp-persona-verify/">' . __( 'Go Pro', 'textdomain' ) . '</a>') 
    );
    return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_action_links' );




?>
