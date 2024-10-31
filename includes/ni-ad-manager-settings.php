<?php
	# if action is found
	if( isset( $_POST[ 'Submit' ] ) && $_POST[ 'Submit' ] == 'Save Changes' ) {

		# update settings
		update_option( 'ad_manager_ads', $_POST[ 'ad_manager_ads' ] );

		# message
		$message = array(
			'message'	=>	"Your updates have been saved.",
			'type'		=>	"updated"
		);

	# close if( isset( $_POST[ 'Submit' ] ) && $_POST[ 'Submit' ] == 'Save Changes' ) {
	}

	# if we are are in the get string
	if( $_GET[ 'Submit' ] == "Export Data" ) {

		# dates
		$start_date_month = esc_attr( $_GET[ 'start-date-month' ] );
		$start_date_day   = esc_attr( $_GET[ 'start-date-day' ] );
		$start_date_year  = esc_attr( $_GET[ 'start-date-year' ] );
		$end_date_month   = esc_attr( $_GET[ 'end-date-month' ] );
		$end_date_day     = esc_attr( $_GET[ 'end-date-day' ] );
		$end_date_year    = esc_attr( $_GET[ 'end-date-year' ] );
		$category	      = esc_attr( $_GET[ 'category' ] );

		# if the dates are valid
		if( checkdate( $start_date_month, $start_date_day, $start_date_year ) && checkdate( $end_date_month, $end_date_day, $end_date_year ) ) {

			# global
			global $start, $end;

			# build dates
			$start = $start_date_year . "-" . $start_date_month . "-" . $start_date_day;
			$end   = $end_date_year . "-" . $end_date_month . "-" . $end_date_day;

			# get posts
			$args = array(
				'posts_per_page'	=>	-1,
				'post_type'			=>	'ad_manager_ads',
				'post_status'		=>	'publish',
			);

			# if any category is selected
			if( $category != 'null' ) {

				# add to args
				$args[ 'tax_query'] = array( array( 'taxonomy' => 'ad_size', 'field' => 'id', 'terms' => $category ) );

			# close if( $category != 'null' ) {
			}

			# create a function to filter by date
			function filter_where( $where = '' ) {

				# global
				global $start, $end;

				# posts in the last x days
				$where .= (string) " AND post_date BETWEEN '". $start ."' AND '". $end ."'";

				# return where string
				return $where;

			# close function filter_where( $where = '' ) {
			}

			# add filter 
			add_filter( 'posts_where', 'filter_where' );

			# run query
			$query = new WP_Query( $args );

			# data headings
			$data = "Ad ID,Ad Name,Ad Type,Loads,Clicks,Click Rate\n";

			# loop through ads
			while ( $query->have_posts() ) : $query->the_post(); setup_postdata( $query ); $count++;

				# post variables
				$post_id   = $query->posts[$count-1]->ID;
				$post_name = $query->posts[$count-1]->post_name;

				# get category array
				$category = wp_get_post_terms( $post_id, 'ad_size' );

				# other ad values
				$ad_name    = $category[0]->name;
				$ad_loads   = get_post_meta( $post_id, 'ad_loads', true );
				$ad_clicks  = get_post_meta( $post_id, 'ad_clicks', true );
				$click_rate = number_format( ( $ad_clicks / $ad_loads ) * 100, 4 );

				# add to data
				$data .= $post_id .",". $post_name .",". $ad_name .",". $ad_loads .",". $ad_clicks .",". $click_rate ."\n";

			# close while ( $the_query->have_posts() ) : $the_query->the_post();
			endwhile;	

			# remove filter AFTER the query is run
			remove_filter( 'posts_where', 'filter_where' );

			# if there are results
			if( $query->have_posts() ) {

				# open & write to the csv file
				$file = fopen( ABSPATH . 'wp-content/plugins/ni-ad-manager/export.csv', 'w' );
				fwrite( $file, $data );
				fclose( $file );

				# file path
				$path = site_url( '/' ) . 'wp-content/plugins/ni-ad-manager/export.csv';

				# message
				$message = array(
					'message'	=>	"Export is Complete! <a href=\"". $path ."\" target=\"_blank\">Click Here</a> to Download File",
					'type'		=>	"updated"
				);

			# otherwise
			} else {

				# message
				$message = array(
					'message'	=>	"Sorry, there were no results that match your search request. Please Try Again.",
					'type'		=>	"error"
				);

			# close if( $query->have_posts() ) {
			}

		# close if( checkdate( $start_date_month, $start_date_day, $start_date_year ) && checkdate( $end_date_month, $end_date_day, $end_date_year ) ) {
		}

	# otherwise
	} else {

		# dates
		$start_date_month = date( "m" );
		$start_date_day   = date( "d" );
		$start_date_year  = date( "Y" );
		$end_date_month   = date( "m" );
		$end_date_day     = date( "d" );
		$end_date_year    = date( "Y" );

	# close if( $_SERVER[ 'REQUEST_METHOD' ] == "GET" ) {
	}
?>

<div class="wrap">
<?php
	# if export is complete
	if( isset( $message ) ) {

		# display message
		echo "<div id=\"message\" class=\"". $message[ 'type' ] ."\">". $message[ 'message' ] ."</div>";

	# close if( isset( $export ) && $export == "complete" ) {
	}
