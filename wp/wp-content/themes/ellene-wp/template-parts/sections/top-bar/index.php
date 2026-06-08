<?php

/**

 * Template part - Top-Bar

 *

 * @package ElleneWp

 */



$top_bar_items = cmb2_get_option('ellene-wp_landing_options', 'top_bar_items');

$top_bar_logo_png = trim((string) cmb2_get_option('ellene-wp_landing_options', 'top_bar_logo_png'));

$hide_top_bar_visual = !empty(cmb2_get_option('ellene-wp_landing_options', 'top_bar_logo_hidden'));



$show_platform_icons = true;

$open_platform_icons_in_new_tab = false;



if (!is_array($top_bar_items)) {

    $top_bar_items = array();

}

$top_bar_items = array_values(array_filter($top_bar_items, 'is_array'));

$sanitize_top_bar_item = static function ($item) {

    if (!is_array($item)) {

        return null;

    }

    if (!empty($item['is_hidden'])) {

        return null;

    }

    $label = trim((string) ($item['label'] ?? ''));

    $href = trim((string) ($item['href'] ?? ''));

    $normalized = strtolower(trim(remove_accents($label)));

    if ($normalized === 'icone plateformes' || $normalized === 'icones stream' || $normalized === 'afficher les icones') {

        return null;

    }

    if ($label === '' || $href === '') {

        return null;

    }

    $item['label'] = $label;

    $item['href'] = $href;

    return $item;

};

// Slots fixes TOP-BAR (0: Titre Single, 1: CTA central, 2: Baseline)
$desktop_right_item = $sanitize_top_bar_item($top_bar_items[0] ?? null);

$desktop_center_item = $sanitize_top_bar_item($top_bar_items[1] ?? null);

$desktop_left_item = $sanitize_top_bar_item($top_bar_items[2] ?? null);



$stream_platforms = cmb2_get_option('ellene-wp_landing_options', 'stream_platforms');

if (!is_array($stream_platforms)) {

    $stream_platforms = array();

}



$platform_icon_map = array(

    'spotify' => 'fa-spotify',

    'apple-music' => 'fa-apple',

    'youtube-music' => 'fa-youtube',

    'deezer' => 'fa-deezer',

    'amazon-music' => 'fa-amazon',

    'soundcloud' => 'fa-soundcloud',

);



$top_bar_platform_links = array();

if ($show_platform_icons) {

    foreach ($stream_platforms as $platform) {

        $is_active = !empty($platform['is_active']);

        $label = isset($platform['label']) ? trim((string) $platform['label']) : '';

        $href = isset($platform['href']) ? trim((string) $platform['href']) : '';



        if (!$is_active || $label === '' || $href === '') {

            continue;

        }



        $key = sanitize_title($label);

        if (!isset($platform_icon_map[$key])) {

            continue;

        }



        $top_bar_platform_links[] = array(

            'href' => $open_platform_icons_in_new_tab ? $href : '#stream',

            'platform' => $key,

            'icon' => $platform_icon_map[$key],

            'label' => $label,

            'external' => $open_platform_icons_in_new_tab,

        );

    }

}



$mobile_title = is_array($desktop_left_item) && !empty($desktop_left_item['label']) ? (string) $desktop_left_item['label'] : '';

$mobile_stream_link = $desktop_center_item;

?>

