        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-site-top .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-bottom .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-before-vlb .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-after-medias .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-after-catalog .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-after-templates .separator,
        body.em-wp-admin-sidebar-chrome #adminmenu li.wp-menu-separator.separator-em-wp-before-settings .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-site-top .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-bottom .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-before-vlb .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-after-medias .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-after-catalog .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-after-templates .separator,
        body.em-wp-admin-dashboard-screen #adminmenu li.wp-menu-separator.separator-em-wp-before-settings .separator {
            background: rgba(255, 255, 255, 0.42) !important;
            opacity: 1 !important;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-vlb,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-medias,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-catalog,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-templates,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-settings {
            cursor: default;
            pointer-events: none;
            margin: 0;
            padding: 0;
            height: auto;
            min-height: 0;
            background: transparent !important;
            border: 0;
            box-shadow: none;
        }

        #adminmenu li.wp-menu-separator.separator-em-wp-site-top .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-bottom .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-vlb .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-medias .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-catalog .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-after-templates .separator,
        #adminmenu li.wp-menu-separator.separator-em-wp-before-settings .separator {
            display: block;
            height: 1px;
            margin: 10px 10px 12px;
            padding: 0;
            border: 0;
            background: #ffffff;
            opacity: 0.42;
            box-shadow: none;
        }

        #adminmenu li.wp-menu-separator:not(.separator-em-wp-site-top):not(.separator-em-wp-bottom):not(.separator-em-wp-before-vlb):not(.separator-em-wp-after-medias):not(.separator-em-wp-after-catalog):not(.separator-em-wp-after-templates):not(.separator-em-wp-before-settings) {
            display: none !important;
        }
