<?php
/**
 * Menu et dashboard admin em-site (Lot B).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_admin_is_dashboard_screen(): bool
{
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return sanitize_key((string) ($_GET['page'] ?? '')) === em_site_admin_dashboard_page_slug();
}

function em_site_admin_dashboard_page_slug(): string
{
	return 'em-dashboard';
}

function em_site_admin_dashboard_admin_url(): string
{
	return admin_url('admin.php?page=' . em_site_admin_dashboard_page_slug());
}

function em_site_admin_register_dashboard_page(): void
{
	add_menu_page(
		__('Dashboard EM-SITE', 'em-wp'),
		__('Dashboard', 'em-wp'),
		'manage_options',
		em_site_admin_dashboard_page_slug(),
		'em_site_admin_render_dashboard_page',
		'dashicons-dashboard',
		3
	);
}
add_action('admin_menu', 'em_site_admin_register_dashboard_page');

function em_site_admin_remove_dashboard_duplicate_submenu(): void
{
	remove_submenu_page(em_site_admin_dashboard_page_slug(), em_site_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_site_admin_remove_dashboard_duplicate_submenu', 999);

function em_site_admin_hide_custom_dashboard_menu_entry(): void
{
	remove_menu_page(em_site_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_site_admin_hide_custom_dashboard_menu_entry', 1000003);

function em_site_admin_point_dashboard_to_home(): void
{
	if (!current_user_can('manage_options')) {
		return;
	}

	global $menu, $submenu;

	remove_submenu_page('index.php', 'index.php');
	remove_submenu_page('index.php', 'update-core.php');
	unset($submenu['index.php']);

	foreach ($menu as $position => $item) {
		if (!is_array($item)) {
			continue;
		}

		$slug = sanitize_key((string) ($item[2] ?? ''));
		if ($slug !== 'index.php' && $slug !== em_site_admin_dashboard_page_slug()) {
			continue;
		}

		$menu[$position][0] = 'DASHBOARD';
		$menu[$position][3] = 'DASHBOARD';
		$menu[$position][2] = 'index.php';
		$menu[$position][4] = trim(((string) ($item[4] ?? 'menu-top')) . ' em-wp-menu-dashboard-entry');
		$menu[$position][6] = 'dashicons-dashboard';
	}
}
add_action('admin_menu', 'em_site_admin_point_dashboard_to_home', 1000002);

function em_site_admin_highlight_dashboard_menu($parent_file)
{
	if (em_site_admin_is_dashboard_screen()) {
		return 'index.php';
	}

	return $parent_file;
}
add_filter('parent_file', 'em_site_admin_highlight_dashboard_menu');

function em_site_admin_highlight_dashboard_submenu($submenu_file)
{
	if (em_site_admin_is_dashboard_screen()) {
		return '';
	}

	return $submenu_file;
}
add_filter('submenu_file', 'em_site_admin_highlight_dashboard_submenu');

function em_site_admin_dashboard_body_class($classes)
{
	if (!em_site_admin_is_dashboard_screen()) {
		return $classes;
	}

	return $classes . ' em-wp-admin-dashboard-screen em-site-admin-screen';
}
add_filter('admin_body_class', 'em_site_admin_dashboard_body_class');

function em_site_admin_dashboard_tabs(): array
{
	return [
		[
			'label' => __('MES RUBRIQUES', 'em-wp'),
			'url' => admin_url('admin.php?page=em-rubriques-overview'),
		],
		[
			'label' => __('MES TEMPLATES', 'em-wp'),
			'url' => admin_url('admin.php?page=em-template'),
		],
		[
			'label' => __('MEDIAS', 'em-wp'),
			'url' => admin_url('upload.php'),
		],
		[
			'label' => __('SETTINGS', 'em-wp'),
			'url' => admin_url('options-general.php'),
		],
	];
}

function em_site_admin_enqueue_dashboard_assets(): void
{
	if (!current_user_can('manage_options')) {
		return;
	}

	wp_enqueue_style(
		'em-site-admin-chrome',
		get_template_directory_uri() . '/assets/admin/css/core/admin-chrome.css',
		[],
		file_exists(get_template_directory() . '/assets/admin/css/core/admin-chrome.css')
			? (string) filemtime(get_template_directory() . '/assets/admin/css/core/admin-chrome.css')
			: (string) wp_get_theme()->get('Version')
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
	if ($page_slug !== em_site_admin_dashboard_page_slug()) {
		return;
	}

	wp_enqueue_style(
		'font-awesome-6',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
		[],
		'6.5.1'
	);

	wp_enqueue_style(
		'em-site-admin-dashboard',
		get_template_directory_uri() . '/assets/admin/css/dashboard.css',
		['em-site-admin-chrome'],
		file_exists(get_template_directory() . '/assets/admin/css/dashboard.css')
			? (string) filemtime(get_template_directory() . '/assets/admin/css/dashboard.css')
			: (string) wp_get_theme()->get('Version')
	);

	wp_enqueue_style(
		'em-site-admin-hub-cards',
		get_template_directory_uri() . '/assets/admin/shared/css/hub-cards.css',
		['em-site-admin-dashboard'],
		file_exists(get_template_directory() . '/assets/admin/shared/css/hub-cards.css')
			? (string) filemtime(get_template_directory() . '/assets/admin/shared/css/hub-cards.css')
			: (string) wp_get_theme()->get('Version')
	);

	wp_enqueue_style(
		'em-site-admin-live-badge',
		get_template_directory_uri() . '/assets/admin/shared/css/live-badge.css',
		['em-site-admin-hub-cards'],
		file_exists(get_template_directory() . '/assets/admin/shared/css/live-badge.css')
			? (string) filemtime(get_template_directory() . '/assets/admin/shared/css/live-badge.css')
			: (string) wp_get_theme()->get('Version')
	);
}
add_action('admin_enqueue_scripts', 'em_site_admin_enqueue_dashboard_assets');

function em_site_admin_mount_sidebar_menu_chrome_scripts(): void
{
	if (!current_user_can('manage_options')) {
		return;
	}
	?>
	<script id="em-site-sidebar-menu-chrome">
		(function () {
			function getDashboardItem() {
				return document.getElementById('menu-dashboard')
					|| document.querySelector('#adminmenu li.em-wp-menu-dashboard-entry');
			}

			function mountDashboardArrow() {
				var item = getDashboardItem();
				if (!item || item.querySelector('.em-wp-dashboard-menu-arrow')) {
					return;
				}

				var link = item.querySelector('a.menu-top');
				var arrow = document.createElement('span');
				arrow.className = 'em-wp-dashboard-menu-arrow';
				arrow.setAttribute('aria-hidden', 'true');

				if (link) {
					link.insertAdjacentElement('afterend', arrow);
					return;
				}

				item.appendChild(arrow);
			}

			mountDashboardArrow();
			document.addEventListener('DOMContentLoaded', mountDashboardArrow);
			window.addEventListener('load', mountDashboardArrow);
		})();
	</script>
	<?php
}
add_action('admin_footer', 'em_site_admin_mount_sidebar_menu_chrome_scripts', 5);

function em_site_admin_render_dashboard_page(): void
{
	if (!current_user_can('manage_options')) {
		return;
	}

	$tabs = em_site_admin_dashboard_tabs();
	$active_template = function_exists('em_site_admin_active_template_label')
		? em_site_admin_active_template_label()
		: 'MAYAMI';
	?>
	<div class="wrap em-wp-admin-module em-wp-hub-sommaire em-site-dashboard">
		<div class="em-wp-hub__greeting">
			<span class="dashicons dashicons-admin-home em-wp-hub__greeting-icon" aria-hidden="true"></span>
			<div class="em-wp-hub__greeting-text">
				<h1><?php esc_html_e('Ellen Masri', 'em-wp'); ?></h1>
				<p class="em-site-dashboard__sub"><?php esc_html_e('MON DASHBOARD', 'em-wp'); ?></p>
			</div>
		</div>

		<div class="em-site-dashboard__tabs" role="navigation" aria-label="Dashboard navigation">
			<a class="em-site-dashboard__tab em-site-dashboard__tab--list" href="<?php echo esc_url(em_site_admin_dashboard_admin_url()); ?>" aria-label="<?php esc_attr_e('Liste', 'em-wp'); ?>"><i class="fa-solid fa-list-ol" aria-hidden="true"></i></a>
			<?php foreach ($tabs as $tab) : ?>
				<a class="em-site-dashboard__tab" href="<?php echo esc_url((string) $tab['url']); ?>"><?php echo esc_html((string) $tab['label']); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="em-site-dashboard__grid">
			<section class="em-site-dashboard__card">
				<header class="em-site-dashboard__card-head"><h2><?php esc_html_e('MES RUBRIQUES', 'em-wp'); ?></h2><a class="em-site-dashboard__gear" href="<?php echo esc_url(admin_url('admin.php?page=em-rubriques-overview')); ?>" aria-label="<?php esc_attr_e('Gérer mes rubriques', 'em-wp'); ?>"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span></a></header>
				<p><?php esc_html_e('Sections réutilisables qui composent tes templates.', 'em-wp'); ?></p>
				<a class="em-site-dashboard__primary" href="<?php echo esc_url(admin_url('admin.php?page=em-rubriques-overview')); ?>"><?php esc_html_e('GÉRER LES RUBRIQUES', 'em-wp'); ?></a>
			</section>

			<section class="em-site-dashboard__card">
				<header class="em-site-dashboard__card-head"><h2><?php esc_html_e('MES TEMPLATES', 'em-wp'); ?></h2><a class="em-site-dashboard__gear" href="<?php echo esc_url(admin_url('admin.php?page=em-template')); ?>" aria-label="<?php esc_attr_e('Gérer mes templates', 'em-wp'); ?>"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span></a></header>
				<p><?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?></p>
				<p class="em-site-dashboard__status"><span class="em-site-pill em-site-pill--primary"><?php echo esc_html($active_template); ?></span><span class="em-site-pill em-site-pill--live"><?php esc_html_e('LIVE', 'em-wp'); ?></span></p>
				<p class="em-site-dashboard__links"><span class="em-site-dashboard__bubble" aria-hidden="true">&#8594;</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('admin.php?page=em-template')); ?>"><?php echo esc_html($active_template); ?></a><span>·</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('admin.php?page=em-template')); ?>"><?php esc_html_e('VOIR TOUT', 'em-wp'); ?></a></p>
			</section>

			<section class="em-site-dashboard__card">
				<header class="em-site-dashboard__card-head"><h2><?php esc_html_e('MES MEDIAS', 'em-wp'); ?></h2><a class="em-site-dashboard__gear" href="<?php echo esc_url(admin_url('upload.php')); ?>" aria-label="<?php esc_attr_e('Gérer mes médias', 'em-wp'); ?>"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span></a></header>
				<p><?php esc_html_e('Accède à ta bibliothèque de fichiers (images, vidéos, documents).', 'em-wp'); ?></p>
				<p class="em-site-dashboard__links"><span class="em-site-dashboard__bubble" aria-hidden="true">&#8594;</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('upload.php')); ?>"><?php esc_html_e('LIBRAIRIE', 'em-wp'); ?></a><span>·</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('media-new.php')); ?>"><?php esc_html_e('AJOUTER', 'em-wp'); ?></a></p>
			</section>

			<section class="em-site-dashboard__card">
				<header class="em-site-dashboard__card-head"><h2><?php esc_html_e('MES SETTINGS', 'em-wp'); ?></h2><a class="em-site-dashboard__gear" href="<?php echo esc_url(admin_url('options-general.php')); ?>" aria-label="<?php esc_attr_e('Voir mes settings', 'em-wp'); ?>"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span></a></header>
				<p><?php esc_html_e('Réglages généraux de ton site.', 'em-wp'); ?></p>
				<p class="em-site-dashboard__links"><span class="em-site-dashboard__bubble" aria-hidden="true">&#8594;</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('themes.php')); ?>"><?php esc_html_e('APPARENCE', 'em-wp'); ?></a><span>·</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(admin_url('options-general.php')); ?>"><?php esc_html_e('GÉNÉRAL', 'em-wp'); ?></a><?php if (function_exists('em_wp_client_admin_gate_settings_admin_url') && em_wp_admin_is_power_user()) : ?><span>·</span><a class="em-site-dashboard__mini-link" href="<?php echo esc_url(em_wp_client_admin_gate_settings_admin_url()); ?>"><?php esc_html_e('VERROU CLIENT', 'em-wp'); ?></a><?php endif; ?></p>
			</section>
		</div>
	</div>
	<?php
}

