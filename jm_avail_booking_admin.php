<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

$avail_booking_settings_page = new Avail_Booking_SettingsPage();

//Add screen option filter Bookings
add_filter('set-screen-option', 'avial_booking_set_bookings_option', 10, 3);

function avial_booking_set_bookings_option($result, $option, $value) {
    $avial_booking_screens = array(
        'avial_booking_bookings_per_page', 'avial_booking_prices_per_page');

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
    $options = get_option('jm_avail_booking_option_name');
    $calendar = new AvailabilityCalendar();
    $year = $_GET[year];
    $month = $_GET[month];
    $instance = $_GET[instance];
    $name = $_GET[name];
    if ($options['threemonths'] == 0) {
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '');
    } else {    
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '0');
        $renderCalendar .= '|';
        $month++;
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '1');
        $renderCalendar .= '|';
        $month++;
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '2');
    }
    echo $renderCalendar;
    die(); // this is required to return a proper result
}

add_action('wp_ajax_availbooking_action', 'availbooking_action_callback');
add_action('wp_ajax_nopriv_availbooking_action', 'availbooking_action_callback');

// Register date picker
add_action('admin_enqueue_scripts', 'Avail_Booking_enqueue_date_picker');

function Avail_Booking_enqueue_date_picker() {
    wp_enqueue_script(
            'field-date-js', plugins_url('jm-avail-booking/js/Field_Date.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true
    );
    If (get_bloginfo('language') == 'nl-NL') {
        wp_enqueue_script(
                'jquery.ui.datepicker-nl.js', plugins_url('jm-avail-booking/js/jquery.ui.datepicker-nl.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),time(), true
        );
    }
    //wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
}