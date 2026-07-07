<?php
/**
 * Bootstrap du système de rubriques (V4) — modèle simplifié.
 *
 * Une rubrique (type) contient des items (footers nommés). Chaque item porte sa
 * STRUCTURE (champs positionnés en lignes/colonnes) et son CONTENU. Tout est
 * ADDITIF : le front actuel n'est pas impacté tant que le pilote n'est pas
 * branché. Voir documentation/REFONTE_RUBRIQUES_CIBLE.md (§12).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

// Cœur : types de champ.
require_once __DIR__ . '/core/field-types/registry.php';
require_once __DIR__ . '/core/field-types/builtin.php';
require_once __DIR__ . '/core/field-types/platforms.php';
require_once __DIR__ . '/core/field-types/platform-cards.php';
require_once __DIR__ . '/core/field-types/badge.php';
require_once __DIR__ . '/core/field-types/button.php';
require_once __DIR__ . '/core/field-types/decorative.php';
require_once __DIR__ . '/core/field-types/media.php';
require_once __DIR__ . '/core/field-types/text.php';

// Cœur : schéma des champs (colonnes, normalisation), types, stockage.
require_once __DIR__ . '/core/schema/layout.php';
require_once __DIR__ . '/core/schema/fields.php';
require_once __DIR__ . '/core/schema/style.php';
require_once __DIR__ . '/core/registry.php';
require_once __DIR__ . '/core/storage.php';

// Moteur de rendu (item par lignes/colonnes).
require_once __DIR__ . '/renderer/platform-players.php';
require_once __DIR__ . '/renderer/fields.php';
require_once __DIR__ . '/renderer/item.php';
require_once __DIR__ . '/renderer/engine.php';

// Types de rubrique déclarés en code (1 dossier par type sous types/).
foreach (glob(__DIR__ . '/types/*/type.php') ?: [] as $em_wp_rubrique_type_file) {
    require_once $em_wp_rubrique_type_file;
}
unset($em_wp_rubrique_type_file);

// Admin : assets, builder (une étape : structure + contenu), page V4.
if (is_admin()) {
    require_once __DIR__ . '/admin/assets.php';
    require_once __DIR__ . '/admin/builder/builder-preview-script.php';
    require_once __DIR__ . '/admin/builder/builder-save-handler.php';
    require_once __DIR__ . '/../admin/shared/components/scotchs-control/scotchs-control.php';
    require_once __DIR__ . '/admin/builder/builder-chip-media-render.php';
    require_once __DIR__ . '/admin/builder/builder-chip-render.php';
    require_once __DIR__ . '/admin/builder/builder-chip-value-render.php';
    require_once __DIR__ . '/admin/builder/builder-appearance-render.php';
    require_once __DIR__ . '/admin/builder/builder-item-render.php';
    require_once __DIR__ . '/admin/items/save.php';
    require_once __DIR__ . '/admin/items/list.php';
    require_once __DIR__ . '/admin/items/list-scripts.php';
    require_once __DIR__ . '/admin/items/create-form.php';
    require_once __DIR__ . '/admin/pages/overview-reorder.php';
    require_once __DIR__ . '/admin/pages/overview-rename.php';
    require_once __DIR__ . '/admin/pages/overview.php';
}
