<?php
if (!function_exists('em_site_overview_display_text')) {
    /**
     * Normalise un texte pour l'affichage admin (anti-mojibake robuste).
     */
    function em_site_overview_display_text(string $text): string
    {
        $current = $text;
        $pattern = '/[\x{00C2}\x{00C3}\x{00E2}\x{FFFD}]/u';

        for ($i = 0; $i < 3; $i++) {
            if (preg_match($pattern, $current) !== 1) {
                break;
            }

            $best = $current;
            $best_score = preg_match_all($pattern, $current);
            $candidates = [];

            if (function_exists('em_site_fix_mojibake_string')) {
                $candidates[] = em_site_fix_mojibake_string($current);
            }

            if (function_exists('mb_convert_encoding')) {
                $candidates[] = @mb_convert_encoding($current, 'UTF-8', 'Windows-1252');
                $candidates[] = @mb_convert_encoding($current, 'UTF-8', 'ISO-8859-1');
            }

            if (function_exists('iconv')) {
                $iconv_candidate = @iconv('Windows-1252', 'UTF-8//IGNORE', $current);
                if (is_string($iconv_candidate)) {
                    $candidates[] = $iconv_candidate;
                }
            }

            foreach ($candidates as $candidate) {
                if (!is_string($candidate) || $candidate === '') {
                    continue;
                }

                $score = preg_match_all($pattern, $candidate);
                if ($score !== false && $best_score !== false && $score < $best_score) {
                    $best = $candidate;
                    $best_score = $score;
                }
            }

            if ($best === $current) {
                break;
            }

            $current = $best;
        }

        return wp_check_invalid_utf8($current, true);
    }
}

if (!function_exists('em_site_overview_is_garbled_text')) {
    /**
     * D?tecte un libell? probablement corrompu (mojibake).
     */
    function em_site_overview_is_garbled_text(string $text): bool
    {
        return preg_match('/?|?|?|\x{FFFD}/u', $text) === 1;
    }
}

if (!function_exists('em_site_overview_item_label_fallback')) {
    /**
     * Fallback lisible depuis le slug technique d'item.
     */
    function em_site_overview_item_label_fallback(string $type_slug, string $item_slug): string
    {
        $slug = sanitize_key($item_slug);
        $prefix = function_exists('em_site_item_slug_prefix') ? sanitize_key(em_site_item_slug_prefix($type_slug)) : '';

        if ($prefix !== '' && strpos($slug, $prefix . '-') === 0) {
            $slug = substr($slug, strlen($prefix) + 1);
        }

        if ($slug === '' || $slug === $prefix) {
            $slug = 'default';
        }

        $label = strtoupper(str_replace(['-', '_'], ' ', $slug));
        return trim(preg_replace('/\s+/', ' ', $label) ?? $label);
    }
}

function em_site_overview_render_focus_back(string $active_slug, array $type): void
{
    $raw_label = function_exists('em_site_overview_type_label')
        ? em_site_overview_type_label((string) $active_slug, $type)
        : (string) ($type['label_plural'] ?? $type['label'] ?? $active_slug);
    $label = em_site_overview_display_text($raw_label);
    ?>
    <div class="em-site-overview__focus-bar" data-overview-focusbar>
        <a href="<?php echo esc_url(em_site_overview_summary_url()); ?>" class="em-site-overview__focus-back" data-overview-back>
            <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
            <span><?php esc_html_e('Retour au sommaire', 'em-site'); ?></span>
        </a>
        <div class="em-site-overview__focus-titlewrap">
            <span class="em-site-overview__focus-kicker"><?php esc_html_e('?dition cibl?e', 'em-site'); ?></span>
            <strong class="em-site-overview__focus-title"><?php echo esc_html($label); ?></strong>
        </div>
    </div>
    <?php
}

/**
 * Sommaire compact des rubriques.
 *
 * @param array<string, array<string, mixed>> $types
 */
