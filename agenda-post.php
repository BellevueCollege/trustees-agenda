<?php
/*
Plugin Name: Board of trustees Agenda
Plugin URI: https://github.com/BellevueCollege/trustees-adenda
Description: This plugin registers the 'Agenda' post type 
Author: Bellevue College Technology Development and Communications
Version: 1.1
Author URI: http://www.bellevuecollege.edu
*/

require_once('template.php'); 

add_action( 'init', 'create_agenda_post_type' );
function create_agenda_post_type() {
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
    									'menu_name' => 'Agenda Archive',
    								),
      'public' 				=> true,
      'supports'            => array( 'title', 'editor', 'comments','page-attributes',),
      'has_archive' 		=> 'agendas',         
      'capability_type' 	=> 'page',
      'rewrite' 			=> array( 'slug' => "agendas" ),   
    )
  );
}

function agendas_rewrite_flush() {
	// First, we "add" the custom post type via the above written function.
	// Note: "add" is written with quotes, as CPTs don't get added to the DB,
	// They are only referenced in the post_type column with a post entry,
	// when you add a post of this CPT.

	// Both the custom post type and the custom taxonomy need to be called in this instance
	create_agenda_post_type();

	// ATTENTION: This is *only* done during plugin activation hook in this example!
	// You should *NEVER EVER* do this on every page load!!
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'agendas_rewrite_flush' );

// Add the Meta Box
function add_agenda_custom_meta_box() {
    add_meta_box(
        'custom_meta_box', // $id
        'Custom Meta Box', // $title 
        'show_agenda_custom_meta_box', // $callback
        'agendas', // $page
        'normal', // $context
        'high'); // $priority
    
    wp_enqueue_script( 'jquery-ui-datepicker' );
    $path = plugin_dir_path( __FILE__ );
    //echo $path;
    wp_register_script( 'agenda-script', plugins_url( '/agenda.js', __FILE__ ) );
    wp_enqueue_script( 'agenda-script');
    wp_enqueue_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);

    wp_register_style( 'agenda-style', plugins_url( '/agenda-style.css', __FILE__ ) );
    wp_enqueue_style( 'agenda-style');

}
add_action('add_meta_boxes', 'add_agenda_custom_meta_box');



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
function show_agenda_custom_meta_box() {
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
									
						    			echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" class="meeting_date" size="30" />
						        		<br /><span class="description error_text">'.$field['desc'].'</span>';
						    	}
						break;

                } //end switch
        echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
}

// Save the Data
function save_agendas($post_id) {
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
            // if($field['id'] == "meeting_date" && empty($_POST[$field['id']]))
            // {
            //   error_log("meeting date empty");
            //   $_POST['post_status'] = "draft";
            // }
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
add_action('save_post_agendas', 'save_agendas',10); 



function save_agendas_post_name($post_id)
{
   
    if(isset($post_id) && !empty($post_id))
    {
        $post = get_post($post_id);
        $meeting_date = get_post_meta($post_id, 'meeting_date', true); 
        $post_title = get_the_title($post_id);
        $post_type = get_post_type($post_id);
        //error_log("post type:". $post_type);
        if(isset($meeting_date) && !empty($meeting_date) && isset($post_type) && $post_type == "agendas") 
        {  
            $post_name = sanitize_title($meeting_date);            

           
        }
        else if(!empty($post_title))
        {
             $post_name = sanitize_title($post_title); 
        }
         // update the post, which calls save_post again
            $update_post = array( 'ID' => $post_id, 'post_name' => $post_name);
           //if($post->post_name !== $post_name  )
            if(!strstr($post->post_name, $post_name)) // Checks if date exists in original postname
            {
                // unhook this function so it doesn't loop infinitely
                remove_action( 'save_post_agendas', 'save_agendas_post_name' );
                $update_return_value = wp_update_post( $update_post);                
                // re-hook this function
                add_action( 'save_post_agendas', 'save_agendas_post_name' );               
            }
    }   
   
}
add_action('save_post_agendas', 'save_agendas_post_name',20); 


function get_custom_post_type_single_agenda_template($single_template) {
     global $post;
    
     $meeting_date = get_post_meta($post->post_id, 'meeting_date', true);
     $meeting_date = sanitize_title($meeting_date);
     if ($post->post_type == 'agendas' || $post->post_type == $meeting_date) {       
          $single_template = dirname( __FILE__ ) . '/single-agendas.php';
     }
     return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_single_agenda_template' );


function get_custom_post_type_archive_agenda_template($archive_template) {
     global $post;   
     $meeting_date = get_post_meta($post->post_id, 'meeting_date', true);
     $meeting_date = sanitize_title($meeting_date);
     if ($post->post_type == 'agendas' || $post->post_type == $meeting_date) {       
          $archive_template = dirname( __FILE__ ) . '/archive-agendas.php';
     }
     return $archive_template;
}
add_filter( 'archive_template', 'get_custom_post_type_archive_agenda_template' );
