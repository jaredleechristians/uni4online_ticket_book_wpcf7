<?php
/*
Plugin Name: Ticket Booking Add-On For Contact Form 7
Description: A test plugin to demonstrate wordpress functionality
Author: Jared Christians
Version: 0.1
*/

function wpcf7_booking_options_page(){
	?>
	<h2>Ticket Booking Add-On For Contact Form 7</h2>
	<p>Add shortcode [ticket_book_cf7] in form before submit button.</p>
	<input type="text" value="[ticket_book_cf7]" id="myInput" disabled>
	<button class="button button-primary" onclick="clipboard()">Copy text</button>
<?php
}

function wpcf7_booking_register_options_page() {
	add_options_page('Booking', 'Booking Add-on', 'manage_options', 'wpcf7_booking', 'wpcf7_booking_options_page');
}
add_action('admin_menu', 'wpcf7_booking_register_options_page');

function wpcf7_booking_booking_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "wpcf7_booking";
	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  booked varchar(10) DEFAULT '0' NOT NULL,
	  PRIMARY KEY  (id)
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	for($i = 0; $i < 100; $i++){
		cf7_booking_install_data();
	}
	
}
register_activation_hook( __FILE__, 'wpcf7_booking_booking_install' );

function cf7_booking_install_data() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpcf7_booking';
	$wpdb->insert( 
		$table_name, 
		array('booked'=>'0')
	);
}

function ticket_book_cf7_shortcode() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpcf7_booking';
	$result = $wpdb->get_results( "SELECT * FROM $table_name" );
	$message = "<div class='wpcf7_booking'>";
	$count = 0;
	foreach ($result as $booking) {
		$count ++;
		$message .= "<label class='checkbox'><input type='checkbox' name='check[]' id='check-$booking->id' value='$booking->id'";
		if($booking->booked == '1'){
			$message .= " disabled ";
		}
		$message.= "></label>";
		if($count == 10){
			$message.="<br>";
			$count = 0;
		}
	}
	$message .= "</div>"; 
	return $message;
}
add_shortcode('ticket_book_cf7', 'ticket_book_cf7_shortcode');

add_action( 'wpcf7_before_send_mail', 'wpcf7_mail_sent_function' ); 

function wpcf7_mail_sent_function( $contact_form ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpcf7_booking';
    $submission = WPCF7_Submission::get_instance();  
    if ( $submission ) {
        $checked = $submission->get_posted_data('check');
		foreach($checked as $booking){
			$wpdb->query($wpdb->prepare( "UPDATE $table_name SET booked = '1' WHERE id = '$booking' "));
		}
    }
}

function wpcf7_booking_hook_javascript() {
	?>
	<script>
		function clipboard() {
			window.navigator.clipboard.writeText('[ticket_book_cf7]').then(function(x) {
			  alert("copied to clipboard: " + '[ticket_book_cf7]');
			});
		}
	</script>
	<?php
}

add_action('admin_head', 'wpcf7_booking_hook_javascript');

function wpcf7_booking_css() {
    wp_register_style('wpcf7_booking_css', plugins_url('css/style.css',__FILE__ ));
    wp_enqueue_style('wpcf7_booking_css');
}

add_action( 'init','wpcf7_booking_css');

?>
