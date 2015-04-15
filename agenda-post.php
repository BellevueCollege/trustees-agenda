<?php
/*
Plugin Name: trustees-adenda
Plugin URI: https://github.com/BellevueCollege/trustees-adenda
Description: This plugin registers the 'Agenda' post type 
Author: Bellevue College Technology Development and Communications
Version: 1.0.0.0
Author URI: http://www.bellevuecollege.edu
*/

require_once('template.php'); 

add_action( 'init', 'create_post_type' );
function create_post_type() {
	//global $post;
	//$meta = "";
	///if($post)
	 	//$meta = get_post_meta($post->ID, 'meeting_date', true);
  register_post_type( 'agendas',
    array(
      'labels'				=> array(
      									'name' => __( 'agenda' ),
      									'singular_name' => __( 'agenda' ) ,
    									'add_new' => 'Add New Agenda',
    									'add_new_item' => 'Add New Agenda',
    									'edit_item' => 'Edit Agenda',
    									'menu_name' => 'agenda',
    								),
      'public' 				=> true,
      'supports'            => array( 'title', 'editor', 'comments','page-attributes',),
      'has_archive' 		=> true,   
      'taxonomies'			=>  array( 'post_tag', 'Agendas' ),	
      'capability_type' 	=> 'page',
      'rewrite' 			=> false,//array( 'slug' => "agendas" ),   
    )
  );
}


// Add the Meta Box
function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box', // $id
        'Custom Meta Box', // $title 
        'show_custom_meta_box', // $callback
        'agendas', // $page
        'normal', // $context
        'high'); // $priority
    
    wp_enqueue_script( 'jquery-ui-datepicker' );
    $path = plugin_dir_path( __FILE__ );
    //echo $path;
    wp_register_script( 'agenda-script', plugins_url( '/agenda.js', __FILE__ ) );
    wp_enqueue_script( 'agenda-script');
    wp_enqueue_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);

}
add_action('add_meta_boxes', 'add_custom_meta_box');



//$prefix = 'custom_';
$custom_meta_fields = array(
  
    array(
        'label'=> 'Meeting Category',
        'desc'  => 'Special Meeting',
        'name'  =>  'meeting_type',
        'id'    =>  'special_meeting',
        'type'  => 'checkbox'
    ),
    array(
        'label'=> 'Date of Meeting',
        'desc'  => '',
        'name'  => 'meeting_date',       
        'id'    => 'meeting_date',
        'type'  => 'text'
    ),
    
);

// The Callback
function show_custom_meta_box() {
global $custom_meta_fields, $post;
 
// Use nonce for verification
echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
     
    // Begin the field table and loop
    echo '<table class="form-table">';
    foreach ($custom_meta_fields as $field) {
        // get value of this field if it exists for this post
        $meta = get_post_meta($post->ID, $field['id'], true);
        // begin a table row with
        echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                    // case items will go here
                		case 'checkbox':
							    echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
							        <label for="'.$field['id'].'">'.$field['desc'].'</label>';
								break;
						// text
						case 'text':
								if($field['id'] == "meeting_date")
								{
									//$meeting_date = get_post_meta()
						    			echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="meeting_date" size="30" />
						        		<br /><span class="description">'.$field['desc'].'</span>';
						    	}
						break;

                } //end switch
        echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
}

// Save the Data
function save_custom_meta($post_id) {
    global $custom_meta_fields;
    
    // verify nonce
    if(isset($_POST['custom_meta_box_nonce']))
    {
    	if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) 
        	return $post_id;
    }
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // check permissions
    if(isset($_POST['post_type']))
    {
	    if ('page' == $_POST['post_type']) {
	        if (!current_user_can('edit_page', $post_id))
	            return $post_id;
	        } elseif (!current_user_can('edit_post', $post_id)) {
	            return $post_id;
	    }
	}
     
    // loop through fields and save the data
    foreach ($custom_meta_fields as $field) {
    	if(isset($field['id']))
    	{
	        $old = get_post_meta($post_id, $field['id'], true);
	        if(isset($_POST[$field['id']]))
	        {
		        $new = $_POST[$field['id']];
		        if ($new && $new != $old) {
		            update_post_meta($post_id, $field['id'], $new);
		        } elseif ('' == $new && $old) {
		            delete_post_meta($post_id, $field['id'], $old);
		        }
		    }
	    }
    } // end foreach
}
add_action('save_post', 'save_custom_meta');

// change slug to include date in the permalink of a post
 
// function tdd_add_rewrite_rules() 
// {
 
// // Register custom rewrite rules
 
// global $wp_rewrite;
// $wp_rewrite->add_rewrite_tag('%agendas%', '([^/]+)', 'agendas=');
// $wp_rewrite->add_rewrite_tag('%post_title%', '([^/]+)', 'post_title=');
 
// $wp_rewrite->add_permastruct('agendas', '/agendas/%post_title%', false);
// //$wp_rewrite->flush_rules( false );
 
// }
// add_filter('post_type_link', 'tdd_permalinks', 10, 3);
 
// function tdd_permalinks($permalink, $post, $leavename) 
// {
 
// $no_data = 'no-date';
 
// $post_id = $post->ID;
// error_log("permalink:".$permalink);
 
// if($post->post_type != 'agendas' || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) 
// return $permalink;
 
// $var1 = get_post_meta($post_id, 'meeting_date', true);
// //$post_title = the_title();
 
// $var1 = sanitize_title($var1);
 
// if(!$var1) { $var1 = $no_data; }
 
// $permalink = str_replace('%post_title%', $var1, $permalink);
 
// return $permalink;
 
// } 
// add_action('init', 'tdd_add_rewrite_rules');



// display single post

function get_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == 'agendas') {
          $single_template = dirname( __FILE__ ) . '/single-agendas.php';
     }
     return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );



?>

