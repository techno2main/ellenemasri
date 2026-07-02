# Prompt de reprise de session - em-site

Utilise ce prompt au début d'une nouvelle session pour figer l'état réel du chantier.

## Prompt à copier-coller
```text
Contexte chantier em-site (à respecter strictement) :

- Dossier de travail : em-site/
- Branche active : feature/em-site
- Source officielle autorisée : em-wp (V4 uniquement)
- Interdictions : aucune copie récursive globale, aucun legacy, aucun fallback, aucun import non whitelisté
- Règle lots : copie étape par étape, tests obligatoires, validation utilisateur avant lot suivant

État figé actuel :
1) Lot 1 V4 exécuté (copie whitelist + adaptations about/contact + assets CSS/JS importés + mini-lot raccord front).
2) Règle stricte <300 lignes validée avec méthode fiable ReadAllLines sur tous les fichiers thème PHP/CSS/JS.
3) Audit BDD validé : WordPress lit bien em_site_bdd/wpem_ avec données em_wp présentes (options/posts non vides, template actif mayami).
4) Le blocage restant de parité front/back vient du chargement incomplet des fonctions métier V4 (options/visibilité), pas d'une mauvaise base source.
5) Prochaine action autorisée : mini-lot "chargement fonctions métier" (front + admin), puis contrôles obligatoires et MAJ docs.

Consignes d'exécution :
- Toujours mettre à jour la documentation de suivi avant tout flow GH.
- Ne rien copier hors whitelist validée.
- Stopper après chaque lot pour validation utilisateur explicite.
```

## Vérifications rapides de reprise
1. Vérifier la branche : `git rev-parse --abbrev-ref HEAD`
2. Vérifier l'état git : `git status --short --untracked-files=all`
3. Vérifier la base active depuis WP-CLI :
   - `docker compose run --rm em-site-local-cli option get em_wp_active_template`
   - `docker compose run --rm em-site-local-cli db query "SELECT COUNT(*) AS emwp_options FROM wpem_options; SELECT COUNT(*) AS emwp_posts FROM wpem_posts;"`
4. Vérifier la règle <300 lignes :
   - `Get-ChildItem -Recurse -File wp\wp-content\themes\em-site -Include *.php,*.css,*.js | Where-Object { ([System.IO.File]::ReadAllLines($_.FullName).Count) -ge 300 }`