<style>

    #top-bar {

        border-top: 2px solid var(--ink);

        border-bottom: 2px solid var(--ink);

        background: var(--ink);

    }



    #top-bar-desktop.top-bar-track {

        animation: none !important;

        transform: none !important;

        max-width: 80rem;

        margin: 0 auto;

        padding: 0 20px;

    }



    #top-bar .top-bar-scroller {

        display: block;

        width: 100%;

        animation: none !important;

        transform: none !important;

    }



    #top-bar .top-bar-line-desktop {

        display: grid;

        grid-template-columns: 1fr auto 1fr;

        align-items: center;

        gap: 14px;

        width: 100%;

        padding-bottom: 10px;

    }



    #top-bar .top-bar-col-left {

        justify-self: start;

    }



    #top-bar .top-bar-col-center {

        justify-self: center;

    }



    #top-bar .top-bar-col-right {

        justify-self: end;

        display: inline-flex;

        align-items: center;

    }



    #top-bar .top-bar-link {

        color: var(--cream);

        transition: color .15s ease;

    }



    #top-bar .top-bar-link:hover {

        color: var(--aqua);

    }



    #top-bar .top-bar-platform-icons {

        display: inline-flex;

        align-items: center;

        gap: 14px;

    }



    #top-bar .top-bar-platform-link {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border: 0;

        background: transparent;

        padding: 0;

        color: var(--cream);

        font-size: 18px;

        line-height: 1;

        transition: color .15s ease, transform .15s ease;

        cursor: pointer;

    }



    #top-bar .top-bar-platform-link:hover {

        color: var(--aqua);

        transform: translateY(-1px);

    }



    #top-bar .top-bar-logo-row {

        display: grid;

        grid-template-columns: auto 1fr;

        align-items: center;

        gap: 16px;

        width: 100%;

        max-width: 80rem;

        margin: 0 auto;

        padding: 0 20px 16px 0;

    }



    #top-bar .top-bar-logo-row.no-visual {

        grid-template-columns: 1fr;

    }



    #top-bar .top-bar-logo-mark,

    #top-bar .top-bar-logo-year {

        flex: 0 0 auto;

        color: oklch(0.92 0.18 95);

        line-height: 1;

        opacity: 0.9;

        white-space: nowrap;

    }



    #top-bar .top-bar-logo-image {

        display: block;

        width: auto;

        height: auto;

        max-width: 220px;

        justify-self: start;

    }



    #top-bar .top-bar-logo-copy {

        display: inline-flex;

        align-items: center;

        justify-self: end;

        gap: 10px;

        text-align: right;

    }



    #top-bar .top-bar-logo-mark {

        font-family: "Brush Script MT", "Segoe Script", "Snell Roundhand", cursive;

        font-size: 20px;

        letter-spacing: 0.02em;

    }



    #top-bar .top-bar-logo-year {

        font-family: var(--font-poster);

        font-size: 14px;

        letter-spacing: 0.22em;

        text-align: right;

    }



    #top-bar-mobile {

        display: none;

    }



    @media (min-width: 768px) {

        #top-bar-desktop.top-bar-track {

            padding: 0 32px;

        }



        #top-bar .top-bar-platform-icons {

            gap: 26px;

        }



        #top-bar .top-bar-platform-link {

            font-size: 26px;

        }



        #top-bar .top-bar-logo-row {

            padding: 0 32px 18px 0;

        }



        #top-bar .top-bar-logo-copy {

            gap: 12px;

        }



        #top-bar .top-bar-logo-mark {

            font-size: 20px;

        }



        #top-bar .top-bar-logo-year {

            font-size: 14px;

        }



        #top-bar .top-bar-logo-image {

            max-width: 280px;

        }

    }



    @media (max-width: 767px) {

        #top-bar-desktop {

            display: none;

        }



        #top-bar-mobile {

            display: flex;

            flex-direction: column;

            align-items: stretch;

            gap: 8px;

            padding: 0 12px;

            width: 100%;

        }



        #top-bar .top-bar-logo-row {

            padding: 0 12px 12px 0;

        }



        #top-bar .top-bar-logo-copy {

            gap: 8px;

        }



        #top-bar .top-bar-logo-mark {

            font-size: 13px;

            letter-spacing: 0.18em;

        }



        #top-bar .top-bar-logo-year {

            font-size: 11px;

            letter-spacing: 0.18em;

        }



        #top-bar .top-bar-logo-image {

            max-width: 160px;

        }



        #top-bar .top-bar-mobile-row {

            display: flex;

            align-items: center;

            width: 100%;

        }



        #top-bar .top-bar-mobile-row-top {

            justify-content: flex-start;

        }



        #top-bar .top-bar-mobile-row-bottom {

            display: none;

        }



        #top-bar .top-bar-mobile-title {

            color: var(--cream);

            font-size: 14px;

            line-height: 1;

            letter-spacing: 0.08em;

            transition: color .15s ease;

            white-space: nowrap;

        }



        #top-bar .top-bar-mobile-title:hover {

            color: var(--aqua);

        }



        #top-bar .top-bar-mobile-row-top .top-bar-platform-icons {

            margin-left: auto;

            gap: 8px;

            flex-shrink: 0;

        }



        #top-bar .top-bar-mobile-row-top .top-bar-platform-link {

            font-size: 18px;

        }

    }

</style>

