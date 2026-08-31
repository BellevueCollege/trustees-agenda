<?php
/*
Plugin Name: Board of Trustees Agenda
Plugin URI: https://github.com/BellevueCollege/trustees-agenda
Description: This plugin registers the 'Agenda' post type
Author: Bellevue College Information Technology Services
Version: 1.4.1
Author URI: http://www.bellevuecollege.edu
GitHub Plugin URI: bellevuecollege/trustees-agenda
*/

//* WARNING: ACF must be installed and active for this Post Type to appear as it is no longer a WP CPT

// changing default save/load location to acf plugin folder
add_filter('acf/settings/save_json', 'trustees_acf_json_save_point');
function trustees_acf_json_save_point( $path ) {
    return plugin_dir_path( __FILE__ ) . 'acf-json';
}

add_filter('acf/settings/load_json', 'trustees_acf_json_load_point');
function trustees_acf_json_load_point( $paths ) {
	// Remove original path and add plugin path
    $paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';
    return $paths;
}

//Killing theme's default agenda so it doesn't hijack plugin
add_action( 'init', 'trustees_remove_ghost_post_type', 99 );
function trustees_remove_ghost_post_type() {
    if ( post_type_exists( 'agenda' ) ) {
        unregister_post_type( 'agenda' );
    }
}

//auto-rename post based on meeting date
// priority 20 ensures ACF has saved data first
add_action( 'acf/save_post', 'save_agendas_post_rename', 20 );

function save_agendas_post_rename( $post_id ) {
	//safety check- If this is a revision, autosave, or we are trashing the post, STOP.
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// deletion or trash = do not run
	if ( get_post_status( $post_id ) === 'trash'|| get_post_status( $post_id ) === 'auto-draft' ) {
        return;
    }

	if ( get_post_type( $post_id ) != 'agendas' ) {
		return;
	}

	//Get date
	$meeting_date = get_field( 'meeting_date', $post_id );

	// this should rarely be empty, but keep this just in case
	if ( empty( $meeting_date ) ) {
        return;
    }
	
	// ensure slug always uses same format
		// ACF saves dates as Ymd (e.g. 20240210), old plugin used Y-m-d.
		if( is_numeric( $meeting_date ) ) {
			$slug_date = date( 'Y-m-d', strtotime( $meeting_date ) );
		} else {
			$slug_date = $meeting_date;
		}

		$new_slug = sanitize_title( $slug_date );

// Only update if the current slug doesn't match the new date
$current_agenda = get_post( $post_id );
if ( $current_agenda->post_name === $new_slug ) {
	return;
}

//Safe Update
remove_action( 'acf/save_post', 'save_agendas_post_rename', 20 );

wp_update_post( array(
	'ID' => $post_id,
	'post_name' => $new_slug
));

add_action( 'acf/save_post', 'save_agendas_post_rename', 20 );
}

/*
 * ADMIN COLUMNS
 */
//Add Sortable Column to Agendas Admin Dashboard
add_filter( 'manage_edit-agendas_columns', 'add_new_agenda_columns' );

function add_new_agenda_columns($agenda_columns) {
	$agenda_columns = array (
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Title' ),
		'meeting_date' => __( 'Meeting Date' ),
		'special' => __( 'Special Meeting' )
	);
	return $agenda_columns;
}

add_action( 'manage_agendas_posts_custom_column', 'my_manage_agenda_columns', 10, 2 );

function my_manage_agenda_columns( $column, $post_id ) {
	switch( $column ) {

		/* If displaying the 'meeting_date' column. */
		case 'meeting_date' :

			/* Get the post meta. */
			$meeting_date = get_post_meta( $post_id, 'meeting_date', true );

			/* If no date is found, output a default message. */
			if ( !empty( $meeting_date ) ){
				// Determine if Ymd (ACF) or Y-m-d (Old)
				echo date('F j, Y', strtotime($meeting_date));
			}
			else{
				echo __( 'Unknown' );
			}
			break;

		/* If displaying the 'special' column. */
		case 'special' :

			/* Get the post meta. */
			$special = get_post_meta( $post_id, 'special_meeting', true );

			// Check for '1' (ACF True) or 'on' (Old Checkbox)
			if ( $special == '1' || $special == 'on' ) 
				echo __( 'Yes' );
			else 
				echo __( 'No' );
			break;
	}
}

// SORTABLE COLUMNS

add_filter( 'manage_edit-agendas_sortable_columns', 'my_agenda_sortable_columns' );

function my_agenda_sortable_columns( $columns ) {

	$columns['meeting_date'] = 'meeting_date';

	return $columns;
}

/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'my_edit_agenda_load' );

function my_edit_agenda_load() {
	add_filter( 'request', 'my_sort_agendas' );
}

/* Sorts the agendas. */
function my_sort_agendas( $vars ) {

	/* Check if we're viewing the 'agendas' post type. */
	if ( isset( $vars['post_type'] ) && 'agendas' == $vars['post_type'] ) {

		/* Check if 'orderby' is set to 'meeting_date'. */
		if ( isset( $vars['orderby'] ) && 'meeting_date' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'meeting_date',
					'orderby' => 'meta_value'
				)
			);
		}
	}

	return $vars;
}


/*
 * WIDGET to Display Upcoming Agendas
 */

// Creating the widget
class trustees_agenda_recent_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'agendas_widget',

			// Widget name will appear in UI
			__('Most Recent Agenda', 'agenda_widget'),

			// Widget description
			array( 'description' => __( 'Displays the most recent agenda', 'agenda_widget' ), )
		);
	}

	// Creating widget front-end, This is where the action takes place
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// Pull Posts
		$agendas = new WP_Query();
		$agendas->query( 'post_type=agendas&order=desc&orderby=meta_value&meta_key=meeting_date&posts_per_page=1' );
		if ( $agendas->found_posts > 0 ) {
			echo '<ul class="agendas_widget">';
				while ( $agendas->have_posts() ) {
					$agendas->the_post();
					$meeting_date_value = get_post_meta( get_the_ID(), 'meeting_date', true );
					$is_special_meeting = get_post_meta( get_the_ID(), 'special_meeting', true );
					$listItem = '<li>';
					$listItem .= '<a href="' . get_permalink() . '">';
					$listItem .= 'Agenda for the ';
					$listItem .= date('F j, Y', strtotime($meeting_date_value));
					if ($is_special_meeting) {
						$listItem .= ' Special';
					}
					$listItem .= ' Meeting</a></li>';
					echo $listItem;
				}
			echo '</ul>';
			wp_reset_postdata();
		} else {
			echo '<p style="padding:25px;">No listing found</p>';
		}

		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		$title = isset($instance['title']) ? $instance['title'] : __( 'New title', 'agenda_widget' );
		// Widget admin form
		?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}

// Register and load the widget
function agendas_load_widget() {
	register_widget( 'trustees_agenda_recent_widget' );
}
add_action( 'widgets_init', 'agendas_load_widget' );
?>