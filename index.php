<?php
/*
Plugin Name: Aistore Contest System
Version:  1.0
Plugin URI: #
Author: susheelhbti
Author URI: http://www.aistore2030.com/
Description: Aistore Contest System 


*/


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


add_action('init', 'aistorecontest_wpdocs_load_textdomain');



function aistorecontest_wpdocs_load_textdomain()
{
    load_plugin_textdomain('aistore', FALSE, basename(dirname(__FILE__)) . '/languages/');
}


function aistore_contest_scripts_method()
{
     
 


 
    //   wp_enqueue_style( 'aistore', get_stylesheet_directory_uri() . '/css/custom.css' );
   wp_enqueue_style('aistore', plugins_url('/css/custom.css', __FILE__), array('style'),'',true);
    wp_enqueue_script('aistore', plugins_url('/js/custom.js', __FILE__), array(
        'jquery'
    ));
}




add_action('wp_enqueue_scripts', 'aistore_contest_scripts_method');

function themeslug_enqueue_style() {
    wp_enqueue_style( 'saksh_contents', plugins_url('/css/custom.css', __FILE__), false );
}
 
function themeslug_enqueue_script() {
    wp_enqueue_script( 'saksh_contents', dirname(__FILE__) .'/js/custom.js', false );
}
 
add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_script' );





function aistorecontest_isadmin()
{
    
    $user          = wp_get_current_user();
    $allowed_roles = array(
        'administrator'
    );
    if (array_intersect($allowed_roles, $user->roles)) {
        return true;
    } else {
        
        return false;
        
    }
}
 

function aistore_plugin_contest_table_install()
{
    global $wpdb;
    
    
    
 
  
    
    
    
    $table_contest_documents = "CREATE TABLE   IF NOT EXISTS  " . $wpdb->prefix . "contest_documents (
  id int(100) NOT NULL  AUTO_INCREMENT,
  eid  int(100) NOT NULL,
  documents  varchar(100)  NOT NULL,
   created_at  timestamp NOT NULL DEFAULT current_timestamp(),
   user_id  int(100) NOT NULL,
  documents_name  varchar(100)  DEFAULT NULL,
  PRIMARY KEY (id)
)  ";
    
    
    $table_contest_system = "CREATE TABLE   IF NOT EXISTS  " . $wpdb->prefix . "contest (
  id int(100) NOT NULL AUTO_INCREMENT, 
  title varchar(100)   NOT NULL,
  term_condition text ,
  amount int(100) NOT NULL,
  user_id int(100)   NOT NULL,
  contest_fee int(100) NOT NULL,
  status varchar(100)   NOT NULL DEFAULT 'pending',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  
  
  PRIMARY KEY (id)
)  ";
     
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    
    dbDelta($table_contest_documents);
    
    dbDelta($table_contest_system);
   


}
register_activation_hook(__FILE__, 'aistore_plugin_contest_table_install');

//include_once dirname(__FILE__) . '/css/custom.css';
include_once dirname(__FILE__) . '/notification.php';


include_once dirname(__FILE__) . '/AistoreContest.class.php';
include_once dirname(__FILE__) . '/AistoreContestSettingsPage.class.php';


add_shortcode('aistore_contest', array(
    'AistoreContest',
    'aistore_contest'
));

add_shortcode('contest_list', array(
    'AistoreContest',
    'contest_list'
));

add_shortcode('aistore_contest_list', array(
    'AistoreContest',
    'aistore_contest_list'
));

add_shortcode('aistore_contest_list_page', array(
    'AistoreContest',
    'aistore_contest_list_page'
));

add_shortcode('aistore_contest_detail', array(
    'AistoreContest',
    'aistore_contest_detail'
));


 