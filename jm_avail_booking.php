<?php
/*
  Plugin Name: WP Availability Calendar & Booking
  Description: Availability Calendar and Booking Form
  Version: 0.4
  Author: Jan Maat
  License: GPLv2
 */

/*  Copyright 2011  Jan Maat  (email : jenj.maat@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// Include Class Autoloader
if (!class_exists('AvailabilityBookingAutoloader')) {
    require_once('includes/availabilitybookingautoloader.php');
    spl_autoload_register('AvailabilityBookingAutoloader::loader');
}
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/*
 * Install 
 */
register_activation_hook(__FILE__, 'AvailabilityBooking_install');

function AvailabilityBooking_install() {
    if (version_compare(get_bloginfo('version'), '3.8.1', '<')) {
        die("This Plugin requires WordPress version 3.8.1 or higher");
    }
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    global $wpdb;
    /*
     * Version db for upgrading
     */
    global $AvailabilityBooking_db_version;
    $AvailabilityBooking_db_version = "2.0";
    /*
     * 
     * create table Bookings
     */
    $db_table_name_bookings = $wpdb->prefix . 'AvailabilityBooking_Bookings';
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name_bookings'") != $db_table_name_bookings) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $db_table_name_bookings . " (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT 'default',
                        `status` INT NOT NULL DEFAULT 0, 
                        `start_date` date NOT NULL default '0000-00-00',
                        `end_date` date NOT NULL default '0000-00-00',
			`email` varchar(255) NOT NULL DEFAULT '',
                        `phone` varchar(20) NOT NULL DEFAULT '',
                        `country` varchar(50) NOT NULL DEFAULT '',
                        `language` varchar(10) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";
        dbDelta($sql);
    }
    /*
     * Create Table Prices
     * 
     */
    $db_table_name_prices = $wpdb->prefix . 'AvailabilityBooking_Prices';
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name_prices'") != $db_table_name_prices) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE " . $db_table_name_prices . " (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT 'default',                        
                        `date` date NOT NULL default '0000-00-00',
                        `price` varchar(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";
        dbDelta($sql);
    }
    add_option("AvailabilityBooking_db_version", $AvailabilityBooking_db_version);
    $installed_ver = get_option("AvailabilityBooking_db_version");

    if ($installed_ver != $AvailabilityBooking_db_version) {

        $db_table_name_bookings = $wpdb->prefix . 'AvailabilityBooking_Bookings';

        $sql = "CREATE TABLE " . $db_table_name_bookings . " (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT 'default',
                        `status` INT NOT NULL DEFAULT 0, 
                        `start_date` date NOT NULL default '0000-00-00',
                        `end_date` date NOT NULL default '0000-00-00',
			`email` varchar(255) NOT NULL DEFAULT '',
                        `phone` varchar(20) NOT NULL DEFAULT '',
                        `country` varchar(50) NOT NULL DEFAULT '',
                        `language` varchar(10) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        $db_table_name_prices = $wpdb->prefix . 'AvailabilityBooking_prices';

        $sql = "CREATE TABLE " . $db_table_name_prices . " (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT 'default',                         
                        `date` date NOT NULL default '0000-00-00', 
                        `price` varchar(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        update_option("AvailabilityBooking_db_version", $AvailabilityBooking_db_version);
    }
}

/*
 * 
 * Plugin uninstall
 */

function AvailabilityBooking_pluginUninstall() {

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
    $table = $wpdb->prefix . "AvailabilityBooking_prices";
    $wpdb->query("DROP TABLE IF EXISTS $table");

//Delete any options thats stored also?
    delete_option('jm_avail_booking_option_name');
//delete_option('wp_yourplugin_version');
    delete_option('AvailabilityBooking_db_version');
}

register_deactivation_hook(__FILE__, 'AvailabilityBooking_pluginUninstall');

