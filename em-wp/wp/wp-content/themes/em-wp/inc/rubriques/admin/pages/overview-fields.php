<?php
/**
 * Design system des champs/contrôles de l'admin Rubriques V4.
 *
 * SOURCE DE VÉRITÉ UNIQUE pour l'apparence de tous les inputs/selects/textarea
 * de l'interface. Émis AVANT overview-styles.php : ce dernier peut donc
 * surcharger ponctuellement (cas spéciaux « borderless »).
 *
 * Principes :
 * - tokens centralisés dans des variables CSS (--emff-*) ;
 * - base scoppée à :is(.em-v4-overview, .em-v4-builder), ciblée par TYPE ;
 * - la base pose `min-height` (jamais `height`) → cohabite avec les
 *   hauteurs/largeurs explicites déjà définies sans conflit ni rognage ;
 * - neutralisation propre des styles natifs WordPress (focus bleu, ombres) ;
 * - cas particuliers conservés : ancre (spécificité supérieure dans
 *   overview-styles), sélecteur de colonnes `.em-v4-rowcols`, et les types
 *   color/range/checkbox/radio/file/hidden (rendu natif intact).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    /* === Tokens (une seule source) ====================================== */
    .em-v4-overview, .em-v4-builder {
        --emff-min-h: 28px;
        --emff-pad-x: 8px;
        --emff-pad-y: 3px;
        --emff-fz: 12px;
        --emff-radius: 6px;
        --emff-ink: #1d2327;
        --emff-bg: #fcfcfd;
        --emff-bg-disabled: #f1f2f4;
        --emff-border: #d6dae0;
        --emff-border-hover: #c2c8d0;
        --emff-border-focus: #9aa4b1;
        --emff-placeholder: #aeb6bf;
        --emff-ring: 0 0 0 1px rgba(120,134,150,.28);
        --emff-transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    /* === Base commune : inputs texte + select + textarea ================ */
    :is(.em-v4-overview, .em-v4-builder) :is(
        input[type="text"],
        input[type="search"],
        input[type="url"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="number"]
    ),
    :is(.em-v4-overview, .em-v4-builder) select:not(.em-v4-rowcols),
    :is(.em-v4-overview, .em-v4-builder) textarea {
        box-sizing:border-box;
        min-height:var(--emff-min-h);
        margin:0;
        padding:var(--emff-pad-y) var(--emff-pad-x);
        font-size:var(--emff-fz);
        line-height:1.4;
        color:var(--emff-ink);
        background-color:var(--emff-bg);
        border:1px solid var(--emff-border);
        border-radius:var(--emff-radius);
        box-shadow:none;
        outline:0;
        transition:var(--emff-transition);
    }

    /* Hover sobre */
    :is(.em-v4-overview, .em-v4-builder) :is(
        input[type="text"], input[type="search"], input[type="url"],
        input[type="email"], input[type="tel"], input[type="password"],
        input[type="number"]
    ):hover,
    :is(.em-v4-overview, .em-v4-builder) select:not(.em-v4-rowcols):hover,
    :is(.em-v4-overview, .em-v4-builder) textarea:hover {
        border-color:var(--emff-border-hover);
    }

    /* Focus élégant (neutralise le halo bleu WordPress) */
    :is(.em-v4-overview, .em-v4-builder) :is(
        input[type="text"], input[type="search"], input[type="url"],
        input[type="email"], input[type="tel"], input[type="password"],
        input[type="number"]
    ):focus,
    :is(.em-v4-overview, .em-v4-builder) select:not(.em-v4-rowcols):focus,
    :is(.em-v4-overview, .em-v4-builder) textarea:focus {
        background-color:#fff;
        border-color:var(--emff-border-focus);
        box-shadow:var(--emff-ring);
        outline:0;
    }

    /* Placeholder atténué */
    :is(.em-v4-overview, .em-v4-builder) :is(
        input[type="text"], input[type="search"], input[type="url"],
        input[type="email"], input[type="tel"], input[type="password"],
        input[type="number"]
    )::placeholder,
    :is(.em-v4-overview, .em-v4-builder) textarea::placeholder {
        color:var(--emff-placeholder);
        opacity:1;
    }

    /* Désactivé */
    :is(.em-v4-overview, .em-v4-builder) :is(
        input[type="text"], input[type="search"], input[type="url"],
        input[type="email"], input[type="tel"], input[type="password"],
        input[type="number"]
    ):disabled,
    :is(.em-v4-overview, .em-v4-builder) select:not(.em-v4-rowcols):disabled,
    :is(.em-v4-overview, .em-v4-builder) textarea:disabled {
        background-color:var(--emff-bg-disabled);
        color:#8a929e;
        cursor:not-allowed;
    }

    /* === Variantes par usage ============================================ */
    /* select : place pour la flèche native + ne déborde jamais. */
    :is(.em-v4-overview, .em-v4-builder) select:not(.em-v4-rowcols) {
        max-width:100%;
        padding-right:24px;
        cursor:pointer;
    }
    /* number : valeurs courtes, lisibilité tabulaire. */
    :is(.em-v4-overview, .em-v4-builder) input[type="number"] {
        font-variant-numeric:tabular-nums;
    }

    /* === Helpers opt-in (réutilisables hors scope ci-dessus) ============ */
    /* Même famille visuelle pour du markup futur dans d'autres modules. */
    input.em-v4-input, select.em-v4-select, textarea.em-v4-input {
        box-sizing:border-box; min-height:28px; margin:0; padding:3px 8px;
        font-size:12px; line-height:1.4; color:#1d2327; background-color:#fcfcfd;
        border:1px solid #d6dae0; border-radius:6px; box-shadow:none; outline:0;
        transition:border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    input.em-v4-input:focus, select.em-v4-select:focus, textarea.em-v4-input:focus {
        background-color:#fff; border-color:#9aa4b1; box-shadow:0 0 0 1px rgba(120,134,150,.28); outline:0;
    }
    .em-v4-input--compact { min-height:24px; padding:1px 7px; }
</style>
<?php
