<?php
add_action('admin_menu', 'Avail_Import_Export_admin_menu');

function Avail_Import_Export_admin_menu() {
    add_submenu_page('bookings', __('Import & Export', 'jm_avail_booking'), __('Import & Export', 'jm_avail_booking'), 'activate_plugins', 'import_export', 'Avail_booking_Import_Export_form_handler');
}

global $wpdb;

// Form posted?
If (isset($_REQUEST['export'])) {

    add_action('wp_loaded', 'availcal_export', 1);

    function availcal_export() {
        global $wpdb;
        $sitename = sanitize_key(get_bloginfo('name'));
        if (!empty($sitename))
            $sitename .= '.';
        $filename = $sitename . 'Availability.' . date('Y-m-d') . '.xml';
        // activate download
        $mimeType = 'text/x-csv';

        //Execute Export
        // Get Bookings
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings';
        $items = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        //Generate xml output
        // Output XML header.
        $output = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        // Output root element.
        $output .= '<Bookings>' . "\n";
        foreach ($items as $item) {
            $output .= "\t" . '<Booking>' . "\n";
            $output .= "\t\t" . '<name>' . $item['name'] . '</name>' . "\n";
            $output .= "\t\t" . '<startdate>' . $item['start_date'] . '</startdate>' . "\n";
            $output .= "\t\t" . '<enddate>' . $item['end_date'] . '</enddate>' . "\n";
            $output .= "\t\t" . '<email>' . $item['email'] . '</email>' . "\n";
            $output .= "\t\t" . '<phone>' . $item['phone'] . '</phone>' . "\n";
            $output .= "\t\t" . '<country>' . $item['country'] . '</country>' . "\n";
            $output .= "\t\t" . '<language>' . $item['language'] . '</language>' . "\n";
            $output .= "\t" . '</booking>' . "\n";
        }
        // Terminate root element.
        $output .= '</bookings>' . "\n";
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        echo $output;
        die();
    }

}

function Avail_booking_Import_Export_form_handler() {
    ?>
    <h2><?php _e('Import & Export', 'jm_avail_booking') ?></h2> 
    <h3><?php _e('Export', 'jm_avail_booking') ?></h3>
    <?php _e('If you click the button below WordPress will create an XML file with all booikings and prices and save this file to your computer.', 'jm_avail_booking'); ?>
    <br />
    <?php _e('Once downloaded  you can use this file in the Import function on another WordPress installation to import the contents of the WP Availability Calendar and Bookings.', 'jm_avail_booking'); ?>
    <form  method = "post" >

        <input type = "hidden" name = "export" value = "true" /><br /><br />
        <input type="submit" value="<?php _e('Download', 'jm_avail_booking') ?>" id="submit" class="button-primary" name="submit">     

    </form>      
    <?php
}
