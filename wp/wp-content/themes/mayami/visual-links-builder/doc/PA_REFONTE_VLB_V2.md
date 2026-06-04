# PLAN D'ACTION REFONTE VLB — VERSION 2.0
**Date** : 2 juin 2026  
**Statut** : EN ATTENTE DE VALIDATION CLIENT

---

## SYNTHÈSE EXÉCUTIVE

### Contexte
Module Visual Links Builder (ex-EPK Builder) avec deux problèmes structurels :
1. **~300 références legacy EPK** dans code/options/hooks/CSS/JS
2. **Moteur email fragile** générant micro-slices (1-4px) → artefacts Gmail mobile

### Objectifs
1. **PHASE 1 (FONDATION)** : Migration complète EPK → Visual Links Builder
2. **PHASES 2-6 (OPTIMISATION)** : Refonte moteur email + validation

### Hiérarchie stratégique STRICTE
```
MODE PRINCIPAL (défaut)
└─> Image unique + HTML image-map
    ├─> Conditions OK → export image-map
    └─> Conditions KO → FALLBACK
        └─> Slicing simplifié robuste (repensé, PAS grille exhaustive)
            └─> Mitigations séparées
                ├─> YouTube preview (non bloquant)
                ├─> Logs/métadonnées (obligatoire)
                └─> Tests multi-clients (validation)
```

---

## POINT DE VIGILANCE CRITIQUE

### Contradiction historique à traiter

**Contexte historique** :  
Le document `Description_System_VLB_2026-06-01.md` mentionne qu'une tentative image-map avait été **jugée non fiable sur certains clients email, notamment Gmail mobile**.

**Position PA révisé** :  
- Image-map = **stratégie SOUS VALIDATION**, PAS vérité acquise
- Tests réels obligatoires AVANT adoption définitive
- Si échec validation → pivot vers fallback comme mode principal

**Garde-fous obligatoires** :
1. Fonction de décision déterministe avec seuils documentés
2. Fallback robuste totalement indépendant (pas variante grille actuelle)
3. Tests exhaustifs Gmail desktop/mobile + Outlook + Apple Mail + Thunderbird
4. Métadonnées export permettant audit post-mortem
5. Capacité de rollback rapide si régression

**Pourquoi retenter image-map** :
- HTML ultra-simple (1 image vs 10-50 slices)
- Robustesse théorique supérieure si critères respectés
- Tests modernes avec garde-fous stricts
- Fallback repensé en sécurité

**Conditions de validation** :
- Rendu visuel parfait Gmail desktop/mobile
- Cliquabilité 100% zones
- Pas d'artefacts nouveaux vs système actuel
- Comportement stable sur 5+ clients email testés

---

## PHASE 1 : MIGRATION EPK → VISUAL LINKS BUILDER

**Priorité** : CRITIQUE — Fondation avant toute refonte email  
**Durée estimée** : 2-3 jours  
**Validation** : Tests workflow complet après migration

### 1.1. Scope de migration

**~300 occurrences** réparties sur **10+ fichiers** :

#### Fichiers CRITIQUES (priorité 1)
- `inc/visual-links.php` (80+ occurrences) — Fonctions métier
- `inc/cmb2-config.php` (6+ IDs CMB2) — Configuration admin
- `functions.php` (20+ occurrences) — Actions AJAX/export

#### Fichiers IMPORTANTS (priorité 2)
- `assets/admin-visual-links-builder.js` (60+ occurrences) — Logique builder
- `assets/admin-visual-links-builder.css` (150+ occurrences) — Styles admin
- `assets/visual-links.css` (18 occurrences) — Styles front-end
- `visual-links-builder/visual-links-builder.html` (2 occurrences) — Builder standalone

#### Fichiers MINEURS (priorité 3)
- `assets/admin-nav.js` (5 occurrences)
- `assets/admin-nav.css` (3 occurrences)
- `template-parts/sections/visual-links.php` (classes CSS)

---

### 1.2. Mapping de migration

#### Options WordPress
| Ancien | Nouveau | Migration |
|--------|---------|-----------|
| `epk_draft_payload` | `visual_links_draft_payload` | Script ONE-TIME |
| `epk_published_payload` | `visual_links_published_payload` | Script ONE-TIME |
| `epk_validation_ready` | `visual_links_validation_ready` | Script ONE-TIME |
| `epk_draft_image_source` | `visual_links_draft_image_source` | Script ONE-TIME |

#### Fonctions PHP (pattern)
```
mayami_*_epk_*        →  mayami_*_visual_links_*
mayami_get_epk_*      →  mayami_get_visual_links_*
mayami_render_epk_*   →  mayami_render_visual_links_*
mayami_handle_*_epk_* →  mayami_handle_*_visual_links_*
```

#### Actions/Hooks WordPress
| Ancien | Nouveau |
|--------|---------|
| `cmb2_render_mayami_epk_builder` | `cmb2_render_mayami_visual_links_builder` |
| `admin_post_mayami_publish_epk_draft` | `admin_post_mayami_publish_visual_links_draft` |
| `admin_post_mayami_unpublish_epk` | `admin_post_mayami_unpublish_visual_links` |
| `wp_ajax_mayami_export_epk_html` | `wp_ajax_mayami_export_visual_links_html` |

#### Nonces
| Ancien | Nouveau |
|--------|---------|
| `mayami_epk_preview` | `mayami_visual_links_preview` |
| `mayami_publish_epk_draft` | `mayami_publish_visual_links_draft` |
| `mayami_unpublish_epk` | `mayami_unpublish_visual_links` |
| `mayami_epk_draft` | `mayami_visual_links_draft` |

#### IDs CMB2
| Ancien | Nouveau |
|--------|---------|
| `section_epk_title` | `section_visual_links_title` |
| `epk_draft_image_source` | `visual_links_draft_image_source` |
| `epk_builder` | `visual_links_builder` |
| `epk_validation_ready` | `visual_links_validation_ready` |
| `epk_draft_payload` | `visual_links_draft_payload` |
| `epk_published_payload` | `visual_links_published_payload` |

