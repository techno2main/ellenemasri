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

        /* Aucun flyout en menu DÉPLIÉ : le sous-menu RUBRIQUES ne doit pas
           apparaître en survol (popover qui « s'ouvre aléatoirement » à droite).
           Il reste affiché inline quand la rubrique est la page courante.
           On garde `body:not(.folded)` pour NE PAS casser le mode réduit, où le
           flyout au survol est le seul moyen d'accéder aux types (sinon RUBRIQUES
           devient inaccessible une fois le menu replié). */
        body:not(.folded) #adminmenu #toplevel_page_em-wp-v4-overview:not(.wp-menu-open):not(.wp-has-current-submenu) > .wp-submenu {
            display: none !important;
        }
