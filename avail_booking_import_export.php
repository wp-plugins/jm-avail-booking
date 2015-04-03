<?php
add_action('admin_menu', 'Avail_Import_Export_admin_menu');

function Avail_Import_Export_admin_menu() {
    add_submenu_page('bookings', __('Import & Export', 'jm_avail_booking'), __('Import & Export', 'jm_avail_booking'), 'activate_plugins', 'import_export', 'Avail_booking_Import_Export_form_handler');
}

global $wpdb;
$jm_error_message = '';

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
        $output .= '<AvailCal>' . "\n";
        // Output Bookings
        $output .= "\t" .'<Bookings>' . "\n";
        foreach ($items as $item) {
            $output .= "\t\t" . '<Booking>' . "\n";
            $output .= "\t\t\t" . '<name>' . $item['name'] . '</name>' . "\n";
            $output .= "\t\t\t" . '<start_date>' . $item['start_date'] . '</start_date>' . "\n";
            $output .= "\t\t\t" . '<end_date>' . $item['end_date'] . '</end_date>' . "\n";
            $output .= "\t\t\t" . '<email>' . $item['email'] . '</email>' . "\n";
            $output .= "\t\t\t" . '<phone>' . $item['phone'] . '</phone>' . "\n";
            $output .= "\t\t\t" . '<country>' . $item['country'] . '</country>' . "\n";
            $output .= "\t\t\t" . '<language>' . $item['language'] . '</language>' . "\n";
            $output .= "\t\t" . '</Booking>' . "\n";
        }
        $output .="\t" . '</Bookings>' . "\n";
        // Get Prices
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Prices';
        $items = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        // Output Prices
        $output .= "\t" .'<Prices>' . "\n";
        foreach ($items as $item) {
            $output .= "\t\t" . '<PriceInfo>' . "\n";
            $output .= "\t\t\t" . '<name>' . $item['name'] . '</name>' . "\n";
            $output .= "\t\t\t" . '<date>' . $item['date'] . '</date>' . "\n";
            $output .= "\t\t\t" . '<price>' . $item['price'] . '</price>' . "\n";            
            $output .= "\t\t" . '</PriceInfo>' . "\n";
        }
        $output .="\t" . '</Prices>' . "\n";
// Terminate root element.        
        $output .= '</AvailCal>' . "\n";
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        echo $output;
        die();
    }

}
If (isset($_REQUEST['import'])) {
    if (empty($_FILES['xml_import']['tmp_name'])) {
        $jm_error_message = __('No file selected', 'jm_avail_booking');
    } else {
        $file = $_FILES['xml_import']['tmp_name'];
        $xml = simplexml_load_file($file);
        if ($xml === false) {
            $jm_error_message = __('XML failure', 'jm_avail_booking');
        } else {
            $db_table_name_bookings = $wpdb->prefix . 'AvailabilityBooking_Bookings';
            $db_table_name_prices = $wpdb->prefix . 'AvailabilityBooking_Prices';
            $bookings = $xml->xpath('Bookings/Booking');
            foreach ($bookings as $booking) {
                $json = json_encode($booking);
                $array = json_decode($json, TRUE);
                $wpdb->insert( $db_table_name_bookings, $array );                
            }
            $prices = $xml->xpath('Prices/PriceInfo');
            foreach ($prices as $price) {
                $json = json_encode($price);
                $array = json_decode($json, TRUE);
                $wpdb->insert( $db_table_name_prices, $array );                
            }
            $jm_update_message = __('Data imported', 'jm_avail_booking');
        }
    }
}

function my_admin_notice() {
    global $jm_error_message;
    global $jm_update_message;
    If ($jm_error_message != '') {
        ?>
        <div class="error">
            <p><?php echo $jm_error_message ?></p>            
        </div>
        <?php
    }
    If ($jm_update_message != '') {
        ?>
        <div class="updated">
            <p><?php echo $jm_update_message ?></p>            
        </div>
        <?php
    }
}

add_action('admin_notices', 'my_admin_notice');

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

    <h3><?php _e('Import', 'jm_avail_booking') ?></h3>
    <form  method = "post" enctype="multipart/form-data" >
        <input type = "hidden" name = "import" value = "true" />
        <p><label for="csv_import"><?php _e('Upload file:', 'jm_avail_booking') ?></label><br/>
            <input id="xml_import" name="xml_import" type="file" class="file"></p>
        <input type="submit" value="<?php _e('Upload', 'jm_avail_booking') ?>" id="submit" class="button-primary" name="submit">
    </form>
    <?php
}
