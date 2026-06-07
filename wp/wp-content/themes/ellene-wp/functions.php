<?php

/**

 * ellene-wp Theme Functions

 * 

 * @package ElleneWp

 */



// Prevent direct access

if (!defined('ABSPATH')) {

    exit;

}



// Include CMB2 Configuration

require_once get_template_directory() . '/inc/cmb2/options-config.php';

require_once get_template_directory() . '/inc/visual-links.php';

require_once get_template_directory() . '/inc/modules/registry.php';

require_once get_template_directory() . '/inc/modules/resolver.php';

require_once get_template_directory() . '/inc/modules/renderer.php';

require_once get_template_directory() . '/inc/modules/shared-sections.php';



/**

 * Get a landing option value from the active key, with legacy key compatibility.

 *

 * This avoids admin/front mismatches when some environments still store data

 * under the previous CMB2 option key.

 *

 * @param string $field_id Option field id.

 * @param mixed  $default  Default value if field is not found.

 * @return mixed

 */

function mayami_get_landing_option($field_id, $default = '') {

    $primary_options = get_option('mayami_landing_options', array());

    if (is_array($primary_options) && array_key_exists($field_id, $primary_options)) {

        return $primary_options[$field_id];

    }



    $legacy_options = get_option('mayami_options', array());

    if (is_array($legacy_options) && array_key_exists($field_id, $legacy_options)) {

        return $legacy_options[$field_id];

    }



    return $default;

}



/**

 * Raise the upload limit reported by WordPress media screens.

 *

 * This affects the limit shown in the admin UI and the size WordPress

 * uses when checking uploads, while still allowing the server's PHP limits

 * to act as the final fallback.

 */

function mayami_upload_size_limit($size) {

    $desired_limit = 128 * MB_IN_BYTES;

    return max((int) $size, $desired_limit);

}

add_filter('upload_size_limit', 'mayami_upload_size_limit');



/**

 * Theme setup

 */

function mayami_theme_setup() {

    // Add theme support

    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    

    // Disable Gutenberg editor (not needed for this landing page)

    add_filter('use_block_editor_for_post', '__return_false');

    

    // Disable WordPress emoji scripts (interferes with Unicode icons)

    remove_action('wp_head', 'print_emoji_detection_script', 7);

    remove_action('wp_print_styles', 'print_emoji_styles');

}

add_action('after_setup_theme', 'mayami_theme_setup');



/**

 * Output the theme favicon on all contexts.

 */

function mayami_output_theme_favicon() {

    $favicon_svg_path = get_template_directory() . '/assets/favicon.svg';

    $favicon_png_32_path = get_template_directory() . '/assets/favicon-32.png';

    $favicon_png_180_path = get_template_directory() . '/assets/favicon-180.png';

    $favicon_svg_url = get_template_directory_uri() . '/assets/favicon.svg';

    $favicon_png_32_url = get_template_directory_uri() . '/assets/favicon-32.png';

    $favicon_png_180_url = get_template_directory_uri() . '/assets/favicon-180.png';



    if (file_exists($favicon_svg_path)) {

        $favicon_svg_url .= '?v=' . filemtime($favicon_svg_path);

    }



    if (file_exists($favicon_png_32_path)) {

        $favicon_png_32_url .= '?v=' . filemtime($favicon_png_32_path);

    }



    if (file_exists($favicon_png_180_path)) {

        $favicon_png_180_url .= '?v=' . filemtime($favicon_png_180_path);

    }



    echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($favicon_svg_url) . '" />' . "\n";

    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";

    echo '<link rel="shortcut icon" href="' . esc_url($favicon_png_32_url) . '" />' . "\n";

    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($favicon_png_180_url) . '" />' . "\n";

}

add_action('wp_head', 'mayami_output_theme_favicon', 1);

add_action('admin_head', 'mayami_output_theme_favicon', 1);

add_action('login_head', 'mayami_output_theme_favicon', 1);



/**

 * Enqueue scripts and styles

 */

