## MAJ du flow GH - complet

Date: 2026-06-07

Périmètre:
1. `wp` uniquement (thème WordPress `ellene-wp`)
2. Flow GitHub exécuté: commits + push sur `feature/wp-ellene-refacto`

Référence push:
1. Commit: `004d7f9`
2. Commit: `c482321`

État réel à intégrer dans le flow GH:
1. Renommage hors VLB finalisé sur les fichiers code ciblés (`.php`, `.js`, `.css`)
2. Correctifs back/front appliqués après validation visuelle:
	- suppression des BOM UTF-8 sur les fichiers modifiés pour éliminer `Cannot modify header information`
	- ajout du fallback `wp/favicon.ico` pour éviter le `GET /favicon.ico 404`
3. Vérification hors VLB: occurrences `mayami` = 0 fichier / 0 match
4. Bloc VLB: corrections d'accents/mojibake appliquées sur les fichiers code + cache-busting des assets admin VLB pour forcer le rechargement navigateur

Découpage recommandé en commit atomique:
1. Commit A - renommage hors VLB + corrections chaînes
2. Commit B - stabilisation encodage (suppression BOM) + fallback favicon racine

Message suggéré (si un seul commit est préféré):
`wp(theme): finaliser rename hors VLB et corriger erreurs back/front (BOM + favicon)`

Checklist push:
1. Vérifier le diff final (pas de fichier VLB inclus)
2. Vérifier qu'aucun fichier upload/media non voulu n'est staged
3. Commit atomique selon le découpage retenu
4. Push sur la branche feature en cours

## Reste à faire

À ce stade, il ne reste plus rien à renommer hors VLB: le périmètre thème principal est à 0 occurrence `mayami` hors exclusions. Concernant le bloc VLB, les erreurs d'accents/mojibake côté code ont été corrigées et un cache-busting des assets admin VLB a été ajouté pour forcer le rechargement navigateur; les chaînes éditoriales verrouillées restent inchangées (`Mayami - EPK 2026`, `Download Mayami EPK 2026 in ONESHEET CLICKABLE`, URL PDF associée, et `Visuel ouvert : Mayami - EPK 2026`). Les seuls renommages encore possibles concernent donc uniquement les identifiants `mayami` du bloc VLB (`inc/vlb/builder.php`, `inc/vlb/menu.php`, `inc/vlb/pages.php`, `inc/vlb/export.php`, `inc/vlb/data.php`, `inc/vlb/shared.php`, `assets/admin-visual-links-builder.css`, `assets/admin-visual-links-builder.js`, `assets/visual-links.css`, `visual-links-builder/admin/assets/builder.js`), soit 10 fichiers et 360 matches actuels. À ne pas renommer par défaut (sauf demande explicite): archives et non-runtime (`_sources/dump_base_sql/wp_ellene_local_2026-06-07.sql`, `.gitignore`, `.copilot-snapshots/pre-lot2-20260605-202445.patch`) ainsi que les contenus éditoriaux liés au single/EPK.

---

## CR immédiat - Post-actions (hors VLB)

Date: 2026-06-07

Contexte d'exécution:
1. Actions appliquées sur les fichiers restants hors VLB (`.php`, `.js`, `.css`)
2. Exclusions respectées: `inc/vlb/**`, `visual-links-builder/**`, `assets/admin-visual-links-builder.{js,css}`, `assets/visual-links.css`, `uploads/**`
3. Correction des chaînes mojibake dans les fichiers modifiés hors VLB

Résultat de vérification stricte post-actions (hors VLB):
1. Fichiers avec occurrences `mayami`: 0
2. Occurrences `mayami` (matches de lignes): 0

Note:
1. Les occurrences restantes globales dans la recherche VS Code proviennent du périmètre VLB explicitement exclu.

---

## CR immédiat - Vérification stricte (code pur, sans actions)

Date: 2026-06-07

Critères appliqués pour cette vérification:
1. Périmètre: `wp/wp-content/themes/ellene-wp/**`
2. Fichiers analysés: code uniquement (`.php`, `.js`, `.css`)
3. Fichiers visuels/media exclus (dans assets inclus): `.svg`, `.png`, `.jpg`, `.jpeg`, `.gif`, `.webp`, `.ico`, `.avif`, `.mp4`, `.mp3`, `.wav`, `.woff`, `.woff2`, `.ttf`, `.eot`, `.otf`, `.pdf`
4. Aucun scan de `wp/wp-content/uploads/**` (exclu)
5. Aucun changement de code effectué dans ce contrôle

