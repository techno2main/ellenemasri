        #adminmenu #menu-dashboard .em-site-dashboard-menu-arrow,
        #adminmenu li.em-site-menu-dashboard-entry .em-site-dashboard-menu-arrow {
            display: block !important;
            visibility: visible !important;
            width: 28px;
            height: 28px;
            margin: 8px auto 14px;
            border-radius: 999px;
            background-color: rgba(255, 255, 255, 0.14);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 22 22' fill='none'%3E%3Cpath d='M11 4v11.5' stroke='%23ffffff' stroke-width='2' stroke-linecap='round'/%3E%3Cpath d='M6 12.5 11 17.5 16 12.5' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 14px 14px;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.22),
                0 2px 6px rgba(0, 0, 0, 0.12);
            animation: em-site-admin-dashboard-arrow-bounce 2.2s ease-in-out infinite;
            pointer-events: none;
        }

        #adminmenu #menu-dashboard,
        #adminmenu li.em-site-menu-dashboard-entry {
            overflow: visible;
            margin-bottom: 14px;
        }

        #adminmenu li.em-site-menu-dashboard-entry.wp-has-current-submenu::after,
        #adminmenu li.em-site-menu-dashboard-entry.wp-menu-open::after {
            display: none !important;
            content: none !important;
        }

        #adminmenu li.em-site-menu-dashboard-entry .wp-submenu {
            display: none !important;
        }

        .folded #adminmenu #menu-dashboard .em-site-dashboard-menu-arrow,
        .folded #adminmenu li.em-site-menu-dashboard-entry .em-site-dashboard-menu-arrow {
            display: none !important;
        }

        @keyframes em-site-admin-dashboard-arrow-bounce {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(3px);
            }
        }