function mayami_enqueue_assets() {

    // Font Awesome 6

    wp_enqueue_style(

        'font-awesome-6',

        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',

        [],

        '6.5.1'

    );

    

    // Compiled Tailwind CSS

    wp_enqueue_style(

        'mayami-tailwind', 

        get_template_directory_uri() . '/style-compiled.css', 

        [], 

        '1.0.0'

    );



    $visual_links_css_path = get_template_directory() . '/assets/visual-links.css';

    wp_enqueue_style(

        'mayami-visual-links',

        get_template_directory_uri() . '/assets/visual-links.css',

        array('mayami-tailwind'),

        file_exists($visual_links_css_path) ? (string) filemtime($visual_links_css_path) : '1.0.0'

    );



    wp_add_inline_style(

        'mayami-tailwind',

        'img, video, iframe { -webkit-user-drag: none; -webkit-touch-callout: none; user-select: none; }'

    );

    

    $stream_player_js_path = get_template_directory() . '/assets/stream-player.js';

    $content_protection_js_path = get_template_directory() . '/assets/content-protection.js';



    // Stream platform player JS

    wp_enqueue_script(

        'mayami-stream-player',

        get_template_directory_uri() . '/assets/stream-player.js',

        [],

        file_exists($stream_player_js_path) ? (string) filemtime($stream_player_js_path) : '1.0.0',

        true

    );



    // Best-effort media protection script

    wp_enqueue_script(

        'mayami-content-protection',

        get_template_directory_uri() . '/assets/content-protection.js',

        [],

        file_exists($content_protection_js_path) ? (string) filemtime($content_protection_js_path) : '1.0.0',

        true

    );

}

add_action('wp_enqueue_scripts', 'mayami_enqueue_assets');



/**

 * Enqueue admin assets for ellene-wp Landing page

 */

function mayami_enqueue_admin_assets($hook) {

    $is_landing_page = ('toplevel_page_mayami_landing_options' === $hook);

    $is_visual_links_page = (strpos((string) $hook, 'mayami_visual_links') !== false);



    if (!$is_landing_page && !$is_visual_links_page) {

        return;

    }



    // Required by the Visual Links Builder (selection from WP media library).

    wp_enqueue_media();



    // Keep existing landing admin assets behavior unchanged.

    if (!$is_landing_page) {

        return;

    }



    $admin_css_path = get_template_directory() . '/assets/admin-nav.css';

    $admin_js_path = get_template_directory() . '/assets/admin-nav.js';

    $visual_links_admin_css_path = get_template_directory() . '/assets/admin-visual-links-builder.css';

    $visual_links_admin_js_path = get_template_directory() . '/assets/admin-visual-links-builder.js';



    // Admin navigation CSS

    wp_enqueue_style(

        'mayami-admin-nav',

        get_template_directory_uri() . '/assets/admin-nav.css',

        [],

        file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : '1.0.0'

    );

    

    // Admin navigation JS

    wp_enqueue_script(

        'mayami-admin-nav',

        get_template_directory_uri() . '/assets/admin-nav.js',

        [],

        file_exists($admin_js_path) ? (string) filemtime($admin_js_path) : '1.0.0',

        true

    );



    wp_enqueue_style(

        'mayami-admin-visual-links-builder',

        get_template_directory_uri() . '/assets/admin-visual-links-builder.css',

        array('mayami-admin-nav'),

        file_exists($visual_links_admin_css_path) ? (string) filemtime($visual_links_admin_css_path) : '1.0.0'

    );



    wp_enqueue_script(

        'mayami-admin-visual-links-builder',

        get_template_directory_uri() . '/assets/admin-visual-links-builder.js',

        array('jquery', 'media-editor', 'media-views', 'wp-util'),

        file_exists($visual_links_admin_js_path) ? (string) filemtime($visual_links_admin_js_path) : '1.0.0',

        true

    );

}

add_action('admin_enqueue_scripts', 'mayami_enqueue_admin_assets');



/**

 * Hide default WordPress footer text on ellene-wp settings page.

 */

function mayami_hide_wp_footer_text_on_landing($text) {

    $screen = get_current_screen();

    if ($screen && (

        $screen->id === 'toplevel_page_mayami_landing_options' ||

        strpos($screen->id, 'mayami_visual_links') !== false

    )) {

        return '';

    }



    return $text;

}

add_filter('admin_footer_text', 'mayami_hide_wp_footer_text_on_landing', 20);



/**

 * Add a prominent "Modifier les détails" button in the media modal for client accounts.

 * The WP media modal has no visible save button — this makes it obvious.

 */