Résultat global (code pur):
1. Fichiers avec occurrences restantes: 18
2. Total occurrences détectées (matches de lignes): 588

Liste finale restante à traiter (sans action pour le moment):
1. 105 - `wp/wp-content/themes/ellene-wp/assets/admin-nav.js`
2. 98 - `wp/wp-content/themes/ellene-wp/assets/admin-nav.css`
3. 97 - `wp/wp-content/themes/ellene-wp/inc/vlb/builder.php`
4. 81 - `wp/wp-content/themes/ellene-wp/assets/admin-visual-links-builder.css`
5. 38 - `wp/wp-content/themes/ellene-wp/inc/vlb/menu.php`
6. 38 - `wp/wp-content/themes/ellene-wp/inc/vlb/pages.php`
7. 36 - `wp/wp-content/themes/ellene-wp/assets/admin-visual-links-builder.js`
8. 24 - `wp/wp-content/themes/ellene-wp/inc/vlb/export.php`
9. 18 - `wp/wp-content/themes/ellene-wp/assets/visual-links.css`
10. 12 - `wp/wp-content/themes/ellene-wp/inc/theme/assets.php`
11. 11 - `wp/wp-content/themes/ellene-wp/inc/vlb/data.php`
12. 10 - `wp/wp-content/themes/ellene-wp/visual-links-builder/admin/assets/builder.js`
13. 7 - `wp/wp-content/themes/ellene-wp/inc/vlb/shared.php`
14. 6 - `wp/wp-content/themes/ellene-wp/template-parts/sections/slider/index.php`
15. 3 - `wp/wp-content/themes/ellene-wp/assets/content-protection.js`
16. 2 - `wp/wp-content/themes/ellene-wp/template-parts/sections/footer/index.php`
17. 1 - `wp/wp-content/themes/ellene-wp/inc/theme/statistics.php`
18. 1 - `wp/wp-content/themes/ellene-wp/inc/cmb2/options-config.php`

Points importants:
1. Cette liste inclut bien du code dans `assets` (JS/CSS), et exclut les visuels de `assets`.
2. `wp/wp-content/debug.log` n'est pas inclus ici car ce n'est pas du code source.
3. `wp/wp-content/uploads/**` n'est pas inclus conformément à la consigne.
4. Les 2 fichiers à 1 occurrence sont des cas potentiellement intentionnels:
	- `inc/cmb2/options-config.php`: clé legacy de migration (`mayami_landing_options`)
	- `inc/theme/statistics.php`: text-domain encore en `mayami`

---

# Inventaire structuré des appels métier mayami (avec exclusions)

Exclusions appliquées:
1. wp/wp-content/themes/ellene-wp/assets
2. wp/wp-content/uploads
3. website
4. documentation
5. AGENTS.md
6. visual-links-builder/exports-html (EPK exports)
7. _sources/mayami-to-ellene.md et _sources/regen-mayami-report.ps1 (auto-références)
8. wp/wp-content/debug.log
9. lignes liées au single "Mayami, My Miami"
10. lignes liées à EPK / Mayami EPK
11. wp/wp-content/themes/ellene-wp/inc/vlb
12. wp/wp-content/themes/ellene-wp/visual-links-builder

- Total occurrences: 249
- Total fichiers impactés: 30
- Total motifs distincts (tokens): 92

## 1) Synthèse par type de fichier

1. code: fichiers=27, occurrences=206
2. data/sql: fichiers=1, occurrences=35
3. autre (.gitignore): fichiers=1, occurrences=6
4. texte/patch: fichiers=1, occurrences=2

## 2) Fichiers classés par occurrences (avec type)

