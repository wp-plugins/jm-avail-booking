<?php
add_action('admin_menu', 'Avail_Booking_Prices_admin_menu');

function Avail_Booking_Prices_admin_menu() {
    $hook = add_submenu_page('bookings', __('WP Availability Calendar & Bookings', 'jm_avail_booking'), __('Prices', 'jm_avail_booking'), 'activate_plugins', 'prices', 'Avail_booking_Prices_page_handler');
    add_action("load-$hook", 'avial_booking_add_price_option');
    add_submenu_page('bookings', __('Add new Price', 'jm_avail_booking'), __('Add new Price', 'jm_avail_booking'), 'activate_plugins', 'prices_form', 'Avail_booking_Prices_form_page_handler');
}

// Screen options
function avial_booking_add_price_option() {
    $option = 'per_page';

    $args = array(
        'label' => __('Prices', 'jm_avail_booking'),
        'default' => 10,
        'option' => 'avial_booking_prices_per_page'
    );

    add_screen_option($option, $args);
}

function Avail_booking_Prices_page_handler() {
    global $wpdb;

    $table = new AvailabilityBooking_Prices_List_Table();
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
        <h2><?php _e('Prices', 'jm_avail_booking') ?> <a class="add-new-h2"
                                                         href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=prices_form'); ?>"><?php _e('Add new price', 'jm_avail_booking') ?></a>
        </h2>
        <?php echo $message; ?>
        <form method="post">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $table->search_box('search', 'search_id'); ?>
        </form>
        <form id="prices-table" method="GET">
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
function Avail_booking_Prices_form_page_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'AvailabilityBooking_Prices'; // do not forget about tables prefix

    $message = '';
    $notice = '';

// this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'name' => 'default',
        'date' => '',
        'price' => '',
    );   

// here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
// combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
// validate data, and if all ok save item to database
// if id is zero insert otherwise update
        $item_valid = Avail_booking_validate_price($item);
        if ($item_valid === true) {
            $options = get_option('jm_avail_booking_option_name');
            $temp = preg_replace('/[^0-9\.\,]/', "", $item['price']);
            $currencies = AvailabilityBookingFunctions::currency();
            $format = $currencies[$options['default_currency']]['format'];
            $seperator = $currencies[$options['default_currency']]['separator'];
            $temp = number_format($temp, 2, $seperator , '');
            $item['price'] = sprintf($format,$temp);
            
            if ($item['id'] == 0) {
                $name = $item['name'];
                $date = $item['date'];
                $result = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE name = '$name' AND date = '$date' ");
                if ($result) {
                    $notice = __('Item not saved. Date already used', 'jm_avail_booking');
                } else {
                    $result = $wpdb->insert($table_name, $item);

                    $item['id'] = $wpdb->insert_id;
                    if ($result === false) {
                        $notice = __('There was an error while saving item', 'jm_avail_booking');                        
                    } else {
                        $message = __('Item was successfully saved', 'jm_avail_booking');
                    }
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
    add_meta_box('prices_form_meta_box', __('Price Data', 'jm_avail_booking'), 'Avail_booking_Prices_form_meta_box_handler', 'prices', 'normal', 'default');
    ?>
    <div class="wrap">
        
        <h2><?php _e('Prices', 'jm_avail_booking') ?> <a class="add-new-h2"
                                                         href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=prices'); ?>"><?php _e('back to list', 'jm_avail_booking') ?></a>
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
                        <?php do_meta_boxes('prices', 'normal', $item); ?>
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
function Avail_booking_Prices_form_meta_box_handler($item) {
    $options = get_option('jm_avail_booking_option_name');
    $room_names = explode(",", $options[rooms]);
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
                            if ((isset($item['name'])) AND ( $item['name'] == $name)) {
                                $selected = 'selected';
                            }
                            echo '<option value="' . $name . '" ' . $selected . '>' . $name . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>


            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="date"><?php _e('Date', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="date" name="date" type="text" style="width: 45%"  value="<?php echo esc_attr($item['date']) ?>"
                           size="50" class="code"  placeholder="<?php _e('yyyy-mm-dd', 'jm_avail_booking') ?>" required>
                </td>
            </tr>

            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="price"><?php _e('Price', 'jm_avail_booking') ?></label>
                </th>
                <td>
                    <input id="price" name="price" type="text" style="width: 45%" value="<?php echo esc_attr($item['price']) ?>"
                           size="50" class="code"  required><br><?php _e(' On save the price input will be formatted conform the settings', 'jm_avail_booking') ?>
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
function Avail_booking_validate_price($item) {
    $messages = array();
    if (empty($item['name']))
        $messages[] = __('Name is required', 'jm_avail_booking');

    if (empty($item['date']))
        $messages[] = __('Date is required', 'jm_avail_booking');

    if (empty($item['price']))
        $messages[] = __('Price is required', 'jm_avail_booking');

//if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
//if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
//...

    if (empty($messages))
        return true;
    return implode('<br />', $messages);
}
