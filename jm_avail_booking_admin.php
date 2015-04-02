<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

$avail_booking_settings_page = new Avail_Booking_SettingsPage();

//Add screen option filter Bookings
add_filter('set-screen-option', 'avial_booking_set_bookings_option', 10, 3);


function avial_booking_set_bookings_option($result, $option, $value) {
    $avial_booking_screens = array(
        'avial_booking_bookings_per_page','avial_booking_prices_per_page');

    if (in_array($option, $avial_booking_screens))
        $result = $value;

    return $result;
}

//Add screen option filter Bookings
add_filter('set-screen-option', 'avial_booking_set_prices_option', 10, 3);


function avial_booking_set_prices_option($result, $option, $value) {
    $avial_booking_screens = array(
        'avial_booking_prices_per_page');

    if (in_array($option, $avial_booking_screens))
        $result = $value;

    return $result;
}


// Ajax call to update calendar
function availbooking_action_callback() {
    check_ajax_referer('availbooking-special-string', 'security');
    $calendar = new AvailabilityCalendar();
    $year = $_POST[year];
    $month = $_POST[month];
    $instance = $_POST[instance];
    $name = $_POST[name];
    $renderCalendar .= $calendar->getDays($year, $month, $instance, $name);

    echo $renderCalendar;
    die(); // this is required to return a proper result
}

add_action('wp_ajax_availbooking_action', 'availbooking_action_callback');
add_action('wp_ajax_nopriv_availbooking_action', 'availbooking_action_callback');