#### Classes CSS (pattern)
```
.mayami-epk-*         →  .mayami-vlb-*
[data-epk-*]          →  [data-vlb-*]
```

#### Paramètres GET
| Ancien | Nouveau |
|--------|---------|
| `mayami_preview=epk` | `mayami_preview=visual_links` |
| `mayami_epk_notice` | `mayami_vlb_notice` |

---

### 1.3. Script de migration ONE-TIME

**Fichier** : `inc/visual-links-migration.php` (NOUVEAU)

**Fonctionnalités** :
1. Migration automatique options WordPress (exécution unique)
2. Fallback de lecture temporaire anciennes clés (transition 1 mois)
3. Écriture EXCLUSIVE dans nouvelles clés
4. Flag `mayami_vlb_migration_done` pour éviter double exécution
5. Notice admin succès migration

**Pseudo-code** :
```php
function mayami_migrate_epk_to_visual_links_options() {
    $options = get_option('mayami_landing_options', []);
    $migrations = [
        'epk_draft_payload' => 'visual_links_draft_payload',
        'epk_published_payload' => 'visual_links_published_payload',
        'epk_validation_ready' => 'visual_links_validation_ready',
        'epk_draft_image_source' => 'visual_links_draft_image_source',
    ];
    
    foreach ($migrations as $old => $new) {
        if (isset($options[$old]) && !isset($options[$new])) {
            $options[$new] = $options[$old];
        }
    }
    
    update_option('mayami_landing_options', $options);
    update_option('mayami_vlb_migration_done', true);
}

// Hook admin_init une seule fois
add_action('admin_init', 'mayami_vlb_migration_handler');
```

**Fallback temporaire lecture** (dans fonctions get) :
```php
function mayami_get_visual_links_draft_payload() {
    $options = get_option('mayami_landing_options', []);
    
    // Priorité nouvelle clé
    if (!empty($options['visual_links_draft_payload'])) {
        return $options['visual_links_draft_payload'];
    }
    
    // Fallback ancienne clé (temporaire)
    if (!empty($options['epk_draft_payload'])) {
        error_log('[VLB] Fallback: lecture epk_draft_payload');
        return $options['epk_draft_payload'];
    }
    
    return mayami_get_visual_links_default_payload();
}
```

**IMPORTANT** : Toujours ÉCRIRE uniquement dans nouvelles clés.

---

### 1.4. Ordre d'exécution Phase 1

**Étape 1.1** : Créer script migration  
- Créer `inc/visual-links-migration.php`
- Tester sur copie base de données
- Valider migration réversible

**Étape 1.2** : Migrer PHP (priorité 1)  
- `inc/visual-links.php` : Renommer fonctions, hooks, nonces
- `inc/cmb2-config.php` : Renommer IDs CMB2
- `functions.php` : Renommer actions AJAX

**Étape 1.3** : Migrer JS (priorité 2)  
- `assets/admin-visual-links-builder.js` : Sélecteurs, actions, messages
- `assets/admin-nav.js` : Fonctions, sélecteurs

**Étape 1.4** : Migrer CSS (priorité 2)  
- `assets/admin-visual-links-builder.css` : Renommer classes
- `assets/visual-links.css` : Renommer classes
- `assets/admin-nav.css` : Renommer sélecteurs

**Étape 1.5** : Migrer HTML/Templates (priorité 3)  
- `visual-links-builder/visual-links-builder.html` : Paramètres URL, alias
- `template-parts/sections/visual-links.php` : Attributs data, classes

**Étape 1.6** : Nettoyer redirections legacy  
- Supprimer ou rediriger anciennes URLs admin
- Nettoyer options store inutiles

**Étape 1.7** : Tests post-migration  
- Workflow draft → preview → publish
- Export Template-HTML
- Export Template-Email
- Vérifier aucune régression

---

### 1.5. Livrables Phase 1

- [ ] Script `inc/visual-links-migration.php` fonctionnel
- [ ] ~300 références EPK migrées
- [ ] Fallback lecture temporaire actif
- [ ] Mapping ancien→nouveau documenté
- [ ] Tests workflow validés
- [ ] Aucune régression constatée

---

## PHASE 2 : MOTEUR EMAIL PRINCIPAL (IMAGE-MAP)

**Priorité** : HAUTE — Mode principal SOUS VALIDATION  
**Durée estimée** : 3-4 jours  
**Validation** : Tests Gmail desktop/mobile obligatoires

### 2.1. Principe

**Architecture** : Image unique + HTML `<img usemap>` + `<map><area>`

**Avantages théoriques** :
- HTML ultra-simple (1 image vs 10-50 slices)
- Rendu visuel parfait (pas de lignes inter-slices)
- Moins de requêtes serveur (1 upload vs multiples)
- Table HTML simplifiée

**Risques historiques** :
- Tentative précédente jugée non fiable Gmail mobile
- Coordonnées `<area>` fixes vs redimensionnement email client
- Support variable clients email

**Garde-fous** :
- Fonction de décision stricte (section 2.3)
- Fallback robuste obligatoire (Phase 3)
- Tests exhaustifs avant adoption (Phase 6)

---

### 2.2. Point d'injection

**Fichier** : `visual-links-builder/visual-links-builder.html`  
**Fonction** : `generateEmailTemplateFromPreviewData(previewData)`  
**Ligne approximative** : ~3145

**Code actuel** :
```javascript
async function generateEmailTemplateFromPreviewData(previewData) {
    const naturalSlices = calculateImageSlices(previewData.scaleX, previewData.scaleY);
    // ... suite logique slicing actuelle
}
```

