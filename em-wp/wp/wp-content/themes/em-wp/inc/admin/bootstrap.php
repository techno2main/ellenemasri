<?php
/**
 * Bootstrap de la couche admin (BO).
 *
 * Menus admin : accueil Dashboard (pages/dashboard/), sommaire Rubriques (pages/rubriques/),
 * catalogues, templates, puis modules em-wp (voir inc/admin/shared/menu/).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/shared/style-panel.php';
require_once __DIR__ . '/shared/catalog-rubrique-page.php';
require_once __DIR__ . '/shared/assets.php';
require_once __DIR__ . '/shared/color-modal.php';
require_once __DIR__ . '/shared/hub-cards.php';
require_once __DIR__ . '/shared/settings-api.php';
require_once __DIR__ . '/shared/register-module-saves.php';
require_once __DIR__ . '/shared/avatars.php';
require_once __DIR__ . '/shared/menu.php';
require_once __DIR__ . '/template/bootstrap.php';
require_once __DIR__ . '/shared/landing-preview.php';
require_once __DIR__ . '/shared/landing-structure-preview.php';
require_once __DIR__ . '/shared/variant-hub.php';
require_once __DIR__ . '/client-access.php';
require_once __DIR__ . '/admin-chrome.php';
require_once __DIR__ . '/themes-preview.php';
require_once __DIR__ . '/modules/top-bar/settings.php';
require_once __DIR__ . '/modules/header/settings.php';
require_once __DIR__ . '/modules/catalog/bootstrap.php';
require_once __DIR__ . '/modules/hero/settings.php';
require_once __DIR__ . '/modules/slider/slides.php';
require_once __DIR__ . '/modules/slider/settings.php';
require_once __DIR__ . '/modules/stream/settings.php';
require_once __DIR__ . '/modules/social/settings.php';
require_once __DIR__ . '/modules/video/settings.php';
require_once __DIR__ . '/modules/release/settings.php';
require_once __DIR__ . '/modules/cta/settings.php';
require_once __DIR__ . '/modules/footer/settings.php';
require_once __DIR__ . '/modules/custom-catalog-rubrique/settings.php';
require_once __DIR__ . '/pages/rubriques.php';
require_once __DIR__ . '/pages/dashboard.php';
require_once __DIR__ . '/shared/menu/layout.php';
require_once __DIR__ . '/shared/menu/accordion.php';
require_once __DIR__ . '/shared/onboarding.php';
require_once __DIR__ . '/modules/coming-soon/register.php';

/**
 * Slugs modules gérés côté admin.
 */
function em_wp_admin_module_slugs(): array
{
    return [
        'top-bar',
        'header',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'footer',
    ];
}
