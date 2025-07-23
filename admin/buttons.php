<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('post_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('page_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('post_tag_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('category_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('product_cat_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('product_tag_row_actions', 'ovesio_add_action_buttons', 10, 2);
add_filter('elementor_library_row_actions', 'ovesio_add_action_buttons', 10, 2);

function ovesio_add_action_buttons($actions, $post)
{
    global $wpdb;

    // Check if Polylang functions exist
    if (!function_exists('pll_the_languages') || !function_exists('pll_get_post_language')) {
        return $actions;
    }

    // Show translation actions if user can edit posts
    if (!current_user_can('edit_posts')) {
        return $actions;
    }

    $table_name = $wpdb->prefix . 'ovesio_list';

    if (isset($post->ID)) {
        // Work on posts
        $sourceLang = pll_get_post_language($post->ID);

        $type = $post->post_type;
        $id = $post->ID;
    } elseif (isset($post->term_id)) {
        // Work on Taxonomies
        $sourceLang = pll_get_term_language($post->term_id);

        $type = $post->taxonomy;
        $id = $post->term_id;
    } else {
        return $actions;
    }

    $languages = pll_the_languages([
        'raw'           => true,
        'hide_if_empty' => false,
        'show_flags'    => true,
    ]);
    $lang_slug = [];
    $lang_flag = [];
    $lang_name = [];
    $pending_lang = [];

    // Get default language slug
    foreach ($languages as $lang) {
        $translation_exists = $wpdb->get_row(
            $wpdb->prepare("SELECT translate_status FROM {$table_name} WHERE resource = %d AND resource_id = %d AND lang = %s ORDER BY id DESC LIMIT 1", $type, $id, ovesio_polylang_code_conversion($lang['slug']))
        );

        if(in_array($type, ['post', 'page', 'product'])){
            $post_lang = pll_get_post($id, $lang['slug']);
        } else {
            $post_lang = pll_get_term($id, $lang['slug']);
        }

        $pending_translations = ($translation_exists && $translation_exists->translate_status != 1);

        if(!$post_lang && $pending_translations){
            $pending_lang[] = $lang['flag'];
        }

        if (!$post_lang && !$pending_translations) {
            $lang_flag[] = $lang['flag'];
            $lang_slug[] = $lang['slug'];
            $lang_name[] = $lang['name'];
        }
    }

    $item = "type={$type}&id={$id}";

    $entries = array_map(
        function ($slug, $flag, $name) use ($item, $sourceLang) {
            return '<a class="ovesio-translate-ajax-request" title="' . $name . '" href="' . admin_url("admin-ajax.php?action=ovesio_translate_content&" . $item . "&source=" . $sourceLang . "&slug=" . $slug . "&_wpnonce=" . wp_create_nonce('ovesio-nonce')) . '" style="margin: 0 4px;">' . $flag . '<a>';
        },
        $lang_slug,
        $lang_flag,
        $lang_name
    );

    if($pending_lang) {
        $pending_lang = implode(', ', $pending_lang);
        $actions['pending_translations'] = '<span class="new-translation">' . esc_html__('Pending translations', 'ovesio') . ': ' . $pending_lang . '</span>';
    }

    if($entries) {
        $actions['translate_all'] = '<span class="translate-all"><a class="ovesio-translate-ajax-request" href="' . admin_url("admin-ajax.php?action=ovesio_translate_content&" . $item . "&source=" . $sourceLang . "&slug=" . implode(',', $lang_slug) . "&_wpnonce=" . wp_create_nonce('ovesio-nonce')) . '">' . esc_html__('Translate All', 'ovesio') . '</a></span>';

        $finalLang = implode('', $entries);
        $actions['new_translation'] = '<span class="new-translation"><a>' . esc_html__('Translate', 'ovesio') . ':</a> ' . $finalLang . '</span>';
    }

    return $actions;
}

// AJAX translation requests
add_action('wp_ajax_ovesio_translate_content', 'ovesio_translate_content_ajax_handler');
function ovesio_translate_content_ajax_handler() {
    if (!empty($_REQUEST['translate'])) {

        // Check if the request is valid
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'ovesio-nonce')) {
            wp_send_json_error('Invalid nonce', 403);
        }

        // Check if the user has permission to edit posts
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied', 403);
        }

        // Validate and sanitize all input variables
        if (!isset($_REQUEST['type'], $_REQUEST['slug'], $_REQUEST['source'])) {
            wp_send_json_error('Missing required parameters', 400);
        }

        $type = sanitize_text_field(wp_unslash($_REQUEST['type']));
        $id = (int) $_REQUEST['id'];
        $source = sanitize_text_field(wp_unslash($_REQUEST['source']));
        $target_lang = sanitize_text_field(wp_unslash($_REQUEST['slug']));

        $request = [];
        $post = null;

        // Check post type
        switch($type) {
            case 'post':
            case 'page':
            case 'product':
            case 'elementor_library':
                $post = get_post($id);

                if (!$post) {
                    wp_send_json_error("Invalid {$type} ID", 400);
                }

                // Check for existing translation
                if (function_exists('pll_get_post_translations')) {
                    $existing_translations = pll_get_post_translations($id);
                    if (!empty($existing_translations[$target_lang])) {
                        wp_send_json_error('Translation for this language already exists', 409);
                    }
                }

                $request = [
                    [
                        'key' => 'post_title',
                        'value' => $post->post_title,
                    ],
                    [
                        'key' => 'post_content',
                        'value' => $post->post_content,
                    ],
                    [
                        'key' => 'post_excerpt',
                        'value' => $post->post_excerpt,
                    ],
                ];

                // Elementor compatibility
                // if ( did_action('elementor/loaded') ) {
                //     $doc = \Elementor\Plugin::$instance->documents->get( $id );
                //     if ( $doc && $doc->is_built_with_elementor() ) {
                //         $raw = get_post_meta( $id, '_elementor_data', true );
                //         if ( $raw ) {
                //             $request[] = [
                //                 'key'   => '_elementor_data',
                //                 'value' => $raw,
                //             ];
                //         }
                //         $settings = get_post_meta( $id, '_elementor_page_settings', true );
                //         if ( ! empty( $settings ) ) {
                //             $request[] = [
                //                 'key'   => '_elementor_page_settings',
                //                 'value' => wp_json_encode( $settings ),
                //             ];
                //         }
                //     }
                // }

                break;
            case 'post_tag':
            case 'category':
            case 'product_cat':
            case 'product_tag':
                $post = get_term($id);

                if (!$post) {
                    wp_send_json_error("Invalid {$type} ID", 400);
                }

                // Check for existing translation
                if (function_exists('pll_get_term_translations')) {
                    $existing_translations = pll_get_term_translations($id);
                    if (!empty($existing_translations[$target_lang])) {
                        wp_send_json_error('Translation for this language already exists', 409);
                    }
                }

                $request = [
                    [
                        'key' => 'name',
                        'value' => $post->name,
                    ],
                    [
                        'key' => 'description',
                        'value' => $post->description,
                    ]
                ];

                break;
        }

        if(empty($request)) {
            wp_send_json_error('Invalid request', 400);
        }

        $response = ovesio_call_translation_ai($request, $source, $target_lang, $type, $id);

        if (!empty($response['errors'])) {
            wp_send_json_error($response['errors'], 500);
        } else {
            wp_send_json_success($response);
        }
    } else {
        // Redirect to referrer page
        $referrer = wp_get_referer();
        if ($referrer) {
            wp_redirect($referrer);
            exit;
        }
    }
}