**Code cible** :
```javascript
async function generateEmailTemplateFromPreviewData(previewData) {
    // NOUVEAU : Décision stratégie
    const strategy = decideEmailExportStrategy(previewData);
    
    console.log('[VLB Email Export] Stratégie choisie:', strategy.mode);
    if (strategy.reason) {
        console.log('[VLB Email Export] Raison:', strategy.reason);
    }
    
    // Branching
    if (strategy.mode === 'image-map') {
        return await generateEmailTemplateUsingImageMap(previewData, strategy);
    } else {
        return await generateEmailTemplateUsingFallback(previewData, strategy);
    }
}
```

---

### 2.3. Fonction de décision (DÉTERMINISTE)

**Fichier** : `visual-links-builder/visual-links-builder.html`  
**Fonction** : `decideEmailExportStrategy(previewData)` (NOUVELLE)

**Critères d'éligibilité image-map** :

#### Seuils configurables (à centraliser)
```javascript
const VLB_EMAIL_CONFIG = {
    IMAGE_MAP_MAX_ZONES: 15,           // Seuil nombre zones
    IMAGE_MAP_MIN_ZONE_WIDTH: 20,      // px après scale 600px
    IMAGE_MAP_MIN_ZONE_HEIGHT: 20,     // px après scale 600px
    IMAGE_MAP_MIN_ZONE_AREA: 400,      // px² après scale
    SLICING_GRID_SNAP: 4,              // px pour snapping
    SLICING_MIN_COL_WIDTH: 12,         // px colonnes
    SLICING_MIN_ROW_HEIGHT: 8,         // px lignes
    EMAIL_TARGET_WIDTH: 600            // px largeur email
};
```

#### Algorithme décision
```javascript
function decideEmailExportStrategy(previewData) {
    const zones = previewData.zones || [];
    const zoneCount = zones.length;
    const emailWidth = VLB_EMAIL_CONFIG.EMAIL_TARGET_WIDTH;
    const scaleX = emailWidth / previewData.naturalWidth;
    const scaleY = scaleX; // Proportionnel
    
    // Critère 1 : Nombre de zones
    if (zoneCount > VLB_EMAIL_CONFIG.IMAGE_MAP_MAX_ZONES) {
        return {
            mode: 'fallback',
            reason: `Trop de zones (${zoneCount} > ${VLB_EMAIL_CONFIG.IMAGE_MAP_MAX_ZONES})`
        };
    }
    
    // Critère 2 : Taille minimale zones après scale
    for (const zone of zones) {
        const scaledW = zone.width * scaleX;
        const scaledH = zone.height * scaleY;
        const scaledArea = scaledW * scaledH;
        
        if (scaledW < VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_WIDTH) {
            return {
                mode: 'fallback',
                reason: `Zone trop étroite (${scaledW.toFixed(1)}px < ${VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_WIDTH}px)`
            };
        }
        
        if (scaledH < VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_HEIGHT) {
            return {
                mode: 'fallback',
                reason: `Zone trop haute (${scaledH.toFixed(1)}px < ${VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_HEIGHT}px)`
            };
        }
        
        if (scaledArea < VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_AREA) {
            return {
                mode: 'fallback',
                reason: `Zone trop petite (${scaledArea.toFixed(0)}px² < ${VLB_EMAIL_CONFIG.IMAGE_MAP_MIN_ZONE_AREA}px²)`
            };
        }
    }
    
    // Critère 3 : Proximité excessive (optionnel)
    // Détecter zones trop proches risquant confusion clic
    // ...
    
    // Critère 4 : Superpositions incohérentes (optionnel)
    // Détecter zones qui se chevauchent
    // ...
    
    // Toutes conditions OK → image-map
    return {
        mode: 'image-map',
        reason: null
    };
}
```

**Important** : Règles métier documentées, seuils justifiés, décision auditable.

---

### 2.4. Génération export image-map

**Fonction** : `generateEmailTemplateUsingImageMap(previewData, strategy)` (NOUVELLE)

**Étapes** :
1. Redimensionner image source vers 600px
2. Créer blob PNG de l'image complète
3. Uploader image unique (réutiliser `uploadEmailSlice`)
4. Calculer coordonnées `<area>` à partir zones
5. Générer HTML image-map
6. Exporter via `exportHtmlFromBuilder`

**Pseudo-code** :
```javascript
async function generateEmailTemplateUsingImageMap(previewData, strategy) {
    const emailWidth = VLB_EMAIL_CONFIG.EMAIL_TARGET_WIDTH;
    const scaleX = emailWidth / previewData.naturalWidth;
    const scaleY = scaleX;
    const emailHeight = Math.round(previewData.naturalHeight * scaleY);
    
    // 1. Redimensionner image
    const canvas = document.createElement('canvas');
    canvas.width = emailWidth;
    canvas.height = emailHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(previewData.sourceImage, 0, 0, emailWidth, emailHeight);
    
    // 2. Créer blob
    const blob = await new Promise(resolve => {
        canvas.toBlob(resolve, 'image/png', 0.9);
    });
    
    // 3. Uploader image unique
    const uploadResult = await uploadEmailSlice(
        blob,
        'visual-links-full.png',
        previewData.exportSubdir,
        'template-email'
    );
    
    // 4. Calculer areas
    const areas = previewData.zones.map(zone => {
        const x1 = Math.round(zone.x * scaleX);
        const y1 = Math.round(zone.y * scaleY);
        const x2 = Math.round((zone.x + zone.width) * scaleX);
        const y2 = Math.round((zone.y + zone.height) * scaleY);
        
        return {
            coords: `${x1},${y1},${x2},${y2}`,
            href: resolveZoneHref(zone),
            alt: zone.label || zone.title || ''
        };
    });
    
    // 5. Générer HTML
    const htmlDocument = buildImageMapEmailTemplate({
        imageSrc: uploadResult.url,
        imageWidth: emailWidth,
        imageHeight: emailHeight,
        areas: areas,
        metadata: {
            strategy: 'image-map',
            zoneCount: previewData.zones.length,
            exportDate: new Date().toISOString(),
            motorVersion: '2.0'
        }
    });
    
    // 6. Exporter
    return await exportHtmlFromBuilder(
        htmlDocument,
        previewData.draftName,
        previewData.draftId,
        previewData.exportSubdir,
        'template-email'
    );
}
```