<div id="top-bar" class="relative z-20 overflow-hidden py-3">

    <div class="top-bar-logo-row<?php echo $hide_top_bar_visual ? ' no-visual' : ''; ?>">

        <?php if (!$hide_top_bar_visual && $top_bar_logo_png !== ''): ?>

            <img src="<?php echo esc_url($top_bar_logo_png); ?>" alt="" class="top-bar-logo-image" loading="lazy" decoding="async" />

        <?php endif; ?>

        <div class="top-bar-logo-copy">

            <?php

                $right_href = is_array($desktop_right_item) && !empty($desktop_right_item['href']) ? (string) $desktop_right_item['href'] : '';

                $right_label = is_array($desktop_right_item) && !empty($desktop_right_item['label']) ? (string) $desktop_right_item['label'] : '';

                $right_is_external = false;

                $right_target = $right_is_external ? '_blank' : '_self';

                $right_rel = $right_is_external ? 'noreferrer' : '';

            ?>

            <?php if ($right_href !== '' && $right_label !== ''): ?>

                <a href="<?php echo esc_url($right_href); ?>" <?php if ($right_is_external): ?>target="<?php echo esc_attr($right_target); ?>" rel="<?php echo esc_attr($right_rel); ?>"<?php endif; ?> class="top-bar-logo-mark top-bar-link">

                    <?php echo esc_html($right_label); ?>

                </a>

            <?php endif; ?>

        </div>

    </div>

    <div id="top-bar-mobile">

        <div class="top-bar-mobile-row top-bar-mobile-row-top">

            <?php if ($mobile_title !== ''): ?>

                <a href="#page-top" class="top-bar-mobile-title font-poster uppercase"><?php echo esc_html($mobile_title); ?></a>

            <?php endif; ?>

            <?php if (!empty($top_bar_platform_links)): ?>

                <span class="top-bar-platform-icons">

                    <?php foreach ($top_bar_platform_links as $platform): ?>

                        <button type="button" data-open-platform="<?php echo esc_attr($platform['platform']); ?>" aria-label="<?php echo esc_attr($platform['label']); ?>" title="<?php echo esc_attr($platform['label']); ?>" class="top-bar-platform-link">

                            <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>

                        </button>

                    <?php endforeach; ?>

                </span>

            <?php endif; ?>

        </div>



        <div class="top-bar-mobile-row top-bar-mobile-row-bottom">

            <?php

                $mobile_stream_href = is_array($mobile_stream_link) && !empty($mobile_stream_link['href']) ? (string) $mobile_stream_link['href'] : '';

                $mobile_stream_label = is_array($mobile_stream_link) && !empty($mobile_stream_link['label']) ? (string) $mobile_stream_link['label'] : '';

                $mobile_stream_external = false;

                $mobile_stream_target = $mobile_stream_external ? '_blank' : '_self';

                $mobile_stream_rel = $mobile_stream_external ? 'noreferrer' : '';

            ?>

            <?php if ($mobile_stream_href !== '' && $mobile_stream_label !== ''): ?>

                <a href="<?php echo esc_url($mobile_stream_href); ?>" <?php if ($mobile_stream_external): ?>target="<?php echo esc_attr($mobile_stream_target); ?>" rel="<?php echo esc_attr($mobile_stream_rel); ?>"<?php endif; ?> class="top-bar-mobile-stream-link font-poster uppercase"><?php echo esc_html($mobile_stream_label); ?></a>

            <?php endif; ?>

        </div>

    </div>



    <div id="top-bar-desktop" class="top-bar-track whitespace-nowrap">

        <div class="top-bar-scroller">

            <div class="top-bar-line-desktop font-poster text-lg uppercase tracking-widest text-cream">

                <div class="top-bar-col-left">

                    <?php

                        $left_href = is_array($desktop_left_item) && !empty($desktop_left_item['href']) ? (string) $desktop_left_item['href'] : '';

                        $left_label = is_array($desktop_left_item) && !empty($desktop_left_item['label']) ? (string) $desktop_left_item['label'] : '';

                        $left_is_external = false;

                        $left_target = $left_is_external ? '_blank' : '_self';

                        $left_rel = $left_is_external ? 'noreferrer' : '';

                    ?>

                    <?php if ($left_href !== '' && $left_label !== ''): ?>

                        <a href="<?php echo esc_url($left_href); ?>" <?php if ($left_is_external): ?>target="<?php echo esc_attr($left_target); ?>" rel="<?php echo esc_attr($left_rel); ?>"<?php endif; ?> class="top-bar-link">

                            <?php echo esc_html($left_label); ?>

                        </a>

                    <?php endif; ?>

                </div>



                <div class="top-bar-col-center">

                    <?php

                        $center_href = is_array($desktop_center_item) && !empty($desktop_center_item['href']) ? (string) $desktop_center_item['href'] : '';

                        $center_label = is_array($desktop_center_item) && !empty($desktop_center_item['label']) ? (string) $desktop_center_item['label'] : '';

                        $center_is_external = false;

                        $center_target = $center_is_external ? '_blank' : '_self';

                        $center_rel = $center_is_external ? 'noreferrer' : '';

                    ?>

                    <?php if ($center_href !== '' && $center_label !== ''): ?>

                        <a href="<?php echo esc_url($center_href); ?>" <?php if ($center_is_external): ?>target="<?php echo esc_attr($center_target); ?>" rel="<?php echo esc_attr($center_rel); ?>"<?php endif; ?> class="top-bar-link">

                            <?php echo esc_html($center_label); ?>

                        </a>

                    <?php endif; ?>

                </div>



                <div class="top-bar-col-right">

                    <?php if (!empty($top_bar_platform_links)): ?>

                        <span class="top-bar-platform-icons">

                            <?php foreach ($top_bar_platform_links as $platform): ?>

                                <button type="button" data-open-platform="<?php echo esc_attr($platform['platform']); ?>" aria-label="<?php echo esc_attr($platform['label']); ?>" title="<?php echo esc_attr($platform['label']); ?>" class="top-bar-platform-link">

                                    <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>

                                </button>

                            <?php endforeach; ?>

                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

