<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of avail_booking_db
 *
 * @author Jan
 */
class Avail_Booking_db {

    public $items;
    private $table_name;

    public function get_items($name, $month, $year) {
        global $wpdb;

        $bookings = array();
        $this->table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings';
        $results = $wpdb->get_results("SELECT start_date, end_date FROM $this->table_name WHERE
            (`name` = '$name') AND ((`status`= 2) OR (`status`= 3))
                AND
            ((YEAR(start_date) = $year AND MONTH(start_date) = $month  )
                OR
            (YEAR(end_date) = $year AND MONTH(end_date) = $month)
                OR
            ((YEAR(start_date) < $year OR MONTH(start_date) < $month )
                AND
            (YEAR(end_date) > $year OR MONTH(end_date) > $month )))
                ORDER BY `start_date`
                ", ARRAY_A);

        $num_rows = count($results);
        if ($num_rows != 0) {
            $counter = 0;
            while ($counter < $num_rows) {
                $bookings[$counter]['start'] = strtotime($results[$counter]['start_date']);
                $bookings[$counter]['end'] = strtotime($results[$counter]['end_date']);
                $counter++;
            }
        }
        return $bookings;
    }

    public function get_last_price($name, $month, $year) {
        global $wpdb;
        $ref_date = $year . '-' . $month . '-00';
        $this->table_name = $wpdb->prefix . 'AvailabilityBooking_Prices';
        $result = $wpdb->get_results("SELECT date, price FROM $this->table_name WHERE
            `date` < '$ref_date'  AND `name` = '$name'
                ORDER BY  `date` DESC Limit 1
                ", ARRAY_A);
        $num_rows = count($result);
        if ($num_rows == 0) {
            $last_price = -1;
        } else {
            $last_price[0]['price'] = $result[0]['price'];
            $last_price[0]['date'] = $result[0]['date'];
        }
        return $last_price;
    }

    public function get_prices($name, $month, $year) {
        global $wpdb;
        $prices = array();
        $this->table_name = $wpdb->prefix . 'AvailabilityBooking_Prices';
        $results = $wpdb->get_results("SELECT date, price FROM $this->table_name WHERE
            ( `name` = '$name' AND YEAR(date) = $year AND MONTH(date) = $month  )", ARRAY_A);
        $num_rows = count($results);
        if ($num_rows != 0) {
            $counter = 0;
            while ($counter < $num_rows) {
                $prices[$counter]['date'] = strtotime($results[$counter]['date']);
                $prices[$counter]['price'] = $results[$counter]['price'];
                $counter++;
            }
        }
        return $prices;
    }

    public function get_ical_bookings($name, $confirmed) {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings';
        if ($confirmed == 0) {
            $results = $wpdb->get_results("SELECT * FROM $this->table_name WHERE
            (`name` = '$name') AND (`status`= 3)
                AND
            ((`start_date` >= CURRENT_DATE )
                OR
            (`end_date` >= CURRENT_DATE)
                )                
                ", ARRAY_A);
        } else {
            $results = $wpdb->get_results("SELECT * FROM $this->table_name WHERE
            (`name` = '$name') AND ((`status`= 2) OR (`status`= 3))
                AND
            ((`start_date` >= CURRENT_DATE )
                OR
            (`end_date` >= CURRENT_DATE)
                )                
                ", ARRAY_A);
        }
        return $results;
    }

    public function get_all_bookings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings';
        $results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        return $results;
    }

}