---

### 2.5. Template HTML image-map

**Fonction** : `buildImageMapEmailTemplate(params)` (NOUVELLE)

**Structure HTML cible** :
```html
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Visual Links</title>
  <meta name="vlb-strategy" content="image-map">
  <meta name="vlb-version" content="2.0">
  <meta name="vlb-export-date" content="{exportDate}">
  <meta name="vlb-zone-count" content="{zoneCount}">
</head>
<body style="margin:0;padding:0;">
  <center>
    <img src="{imageSrc}" 
         width="{imageWidth}" 
         height="{imageHeight}" 
         usemap="#vlb-map" 
         border="0" 
         alt="Visual Links"
         style="display:block;max-width:100%;height:auto;">
    <map name="vlb-map">
      <area shape="rect" coords="{x1},{y1},{x2},{y2}" href="{href}" alt="{alt}">
      <!-- ... autres areas -->
    </map>
  </center>
</body>
</html>
```

**Implémentation** :
```javascript
function buildImageMapEmailTemplate(params) {
    const { imageSrc, imageWidth, imageHeight, areas, metadata } = params;
    
    const areasHtml = areas.map(area => 
        `<area shape="rect" coords="${area.coords}" href="${area.href}" alt="${escapeHtml(area.alt)}">`
    ).join('\n      ');
    
    return `<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Visual Links</title>
  <meta name="vlb-strategy" content="${metadata.strategy}">
  <meta name="vlb-version" content="${metadata.motorVersion}">
  <meta name="vlb-export-date" content="${metadata.exportDate}">
  <meta name="vlb-zone-count" content="${metadata.zoneCount}">
</head>
<body style="margin:0;padding:0;">
  <center>
    <img src="${imageSrc}" 
         width="${imageWidth}" 
         height="${imageHeight}" 
         usemap="#vlb-map" 
         border="0" 
         alt="Visual Links"
         style="display:block;max-width:100%;height:auto;">
    <map name="vlb-map">
      ${areasHtml}
    </map>
  </center>
</body>
</html>`;
}
```

---

### 2.6. Livrables Phase 2

- [ ] Fonction `decideEmailExportStrategy()` implémentée
- [ ] Seuils configurables centralisés
- [ ] Fonction `generateEmailTemplateUsingImageMap()` implémentée
- [ ] Template HTML image-map validé
- [ ] Métadonnées export intégrées
- [ ] Logs console clairs
- [ ] Tests basiques OK

---

## PHASE 3 : FALLBACK ROBUSTE (SLICING SIMPLIFIÉ)

**Priorité** : HAUTE — Sécurité si image-map KO  
**Durée estimée** : 4-5 jours  
**Validation** : Comparaison avec système actuel obligatoire

### 3.1. Principe

**INTERDIT** : Conserver logique grille exhaustive actuelle  
**OBJECTIF** : Repenser moteur slicing pour robustesse email

**Différences vs système actuel** :
| Système actuel | Fallback repensé |
|----------------|------------------|
| Grille complète cellule par cellule | Bandes horizontales prioritaires |
| Micro-slices 1-4px acceptées | Élimination stricte < 12px colonnes |
| Aucune fusion adjacentes | Fusion agressive segments compatibles |
| Coordonnées brutes | Snapping grille 4px |
| Table complexe | Table simplifiée |

**Philosophie** :  
Privilégier la robustesse sur la fidélité pixel-perfect.

---

### 3.2. Architecture fallback

**Fonction** : `generateEmailTemplateUsingFallback(previewData, strategy)` (NOUVELLE)

**Étapes** :
1. Calculer slices SIMPLIFIÉES (pas `calculateImageSlices` actuel)
2. Snapper coordonnées sur grille 4px
3. Éliminer micro-colonnes/lignes
4. Fusionner slices adjacentes même lien
5. Redimensionner vers 600px
6. Uploader slices
7. Générer HTML table simplifié
8. Exporter

**Pseudo-code** :
```javascript
async function generateEmailTemplateUsingFallback(previewData, strategy) {
    // 1. Calculer slices simplifiées (NOUVELLE fonction)
    const simplifiedSlices = calculateSimplifiedSlices(previewData);
    
    console.log('[VLB Fallback] Slices avant simplification:', previewData.zones.length);
    console.log('[VLB Fallback] Slices après simplification:', simplifiedSlices.length);
    
    // 2. Redimensionner vers 600px
    const emailWidth = VLB_EMAIL_CONFIG.EMAIL_TARGET_WIDTH;
    const emailHeight = Math.round(emailWidth * previewData.naturalHeight / previewData.naturalWidth);
    
    const scaledSlices = buildScaledEmailSlicesFromNaturalSlices(
        simplifiedSlices,
        previewData.naturalWidth,
        previewData.naturalHeight,
        emailWidth,
        emailHeight
    );
    
    // 3. Uploader slices
    const uploadedSlices = await uploadAllEmailSlices(
        scaledSlices,
        previewData.sourceCanvas,
        previewData.exportSubdir
    );
    
    // 4. Générer HTML table
    const htmlDocument = buildSlicedEmailTemplateHtmlDocument(
        emailWidth,
        uploadedSlices,
        {
            strategy: 'fallback-simplified',
            reason: strategy.reason,
            sliceCount: uploadedSlices.length,
            exportDate: new Date().toISOString(),
            motorVersion: '2.0'
        }
    );
    
    // 5. Exporter
    return await exportHtmlFromBuilder(
        htmlDocument,
        previewData.draftName,
        previewData.draftId,
        previewData.exportSubdir,
        'template-email'
    );
}
```

