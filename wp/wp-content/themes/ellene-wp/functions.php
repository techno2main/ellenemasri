<?php

/**
 * ellene-wp Theme Functions
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/theme/setup.php';
require_once get_template_directory() . '/inc/cmb2/options-config.php';
require_once get_template_directory() . '/inc/theme/assets.php';
require_once get_template_directory() . '/inc/theme/statistics.php';
require_once get_template_directory() . '/inc/theme/seo.php';
require_once get_template_directory() . '/inc/vlb/bootstrap.php';
require_once get_template_directory() . '/inc/modules/registry.php';
require_once get_template_directory() . '/inc/modules/resolver.php';
require_once get_template_directory() . '/inc/modules/renderer.php';
require_once get_template_directory() . '/inc/modules/shared-sections.php';