<?php

class AvailabilityBooking_Bookings_List_Table extends WP_List_Table {

    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'booking',
            'plural' => 'bookings',
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

    function column_status($item) {
        switch ($item['status']) {
            case 1:
                $item['status'] = __('requested', 'jm_avail_booking');
                return $item['status'];
                break;
            case 2:
                $item['status'] = __('reserved', 'jm_avail_booking');
                return $item['status'];
                break;
            case 3:
                $item['status'] = __('booked', 'jm_avail_booking');
                return $item['status'];
                break;
            case 4:
                $item['status'] = __('rejected', 'jm_avail_booking');
                return $item['status'];
                break;
        }
        return $item['status'];
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
            'edit' => sprintf('<a href="?page=bookings_form&id=%s">%s</a>', $item['id'], __('Edit', 'jm_avail_booking')),
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
            'status' => __('Status', 'jm_avail_booking'),
            'start_date' => __('Start Date', 'jm_avail_booking'),
            'end_date' => __('End Date', 'jm_avail_booking'),
            'email' => __('E-Mail', 'jm_avail_booking'),
            'phone' => __('Phone Number', 'jm_avail_booking'),
            'country' => __('Country', 'jm_avail_booking'),
            'language' => __('Language', 'jm_avail_booking')
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
            'status' => array('status', true),
            'start_date' => array('start_date', true),
            'end_date' => array('end_date', true),
            'email' => array('email', true),
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
            $selected = 'selected = "selected"';
            ?>
            <label><?php _e('Filter Time Range', 'jm_avail_booking') ?></label>
            <select name = "filter_bookings">
                <option value ="all" <?php if ($_REQUEST['filter_bookings'] == 'all') echo $selected ?>><?php _e('All', 'jm_avail_booking') ?></option>
                <option value ="old"<?php if ($_REQUEST['filter_bookings'] == 'old') echo $selected ?>><?php _e('Untill this Month', 'jm_avail_booking') ?></option>
                <option value ="new"<?php if ($_REQUEST['filter_bookings'] == 'new') echo $selected ?>><?php _e('This Month and later', 'jm_avail_booking') ?></option>
            </select>
            <label><?php _e('Filter Status', 'jm_avail_booking') ?></label>
            <select name = "filter_status">
                <option value ="0" <?php if ($_REQUEST['filter_status'] == '0') echo $selected ?>><?php _e('All', 'jm_avail_booking') ?></option>
                <option value ="1" <?php if ($_REQUEST['filter_status'] == '1') echo $selected ?>><?php _e('Requested', 'jm_avail_booking') ?></option>
                <option value ="2"<?php if ($_REQUEST['filter_status'] == '2') echo $selected ?>><?php _e('Reserved', 'jm_avail_booking') ?></option>
                <option value ="3"<?php if ($_REQUEST['filter_status'] == '3') echo $selected ?>><?php _e('Booked', 'jm_avail_booking') ?></option>
                <option value ="4"<?php if ($_REQUEST['filter_status'] == '4') echo $selected ?>><?php _e('Rejected', 'jm_avail_booking') ?></option>
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
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings'; // do not forget about tables prefix

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
        $table_name = $wpdb->prefix . 'AvailabilityBooking_Bookings'; // do not forget about tables prefix

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

            // Trim Search Term
            $search = trim($_REQUEST['s']);
            $search_status = 0;
            if ($search == __('requested', 'jm_avail_booking')) {
                $search_status = 1;
            }
            if ($search == __('booked', 'jm_avail_booking')) {
                $search_status = 2;
            }
            if ($search == __('rejected', 'jm_avail_booking')) {
                $search_status = 3;
            }
            if ($search == __('waiting', 'jm_avail_booking')) {
                $search_status = 4;
            }

            /* Notice how you can search multiple columns for your search term easily, and return one data set */
            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE 
                `name` LIKE '%%%s%%' 
                OR `status`= '$search_status' 
                OR `start_date` LIKE '%%%s%%'
                OR `end_date` LIKE '%%%s%%'
                OR `email` LIKE '%%%s%%'
                ORDER BY $orderby $order
                LIMIT %d OFFSET %d
                    ", $search, $search, $search, $search, $per_page, $offset), ARRAY_A);
            $total_items = 0 ;
        
        } elseif (isset($_REQUEST['filter_bookings'])) {
            // determine current month
            $from = $_REQUEST['filter_status'];
            $to = $_REQUEST['filter_status'];
            if ($to == 0){$to = 5; } 
            $currentTime = date("Y-m-01");
            if ($_REQUEST['filter_bookings'] == 'old') {
                $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE end_date < '$currentTime' AND status BETWEEN $from AND $to ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset), ARRAY_A);
                $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE end_date < '$currentTime' AND status BETWEEN $from AND $to");
            } else {
                if ($_REQUEST['filter_bookings'] == 'all') $currentTime = '2000-01-01';
                $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE end_date >= '$currentTime' AND status BETWEEN $from AND $to ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset), ARRAY_A);
                $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE end_date >= '$currentTime' AND status BETWEEN $from AND $to ");
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