---

### 3.3. Calcul slices simplifiées

**Fonction** : `calculateSimplifiedSlices(previewData)` (NOUVELLE)

**Algorithme** :
1. Extraire coordonnées X/Y zones
2. **Snapper** sur grille 4px
3. Trier et dédupliquer
4. **Éliminer** coordonnées créant colonnes < 12px
5. **Éliminer** coordonnées créant lignes < 8px
6. Construire grille simplifiée
7. Pour chaque cellule : détecter zone intersectée
8. **Fusionner** cellules adjacentes horizontales même lien
9. Retourner slices simplifiées

**Implémentation** :
```javascript
function calculateSimplifiedSlices(previewData) {
    const zones = previewData.zones || [];
    const width = previewData.naturalWidth;
    const height = previewData.naturalHeight;
    
    // 1. Extraire coordonnées et snapper
    let xCoords = [0, width];
    let yCoords = [0, height];
    
    zones.forEach(zone => {
        xCoords.push(snapToGrid(zone.x, VLB_EMAIL_CONFIG.SLICING_GRID_SNAP));
        xCoords.push(snapToGrid(zone.x + zone.width, VLB_EMAIL_CONFIG.SLICING_GRID_SNAP));
        yCoords.push(snapToGrid(zone.y, VLB_EMAIL_CONFIG.SLICING_GRID_SNAP));
        yCoords.push(snapToGrid(zone.y + zone.height, VLB_EMAIL_CONFIG.SLICING_GRID_SNAP));
    });
    
    // 2. Trier et dédupliquer
    xCoords = [...new Set(xCoords)].sort((a, b) => a - b);
    yCoords = [...new Set(yCoords)].sort((a, b) => a - b);
    
    // 3. Éliminer micro-colonnes
    xCoords = eliminateThinColumns(xCoords, VLB_EMAIL_CONFIG.SLICING_MIN_COL_WIDTH);
    
    // 4. Éliminer micro-lignes
    yCoords = eliminateThinRows(yCoords, VLB_EMAIL_CONFIG.SLICING_MIN_ROW_HEIGHT);
    
    // 5. Construire slices
    const slices = [];
    for (let i = 0; i < yCoords.length - 1; i++) {
        for (let j = 0; j < xCoords.length - 1; j++) {
            const slice = {
                x: xCoords[j],
                y: yCoords[i],
                width: xCoords[j + 1] - xCoords[j],
                height: yCoords[i + 1] - yCoords[i],
                link: null,
                zone: null
            };
            
            // Détecter zone intersectée
            const intersected = findIntersectingZone(slice, zones);
            if (intersected) {
                slice.link = resolveZoneHref(intersected);
                slice.zone = intersected;
            }
            
            slices.push(slice);
        }
    }
    
    // 6. Fusionner slices adjacentes
    return mergeAdjacentSlicesHorizontally(slices);
}

function snapToGrid(value, grid) {
    return Math.round(value / grid) * grid;
}

function eliminateThinColumns(xCoords, minWidth) {
    const filtered = [xCoords[0]];
    for (let i = 1; i < xCoords.length; i++) {
        const width = xCoords[i] - filtered[filtered.length - 1];
        if (width >= minWidth || i === xCoords.length - 1) {
            filtered.push(xCoords[i]);
        }
    }
    return filtered;
}

function eliminateThinRows(yCoords, minHeight) {
    const filtered = [yCoords[0]];
    for (let i = 1; i < yCoords.length; i++) {
        const height = yCoords[i] - filtered[filtered.length - 1];
        if (height >= minHeight || i === yCoords.length - 1) {
            filtered.push(yCoords[i]);
        }
    }
    return filtered;
}

function mergeAdjacentSlicesHorizontally(slices) {
    // Grouper par ligne (y + height)
    const rows = {};
    slices.forEach(slice => {
        const rowKey = `${slice.y}_${slice.height}`;
        if (!rows[rowKey]) rows[rowKey] = [];
        rows[rowKey].push(slice);
    });
    
    // Fusionner dans chaque ligne
    const merged = [];
    Object.values(rows).forEach(row => {
        row.sort((a, b) => a.x - b.x);
        
        let current = row[0];
        for (let i = 1; i < row.length; i++) {
            const next = row[i];
            
            // Même lien ET adjacentes → fusionner
            if (current.link === next.link && current.x + current.width === next.x) {
                current.width += next.width;
            } else {
                merged.push(current);
                current = next;
            }
        }
        merged.push(current);
    });
    
    return merged;
}
```

---

### 3.4. Comparaison système actuel vs fallback

**Objectif** : Validation amélioration réelle

**Métriques à comparer** :
- Nombre de slices générées
- Nombre de micro-slices (<12px large)
- Complexité table HTML (lignes × colonnes max)
- Poids total images
- Rendu Gmail mobile (visuel)

**Cas de test comparatif** :
- Visuel type 1200×800px avec 8 zones musique
- Export actuel vs export fallback
- Compter slices avant/après
- Vérifier rendu Gmail

**Critère de succès** :  
Réduction ≥50% du nombre de slices, élimination totale micro-slices <12px.

---

### 3.5. Livrables Phase 3

- [ ] Fonction `calculateSimplifiedSlices()` implémentée
- [ ] Snapping grille 4px fonctionnel
- [ ] Élimination micro-colonnes/lignes OK
- [ ] Fusion adjacentes OK
- [ ] Fonction `generateEmailTemplateUsingFallback()` implémentée
- [ ] Comparaison avec système actuel documentée
- [ ] Réduction slices ≥50% validée
- [ ] Tests Gmail mobile OK

---

## PHASE 4 : MITIGATION YOUTUBE PREVIEW

**Priorité** : MOYENNE — Non bloquant refonte moteur  
**Durée estimée** : 1 jour  
**Validation** : Tests avec lien YouTube présent

### 4.1. Contexte

