<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if (!current_user_can('activate_plugins')) {
    return;
}

global $wpdb;
/*
 * 
 * Bookings table
 */
$table = $wpdb->prefix . "AvailabilityBooking_Bookings";
$wpdb->query("DROP TABLE IF EXISTS $table");
/*
 * 
 * Prices table
 */
$table = $wpdb->prefix . "AvailabilityBooking_Prices";
$wpdb->query("DROP TABLE IF EXISTS $table");

//Delete any options thats stored also?
delete_option('jm_avail_booking_option_name');
//delete_option('wp_yourplugin_version');
delete_option('AvailabilityBooking_db_version');
