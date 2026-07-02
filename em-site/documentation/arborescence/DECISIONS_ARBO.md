# Décisions Arbo - Projet em-site

## Objectif
Avoir une base propre, isolée de em-wp, avec une structure claire, scalable et maintenable.

## Périmètre
1. Tout nouveau développement se fait dans em-site.
2. La version stable actuelle (uniquement V4) devient la source officielle pour la copie vers em-site/wp.
3. Aucun vocabulaire d'ancienne version dans les dossiers, fichiers ou noms métier.
4. Aucun legacy, aucun fallback : un système unique de rubriques dynamiques administrables.

## Conventions globales
1. Un fichier applicatif = une responsabilité.
2. Taille cible max par fichier applicatif : 300 lignes.
3. Nommage theme : em-site.
4. Préfixe PHP : em_site_.
5. Couches séparées : core, domain, front, admin, infra, helpers.
6. Assets scindés dès le départ : core, modules, pages.
7. Pas de fichier CSS/JS monolithique : un besoin = un fichier dédié.
8. Taille cible max par fichier CSS/JS : 300 lignes.

## Décisions validées
1. On garde wp dans em-site/wp.
2. On garde scripts, backup, export dans le dossier bdd.
3. Option validée : resolver dans domain, rendu HTML dans front.
4. Une seule catégorie de rubriques : dynamiques.
5. L'admin peut créer, modifier et supprimer des rubriques à la volée.
6. Aucune distinction de type native, legacy ou autre terminologie technique côté produit.

## Méthode de migration validée
1. On ne part pas d'un thème WordPress vierge.
2. On part de la source officielle stable (actuellement V4) et on recopie étape par étape vers em-site/wp.
3. Priorité de copie : admin personnalisé et front personnalisé, pour éviter toute réécriture inutile.
4. On exclut explicitement les fichiers legacy, fallback et le bruit historique non nécessaire.
5. Chaque lot copié est contrôlé avant le lot suivant (approche incrémentale et réversible).

## Intégration Header
1. Header est une rubrique dynamique d'orchestration.
2. Hero et Slider restent des rubriques dynamiques autonomes.
3. Header décide de la composition (Hero seul, Slider seul, Hero + Slider) et de l'ordre d'affichage.
4. Domaine : règles Header dans domain/sections/header.php.
5. Resolver : décision finale dans domain/resolver/header.php.
6. Front : rendu d'assemblage dans front/modules/header/render.php, puis rendu des rubriques Hero/Slider.

## Décisions à valider ensemble
1. Valider la liste initiale des rubriques dynamiques : Top-Bar, Hero, Slider, Stream, Social, Video, Release, CTA, About, Contact, Footer.
