<?php

class Avail_Booking_SettingsPage {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
                'Settings Admin', '' . __('WP Availability Calendar & Booking Settings', 'jm_avail_booking') . '', 'manage_options', 'jm_avail_booking-setting-admin', array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        // Set class property
        $this->options = get_option('jm_avail_booking_option_name');
        ?>
        <div class="wrap">

            <h2>Availability Calendar and Booking</h2>           
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('jm_avail_booking_option_group');
                do_settings_sections('jm_avail_booking-setting-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        $option_name = $this->options;
        register_setting(
                'jm_avail_booking_option_group', // Option group
                'jm_avail_booking_option_name', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'setting_section_id', // ID
                '' . __('Settings', 'jm_avail_booking') . '', // Title
                array($this, 'print_section_info'), // Callback
                'jm_avail_booking-setting-admin' // Page
        );
        add_settings_field(
                'threemonths', ' ' . __('Calendar Display', 'jm_avail_booking') . '', array($this, 'threemonths_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'in_widget', ' ' . __('Used in Widget', 'jm_avail_booking') . '', array($this, 'in_widget_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'firstlast', ' ' . __('Display Last Day as free', 'jm_avail_booking') . '', array($this, 'firstlast_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                'weeknumbers', ' ' . __('Show Weeknumbers', 'jm_avail_booking') . '', array($this, 'weeknumbers_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'showprices', ' ' . __('Show Prices', 'jm_avail_booking') . '', array($this, 'showprices_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
       
        add_settings_field(
                'default_currency', ' ' . __('Default Currency', 'jm_avail_booking') . '', array($this, 'default_currency_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'min_nights', ' ' . __('Minimum Nights', 'jm_avail_booking') . '', array($this, 'min_nights_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'rooms', ' ' . __('List of Rooms', 'jm_avail_booking') . '', array($this, 'rooms_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'status', ' ' . __('Status booking with ContactForm7', 'jm_avail_booking') . '', array($this, 'status_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id', array(
            'options-name' => 'jm_avail_booking_option_name',
            'id' => 'status',
            'class' => '',
            'value' => array(
                '1' => __('Requested', 'jm_avail_booking'),
                '2' => __('Reserved', 'jm_avail_booking'),
                '3' => __('Booked', 'jm_avail_booking'),
            ),
            'label' => __('Select value of status after a booking with ContactForm 7.', 'jm_avail_booking'),
        ));
        add_settings_field(
                'booking_form', ' ' . __('Title of page with booking form', 'jm_avail_booking') . '', array($this, 'booking_form_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id', array(
            'options-name' => 'jm_avail_booking_option_name',
            'id' => 'booking_form',
            'class' => '',
            'value' => '',
            'label' => __('Select page with the single booking form. Open also the permalinks settings and click on save!! ', 'jm_avail_booking'),
        ));
        add_settings_field(
                'default_status', ' ' . __('Default Status', 'jm_avail_booking') . '', array($this, 'default_status_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id', array(
            'options-name' => 'jm_avail_booking_option_name',
            'id' => 'default_status',
            'class' => '',
            'value' => array(
                '1' => __('Requested', 'jm_avail_booking'),
                '2' => __('Reserved', 'jm_avail_booking'),
                '3' => __('Booked', 'jm_avail_booking'),
                '4' => __('Rejected', 'jm_avail_booking'),
            ),
            'label' => __('Select the default status for new bookings', 'jm_avail_booking'),
        ));
        add_settings_field(
                'default_country', ' ' . __('Default Country', 'jm_avail_booking') . '', array($this, 'default_country_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'default_language', ' ' . __('Default Language', 'jm_avail_booking') . '', array($this, 'default_language_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id', array(
            'options-name' => 'jm_avail_booking_option_name',
            'id' => 'default_language',
            'class' => '',
            'value' => array(
                'nl' => __('NL', 'jm_avail_booking'),
                'en' => __('EN', 'jm_avail_booking'),                
            ),
            'label' => __('Select the default language for new bookings', 'jm_avail_booking'),
        ));
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if (isset($input['threemonths']))
            $new_input['threemonths'] = absint($input['threemonths']);
        if (isset($input['in_widget']))
            $new_input['in_widget'] = absint($input['in_widget']);
        if (isset($input['firstlast']))
            $new_input['firstlast'] = absint($input['firstlast']);
        if (isset($input['weeknumbers']))
            $new_input['weeknumbers'] = absint($input['weeknumbers']);
        if (isset($input['showprices']))
            $new_input['showprices'] = absint($input['showprices']);        
        if (isset($input['default_currency']))
            $new_input['default_currency'] = sanitize_text_field($input['default_currency']);
        if (isset($input['min_nights']))
            $new_input['min_nights'] = absint($input['min_nights']);
        if (isset($input['rooms']))
            $new_input['rooms'] = sanitize_text_field($input['rooms']);
        if (isset($input['status']))
            $new_input['status'] = sanitize_text_field($input['status']);
        if (isset($input['booking_form']))
            $new_input['booking_form'] = sanitize_text_field($input['booking_form']);
        if (isset($input['default_status']))
            $new_input['default_status'] = sanitize_text_field($input['default_status']);
        if (isset($input['default_country']))
            $new_input['default_country'] = sanitize_text_field($input['default_country']);
        if (isset($input['default_language']))
            $new_input['default_language'] = sanitize_text_field($input['default_language']);

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        _e('Check the following settings:', 'jm_avail_booking');
    }

    public function threemonths_callback() {
        echo '<input type="checkbox" id="threemonths" name="jm_avail_booking_option_name[threemonths]" value="1" ' . checked(1, $this->options['threemonths'], false) . ' />';
        _e('Display a block of three months - one month default', 'jm_avail_booking');
    }
    public function in_widget_callback() {
        echo '<input type="checkbox" id="in_widget" name="jm_avail_booking_option_name[in_widget]" value="1" ' . checked(1, $this->options['in_widget'], false) . ' />';
        _e('The calendar is also used in a custom text widget', 'jm_avail_booking');
    }
    public function firstlast_callback() {
        echo '<input type="checkbox" id="firstlast" name="jm_avail_booking_option_name[firstlast]" value="1" ' . checked(1, $this->options['firstlast'], false) . ' />';
        _e('Checkout and new Checkin on same day', 'jm_avail_booking');
    }

    public function weeknumbers_callback() {
        echo '<input type="checkbox" id="weeknumbers" name="jm_avail_booking_option_name[weeknumbers]" value="1" ' . checked(1, $this->options['weeknumbers'], false) . ' />';
    }

    public function showprices_callback() {
        echo '<input type="checkbox" id="showprices" name="jm_avail_booking_option_name[showprices]" value="1" ' . checked(1, $this->options['showprices'], false) . ' />';
    }

    
    public function default_currency_callback() {
        $currencies = AvailabilityBookingFunctions::currency();
        ?>
        <select name="jm_avail_booking_option_name[default_currency]" id="jm_avail_booking_option_name[default_currency]" required>
            <?php
            foreach ($currencies as $key => $currency) {
                $test = $key;
                $test2 = $currency;
                ?>                       

                <option value="<?php echo $key ?>" <?php if ($this->options['default_currency'] == $key) echo 'selected="selected"'; ?>><?php echo $currency['label'] ?></option>
                <?php
            }
            ?>                        
        </select>
        <?php
        _e('Select  the default currency for new prices', 'jm_avail_booking');
    }
    public function min_nights_callback() {
        printf('<input id="min_nights" name="jm_avail_booking_option_name[min_nights]" size="4" type="text" value="%s" />', isset($this->options['min_nights']) ? esc_attr($this->options['min_nights']) : '1');
    }

    public function rooms_callback() {
        echo '<textarea id="rooms" name="jm_avail_booking_option_name[rooms]" rows="5" cols="50">' . $this->options['rooms'] . '</textarea>';
        echo '<p>';
        _e('Give the available room names separated by a comma. If empty the name default is used', 'jm_avail_booking');
        echo '</p>';
    }

    public function status_callback($args) {
        // Set the options-name value to a variable
        $name = $args['options-name'] . '[' . $args['id'] . ']';

        // Get the options from the database
        $options = get_option($args['options-name']);
        ?>

        <select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if (!empty($args['class'])) echo 'class="' . $args['class'] . '" '; ?>>
            <?php foreach ($args['value'] as $key => $value) : ?>
                <option value="<?php esc_attr_e($key); ?>"<?php if (isset($options[$args['id']])) selected($key, $options[$args['id']], true); ?>><?php esc_attr_e($value); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="<?php echo $args['id']; ?>" style=""><?php esc_attr_e($args['label']); ?></label>

        <?php
    }

    public function booking_form_callback($args) {
        // Set the options-name value to a variable
        $name = $args['options-name'] . '[' . $args['id'] . ']';

        // Get the options from the database
        $options = get_option($args['options-name']);
        ?>

        <select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>"> 
            <option selected="selected"  value=""><?php echo esc_attr(__('No generic form', 'jm_avail_booking')); ?></option> 
            <?php
            $selected_page = $options[$args['id']];
            $pages = get_pages();
            foreach ($pages as $page) {
                $option = '<option value="' . $page->post_title . '" ';
                $option .= ( $page->post_title == $selected_page ) ? 'selected="selected"' : '';
                $option .= '>';
                $option .= $page->post_title;
                $option .= '</option>';
                echo $option;
            }
            ?>
        </select>
        <label for="<?php echo $args['id']; ?>" style=""><?php esc_attr_e($args['label']); ?></label>
        <?php
    }

    public function default_status_callback($args) {
        // Set the options-name value to a variable
        $name = $args['options-name'] . '[' . $args['id'] . ']';

        // Get the options from the database
        $options = get_option($args['options-name']);
        ?>

        <select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if (!empty($args['class'])) echo 'class="' . $args['class'] . '" '; ?>>
            <?php foreach ($args['value'] as $key => $value) : ?>
                <option value="<?php esc_attr_e($key); ?>"<?php if (isset($options[$args['id']])) selected($key, $options[$args['id']], true); ?>><?php esc_attr_e($value); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="<?php echo $args['id']; ?>" style=""><?php esc_attr_e($args['label']); ?></label>

        <?php
    }

    public function default_country_callback() {
        $countries = array("Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Arctic Ocean", "Aruba", "Ashmore and Cartier Islands", "Atlantic Ocean", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Baker Island", "Bangladesh", "Barbados", "Bassas da India", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Virgin Islands", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Clipperton Island", "Cocos Islands", "Colombia", "Comoros", "Cook Islands", "Coral Sea Islands", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Deutschland", "Democratic Republic of the Congo", "Djibouti", "Deutschland", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Europa Island", "Falkland Islands (Islas Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern and Antarctic Lands", "Gabon", "Gambia", "Gaza Strip", "Georgia", "Ghana", "Gibraltar", "Glorioso Islands", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard Island and McDonald Islands", "Honduras", "Hong Kong", "Howland Island", "Hungary", "Iceland", "India", "Indian Ocean", "Indonesia", "Iran", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Jan Mayen", "Japan", "Jarvis Island", "Jersey", "Johnston Atoll", "Jordan", "Juan de Nova Island", "Kazakhstan", "Kenya", "Kingman Reef", "Kiribati", "Kerguelen Archipelago", "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia", "Midway Islands", "Moldova", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Navassa Island", "Nepal", "Nederland", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "North Korea", "North Sea", "Northern Mariana Islands", "Norway", "Oman", "Pacific Ocean", "Pakistan", "Palau", "Palmyra Atoll", "Panama", "Papua New Guinea", "Paracel Islands", "Paraguay", "Peru", "Philippines", "Pitcairn Islands", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Republic of the Congo", "Romania", "Russia", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "South Korea", "Spain", "Spratly Islands", "Sri Lanka", "Sudan", "Suriname", "Svalbard", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tromelin Island", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "USA", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands", "Wake Island", "Wallis and Futuna", "West Bank", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
        ?>
        <select name="jm_avail_booking_option_name[default_country]" id="jm_avail_booking_option_name[default_country]" required>
            <?php
            foreach ($countries as $country) {
                ?>                       

                <option value="<?php echo $country ?>" <?php if ($this->options['default_country'] == $country) echo 'selected="selected"'; ?>><?php echo $country ?></option>
                <?php
            }
            ?>                        
        </select>
        <?php
        _e('Select  the default country for new bookings', 'jm_avail_booking');
    }
    public function default_language_callback($args) {
        // Set the options-name value to a variable
        $name = $args['options-name'] . '[' . $args['id'] . ']';

        // Get the options from the database
        $options = get_option($args['options-name']);
        ?>

        <select name="<?php echo $name; ?>" id="<?php echo $args['id']; ?>" <?php if (!empty($args['class'])) echo 'class="' . $args['class'] . '" '; ?>>
            <?php foreach ($args['value'] as $key => $value) : ?>
                <option value="<?php esc_attr_e($key); ?>"<?php if (isset($options[$args['id']])) selected($key, $options[$args['id']], true); ?>><?php esc_attr_e($value); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="<?php echo $args['id']; ?>" style=""><?php esc_attr_e($args['label']); ?></label>

        <?php
    }

}
