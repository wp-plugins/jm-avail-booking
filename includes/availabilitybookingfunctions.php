<?php
class AvailabilityBookingFunctions {

    public static function month_to_name($month) {
        $month = ltrim($month, '0');
        $month_array[1] = __('January', 'jm_avail_booking');
        $month_array[2] = __('February', 'jm_avail_booking');
        $month_array[3] = __('March', 'jm_avail_booking');
        $month_array[4] = __('April', 'jm_avail_booking');
        $month_array[5] = __('May', 'jm_avail_booking');
        $month_array[6] = __('June', 'jm_avail_booking');
        $month_array[7] = __('July', 'jm_avail_booking');
        $month_array[8] = __('August', 'jm_avail_booking');
        $month_array[9] = __('September', 'jm_avail_booking');
        $month_array[10] = __('October', 'jm_avail_booking');
        $month_array[11] = __('November', 'jm_avail_booking');
        $month_array[12] = __('December', 'jm_avail_booking');

        return $month_array[$month];
    }

}