**Problème** :  
Gmail Desktop peut injecter automatiquement une preview card YouTube si URL détectée dans l'email. Comportement hors contrôle du builder.

**Constatation historique** :  
Document `Description_System_VLB_2026-06-01.md` mentionne l'apparition de previews YouTube non souhaitées.

---

### 4.2. Actions possibles

#### Audit placement URLs YouTube
- Vérifier où les URLs YouTube apparaissent dans HTML/TXT généré
- Identifier si exposées hors strict `href` des liens

#### Mitigation
- URLs YouTube UNIQUEMENT dans attributs `href` (liens cliquables)
- NE PAS les mettre en texte visible
- NE PAS les mettre dans `<title>`, `<meta>`, commentaires HTML
- Éviter duplication URL en attributs `alt` si possible

#### Documentation
- Informer cliente que comportement Gmail preview reste partiellement hors contrôle
- Documenter ce qui a été fait et ce qui reste impossible à maîtriser

---

### 4.3. Implémentation

**Fonction** : `resolveZoneHref(zone)` (existante, audit)

Vérifier que cette fonction retourne uniquement URL brute pour usage dans `href`, sans exposition ailleurs.

**Template HTML** : Audit complet

Vérifier que les templates image-map et fallback n'exposent pas URLs YouTube hors balises `<a href>` et `<area href>`.

---

### 4.4. Livrables Phase 4

- [ ] Audit URLs YouTube dans templates HTML
- [ ] URLs uniquement dans `href`
- [ ] Aucune exposition texte visible
- [ ] Documentation limites Gmail
- [ ] Tests avec lien YouTube validés

---

## PHASE 5 : LOGS, MÉTADONNÉES, TRAÇABILITÉ

**Priorité** : HAUTE — Obligatoire pour audit  
**Durée estimée** : 1-2 jours  
**Validation** : Capacité audit post-export

### 5.1. Métadonnées export HTML

**Balises `<meta>` obligatoires** :
```html
<meta name="vlb-strategy" content="image-map|fallback-simplified">
<meta name="vlb-version" content="2.0">
<meta name="vlb-export-date" content="2026-06-02T14:30:00Z">
<meta name="vlb-zone-count" content="8">
<meta name="vlb-slice-count" content="12"> <!-- si fallback -->
<meta name="vlb-fallback-reason" content="..."> <!-- si fallback -->
```

**Avantages** :
- Audit post-mortem fichier HTML
- Comprendre pourquoi telle stratégie choisie
- Détecter exports anciens vs nouveaux (version 2.0)
- Tracer évolution du moteur

---

### 5.2. Logs console développeur

**Console logs obligatoires** :
```javascript
console.log('[VLB Email Export] Stratégie choisie: image-map');
console.log('[VLB Email Export] Raison fallback: Zone trop étroite (18.5px < 20px)');
console.log('[VLB Fallback] Slices avant simplification: 45');
console.log('[VLB Fallback] Slices après simplification: 18');
console.log('[VLB Export] Upload image unique: visual-links-full.png');
console.log('[VLB Export] Export terminé: template-email/index.html');
```

**Format standardisé** : `[VLB <Module>] <Message>`

---

### 5.3. État export admin (optionnel)

**Ajout UI possible** :  
Afficher dans interface admin la stratégie du dernier export.

**Exemple** :
```
Dernier export email : 2 juin 2026 14:30
Stratégie : Image-map
Zones : 8
Assets : 1 image
```

**Priorité** : Basse (optionnel, amélioration future)

---

### 5.4. Fichier log serveur (optionnel)

**Stockage** : `visual-links-builder/exports-html/<name>/export.log`

**Contenu** :
```json
{
  "strategy": "image-map",
  "version": "2.0",
  "exportDate": "2026-06-02T14:30:00Z",
  "zoneCount": 8,
  "sliceCount": 1,
  "fallbackReason": null,
  "imageWidth": 600,
  "imageHeight": 400
}
```

**Priorité** : Moyenne (utile pour audit long terme)

---

### 5.5. Livrables Phase 5

- [ ] Métadonnées `<meta>` implémentées
- [ ] Logs console standardisés
- [ ] Fichier log JSON optionnel créé
- [ ] Documentation format métadonnées
- [ ] Capacité audit post-export validée

---

## PHASE 6 : TESTS ET VALIDATION MULTI-CLIENTS

**Priorité** : CRITIQUE — Validation avant adoption  
**Durée estimée** : 3-5 jours  
**Validation** : Matrice de tests complète

### 6.1. Matrice de tests

**Clients email cibles** :
- Gmail Desktop (Chrome, Firefox)
- Gmail Mobile (Android, iOS)
- Outlook Desktop (Windows)
- Outlook.com (web)
- Apple Mail (macOS, iOS)
- Thunderbird

**Cas de test** :

#### Cas 1 : Visuel simple (2-3 zones)
- **Stratégie attendue** : Image-map
- **Validation** :
  - Rendu visuel parfait
  - Cliquabilité 100% zones
  - Aucun artefact
  - Tous clients email OK

#### Cas 2 : Visuel moyen (8-10 zones plateformes musique)
- **Stratégie attendue** : Image-map (si zones >20px)
- **Validation** :
  - Rendu visuel parfait
  - Cliquabilité toutes zones
  - Comparaison vs système actuel
  - Tous clients email OK

#### Cas 3 : Lien YouTube présent
- **Stratégie attendue** : Image-map ou Fallback selon zones
- **Validation** :
  - Rendu visuel OK
  - Cliquabilité OK
  - Preview YouTube observée (Gmail) → documentée
  - Comportement acceptable

#### Cas 4 : Visuel complexe (>15 zones OU zones <20px)
- **Stratégie attendue** : Fallback
- **Validation** :
  - Rendu visuel propre (pas micro-slices)
  - Cliquabilité zones principales OK
  - Meilleur que système actuel
  - Gmail mobile rendu amélioré

