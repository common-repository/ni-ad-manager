<?php
/*
Plugin Name: Display Ad Manager Ads
Plugin URI: http://www.niblettindustries.com
Description: Insert the Ad Manager Ad on the website
Author: Niblett Industries
Version: 0.1
Author URI: http://www.niblettindustries.com/
*/


	/* add our function to the widgets_init hook. */
	add_action( 'widgets_init', 'ad_manager_load_widgets' );

	/* function that registers the widget. */
	function ad_manager_load_widgets() {

		# register widget
		register_widget( 'ad_manager_widget' );

	# close function ad_manager_load_widgets() {
	}

	# start ad_manager widget
	class ad_manager_widget extends WP_Widget {

		# setup the widget
		function ad_manager_widget() {

			# widget control options
			$widget_ops = array('classname' => '', 'description' => __('Ad Manager'));
			$control_ops = array('width' => 210, 'height' => 220);
			$this->WP_Widget('ad_manager_widget', __('Ad Manager'), $widget_ops, $control_ops);

		# close function ad_manager_widget() {
		}

		# this function displays the content on the page
		function widget( $args, $instance ) {

			# extract instance values
			extract( $instance );

			# output array
			$output = array();

			# get posts
			$args = array(
				'numberposts'	=>	-1,
				'post_type'		=>	'ad_manager_ads',
				'post_status'	=>	'publish',
				'tax_query'		=>	array( array( 'taxonomy' => 'ad_size', 'field' => 'id', 'terms' => $ad_type ) ),
			);

			# get posts
			$posts = get_posts( $args );

			# loop through the posts
			foreach( $posts as $post ) {

				# add to array
				$output[] = $post->ID;

				# if ignore_weights is not set
				if( $ignore_weights != 'true' ) {

					# get weight
					$ad_weight = get_post_meta( $post->ID, 'ad_weight', true );

					# loop through weight
					for( $x=0; $x<$ad_weight; $x++ ) {

						# add to output
						$output[] = $post->ID;

					# close for( $x=0; $x<$ad_weight; $x++ ) {
					}

				# close if( $ignore_weights != 'true' ) {
				}

			# close foreach( $posts as $post ) {
			}

			# count items in array & select ad
			$total 		 = count( $output ) - 1;
			$selected_ad = $output[ rand( 0, $total ) ];

			# update ad load count
			$count = get_post_meta( $post->ID, 'ad_loads', true ) + 1;
			$image = get_the_post_thumbnail( $post->ID );
			$nonce = wp_create_nonce( 'ad_manager_ad' );
			$path  = add_query_arg( array( 'redir_id' => $selected_ad, 'verify' => $nonce ), get_permalink( get_option( 'ad_manager_ads', "" ) ) );
			update_post_meta( $post->ID, 'ad_loads', $count );

			# print result
			echo "<a href=\"". $path ."\" target=\"_blank\">". $image ."</a>\n";

		# close function widget( $args, $instance ) {
		}

		# this function will update each instance on the widget page
		function update( $new_instance, $old_instance ) {

			# start new array value
			$instance = $old_instance;

			# strip tags (if needed) and update the widget array values.
			$instance[ 'ad_type' ] 		  = strip_tags( $new_instance[ 'ad_type' ] );
			$instance[ 'ignore_weights' ] = strip_tags( $new_instance[ 'ignore_weights' ] );

			# return instance value
			return $instance;

		# close function update( $new_instance, $old_instance ) {
		}

		# this function will display all the options on the widget back-end page
		function form( $instance ) {

			/* Set up some default widget settings. */
			$defaults = array(  );
			$instance = wp_parse_args( (array) $instance, $defaults );
?>
  <p>
    <label for="<?php echo $this->get_field_id( 'ad_type' ); ?>"><strong>Ad Type</strong>:</label><br />
    <select name="<?php echo $this->get_field_name( 'ad_type' ); ?>" id="<?php echo $this->get_field_id( 'ad_type' ); ?>">
<?php
			# get terms
			$terms = get_terms( 'ad_size', array( 'orderby' => 'name', 'hide_empty' => true ) );

			# loop through each term
			foreach ( $terms as $term ) {

				# if term id is set to instance
				if( $instance[ 'ad_type' ] == $term->term_id ) {

					# print each term
					echo "<option value=\"". $term->term_id ."\" selected=\"selected\">" . $term->name . "</option>\n";

				# otherwise
				} else {

					# print each term
					echo "<option value=\"". $term->term_id ."\">" . $term->name . "</option>\n";

				# close if( $instance[ 'ad_type' ] == $term->term_id ) {
				}

			# close foreach ( $terms as $term ) {
			}
?>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id( 'ignore_weights' ); ?>"><strong>Ignore Ad Weights</strong>: <input type="checkbox" name="<?php echo $this->get_field_name( 'ignore_weights' ); ?>" id="<?php echo $this->get_field_id( 'ignore_weights' ); ?>" value="true"<? echo $instance[ 'ignore_weights' ] == "true" ? " checked=\"checked\"" : ""; ?> /></label>
  </p>
<?php
		# close function form( $instance ) {
		}

	# close class ad_manager_widget extends WP_Widget {
	}
?>