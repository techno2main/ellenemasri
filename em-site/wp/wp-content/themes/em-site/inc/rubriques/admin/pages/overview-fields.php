<?php
/**
 * Styles des champs de l'overview Rubriques (wrapper inline).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

$css_path = get_template_directory() . '/assets/admin/css/rubriques-overview/fields-controls.css';
if (!is_readable($css_path)) {
    return;
}

$css = (string) file_get_contents($css_path);
if ($css === '') {
    return;
}
?>
<style>
<?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</style>
