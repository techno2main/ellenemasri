<?php

/**
 * Visual Links builder and publish workflow.
 *
 * @package ClientWp
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('cmb2_render_mayami_visual_links_builder', 'mayami_render_visual_links_builder_field', 10, 5);
add_action('admin_post_mayami_publish_visual_links_draft', 'mayami_handle_publish_visual_links_draft');
add_action('admin_post_mayami_unpublish_visual_links', 'mayami_handle_unpublish_visual_links');
add_action('admin_notices', 'mayami_render_visual_links_admin_notice');



function mayami_sanitize_visual_links_payload($value) {

    if (is_string($value)) {

        $value = wp_unslash($value);

    }



    $payload = mayami_decode_visual_links_payload($value);

    if (!mayami_has_visual_links_payload_data($payload)) {

        return '';

    }



    return wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

}



function mayami_get_saved_visual_links_payload($field_id) {

    return mayami_decode_visual_links_payload(mayami_get_landing_option($field_id, ''));

}



function mayami_is_visual_links_preview_request() {

    if (is_admin()) {

        return false;

    }



    if (!isset($_GET['mayami_preview']) || wp_unslash($_GET['mayami_preview']) !== 'visual_links') {

        return false;

    }



    if (!is_user_logged_in() || !current_user_can('manage_options')) {

        return false;

    }



    $nonce = isset($_GET['mayami_preview_nonce']) ? sanitize_text_field(wp_unslash($_GET['mayami_preview_nonce'])) : '';



    return $nonce !== '' && wp_verify_nonce($nonce, 'mayami_visual_links_preview');

}



function mayami_get_visual_links_preview_url() {

    return add_query_arg(

        array(

            'mayami_preview' => 'visual_links',

            'mayami_preview_nonce' => wp_create_nonce('mayami_visual_links_preview'),

        ),

        home_url('/')

    );

}



function mayami_get_visual_links_front_payload() {

    if (mayami_is_visual_links_preview_request()) {

        $draft_payload = mayami_get_saved_visual_links_payload('visual_links_draft_payload');

        if (mayami_has_visual_links_payload_data($draft_payload)) {

            return $draft_payload;

        }

    }



    return mayami_get_saved_visual_links_payload('visual_links_published_payload');

}



function mayami_get_visual_links_zone_href($zone) {

    if (!is_array($zone)) {

        return '';

    }



    $href_type = $zone['hrefType'] ?? 'url';

    $href_value = trim((string) ($zone['hrefValue'] ?? ''));



    if ($href_value === '') {

        return '';

    }



    if ($href_type === 'anchor') {

        return '#' . sanitize_title(ltrim($href_value, '#'));

    }



    return esc_url($href_value);

}



function mayami_format_visual_links_timestamp($value) {

    if (!is_string($value) || trim($value) === '') {

        return 'Non défini';

    }



    $timestamp = strtotime($value);

    if (!$timestamp) {

        return 'Non défini';

    }



    return wp_date('d/m/Y H:i', $timestamp);

}



function mayami_render_visual_links_builder_field($field, $escaped_value, $object_id, $object_type, $field_type_object) {

    $draft_payload = mayami_get_saved_visual_links_payload('visual_links_draft_payload');

    $published_payload = mayami_get_saved_visual_links_payload('visual_links_published_payload');

    $validation_ready = (bool) mayami_get_landing_option('visual_links_validation_ready', false);

    ?>

    <div

        class="mayami-vlb-builder"

        data-action-endpoint="<?php echo esc_url(admin_url('admin-post.php')); ?>"

        data-preview-url="<?php echo esc_url(mayami_get_visual_links_preview_url()); ?>"

        data-publish-nonce="<?php echo esc_attr(wp_create_nonce('mayami_publish_visual_links_draft')); ?>"

        data-unpublish-nonce="<?php echo esc_attr(wp_create_nonce('mayami_unpublish_visual_links')); ?>"

    >

        <div class="mayami-vlb-proto-shell">

            <div class="mayami-vlb-proto-header">

                <div class="mayami-vlb-proto-header-copy">

                    <h3>Créateur de liens sur image - Visual Links</h3>

                    <p>Dessinez des zones cliquables sur votre image puis prévisualisez et publiez la version validée sur la landing.</p>

                </div>

                <div class="mayami-vlb-builder__badges">

                    <span class="mayami-vlb-badge mayami-vlb-badge--draft">Brouillon: <?php echo esc_html(mayami_has_visual_links_payload_data($draft_payload) ? 'prêt' : 'vide'); ?></span>

                    <span class="mayami-vlb-badge mayami-vlb-badge--published">Front: <?php echo esc_html(mayami_has_visual_links_payload_data($published_payload) ? 'publié' : 'hors ligne'); ?></span>

                    <span class="mayami-vlb-badge mayami-vlb-badge--check">Validation: <?php echo esc_html($validation_ready ? 'ok' : 'requise'); ?></span>

                </div>

            </div>



            <div class="mayami-vlb-proto-main">

                <div class="mayami-vlb-canvas-area">

                    <div class="mayami-vlb-upload-panel">

                        <div class="mayami-vlb-native-media-host"></div>

                        <button type="button" class="mayami-vlb-btn mayami-vlb-btn-secondary mayami-vlb-clear-image">Retirer le visuel</button>

                    </div>



                    <div class="mayami-vlb-canvas-empty">Sélectionnez une image dans la médiathèque pour commencer.</div>



                    <div class="mayami-vlb-canvas-wrapper" hidden>

                        <img class="mayami-vlb-canvas-image" src="" alt="">

                        <div class="mayami-vlb-canvas-overlay"></div>

                    </div>



                    <div class="mayami-vlb-info-box">

                        <strong>Mode d'emploi :</strong>

                        1. Choisissez le visuel dans la médiathèque.<br>

                        2. Cliquez-glissez pour dessiner des zones rectangulaires.<br>

                        3. Ajoutez un lien ou une ancre pour chaque zone.<br>

                        4. Enregistrez, prévisualisez, validez, puis publiez.

                    </div>



                    <div class="mayami-vlb-form-grid">

                        <label>

                            <span>Kicker</span>

                            <input type="text" class="regular-text mayami-vlb-input" data-vlb-field="kicker" placeholder="VISUAL LINKS">

                        </label>

                        <label>

                            <span>Titre</span>

                            <input type="text" class="regular-text mayami-vlb-input" data-vlb-field="title" placeholder="Visual Links">

                        </label>

                        <label class="mayami-vlb-form-grid__full">

                            <span>Description</span>

                            <textarea rows="3" class="large-text mayami-vlb-input" data-vlb-field="description" placeholder="Présentez rapidement cette section."></textarea>

                        </label>

                        <label class="mayami-vlb-form-grid__full">

                            <span>Texte alternatif de l'image</span>

                            <input type="text" class="regular-text mayami-vlb-input" data-vlb-field="imageAlt" placeholder="Visuel cliquable de client-wp">

                        </label>

                    </div>

                </div>



                <aside class="mayami-vlb-sidebar">

                    <div class="mayami-vlb-stats">

                        <div class="mayami-vlb-stat-box">

                            <div class="mayami-vlb-stat-number mayami-vlb-zone-count">0</div>

                            <div class="mayami-vlb-stat-label">Zones</div>

                        </div>

                        <div class="mayami-vlb-stat-box">

                            <div class="mayami-vlb-stat-number mayami-vlb-linked-count">0</div>

                            <div class="mayami-vlb-stat-label">Liées</div>

                        </div>

                    </div>



                    <h2>Zones cliquables</h2>



                    <div class="mayami-vlb-zones-list"></div>



                    <div class="mayami-vlb-actions">

                        <button type="button" class="mayami-vlb-btn mayami-vlb-btn-secondary mayami-vlb-reset-zones">Tout effacer</button>

                        <a href="<?php echo esc_url(mayami_get_visual_links_preview_url()); ?>" target="_blank" rel="noreferrer" class="mayami-vlb-btn mayami-vlb-btn-secondary mayami-vlb-preview-link">Prévisualiser la landing</a>

                        <button type="button" class="mayami-vlb-btn mayami-vlb-btn-primary mayami-vlb-publish-button">Publier Visual Links sur le front</button>

                        <button type="button" class="mayami-vlb-btn mayami-vlb-btn-secondary mayami-vlb-unpublish-button">Retirer Visual Links du front</button>

                    </div>



                    <div class="mayami-vlb-workflow-box">

                        <p><strong>Dernier brouillon :</strong> <span class="mayami-vlb-draft-status"><?php echo esc_html(mayami_format_visual_links_timestamp($draft_payload['updatedAt'] ?? '')); ?></span></p>

                        <p><strong>Version front :</strong> <span class="mayami-vlb-published-status"><?php echo esc_html(mayami_format_visual_links_timestamp($published_payload['publishedAt'] ?? '')); ?></span></p>

                        <div class="mayami-vlb-validation-host"></div>

                        <p class="mayami-vlb-workflow-note">Prévisualisation et publication utilisent la dernière version enregistrée. Sauvegardez client-wp Landing avant ces actions.</p>

                    </div>

                </aside>

            </div>

        </div>

    </div>

    <?php

}



function mayami_handle_publish_visual_links_draft() {

    if (!current_user_can('manage_options')) {

        wp_die(esc_html__('You are not allowed to do this.', 'mayami'), 403);

    }



    check_admin_referer('mayami_publish_visual_links_draft');



    $options = get_option('mayami_landing_options', array());

    if (!is_array($options)) {

        $options = array();

    }



    $draft_payload = mayami_decode_visual_links_payload($options['visual_links_draft_payload'] ?? '');

    if (!mayami_has_visual_links_payload_data($draft_payload)) {

        mayami_redirect_visual_links_notice('draft-missing');

    }



    if (empty($options['visual_links_validation_ready'])) {

        mayami_redirect_visual_links_notice('validation-missing');

    }



    $draft_payload['publishedAt'] = wp_date(DATE_ATOM, current_time('timestamp'));

    $options['visual_links_published_payload'] = wp_json_encode($draft_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    update_option('mayami_landing_options', $options);



    mayami_redirect_visual_links_notice('published');

}



function mayami_handle_unpublish_visual_links() {

    if (!current_user_can('manage_options')) {

        wp_die(esc_html__('You are not allowed to do this.', 'mayami'), 403);

    }



    check_admin_referer('mayami_unpublish_visual_links');



    $options = get_option('mayami_landing_options', array());

    if (!is_array($options)) {

        $options = array();

    }



    $options['visual_links_published_payload'] = '';

    update_option('mayami_landing_options', $options);



    mayami_redirect_visual_links_notice('unpublished');

}



function mayami_redirect_visual_links_notice($notice) {

    $url = add_query_arg(

        array(

            'page' => 'mayami_landing_options',

            'mayami_visual_links_notice' => $notice,

        ),

        admin_url('admin.php')

    );



    wp_safe_redirect($url);

    exit;

}



function mayami_render_visual_links_admin_notice() {

    if (!is_admin()) {

        return;

    }



    if (!isset($_GET['page']) || wp_unslash($_GET['page']) !== 'mayami_landing_options') {

        return;

    }



    if (!isset($_GET['mayami_visual_links_notice'])) {

        return;

    }



    $notice = sanitize_key(wp_unslash($_GET['mayami_visual_links_notice']));

    $message = '';

    $class = 'notice-info';



    switch ($notice) {

        case 'published':

            $class = 'notice-success';

            $message = 'Le brouillon Visual Links a été publié sur le front.';

            break;

        case 'unpublished':

            $class = 'notice-warning';

            $message = 'Visual Links a été retiré du front.';

            break;

        case 'validation-missing':

            $class = 'notice-error';

            $message = 'Cochez d’abord la validation finale Visual Links puis enregistrez la page avant publication.';

            break;

        case 'draft-missing':

            $class = 'notice-error';

            $message = 'Aucun brouillon Visual Links valide à publier.';

            break;

    }



    if ($message === '') {

        return;

    }



    printf('<div class="notice %1$s is-dismissible"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

}

