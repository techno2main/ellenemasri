        body.em-site-admin-sidebar-chrome #adminmenuback,
        body.em-site-admin-sidebar-chrome #adminmenuwrap,
        body.em-site-admin-sidebar-chrome #adminmenu,
        body.em-site-admin-sidebar-chrome #adminmenu .wp-submenu,
        body.em-site-admin-dashboard-screen #adminmenuback,
        body.em-site-admin-dashboard-screen #adminmenuwrap,
        body.em-site-admin-dashboard-screen #adminmenu,
        body.em-site-admin-dashboard-screen #adminmenu .wp-submenu {
            background: #4e080e !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenuwrap,
        body.em-site-admin-dashboard-screen #adminmenuwrap {
            height: calc(100vh - 32px) !important;
            max-height: calc(100vh - 32px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            box-sizing: border-box;
            padding-bottom: 64px !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenuwrap::-webkit-scrollbar,
        body.em-site-admin-dashboard-screen #adminmenuwrap::-webkit-scrollbar {
            width: 0;
            height: 0;
            background: transparent;
        }

        body.em-site-admin-sidebar-chrome #adminmenuback,
        body.em-site-admin-dashboard-screen #adminmenuback {
            height: calc(100vh - 32px) !important;
            max-height: calc(100vh - 32px) !important;
        }

        @media screen and (max-width: 782px) {
            body.em-site-admin-sidebar-chrome #adminmenuwrap,
            body.em-site-admin-dashboard-screen #adminmenuwrap,
            body.em-site-admin-sidebar-chrome #adminmenuback,
            body.em-site-admin-dashboard-screen #adminmenuback {
                height: calc(100vh - 46px) !important;
                max-height: calc(100vh - 46px) !important;
            }
        }

        body.em-site-admin-sidebar-chrome #adminmenu,
        body.em-site-admin-dashboard-screen #adminmenu {
            padding-bottom: 56px !important;
            box-sizing: border-box;
        }

        body.em-site-admin-sidebar-chrome #adminmenu a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu .wp-submenu a,
        body.em-site-admin-dashboard-screen #adminmenu a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu .wp-submenu a {
            color: rgba(255, 255, 255, 0.92) !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenu .wp-menu-image::before,
        body.em-site-admin-dashboard-screen #adminmenu .wp-menu-image::before {
            color: rgba(255, 255, 255, 0.92) !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenu li.menu-top:hover,
        body.em-site-admin-sidebar-chrome #adminmenu li.opensub > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li > a.menu-top:focus,
        body.em-site-admin-dashboard-screen #adminmenu li.menu-top:hover,
        body.em-site-admin-dashboard-screen #adminmenu li.opensub > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li > a.menu-top:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenu li.current a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.wp-has-current-submenu a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-accordion-parent.wp-has-current-submenu > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-accordion-child.current > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-submenu-current > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-rubrique-current > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-template-editing > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.current a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.wp-has-current-submenu a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-dashboard-entry.current > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-submenu-current > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-rubrique-current > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-template-editing > a.menu-top {
            background: rgba(255, 255, 255, 0.26) !important;
            color: #ffffff !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-submenu-current:hover > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-rubrique-current:hover > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-template-editing:hover > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.current:hover > a.menu-top,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-accordion-child.current:hover > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-submenu-current:hover > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-rubrique-current:hover > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-template-editing:hover > a.menu-top,
        body.em-site-admin-dashboard-screen #adminmenu li.current:hover > a.menu-top {
            background: rgba(255, 255, 255, 0.32) !important;
            color: #ffffff !important;
        }

        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-section-label .wp-menu-name,
        body.em-site-admin-sidebar-chrome #adminmenu li.em-site-menu-wp-settings-label .wp-menu-name,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-section-label .wp-menu-name,
        body.em-site-admin-dashboard-screen #adminmenu li.em-site-menu-wp-settings-label .wp-menu-name {
            color: rgba(255, 255, 255, 0.72) !important;
        }
