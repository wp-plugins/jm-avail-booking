<?php
/*
  Plugin Name: WP Availability Calendar & Booking
  Description: Availability Calendar and Booking Form
  Version: 1.2.0
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
// include airbenb
require_once ('avail_booking_airbenb.php');

register_deactivation_hook(__FILE__, 'avail_booking_deactivate');

function avail_booking_deactivate() {
    flush_rewrite_rules();
}

// Add the endpoint rewrite rules
add_filter('init', 'avail_booking_add_rules');

function avail_booking_add_rules() {
    add_rewrite_endpoint('airbenb', EP_ROOT);
}
/*
 * Install 
 */
register_activation_hook(__FILE__, 'AvailabilityBooking_install');

function AvailabilityBooking_install() {
    if (version_compare(get_bloginfo('version'), '3.8.1', '<')) {
        die("This Plugin requires WordPress version 3.8.1 or higher");
    }

    /*
     * Add rewrite rules for Airbenb Sync
     * 
     */
    avail_booking_add_rules();
    flush_rewrite_rules();
    
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
        $db_table_name_prices = $wpdb->prefix . 'AvailabilityBooking_Prices';

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

// Add actions
add_action('init', 'jm_avail_booking_init');

function jm_avail_booking_init() {
    load_plugin_textdomain('jm_avail_booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Add shortcode
add_shortcode('availbooking', 'jm_avail_booking_shortcode');

/*
 * Check if shortcode is present on the post to add the bootstrapValitdator
 * 
 */

function jm_avail_booking_check_for_shortcode($posts) {
    global $wp_query;
    $posts = $wp_query->posts;
    $pattern = get_shortcode_regex();
    $options = get_option('jm_avail_booking_option_name');
    if ($options['ínternal_datepicker'] = 1) {
        wp_enqueue_script('cf7_field-date-js', plugins_url('jm-avail-booking/js/cf7_field_date.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true
        );
        If (get_bloginfo('language') == 'nl-NL') {
            wp_enqueue_script(
                    'jquery.ui.datepicker-nl.js', plugins_url('jm-avail-booking/js/jquery.ui.datepicker-nl.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true
            );
        }
        wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
    }
    foreach ($posts as $post) {
        if (has_shortcode($post->post_content, 'availbooking') OR ( isset($options['in_widget']) AND ( $options['in_widget'] == 1))) {
            $url = plugin_dir_url(__FILE__);
            wp_enqueue_style('bootstrapValidator', $url . 'css/bootstrapValidator.min.css');
            wp_enqueue_style('availbooking', $url . 'css/availbooking.css');
            wp_enqueue_script('bootstrapValidator', $url . 'js/bootstrapValidator.min.js', array('jquery'), '0.4.5', true);
            wp_enqueue_script('excecutevalidation', $url . 'js/excecutevalidation.js', array('bootstrapValidator'), '', true);

            if ($options['threemonths'] == 0) {
                wp_enqueue_script('availbooking', $url . 'js/availbooking.js', array('jquery'), '0.4.5', true);
            } else {
                wp_enqueue_script('availbooking', $url . 'js/availbooking_3.js', array('jquery'), '0.4.5', true);
            }
            wp_localize_script('availbooking', 'availbooking', array(
// URL to wp-admin/admin-ajax.php to process the request
                'ajaxurl' => admin_url('admin-ajax.php'),
                // generate a nonce with a unique ID "myajax-post-comment-nonce"
// so that you can check it later when an AJAX request is sent
                'security' => wp_create_nonce('availbooking-special-string')
            ));
            break;
        }
    }
}

// perform the check when the_posts() function is called
add_action('wp', 'jm_avail_booking_check_for_shortcode');

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
    $options = get_option('jm_avail_booking_option_name');
    $calendar = new AvailabilityCalendar();
    $year = $calendar->currentYear;
    $month = $calendar->currentMonth;
    if ($options['threemonths'] == 0) {
        $renderCalendar = $calendar->getHeader($year, $month, $instance, '');
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '');
        $renderCalendar .= $calendar->closeTable('');
    } else {
        $renderCalendar = $calendar->getHeader($year, $month, $instance, '0');
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '0');
        $renderCalendar .= $calendar->closeTable('0');
        $month++;
        $renderCalendar .= $calendar->getHeader($year, $month, $instance, '1');
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '1');
        $renderCalendar .= $calendar->closeTable('1');
        $month++;
        $renderCalendar .= $calendar->getHeader($year, $month, $instance, '2');
        $renderCalendar .= $calendar->getDays($year, $month, $instance, $name, '2');
        $renderCalendar .= $calendar->closeTable('2');
    }
    return $renderCalendar;
}

/*
 * 
 * Contact form 7 integration
 * 
 */

function add_availcal_query_vars($aVars) {
    $aVars[] = "acc_name"; // represents the name of the accomodation as shown in the URL
    return $aVars;
}

// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_availcal_query_vars');

function add_availcal_rewrite_rules($aRules) {
    $options = get_option('jm_avail_booking_option_name');
    $acc_name = strtolower($options['booking_form']);
    $aNewRules = array('' . $acc_name . '/([^/]+)/?$' => 'index.php?pagename=' . $acc_name . '&acc_name=$matches[1]');
    $aRules = $aNewRules + $aRules;
    return $aRules;
}

// hook add_rewrite_rules function into rewrite_rules_array
add_filter('rewrite_rules_array', 'add_availcal_rewrite_rules');

if (function_exists('wpcf7_add_shortcode')) {
    wpcf7_add_shortcode('booking', 'wpcf7_booking_shortcode_handler', true);
    add_action('wpcf7_before_send_mail', 'avail_cf7_db_update');
}

function wpcf7_booking_shortcode_handler($tag) {
    global $wp_query;
    $html = '<input type="hidden" name="avail_wpcf7" value="booking" />';
    $acc_name = urldecode($wp_query->query_vars['acc_name']);
    if ($tag['name'] != "") {
        $html .= '<input type="hidden" name="booking" id="booking" value="' . $tag['name'] . '" />';
    } elseif (isset($wp_query->query_vars['acc_name']) AND ( $wp_query->query_vars['acc_name'] != '')) {
        $acc_name = urldecode($wp_query->query_vars['acc_name']);
        $html .= '<input type="hidden" name="booking" id="booking" value="' . $acc_name . '" />';
    } else {
        $options = get_option('jm_avail_booking_option_name');
        if ($options[rooms] != "") {
//$rooms_values = explode(",", $options[rooms]);
            $rooms_values = array_map(function($el) {
                return explode(':', $el);
            }, explode(',', $options['rooms']));

            if ($tag[attr] != "") {
                $rooms_names = array_map(function($el) {
                    return explode(':', $el);
                }, explode(',', $tag[attr]));
            } else {
//$rooms_names = explode(",", $options[rooms]);
                $rooms_names = array_map(function($el) {
                    return explode(':', $el);
                }, explode(',', $options['rooms']));
            }
        } else {
            $rooms_names[0][0] = 'default';
            $rooms_values[0][0] = 'default';
        }
        $html .= '<select name="booking" id="booking" required>';
        $i = 0;
        while ($i < count($rooms_names)) {
            $html .= '<option value="' . $rooms_values[$i][0] . '" ' . $selected . '>' . $rooms_names[$i][0] . '</option>';
            $i ++;
        }
        $html .= '</select>';
    }

    return $html;
}

/*
 * Minimum number of nights
 * 
 */

function calendar_js() {
    $options = get_option('jm_avail_booking_option_name');
    $start_minDate = 0;
    $adjustments_array = array("[0,0,0,0,0,0,0]", "[0,0,0,0,0,2,1]", "[0,0,0,0,2,2,1]", "[0,0,0,2,2,2,1]", "[0,0,2,2,2,2,1]", "[0,2,2,2,2,2,1]", "[2,2,2,2,2,4,3]",
        "[2,2,2,2,4,4,3]", "[2,2,2,4,4,4,3]", "[2,2,4,4,4,4,3]", "[2,4,4,4,4,4,3]", "[4,4,4,4,4,6,5]", "[4,4,4,4,6,6,5]", "[4,4,4,6,6,6,5]", "[4,4,6,6,6,6,5]");
    if (isset($options['restrict_reservations']) OR ( $options['restrict_reservations'] != 0)) {
        $start_minDate = $options['restrict_reservations'];
    }
    if (!isset($options['working_days']) OR ( $options['working_days'] == 0)) {
        $adjustments = "[0,0,0,0,0,0,0]";
    } else {
        $adjustments = $adjustments_array[$start_minDate];
    }
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery(function ($) {
                var start_minDate = <?php echo $start_minDate; ?>;
                var WorkingDays = new Date();
                var adjustments = <?php echo $adjustments; ?>; // Offsets by day of the week
                WorkingDays.setDate(WorkingDays.getDate() + <?php echo $start_minDate; ?> + adjustments[WorkingDays.getDay()]);
                $('#start_date').datepicker({minDate: WorkingDays});
            });
        });
    </script>
    <?php
    If ((isset($options['fixed_days'])) AND ( $options['fixed_days'] == 1)) {
        $disable_checkin_date = '" "';
        $allow_checkin_date = '" "';
        $disable_checkout_date = '" "';
        $allow_checkout_date = '" "';
        preg_match_all("/([^,: ]+):([^,: ]+)/", $options['checkin_exceptions'], $r);
        if ($r[1]) {
            $result = array_combine($r[1], $r[2]);
            $disable_checkin_date = array_keys($result);
            $allow_checkin_date = array_values($result);
            $disable_checkin_date = '"' . implode("','", $disable_checkin_date) . '"';
            $allow_checkin_date = '"' . implode("','", $allow_checkin_date) . '"';
        }
        $allow_checkin_day = $options['checkin_day'];

        preg_match_all("/([^,: ]+):([^,: ]+)/", $options['checkout_exceptions'], $r);
        if ($r[1]) {
            $result = array_combine($r[1], $r[2]);
            $disable_checkout_date = array_keys($result);
            $allow_checkout_date = array_values($result);
            $disable_checkout_date = '"' . implode("','", $disable_checkout_date) . '"';
            $allow_checkout_date = '"' . implode("','", $allow_checkout_date) . '"';
        }
        $allow_checkout_day = $options['checkout_day'];
        ?>
        <script>

            jQuery(function ($) {
                var start = $('input[name="start_date"]');
                var end = $('input[name="end_date"]');
                var allow_checkin_day = <?php echo $allow_checkin_day; ?>;
                var disable_checkin_date = <?php echo $disable_checkin_date; ?>;
                var allow_checkin_date = <?php echo $allow_checkin_date; ?>;
                var allow_checkout_day = <?php echo $allow_checkout_day; ?>;
                var disable_checkout_date = <?php echo $disable_checkout_date; ?>;
                var allow_checkout_date = <?php echo $allow_checkout_date; ?>;

                $('#start_date').on('keypress', function (e) {
                    e.preventDefault(); // Don't allow direct editing
                });

                start.datepicker({
                    beforeShowDay: function (date) {

                        var a = 0;
                        var string = date.getDay();
                        var past = date;
                        var WorkingDays = new Date();
                        var adjustments = <?php echo $adjustments; ?>; // Offsets by day of the week
                        WorkingDays.setDate(WorkingDays.getDate() + <?php echo $start_minDate; ?> + adjustments[WorkingDays.getDay()]);
                        var d = WorkingDays;

                        if (past > d) {
                            if (allow_checkin_day == string) {
                                a = 1;
                            }
                            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                            if (disable_checkin_date.indexOf(string) != -1) {
                                a = 0;
                            }
                            if (allow_checkin_date.indexOf(string) != -1) {
                                a = 1;
                            }
                        }
                        if (a == 1) {
                            return [true];
                        } else {
                            return [false];
                        }
                    }
                });
                end.datepicker({
                    beforeShowDay: function (date) {
                        var a = 0;
                        var string = date.getDay();
                        var start_date = $('#start_date').val();
                        var current = jQuery.datepicker.formatDate('yy-mm-dd', date);
                        if (current > start_date) {
                            if (allow_checkout_day == string) {
                                a = 1;
                            }
                            var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                            if (disable_checkout_date.indexOf(string) != -1) {
                                a = 0;
                            }
                            if (allow_checkout_date.indexOf(string) != -1) {
                                a = 1;
                            }
                        }
                        if (a == 1) {
                            return [true];
                        } else {
                            return [false];
                        }
                    }
                });
            });

        </script>
        <?php
    } else {
        ?>

        <script>

            jQuery(function ($) {
                var start = $('input[name="start_date"]');
                var end = $('input[name="end_date"]');


                $('#start_date').on('keypress', function (e) {
                    e.preventDefault(); // Don't allow direct editing             
                });
                $('#end_date').on('keypress', function (e) {
                    e.preventDefault(); // Don't allow direct editing
                });
                start.on('change', function () {
                    var start_date = $(this).datepicker('getDate');
                    start_date.setDate(start_date.getDate() + <?php echo $options['min_nights']; ?>);
                    end.datepicker('option', 'minDate', start_date);
                });
            });
        </script>
        <?php
    }
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
        if (isset($options['status'])) {
            $status = $options['status'];
        }
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



    