1. _sources/dump_base_sql/wp_ellene_local_2026-06-07.sql | type=data/sql | occurrences=35
2. wp/wp-content/themes/ellene-wp/inc/theme/assets.php | type=code | occurrences=34
3. wp/wp-content/themes/ellene-wp/template-parts/sections/stream/index.php | type=code | occurrences=31
4. wp/wp-content/themes/ellene-wp/template-parts/sections/hero/index.php | type=code | occurrences=24
5. wp/wp-content/themes/ellene-wp/inc/cmb2/options-config.php | type=code | occurrences=15
6. wp/wp-content/themes/ellene-wp/template-parts/sections/cta/index.php | type=code | occurrences=14
7. wp/wp-content/themes/ellene-wp/template-parts/sections/slider/index.php | type=code | occurrences=14
8. wp/wp-content/themes/ellene-wp/template-parts/sections/social/index.php | type=code | occurrences=13
9. wp/wp-content/themes/ellene-wp/inc/theme/setup.php | type=code | occurrences=11
10. wp/wp-content/themes/ellene-wp/template-parts/sections/video/index.php | type=code | occurrences=8
11. .gitignore | type=autre (.gitignore) | occurrences=6
12. wp/wp-content/themes/ellene-wp/inc/modules/resolver.php | type=code | occurrences=6
13. wp/wp-content/themes/ellene-wp/inc/theme/statistics.php | type=code | occurrences=6
14. wp/wp-content/themes/ellene-wp/template-parts/sections/release/index.php | type=code | occurrences=5
15. wp/wp-content/themes/ellene-wp/template-parts/sections/footer/index.php | type=code | occurrences=4
16. wp/wp-content/themes/ellene-wp/template-parts/sections/footer/sticky-bar.php | type=code | occurrences=4
17. wp/wp-content/themes/ellene-wp/template-parts/sections/top-bar/index.php | type=code | occurrences=4
18. .copilot-snapshots/pre-lot2-20260605-202445.patch | type=texte/patch | occurrences=2
19. wp/wp-content/themes/ellene-wp/inc/theme/seo.php | type=code | occurrences=2
20. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/cta.php | type=code | occurrences=1
21. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/footer.php | type=code | occurrences=1
22. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/hero.php | type=code | occurrences=1
23. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/modules.php | type=code | occurrences=1
24. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/release.php | type=code | occurrences=1
25. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/slider.php | type=code | occurrences=1
26. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/social.php | type=code | occurrences=1
27. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/stream.php | type=code | occurrences=1
28. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/top-bar.php | type=code | occurrences=1
29. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/video.php | type=code | occurrences=1
30. wp/wp-content/themes/ellene-wp/style.css | type=code | occurrences=1

## 3) Classement des motifs contenant mayami

