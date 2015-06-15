<?php

/*
 * Availability Calendar
 * 
 * 
 */

class AvailabilityCalendar {

    public $currentYear;
    public $currentMonth;
    private $currentdayofMonth;
    private $month_name;
    private $options;

    // The constructor.
    function __construct() {
        $currentTime = current_time('mysql');
        list( $currentYear, $currentMonth, $dayofMonth ) = preg_split('([^0-9])', $currentTime);
        $this->currentYear = $currentYear;
        $this->currentMonth = $currentMonth;
        $this->dayofMonth = $dayofMonth;        //Get plugin options
        $options = get_option('jm_avail_booking_option_name');
        $this->options = $options;
        $this->week_firstday = get_option('start_of_week', '1');
        preg_match_all("/([^,: ]+):([^,: ]+)/", $options['rooms'], $r);
        $result = array_combine($r[1], $r[2]);
        $this->numberrooms = $result;
    }

    public function getHeader($year, $month, $instance, $display) {

        $colspan = 5;
        If (isset($this->options[weeknumbers])) {
            $colspan = 6;
        }
        $left_cel = '';
        $right_cel = '';
        if (($display == '') or ( $display == 0)) {
            $left_cel = '&lt;--';
            $calendar = "<div class=\"availcal\" >";
        }
        if (($display == '') or ( $display == 2)) {
            $right_cel = '--&gt;';
        }
        $month_name = AvailabilityBookingFunctions::month_to_name($month);
        //Built Calendar
        // Build availcal div

        $calendar .= "	<div class=\"table_pos\"><table class=\"table table-bordered\"  >
                        <thead>
                        <tr class=\"cal_title\">
			<td class=\"left\"><a href=\"#\" class=\"makeRequest\" data-instance=\"$instance\" >$left_cel</a></td>
			<td colspan=\"$colspan\" class=\"cal_month\">
			<div id=\"availcalheader$display$instance\">$month_name - $year</div></td>		
			<td class=\"right\"><a href=\"#\" class=\"makeRequest2\" data-instance=\"$instance\">$right_cel</a></td></tr>";
        if (isset($this->options[weeknumbers])) {
            $calendar .= "<td class=\"weeknbr\">&#35;</td>";
        }
        if ($this->week_firstday == '0') {
            $calendar .= "<td>" . __('Su', 'jm_avail_booking') . "</td><td>" . __('Mo', 'jm_avail_booking') . "</td><td>" . __('Tu', 'jm_avail_booking') . "</td><td>" . __('We', 'jm_avail_booking') . "</td><td>" . __('Th', 'jm_avail_booking') . "</td><td>" . __('Fr', 'jm_avail_booking') . "</td><td>" . __('Za', 'jm_avail_booking') . "</td></tr>";
        } else {
            $calendar .= "<td>" . __('Mo', 'jm_avail_booking') . "</td><td>" . __('Tu', 'jm_avail_booking') . "</td><td>" . __('We', 'jm_avail_booking') . "</td><td>" . __('Th', 'jm_avail_booking') . "</td><td>" . __('Fr', 'jm_avail_booking') . "</td><td>" . __('Sa', 'jm_avail_booking') . "</td><td>" . __('Su', 'jm_avail_booking') . "</td></tr>";
        }
        $calendar .= "</thead>";

        $calendar .= "<tbody id=\"data-update$display$instance\">";
        return $calendar;
    }