function mayami_media_modal_edit_button() {

    $screen = get_current_screen();

    if (!$screen || $screen->base !== 'upload') {

        return;

    }

    $current_user = wp_get_current_user();

    if ($current_user && $current_user->user_login === 'admin-my') {

        return;

    }

    ?>

    <script>

    (function() {

        function injectEditButton() {

            var sidebar = document.querySelector('.attachment-details .details');

            if (!sidebar || sidebar.querySelector('.mayami-edit-btn')) return;



            var editLink = sidebar.querySelector('a.edit-attachment');

            if (!editLink) return;



            var btn = document.createElement('a');

            btn.href = editLink.href;

            btn.className = 'button button-primary mayami-edit-btn';

            btn.textContent = '💾 Modifier / Enregistrer';

            btn.style.cssText = 'display:block;text-align:center;margin:12px 0 4px;width:100%;box-sizing:border-box;';

            sidebar.insertBefore(btn, editLink);

        }



        var observer = new MutationObserver(function() {

            injectEditButton();

        });

        observer.observe(document.body, { childList: true, subtree: true });

    })();

    </script>

    <?php

}

add_action('admin_footer', 'mayami_media_modal_edit_button');



/**

 * Simplify WP admin menu for client accounts.

 * Keep full admin menu for the technical owner account.

 */

function mayami_limit_admin_menu_for_client() {

    if (!is_admin()) {

        return;

    }



    if (!current_user_can('manage_options')) {

        return;

    }



    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {

        return;

    }



    // Keep full menu for owner account.

    if ($current_user->user_login === 'admin-my') {

        return;

    }



    // Hide unrelated content menus for client-facing admin.

    remove_menu_page('index.php');               // Tableau de bord

    remove_menu_page('edit.php');                 // Articles

    remove_menu_page('edit-comments.php');        // Commentaires

    remove_menu_page('edit.php?post_type=page'); // Pages

    remove_menu_page('themes.php');              // Apparence (thèmes, personnalisation)

    remove_menu_page('plugins.php');             // Extensions

    remove_menu_page('users.php');               // Utilisateurs

    remove_menu_page('tools.php');               // Outils

    remove_menu_page('options-general.php');     // Réglages

}

add_action('admin_menu', 'mayami_limit_admin_menu_for_client', 999);



/**

 * Redirect client accounts to ellene-wp Landing instead of the dashboard after login.

 */

function mayami_client_login_redirect($redirect_to, $requested_redirect_to, $user) {

    if (is_wp_error($user) || empty($user->user_login)) {

        return $redirect_to;

    }



    if ($user->user_login === 'admin-my') {

        return $redirect_to;

    }



    return admin_url('admin.php?page=mayami_landing_options');

}

add_filter('login_redirect', 'mayami_client_login_redirect', 10, 3);



/**

 * Keep admin bar actions coherent with the reduced client menu.

 */

function mayami_limit_admin_bar_for_client($wp_admin_bar) {

    if (!is_admin_bar_showing()) {

        return;

    }



    if (!current_user_can('manage_options')) {

        return;

    }



    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {

        return;

    }



    if ($current_user->user_login === 'admin-my') {

        return;

    }



    // Keep only media creation for client admins.

    // Remove the default "+ New / Créer" parent and its children,

    // then add a single direct shortcut to media upload.

    $wp_admin_bar->remove_node('new-content');

    $wp_admin_bar->remove_node('new-post');

    $wp_admin_bar->remove_node('new-page');

    $wp_admin_bar->remove_node('new-user');

    $wp_admin_bar->remove_node('new-media');



    $wp_admin_bar->add_node([

        'id'    => 'mayami-new-media',

        'title' => 'Ajouter un media',

        'href'  => admin_url('media-new.php'),

        'meta'  => [

            'title' => 'Ajouter un media',

        ],

    ]);



    // Hide comments bubble since comments menu is also hidden.

    $wp_admin_bar->remove_node('comments');



    // Hide Customizer shortcut for client admins.

    $wp_admin_bar->remove_node('customize');

}

add_action('admin_bar_menu', 'mayami_limit_admin_bar_for_client', 999);



/**

 * Redirect the front-end admin bar "Edit" link to ellene-wp Landing settings.

 */

function mayami_redirect_admin_bar_edit_to_landing($wp_admin_bar) {

    if (!is_admin_bar_showing() || is_admin()) {

        return;

    }



    if (!current_user_can('manage_options') || !is_front_page()) {

        return;

    }



    $current_user = wp_get_current_user();

    if (!$current_user || empty($current_user->user_login)) {

        return;

    }



    if ($current_user->user_login === 'admin-my') {

        return;

    }



    $edit_node = $wp_admin_bar->get_node('edit');

    if (!$edit_node) {

        return;

    }



    $edit_node->href = admin_url('admin.php?page=mayami_landing_options');

    $wp_admin_bar->add_node($edit_node);

}

