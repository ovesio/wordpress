<?php
if (!defined('ABSPATH')) exit;

// Settings page Header
function ovesio_settings_tabs()
{
    if (isset($_GET['tab'], $_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'switch_tab')) {
        $active_tab = sanitize_key(wp_unslash($_GET['tab']));
    } else {
        $active_tab = 'api'; // Default tab
    }

?>
    <div class="wrap">
        <h1><?php esc_html_e('Ovesio', 'ovesio'); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=ovesio&tab=api&_wpnonce=<?php echo esc_attr(wp_create_nonce('switch_tab')); ?>" class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('API Settings', 'ovesio'); ?>
            </a>
            <a href="?page=ovesio&tab=translation&_wpnonce=<?php echo esc_attr(wp_create_nonce('switch_tab')); ?>" class="nav-tab <?php echo $active_tab === 'translation' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Translation Settings', 'ovesio'); ?>
            </a>
        </h2>

        <?php
        if ($active_tab === 'api') {
            ovesio_api_page();
        } elseif ($active_tab === 'translation') {
            ovesio_translation_settings_page();
        }
        ?>
    </div>
<?php
}
