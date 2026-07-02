# Étape 4 - Contrôle fonctionnel applicatif (A→G)

## Horodatage (Paris)
1. Dernière mise à jour : 2026-07-02 17:50:56.

## Avancement
- [x] Contrôle A effectué
- [x] Contrôle B effectué
- [x] Contrôle C effectué
- [x] Contrôle D effectué
- [x] Contrôle E effectué
- [x] Contrôle F effectué
- [x] Contrôle G effectué

## Statut
En cours.

## Objectif
Valider le comportement applicatif em-site-local après réimport base V4.

## Critère de sortie
Aucun blocage critique sur front, admin, données et configuration thème.

## Checklist A→G (résultats)
1. A - Stack em-site-local démarrée : ✅ OK
   - Conteneurs actifs : em-site-local, em-site-local-db, em-site-local-pma.
2. B - Front public accessible : ⚠️ BLOQUÉ
   - État actuel après rollback ciblé : HTTP 200 avec réponse vide (longueur 0).
3. C - Back-office WordPress accessible : ✅ OK
   - wp-admin renvoie 302 attendu.
4. D - phpMyAdmin accessible : ✅ OK
   - HTTP 200 sur http://localhost:8291.
5. E - Base importée conforme : ✅ OK
   - 12 tables wpem_ détectées.
   - Volumes contrôlés : options 309, posts 31, users 2.
6. F - Options critiques cohérentes : ✅ OK
   - home/siteurl = http://localhost:8290.
   - template/stylesheet = em-site.
7. G - Identifiants admin attendus : ✅ OK (réappliqué après réimport)
   - admin-ellene
   - admin-tyson

## Clarification admin
1. L'URL /wp-admin/install.php affichée dans le navigateur ne révèle pas un nouvel install.
2. WordPress déjà installé : l'écran "Already Installed" est normal sur cette URL.
3. Parcours normal confirmé : /wp-admin -> redirection 302 vers /wp-login.php -> formulaire de login présent.

## Décision
1. Copie massive du thème annulée à la demande utilisateur (rollback ciblé sur le dossier thème uniquement).
2. Les documents de suivi sont conservés et mis à jour.
3. La suite se fait en copie sélective (whitelist) des seuls fichiers V4 nécessaires à la nouvelle arborescence cible.
4. Reset complet appliqué : thème `em-site` reconstruit depuis zéro avec fichiers vides uniquement selon l'arborescence cible.