function em_site_overview_render_directory(array $types, string $active_slug): void
{
    ?>
    <section class="em-site-overview__summary" data-overview-summary>
        <div class="em-site-overview__summary-head">
            <div>
                <p class="em-site-overview__eyebrow"><?php esc_html_e('Sommaire des rubriques', 'em-site'); ?></p>
            </div>
        </div>

        <div class="em-site-overview__directory" role="list">
            <?php
            $slots_total = count($types);
            $slot_index = 0;
            ?>
            <?php foreach ($types as $slug => $type) : ?>
                <?php
                $slug = (string) $slug;
                $items = em_site_get_items($slug);
                $count = count($items);
                $raw_label = function_exists('em_site_overview_type_label')
                    ? em_site_overview_type_label((string) $slug, $type)
                    : (string) ($type['label_plural'] ?? $type['label'] ?? $slug);
                $label = em_site_overview_display_text($raw_label);
                $icon = function_exists('em_site_overview_type_icon')
                    ? em_site_overview_type_icon((string) $slug, $type)
                    : (string) ($type['icon'] ?? 'dashicons-screenoptions');
                $is_active = ($active_slug === $slug);
                ?>
                <a
                    href="<?php echo esc_url(add_query_arg('type', $slug, em_site_overview_summary_url())); ?>"
                    class="em-site-overview__directory-link<?php echo $is_active ? ' is-active' : ''; ?>"
                    data-focus-slug="<?php echo esc_attr($slug); ?>"
                    data-item-count="<?php echo esc_attr((string) $count); ?>"
                    role="listitem"
                    aria-current="<?php echo $is_active ? 'true' : 'false'; ?>"
                >
                    <span class="em-site-overview__directory-content">
                        <span class="em-site-overview__directory-topline">
                            <span class="em-site-overview__directory-heading">
                                <span class="em-site-overview__directory-icon dashicons <?php echo esc_attr($icon); ?>" aria-hidden="true"></span>
                                <strong class="em-site-overview__directory-label"><?php echo esc_html($label); ?></strong>
                            </span>
                        </span>
                        <span class="em-site-overview__directory-meta">
                            <?php if ($items === []) : ?>
                                <span class="em-site-overview__directory-pill is-empty"><?php esc_html_e('Aucun item', 'em-site'); ?></span>
                            <?php else : ?>
                                <?php foreach ($items as $item_slug => $item_label) : ?>
                                    <?php
                                    unset($item_label);
                                    // Vue sommaire: on privil?gie un libell? stable d?riv? du slug
                                    // pour neutraliser d?finitivement les labels corrompus.
                                    $display_label = em_site_overview_item_label_fallback($slug, (string) $item_slug);
                                    ?>
                                    <span class="em-site-overview__directory-pill"><?php echo esc_html($display_label); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="em-site-overview__directory-rail" aria-hidden="true">
                        <span class="em-site-overview__directory-arrow dashicons dashicons-arrow-right-alt2"></span>
                        <span class="em-site-overview__directory-map">
                            <?php for ($i = 0; $i < $slots_total; $i++) : ?>
                                <span class="em-site-overview__directory-map-slot<?php echo $i === $slot_index ? ' is-current' : ''; ?>"></span>
                            <?php endfor; ?>
                        </span>
                    </span>
                </a>
                <?php $slot_index++; ?>
            <?php endforeach; ?>
            <button
                type="button"
                class="em-site-overview__directory-link em-site-overview__directory-link--create"
                data-overview-create-toggle
                aria-expanded="false"
                role="listitem"
            >
                <span class="em-site-overview__directory-content">
                    <span class="em-site-overview__directory-topline">
                        <span class="em-site-overview__directory-heading">
                            <span class="em-site-overview__directory-icon dashicons dashicons-welcome-add-page" aria-hidden="true"></span>
                            <strong class="em-site-overview__directory-label em-site-overview__directory-label--create"><?php esc_html_e('Ajouter', 'em-site'); ?></strong>
                        </span>
                    </span>
                    <span class="em-site-overview__directory-meta">
                        <span class="em-site-overview__directory-pill em-site-overview__directory-pill--createhint"><?php esc_html_e('+ NOUVELLE RUBRIQUE', 'em-site'); ?></span>
                    </span>
                </span>
                <span class="em-site-overview__directory-rail" aria-hidden="true">
                    <span class="em-site-overview__directory-arrow dashicons dashicons-plus-alt2"></span>
                </span>
            </button>
        </div>

        <?php em_site_overview_render_create_type(); ?>
    </section>
    <?php
}

/**
 * Rendu de la page.
 */