    public function getDays($year, $month, $instance, $name, $display) {

        //Determin month to display
        $line_counter = 0;
        switch ($month) {
            case 13:
                $month = 01;
                $year = $year + 1;
                break;
            case 14:
                $month = 02;
                $year = $year + 1;
                break;
            case 15:
                $month = 03;
                $year = $year + 1;
                break;
            case 0;
                if ($year <= $this->currentYear) {
                    $year = $this->currentYear;
                    $month = 01;
                } else {
                    $month = 12;
                    $year = $year - 1;
                }
        }
        if (($month < $this->currentMonth) AND ( $year == $this->currentYear)) {
            $month = $this->currentMonth;
        }
        $month = sprintf("%02s", $month);

        $first_of_month = mktime(0, 0, 0, $month, 1, $year);
        $maxdays = date('t', $first_of_month);
        $date_info = getdate($first_of_month);
        $bookings = array();
        if ($this->week_firstday == '0') {
            $weekday = $date_info['wday'];
        } else {
            $weekday = $date_info['wday'] - 1;
            if ($weekday == -1) {
                $weekday = 6;
            }
        }



        $month_name = AvailabilityBookingFunctions::month_to_name($month);
        $monthmin = $month - 1;
        $monthplus = $month + 1;
        $header = $month_name . " - " . $year;

        // Hotel mode check

        If (isset($this->options[hotel])AND $this->options[hotel] == 1) {
            $counter_limit = $this->numberrooms[$name];
        } else {
            $counter_limit = 1;
        }

        //Body part Table

        $calendar .= "<tr id=\"table_info$display$instance\" style=\"display: none;\" data-year=\"$year\" data-monthmin=\"$monthmin\" data-monthplus=\"$monthplus\" data-monthname=\"$header\" data-name=\"$name\"></tr><tr>";
        $day = 1;
        $linkDate = mktime(0, 0, 0, $month, $day, $year);
        $week = (int) date('W', $linkDate);
        if ($this->week_firstday == '0') {
            $week = (int) date('W', ($linkDate + 60 * 60 * 24));
        }
        if (isset($this->options[weeknumbers])) {
            $calendar .= "<td class=\"weeknbr\">$week</td>";
        }
        if ($weekday > 0) {
            $calendar .= "<td colspan=\"$weekday\">&nbsp;</td>\n";
        }
        $teller = 0;
        $last_price = 0;
        $avail_booking_db = new Avail_Booking_db();
        /*
         * Get price info
         * $last_price = -1 if no last price is found in db
         * 
         * 
         */
        $last_price_array = $avail_booking_db->get_last_price($name, $month, $year);
        if ($last_price_array != -1) {
            $last_price = $last_price_array[0]['price'];
        }
        $prices = $avail_booking_db->get_prices($name, $month, $year);

        while ($day <= $maxdays) {
            $linkDate = mktime(0, 0, 0, $month, $day, $year);
            foreach ($prices as $price) {
                if ($linkDate == $price['date']) {
                    $last_price = $price['price'];
                }
            }
            $week = (int) date('W', $linkDate);
            if ($this->week_firstday == '0') {
                $week = (int) date('W', ($linkDate + 60 * 60 * 24));
            }
            if ($weekday == 7) {
                $calendar .= "</tr>\n<tr>";
                if (isset($this->options[weeknumbers])) {
                    $calendar .= "<td class=\"weeknbr\">$week</td>";
                }
                $weekday = 0;
                $line_counter++;
            }


            if (($day == $this->dayofMonth) and ( $year == $this->currentYear) and ( $month == $this->currentMonth)) {
                $status = 4;
            } else {
                $status = 0;
            }


            $bookings = $avail_booking_db->get_items($name, $month, $year);
            $room_counter = 0;
            foreach ($bookings as $booking) {
                if (($linkDate <= $booking['end']) and ( $linkDate >= $booking['start'])) {
                    $room_counter++; //busy
                    if (isset($this->options[firstlast])AND $this->options[firstlast] == 1) {
                        if ($linkDate == $booking['end'])   {
                            $room_counter-- ;
                        }
                    }
                }
            }
            if ($room_counter >= $counter_limit) {
                $class = 'cal_post';
            } else {
                $class = 'cal_empty';
            }

            $text_price = '';
            If ((isset($this->options['showprices']) AND ( $last_price != -1))) {
                $text_price = "<br><div class= \"textprice\">" . $last_price . "</div>";
            }
            $calendar .= "<td class=\"$class\">$day  $text_price </td>\n";
            $day++;
            $weekday++;
        }
        if ($weekday != 7) {
            $calendar .= '<td colspan="' . (7 - $weekday) . '">&nbsp;</td>';
        }

        $calendar .= "</tr>";
        // footer
        if ($line_counter < 5) {
            if (isset($this->options[weeknumbers])) {
                $calendar .= "<td class=\"weeknbr\"><div class=\"cel_hidden\">$day  $text_price</div> </td><td colspan=\"7\">&nbsp;</td></tr><tr>";
            } else {
                $calendar .= "<td  colspan=\"7\"><div class=\"cel_hidden\">$day  $text_price</div> </td></tr><tr>";
            }
        }
        if (isset($this->options[weeknumbers])) {
            $calendar .= "<td class=\"weeknbr\">&nbsp;</td>";
        }
        $calendar .= "<td class=\"cal_post display_post\">&nbsp;</td> <td class=\"display_post\" colspan=\"6\">"
                . __('booked', 'jm_avail_booking');

        $calendar .= "</td></tr>\n";



        return $calendar;
    }

    public function closeTable($display) {

        $calendar = "</tbody></table></div>";
        if (($display == '') OR ( $display == 2)) {
            $calendar .= "</div>";
        }
        return $calendar;
    }

}