1. mayami_landing_options | occurrences=62
2. mayami_get_landing_option | occurrences=32
3. mayami | occurrences=11
4. __mayamiYouTubeReadyCallbacks | occurrences=5
5. mayami_get_oembed_iframe_src | occurrences=4
6. mayami_output_theme_favicon | occurrences=4
7. mayami_extract_tiktok_video_id | occurrences=3
8. mayami_extract_youtube_id | occurrences=3
9. mayami_get_youtube_channel_id_from_url | occurrences=3
10. mayami_hero_extract_youtube_id | occurrences=3
11. mayami-admin-nav | occurrences=3
12. __mayamiYouTubeApiLoading | occurrences=2
13. mayami_build_stream_embed_src | occurrences=2
14. mayami_client_login_redirect | occurrences=2
15. mayami_detect_stream_platform_key | occurrences=2
16. mayami_enqueue_admin_assets | occurrences=2
17. mayami_enqueue_assets | occurrences=2
18. mayami_extract_iframe_src_from_html | occurrences=2
19. mayami_extract_youtube_channel_id_from_html | occurrences=2
20. mayami_force_landing_noindex | occurrences=2
21. mayami_hide_wp_footer_text_on_landing | occurrences=2
22. mayami_limit_admin_bar_for_client | occurrences=2
23. mayami_limit_admin_menu_for_client | occurrences=2
24. mayami_media_modal_edit_button | occurrences=2
25. mayami_redirect_admin_bar_edit_to_landing | occurrences=2
26. mayami_register_cmb2_cta_section | occurrences=2
27. mayami_register_cmb2_footer_section | occurrences=2
28. mayami_register_cmb2_hero_section | occurrences=2
29. mayami_register_cmb2_modules_section | occurrences=2
30. mayami_register_cmb2_release_section | occurrences=2
31. mayami_register_cmb2_slider_section | occurrences=2
32. mayami_register_cmb2_social_section | occurrences=2
33. mayami_register_cmb2_stream_section | occurrences=2
34. mayami_register_cmb2_top_bar_section | occurrences=2
35. mayami_register_cmb2_video_section | occurrences=2
36. mayami_register_options | occurrences=2
37. mayami_register_statistics_page | occurrences=2
38. mayami_resolve_stream_final_url | occurrences=2
39. mayami_statistics_page | occurrences=2
40. mayami_stream_player_height | occurrences=2
41. mayami_theme_setup | occurrences=2
42. mayami_upload_size_limit | occurrences=2
43. mayami_visual_links | occurrences=2
44. mayami-admin-visual-links-builder | occurrences=2
45. mayami-edit-btn | occurrences=2
46. mayami-legal-footer | occurrences=2
47. mayami-tailwind | occurrences=2
48. toplevel_page_mayami_landing_options | occurrences=2
49. _transient_mayami_stream_url_23962da8ccfeb68824d34f8c81f6e788 | occurrences=1
50. _transient_mayami_stream_url_7c43a2ed0d38845a636720eedd4668e5 | occurrences=1
51. _transient_mayami_stream_url_b183d08e0d9e6b3502e3fbe244167b51 | occurrences=1
52. _transient_mayami_stream_url_c8b3305767c58a86661d7afd32493a1e | occurrences=1
53. _transient_mayami_stream_url_d5a42c7d9497b8807e6781619b0175e5 | occurrences=1
54. _transient_mayami_stream_url_dcaa5a05daabb38437017065eac46fd9 | occurrences=1
55. _transient_timeout_mayami_stream_url_23962da8ccfeb68824d34f8c81f6e788 | occurrences=1
56. _transient_timeout_mayami_stream_url_7c43a2ed0d38845a636720eedd4668e5 | occurrences=1
57. _transient_timeout_mayami_stream_url_8b634c7d87fa4000ec7aa6974ef3c494 | occurrences=1
58. _transient_timeout_mayami_stream_url_b183d08e0d9e6b3502e3fbe244167b51 | occurrences=1
59. _transient_timeout_mayami_stream_url_c8b3305767c58a86661d7afd32493a1e | occurrences=1
60. _transient_timeout_mayami_stream_url_d5a42c7d9497b8807e6781619b0175e5 | occurrences=1
61. _transient_timeout_mayami_stream_url_dcaa5a05daabb38437017065eac46fd9 | occurrences=1
62. ellene_home_seeded_from_mayami_v1 | occurrences=1
63. ellene_home_seeded_from_mayami_v2 | occurrences=1
64. INVENTAIRE_PHASE2_MAYAMI | occurrences=1
65. mayami_content_initialized | occurrences=1
66. mayami_follow_youtube_link_synced_20260529 | occurrences=1
67. mayami_force_youtube_stream_url_20260530 | occurrences=1
68. mayami_get_primary_stream_link | occurrences=1
69. mayami_hero_top_artist_synced_20260529 | occurrences=1
70. mayami_marquee_items_synced_20260530 | occurrences=1
71. mayami_marquee_items_synced_20260530_v2 | occurrences=1
72. mayami_marquee_items_synced_20260530_v3 | occurrences=1
73. mayami_marquee_items_synced_20260530_v4 | occurrences=1
74. mayami_marquee_items_synced_20260530_v5 | occurrences=1
75. mayami_marquee_play_link_synced_20260529 | occurrences=1
76. mayami_options | occurrences=1
77. mayami_platform_links_synced_20260529 | occurrences=1
78. mayami_social_links_synced_20260530 | occurrences=1
79. mayami_statistics | occurrences=1
80. mayami_sticky_links_synced_20260530 | occurrences=1
81. mayami_stream_oembed_ | occurrences=1
82. mayami_stream_platforms_from_links_synced_20260530 | occurrences=1
83. mayami_stream_platforms_synced_20260529 | occurrences=1
84. mayami_stream_url_ | occurrences=1
85. mayami_stream_values_aligned_with_front_20260530 | occurrences=1
86. mayami_stream_yt_channel_ | occurrences=1
87. mayami-content-protection | occurrences=1
88. mayami-new-media | occurrences=1
89. mayami-stream-player | occurrences=1
90. PA_RESTRUCTURATION_ELLENE_MAYAMI | occurrences=1
91. theme_mods_mayami | occurrences=1
92. wp-global-styles-mayami | occurrences=1

