<?php 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
exit;

// Creation du widget ASIN
class puipui_dbgt_widget extends WP_Widget {
		
	function __construct() {
		parent::__construct(
		 
		// Base ID of your widget
		'puipui_dbgt_widget', 
		  
		// Widget name will appear in UI
		__('.: Smart Gallery DBGT :.', 'puipui_dbgt_widget'), 
		  
		// Widget description
		array( 'description' => __( 'Mini Gallery Intelligente', 'puipui_dbgt_widget' ), ) 
		);
	}
	  
	  
	// Creating widget front-end
	public function widget( $args, $instance ) {
		$title = 		apply_filters( 'widget_title', $instance['title'] );
		$keyword = 		apply_filters( 'widget_title', $instance['keyword'] );  
		$number = 		apply_filters( 'widget_title', $instance['number'] );  
		$imagesize = 	apply_filters( 'widget_title', $instance['imagesize'] ); 
		$legal = 		apply_filters( 'widget_title', $instance['legal'] ); 

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if (! empty( $title ) ) { echo $args['before_title'] . $title . $args['after_title'];}

		// This is where you run the code and display the output
		echo do_shortcode( "[smartgallery_dbgt keyword='$keyword' number='$number' sidebar='oui' imagesize='$imagesize' legal='$legal']" );

		echo $args['after_widget'];
	}
		   
		   
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			
			$title = $instance[ 'title' ];
			
		}
		else {
			
			$title = __( 'Mini Gallery', 'puipui_dbgt_widget' );
			
		}
		$keyword 	= 	isset($instance[ 'keyword' ]) ? $instance[ 'keyword' ] : '';
		$number 	= 	isset($instance[ 'number' ]) ? $instance[ 'number' ] : '';
		$imagesize 	= 	isset($instance[ 'imagesize' ]) ? $instance[ 'imagesize' ] : '' ;
		$legal 		= 	isset($instance[ 'legal' ]) ? $instance[ 'legal' ] : '';
		
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'keyword' ); ?>">Keyword</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'keyword' ); ?>" name="<?php echo $this->get_field_name( 'keyword' ); ?>" type="text" value="<?php echo esc_attr( $keyword ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>">Number</label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>">
			<?php
			// Your options array
			$options = array(
				"1" => __( "1", "1" ),
				"2" => __( "2", "2" ),
				"3" => __( "3", "3" ),
				"4" => __( "4", "4" ),
				"5" => __( "5", "5" ),
				"6" => __( "6", "6" ),
				"7" => __( "7", "7" ),
				"8" => __( "8", "8" ),
				"9" => __( "9", "9" ),
				"10" => __( "10", "10" ),
			);
			// Loop through options and add each one to the select dropdown
			foreach ( $options as $key => $name ) {
				echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $number, $key, false ) . '>'. $name . '</option>';
			} ?>
		</select>
		</p>
		<p <?php $smartgallery_dbgt_library = get_option('puipui_dbgt_form_option_library'); if ($smartgallery_dbgt_library != "pixabay") {echo "style='display:none;'"; }?>>
			<label for="<?php echo $this->get_field_id( 'imagesize' ); ?>">Image Size</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'imagesize' ); ?>" name="<?php echo $this->get_field_name( 'imagesize' ); ?>">
					<?php
					// Your options array
					$options = array(
						'default' => __( 'Default', 'default' ),
						'thumb' => __( 'Thumb', 'thumb' ),
						'medium' => __( 'Medium', 'medium' ),
						'large' => __( 'Large', 'large' ),
					);
					// Loop through options and add each one to the select dropdown
					foreach ( $options as $key => $name ) {
						echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $imagesize, $key, false ) . '>'. $name . '</option>';
					} ?>
			</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'legal' ); ?>">Mentions Légales</label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'legal' ); ?>" name="<?php echo $this->get_field_name( 'legal' ); ?>">
				<?php
				// Your options array
				$options = array(
					'default' => __( 'Default', 'default' ),
					'yes' => __( 'Yes With Backlink', 'yes' ),
					'yesbis' => __( 'Yes No Backlink', 'yesbis' ),
					'no' => __( 'No', 'no' ),
				);
				// Loop through options and add each one to the select dropdown
				foreach ( $options as $key => $name ) {
					echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $legal, $key, false ) . '>'. $name . '</option>';
				} ?>
		</select>
		</p>
		
		<?php 
	}
		  
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['keyword'] = ( ! empty( $new_instance['keyword'] ) ) ? strip_tags( $new_instance['keyword'] ) : '';
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : '';
		$instance['imagesize'] = ( ! empty( $new_instance['imagesize'] ) ) ? strip_tags( $new_instance['imagesize'] ) : '';
		$instance['legal'] = ( ! empty( $new_instance['legal'] ) ) ? strip_tags( $new_instance['legal'] ) : '';
		return $instance;
	}
} 

// Register and load the widget
function puipui_dbgt_load_widget() {
    register_widget( 'puipui_dbgt_widget' );
}
add_action( 'widgets_init', 'puipui_dbgt_load_widget' );