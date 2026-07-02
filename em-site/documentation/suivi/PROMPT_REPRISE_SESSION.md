# Prompt de reprise de session - em-site

Utilise ce prompt au début d'une nouvelle session pour figer l'état réel du chantier.

## Prompt à copier-coller
```text
Contexte chantier em-site (à respecter strictement) :

- Dossier de travail : em-site/
- Branche active : feature/em-site
- Source officielle autorisée : em-wp (uniquement)
- Interdictions : aucune copie récursive globale, aucun legacy, aucun fallback, aucun import non whitelisté
- Règle lots : copie étape par étape, tests obligatoires, validation utilisateur avant lot suivant

État figé actuel :
1) Point de rollback GH actif créé sur feature/em-site à la demande utilisateur.
2) Structure de thème conservée pour imports contrôlés.
3) Fallback texte WordPress neutralisé via header/footer thème minimal.
4) Top-bar réactivée en FRONT, placeholder top-bar retiré automatiquement quand les données sont présentes.
5) Layout global centralisé dans `assets/front/css/core/layout.css` ; ne pas remettre de logique spécifique rubrique dans ce fichier.
6) Prochaine action autorisée : poursuivre rubrique par rubrique (HEADER ensuite), avec validation visuelle utilisateur après chaque rubrique.

Consignes d'exécution :
- Toujours mettre à jour la documentation de suivi avant tout flow GH.
- Ne rien copier hors whitelist validée.
- FRONT uniquement tant que l'utilisateur ne rouvre pas le scope.
- Stopper après chaque rubrique pour validation utilisateur explicite.
- Pour chaque rubrique : copier seulement les règles utiles depuis la source officielle, vers `assets/front/css/modules/<rubrique>/index.css`.
- Interdiction de copier un dossier CSS complet dans la cible.
- `assets/front/css/core/layout.css` ne doit contenir que le commun global.
- Aucune mention `v4` dans le CSS cible (sélecteurs, classes, data-attrs, commentaires).
- Conserver les accents en français dans les commentaires.
```

## Vérifications rapides de reprise
1. Vérifier la branche : `git rev-parse --abbrev-ref HEAD`
2. Vérifier l'état git : `git status --short --untracked-files=all`
3. Vérifier la base active depuis WP-CLI :
   - `docker compose run --rm em-site-local-cli option get em_wp_active_template`
   - `docker compose run --rm em-site-local-cli db query "SELECT COUNT(*) AS emwp_options FROM wpem_options; SELECT COUNT(*) AS emwp_posts FROM wpem_posts;"`
4. Vérifier la règle <300 lignes :
   - `Get-ChildItem -Recurse -File wp\wp-content\themes\em-site -Include *.php,*.css,*.js | Where-Object { ([System.IO.File]::ReadAllLines($_.FullName).Count) -ge 300 }`
