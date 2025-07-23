<?php
if (!defined('ABSPATH')) exit;

function ovesio_api_page()
{
    $api_key = ovesio_get_option('ovesio_api_settings', 'api_key', '');
    $api_url = ovesio_get_option('ovesio_api_settings', 'api_url', '');
    $api_url = $api_url ? $api_url : 'https://api.ovesio.com/v1/';

    $security_hash = ovesio_get_option('ovesio_api_settings', 'security_hash', '');
    $security_hash = $security_hash ? $security_hash : uniqid();
?>
    <form method="post" action="options.php" class="ovesio-settings-form">
        <?php settings_fields('ovesio_api'); ?>
        <?php do_settings_sections('ovesio_api'); ?>
        <input type="hidden" name="ovesio_api_settings[security_hash]" value="<?php echo esc_attr($security_hash); ?>">
        <table class="form-table">
            <tr class="section">
                <th scope="row">
                    <label for="ovesio_api_settings_api_url"><?php esc_html_e('API Url', 'ovesio'); ?></label>
                </th>
                <td>
                    <input type="text" id="ovesio_api_settings_api_url" name="ovesio_api_settings[api_url]" value="<?php echo esc_attr($api_url); ?>" class="required regular-text">
                    <p class="description"><?php esc_html_e('Your Ovesio API Url', 'ovesio'); ?></p>
                </td>
            </tr>
            <tr class="section">
                <th scope="row">
                    <label for="ovesio_api_settings_api_key"><?php esc_html_e('API Key', 'ovesio'); ?></label>
                </th>
                <td>
                    <input type="password" id="ovesio_api_settings_api_key" name="ovesio_api_settings[api_key]" value="<?php echo esc_attr($api_key); ?>" class="required regular-text">
                    <span style="margin: 5px;">
                        <a style="text-decoration: none;" target="_blank" href="https://app.ovesio.com/"><?php esc_html_e('Get API Key', 'ovesio'); ?></a>
                    </span>
                    <p class="description"><?php esc_html_e('Your Ovesio API Key is under Ovesio App / Settings / API Token', 'ovesio'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
<?php
}
