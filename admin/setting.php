<?php

function persona_settings_init() {
    // register a new setting for "persona" page
    register_setting( 
        'persona',        
        'persona_options'
    );
    
    // register a new section in the "persona" page
    add_settings_section(
        'persona_value_section',         //Slug-name to identify the section
        __( 'Value Of Persona API', 'persona' ),      //Formatted title of the section
        'persona_setting_sections_func',          //Function that echos out any content at the top of the section (between heading and fields).
        'persona'
    );
    
    // register a new field in the "persona_value_section" section
    add_settings_field(
        //Slug-name to identify the field. 
        'templateID',                                               
        //Formatted title of the field. Shown as the label for the field during output.
        __( 'Persona Template ID', 'persona_manage_setting' ),    
        //Function that fills the field with the desired form inputs. The function should echo its output.
        'persona_setting_fields_func',  
        //slug-name of the settings page on which to show the section 
        'persona',
        //slug-name of the section in which to show the box.
        'persona_value_section'
    );
}


function persona_setting_sections_func( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'Settings for Persona API', 'persona'); ?>
    </p>
    <?php
}
    

function persona_setting_fields_func( $args ) {
    // get the value of the setting we've registered with register_setting()
    $persona_options = get_option( 'persona_options' );
    $tempID='tmpl_JAZjHuAT738Q63BdgCuEJQre';
    if(isset($persona_options['tempID']))$tempID=$persona_options['tempID'];
    echo '<input type="text" class="persona_admin_input" name="persona_options[tempID]" value="'.$tempID.'">';
    echo '<p class="description">';
    esc_html_e( 'You have to input the tempID(sandbox) for persona verify! To execute production mode, go to ', 'persona' ); 
    echo '<a href="http://vladitour.com/wp-persona-verify/" target="_blank" >Pro Version</a>';
    echo '</p>';
}


function persona_verify_menu_page_html(){
// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    return;
    }
    
    // add error/update messages
    
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved Successfully!', 'persona_manage_setting' ), 'updated' );
    }
    
    // show error/update messages
    settings_errors( 'wporg_messages' );

    ?>

    <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
    <?php
    // output security fields for the registered setting "persona"
    settings_fields( 'persona' );
    // output setting sections and their fields
    // (sections are registered for "persona", each field is registered to a specific section)
    do_settings_sections( 'persona' );
    // output save settings button
    submit_button( 'Save Settings' );
    ?>
    </form>
    </div>
    <?php
   }