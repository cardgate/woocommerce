<?php

/**
 * Title: Payments list table
 * Description: 
 * Copyright: Copyright (c) 2012
 * Company: Cardgate
 * @author CardGate
 * @version 1.0
 */
class Cardgate_PaymentsListTable extends WP_List_Table {

    /**
     * Constructs and initializes a payments list table
     */
    public function __construct() {
        parent::__construct( array(
            'single' => 'payment',
            'plural' => 'payments',
            'ajax' => false //We won't support Ajax for this table
        ) );
    }

    /**
     * Checks the current user's permissions
     */
    function ajax_user_can() {
        return current_user_can( 'manage_cardgate_payments' );
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        global $wpdb;
        
        $qryWhere = '';

        /* -- Process actions -- */
        $this->process_bulk_action();

        /* -- Preparing the query -- */
        $query = "SELECT * FROM " . $wpdb->prefix . 'cardgate_payments';

        /* -- handle search string if it exists -- */
        if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) {
            $search = $_REQUEST['s'];
            $columns = $this->get_columns();
            foreach ( $columns as $k => $v ) {
                if ( $k != 'cb' ) {
                    $qryWhere .= $k . " LIKE '%" . $search . "%' || ";
                }
            }
            $query .= " WHERE " . substr( $qryWhere, 0, -3 );
        }

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty( $_GET["orderby"] ) ? $_GET["orderby"] : 'date_gmt';
        $order = !empty( $_GET["order"] ) ? $_GET["order"] : 'DESC';
        if ( !empty( $orderby ) & !empty( $order ) ) {
            $query.=' ORDER BY ' . $orderby . ' ' . $order;
        }
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query( $query ); //return the total number of affected rows
        //How many to display per page?
        $perpage = 10;
        //Which page is this?
        $paged = !empty( $_GET["paged"] ) ? $_GET["paged"] : '';
        if ( !empty( $_GET['paged'] ) && $totalitems < $paged * $perpage )
            $paged = '';
        //Page Number
        if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
            $paged = 1;
        }
        //How many pages do we have in total?
        $totalpages = ceil( $totalitems / $perpage );
        //adjust the query to take pagination into account
        if ( !empty( $paged ) && !empty( $perpage ) ) {
            $offset = ($paged - 1) * $perpage;
            $query.=' LIMIT ' . ( int ) $offset . ',' . ( int ) $perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        /* -- Fetch the items -- */

        $items = $wpdb->get_results( $query, ARRAY_A );
        // swap the order number for the sequential order number if it exists
        $this->items = $this->swap_order_numbers( $items );
    }

    /**
     * Message to be displayed when there are no items
     */
    function no_items() {
        _e( 'No payments found.', 'cardgate' );
    }

    /**
     * Get a list of columns
     */
    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'date_gmt' => __( 'Date', 'cardgate' ),
            'order_id' => 'Order ID',
            'transaction_id' => __( 'Transaction ID', 'cardgate' ),
            'first_name' => __( 'Customer Name', 'cardgate' ),
            'amount' => __( 'Amount', 'cardgate' ),
            'status' => __( 'Payment Status', 'cardgate' )
        );
    }

    /**
     * Get a list of sortable columns
     */
    function get_sortable_columns() {
        return array(
            'date_gmt' => array( 'date_gmt', false ),
            'amount' => array( 'amount', false ),
            'status' => array( 'status', false )
        );
    }

    function column_cb( $item ) {
        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ 'payments',
                /* $2%s */ $item['order_id']
        );
    }

    /**
     * Output for date column
     */
    function column_date_gmt( $item ) {
        //Build row actions
        if ( isset( $_REQUEST['s'] ) ) {
            $s = $_REQUEST['s'];
        } else {
            $s = '';
        }
        
        if ( !empty($_REQUEST['orderby']) && !empty($_REQUEST['order']) ) {
            $actions = array(
                'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&orderby=%s&order=%s&s=%s">Delete</a>', $_REQUEST['page'], 'delete1', $item['id'], $_REQUEST['orderby'], $_REQUEST['order'], $s ),
            );
        } else {
            $actions = array(
                'delete' => sprintf( '<a href="?page=%s&action=%s&id=%s&s=%s">Delete</a>', $_REQUEST['page'], 'delete1', $item['id'], $s ),
            );
        }
        
        

        //Return the title contents
        return sprintf( '%1$s%2$s',
                /* $1%s */ $item['date_gmt'],
                /* $3%s */ $this->row_actions( $actions )
        );
    }

    /**
     * Output for order ID column
     */
    function column_order_id( $item ) {
        echo $item['order_id'];
    }

    /**
     * Output for transaction ID column
     */
    function column_transaction_id( $item ) {
        echo $item['transaction_id'];
    }

    /**
     * Output for First Name column
     */
    function column_first_name( $item ) {
        echo $item['first_name'] . ' ' . $item['last_name'];
    }

    /**
     * Output for amount column
     */
    function column_amount( $item ) {
        $c = array( 'EUR' => '&euro;', 'GBP' => '&pound;', 'USD' => '&dollar;' );
        $item['currency'];
        echo $c[$item['currency']] . ' ' . number_format( $item['amount'] / 100, 2 );
    }

    /**
     * Output for status column
     */
    function column_status( $item ) {
        echo $item['status'];
    }

    /**
     * Set bulk action options
     */
    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * Process bulk actions
     */
    function process_bulk_action() {
        global $wpdb;
        $table = $wpdb->prefix . 'cardgate_payments';

        // Delete a simgle action
        if ( 'delete1' === $this->current_action() ) {
            $query = $wpdb->prepare( "DELETE FROM $table WHERE id=%d LIMIT 1", $_REQUEST['id'] );
            $wpdb->query( $query );
            return;
        }

        //Delete when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
            $max = count( $_REQUEST['payments'] );
            $s = '';
            for ( $x = 0; $x < $max; $x++ ) {
                $s .=$wpdb->prepare( "%d", $_REQUEST['payments'][$x] );
                if ( $x != $max - 1 )
                    $s .=', ';
            }
            $query = "DELETE FROM $table WHERE order_id IN ($s)";
            $wpdb->query( $query );
        }
    }

    /**
     * Display the pagination.
     *
     * @since 3.1.0
     * @access protected
     */
    function pagination( $which ) {
        if ( empty( $this->_pagination_args ) )
            return;
        $page_args = $this->_pagination_args;
        extract( $page_args );

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();

        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
        $current_url = remove_query_arg( 's', $current_url );
        if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) {
            $current_url = add_query_arg( 's', $_REQUEST['s'], $current_url );
        }

        $page_links = array();

        $disable_first = $disable_last = '';
        if ( $current == 1 )
            $disable_first = ' disabled';
        if ( $current == $total_pages )
            $disable_last = ' disabled';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'first-page' . $disable_first, esc_attr__( 'Go to the first page' ), esc_url( remove_query_arg( 'paged', $current_url ) ), '&laquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'prev-page' . $disable_first, esc_attr__( 'Go to the previous page' ), esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ), '&lsaquo;'
        );

        if ( 'bottom' == $which )
            $html_current_page = $current;
        else
            $html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />", esc_attr__( 'Current page' ), esc_attr( 'paged' ), $current, strlen( $total_pages )
            );

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = '<span class="paging-input">' . sprintf( __( '%1$s of %2$s', 'cardgate' ), $html_current_page, $html_total_pages ) . '</span>';

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'next-page' . $disable_last, esc_attr__( 'Go to the next page' ), esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ), '&rsaquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>", 'last-page' . $disable_last, esc_attr__( 'Go to the last page' ), esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ), '&raquo;'
        );

        $output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages )
            $page_class = $total_pages < 2 ? ' one-page' : '';
        else
            $page_class = ' no-pages';

        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    /**
     * Print column headers, accounting for hidden and sortable columns.
     *
     * @since 3.1.0
     * @access protected
     *
     * @param bool $with_id Whether to set the id attribute or not
     */
    function print_column_headers( $with_id = true ) {
        $screen = get_current_screen();

        list( $columns, $hidden, $sortable ) = $this->get_column_info();

        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $current_url = remove_query_arg( 'paged', $current_url );
        $current_url = remove_query_arg( 's', $current_url );
        if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) {
            $current_url = add_query_arg( 's', $_REQUEST['s'], $current_url );
        }


        if ( isset( $_GET['orderby'] ) )
            $current_orderby = $_GET['orderby'];
        else
            $current_orderby = '';

        if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
            $current_order = 'desc';
        else
            $current_order = 'asc';

        foreach ( $columns as $column_key => $column_display_name ) {
            $class = array( 'manage-column', "column-$column_key" );

            $style = '';
            if ( in_array( $column_key, $hidden ) )
                $style = 'display:none;';

            $style = ' style="' . $style . '"';

            if ( 'cb' == $column_key )
                $class[] = 'check-column';
            elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                $class[] = 'num';

            if ( isset( $sortable[$column_key] ) ) {
                list( $orderby, $desc_first ) = $sortable[$column_key];

                if ( $current_orderby == $orderby ) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $id = $with_id ? "id='$column_key'" : '';

            if ( !empty( $class ) )
                $class = "class='" . join( ' ', $class ) . "'";

            echo "<th scope='col' $id $class $style>$column_display_name</th>";
        }
    }

    private function swap_order_numbers( $items ) {
        global $wpdb;
        
        // swap order_id with sequetial order_id if it exists
        $tableName = $wpdb->prefix . 'postmeta';
        $qry = $wpdb->prepare( "SELECT post_id, meta_value FROM $tableName WHERE  meta_key='%s' ", '_order_number');
        $seq_order_ids = $wpdb->get_results( $qry, ARRAY_A );
        if ( count( $seq_order_ids ) > 0 ) {
            $seq = array();
            foreach ( $seq_order_ids as $k => $v ) {
                $seq[$v['post_id']] = $v['meta_value'];
            }
            $new_items = array();
            foreach ( $items as $k => $v ) {
                if ( isset( $seq[$v['order_id']] ) ) {
                    $v['order_id'] = $seq[$v['order_id']];
                }
                $new_items[] = $v;
            }
            return $new_items;
        }
        return $items;
    }

}
