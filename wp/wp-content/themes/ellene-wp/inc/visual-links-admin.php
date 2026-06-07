<?php

/**
 * Visual Links admin helpers and AJAX handlers.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_register_visual_links_html_menu() {
    $root_slug = 'mayami_visual_links_builder';

    add_menu_page(
        'Visual Links Builder',
        'Visual Links Builder',
        'manage_options',
        $root_slug,
        'mayami_render_visual_links_html_builder_page',
        'dashicons-format-image',
        31
    );

    add_submenu_page(
        $root_slug,
        'Nouveau visuel',
        'Nouveau visuel',
        'manage_options',
        'mayami_visual_links_builder_new',
        'mayami_render_visual_links_new_submenu_page'
    );

    add_submenu_page(
        $root_slug,
        'Liste des visuels',
        'Liste des visuels',
        'manage_options',
        'mayami_visual_links_drafts',
        'mayami_render_visual_links_drafts_page'
    );

    add_submenu_page(
        null,
        'Preview Visual Links',
        'Preview Visual Links',
        'manage_options',
        'mayami_visual_links_preview',
        'mayami_render_visual_links_preview_page'
    );
}

add_action('admin_menu', 'mayami_register_visual_links_html_menu', 20);

function mayami_remove_visual_links_duplicate_submenu() {
    remove_submenu_page('mayami_visual_links_builder', 'mayami_visual_links_builder');
}

add_action('admin_menu', 'mayami_remove_visual_links_duplicate_submenu', 999);

function mayami_visual_links_redirect_legacy_admin_pages() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    if ($page === '') {
        return;
    }

    $targets = array(
        'mayami_epk_html_builder' => 'mayami_visual_links_builder',
        'mayami_epk_html_builder_new' => 'mayami_visual_links_builder_new',
        'mayami_epk_drafts' => 'mayami_visual_links_drafts',
    );

    if (!isset($targets[$page])) {
        return;
    }

    $target_page = $targets[$page];
    $redirect = add_query_arg(array('page' => $target_page), admin_url('admin.php'));

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    if ($draft_id !== '') {
        $redirect = add_query_arg(array('draft_id' => $draft_id), $redirect);
    }

    wp_safe_redirect($redirect);
    exit;
}

add_action('admin_init', 'mayami_visual_links_redirect_legacy_admin_pages', 5);

function mayami_render_visual_links_new_submenu_page() {
    if (isset($_GET['draft_id'])) {
        unset($_GET['draft_id']);
    }

    mayami_render_visual_links_html_builder_page();
}

function mayami_handle_delete_visual_links_draft() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to do this.', 'mayami'), 403);
    }

    check_admin_referer('mayami_delete_visual_links_draft');

    $draft_id = isset($_POST['draft_id']) ? sanitize_text_field(wp_unslash($_POST['draft_id'])) : '';
    if ($draft_id !== '') {
        $store = mayami_get_visual_links_drafts_store();
        if (isset($store[$draft_id])) {
            unset($store[$draft_id]);
            mayami_update_visual_links_drafts_store($store);
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_drafts'));
    exit;
}

add_action('admin_post_mayami_delete_visual_links_draft', 'mayami_handle_delete_visual_links_draft');

function mayami_get_visual_links_drafts_store() {
    $store = get_option('mayami_visual_links_drafts_store', array());
    return is_array($store) ? $store : array();
}

function mayami_update_visual_links_drafts_store($store) {
    if (!is_array($store)) {
        $store = array();
    }

    return update_option('mayami_visual_links_drafts_store', $store, false);
}

function mayami_visual_links_handle_legacy_draft_page_slugs() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    if ($page === '' || strpos($page, 'mayami_epk_draft_') !== 0) {
        return;
    }

    $store = mayami_get_visual_links_drafts_store();
    foreach ($store as $draft) {
        $draft_id = isset($draft['id']) ? (string) $draft['id'] : '';
        if ($draft_id === '') {
            continue;
        }

        $legacy_slug = 'mayami_epk_draft_' . substr(md5($draft_id), 0, 12);
        if ($legacy_slug === $page) {
            wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_builder&draft_id=' . rawurlencode($draft_id)));
            exit;
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=mayami_visual_links_drafts'));
    exit;
}

add_action('admin_init', 'mayami_visual_links_handle_legacy_draft_page_slugs');

function mayami_sanitize_visual_links_html_payload($payload) {
    if (!is_array($payload)) {
        return array(
            'imageUrl' => '',
            'pdfHeaderEnabled' => false,
            'pdfCtaText' => 'DOWNLOAD THIS ELLENE-WP ONESHEET - CLICKABLE IN PDF',
            'pdfUrl' => '',
            'zones' => array(),
        );
    }

    $image_url = '';
    if (!empty($payload['imageUrl'])) {
        $raw_image_url = trim((string) $payload['imageUrl']);
        if (stripos($raw_image_url, 'data:image/') === 0) {
            $normalized_data_url = preg_replace('/\s+/', '', $raw_image_url);
            if (is_string($normalized_data_url) && strlen($normalized_data_url) <= (10 * MB_IN_BYTES)) {
                $image_url = $normalized_data_url;
            }
        } else {
            $image_url = esc_url_raw($raw_image_url);
        }
    }

    $pdf_url = '';
    if (!empty($payload['pdfUrl'])) {
        $raw_pdf_url = trim((string) $payload['pdfUrl']);
        $pdf_url = esc_url_raw($raw_pdf_url);
    }

    $pdf_cta_text = 'DOWNLOAD THIS ELLENE-WP ONESHEET - CLICKABLE IN PDF';
    if (isset($payload['pdfCtaText'])) {
        $raw_pdf_cta_text = trim((string) $payload['pdfCtaText']);
        if ($raw_pdf_cta_text !== '') {
            $pdf_cta_text = sanitize_text_field($raw_pdf_cta_text);
        }
    }

    $pdf_header_enabled = false;
    if (array_key_exists('pdfHeaderEnabled', $payload)) {
        $pdf_header_enabled = (bool) $payload['pdfHeaderEnabled'];
    }

    $zones = array();
    if (!empty($payload['zones']) && is_array($payload['zones'])) {
        foreach ($payload['zones'] as $zone) {
            if (!is_array($zone)) {
                continue;
            }

            $x = isset($zone['x']) ? (float) $zone['x'] : 0;
            $y = isset($zone['y']) ? (float) $zone['y'] : 0;
            $width = isset($zone['width']) ? (float) $zone['width'] : 0;
            $height = isset($zone['height']) ? (float) $zone['height'] : 0;

            if ($width <= 0 || $height <= 0) {
                continue;
            }

            $href_type = (isset($zone['hrefType']) && $zone['hrefType'] === 'anchor') ? 'anchor' : 'url';
            $href_value = isset($zone['hrefValue']) ? trim((string) $zone['hrefValue']) : '';
            if ($href_type === 'url') {
                $href_value = esc_url_raw($href_value);
            } else {
                $href_value = sanitize_text_field($href_value);
            }

            $zones[] = array(
                'id' => sanitize_text_field((string) ($zone['id'] ?? wp_generate_uuid4())),
                'x' => max(0, $x),
                'y' => max(0, $y),
                'width' => max(0, $width),
                'height' => max(0, $height),
                'hrefType' => $href_type,
                'hrefValue' => $href_value,
            );
        }
    }

    $canvas_width = isset($payload['canvasWidth']) ? (float) $payload['canvasWidth'] : 0;
    $canvas_height = isset($payload['canvasHeight']) ? (float) $payload['canvasHeight'] : 0;

    return array(
        'imageUrl' => $image_url,
        'pdfHeaderEnabled' => $pdf_header_enabled,
        'pdfCtaText' => $pdf_cta_text,
        'pdfUrl' => $pdf_url,
        'canvasWidth' => max(0, $canvas_width),
        'canvasHeight' => max(0, $canvas_height),
        'zones' => $zones,
    );
}

function mayami_ajax_save_visual_links_draft() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé.'), 403);
    }

    check_ajax_referer('mayami_visual_links_draft', 'nonce');

    $draft_name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    if ($draft_name === '') {
        wp_send_json_error(array('message' => 'Veuillez saisir un nom de visuel.'), 400);
    }

    $payload_raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
    $payload = json_decode((string) $payload_raw, true);
    if (!is_array($payload)) {
        wp_send_json_error(array('message' => 'Payload visuel invalide.'), 400);
    }

    $payload = mayami_sanitize_visual_links_html_payload($payload);
    $store = mayami_get_visual_links_drafts_store();

    $draft_id = isset($_POST['draft_id']) ? sanitize_text_field(wp_unslash($_POST['draft_id'])) : '';
    if ($draft_id === '') {
        $draft_id = wp_generate_uuid4();
    }

    $store[$draft_id] = array(
        'id' => $draft_id,
        'name' => $draft_name,
        'payload' => $payload,
        'updated_at' => current_time('mysql'),
    );

    mayami_update_visual_links_drafts_store($store);

    wp_send_json_success(array(
        'id' => $draft_id,
        'name' => $draft_name,
        'updatedAt' => $store[$draft_id]['updated_at'],
    ));
}

add_action('wp_ajax_mayami_save_visual_links_draft', 'mayami_ajax_save_visual_links_draft');

function mayami_ajax_get_visual_links_draft() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé.'), 403);
    }

    check_ajax_referer('mayami_visual_links_draft', 'nonce');

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    if ($draft_id === '') {
        wp_send_json_error(array('message' => 'Draft introuvable.'), 400);
    }

    $store = mayami_get_visual_links_drafts_store();
    if (empty($store[$draft_id]) || !is_array($store[$draft_id])) {
        wp_send_json_error(array('message' => 'Visuel non trouvé.'), 404);
    }

    $draft = $store[$draft_id];
    wp_send_json_success(array(
        'id' => $draft['id'],
        'name' => $draft['name'],
        'payload' => $draft['payload'],
        'updatedAt' => $draft['updated_at'],
        'export_url' => isset($draft['export_url']) ? (string) $draft['export_url'] : '',
        'export_filename' => isset($draft['export_filename']) ? (string) $draft['export_filename'] : '',
        'export_updated_at' => isset($draft['export_updated_at']) ? (string) $draft['export_updated_at'] : '',
        'template_email_export_url' => isset($draft['template_email_export_url']) ? (string) $draft['template_email_export_url'] : '',
        'template_email_export_filename' => isset($draft['template_email_export_filename']) ? (string) $draft['template_email_export_filename'] : '',
        'template_email_export_updated_at' => isset($draft['template_email_export_updated_at']) ? (string) $draft['template_email_export_updated_at'] : '',
        'template_html_export_url' => isset($draft['template_html_export_url']) ? (string) $draft['template_html_export_url'] : '',
        'template_html_export_filename' => isset($draft['template_html_export_filename']) ? (string) $draft['template_html_export_filename'] : '',
        'template_html_export_updated_at' => isset($draft['template_html_export_updated_at']) ? (string) $draft['template_html_export_updated_at'] : '',
    ));
}

add_action('wp_ajax_mayami_get_visual_links_draft', 'mayami_ajax_get_visual_links_draft');

function mayami_get_visual_links_export_dir() {
    $export_dir = trailingslashit(get_template_directory()) . 'visual-links-builder/exports-html';

    if (!is_dir($export_dir) && !wp_mkdir_p($export_dir)) {
        return new WP_Error(
            'export_dir_create_failed',
            'Impossible de créer le dossier d\'export requis: visual-links-builder/exports-html. Créez-le manuellement via FTP puis mettez les droits en écriture (755/775).'
        );
    }

    if (!is_writable($export_dir)) {
        return new WP_Error(
            'export_dir_not_writable',
            'Le dossier d\'export requis n\'est pas accessible en écriture: visual-links-builder/exports-html. Vérifiez les permissions (755/775) et le propriétaire.'
        );
    }

    return $export_dir;
}

function mayami_get_visual_links_export_target($requested_subdir = '') {
    $base_dir = mayami_get_visual_links_export_dir();
    if (is_wp_error($base_dir)) {
        return $base_dir;
    }

    $base_url = trailingslashit(get_template_directory_uri()) . 'visual-links-builder/exports-html';
    $raw_subdir = trim(str_replace('\\', '/', (string) $requested_subdir), "/ \t\n\r\0\x0B");

    if ($raw_subdir === '') {
        return array(
            'dir' => $base_dir,
            'url' => $base_url,
            'subdir' => '',
        );
    }

    $segments = array_values(array_filter(explode('/', $raw_subdir), static function($segment) {
        return $segment !== '';
    }));

    $safe_segments = array();
    foreach ($segments as $segment) {
        $safe = sanitize_file_name(remove_accents((string) $segment));
        $safe = trim((string) $safe, " .-_\t\n\r\0\x0B");
        if ($safe !== '') {
            $safe_segments[] = $safe;
        }
    }

    if (empty($safe_segments)) {
        return new WP_Error('invalid_export_subdir', 'Sous-dossier d\'export invalide.');
    }

    $safe_subdir = implode('/', $safe_segments);
    $target_dir = trailingslashit($base_dir) . $safe_subdir;

    if (!is_dir($target_dir) && !wp_mkdir_p($target_dir)) {
        return new WP_Error('export_subdir_create_failed', 'Impossible de créer le sous-dossier d\'export: ' . $safe_subdir . '.');
    }

    if (!is_writable($target_dir)) {
        return new WP_Error('export_subdir_not_writable', 'Le sous-dossier d\'export n\'est pas accessible en écriture: ' . $safe_subdir . '.');
    }

    $encoded_segments = array_map('rawurlencode', $safe_segments);
    $target_url = trailingslashit($base_url) . implode('/', $encoded_segments);

    return array(
        'dir' => $target_dir,
        'url' => $target_url,
        'subdir' => $safe_subdir,
    );
}

function mayami_build_visual_links_export_subdir($draft_name = '', $export_bucket = '') {
    $safe_name = sanitize_file_name(remove_accents((string) $draft_name));
    $safe_name = trim((string) $safe_name, " .-_\t\n\r\0\x0B");
    if ($safe_name === '') {
        $safe_name = 'visual-links';
    }

    $bucket_key = sanitize_key((string) $export_bucket);
    if ($bucket_key === 'template-email') {
        return $safe_name . '/Template-Email';
    }
    if ($bucket_key === 'template-html') {
        return $safe_name . '/Template-HTML';
    }

    return $safe_name;
}

function mayami_ajax_upload_visual_links_slice() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé.'), 403);
    }

    check_ajax_referer('mayami_visual_links_draft', 'nonce');

    if (empty($_FILES['slice_file']) || !is_array($_FILES['slice_file'])) {
        wp_send_json_error(array('message' => 'Fichier slice manquant.'), 400);
    }

    $file = $_FILES['slice_file'];
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(array('message' => 'Erreur upload slice (code ' . (int) ($file['error'] ?? -1) . ').'), 400);
    }

    $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
    if ($tmp_name === '' || !file_exists($tmp_name) || !is_readable($tmp_name)) {
        wp_send_json_error(array('message' => 'Fichier temporaire invalide pour la slice.'), 400);
    }

    if (!is_uploaded_file($tmp_name) && !isset($file['name'])) {
        wp_send_json_error(array('message' => 'Upload slice non reconnu par le serveur.'), 400);
    }

    $requested_filename = isset($_POST['filename']) ? sanitize_file_name(wp_unslash((string) $_POST['filename'])) : '';
    if ($requested_filename === '') {
        $requested_filename = 'slice-' . wp_generate_uuid4() . '.jpg';
    }

    $allowed_exts = array('jpg', 'jpeg', 'jpe', 'png', 'webp');
    $requested_ext = strtolower((string) pathinfo($requested_filename, PATHINFO_EXTENSION));
    if ($requested_ext === '' || !in_array($requested_ext, $allowed_exts, true)) {
        wp_send_json_error(array('message' => 'Extension de slice non autorisée (jpg/png/webp uniquement).'), 400);
    }

    $image_info = @getimagesize($tmp_name);
    if ($image_info === false || empty($image_info['mime'])) {
        wp_send_json_error(array('message' => 'Le fichier slice n\'est pas une image valide.'), 400);
    }

    $mime_to_ext = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    );
    $detected_mime = strtolower((string) $image_info['mime']);
    if (!isset($mime_to_ext[$detected_mime])) {
        wp_send_json_error(array('message' => 'Format image slice non pris en charge (' . $detected_mime . ').'), 400);
    }

    $filename_base = sanitize_file_name(pathinfo($requested_filename, PATHINFO_FILENAME));
    if ($filename_base === '') {
        $filename_base = 'slice-' . time();
    }
    $filename = $filename_base . '.' . $mime_to_ext[$detected_mime];

    $requested_subdir = isset($_POST['export_subdir']) ? sanitize_text_field(wp_unslash((string) $_POST['export_subdir'])) : '';
    $draft_name = isset($_POST['draft_name']) ? sanitize_text_field(wp_unslash((string) $_POST['draft_name'])) : '';
    $export_bucket = isset($_POST['export_bucket']) ? sanitize_key(wp_unslash((string) $_POST['export_bucket'])) : '';
    if ($requested_subdir === '' && $export_bucket !== '') {
        $requested_subdir = mayami_build_visual_links_export_subdir($draft_name, $export_bucket);
    }

    $export_target = mayami_get_visual_links_export_target($requested_subdir);
    if (is_wp_error($export_target)) {
        wp_send_json_error(array('message' => $export_target->get_error_message()), 500);
    }

    $target_path = trailingslashit((string) $export_target['dir']) . $filename;
    $moved = move_uploaded_file($tmp_name, $target_path);
    if (!$moved) {
        $raw_data = file_get_contents($tmp_name);
        if ($raw_data === false || file_put_contents($target_path, $raw_data) === false) {
            wp_send_json_error(array('message' => 'Impossible d\'écrire la slice ' . $filename . '.'), 500);
        }
    }

    $file_url = trailingslashit((string) $export_target['url']) . rawurlencode($filename);

    wp_send_json_success(array(
        'filename' => $filename,
        'path' => $target_path,
        'url' => $file_url,
        'subdir' => (string) $export_target['subdir'],
    ));
}

add_action('wp_ajax_mayami_upload_visual_links_slice', 'mayami_ajax_upload_visual_links_slice');

function mayami_ajax_export_visual_links_html() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé.'), 403);
    }

    check_ajax_referer('mayami_visual_links_draft', 'nonce');

    $html = isset($_POST['html']) ? (string) wp_unslash($_POST['html']) : '';
    if (trim($html) === '') {
        wp_send_json_error(array('message' => 'Contenu HTML vide.'), 400);
    }

    if (strlen($html) > (10 * MB_IN_BYTES)) {
        wp_send_json_error(array('message' => 'Le fichier HTML est trop volumineux.'), 400);
    }

    $draft_name = isset($_POST['draft_name']) ? sanitize_text_field(wp_unslash($_POST['draft_name'])) : '';
    if ($draft_name === '') {
        $draft_name = 'visual-links';
    }
    $safe_name = sanitize_file_name(remove_accents($draft_name));
    $safe_name = trim((string) $safe_name, " .-_\t\n\r\0\x0B");
    if ($safe_name === '') {
        $safe_name = 'visual-links';
    }
    $filename = $safe_name . '.html';

    $requested_subdir = isset($_POST['export_subdir']) ? sanitize_text_field(wp_unslash((string) $_POST['export_subdir'])) : '';
    $export_bucket = isset($_POST['export_bucket']) ? sanitize_key(wp_unslash((string) $_POST['export_bucket'])) : '';
    if ($requested_subdir === '' && $export_bucket !== '') {
        $requested_subdir = mayami_build_visual_links_export_subdir($draft_name, $export_bucket);
    }

    $export_target = mayami_get_visual_links_export_target($requested_subdir);
    if (is_wp_error($export_target)) {
        wp_send_json_error(array('message' => $export_target->get_error_message()), 500);
    }

    $export_path = trailingslashit((string) $export_target['dir']) . $filename;
    $written = file_put_contents($export_path, $html);
    if ($written === false) {
        $last_error = error_get_last();
        $last_error_message = is_array($last_error) && !empty($last_error['message']) ? (string) $last_error['message'] : 'inconnue';
        wp_send_json_error(array('message' => 'Échec de l\'écriture du fichier ' . $filename . ' (' . $last_error_message . ').'), 500);
    }

    if ($export_bucket === 'template-email') {
        $txt_filename = $safe_name . '.txt';
        $txt_path = trailingslashit((string) $export_target['dir']) . $txt_filename;
        $txt_written = file_put_contents($txt_path, $html);
        if ($txt_written === false) {
            wp_send_json_error(array('message' => 'Échec de l\'écriture du fichier TXT ' . $txt_filename . '.'), 500);
        }
    }

    $export_url = trailingslashit((string) $export_target['url']) . rawurlencode($filename);
    $export_updated_at = current_time('mysql');

    $draft_id = isset($_POST['draft_id']) ? sanitize_text_field(wp_unslash($_POST['draft_id'])) : '';
    if ($draft_id !== '') {
        $store = mayami_get_visual_links_drafts_store();
        if (!empty($store[$draft_id]) && is_array($store[$draft_id])) {
            $store[$draft_id]['export_url'] = $export_url;
            $store[$draft_id]['export_filename'] = $filename;
            $store[$draft_id]['export_updated_at'] = $export_updated_at;

            if ($export_bucket === 'template-email') {
                $store[$draft_id]['template_email_export_url'] = $export_url;
                $store[$draft_id]['template_email_export_filename'] = $filename;
                $store[$draft_id]['template_email_export_updated_at'] = $export_updated_at;
            } elseif ($export_bucket === 'template-html') {
                $store[$draft_id]['template_html_export_url'] = $export_url;
                $store[$draft_id]['template_html_export_filename'] = $filename;
                $store[$draft_id]['template_html_export_updated_at'] = $export_updated_at;
            }

            mayami_update_visual_links_drafts_store($store);
        }
    }

    wp_send_json_success(array(
        'path' => $export_path,
        'url' => $export_url,
        'filename' => $filename,
        'bytes' => (int) $written,
        'subdir' => (string) $export_target['subdir'],
    ));
}

add_action('wp_ajax_mayami_export_visual_links_html', 'mayami_ajax_export_visual_links_html');

function mayami_purge_directory_children($dir) {
    $dir = (string) $dir;
    if ($dir === '' || !is_dir($dir)) {
        return 0;
    }

    $deleted = 0;
    $items = scandir($dir);
    if (!is_array($items)) {
        return new WP_Error('purge_scan_failed', 'Impossible de lire le contenu du dossier à purger.');
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = trailingslashit($dir) . $item;
        if (is_dir($path)) {
            $nested = mayami_purge_directory_children($path);
            if (is_wp_error($nested)) {
                return $nested;
            }
            if (!@rmdir($path)) {
                return new WP_Error('purge_rmdir_failed', 'Impossible de supprimer le sous-dossier: ' . basename($path));
            }
            $deleted += (int) $nested + 1;
            continue;
        }

        if (is_file($path)) {
            wp_delete_file($path);
            if (file_exists($path)) {
                return new WP_Error('purge_delete_failed', 'Impossible de supprimer le fichier: ' . basename($path));
            }
            $deleted++;
        }
    }

    return $deleted;
}

function mayami_ajax_purge_visual_export_bucket() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Accès refusé.'), 403);
    }

    check_ajax_referer('mayami_visual_links_draft', 'nonce');

    $draft_name = isset($_POST['draft_name']) ? sanitize_text_field(wp_unslash($_POST['draft_name'])) : '';
    $export_bucket = isset($_POST['export_bucket']) ? sanitize_key(wp_unslash((string) $_POST['export_bucket'])) : '';

    if ($draft_name === '' || $export_bucket === '') {
        wp_send_json_error(array('message' => 'Paramètres manquants.'), 400);
    }

    $subdir = mayami_build_visual_links_export_subdir($draft_name, $export_bucket);
    $base_dir = mayami_get_visual_links_export_dir();
    if (is_wp_error($base_dir)) {
        wp_send_json_error(array('message' => $base_dir->get_error_message()), 500);
    }

    $safe_subdir = trim(str_replace('\\', '/', $subdir), '/');
    if ($safe_subdir === '' || strpos($safe_subdir, '..') !== false) {
        wp_send_json_error(array('message' => 'Sous-dossier invalide.'), 400);
    }

    $target_dir = trailingslashit($base_dir) . $safe_subdir;
    if (!is_dir($target_dir)) {
        wp_send_json_success(array('purged' => 0));
        return;
    }

    $purged = mayami_purge_directory_children($target_dir);
    if (is_wp_error($purged)) {
        wp_send_json_error(array('message' => $purged->get_error_message()), 500);
    }

    wp_send_json_success(array('purged' => $purged));
}

add_action('wp_ajax_mayami_purge_visual_export_bucket', 'mayami_ajax_purge_visual_export_bucket');

function mayami_render_visual_links_html_builder_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    $html_builder_url = add_query_arg(array(
        'wp_ajax_url' => admin_url('admin-ajax.php'),
        'wp_nonce' => wp_create_nonce('mayami_visual_links_draft'),
        'visual_links_draft_id' => $draft_id,
    ), trailingslashit(get_template_directory_uri()) . 'visual-links-builder/visual-links-builder.html');

    $selected_name = '';
    if ($draft_id !== '') {
        $store = mayami_get_visual_links_drafts_store();
        if (!empty($store[$draft_id]['name'])) {
            $selected_name = (string) $store[$draft_id]['name'];
        }
    }
    ?>
    <div class="wrap mayami-vlb-html-page">
        <h1>Visual Links Builder</h1>
        <p>Utilisez ce builder pour ajouter des zones cliquables sur n'importe quel visuel.</p>
        <?php if ($selected_name !== '') : ?>
            <p><strong>Visuel ouvert :</strong> <?php echo esc_html($selected_name); ?></p>
        <?php endif; ?>
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;overflow:hidden;">
            <iframe
                src="<?php echo esc_url($html_builder_url); ?>"
                title="Visual Links Builder"
                style="width:100%;height:calc(100vh - 210px);min-height:760px;border:0;display:block;"
            ></iframe>
        </div>
    </div>
    <?php
}

function mayami_render_visual_links_preview_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $draft_id = isset($_GET['draft_id']) ? sanitize_text_field(wp_unslash($_GET['draft_id'])) : '';
    $builder_url = admin_url('admin.php?page=mayami_visual_links_builder' . ($draft_id ? '&draft_id=' . urlencode($draft_id) : ''));
    ?>
    <div class="wrap mayami-vlb-preview-page" style="padding:0;margin:0;">
        <div id="previewContainer" style="position:relative;width:100%;height:100vh;overflow:auto;"></div>
        <script>
        (function() {
            const previewHtml = sessionStorage.getItem('mayami_vlb_preview_html');
            const container = document.getElementById('previewContainer');

            if (!previewHtml) {
                container.innerHTML = '<div style="padding:40px;text-align:center;"><h2>Aucune preview disponible</h2><p><a href="<?php echo esc_url($builder_url); ?>" class="button button-primary">Retour au builder</a></p></div>';
                return;
            }

            sessionStorage.removeItem('mayami_vlb_preview_html');

            const iframe = document.createElement('iframe');
            iframe.style.cssText = 'width:100%;height:100%;border:0;display:block;';
            container.appendChild(iframe);

            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(previewHtml);
            iframe.contentWindow.document.close();
        })();
        </script>
    </div>
    <?php
}

function mayami_render_visual_links_drafts_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);
    }

    $store = mayami_get_visual_links_drafts_store();
    uasort($store, function($a, $b) {
        return strcmp((string) ($b['updated_at'] ?? ''), (string) ($a['updated_at'] ?? ''));
    });
    ?>
    <div class="wrap mayami-vlb-drafts-page">
        <style>
            .mayami-delete-modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.58);
                z-index: 100000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }
            .mayami-delete-modal-backdrop.is-open { display: flex; }
            .mayami-delete-modal {
                width: min(520px, 100%);
                background: #fff;
                border-radius: 12px;
                border: 1px solid #d0d7e2;
                box-shadow: 0 18px 42px rgba(15, 23, 42, 0.22);
                overflow: hidden;
            }
            .mayami-delete-modal__head {
                padding: 16px 18px 10px;
                font-size: 19px;
                font-weight: 700;
                color: #0f172a;
            }
            .mayami-delete-modal__body {
                padding: 0 18px 16px;
                color: #334155;
                font-size: 14px;
                line-height: 1.45;
            }
            .mayami-delete-modal__actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 14px 18px 16px;
                border-top: 1px solid #e2e8f0;
                background: #f8fafc;
            }
        </style>
        <h1>Liste des visuels</h1>
        <p>Ouvrez un visuel existant pour reprendre l'edition dans Visual Links Builder.</p>
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mayami_visual_links_builder_new')); ?>" class="button button-primary">Nouveau visuel</a>
        </p>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Nom du visuel</th>
                    <th>Dernière mise à jour</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($store)) : ?>
                    <tr>
                        <td colspan="3">Aucun visuel enregistre pour le moment.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($store as $draft) : ?>
                        <tr>
                            <td><?php echo esc_html((string) ($draft['name'] ?? 'Sans nom')); ?></td>
                            <td><?php echo esc_html((string) ($draft['updated_at'] ?? '')); ?></td>
                            <td>
                                <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=mayami_visual_links_builder&draft_id=' . rawurlencode((string) ($draft['id'] ?? '')))); ?>">
                                    Ouvrir
                                </a>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-left:8px;">
                                    <?php wp_nonce_field('mayami_delete_visual_links_draft'); ?>
                                    <input type="hidden" name="action" value="mayami_delete_visual_links_draft">
                                    <input type="hidden" name="draft_id" value="<?php echo esc_attr((string) ($draft['id'] ?? '')); ?>">
                                    <button
                                        type="submit"
                                        class="button button-link-delete mayami-delete-draft-btn"
                                        data-draft-name="<?php echo esc_attr((string) ($draft['name'] ?? 'ce visuel')); ?>"
                                    >
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mayami-delete-modal-backdrop" id="mayamiDeleteModalBackdrop" aria-hidden="true">
        <div class="mayami-delete-modal" role="dialog" aria-modal="true" aria-labelledby="mayamiDeleteModalTitle">
            <div class="mayami-delete-modal__head" id="mayamiDeleteModalTitle">Supprimer ce visuel ?</div>
            <div class="mayami-delete-modal__body" id="mayamiDeleteModalBody">
                Cette action est definitive et supprimera ce visuel de la liste.
            </div>
            <div class="mayami-delete-modal__actions">
                <button type="button" class="button" id="mayamiDeleteCancelBtn">Annuler</button>
                <button type="button" class="button button-primary" id="mayamiDeleteConfirmBtn">Supprimer</button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const modalBackdrop = document.getElementById('mayamiDeleteModalBackdrop');
        const modalBody = document.getElementById('mayamiDeleteModalBody');
        const cancelBtn = document.getElementById('mayamiDeleteCancelBtn');
        const confirmBtn = document.getElementById('mayamiDeleteConfirmBtn');
        const deleteButtons = Array.from(document.querySelectorAll('.mayami-delete-draft-btn'));
        let pendingForm = null;

        function closeModal() {
            pendingForm = null;
            modalBackdrop.classList.remove('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'true');
        }

        function openModal(form, draftName) {
            pendingForm = form;
            modalBody.textContent = 'Supprimer definitivement "' + String(draftName || 'ce visuel') + '" ? Cette action est irreversible.';
            modalBackdrop.classList.add('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'false');
            confirmBtn.focus();
        }

        deleteButtons.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                const form = btn.closest('form');
                if (!form) { return; }
                openModal(form, btn.getAttribute('data-draft-name') || 'ce visuel');
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', () => {
            if (pendingForm) { pendingForm.submit(); }
        });
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) { closeModal(); }
        });
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) { closeModal(); }
        });
    })();
    </script>
    <?php
}