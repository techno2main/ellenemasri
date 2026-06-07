<?php

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
