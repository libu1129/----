<?php

function bravepop_enqueue_front_scripts() {
	if ( !is_admin() ) {
      wp_register_script( 'bravepop_front_js', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/brave.js' ,'','',true);

      $verbs = array(
         'loggedin' => is_user_logged_in() ? 'true' : 'false',
         'isadmin' => current_user_can('activate_plugins') ? 'true' : 'false',
         'referer' => wp_get_referer(),
         'security' => wp_create_nonce('brave-ajax-nonce'),
         'goalSecurity' => wp_create_nonce('brave-ajax-goal-nonce'),
         'ajaxURL' => esc_url(admin_url( 'admin-ajax.php' )),
         'field_required' => __( 'Required', 'bravepop' ),
         'no_html_allowed' => __( 'No Html Allowed', 'bravepop' ),
         'invalid_number' => __( 'Invalid Number', 'bravepop' ),
         'invalid_email' => __( 'Invalid Email', 'bravepop' ),
         'invalid_url' => __( 'Invalid URL', 'bravepop' ),
         'invalid_date' => __( 'Invalid Date', 'bravepop' ),
         'fname_required' => __( 'First Name is Required.', 'bravepop' ),
         'fname_required' => __( 'First Name is Required.', 'bravepop' ),
         'lname_required' => __( 'Last Name is Required.', 'bravepop' ),
         'username_required' => __( 'Username is Required.', 'bravepop' ),
         'email_required' => __( 'Email is Required.', 'bravepop' ),
         'email_invalid' => __( 'Invalid Email addresss.', 'bravepop' ),
         'pass_required' => __( 'Password is Required.', 'bravepop' ),
         'pass_short' => __( 'Password is too Short.', 'bravepop' ),
         'login_error' => __( 'Something Went Wrong. Please contact the Site administrator.', 'bravepop' ),
         'pass_reset_success' => __( 'Please check your Email for the Password reset link.', 'bravepop' ),
      );
      wp_localize_script( 'bravepop_front_js', 'bravepop_global', $verbs );
      wp_enqueue_script('bravepop_front_js');

      //ENQEUE STYLE 
		wp_enqueue_style('bravepop_front_css',  BRAVEPOP_PLUGIN_PATH . 'assets/css/frontend.min.css');
	}
}
add_action('wp_enqueue_scripts', 'bravepop_enqueue_front_scripts');


add_action( 'wp_head', 'bravepop_popupjs_vars' );
function bravepop_popupjs_vars() { 
   print_r('<script> var brave_popup_data = {};  var brave_popup_videos = {};  var brave_popup_formData = {};</script>');
}