function jm_avail_booking_init() {
    load_plugin_textdomain('jm_avail_booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Add actions
add_action('init', 'jm_avail_booking_init');

/*
 * Check if shortcode is present on the post to add the bootstrapValitdator
 * 
 */

function jm_avail_booking_check_for_shortcode($posts) {
    if (empty($posts))
        return $posts;

// false because we have to search through the posts first
    $found = false;

// search through each post
    foreach ($posts as $post) {
// check the post content for the short code
        if (stripos($post->post_content, '[availbooking'))
// we have found a post with the short code
            $found = true;
// stop the search
        break;
    }

    if ($found) {
// $url contains the path to your plugin folder
        $url = plugin_dir_url(__FILE__);
        wp_enqueue_style('bootstrapValidator', $url . 'css/bootstrapValidator.min.css');
        wp_enqueue_style('availbooking', $url . 'css/availbooking.css');
        wp_enqueue_script('bootstrapValidator', $url . 'js/bootstrapValidator.min.js', array('jquery'), '0.4.5', true);
        wp_enqueue_script('excecutevalidation', $url . 'js/excecutevalidation.js', array('bootstrapValidator'), '', true);
        wp_enqueue_script('availbooking', $url . '/js/availbooking.js', array('jquery'), '0.4.5', true);
        wp_localize_script('availbooking', 'availbooking', array(
            // URL to wp-admin/admin-ajax.php to process the request
            'ajaxurl' => admin_url('admin-ajax.php'),
            // generate a nonce with a unique ID "myajax-post-comment-nonce"
            // so that you can check it later when an AJAX request is sent
            'security' => wp_create_nonce('availbooking-special-string')
        ));
    }
    return $posts;
}

// perform the check when the_posts() function is called
add_action('the_posts', 'jm_avail_booking_check_for_shortcode');

// Add shortcode
add_shortcode('availbooking', 'jm_avail_booking_shortcode');

/**
 * 
 * @param array $attr Attributes of the shortcode.
 * @return string HTML content to display gallery.
 */
function jm_avail_booking_shortcode($atts) {
    static $instance = 0;
    $instance++;
    if (isset($atts[name])) {
        $name = $atts[name];
    } else {
        $name = 'default';
    }

    $calendar = new AvailabilityCalendar();
    $year = $calendar->currentYear;
    $month = $calendar->currentMonth;
    $renderCalendar = $calendar->getHeader($year, $month, $instance);
    $renderCalendar .= $calendar->getDays($year, $month, $instance, $name);
    $renderCalendar .= $calendar->closeTable();
    return $renderCalendar;
}

/*
 * 
 * Contact form 7 integration
 * 
 */

if (function_exists('wpcf7_add_shortcode')) {
    wpcf7_add_shortcode('booking', 'wpcf7_booking_shortcode_handler', true);
    add_action('wpcf7_before_send_mail', 'avail_cf7_db_update');
}

function wpcf7_booking_shortcode_handler($tag) {
    $html = '<input type="hidden" name="avail_wpcf7" value="booking" />';
    $options = get_option('jm_avail_booking_option_name');
    if ($options[rooms] != "") {
        $rooms_values = explode(",", $options[rooms]);
        if ($tag[attr] != "") {
            $rooms_names = explode(",", $tag[attr]);
        } else {
            $rooms_names = explode(",", $options[rooms]);
        }
    } else {
        $rooms_names[0] = 'default';
        $rooms_values[0] = 'default';
    }


    $html .= '<select name="booking" id="booking" required>';
    $i = 0;
    while ($i < count($rooms_values)) {
        $html .= '<option value="' . $rooms_values[$i] . '" ' . $selected . '>' . $rooms_names[$i] . '</option>';
        $i ++;
    }
    $html .= '</select>';
    return $html;
}

/*
 * Minimum number of nights
 * 
 */

function calendar_js() {
    $options = get_option('jm_avail_booking_option_name');
    ?>
    <script>
        jQuery(function ($) {
            var start = $('input[name="start_date"]');
            var end = $('input[name="end_date"]');

            start.on('change', function () {
                var start_date = $(this).datepicker('getDate');
                start_date.setDate(start_date.getDate() + <?php echo $options['min_nights']; ?>);
                end.datepicker('option', 'minDate', start_date);
            });
        });
    </script>
    <?php
}

add_action('wp_footer', 'calendar_js');
/*
 * Update database
 */

function avail_cf7_db_update($cf7) {
    global $wpdb;
    $db_table_name_bookings = $wpdb->prefix . 'AvailabilityBooking_Bookings';
    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
        $posted_data = $submission->get_posted_data();
        if (!isset($posted_data[avail_wpcf7])) {
            return;
        }
        $date = new DateTime($posted_data['start_date']);
        $start_date = $date->format('Y-m-d');
        $date = new DateTime($posted_data['end_date']);
        $end_date = $date->format('Y-m-d');
        $options = get_option('jm_avail_booking_option_name');
        $status = 1;
        if (isset($options['status'])) {$status = $options['status'];}
        $wpdb->insert(
                $db_table_name_bookings, array(
            'name' => $posted_data['booking'],
            'status' => $status,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'email' => $posted_data['your-email'],
            'phone' => $posted_data['your-phone'],
            'country' => $posted_data['your-country'],
            'language' => $posted_data['your-language']
                )
        );
    }
}

if (is_admin()) {
    require_once('jm_avail_booking_admin.php');
    require_once ('avail_booking_bookings_page.php');
    require_once ('avail_booking_prices_page.php');
    require_once ('avail_booking_import_export.php');
}