?>
    <div class="metabox-holder">
      <!-- // TODO Move style in css -->
      <div class='postbox-container' style='width: 99.5%'>
        <div id="" class="meta-box-sortables" >


          <form id="ad-manager-settings" action="options-general.php?page=ad-manager-settings" method="post">
          <h2><?php echo __( 'Ad Manager Settings', 'menu-ad-manager-settings' ); ?></h2>
<?php
	settings_fields( 'ad-manager-settings' );
	do_settings_sections( 'ad-manager-settings' );
?>

          <div  class="postbox " >
            <div class="handlediv" title=""><br />
            </div>
            <h3 class='hndle'><span>Ad Manager Settings</span></h3>
            <div class="inside">
              <table class="form-table">
                <tr>
                  <td width="175"><span><strong>Redirect Page:</strong>:</span></td>
                  <td><?php
	# wp_dropdown_pages array
	$args = array(
		'show_option_none'	=>	'Select Redirect Page',
		'depth'				=>	0,
		'selected'			=>	get_option( 'ad_manager_ads', "" ),
		'name'				=>	'ad_manager_ads',
		'hierarchical'		=>	1,
		'sort_order'		=>	'ASC',
		'sort_column'		=>	'post_title',
	);

	# display pages in drop down
	wp_dropdown_pages( $args );
                  ?></td>
                </tr>
                <tr>
                  <td colspan="2"><div id="form-buttons"><input type="submit" name="Submit" value="Save Changes" /></div></td>
                </tr>
              </table>
            </div>
            <!-- . inside -->
          </div>
          <!-- .postbox -->
          </form>

          <p>&nbsp;</p>

          <form id="ad-manager-export" action="options-general.php" method="get">
          <input type="hidden" name="page" id="page" value="ad-manager-settings" />
          <div  class="postbox " >
            <div class="handlediv" title=""><br />
            </div>
            <h3 class='hndle'><span>Ad Manager Export</span></h3>
            <div class="inside">
              <table class="form-table">
                <tr>
                  <td width="175"><span><strong>Start Date:</strong>:</span></td>
                  <td><select name="start-date-month" id="start-date-month">
<?php
	# loop through months
	for( $x=1; $x<=12; $x++ ) {

		# if the current month is selected
		if( $x == $start_date_month ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">". date( "F", mktime( 0, 0, 0, $x, date( "d" ), date( "Y" ) ) ) ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". date( "F", mktime( 0, 0, 0, $x, date( "d" ), date( "Y" ) ) ) ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select>

                  <select name="start-date-day" id="start-date-day">
<?php
	# loop through months
	for( $x=1; $x<=31; $x++ ) {

		# if the current month is selected
		if( $x == $start_date_day ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">".$x ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". $x ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select>

                  <select name="start-date-year" id="start-date-year">
<?php
	# loop through months
	for( $x=date("Y")-3; $x<=date("Y")+3; $x++ ) {

		# if the current month is selected
		if( $x == $start_date_year ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">".$x ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". $x ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select></td>
                </tr>
                <tr>
                  <td width="175"><span><strong>End Date:</strong>:</span></td>
                  <td><select name="end-date-month" id="end-date-month">
<?php
	# loop through months
	for( $x=1; $x<=12; $x++ ) {

		# if the current month is selected
		if( $x == $end_date_month ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">". date( "F", mktime( 0, 0, 0, $x, date( "d" ), date( "Y" ) ) ) ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". date( "F", mktime( 0, 0, 0, $x, date( "d" ), date( "Y" ) ) ) ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select>

                  <select name="end-date-day" id="end-date-day">
<?php
	# loop through months
	for( $x=1; $x<=31; $x++ ) {

		# if the current day is selected
		if( $x == $end_date_day ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">".$x ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". $x ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select>

                  <select name="end-date-year" id="end-date-year">
<?php
	# loop through months
	for( $x=date("Y")-3; $x<=date("Y")+3; $x++ ) {

		# if the current year is selected
		if( $x == $end_date_year ) {

			# print option as selected
			echo "<option value=\"". $x ."\" selected=\"selected\">".$x ."</option>\n";

		# otherwise
		} else {

			# print option as selected
			echo "<option value=\"". $x ."\">". $x ."</option>\n";

		# close if( $x == $start_date_month ) {
		}

	# close for( $x=1; $x<=12; $x++ ) {
	}
?>
                  </select></td>
                </tr>
                <tr>
                  <td><strong>Ad Type</strong></td>
                  <td><select name="category" id="category">
                    <option value="null">Select Ad Type</option>
<?php
	# terms
	$terms = get_terms( array( 'ad_size' ), $args );

	# loop through terms
	foreach( $terms as $term ) {

		# if category is set to term
		if( $category == $term->term_id ) {

			# print name
			echo "                    <option value=\"". $term->term_id ."\" selected=\"selected\">". $term->name ."</option>\n";

		# otherwise
		} else {

			# print name
			echo "                    <option value=\"". $term->term_id ."\">". $term->name ."</option>\n";

		# close if( $category == $term->term_id ) {
		}

	# close foreach( $terms as $term ) {
	}
?>
                  </select></td>
                </tr>
                <tr>
                  <td colspan="2"><div id="form-buttons"><input type="submit" name="Submit" value="Export Data" /></div></td>
                </tr>
              </table>
            </div>
            <!-- . inside -->
          </div>
          <!-- .postbox -->       
           </form>



        </div>
        <!-- .metabox-sortables -->
      </div>
      <!-- .postbox-container -->
    </div>
    <!-- .metabox-holder -->
</div>
