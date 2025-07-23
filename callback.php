<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register public callback url
add_action('init', 'ovesio_add_public_callback');
add_filter('query_vars', 'ovesio_register_callback_query_var');
add_action('template_redirect', 'ovesio_handle_public_endpoint');

function ovesio_add_public_callback() {
    add_rewrite_rule('^ovesio-callback$', 'index.php?ovesio_callback=1', 'top');
}

function ovesio_register_callback_query_var($vars) {
    $vars[] = 'ovesio_callback';
    $vars[] = 'security_hash';
    return $vars;
}

function ovesio_handle_public_endpoint() {
    // Check security
    $security_hash = ovesio_get_option('ovesio_api_settings', 'security_hash');
    if (get_query_var('security_hash') != $security_hash) {
        return;
    }

    if (get_query_var('ovesio_callback') == '1') {
        header('Content-Type: application/json');

        $callbackHandler = new \Ovesio\Callback\CallbackHandler();
        if($callback = $callbackHandler->handle()) {
            list($type, $id) = explode('/', $callback->ref);

            try {
                if(in_array($type, ['page', 'post', 'product', 'post_tag', 'category'])) {
                    ovesio_wp_post_callback($type, $id, $callback);
                } else {
                    throw new Exception('Unsupported resource type: '. $type);
                }
            } catch (Exception $e) {
                $callbackHandler->fail($e->getMessage());
            }

            $callbackHandler->success();
        } else {
            $callbackHandler->fail();
        }
        exit();
    }
}

// Flush rules la activare
register_activation_hook(__FILE__, function () {
    ovesio_add_public_callback();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

function ovesio_wp_post_callback($type, $id, $callback)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ovesio_list';
    $target_lang = $callback->to;

    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$table_name} WHERE resource = %s AND resource_id = %d AND lang = %s AND translate_id = %d AND translate_status = 0 AND content_id IS NULL", $type, $id, $target_lang, $callback->id)
    );

    if(empty($row->id)) {
        throw new Exception('Translation not found!');
    }

    $post_status = ovesio_get_option('ovesio_options', 'post_status', 'publish');

    if(in_array($type, ['post', 'page', 'product'])) {
        $post = (array) get_post($id);
        unset($post['ID']);

        $post['post_status'] = $post_status;
        foreach($callback->content as $content) {
            $post[$content->key] = $content->value;
        }

        $new_post_id = wp_insert_post($post);

        // Check if the post was created successfully
        if (is_wp_error($new_post_id)) {
            wp_send_json_error('Post creation failed: ' . $new_post_id->get_error_message(), 500);
        }

        // Copy custom fields
        $meta = get_post_meta($id);
        if (!empty($meta)) {
            foreach ($meta as $key => $values) {
                // Skip Polylang and WordPress core fields if needed
                if (in_array($key, [
                    '_edit_lock',
                    '_edit_last',
                    '_thumbnail_id',
                    '_wp_old_slug',
                    '_icl_lang_duplicate_of',
                    '_polylang_translation',
                    '_pll_language',
                    '_pll_trid'
                ])) {
                    continue;
                }
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
        }

        // Copy featured image
        $thumbnail_id = get_post_thumbnail_id($id);
        if ($thumbnail_id && get_post($thumbnail_id)) {
            // Make sure the image exists before setting
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }

        if (function_exists('pll_set_post_language')) {
            // Set the language for the new post
            pll_set_post_language($new_post_id, $target_lang);

            // Get existing translations
            $translations = pll_get_post_translations($id);

            // Add the new translation
            if (!isset($translations[$target_lang])) {
                $translations[$target_lang] = $new_post_id;
                // Save the updated translations
                pll_save_post_translations($translations);
            }

            // Categories relations
            $cat_replations = ovesio_categories_relations($id, $target_lang, $type);
            if (!empty($cat_replations)) {
                if ($type != 'post' && $type != 'page') {
                    foreach ($cat_replations as $cat) {
                        $catType = get_term($cat)->taxonomy;
                        wp_set_object_terms(
                            $new_post_id,
                            [$cat],
                            $catType,
                            true
                        );
                    }
                } else {
                    wp_set_post_categories($new_post_id, ovesio_categories_relations($id, $target_lang, $type));
                }
            }

            // Tags relations
            if (!empty(ovesio_tags_relations($id, $target_lang))) {
                wp_set_post_tags($new_post_id, ovesio_tags_relations($id, $target_lang));
            }

            // Set product Type and variations for WooCommerce products
            if ($type === 'product') {
                ovesio_set_product_type($id, $new_post_id);
            }
        }
    } elseif(in_array($type, ['post_tag', 'category'])) {
        $term = (array) get_term($id, $type);
        if (is_wp_error($term) || !$term) {
            wp_send_json_error('Term not found.', 404);
        }

        foreach($callback->content as $content) {
            $term[$content->key] = $content->value;
        }

        $name = $term['name'];
        unset($term['name']);

        // Create the translated term
        $parent_cat = ovesio_parent_category_relations($id, $target_lang);

        // Check if the Term exists in the target language To append new lang sulg
        $term_exists = term_exists($name, $type, $parent_cat);
        // If the term already exists, append the language slug to the title
        if(!empty($term_exists)) {
            $existing_term = get_term($term_exists['term_id'], $type);
            $term['slug'] = $existing_term->slug;
        } else {
            $term['slug'] = sanitize_title($name);
        }

        $new_term = wp_insert_term($name, $type, $term);

        if (is_wp_error($new_term)) {
            wp_send_json_error('Term creation failed: ' . $new_term->get_error_message(), 500);
        }

        if (!isset($new_term['term_id'])) {
            wp_send_json_error('Term ID not returned', 500);
        }

        $new_post_id = $new_term['term_id'];

        // Set language
        if (function_exists('pll_set_term_language')) {
            pll_set_term_language($new_term['term_id'], $target_lang);
        }

        // Get existing translations
        if (function_exists('pll_get_term_translations') && function_exists('pll_save_term_translations')) {
            $translations = pll_get_term_translations($id);
            if (!isset($translations[$target_lang])) {
                // Add the new translation
                $translations[$target_lang] = $new_term['term_id'];
                // Save the updated translations
                pll_save_term_translations($translations);
            }
        }
    } else {
        wp_send_json_error('Request failed: unknown resource type ' . $type, 500);
    }

    if(!$new_post_id) {
        wp_send_json_error('Request failed: new post ID not returned', 500);
    }

    // Update table
    $wpdb->update(
        $table_name,
        [
            'translate_status' => 1,
            'content_id' => $new_post_id
        ],
        [
            'id' => $row->id,
        ],
        ['%d', '%d'],
        ['%d']
    );
}
