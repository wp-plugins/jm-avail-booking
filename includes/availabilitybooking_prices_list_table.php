<?php

class AvailabilityBooking_Prices_List_Table extends WP_List_Table {

    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'price',
            'plural' => 'prices',
        ));
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    function column_default($item, $column_name) {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_name($item) {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=prices_form&id=%s">%s</a>', $item['id'], __('Edit', 'jm_avail_booking')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'jm_avail_booking')),
        );

        return sprintf('%s %s', $item['name'], $this->row_actions($actions)
        );
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'name' => __('Name', 'jm_avail_booking'),
            'date' => __('Date', 'jm_avail_booking'),
            'price' => __('Price', 'jm_avail_booking'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array('name', true),
            'date' => array('date', true),
            'price' => array('price', true),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * Add Filter to table navigation
     */
    function extra_tablenav($which) {
        if ($which == "top") {
            $options = get_option('jm_avail_booking_option_name');
            $room_names = explode(",", $options[rooms]);
            ?>
            <label><?php _e('Filter Room', 'jm_avail_booking') ?></label>
            <select name="filter_room" id="filter_room" required>
                <?php If ($options['rooms'] == "" ) { ?>
                <option value="default" <?php if (isset($_REQUEST['filter_room'])) selected($_REQUEST['filter_room'], 'default'); ?>><?php _e('default', 'jm_avail_booking') ?></option>
                <?php };
                foreach ($room_names as $name) {
                    $selected = "";
                    if ((isset($_REQUEST['filter_room'])) AND ( $_REQUEST['filter_room'] == $name)) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . $name . '" ' . $selected . '>' . $name . '</option>';
                }
                ?>
            </select>
            <?php
            $selected = 'selected = "selected"';
            ?>
            <label><?php _e('Filter Status', 'jm_avail_booking') ?></label>
            <select name = "filter_prices">
                <option value ="all" <?php if ($_REQUEST['filter_prices'] == 'all') echo $selected ?>><?php _e('All', 'jm_avail_booking') ?></option>
                <option value ="expired"<?php if ($_REQUEST['filter_prices'] == 'expired') echo $selected ?>><?php _e('Expired', 'jm_avail_booking') ?></option>
                <option value ="active"<?php if ($_REQUEST['filter_prices'] == 'active') echo $selected ?>><?php _e('Active', 'jm_avail_booking') ?></option>
            </select>
            <?php
            submit_button(__('Filter'), 'button', 'filter_action', false);
        }
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Prices'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids))
                $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Prices'; // do not forget about tables prefix

        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen->get_option('per_page', 'option');

        $per_page = get_user_meta($user, $option, true);

        if (empty($per_page) || $per_page < 1) {

            $per_page = $screen->get_option('per_page', 'default');
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        // prepare query params, as usual current page, order by and order direction

        $paged = $this->get_pagenum() - 1;
        $offset = 0;
        if (!empty($paged) && !empty($per_page)) {
            $offset = ($paged) * $per_page;
        }
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        if (isset($_REQUEST['s']) AND ( $_REQUEST['s'] != '')) {
            $search = trim($_REQUEST['s']);
            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE 
                `name` LIKE '%%%s%%'                 
                OR `date` LIKE '%%%s%%'                
                OR `price` LIKE '%%%s%%'
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d
                    ", $search, $search, $search, $per_page, $offset), ARRAY_A);
            $total_items = 0;
        } elseif (isset($_REQUEST['filter_room'])) {
            $name = $_REQUEST['filter_room'];
            $currentTime = current_time('mysql');
            list( $year, $month, $day ) = preg_split('([^0-9])', $currentTime);
            $avail_booking_db = new Avail_Booking_db();
            $last_price_array = $avail_booking_db->get_last_price($name, $month, $year);
            if ($last_price_array != -1) {
                $last_price_date = $last_price_array[0]['date'];
            }
            switch ($_REQUEST['filter_prices']) {
                case 'all':
                    $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE
                    `name` = '%s'
                    ORDER BY $orderby $order LIMIT %d OFFSET %d
                            ", $name, $per_page, $offset), ARRAY_A);
                    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE name = '$name'");
                    break;
                case 'expired':
                     $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE
                    `name` = '%s' AND date < '%s' 
                      ORDER BY $orderby $order LIMIT %d OFFSET %d
                              ", $name, $last_price_date, $per_page, $offset), ARRAY_A);
                    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE name = '$name' AND date < '$last_price_date'");
                    break;
                case 'active':
                     $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE
                    `name` = '%s' AND date >= '%s'
                    ORDER BY $orderby $order LIMIT %d OFFSET %d
                            ", $name, $last_price_date, $per_page, $offset), ARRAY_A);
                    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE name = '$name' AND date >= '$last_price_date'");
                    break;
            }
        } else {
            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset), ARRAY_A);
        }

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

}