add_action('admin_bar_menu', 'mayami_redirect_admin_bar_edit_to_landing', 1001);



/**

 * Register an independent Visual Links Builder top-level menu.

 */

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



    // Hidden preview page (not shown in menu)

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



/**

 * Remove duplicate submenu generated automatically for top-level menu.

 */

function mayami_remove_visual_links_duplicate_submenu() {

    remove_submenu_page('mayami_visual_links_builder', 'mayami_visual_links_builder');

}

add_action('admin_menu', 'mayami_remove_visual_links_duplicate_submenu', 999);



/**

 * Redirect legacy EPK admin slugs to current Visual Links slugs.

 */

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



/**

 * Render the "Nouveau visuel" submenu.

 */

function mayami_render_visual_links_new_submenu_page() {

    if (isset($_GET['draft_id'])) {

        unset($_GET['draft_id']);

    }



    mayami_render_visual_links_html_builder_page();

}



/**

 * Handle draft deletion from admin list.

 */

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



/**

 * Return the full Visual Links drafts store from options.

 *

 * @return array<string, array<string, mixed>>

 */

function mayami_get_visual_links_drafts_store() {

    $store = get_option('mayami_visual_links_drafts_store', array());

    return is_array($store) ? $store : array();

}



/**

 * Persist the Visual Links drafts store in options.

 *

 * @param array<string, array<string, mixed>> $store Draft store.

 * @return bool

 */

function mayami_update_visual_links_drafts_store($store) {

    if (!is_array($store)) {

        $store = array();

    }



    return update_option('mayami_visual_links_drafts_store', $store, false);

}



/**

 * Backward compatibility for legacy draft submenu slugs.

 *

 * Older links may still point to page=mayami_epk_draft_<hash>.

 * We map them back to the real draft_id and redirect to the current builder URL.

 */

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



/**

 * Sanitize and normalize a payload coming from the HTML builder.

 *

 * @param array<string, mixed> $payload Raw payload.

 * @return array<string, mixed>

 */

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



        // Keep data URLs generated by the local HTML builder so draft reopening

        // can restore the exact visual without relying on browser local storage.

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



/**

 * AJAX: save a draft in DB.

 */

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



/**

 * AJAX: load one draft from DB.

 */

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

        'id'              => $draft['id'],

        'name'            => $draft['name'],

        'payload'         => $draft['payload'],

        'updatedAt'       => $draft['updated_at'],

        'export_url'      => isset($draft['export_url'])      ? (string) $draft['export_url']      : '',

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



/**

 * Resolve the required export directory for Visual Links Builder.

 *

 * @return string|WP_Error

 */

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



/**

 * Resolve export target (base folder or safe subfolder) for Visual Links Builder.

 *

 * @param string $requested_subdir Optional subfolder relative to exports-html.

 * @return array|WP_Error

 */

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



/**

 * Build a normalized export subdir for one visual and one template bucket.

 *

 * Result format: <visual-slug>/Template-Email or <visual-slug>/Template-HTML

 *

 * @param string $draft_name   Visual name.

 * @param string $export_bucket template-email|template-html.

 * @return string

 */

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



/**

 * AJAX: upload one generated image slice for email templates.

 */

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



    // Some server setups make is_uploaded_file() unreliable in admin-ajax contexts.

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



/**

 * AJAX: export current preview to the dedicated HTML file used for communication.

 */

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



    // Pour le bucket template-email, sauvegarder aussi un fichier .txt avec le code HTML

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



    // Persist export URL into the draft store so it survives page refreshes.

    $draft_id = isset($_POST['draft_id']) ? sanitize_text_field(wp_unslash($_POST['draft_id'])) : '';

    if ($draft_id !== '') {

        $store = mayami_get_visual_links_drafts_store();

        if (!empty($store[$draft_id]) && is_array($store[$draft_id])) {

            $store[$draft_id]['export_url']      = $export_url;

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

        'path'     => $export_path,

        'url'      => $export_url,

        'filename' => $filename,

        'bytes'    => (int) $written,

        'subdir'   => (string) $export_target['subdir'],

    ));

}

add_action('wp_ajax_mayami_export_visual_links_html', 'mayami_ajax_export_visual_links_html');



