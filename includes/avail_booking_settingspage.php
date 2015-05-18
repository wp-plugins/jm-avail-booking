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
        $option_name = $this->options ;
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
                'firstlast', ' ' . __('Display Last Day as free', 'jm_avail_booking') . '', array($this, 'firstlast_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );

        add_settings_field(
                'weeknumbers', ' ' . __('Show Weeknumbers', 'jm_avail_booking') . '', array($this, 'weeknumbers_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'showprices', ' ' . __('Show Prices', 'jm_avail_booking') . '', array($this, 'showprices_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
        );
        add_settings_field(
                'usedollar', ' ' . __('Use Dollar sign', 'jm_avail_booking') . '', array($this, 'usedollar_callback'), 'jm_avail_booking-setting-admin', 'setting_section_id'
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

        if (isset($input['firstlast']))
            $new_input['firstlast'] = absint($input['firstlast']);

        if (isset($input['weeknumbers']))
            $new_input['weeknumbers'] = absint($input['weeknumbers']);

        if (isset($input['showprices']))
            $new_input['showprices'] = absint($input['showprices']);
        if (isset($input['usedollar']))
            $new_input['usedollar'] = absint($input['usedollar']);

        if (isset($input['min_nights']))
            $new_input['min_nights'] = absint($input['min_nights']);

        if (isset($input['rooms']))
            $new_input['rooms'] = sanitize_text_field($input['rooms']);
        if (isset($input['status']))
            $new_input['status'] = sanitize_text_field($input['status']);
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

    public function usedollar_callback() {
        echo '<input type="checkbox" id="usedollar" name="jm_avail_booking_option_name[usedollar]" value="1" ' . checked(1, $this->options['usedollar'], false) . ' />';
        _e('Default Euro', 'jm_avail_booking');
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

}
