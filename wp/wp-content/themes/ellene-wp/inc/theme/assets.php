<?php

/**
 * Frontend and admin asset loading.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_enqueue_assets() {
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    wp_enqueue_style(
        'ellene-wp-tailwind',
        get_template_directory_uri() . '/style-compiled.css',
        array(),
        '1.0.0'
    );

    wp_add_inline_style(
        'ellene-wp-tailwind',
        'img, video, iframe { -webkit-user-drag: none; -webkit-touch-callout: none; user-select: none; }'
    );

    $stream_player_js_path = get_template_directory() . '/assets/stream-player.js';
    $content_protection_js_path = get_template_directory() . '/assets/content-protection.js';

    wp_enqueue_script(
        'ellene-wp-stream-player',
        get_template_directory_uri() . '/assets/stream-player.js',
        array(),
        file_exists($stream_player_js_path) ? (string) filemtime($stream_player_js_path) : '1.0.0',
        true
    );

    wp_enqueue_script(
        'ellene-wp-content-protection',
        get_template_directory_uri() . '/assets/content-protection.js',
        array(),
        file_exists($content_protection_js_path) ? (string) filemtime($content_protection_js_path) : '1.0.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'ellene_wp_enqueue_assets');

function ellene_wp_enqueue_admin_assets($hook) {
    $is_landing_page = ('toplevel_page_ellene-wp_landing_options' === $hook);
    $is_visual_links_page = (strpos((string) $hook, 'ellene_wp_visual_links') !== false);

    if (!$is_landing_page && !$is_visual_links_page) {
        return;
    }

    wp_enqueue_media();

    if (!$is_landing_page) {
        return;
    }

    $admin_css_path = get_template_directory() . '/assets/admin-nav.css';
    $admin_js_path = get_template_directory() . '/assets/admin-nav.js';
    $visual_links_admin_css_path = get_template_directory() . '/assets/admin-visual-links-builder.css';
    $visual_links_admin_js_path = get_template_directory() . '/assets/admin-visual-links-builder.js';

    wp_enqueue_style(
        'ellene-wp-admin-nav',
        get_template_directory_uri() . '/assets/admin-nav.css',
        array(),
        file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'ellene-wp-admin-nav',
        get_template_directory_uri() . '/assets/admin-nav.js',
        array(),
        file_exists($admin_js_path) ? (string) filemtime($admin_js_path) : '1.0.0',
        true
    );

    wp_enqueue_style(
        'ellene-wp-admin-visual-links-builder',
        get_template_directory_uri() . '/assets/admin-visual-links-builder.css',
        array('ellene-wp-admin-nav'),
        file_exists($visual_links_admin_css_path) ? (string) filemtime($visual_links_admin_css_path) : '1.0.0'
    );

    wp_enqueue_script(
        'ellene-wp-admin-visual-links-builder',
        get_template_directory_uri() . '/assets/admin-visual-links-builder.js',
        array('jquery', 'media-editor', 'media-views', 'wp-util'),
        file_exists($visual_links_admin_js_path) ? (string) filemtime($visual_links_admin_js_path) : '1.0.0',
        true
    );
}

add_action('admin_enqueue_scripts', 'ellene_wp_enqueue_admin_assets');

function ellene_wp_resolve_user_login_from_avatar_subject($id_or_email) {
    if ($id_or_email instanceof WP_User) {
        return (string) $id_or_email->user_login;
    }

    if ($id_or_email instanceof WP_Comment) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);

            return $user ? (string) $user->user_login : '';
        }

        if (!empty($id_or_email->comment_author_email)) {
            $user = get_user_by('email', (string) $id_or_email->comment_author_email);

            return $user ? (string) $user->user_login : '';
        }

        return '';
    }

    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', (int) $id_or_email);

        return $user ? (string) $user->user_login : '';
    }

    if (is_string($id_or_email) && is_email($id_or_email)) {
        $user = get_user_by('email', $id_or_email);

        return $user ? (string) $user->user_login : '';
    }

    return '';
}

function ellene_wp_customize_account_avatars($args, $id_or_email) {
    $user_login = strtolower(ellene_wp_resolve_user_login_from_avatar_subject($id_or_email));

    if ($user_login === '') {
        return $args;
    }

    $custom_url = '';

    if ($user_login === 'ellene-admin') {
        $custom_url = get_site_icon_url((int) $args['size']);

        if (!$custom_url) {
            $custom_url = get_site_icon_url(96);
        }
    } elseif ($user_login === 'admin-my') {
        $custom_url = 'https://ellenemasri.com/wp/wp-content/uploads/2026/06/TAD.jpg';
    }

    if (!$custom_url) {
        return $args;
    }

    $args['url'] = esc_url_raw($custom_url);
    $args['found_avatar'] = true;

    return $args;
}

add_filter('pre_get_avatar_data', 'ellene_wp_customize_account_avatars', 20, 2);

function ellene_wp_hide_wp_footer_text_on_landing($text) {
    return '';
}

add_filter('admin_footer_text', 'ellene_wp_hide_wp_footer_text_on_landing', 20);

function ellene_wp_hide_wp_footer_version($text) {
    return '';
}

add_filter('update_footer', 'ellene_wp_hide_wp_footer_version', 20);

function ellene_wp_media_modal_edit_button() {
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
            if (!sidebar || sidebar.querySelector('.ellene-wp-edit-btn')) return;

            var editLink = sidebar.querySelector('a.edit-attachment');
            if (!editLink) return;

            var btn = document.createElement('a');
            btn.href = editLink.href;
            btn.className = 'button button-primary ellene-wp-edit-btn';
            btn.textContent = 'Modifier / Enregistrer';
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

add_action('admin_footer', 'ellene_wp_media_modal_edit_button');

function ellene_wp_limit_admin_menu_for_client() {
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

    if ($current_user->user_login === 'admin-my') {
        return;
    }

    $user_login = strtolower((string) $current_user->user_login);
    $is_ellene_admin = ($user_login === 'ellene-admin');

    remove_menu_page('index.php');
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('edit.php?post_type=page');
    if (!$is_ellene_admin) {
        remove_menu_page('themes.php');
    }
    remove_menu_page('plugins.php');
    remove_menu_page('users.php');
    remove_menu_page('tools.php');

    if (!$is_ellene_admin) {
        remove_menu_page('options-general.php');
    }
}

add_action('admin_menu', 'ellene_wp_limit_admin_menu_for_client', 999);

function ellene_wp_limit_settings_submenu_for_ellene_admin() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }

    global $submenu;

    if (empty($submenu['options-general.php']) || !is_array($submenu['options-general.php'])) {
        return;
    }

    foreach ($submenu['options-general.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) $submenu_item[2] : '';

        if ($slug !== 'options-general.php') {
            unset($submenu['options-general.php'][$index]);
        }
    }
}

add_action('admin_menu', 'ellene_wp_limit_settings_submenu_for_ellene_admin', 1000);

function ellene_wp_limit_appearance_submenu_for_ellene_admin() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }

    global $submenu;

    if (empty($submenu['themes.php']) || !is_array($submenu['themes.php'])) {
        return;
    }

    foreach ($submenu['themes.php'] as $index => $submenu_item) {
        $slug = isset($submenu_item[2]) ? (string) $submenu_item[2] : '';

        if ($slug !== 'themes.php') {
            unset($submenu['themes.php'][$index]);
        }
    }
}

add_action('admin_menu', 'ellene_wp_limit_appearance_submenu_for_ellene_admin', 1001);

function ellene_wp_redirect_appearance_pages_to_themes_for_ellene_admin() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }

    global $pagenow;

    $blocked_pagenow = array(
        'customize.php',
        'site-editor.php',
        'theme-editor.php',
    );

    if (in_array((string) $pagenow, $blocked_pagenow, true)) {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }
}

add_action('admin_init', 'ellene_wp_redirect_appearance_pages_to_themes_for_ellene_admin', 20);

function ellene_wp_hide_brevo_menu_for_ellene_admin() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }

    $known_brevo_slugs = array(
        'sib_page_home',
        'mailin',
        'admin.php?page=sib_page_home',
    );

    foreach ($known_brevo_slugs as $slug) {
        remove_menu_page($slug);
        remove_submenu_page($slug, $slug);
    }

    global $menu, $submenu;

    if (!empty($menu) && is_array($menu)) {
        foreach ($menu as $index => $menu_item) {
            $menu_title = isset($menu_item[0]) ? strtolower(wp_strip_all_tags((string) $menu_item[0])) : '';
            $menu_slug = isset($menu_item[2]) ? strtolower((string) $menu_item[2]) : '';

            $title_match = (strpos($menu_title, 'brevo') !== false || strpos($menu_title, 'sendinblue') !== false);
            $slug_match = (
                strpos($menu_slug, 'brevo') !== false ||
                strpos($menu_slug, 'sendinblue') !== false ||
                strpos($menu_slug, 'sib_page_home') !== false ||
                strpos($menu_slug, 'mailin') !== false
            );

            if ($title_match || $slug_match) {
                unset($menu[$index]);
            }
        }
    }

    if (!empty($submenu) && is_array($submenu)) {
        foreach ($submenu as $parent_slug => $submenu_items) {
            if (!is_array($submenu_items)) {
                continue;
            }

            foreach ($submenu_items as $sub_index => $submenu_item) {
                $submenu_title = isset($submenu_item[0]) ? strtolower(wp_strip_all_tags((string) $submenu_item[0])) : '';
                $submenu_slug = isset($submenu_item[2]) ? strtolower((string) $submenu_item[2]) : '';

                $title_match = (strpos($submenu_title, 'brevo') !== false || strpos($submenu_title, 'sendinblue') !== false);
                $slug_match = (
                    strpos($submenu_slug, 'brevo') !== false ||
                    strpos($submenu_slug, 'sendinblue') !== false ||
                    strpos($submenu_slug, 'sib_page_home') !== false ||
                    strpos($submenu_slug, 'mailin') !== false
                );

                if ($title_match || $slug_match) {
                    unset($submenu[$parent_slug][$sub_index]);
                }
            }
        }
    }
}

add_action('admin_menu', 'ellene_wp_hide_brevo_menu_for_ellene_admin', 99999);

function ellene_wp_render_vlb_notice_intermediate_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this page.', 'ellene-wp'), 403);
    }
    ?>
    <style>
        body.wp-admin,
        #wpcontent,
        #wpbody,
        #wpbody-content {
            background: #f8d24a;
        }

        .wrap {
            max-width: none;
        }

        .wrap .notice.notice-warning {
            background: transparent;
            box-shadow: none;
        }

        #wpbody-content {
            min-height: calc(100vh - 32px);
            padding-bottom: 24px;
        }

        .vlb-notice-cone {
            display: block;
            width: 187px;
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        @media (min-width: 1024px) {
            .vlb-notice-gif {
                width: auto;
                max-height: 420px;
            }
        }
    </style>
    <div class="wrap">
        <h1>VISUAL LINKS BUILDER (VLB)</h1>
        <div class="notice notice-warning" style="margin: 12px 0 0;">
            <p><strong>DO NOT TOUCH WESH 😎</strong></p>
        </div>
        <div style="margin-top: 24px; text-align: left;">
            <img
                src="https://www.ellenemasri.com/wp/wp-content/uploads/2026/06/traffic-cone.png"
                alt="Traffic cone"
                class="vlb-notice-cone"
            />
            <p><strong>Still working on it. Hee Hee !</strong></p>
        </div>
    </div>
    <?php
}

function ellene_wp_register_vlb_notice_intermediate_page() {
    add_submenu_page(
        null,
        'VLB - Development Notice',
        'VLB - Development Notice',
        'manage_options',
        'ellene_vlb_notice_page',
        'ellene_wp_render_vlb_notice_intermediate_page'
    );
}

add_action('admin_menu', 'ellene_wp_register_vlb_notice_intermediate_page', 60);

function ellene_wp_redirect_vlb_to_intermediate_notice_for_ellene_admin() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    $is_vlb_page = (strpos($page, 'mayami_visual_links_') === 0);

    if (!$is_vlb_page) {
        return;
    }

    $redirect = add_query_arg(array('page' => 'ellene_vlb_notice_page'), admin_url('admin.php'));

    wp_safe_redirect($redirect);
    exit;
}

add_action('admin_init', 'ellene_wp_redirect_vlb_to_intermediate_notice_for_ellene_admin', 20);

function ellene_wp_hide_brevo_menu_for_ellene_admin_fallback_css_js() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->user_login)) {
        return;
    }

    if (strtolower((string) $current_user->user_login) !== 'ellene-admin') {
        return;
    }
    ?>
    <style>
      #adminmenu a[href*="page=sib_page_home"],
      #adminmenu a[href*="mailin"],
      #adminmenu a[href*="brevo"],
      #adminmenu a[href*="sendinblue"] {
        display: none !important;
      }
      #adminmenu li.hidden-by-ellene-brevo-hide {
        display: none !important;
      }
    </style>
    <script>
    (function() {
      var items = document.querySelectorAll('#adminmenu li.menu-top');
      items.forEach(function(li) {
        var anchor = li.querySelector('a.menu-top');
        if (!anchor) return;
        var text = (anchor.textContent || '').toLowerCase();
        var href = (anchor.getAttribute('href') || '').toLowerCase();
        if (
          text.indexOf('brevo') !== -1 ||
          text.indexOf('sendinblue') !== -1 ||
          href.indexOf('sib_page_home') !== -1 ||
          href.indexOf('mailin') !== -1 ||
          href.indexOf('brevo') !== -1
        ) {
          li.classList.add('hidden-by-ellene-brevo-hide');
        }
      });
    })();
    </script>
    <?php
}

add_action('admin_head', 'ellene_wp_hide_brevo_menu_for_ellene_admin_fallback_css_js', 100);

function ellene_wp_client_login_redirect($redirect_to, $requested_redirect_to, $user) {
    if (is_wp_error($user) || empty($user->user_login)) {
        return $redirect_to;
    }

    if ($user->user_login === 'admin-my') {
        return $redirect_to;
    }

    return admin_url('admin.php?page=ellene-wp_landing_options');
}

add_filter('login_redirect', 'ellene_wp_client_login_redirect', 10, 3);

function ellene_wp_limit_admin_bar_for_client($wp_admin_bar) {
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

    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('new-post');
    $wp_admin_bar->remove_node('new-page');
    $wp_admin_bar->remove_node('new-user');
    $wp_admin_bar->remove_node('new-media');

    $wp_admin_bar->add_node(array(
        'id'    => 'ellene-wp-new-media',
        'title' => 'Ajouter un media',
        'href'  => admin_url('media-new.php'),
        'meta'  => array(
            'title' => 'Ajouter un media',
        ),
    ));

    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('customize');
}

add_action('admin_bar_menu', 'ellene_wp_limit_admin_bar_for_client', 999);

function ellene_wp_redirect_admin_bar_edit_to_landing($wp_admin_bar) {
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

    $edit_node->href = admin_url('admin.php?page=ellene-wp_landing_options');
    $wp_admin_bar->add_node($edit_node);
}

add_action('admin_bar_menu', 'ellene_wp_redirect_admin_bar_edit_to_landing', 1001);

function ellene_wp_output_active_theme_name_in_admin_menu() {
    if (!is_admin()) {
        return;
    }

    $theme = wp_get_theme();
    $theme_name = trim((string) $theme->get('Name'));

    if ($theme_name === '') {
        return;
    }

    $label = 'Thème: ' . $theme_name;
    ?>
    <style>
    #adminmenu::before {
        content: "<?php echo esc_js($label); ?>";
        display: block;
        padding: 8px 12px 7px;
        margin: 0 0 4px;
        font-size: 10px;
        line-height: 1.2;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(240, 246, 252, 0.62);
        border-bottom: 1px solid rgba(240, 246, 252, 0.14);
        pointer-events: none;
    }

    .folded #adminmenu::before {
        content: "<?php echo esc_js($theme_name); ?>";
        font-size: 9px;
        letter-spacing: 0;
        text-transform: none;
        text-align: center;
        padding: 8px 2px 7px;
    }
    </style>
    <?php
}

add_action('admin_head', 'ellene_wp_output_active_theme_name_in_admin_menu', 20);

function ellene_wp_customize_themes_admin_preview() {
    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'themes') {
        return;
    }

    $current_user = wp_get_current_user();
    $is_ellene_admin = ($current_user && strtolower((string) $current_user->user_login) === 'ellene-admin');
    ?>
    <style>
    .theme-wrap .theme-author,
    .theme-wrap .theme-author a {
        color: #8f4de8 !important;
    }

    .theme-wrap .theme-author a,
    .theme-wrap .theme-author a:hover,
    .theme-wrap .theme-author a:focus,
    .theme-wrap .theme-author a:active {
        text-decoration: none !important;
        box-shadow: none;
    }

    <?php if ($is_ellene_admin) : ?>
    .wrap .page-title-action[href*="theme-install.php"],
    .wrap a.page-title-action[href*="themes.php?page=theme-install"],
    .wrap .add-new-h2,
    .theme-browser .themes .add-new-theme {
        display: none !important;
    }

    .theme-wrap .theme-actions .button[href*="customize.php"],
    .theme-wrap .theme-actions .button[href*="site-editor.php"],
    .theme-wrap .theme-actions .button[href*="wp_global_styles"],
    .theme-wrap .theme-actions .button.load-customize,
    .theme-wrap .theme-actions .button,
    .theme-overlay .theme-actions .button,
    .theme-overlay .theme-actions .button-link {
        display: none !important;
    }
    <?php endif; ?>
    </style>
    <script>
    (function() {
        var clickHandlerBound = false;

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function autoLinkUrlsPreservingAnchors(html) {
            var parts = String(html || '').split(/(<a\b[^>]*>.*?<\/a>)/gi);

            return parts.map(function(part) {
                if (/^<a\b/i.test(part)) {
                    return part;
                }

                return part.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
            }).join('');
        }

        function renderRichDescriptionLine(line) {
            var html = escapeHtml(line || '');

            // Basic markdown-like formatting supported in style.css Description.
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
            html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

            // Rich tokens: [b][/b], [i][/i], [u][/u], [color=#ff6600][/color], [link=https://...][/link]
            html = html.replace(/\[b\]([\s\S]+?)\[\/b\]/gi, '<strong>$1</strong>');
            html = html.replace(/\[i\]([\s\S]+?)\[\/i\]/gi, '<em>$1</em>');
            html = html.replace(/\[u\]([\s\S]+?)\[\/u\]/gi, '<span style="text-decoration: underline;">$1</span>');
            html = html.replace(/\[link=(https?:\/\/[^\]\s]+)\]([\s\S]+?)\[\/link\]/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>');
            html = html.replace(/\[link\s+url=&quot;(https?:\/\/[^&]+)&quot;\]([\s\S]+?)\[\/link\]/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>');
            html = html.replace(/\[color=([^\]]+)\]([\s\S]+?)\[\/color\]/gi, function(match, rawColor, content) {
                var color = String(rawColor || '').trim();
                var isHex = /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i.test(color);
                var isNamed = /^[a-z]{3,20}$/i.test(color);
                var isRgb = /^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(\s*,\s*(0|0?\.\d+|1))?\s*\)$/i.test(color);

                if (!isHex && !isNamed && !isRgb) {
                    return content;
                }

                return '<span style="color: ' + color + ';">' + content + '</span>';
            });

            return autoLinkUrlsPreservingAnchors(html);
        }

        function applyThemeDescriptionRichFormatting() {
            var descriptionNodes = document.querySelectorAll('.theme-description, .theme-wrap .theme-info p:not(.theme-author), .theme-overlay .theme-info p:not(.theme-author)');
            var separatorPattern = /\s*\|\|\s*|\s*\|\s+\|\s*/;
            var enrichPattern = /\|\||\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)|https?:\/\/|\*\*[^*]+\*\*|\*[^*]+\*|`[^`]+`|\[(?:b|i|u|color|link)(?:=|\s|\])/i;

            descriptionNodes.forEach(function(node) {
                if (!node) {
                    return;
                }

                var rawText = (node.textContent || '').trim();
                if (!enrichPattern.test(rawText)) {
                    return;
                }

                var lines = rawText.split(separatorPattern).map(function(part) {
                    return part.trim();
                }).filter(function(part) {
                    return part.length > 0;
                });

                if (!lines.length) {
                    return;
                }

                node.innerHTML = lines.map(renderRichDescriptionLine).join('<br>');
                node.style.whiteSpace = 'normal';
                node.setAttribute('data-ellene-linebreak-ready', '1');
            });
        }

        function forceThemeAuthorLinksToBlank() {
            var authorLinks = document.querySelectorAll('.theme-wrap .theme-author a, .theme-overlay .theme-author a');

            authorLinks.forEach(function(link) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            });
        }

        function bindThemeAuthorClickHandler() {
            if (clickHandlerBound) {
                return;
            }

            document.addEventListener('click', function(event) {
                var link = event.target && event.target.closest('.theme-wrap .theme-author a, .theme-overlay .theme-author a');
                if (!link) {
                    return;
                }

                var href = link.getAttribute('href');
                if (!href) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                window.open(href, '_blank', 'noopener,noreferrer');
            }, true);

            clickHandlerBound = true;
        }

        forceThemeAuthorLinksToBlank();
        bindThemeAuthorClickHandler();
        applyThemeDescriptionRichFormatting();

        var observer = new MutationObserver(function() {
            forceThemeAuthorLinksToBlank();
            applyThemeDescriptionRichFormatting();
        });

        var observerTarget = document.body || document.documentElement;
        if (observerTarget && observerTarget.nodeType === 1) {
            observer.observe(observerTarget, { childList: true, subtree: true });
        }
    })();
    </script>
    <?php if ($is_ellene_admin) : ?>
    <script>
    (function() {
        var labelsToHide = ['personnaliser', 'compositions', 'polices'];

        function shouldHideButton(button) {
            if (!button) {
                return false;
            }

            var label = (button.textContent || '').toLowerCase().trim();
            return labelsToHide.indexOf(label) !== -1;
        }

        function hideThemeInfoButtons() {
            var buttons = document.querySelectorAll('.theme-actions .button, .theme-actions .button-link');
            buttons.forEach(function(button) {
                if (shouldHideButton(button)) {
                    button.style.display = 'none';
                }
            });

            var addThemeButtons = document.querySelectorAll('.page-title-action, .add-new-h2, .add-new-theme');
            addThemeButtons.forEach(function(button) {
                var label = (button.textContent || '').toLowerCase().trim();
                if (label === 'ajouter un thème' || label === 'add new theme') {
                    button.style.display = 'none';
                }
            });
        }

        hideThemeInfoButtons();

        var observer = new MutationObserver(function() {
            hideThemeInfoButtons();
        });

        var observerTarget = document.body || document.documentElement;
        if (observerTarget && observerTarget.nodeType === 1) {
            observer.observe(observerTarget, { childList: true, subtree: true });
        }
    })();
    </script>
    <?php endif; ?>
    <?php
}

add_action('admin_head', 'ellene_wp_customize_themes_admin_preview', 30);

function ellene_wp_force_by_label_in_theme_preview($translation, $text, $domain) {
    if (!is_admin()) {
        return $translation;
    }

    if ($text !== 'By %s') {
        return $translation;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'themes') {
        return $translation;
    }

    return '%s';
}

add_filter('gettext', 'ellene_wp_force_by_label_in_theme_preview', 20, 3);

function ellene_wp_customize_media_admin_labels($translation, $text, $domain) {
    if (!is_admin()) {
        return $translation;
    }

    $replacements_by_rendered_text = array(
        'Téléverser un média' => 'Uploader un média',
        'Téléverser des fichiers' => 'Uploader des fichiers',
        'Déposez vos fichiers pour les téléverser' => 'Dépose tes fichiers pour les uploader',
        'Ajouter un fichier média' => 'Uploader un média',
    );

    if (isset($replacements_by_rendered_text[$translation])) {
        return $replacements_by_rendered_text[$translation];
    }

    $replacements_by_source_text = array(
        'Upload New Media' => 'Uploader un média',
        'Upload Files' => 'Uploader des fichiers',
        'Drop files to upload' => 'Dépose tes fichiers pour les uploader',
        'Add New Media File' => 'Uploader un média',
    );

    if (isset($replacements_by_source_text[$text])) {
        return $replacements_by_source_text[$text];
    }

    return $translation;
}

add_filter('gettext', 'ellene_wp_customize_media_admin_labels', 25, 3);

function ellene_wp_wink_on_upload_media_title() {
    global $pagenow;

    $screen = get_current_screen();

    $is_media_new_page = ((string) $pagenow === 'media-new.php');
    $is_media_screen = ($screen && ($screen->id === 'media' || $screen->base === 'media'));

    if (!$is_media_new_page && !$is_media_screen) {
        return;
    }
    ?>
    <script>
    (function() {
        function applyWink() {
            var title = document.querySelector('.wrap h1');
            if (!title || title.getAttribute('data-ellene-winked') === '1') {
                return;
            }

            var label = (title.textContent || '').trim();
            if (!/^Uploader\s+un/i.test(label)) {
                return;
            }

            title.textContent = label.replace(/^Uploader\b/i, 'Uploader 😉');
            title.setAttribute('data-ellene-winked', '1');
        }

        applyWink();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyWink, { once: true });
        }

        var observer = new MutationObserver(function() {
            applyWink();
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>
    <?php
}

add_action('admin_footer', 'ellene_wp_wink_on_upload_media_title', 30);

