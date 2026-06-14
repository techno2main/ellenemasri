        body.em-wp-admin-sidebar-chrome #adminmenu li.em-wp-menu-accordion-parent > a.menu-top::after,
        body.em-wp-admin-dashboard-screen #adminmenu li.em-wp-menu-accordion-parent > a.menu-top::after {
            color: rgba(255, 255, 255, 0.85);
        }

        #adminmenu li.em-wp-menu-accordion-child {
            display: none !important;
        }

        body.em-wp-accordion-catalog-open #adminmenu li.em-wp-menu-accordion-catalog-child {
            display: block !important;
        }

        body.em-wp-accordion-medias-open #adminmenu li.em-wp-menu-accordion-medias-child {
            display: block !important;
        }

        body.em-wp-accordion-templates-open #adminmenu li.em-wp-menu-accordion-templates-child {
            display: block !important;
        }

        body.em-wp-accordion-settings-open #adminmenu li.em-wp-menu-accordion-settings-child {
            display: block !important;
        }

        #adminmenu li.em-wp-menu-accordion-parent .wp-submenu,
        #adminmenu li.em-wp-menu-accordion-medias-parent .wp-submenu,
        #adminmenu #menu-upload .wp-submenu,
        #adminmenu #menu-media .wp-submenu,
        #adminmenu li.em-wp-menu-accordion-settings-child .wp-submenu,
        #adminmenu #menu-appearance .wp-submenu,
        #adminmenu #menu-settings .wp-submenu {
            display: none !important;
        }

        #adminmenu li.em-wp-menu-accordion-settings-child .wp-menu-name {
            padding-left: 10px;
        }

        /* Sous-menus Catalogues / Templates / Médias — décalage visuel sans toucher au PHP */
        #adminmenu li.em-wp-menu-accordion-catalog-child > a.menu-top,
        #adminmenu li.em-wp-menu-accordion-templates-child > a.menu-top,
        #adminmenu li.em-wp-menu-accordion-medias-child > a.menu-top,
        #adminmenu li.em-wp-menu-rubrique-child > a.menu-top {
            padding-left: 22px !important;
            box-sizing: border-box;
        }

        #adminmenu li.em-wp-menu-accordion-catalog-child .wp-menu-image,
        #adminmenu li.em-wp-menu-accordion-templates-child .wp-menu-image,
        #adminmenu li.em-wp-menu-accordion-medias-child .wp-menu-image,
        #adminmenu li.em-wp-menu-rubrique-child .wp-menu-image {
            width: 30px;
        }

        #adminmenu li.em-wp-menu-accordion-catalog-child .wp-menu-name,
        #adminmenu li.em-wp-menu-accordion-templates-child .wp-menu-name,
        #adminmenu li.em-wp-menu-accordion-medias-child .wp-menu-name,
        #adminmenu li.em-wp-menu-rubrique-child .wp-menu-name {
            padding-left: 0;
        }

        .folded #adminmenu li.em-wp-menu-accordion-catalog-child > a.menu-top,
        .folded #adminmenu li.em-wp-menu-accordion-templates-child > a.menu-top,
        .folded #adminmenu li.em-wp-menu-accordion-medias-child > a.menu-top,
        .folded #adminmenu li.em-wp-menu-rubrique-child > a.menu-top {
            padding-left: 0 !important;
        }

        #adminmenu li.em-wp-menu-template-live > a.menu-top .wp-menu-name::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: 8px;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.28);
            vertical-align: middle;
            transform: translateY(-1px);
        }

        #adminmenu li.em-wp-menu-rubrique-current > a.menu-top .wp-menu-name::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: 8px;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.28);
            vertical-align: middle;
            transform: translateY(-1px);
        }

        .folded #adminmenu li.em-wp-menu-template-live > a.menu-top .wp-menu-name::before {
            margin-right: 0;
        }

        .folded #adminmenu li.em-wp-menu-rubrique-current > a.menu-top .wp-menu-name::before {
            margin-right: 0;
        }

        #adminmenu li.em-wp-menu-accordion-parent.menu-top {
            position: relative;
        }

        #adminmenu li.em-wp-menu-accordion-parent > a.menu-top {
            position: relative;
            display: flex !important;
            align-items: center !important;
            min-height: 34px;
            padding-right: 28px !important;
        }

        #adminmenu li.em-wp-menu-accordion-parent > a.menu-top .wp-menu-image {
            margin-top: 0 !important;
        }

        #adminmenu li.em-wp-menu-accordion-parent > a.menu-top .wp-menu-name {
            padding: 8px 0;
        }

        /* Flèche native WordPress (div.wp-menu-arrow) — tous les parents accordéon */
        #adminmenu li.em-wp-menu-accordion-parent .wp-menu-arrow {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }

        body.em-wp-admin-sidebar-chrome #adminmenu li.em-wp-menu-accordion-parent.menu-top::after,
        body.em-wp-admin-sidebar-chrome #adminmenu li.em-wp-menu-accordion-parent.wp-has-submenu::after,
        body.em-wp-admin-sidebar-chrome #adminmenu li.em-wp-menu-accordion-parent.wp-has-current-submenu::after {
            display: none !important;
            content: none !important;
            border: 0 !important;
            width: 0 !important;
            height: 0 !important;
        }

        #adminmenu li.em-wp-menu-accordion-parent > a.menu-top::after {
            content: '\f347';
            position: absolute;
            top: 50%;
            right: 8px;
            font-family: dashicons;
            font-size: 16px;
            line-height: 1;
            opacity: 0.72;
            transform: translateY(-50%);
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        /* Fermé : chevron dashicons vers le bas (comme MEDIAS). Ouvert : aucune flèche. */
        body.em-wp-accordion-catalog-open #adminmenu li.em-wp-menu-accordion-catalog-parent > a.menu-top::after,
        body.em-wp-accordion-medias-open #adminmenu li.em-wp-menu-accordion-medias-parent > a.menu-top::after,
        body.em-wp-accordion-templates-open #adminmenu li.em-wp-menu-accordion-templates-parent > a.menu-top::after,
        body.em-wp-accordion-settings-open #adminmenu li.em-wp-menu-accordion-settings-parent > a.menu-top::after {
            content: none !important;
            display: none !important;
        }