/**

 * Supprime récursivement les enfants d'un dossier (fichiers + sous-dossiers),

 * sans supprimer le dossier racine lui-même.

 *

 * @param string $dir Dossier cible.

 * @return int|WP_Error Nombre d'éléments supprimés ou WP_Error.

 */

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



/**

 * AJAX: purge tous les fichiers d'un bucket d'export (Template-Email ou Template-HTML).

 * Appelé avant chaque nouvelle génération pour ne conserver que les fichiers à jour.

 */

function mayami_ajax_purge_visual_export_bucket() {

    if (!current_user_can('manage_options')) {

        wp_send_json_error(array('message' => 'Accès refusé.'), 403);

    }



    check_ajax_referer('mayami_visual_links_draft', 'nonce');



    $draft_name   = isset($_POST['draft_name'])   ? sanitize_text_field(wp_unslash($_POST['draft_name']))   : '';

    $export_bucket = isset($_POST['export_bucket']) ? sanitize_key(wp_unslash($_POST['export_bucket'])) : '';



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



/**

 * Render the standalone HTML Visual Links Builder inside WP admin.

 */

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



/**

 * Render the Visual Links preview page (displays preview HTML from sessionStorage).

 */

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



            // Clear sessionStorage after reading

            sessionStorage.removeItem('mayami_vlb_preview_html');



            // Create iframe to display preview HTML

            const iframe = document.createElement('iframe');

            iframe.style.cssText = 'width:100%;height:100%;border:0;display:block;';

            container.appendChild(iframe);



            // Write preview HTML to iframe

            iframe.contentWindow.document.open();

            iframe.contentWindow.document.write(previewHtml);

            iframe.contentWindow.document.close();

        })();

        </script>

    </div>

    <?php

}



/**

 * Render the visual drafts list page.

 */

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

            .mayami-delete-modal-backdrop.is-open {

                display: flex;

            }

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

                if (!form) {

                    return;

                }

                openModal(form, btn.getAttribute('data-draft-name') || 'ce visuel');

            });

        });



        cancelBtn.addEventListener('click', closeModal);



        confirmBtn.addEventListener('click', () => {

            if (pendingForm) {

                pendingForm.submit();

            }

        });



        modalBackdrop.addEventListener('click', (event) => {

            if (event.target === modalBackdrop) {

                closeModal();

            }

        });



        window.addEventListener('keydown', (event) => {

            if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) {

                closeModal();

            }

        });

    })();

    </script>

    <?php

}



// Hidden Statistics page reserved for the owner account only.

function mayami_register_statistics_page() {

    $current_user = wp_get_current_user();

    if (!$current_user || $current_user->user_login !== 'admin-my') {

        return;

    }



    add_submenu_page(

        null,

        'Statistics',

        'Statistics',

        'manage_options',

        'mayami_statistics',

        'mayami_statistics_page'

    );

}

add_action('admin_menu', 'mayami_register_statistics_page');



function mayami_statistics_page() {

    $current_user = wp_get_current_user();

    if (!$current_user || $current_user->user_login !== 'admin-my') {

        wp_die(esc_html__('You are not allowed to access this page.', 'mayami'), 403);

    }



    ?>

    <div class="wrap">

        <h1>📊 Statistics — ellenemasri.pro</h1>

        <p>View your site analytics directly in Google Analytics 4.</p>

        <a href="https://analytics.google.com/analytics/web/#/p539563734/reports/reportinghub"

           target="_blank"

           class="button button-primary" style="font-size:15px;padding:10px 20px;height:auto;margin-top:10px;">

            Open Google Analytics 4 →

        </a>

        <p style="margin-top:20px;color:#666;font-size:13px;">

            Opens in a new tab. Sign in with the Google account linked to this site.

        </p>

    </div>

    <?php

}



/**

 * Force noindex on the public landing while launch is pending.

 *

 * This keeps the site out of search results without relying on plugin settings.

 */

function mayami_force_landing_noindex($robots) {

    if (is_admin()) {

        return $robots;

    }



    if (is_front_page() || is_home()) {

        return array(

            'noindex' => true,

            'nofollow' => true,

            'noarchive' => true,

            'nosnippet' => true,

            'max-snippet' => 0,

            'max-image-preview' => 'none',

            'max-video-preview' => 0,

        );

    }



    return $robots;

}

add_filter('wp_robots', 'mayami_force_landing_noindex');



