        /* RUBRIQUES : icône + libellé alignés dans les sous-menus de gauche. */
        #adminmenu #toplevel_page_em-wp-v4-overview .wp-submenu li > a {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #adminmenu #toplevel_page_em-wp-v4-overview .wp-submenu .em-wp-rubrique-submenu__icon {
            font-size: 16px;
            width: 16px;
            height: 16px;
            line-height: 16px;
            flex: 0 0 auto;
        }

        #adminmenu #toplevel_page_em-wp-v4-overview .wp-submenu .em-wp-rubrique-submenu__text {
            flex: 1 1 auto;
        }

        /* Masque le 1er sous-menu auto (lien « …page=em-wp-v4-overview » sans &type) :
           on le garde pour que le parent RUBRIQUES n'ouvre pas le 1er type, mais on
           ne l'affiche pas (l'utilisateur ne veut pas d'entrée « Vue d'ensemble »). */
        #adminmenu #toplevel_page_em-wp-v4-overview .wp-submenu li:has(> a[href$="page=em-wp-v4-overview"]) {
            display: none;
        }

        /* Pas de sous-menu en flyout à droite : il n'apparaît qu'en inline quand
           la rubrique RUBRIQUES est active (menu déplié uniquement). */
        body:not(.folded) #adminmenu #toplevel_page_em-wp-v4-overview:not(.wp-has-current-submenu):not(.wp-menu-open):hover > .wp-submenu,
        body:not(.folded) #adminmenu #toplevel_page_em-wp-v4-overview:not(.wp-has-current-submenu):not(.wp-menu-open).opensub > .wp-submenu {
            left: -999em !important;
        }
