<?php
add_action('admin_menu', 'Avail_Booking_Bookings_admin_menu');

// Register date picker
//add_action('admin_enqueue_scripts', 'Avail_Booking_enqueue_date_picker');

/*
 * Admin Settings fields 
 */

function Avail_Booking_Bookings_admin_menu() {
    $hook = add_menu_page(__('bookings', 'jm_avail_booking'), __('WP Availability Calendar & Bookings', 'jm_avail_booking'), 'activate_plugins', 'bookings', 'Avail_booking_Bookings_page_handler');
    add_action("load-$hook", 'avial_booking_add_option');
    add_submenu_page('bookings', __('WP Availability Calendar & Bookings', 'jm_avail_booking'), __('Bookings', 'jm_avail_booking'), 'activate_plugins', 'bookings', 'Avail_booking_Bookings_page_handler');
    add_submenu_page('bookings', __('Add new Booking', 'jm_avail_booking'), __('Add new Booking', 'jm_avail_booking'), 'activate_plugins', 'bookings_form', 'Avail_booking_Bookings_form_page_handler');
}

// Screen options
function avial_booking_add_option() {
    $option = 'per_page';

    $args = array(
        'label' => __('Bookings', 'jm_avail_booking'),
        'default' => 10,
        'option' => 'avial_booking_bookings_per_page'
    );

    add_screen_option($option, $args);
}

/**
 * Enqueue the date picker
 */
/*
function Avail_Booking_enqueue_date_picker() {
    wp_enqueue_script(
            'field-date-js', plugins_url('jm-avail-booking/js/Field_Date.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true
    );
    If (get_bloginfo('language') == 'nl-NL') {
        wp_enqueue_script(
                'jquery.ui.datepicker-nl.js', plugins_url('jm-avail-booking/js/jquery.ui.datepicker-nl.js'), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),time(), true
        );
    }
    wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
}
*/
function Avail_booking_Bookings_page_handler() {
    global $wpdb;

    $table = new AvailabilityBooking_Bookings_List_Table();
    $table->prepare_items();

    //Fetch, prepare, sort, and filter our data...
    if (isset($_POST['s'])) {
        $table->prepare_items($_POST['s']);
    } else {
        $table->prepare_items('null');
    }

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'jm_avail_booking'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
    <div class="wrap">
        
        <h2><?php _e('Bookings', 'jm_avail_booking') ?> <a class="add-new-h2"
                                                           href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bookings_form'); ?>"><?php _e('Add new booking', 'jm_avail_booking') ?></a>
        </h2>
        <?php echo $message; ?>
        <form method="post">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $table->search_box('search', 'search_id'); ?>
        </form>
        <form id="bookings-table" method="GET">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

            <?php $table->display() ?>
        </form>

    </div>
    <?php
}

/**
 * Form page handler checks is there some data posted and tries to save it
 * Also it renders basic wrapper in which we are callin meta box render
 */
function Avail_booking_Bookings_form_page_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings'; // do not forget about tables prefix
    $options = get_option('jm_avail_booking_option_name');
    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => 'default',
        'status' => $options['default_status'],
        'start_date' => '',
        'end_date' => '',
        'email' => '',
        'phone' => '',
        'country' => $options['default_country'],
        'language' =>$options['default_language'],
    );

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
// combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
// validate data, and if all ok save item to database
// if id is zero insert otherwise update
        $item_valid = Avail_booking_validate_booking($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result === false) {
                    $notice = __('There was an error while saving item', 'jm_avail_booking');                    
                } else {
                    $message = __('Item was successfully saved', 'jm_avail_booking');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result === false) {
                    $notice = __('There was an error while updating item', 'jm_avail_booking');
                } else {
                    $message = __('Item was successfully updated', 'jm_avail_booking');
                }
            }
        } else {
// if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    } else {
// if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'jm_avail_booking');
            }
        }
    }