## 4) Shortlist finale pour remplacement métier (objectif ellene-wp)

Validation du périmètre:
1. Le rapport correspond bien au décompte filtré: 30 fichiers.
2. Le périmètre EPK, single et médias WP est exclu.
3. Le périmètre VLB est exclu.

Fichiers réellement cibles de renommage métier:
1. Code WordPress thème: 27 fichiers
2. SQL de données (à traiter avec prudence): 1 fichier
3. Fichiers hors code à ignorer pour renommage métier: .gitignore et patch snapshot

Règle de remplacement attendue:
1. Remplacer les identifiants métier préfixés mayami_:
2. Pour les clés/slugs/option_key (texte): mayami_ -> ellene-wp_
3. Pour les identifiants PHP (fonctions, callbacks, hooks nommés): mayami_ -> ellene_wp_ (underscore obligatoire, tiret interdit en identifiant PHP)
2. Conserver inchangés tous les noms liés au single et à l'EPK.
3. Ne pas renommer les fichiers médias ni leurs références de contenu.

Priorité de remplacement (ordre recommandé):
1. options key et page slug CMB2: mayami_landing_options, toplevel_page_mayami_landing_options
2. fonctions PHP métier: mayami_get_landing_option, mayami_register_options, mayami_theme_setup, etc.
3. hooks/actions/filters WordPress contenant mayami
4. sélecteurs/classes CSS/JS métier mayami (hors branding single/EPK)
5. résidus SQL strictement techniques (hors contenu éditorial)

## 5) État d'avancement réel (working tree)

Résumé:
1. Fichiers traités (modifiés): 27 / 30
2. Fichiers conservés volontairement (non-runtime/archive): 3 / 30
3. État global du périmètre: 30 / 30 clôturé
3. Migration BDD one-shot ajoutée dans `inc/cmb2/options-config.php` pour restaurer les données vers la clé active `ellene-wp_landing_options`.

Fichiers déjà traités:
1. wp/wp-content/themes/ellene-wp/inc/cmb2/options-config.php
2. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/cta.php
3. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/footer.php
4. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/hero.php
5. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/modules.php
6. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/release.php
7. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/slider.php
8. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/social.php
9. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/stream.php
10. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/top-bar.php
11. wp/wp-content/themes/ellene-wp/inc/cmb2/options-sections/video.php
12. wp/wp-content/themes/ellene-wp/inc/modules/resolver.php
13. wp/wp-content/themes/ellene-wp/inc/theme/assets.php
14. wp/wp-content/themes/ellene-wp/inc/theme/seo.php
15. wp/wp-content/themes/ellene-wp/inc/theme/setup.php
16. wp/wp-content/themes/ellene-wp/inc/theme/statistics.php
17. wp/wp-content/themes/ellene-wp/template-parts/sections/cta/index.php
18. wp/wp-content/themes/ellene-wp/template-parts/sections/footer/index.php
19. wp/wp-content/themes/ellene-wp/template-parts/sections/footer/sticky-bar.php
20. wp/wp-content/themes/ellene-wp/template-parts/sections/hero/index.php
21. wp/wp-content/themes/ellene-wp/template-parts/sections/release/index.php
22. wp/wp-content/themes/ellene-wp/template-parts/sections/slider/index.php
23. wp/wp-content/themes/ellene-wp/template-parts/sections/social/index.php
24. wp/wp-content/themes/ellene-wp/template-parts/sections/stream/index.php
25. wp/wp-content/themes/ellene-wp/template-parts/sections/top-bar/index.php
26. wp/wp-content/themes/ellene-wp/template-parts/sections/video/index.php
27. wp/wp-content/themes/ellene-wp/style.css

Fichiers conservés volontairement (hors runtime):
1. _sources/dump_base_sql/wp_ellene_local_2026-06-07.sql (dump source, non exécuté par le thème)
2. .gitignore (règles d'ignore historiques, pas d'impact front/back)
3. .copilot-snapshots/pre-lot2-20260605-202445.patch (archive snapshot, non-runtime)