#### Cas 5 : Export après purge
- **Validation** :
  - Anciens fichiers supprimés
  - Nouveaux fichiers créés
  - HTML + TXT présents
  - Métadonnées correctes

#### Cas 6 : Draft legacy migré
- **Validation** :
  - Lecture ancienne clé OK (fallback)
  - Écriture nouvelle clé OK
  - Workflow complet OK
  - Aucune régression

---

### 6.2. Grille validation

| Cas | Gmail Desktop | Gmail Mobile | Outlook Desktop | Outlook.com | Apple Mail | Thunderbird | Statut |
|-----|---------------|--------------|-----------------|-------------|------------|-------------|--------|
| Cas 1 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| Cas 2 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| Cas 3 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| Cas 4 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| Cas 5 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| Cas 6 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |

**Critère de succès** :  
✅ sur TOUS les clients pour Cas 1, 2, 5, 6  
✅ sur Gmail + Outlook pour Cas 3, 4

---

### 6.3. Procédure test

**Pour chaque cas** :
1. Créer draft VLB avec données test
2. Exporter Template-Email
3. Vérifier stratégie choisie (logs console)
4. Vérifier métadonnées HTML
5. Envoyer email test à comptes clients email
6. Vérifier rendu visuel (captures écran)
7. Tester cliquabilité chaque zone
8. Documenter écarts éventuels

---

### 6.4. Critères validation finale

**Image-map (MODE PRINCIPAL)** :
- ✅ Rendu parfait Gmail desktop ET mobile
- ✅ Cliquabilité 100% zones
- ✅ Aucun artefact nouveau vs système actuel
- ✅ Support ≥4 clients email sur 6 testés

**Si échec validation image-map** :
- ❌ Pivot vers fallback comme mode principal
- 🔄 Réécrire fonction décision (toujours retourner fallback)
- 📝 Documenter raisons échec

**Fallback** :
- ✅ Réduction ≥50% slices vs système actuel
- ✅ Élimination micro-slices <12px
- ✅ Rendu Gmail mobile amélioré
- ✅ Aucune régression fonctionnelle

---

### 6.5. Livrables Phase 6

- [ ] Matrice de tests complétée
- [ ] Captures écran tous clients
- [ ] Rapport écarts documentés
- [ ] Décision finale image-map validée OU pivot fallback
- [ ] Validation cliente obtenue

---

## RÉCAPITULATIF FICHIERS MODIFIÉS

### Fichiers PHP

1. **`inc/visual-links-migration.php`** (NOUVEAU)
   - Script migration ONE-TIME
   - Fallback lecture temporaire

2. **`inc/visual-links.php`** (MODIFICATIONS MASSIVES)
   - Renommer fonctions : `mayami_*_epk_*` → `mayami_*_visual_links_*`
   - Renommer hooks/actions
   - Renommer nonces
   - Renommer clés options

3. **`inc/cmb2-config.php`** (MODIFICATIONS IMPORTANTES)
   - Renommer IDs CMB2
   - Mettre à jour labels UI

4. **`functions.php`** (MODIFICATIONS IMPORTANTES)
   - Renommer `mayami_ajax_export_epk_html` → `mayami_ajax_export_visual_links_html`
   - Renommer actions AJAX
   - Nettoyer redirections legacy

5. **`template-parts/sections/visual-links.php`** (MODIFICATIONS MINEURES)
   - Renommer attributs data
   - Renommer classes CSS

---

### Fichiers JavaScript

1. **`visual-links-builder/visual-links-builder.html`** (MODIFICATIONS CRITIQUES)
   - **AJOUTER** : Config `VLB_EMAIL_CONFIG`
   - **AJOUTER** : Fonction `decideEmailExportStrategy()`
   - **AJOUTER** : Fonction `generateEmailTemplateUsingImageMap()`
   - **AJOUTER** : Fonction `buildImageMapEmailTemplate()`
   - **AJOUTER** : Fonction `generateEmailTemplateUsingFallback()`
   - **AJOUTER** : Fonction `calculateSimplifiedSlices()`
   - **AJOUTER** : Fonctions helpers (snap, eliminate, merge)
   - **MODIFIER** : Fonction `generateEmailTemplateFromPreviewData()` → branching
   - **RENOMMER** : Paramètre URL `epk_draft_id` → `visual_links_draft_id`
   - **RENOMMER** : Alias `mayamiExportEpkHtmlFromOpener`

2. **`assets/admin-visual-links-builder.js`** (MODIFICATIONS IMPORTANTES)
   - Renommer sélecteurs CMB2
   - Renommer classes
   - Renommer attributs data
   - Renommer actions AJAX
   - Mettre à jour messages UI

3. **`assets/admin-nav.js`** (MODIFICATIONS MINEURES)
   - Renommer fonction `hideLandingEpkSection`
   - Renommer sélecteurs

---

### Fichiers CSS

1. **`assets/admin-visual-links-builder.css`** (MODIFICATIONS MASSIVES)
   - Renommer : `.mayami-epk-*` → `.mayami-vlb-*`
   - ~150 occurrences

2. **`assets/visual-links.css`** (MODIFICATIONS IMPORTANTES)
   - Renommer : `.mayami-epk-*` → `.mayami-vlb-*`
   - Renommer : `[data-epk-preview]` → `[data-vlb-preview]`
   - 18 occurrences

3. **`assets/admin-nav.css`** (MODIFICATIONS MINEURES)
   - Renommer : `.cmb2-id-section-epk-title` → `.cmb2-id-section-visual-links-title`
   - 3 occurrences

---

## ORDRE D'EXÉCUTION GLOBAL

