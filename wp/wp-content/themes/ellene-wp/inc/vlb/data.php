<?php

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
