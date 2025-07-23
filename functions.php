<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('pre')) {
    function pre($var, $exit = false)
    {
        echo "<pre>".print_r($var, true)."</pre>\n";
        if(!empty($exit)) exit();
    }
}

function ovesio_polylang_code_conversion($code) {
    //Ovesio => Polylang
    $langs_match = [
        'en' => 'gb',
        'el' => 'gr',
        'cs' => 'cz',
        'da' => 'dk',
        'pt-br' => 'pt',
    ];

    if(in_array($code, $langs_match)) {
        return $code;
    }

    return $code;
}

function ovesio_polylang_to_ovesio_code_conversion($code) {
    $translation_to = (array) ovesio_get_option('ovesio_options', 'translation_to');

    if(in_array($code, $translation_to)) {
        return $code;
    }

    if($code == 'pt' && in_array('pt-br', $translation_to)) {
        $code = 'pt-br';
    }

    return $code;
}

// Sanitization functions
function ovesio_sanitize_api_options($input)
{
    $input['api_key'] = sanitize_text_field($input['api_key'] ?? '');
    $input['api_url'] = sanitize_text_field($input['api_url'] ?? '');
    $input['security_hash'] = sanitize_text_field($input['security_hash']);

    return $input;
}

function ovesio_sanitize_options($input)
{
    $input['translation_to'] = $input['translation_to'] ?? [];
    $translation_default_language = sanitize_text_field($input['translation_default_language']);
    $input['translation_workflow'] = sanitize_text_field($input['translation_workflow']);
    $input['post_status'] = sanitize_text_field($input['post_status']);

    //Remove default language
    if($translation_default_language == 'system'){
        $system_default_language = ovesio_polylang_to_ovesio_code_conversion(pll_default_language());

        $lang_id = array_search($system_default_language, (array)$input['translation_to']);
        if(is_numeric($lang_id)) {
            unset($input['translation_to'][$lang_id]);
        }
    }

    $input['translation_default_language'] = $translation_default_language;

    return $input;
}

function ovesio_categories_relations($id, $target_lang, $post_type = 'post') {
    $catLang = [];
    if ($post_type != 'post' && $post_type != 'page') {
        // for custom post types
        $taxonomies_obj = get_object_taxonomies($post_type);
        foreach ($taxonomies_obj as $tax) {
            $term_all = wp_get_post_terms($id, $tax, [
                'fields' => 'ids',
            ]);
            foreach ($term_all as $term) {
                $translations = pll_get_term_translations($term);
                if (!empty($translations)) {
                    $catLang[] = $translations;
                }
            }
        }
    } else {
        foreach (wp_get_post_categories($id) as $cat) {
            $catLang[] = pll_get_term_translations($cat);
        }
    }

    return array_column($catLang, $target_lang);
}

function ovesio_parent_category_relations($id, $target_lang) {
    $parent = get_term($id)->parent;
    if ($parent) {
        $parentLang = pll_get_term_translations($parent);
        if (empty($parentLang[$target_lang])) {
            return 0;
        } else {
            return $parentLang[$target_lang];
        }
    } else {
        return 0;
    }
}

function ovesio_tags_relations($id, $target_lang) {
    $catLang = [];
    foreach (array_column(wp_get_post_tags($id), 'term_id') as $cat) {
        $catLang[] = pll_get_term_translations($cat);
    }

    return array_column($catLang, $target_lang);
}

// Get a setting from the plugin options
function ovesio_get_option($optionName, $key = null, $default = '') {
    $options = get_option($optionName, array());

    if($key) {
        return (isset($options[$key]) ? $options[$key] : $default);
    }

    return $options;
}

function ovesio_set_product_type($productId, $newProductId) {
    $productType = wc_get_product($productId)->get_type();
    wp_set_object_terms($newProductId, $productType, 'product_type');

    // For variable products, copy variations
    if ($productType === 'variable') {
        $source_product = wc_get_product($productId);
        $target_product = wc_get_product($newProductId);
        $target_product->set_attributes($source_product->get_attributes());
        $target_product->save();
    }
}

function ovesio_call_translation_ai($callback, $source, $target, $type, $id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ovesio_list';

    $ref = $type . '/' . $id;

    $url = ovesio_get_option('ovesio_api_settings', 'api_url', '');
    $key = ovesio_get_option('ovesio_api_settings', 'api_key');
    $security_hash = ovesio_get_option('ovesio_api_settings', 'security_hash');

    $workflow = ovesio_get_option('ovesio_options', 'translation_workflow');

    $translation_default_language = ovesio_get_option('ovesio_options', 'translation_default_language', '');
    if($translation_default_language == 'auto') {
        $source = 'auto';
    }

    if($target == 'all')
    {
        $system_languages = PLL()->model->get_languages_list();
        $system_languages = array_column($system_languages, 'slug');
    } else {
        $targets = explode(',', $target);
    }

    $to_langs = [];
    foreach($targets as $to) {
        $to_langs[] = ovesio_polylang_to_ovesio_code_conversion($to);
    }

    $response = [];
    try {
        $ovesio = new Ovesio\OvesioAI($key, $url);
        $request = $ovesio->translate()
            ->from($source)
            ->to($to_langs)
            ->useExistingTranslation(true)
            ->callbackUrl(home_url('/index.php?ovesio_callback=1&security_hash=' . $security_hash))
            ->data($callback, $ref)
            ->filterByValue();

        if(!empty($workflow)){
            $request = $request->workflow($workflow);
        }
        $request = $request->request();

        if(!empty($request->success)) {
            list($account , $token) = explode(':', $key);

            $translate_id = $request->data[0]->id;
            $link = str_replace(['api', 'v1/'], ['app', 'account/' . $account], $url) . '/app/translate_requests/' . urlencode($translate_id);

            foreach($to_langs as $lang)
            {
                $wpdb->insert($table_name, [
                    'resource' => $type,
                    'resource_id' => $id,
                    'lang' => $lang,
                    'translate_id' => $translate_id,
                    'translate_hash' => md5(json_encode($callback)),
                    'translate_date' => date('Y-m-d H:i:s'),
                    'translate_status' => 0,
                    'link' => $link
                ]);
            }

            $response['success'] = 'Translation sent successful, id:' . $translate_id;
        } else {
            $response['errors'] = 'Translation failed sending: ' . implode(',', (array) $request->errors);
        }
    } catch (Exception $e) {
        $response['errors'] = 'Translation failed: ' . $e->getMessage();
    }

    return $response;
}