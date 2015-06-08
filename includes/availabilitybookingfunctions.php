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

    public static function currency() {
        $currencies = array(
            'AUD' => array(
                'label' => 'Australian Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'CAD' => array(
                'label' => 'Canadian Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'EUR' => array(
                'label' => 'Euro',
                'format' => '€ %s',
                'separator' => ',',
            ),
            'GBP' => array(
                'label' => 'Pound Sterling',
                'format' => '£ %s',
                'separator' => '.',
            ),
            'JPY' => array(
                'label' => 'Japanese Yen',
                'format' => '¥ %s',
                'separator' => '.',
            ),
            'USD' => array(
                'label' => 'U.S. Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'NZD' => array(
                'label' => 'N.Z. Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'CHF' => array(
                'label' => 'Swiss Franc',
                'format' => '%s Fr',
                'separator' => ',',
            ),
            'HKD' => array(
                'label' => 'Hong Kong Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'SGD' => array(
                'label' => 'Singapore Dollar',
                'format' => '$ %s',
                'separator' => '.',
            ),
            'SEK' => array(
                'label' => 'Swedish Krona',
                'format' => '%s kr',
                'separator' => ',',
            ),
            'DKK' => array(
                'label' => 'Danish Krone',
                'format' => '%s kr',
                'separator' => ',',
            ),
            'PLN' => array(
                'label' => 'Polish Zloty',
                'format' => '%s zł',
                'separator' => ',',
            ),
            'NOK' => array(
                'label' => 'Norwegian Krone',
                'format' => '%s kr',
                'separator' => ',',
            ),
            'HUF' => array(
                'label' => 'Hungarian Forint',
                'format' => '%s Ft',
                'separator' => ',',
            ),
            'CZK' => array(
                'label' => 'Czech Koruna',
                'format' => '%s Kč',
                'separator' => ',',
            ),
            'ILS' => array(
                'label' => 'Israeli New Sheqel',
                'format' => '₪ %s',
                'separator' => ',',
            ),
            'MXN' => array(
                'label' => 'Mexican Peso',
                'format' => '$ %s',
                'separator' => ',',
            ),
            'BRL' => array(
                'label' => 'Brazilian Real',
                'format' => 'R$ %s',
                'separator' => ',',
            ),
            'MYR' => array(
                'label' => 'Malaysian Ringgit',
                'format' => 'RM %s',
                'separator' => '.',
            ),
            'PHP' => array(
                'label' => 'Philippine Peso',
                'format' => '₱ %s',
                'separator' => '.',
            ),
            'TWD' => array(
                'label' => 'New Taiwan Dollar',
                'format' => 'NT$ %s',
                'separator' => '.',
            ),
            'THB' => array(
                'label' => 'Thai Baht',
                'format' => '฿ %s',
                'separator' => '.',
            ),
            'TRY' => array(
                'label' => 'Turkish Lira',
                'format' => 'TRY %s', // Unicode is ₺ but this doesn't seem to be widely supported yet (introduced Sep 2012)
                'separator' => ',',
            ),
        );

        return $currencies;
    }

}
