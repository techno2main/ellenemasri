<?php
/**
 * Visual Links Builder - Migration EPK → VLB (ONE-TIME)
 *
 * Ce script migre les données du système legacy "EPK" vers le nouveau
 * système "Visual Links Builder".
 *
 * **IMPORTANT** :
 * - Ce script doit être exécuté UNE SEULE FOIS
 * - Il migre les options WordPress sans perte de données
 * - Il conserve un fallback de lecture temporaire (1 mois)
 *
 * @package Mayami
 * @since 2026-06-02
 * @see CADRE_PROJET_REFONTE_VLB.md
 */

/**
 * Migrer les options EPK vers Visual Links Builder.
 *
 * Copie les données des anciennes clés d'options vers les nouvelles,
 * sans supprimer les anciennes (fallback temporaire).
 *
 * @return array Rapport de migration [success => bool, migrated => array, errors => array]
 */
function mayami_migrate_epk_to_visual_links_options() {
    // Mapping ancien → nouveau
    $options_mapping = [
        'epk_draft_payload' => 'visual_links_draft_payload',
        'epk_published_payload' => 'visual_links_published_payload',
        'epk_validation_ready' => 'visual_links_validation_ready',
        'epk_draft_image_source' => 'visual_links_draft_image_source',
    ];

    $migrated = [];
    $errors = [];

    // Charger les options existantes
    $landing_options = get_option('mayami_landing', []);

    foreach ($options_mapping as $old_key => $new_key) {
        // Vérifier si ancienne clé existe
        if (!isset($landing_options[$old_key])) {
            continue;
        }

        $old_value = $landing_options[$old_key];

        // Vérifier si nouvelle clé existe déjà
        if (isset($landing_options[$new_key]) && !empty($landing_options[$new_key])) {
            // Nouvelle clé existe déjà, on ne migre pas
            $errors[] = sprintf(
                'Option "%s" existe déjà, migration ignorée pour "%s"',
                $new_key,
                $old_key
            );
            continue;
        }

        // Copier la valeur vers nouvelle clé
        $landing_options[$new_key] = $old_value;
        $migrated[$old_key] = $new_key;

        error_log(sprintf(
            '[VLB Migration] %s → %s (taille: %d bytes)',
            $old_key,
            $new_key,
            strlen(is_string($old_value) ? $old_value : json_encode($old_value))
        ));
    }

    // Sauvegarder les options mises à jour
    if (count($migrated) > 0) {
        update_option('mayami_landing', $landing_options);
        error_log(sprintf('[VLB Migration] Migration réussie : %d options migrées', count($migrated)));
    } else {
        error_log('[VLB Migration] Aucune option à migrer');
    }

    return [
        'success' => true,
        'migrated' => $migrated,
        'errors' => $errors,
    ];
}

/**
 * Afficher le rapport de migration dans l'admin WordPress.
 *
 * @param array $report Rapport retourné par mayami_migrate_epk_to_visual_links_options()
 */
function mayami_display_migration_report($report) {
    ?>
    <div class="notice notice-<?php echo count($report['errors']) > 0 ? 'warning' : 'success'; ?>">
        <h3>Migration EPK → Visual Links Builder</h3>
        
        <?php if (count($report['migrated']) > 0): ?>
            <p><strong>✅ Options migrées avec succès :</strong></p>
            <ul>
                <?php foreach ($report['migrated'] as $old => $new): ?>
                    <li><code><?php echo esc_html($old); ?></code> → <code><?php echo esc_html($new); ?></code></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucune option à migrer (déjà migrées ou absentes).</p>
        <?php endif; ?>
        
        <?php if (count($report['errors']) > 0): ?>
            <p><strong>⚠️ Avertissements :</strong></p>
            <ul>
                <?php foreach ($report['errors'] as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <p><em>Note : Les anciennes options EPK sont conservées temporairement pour fallback (1 mois).</em></p>
    </div>
    <?php
}

/**
 * Page admin WordPress pour exécuter la migration manuellement.
 *
 * À ajouter temporairement dans functions.php pour accès admin.
 */
function mayami_render_migration_admin_page() {
    // Vérifier permissions
    if (!current_user_can('manage_options')) {
        wp_die('Accès non autorisé');
    }

    // Traiter la migration si formulaire soumis
    $report = null;
    if (isset($_POST['mayami_run_migration']) && check_admin_referer('mayami_vlb_migration')) {
        $report = mayami_migrate_epk_to_visual_links_options();
    }

    ?>
    <div class="wrap">
        <h1>Migration Visual Links Builder</h1>
        <p>Cette page permet de migrer les données EPK vers le nouveau système Visual Links Builder.</p>
        
        <?php if ($report !== null): ?>
            <?php mayami_display_migration_report($report); ?>
        <?php endif; ?>
        
        <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir exécuter la migration ? Cette opération copie les données EPK vers Visual Links.');">
            <?php wp_nonce_field('mayami_vlb_migration'); ?>
            <p>
                <button type="submit" name="mayami_run_migration" class="button button-primary">
                    Exécuter la migration
                </button>
            </p>
        </form>
        
        <hr>
        
        <h2>État actuel des options</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Clé d'option</th>
                    <th>Statut</th>
                    <th>Taille</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $landing_options = get_option('mayami_landing', []);
                $keys_to_check = [
                    'epk_draft_payload',
                    'visual_links_draft_payload',
                    'epk_published_payload',
                    'visual_links_published_payload',
                    'epk_validation_ready',
                    'visual_links_validation_ready',
                    'epk_draft_image_source',
                    'visual_links_draft_image_source',
                ];
                
                foreach ($keys_to_check as $key) {
                    $exists = isset($landing_options[$key]);
                    $value = $exists ? $landing_options[$key] : null;
                    $size = $exists ? strlen(is_string($value) ? $value : json_encode($value)) : 0;
                    ?>
                    <tr>
                        <td><code><?php echo esc_html($key); ?></code></td>
                        <td>
                            <?php if ($exists): ?>
                                <span style="color: green;">✓ Existe</span>
                            <?php else: ?>
                                <span style="color: #999;">✗ Absent</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $exists ? esc_html(number_format($size) . ' bytes') : '-'; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Hook pour ajouter la page admin de migration (temporaire).
 *
 * À appeler depuis functions.php :
 * add_action('admin_menu', 'mayami_add_migration_admin_menu');
 */
function mayami_add_migration_admin_menu() {
    add_management_page(
        'Migration VLB',
        'Migration VLB',
        'manage_options',
        'mayami-vlb-migration',
        'mayami_render_migration_admin_page'
    );
}

// Note : Ce fichier est chargé mais les hooks ne sont pas ajoutés automatiquement.
// Pour activer la page admin, ajouter dans functions.php :
// require_once get_template_directory() . '/inc/visual-links-migration.php';
// add_action('admin_menu', 'mayami_add_migration_admin_menu');
