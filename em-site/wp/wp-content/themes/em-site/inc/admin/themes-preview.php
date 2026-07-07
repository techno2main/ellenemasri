<?php
/**
 * Page Apparence → Thèmes : screenshot EM-WP + description riche (style.css).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche le copyright auteur sans préfixe « Par » / « By ».
 */
function em_wp_force_theme_author_label_in_preview(string $translation, string $text, string $domain): string
{
    if (!is_admin() || $text !== 'By %s') {
        return $translation;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'themes') {
        return $translation;
    }

    return '%s';
}
add_filter('gettext', 'em_wp_force_theme_author_label_in_preview', 20, 3);

/**
 * CSS + JS page themes.php (screenshot contain, description || [b] [color] [link]).
 */
function em_wp_customize_themes_admin_preview(): void
{
    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->id !== 'themes') {
        return;
    }

    $theme_slug = sanitize_key(get_stylesheet());
    $hide_theme_actions = function_exists('em_wp_admin_should_limit_client_admin')
        && em_wp_admin_should_limit_client_admin();
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

    body.themes-php .theme-wrap .theme-screenshot,
    body.themes-php .theme.<?php echo esc_attr($theme_slug); ?> .theme-screenshot,
    body.themes-php .theme-overlay .theme-screenshot {
        background-size: contain !important;
        background-position: center center !important;
        background-repeat: no-repeat !important;
        background-color: #ffffff;
    }

    body.themes-php .theme-wrap .screenshot,
    body.themes-php .theme.<?php echo esc_attr($theme_slug); ?> .screenshot {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
    }

    body.themes-php .theme-wrap .screenshot img,
    body.themes-php .theme.<?php echo esc_attr($theme_slug); ?> .screenshot img {
        position: relative !important;
        inset: auto !important;
        width: auto !important;
        max-width: 100%;
        height: auto !important;
        max-height: 100%;
        object-fit: contain !important;
        object-position: center center !important;
        margin: 0 auto;
    }
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

            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
            html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
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
            html = html.replace(/\[b\]([\s\S]+?)\[\/b\]/gi, '<strong>$1</strong>');
            html = html.replace(/\[i\]([\s\S]+?)\[\/i\]/gi, '<em>$1</em>');
            html = html.replace(/\[u\]([\s\S]+?)\[\/u\]/gi, '<span style="text-decoration: underline;">$1</span>');
            html = html.replace(/\[link=(https?:\/\/[^\]\s]+)\]([\s\S]+?)\[\/link\]/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>');
            html = html.replace(/\[link\s+url=&quot;(https?:\/\/[^&]+)&quot;\]([\s\S]+?)\[\/link\]/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$2</a>');

            return autoLinkUrlsPreservingAnchors(html);
        }

        function applyThemeDescriptionRichFormatting() {
            var descriptionNodes = document.querySelectorAll('.theme-description, .theme-wrap .theme-info p:not(.theme-author), .theme-overlay .theme-info p:not(.theme-author)');
            var separatorPattern = /\s*\|\|\s*|\s*\|\s+\|\s*/;
            var enrichPattern = /\|\||\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)|https?:\/\/|\*\*[^*]+\*\*|\*[^*]+\*|`[^`]+`|\[(?:b|i|u|color|link)(?:=|\s|\])/i;

            descriptionNodes.forEach(function(node) {
                if (!node || node.getAttribute('data-em-wp-linebreak-ready') === '1') {
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
                node.setAttribute('data-em-wp-linebreak-ready', '1');
            });
        }

        function forceThemeAuthorLinksToBlank() {
            document.querySelectorAll('.theme-wrap .theme-author a, .theme-overlay .theme-author a').forEach(function(link) {
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
    <?php if ($hide_theme_actions) { ?>
    <script>
    (function() {
        var labelsToHide = [
            'personnaliser', 'customize',
            'compositions', 'patterns',
            'polices', 'fonts',
            'menus'
        ];

        function shouldHideButton(button) {
            if (!button) {
                return false;
            }

            var label = (button.textContent || '').toLowerCase().trim();
            return labelsToHide.indexOf(label) !== -1;
        }

        function hideThemeInfoButtons() {
            document.querySelectorAll('.theme-actions .button, .theme-actions .button-link, .theme-overlay .theme-actions .button, .theme-overlay .theme-actions .button-link').forEach(function(button) {
                if (shouldHideButton(button)) {
                    button.style.display = 'none';
                }
            });
        }

        hideThemeInfoButtons();

        var observer = new MutationObserver(hideThemeInfoButtons);
        var target = document.body || document.documentElement;
        if (target && target.nodeType === 1) {
            observer.observe(target, { childList: true, subtree: true });
        }
    })();
    </script>
    <?php } ?>
    <?php
}
add_action('admin_head', 'em_wp_customize_themes_admin_preview', 30);