// here we adding our custom meta box
    add_meta_box('bookings_form_meta_box', __('Bookings Data', 'jm_avail_booking'), 'Avail_booking_Bookings_form_meta_box_handler', 'booking', 'normal', 'default');
    ?>
    <div class="wrap">        
        <h2><?php _e('Bookings', 'jm_avail_booking') ?> <a class="add-new-h2"
                                                           href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=bookings'); ?>"><?php _e('back to list', 'jm_avail_booking') ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>"/>
            <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
            <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php /* And here we call our custom meta box */ ?>
                        <?php do_meta_boxes('booking', 'normal', $item); ?>
                        <input type="submit" value="<?php _e('Save', 'jm_avail_booking') ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function Avail_booking_Bookings_form_meta_box_handler($item) {
    $options = get_option('jm_avail_booking_option_name');    
    $room_names= array_map(function($el) {
        return explode(':', $el);
    }, explode(',', $options['rooms']));
    $countries = array("Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Arctic Ocean", "Aruba", "Ashmore and Cartier Islands", "Atlantic Ocean", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Baker Island", "Bangladesh", "Barbados", "Bassas da India", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Virgin Islands", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Clipperton Island", "Cocos Islands", "Colombia", "Comoros", "Cook Islands", "Coral Sea Islands", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Deutschland", "Democratic Republic of the Congo", "Djibouti", "Deutschland", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Europa Island", "Falkland Islands (Islas Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern and Antarctic Lands", "Gabon", "Gambia", "Gaza Strip", "Georgia", "Ghana", "Gibraltar", "Glorioso Islands", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard Island and McDonald Islands", "Honduras", "Hong Kong", "Howland Island", "Hungary", "Iceland", "India", "Indian Ocean", "Indonesia", "Iran", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Jan Mayen", "Japan", "Jarvis Island", "Jersey", "Johnston Atoll", "Jordan", "Juan de Nova Island", "Kazakhstan", "Kenya", "Kingman Reef", "Kiribati", "Kerguelen Archipelago", "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia", "Midway Islands", "Moldova", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Navassa Island", "Nepal", "Nederland", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "North Korea", "North Sea", "Northern Mariana Islands", "Norway", "Oman", "Pacific Ocean", "Pakistan", "Palau", "Palmyra Atoll", "Panama", "Papua New Guinea", "Paracel Islands", "Paraguay", "Peru", "Philippines", "Pitcairn Islands", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Republic of the Congo", "Romania", "Russia", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "South Korea", "Spain", "Spratly Islands", "Sri Lanka", "Sudan", "Suriname", "Svalbard", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tromelin Island", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "USA", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Viet Nam", "Virgin Islands", "Wake Island", "Wallis and Futuna", "West Bank", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
    ?>

    <table  cellspacing="2" cellpadding="5" style="width: 50%;" class="form-table">
        <tbody>                 

            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php _e('Name', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <select name="name" id="name" required>
                        <?php If ($options['rooms'] == "" ) { ?>
                         <option value="default" <?php if (isset($item['name'])) selected($item['name'][0], 'default'); ?>><?php _e('default', 'jm_avail_booking') ?></option> 
                        <?php };
                        foreach ($room_names as $name) {
                            $selected = "";
                            if ((isset($item['name'])) AND ( $item['name'] == $name[0])) {
                                $selected = 'selected';
                            }
                            echo '<option value="' . $name[0] . '" ' . $selected . '>' . $name[0] . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="status"><?php _e('Status', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <select name="status" id="status" required>
                        <option value="1" <?php if (isset($item['status'])) selected($item['status'][0], '1'); ?>><?php _e('Requested', 'jm_avail_booking') ?></option>
                        <option value="2" <?php if (isset($item['status'])) selected($item['status'][0], '2'); ?>><?php _e('Reserved', 'jm_avail_booking') ?></option>
                        <option value="3" <?php if (isset($item['status'])) selected($item['status'][0], '3'); ?>><?php _e('Booked', 'jm_avail_booking') ?></option>
                        <option value="4" <?php if (isset($item['status'])) selected($item['status'][0], '4'); ?>><?php _e('Rejected', 'jm_avail_booking') ?></option>

                    </select>

                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="start_date"><?php _e('Check In', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="start_date" name="start_date" type="text" style="width: 45%"  value="<?php echo esc_attr($item['start_date']) ?>"
                           size="50" class="code"  placeholder="<?php _e('yyyy-mm-dd', 'jm_avail_booking') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="end_date"><?php _e('Check Out', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="end_date" name="end_date" type="text" style="width: 45%" value="<?php echo esc_attr($item['end_date']) ?>"
                           size="25" class="code" placeholder="<?php _e('yyyy-mm-dd', 'jm_avail_booking') ?>" required>
                </td>
            </tr>                
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="email"><?php _e('E-Mail', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="email" name="email" type="email" style="width: 45%" value="<?php echo esc_attr($item['email']) ?>"
                           size="50" class="code" placeholder="<?php _e('E-Mail', 'jm_avail_booking') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="Phone"><?php _e('Phone Number', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="phone" name="phone" type="text" style="width: 45%" value="<?php echo esc_attr($item['phone']) ?>"
                           size="50" class="code" placeholder="<?php _e('Phone Number', 'jm_avail_booking') ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="CountryPhone"><?php _e('Country', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <select name="country" id="country" required>
                        <?php
                        foreach ($countries as $country)    {
                            ?>                       
                            <option value="<?php echo $country ?>" <?php if ( $item['country'] == $country ) echo 'selected="selected"'; ?>><?php echo $country?></option>
                            <?php 
                        }
                        ?>                        
                    </select>

                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="Language"><?php _e('Language', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <select name="language" id="language" required>
                        <option value="nl" <?php if ( $item['language'] == 'nl' ) echo 'selected="selected"'; ?>>NL</option>
                        <option value="en" <?php if ( $item['language'] == 'en' ) echo 'selected="selected"'; ?>>EN</option>
                    </select>

                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function Avail_booking_validate_booking($item) {
    $messages = array();

    if (empty($item['name']))
        $messages[] = __('Name is required', 'jm_avail_booking');
    if (empty($item['status']))
        $messages[] = __('Status is required', 'jm_avail_booking');
    if (empty($item['start_date']))
        $messages[] = __('Start Date is required', 'jm_avail_booking');
    if (empty($item['end_date']))
        $messages[] = __('End Date is required', 'jm_avail_booking');
    if (!empty($item['email']) && !is_email($item['email']))
        $messages[] = __('E-Mail is in wrong format', 'jm_avail_booking');
    if (empty($item['phone'])) 
        $messages[] = __('Phone number is required', 'jm_avail_booking');
    

//if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
//if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
//...

    if (empty($messages))
        return true;
    return implode('<br />', $messages);
}
