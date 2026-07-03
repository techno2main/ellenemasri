# Prompt de reprise de session - em-site

Utilise ce prompt au début d'une nouvelle session pour figer l'état réel du chantier.

## Prompt à copier-coller
```text
Contexte chantier em-site (à respecter strictement) :

- Dossier de travail : em-site/
- Branche active : feature/em-site
- Source officielle autorisée : em-wp (uniquement)
- Interdictions : aucune copie récursive globale, aucun import massif non justifié, aucun legacy/fallback réintroduit
- Règle lots : exécution par étapes courtes, tests obligatoires, validation utilisateur avant lot suivant

État figé actuel :
1) Le FRONT est validé par l'utilisateur sur em-site.
2) Les ajustements mobiles récents sont validés (ancres, stream, footer).
3) Le scope actif bascule vers l'ADMIN.
4) Objectif : rebrancher l'admin à l'identique de em-wp, sans casser le front stabilisé.
5) Prochaine action autorisée : démarrer par un inventaire ADMIN source -> cible avant tout rebranchement.

Consignes d'exécution :
- Toujours mettre à jour la documentation de suivi avant tout flow GH.
- Ne rien copier hors périmètre du lot courant validé.
- ADMIN uniquement pour la nouvelle phase tant que l'utilisateur ne redéfinit pas le scope.
- Stopper à la fin de chaque lot ADMIN pour validation explicite.
- Garder une implémentation isofonctionnelle avec la source em-wp (mêmes comportements attendus).
- Préserver la structure em-site existante, éviter toute réorganisation non demandée.
- Contrôler systématiquement : menus admin, pages, hooks, assets, AJAX, nonces, permissions.
- Conserver les accents en français dans les commentaires.

Plan de lot imposé pour la reprise :
- Lot A : inventaire et cartographie complète des composants admin source em-wp.
- Lot B : bootstrap admin minimal sur em-site (chargement + accès pages de base).
- Lot C : rebranchement des écrans admin coeur (un sous-lot à la fois).
- Lot D : endpoints/actions admin (AJAX, nonces, capabilities, persistance).
- Lot E : contrôle de parité source/cible + stabilisation doc.
```

## Vérifications rapides de reprise
1. Vérifier la branche : `git rev-parse --abbrev-ref HEAD`
2. Vérifier l'état git : `git status --short --untracked-files=all`
3. Vérifier la disponibilité admin locale :
   - `docker compose ps`
   - `docker compose run --rm em-site-local-cli option get siteurl`
4. Vérifier la présence des points d'entrée admin côté thème :
   - `git grep -n "admin|wp_ajax|admin_menu|admin_enqueue_scripts" wp/wp-content/themes/em-site`
5. Vérifier la règle <300 lignes :
   - `Get-ChildItem -Recurse -File wp\wp-content\themes\em-site -Include *.php,*.css,*.js | Where-Object { ([System.IO.File]::ReadAllLines($_.FullName).Count) -ge 300 }`
