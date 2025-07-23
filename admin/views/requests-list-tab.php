<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

function ovesio_requests_list_page() {
    if ( ! class_exists('WP_List_Table') ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Ovesio_Requests_Table extends WP_List_Table {

        public function __construct() {
            parent::__construct([
                'singular' => 'request',
                'plural'   => 'requests',
                'ajax'     => false,
            ]);
        }

        public function get_columns() {
            return [
                'id'               => 'ID',
                'resource'         => 'Resource',
                'lang'             => 'Language',
                'content'          => 'Content',
                'translate_status' => 'Status',
                'created_at'       => 'Created',
                'link'             => 'Link',
            ];
        }

        public function get_sortable_columns() {
            return []; // add if needed
        }

        public function get_bulk_actions() {
            return []; // none
        }

        public function prepare_items() {
            global $wpdb;

            $table      = $wpdb->prefix . 'ovesio_list';
            $per_page   = 20;
            $current    = $this->get_pagenum();
            $offset     = ($current - 1) * $per_page;

            /* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
            $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

            $where  = '';
            $args   = [];

            if ($search !== '') {
                $like  = '%' . $wpdb->esc_like($search) . '%';
                $where = "WHERE resource LIKE %s OR lang LIKE %s OR CAST(resource_id AS CHAR) LIKE %s";
                $args  = [ $like, $like, $like ];
            }

            // Count
            $count_sql = "SELECT COUNT(*) FROM $table $where";

            if ( $args ) {
                /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared */
                $query = $wpdb->prepare( $count_sql, ...$args );
            } else {
                $query = $count_sql;
            }


            /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */
            $total     = $args ? $wpdb->get_var($query) : $wpdb->get_var($count_sql);

            // Data
            $data_sql = "SELECT id, resource, resource_id, lang, content_id, translate_status, created_at, link
                        FROM $table $where
                        ORDER BY created_at DESC
                        LIMIT %d OFFSET %d";
            $args[]   = $per_page;
            $args[]   = $offset;

            /* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching */
            $this->items = $wpdb->get_results($wpdb->prepare($data_sql, $args), ARRAY_A);

            // Columns
            $columns  = $this->get_columns();
            $hidden   = [];
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = [ $columns, $hidden, $sortable ];

            $this->set_pagination_args([
                'total_items' => (int) $total,
                'per_page'    => $per_page,
                'total_pages' => ceil($total / $per_page),
            ]);
        }

        public function column_default($item, $column_name) {
            $links =  [
                'page' => '<a href="' . admin_url('post.php?post=%d&action=edit') . '" target="_blank">%s</a>',
                'post' => '<a href="' . admin_url('post.php?post=%d&action=edit') . '" target="_blank">%s</a>',
                'post_tag' => '<a href="' . admin_url('term.php?taxonomy=post_tag&tag_ID=%d') . '" target="_blank">%s</a>',
                'category' => '<a href="' . admin_url('term.php?taxonomy=category&tag_ID=%d') . '" target="_blank">%s</a>',
                'product' => '<a href="' . admin_url('post.php?post=%d&action=edit') . '" target="_blank">%s</a>',
                'product_cat' => '<a href="' . admin_url('edit-tags.php?taxonomy=product_cat&tag_ID=%d') . '" target="_blank">%s</a>',
                'product_tag' => '<a href="' . admin_url('edit-tags.php?taxonomy=product_tag&tag_ID=%d') . '" target="_blank">%s</a>',
            ];

            switch ($column_name) {
                case 'translate_status':
                    return $item['translate_status'] ? '<strong style="color: green;">Completed</strong>' : '<strong>Pending</strong>';
                case 'resource':
                    if(isset($links[$item['resource']])) {
                        return sprintf($links[$item['resource']], $item['resource_id'], $item['resource'] . ' (' . $item['resource_id'] . ')');
                    } else {
                        return '-';
                    }
                case 'content':
                    if(isset($links[$item['resource']]) && $item['content_id']) {
                        return sprintf($links[$item['resource']], $item['content_id'], $item['resource'] . ' (' . $item['content_id'] . ')');
                    } else {
                        return '-';
                    }
                case 'link':
                    return $item['link'] ? '<a href="' . esc_url($item['link']) . '" target="_blank">View</a>' : '-';
                default:
                    return esc_html($item[$column_name] ?? '-');
            }
        }

        public function no_items() {
            echo 'No requests found.';
        }
    }

    $table = new Ovesio_Requests_Table();
    $table->prepare_items();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Requests</h1>';
    echo '<hr class="wp-header-end">';

    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="ovesio_requests" />';
    $table->search_box('Search Requests', 'ovesio-requests');
    $table->display();
    echo '</form>';

    echo '</div>';
}