```
PHASE 1 — Migration EPK → VLB (2-3 jours)
├─ 1.1. Créer script migration
├─ 1.2. Migrer PHP (priorité 1)
├─ 1.3. Migrer JS (priorité 2)
├─ 1.4. Migrer CSS (priorité 2)
├─ 1.5. Migrer HTML/Templates (priorité 3)
├─ 1.6. Nettoyer legacy
└─ 1.7. Tests post-migration
    └─ ✅ VALIDATION AVANT PHASE 2

PHASE 2 — Moteur email principal (3-4 jours)
├─ 2.1. Créer config centralisée
├─ 2.2. Implémenter fonction décision
├─ 2.3. Implémenter génération image-map
├─ 2.4. Implémenter template HTML
├─ 2.5. Intégrer métadonnées
└─ 2.6. Tests basiques
    └─ ⚠️ VALIDATION PARTIELLE (tests complets Phase 6)

PHASE 3 — Fallback robuste (4-5 jours)
├─ 3.1. Implémenter calcul slices simplifiées
├─ 3.2. Implémenter snapping/élimination/fusion
├─ 3.3. Implémenter génération fallback
├─ 3.4. Comparaison vs système actuel
└─ 3.5. Tests basiques
    └─ ⚠️ VALIDATION PARTIELLE

PHASE 4 — Mitigation YouTube (1 jour)
├─ 4.1. Audit URLs YouTube
├─ 4.2. Mitigation exposition
├─ 4.3. Documentation limites
└─ 4.4. Tests avec YouTube
    └─ ✅ VALIDATION

PHASE 5 — Logs/Métadonnées (1-2 jours)
├─ 5.1. Implémenter métadonnées HTML
├─ 5.2. Standardiser logs console
├─ 5.3. Fichier log JSON (optionnel)
└─ 5.4. Documentation
    └─ ✅ VALIDATION

PHASE 6 — Tests multi-clients (3-5 jours)
├─ 6.1. Préparer matrice tests
├─ 6.2. Exécuter 6 cas sur 6 clients
├─ 6.3. Documenter résultats
├─ 6.4. Décision finale image-map OU pivot fallback
└─ 6.5. Validation cliente
    └─ ✅ VALIDATION FINALE
```

**Durée totale estimée** : 14-20 jours

---

## RISQUES ET MITIGATIONS

### Risque 1 : Casse drafts existants
**Probabilité** : Moyenne  
**Impact** : Élevé  
**Mitigation** :
- Script migration testé sur copie base
- Fallback lecture temporaire (1 mois)
- Tests exhaustifs post-migration
- Sauvegarde base avant déploiement

---

### Risque 2 : Image-map échoue validation Gmail mobile
**Probabilité** : Moyenne (historique échec précédent)  
**Impact** : Moyen  
**Mitigation** :
- Fallback robuste implémenté en parallèle
- Fonction décision stricte avec seuils conservateurs
- Tests multi-clients obligatoires
- Pivot rapide vers fallback si nécessaire
- Pas de déploiement mode principal sans validation réelle

---

### Risque 3 : Régression fonctionnelle workflow
**Probabilité** : Faible  
**Impact** : Élevé  
**Mitigation** :
- Tests workflow complet après chaque phase
- Validation draft → preview → publish → export
- Comparaison avant/après méthodique
- Rollback possible si régression

---

### Risque 4 : Incompatibilité clients email fallback
**Probabilité** : Faible  
**Impact** : Moyen  
**Mitigation** :
- Tests sur 6 clients email minimum
- Fallback conçu robustesse email (tables simples)
- Pas de CSS complexe
- Structure HTML conservatrice

---

### Risque 5 : Surcharge développement
**Probabilité** : Moyenne  
**Impact** : Moyen (délais)  
**Mitigation** :
- Phases séquencées et validées une par une
- Livrables clairs par phase
- Priorisation stricte (Phase 1 avant tout)
- Points de validation cliente réguliers

---

## CHECKLIST VALIDATION FINALE

### Migration EPK → VLB
- [ ] 0 référence `epk`/`EPK` dans code (hors commentaires historiques)
- [ ] Migration options WordPress fonctionnelle
- [ ] Fallback lecture temporaire OK
- [ ] Workflow draft → preview → publish OK
- [ ] Aucune régression workflow
- [ ] Aucune erreur PHP/JS console

---

### Moteur email
- [ ] Fonction décision implémentée et testée
- [ ] Mode image-map fonctionnel
- [ ] Mode fallback fonctionnel
- [ ] Métadonnées export présentes
- [ ] Logs console clairs
- [ ] Export HTML + TXT OK

---

### Tests multi-clients
- [ ] Matrice 6 cas × 6 clients complétée
- [ ] Gmail desktop validé
- [ ] Gmail mobile validé
- [ ] Outlook validé
- [ ] Apple Mail validé (optionnel si non dispo)
- [ ] Captures écran documentées
- [ ] Rapport écarts documenté

---

### Validation image-map (CRITIQUE)
- [ ] Rendu Gmail desktop parfait
- [ ] Rendu Gmail mobile parfait
- [ ] Cliquabilité 100% zones
- [ ] Aucun artefact nouveau
- [ ] Support ≥4/6 clients testés
- [ ] **OU** décision pivot vers fallback documentée

---

### Documentation
- [ ] Mapping ancien→nouveau complet
- [ ] Script migration documenté
- [ ] Fonctions email documentées
- [ ] Seuils décision documentés
- [ ] Limites connues documentées
- [ ] Procédure test documentée

---

## DÉCISION FINALE REQUISE

**Ce PA révisé attend validation cliente avant exécution.**

**Points de validation spécifiques** :
1. ✅ Hiérarchie stratégique acceptée (image-map principal, fallback sécurité)
2. ✅ Traitement contradiction historique acceptable
3. ✅ Séquencement phases validé
4. ✅ Durée estimée acceptable (14-20 jours)
5. ✅ Risques/mitigations compris
6. ✅ Critères validation finale clairs

**Prochaine étape après validation** :  
Démarrage PHASE 1 — Migration EPK → VLB

---

**FIN DU PLAN D'ACTION RÉVISÉ V2.0**
