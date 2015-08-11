<?php

// Handle the custom format display if needed
add_filter('template_redirect', 'avail_booking_template_redirect');

function avail_booking_template_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['airbenb'])) {
        $options = get_option('jm_avail_booking_option_name');
        
        $avail_booking_db = new Avail_Booking_db();
        $results = $avail_booking_db->get_ical_bookings($wp_query->query_vars['airbenb'], $options['airbenb_confirmed']);
        $summary = $options['summary'];
        $description = $options['description'];
        
        $extra_day = 1;
        if (isset($options['firstlast']))   {
            $extra_day = 0;
        }
        $site_name = parse_url(site_url(), PHP_URL_HOST);
        $file_name = sanitize_file_name( $site_name . date("Ymd")  );        
        
        // Start output
        ob_start();


// - file header -
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $file_name . '.ics"');

// - content header -
        $output = "BEGIN:VCALENDAR\r\nPRODID:-//BenB//Utrecht//EN\r\nVERSION:2.0\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:Test\r\nX-WR-TIMEZONE:Europe/Amsterdam\r\nX-WR-CALDESC:\r\n";

        foreach ($results as $result) {
            $split = explode('@', $result['email']);
            $name = $split[0];
            $output .= "BEGIN:VEVENT\r\n";
            $output .= "SUMMARY:" . $result[$summary] . "\r\n";
            $output .= "UID:" . $result['id'] . "-" . $name . "@" . $site_name . "\r\n";
            $output .= "STATUS:CONFIRMED\r\n";
            $output .= "DTSTART;VALUE=DATE:" . str_replace("-", "", $result['start_date']) . "\r\n";
            $output .= "DTEND;VALUE=DATE:" . str_replace("-", "", date('Y-m-d', strtotime($result['end_date'] . ' +' . $extra_day . ' day'))) . "\r\n";
            $output .= "DESCRIPTION:" . $result[$description] . "\r\n";
            $output .= "END:VEVENT\r\n";
        }
        $output .= "END:VCALENDAR";
        echo $output;

        exit();
    }
}
