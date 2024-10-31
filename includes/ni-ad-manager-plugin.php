<?php
	# start new class
	$AdManager = new AdManager();

	# create class
	class AdManager {

		# public variables
		public $plugin_path;

		/**
		 * PHP5 constructor method.
		 * @since 0.1
		 */
		function __construct() {

			# plugin page
			$this->plugin_path = ABSPATH . 'wp-content/plugins/ni-ad-manager/';

			# check if we can enable theme support
			if( function_exists( 'add_theme_support' ) ) {

				# enable thumbnail support
				add_theme_support( 'post-thumbnails', array( 'ad_manager_ads' ) );

			# close if( function_exists( 'add_theme_support' ) ) {
			}

			# plugin startup
			add_action( 'init', array( &$this, 'ad_manager_setup' ) );

			# load into startup
			add_action( 'init',  array( &$this, 'load_headers' ) );

			# register post type & taxonomy [ad_manager_ads]
			add_action( 'init', array( &$this, 'ad_manager_post_type' ) );
			add_action( 'init', array( &$this, 'ad_manager_post_type_taxonomy' ) );

			# add meta box
			add_action( 'add_meta_boxes', array( &$this, 'ad_manager_meta_box' ) );
			add_action( 'save_post', array( &$this, 'ad_manager_meta_box_save' ) );

			# add filter
			add_action( 'init', array( &$this, 'change_ad_manager_ads_column_headings' ) );
			add_action( 'manage_ad_manager_ads_posts_custom_column', array( &$this, 'change_ad_manager_ads_column_values' ), 10, 2 );

			# add Shortcode
			add_shortcode( 'adManager', array( &$this, 'adManager_Redirect' ) );

			# add a plugin settings page 
			add_action( 'admin_menu', array( &$this, 'settings_menu' ) );


		# close function __construct() {
		}


		/**
		 * plugin startup sequence
		 * @since 0.1
		 */
		function ad_manager_setup() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# if there isn't a 'redirect' page setup already
				if( get_option( 'ad_manager_ads', "" ) == "" ) {

					# insert new page
					$new_page = wp_insert_post( array( 'post_author' => '1', 'post_title' => 'Ad Manager Redirect Page', 'post_content' => '[adManager][/adManager]', 'post_status' => 'publish', 'comment_status' => 'open', 'ping_status' => 'open', 'post_name' => 'redirect', 'post_parent' => '0', 'menu_order' => '1', 'post_type' => 'page' ) );

					# create option
					update_option( 'ad_manager_ads', $new_page );

				# close if( get_option( 'ad_manager_ads', "" ) == "" ) {
				}

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_post_type() {
		}


		/**
		 * plugin load headers
		 * @since 0.1
		 */
		function load_headers() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# if this is the admin section
				if( is_admin() ) {

					# register & enqueue events.css
					wp_register_style( 'ad-manager-css', site_url( '/' ) . "wp-content/plugins/ni-ad-manager/ad-manager.css" );
					wp_enqueue_style( 'ad-manager-css' );

				# close if( !is_admin() ) {
				}

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_post_type() {
		}


		/**
		 * register new post type {ad_manager_ads}
		 * @since 0.1
		 */
		function ad_manager_post_type() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# args
				$args = array(
					'label'					=>	'Ad Manager',
					'labels'				=>	array( 'name' => __( 'Ad Manager' ), 'singular_name' => __( 'Ad Manager' ), 'add_new' => __( 'Add Advertisment' ), 'all_items' => __( 'View Advertisments' ), 'add_new_item' => __( 'Add Advertisment' ), 'edit_item' => __( 'Edit Advertisment' ), 'new_item' => __( 'New Advertisment' ), 'view_item' => __( 'View Advertisments' ), 'search_item' => __( 'Search Advertisments' ), 'not_found' => __( 'No Advertisments Found' ), 'not_found_in_trash' => __( 'No Advertisments in Trash' ), 'menu_name' => __( 'Ad Manager' ) ),
					'description'			=>	'Niblett Industries Ad Manager',
					'public' 				=>	true,
					'exclude_from_search' 	=>	true,
					'publicly_queryable'	=>	true,
					'show_ui'	 			=>	true,
					'show_in_nav_menus'		=>	false,
					'show_in_menu'			=>	true,
					'show_in_admin_bar'		=>	false,
					'menu_position'			=>	25,
					'supports'				=>	array( 'title', 'thumbnail' ),
					'has_archive' 			=>	true,
					'taxonomies' 			=>	array( 'ad_size' ),
				);

				# register post type
				register_post_type( 'ad_manager_ads', $args );

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_post_type() {
		}


		/**
		 * register new taxonomy {ad_size} and assign it strictly to ad_manager_ads
		 * @since 0.1
		 */
		function ad_manager_post_type_taxonomy() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# taxonomy labels
				$labels = array(
					'name' 				=> _x( 'Ad Types', 'taxonomy general name' ),
					'singular_name' 	=> _x( 'Ad Type', 'taxonomy singular name' ),
					'search_items' 		=> __( 'Search Ad Types' ),
					'all_items' 		=> __( 'All Ad Types' ),
					'parent_item' 		=> __( 'Parent Ad Type' ),
					'parent_item_colon' => __( 'Parent Ad Type:' ),
					'edit_item' 		=> __( 'Edit Ad Type' ), 
					'update_item' 		=> __( 'Update Ad Type' ),
					'add_new_item' 		=> __( 'Add New Ad Type' ),
					'new_item_name' 	=> __( 'New Ad Type' ),
					'menu_name' 		=> __( 'Ad Types' ),
				); 	

				# taxonomy args
				$args = array(
					'hierarchical' 	=> true,
					'labels'		=> $labels,
					'show_ui' 		=> true,
					'query_var' 	=> true,
				);

				# register post type
				register_taxonomy( 'ad_size', array( 'ad_manager_ads' ), $args );

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_post_type_taxonomy() {
		}


		/**
		 * register meta box for ad_manager_ads post type
		 * @since 0.1
		 */
		function ad_manager_meta_box() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# add a meta box to ad manager page
				add_meta_box( '_ad_manager_meta_box', __( 'Advertisment Options', 'advertisment-options' ), array( 'AdManager', 'ad_manager_meta_box_hook' ), 'ad_manager_ads', 'normal', 'default' );

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_meta_box() {
		}


		/**
		 * meta box for ad_manager_ads post type content
		 * @since 0.1
		 */
		function ad_manager_meta_box_hook( $post ) {

			# featured_post_noncename
			wp_nonce_field( plugin_basename(__FILE__), 'ad_options_noncename' );

			# post meta
			$ad_link   = get_post_meta( $post->ID, 'ad_link', true );
			$ad_weight = get_post_meta( $post->ID, 'ad_weight', true );
			$option_values = array( 0 => 'Low Priority', 10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 'Medium Priority', 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 'High Priority' );

			# if ad_weight isn't set
			if( !is_numeric( $ad_weight ) || $ad_weight < 0 || $ad_weight > 100 ) {

				# set ad weight to 50
				$ad_weight = 50;

			# close if( !is_numeric( $ad_weight ) || $ad_weight < 0 || $ad_weight > 100 ) {
			}

			# print options
			echo "<style type=\"text/css\">\n";
			echo " div.ad_manager_metabox_option { margin:0px; padding:10px 0px 0px 0px; }\n";
			echo " div.ad_manager_metabox_option p { margin:0px; padding:0px; }\n";
			echo " div.ad_manager_metabox_option input[type='text'] { width:99%; padding:3px; }\n";
			echo " div.ad_manager_metabox_option select { width:150px; padding:3px; }\n";
			echo "</style>\n\n";

			echo "<div class=\"ad_manager_metabox_option\">\n";
			echo "  <p><strong>Advertisment Link Path</strong></p>\n";
			echo "  <input type=\"text\" name=\"ad_link\" id=\"ad_link\" value=\"". $ad_link ."\" />\n";
			echo "</div>\n\n";

			echo "<div class=\"ad_manager_metabox_option\">\n";
			echo "  <p><strong>Advertisment Weight</strong></p>\n";
			echo "  <select name=\"ad_weight\" id=\"ad_weight\">\n";

			# loop through values
			for( $x=0; $x<=100; $x+=10 ) {

				# if x is set to ad_weight
				if( $x == $ad_weight ) {

					# print results
					echo "    <option value=\"". $x ."\" selected=\"selected\">". $option_values[ $x ] ."</option>\n";

				# otherwise
				} else {

					# print results
					echo "    <option value=\"". $x ."\">". $option_values[ $x ] ."</option>\n";

				# close if( $x == $ad_weight ) {
				}

			# close for( $x=0; $x<=100; $x+10 ) {
			}

			echo "  </select>\n";
			echo "</div>\n";

		# close function ad_manager_meta_box_hook() {
		}


		/**
		 * save meta box data for ad_manager_ads post type
		 * @since 0.1
		 */
		function ad_manager_meta_box_save( $post_id ) {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# verify this came from our screen and with proper authorization, because save_post can be triggered other times
				if( !wp_verify_nonce( $_POST[ 'ad_options_noncename' ], plugin_basename(__FILE__)  )) {

					# return post id
					return $post_id;

				# close if( !wp_verify_nonce( $_POST[ 'ad_options_noncename' ], plugin_basename(__FILE__) ) ) {
				}

				# don't do anything on auto-save
				if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {

					# return post id
					return $post_id;

				# close if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				}

				# update post meta
				update_post_meta( $post_id, 'ad_link', $_POST[ 'ad_link' ] );
				update_post_meta( $post_id, 'ad_weight', $_POST[ 'ad_weight' ] );

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function ad_manager_meta_box_save() {
		}


		/**
		 * update ad_manager_ads listing columns 
		 * @since 0.1
		 */
		function change_ad_manager_ads_column_headings() {

			# add filter
			add_filter('manage_edit-ad_manager_ads_columns', array( &$this, 'manage_edit_ad_manager_ads_columns' ) );

		# close function manage_edit_service_columns() {
		}


		/**
		 * set ad_manager_ads listing column headings
		 * @since 0.1
		 */
		function manage_edit_ad_manager_ads_columns() {

			# create an array of table columns
			$new_columns[ 'cb' ] 		= '<input type="checkbox" />';
			$new_columns[ 'title' ]		= _x( 'Title' );
			$new_columns[ 'ad_type' ]	= _x( 'Ad Type' );
			$new_columns[ 'image' ]		= _x( 'Image' );
			$new_columns[ 'ad_path' ]	= _x( 'Link Path' );
			$new_columns[ 'ad_weight' ]	= _x( 'Link Weight' );
			$new_columns[ 'ad_loads' ]	= _x( 'Ad Loads' );
			$new_columns[ 'ad_clicks' ]	= _x( 'Ad Clicks' );

			# return array of columns
			return $new_columns;

		# close function manage_edit_service_columns() {
		}


		/**
		 * update ad_manager_ads listing column data 
		 * @since 0.1
		 */
		function change_ad_manager_ads_column_values( $column_name, $id ) {

			# global
			global $wpdb;

			# switch column name
			switch( $column_name ) {

				# ad_type
				case "ad_type":

					# get category array
					$category = wp_get_post_terms( $id, 'ad_size' );

					# loop through categories
					for( $x=0; $x<count( $category ); $x++ ) {

						# add comma seperator if above 1
						if( $x > 0 ) {

							# add comma seperator
							echo ", ";

						# close if( $x > 0 ) {
						}

						# return value
						echo $category[$x]->name;

					# close for( $x=0; $x<count( $category ); $x++ ) {
					}

				break;

				# image
				case "image":

					# if this ad has an image
					if( has_post_thumbnail( $id ) ) {

						# print image
						echo get_the_post_thumbnail( $id, array( 150, 150 ) );

					# close if( has_post_thumbnail( $id ) ) {
					}

				break;

				# ad_path
				case "ad_path":
					# return value
					echo get_post_meta( $id, 'ad_link', true );
				break;

				# ad_weight
				case "ad_weight":
					# return value
					echo get_post_meta( $id, 'ad_weight', true );
				break;

				# ad_loads
				case "ad_loads":
					# return value
					echo is_numeric( get_post_meta( $id, 'ad_loads', true ) ) ? get_post_meta( $id, 'ad_loads', true ) : "0";
				break;

				# ad_clicks
				case "ad_clicks":
					# return value
					echo is_numeric( get_post_meta( $id, 'ad_clicks', true ) ) ? get_post_meta( $id, 'ad_clicks', true ) : "0";
				break;

			# close switch( $column_name ) {
			}

		# close function manage_service_columns( $column_name, $id ) {
		}

		# ad_manager redirect shortcode
		function adManager_Redirect() {

			# get variables
			$redir_id = esc_attr( $_GET[ 'redir_id' ] );
			$verify   = esc_attr( $_GET[ 'verify' ] );

			# make sure the nonce is verified
			if( wp_verify_nonce( $verify, 'ad_manager_ad' ) ) {

				# redirect path
				$redirect = get_post_meta( $redir_id, 'ad_link', true );

				# get current click count & update
				$click_count = get_post_meta( $redir_id, 'ad_clicks', true ) + 1;
				update_post_meta( $redir_id, 'ad_clicks', $click_count );

				# redirect to url
				echo "<meta http-equiv=\"refresh\" content=\"0;url=". $redirect ."\" />\n";
				exit();

			# otherwise
			} else {

				# stop loading
				die( "Invalid Verification" );

			# close if( !wp_verify_nonce( $verify, 'ad_manager_ad' ) ) {
			}

		# close function adManager_Redirect() {
		}


		/**
		 * plugin startup sequence
		 * @since 0.1
		 */
		function settings_menu() {

			# if role is administrator
			if( current_user_can( 'manage_options' ) ) {

				# add the plugin page to the settings menu
				add_options_page( 'Ad Manager Settings', 'Ad Manager Settings', 'manage_options', 'ad-manager-settings', array( $this, 'settings_page' ) );

			# close if( current_user_can( 'manage_options' ) ) {
			}

		# close function settings_menu() {
		}


		/**
		 * settings page function called above
		 * @since 0.1
		 */
		function settings_page() {

			# include page
			include $this->plugin_path . 'includes/ni-ad-manager-settings.php';

		# close function settings_page() {
		}

	# close class TablePrefix {
	}
?>