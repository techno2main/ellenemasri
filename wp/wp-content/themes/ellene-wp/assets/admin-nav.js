/**

 * Admin Navigation Sticky - Ellene Landing

 */

(function() {

  'use strict';



  const SECTION_IDS = [

    'section_modules_title',

    'section_top_bar_title',

    'section_hero_title',

    'section_slider_title',

    'section_stream_title',

    'section_social_title',

    'section_video_title',

    'section_release_title',

    'section_cta_title',

    'section_footer_title'

  ];



  const TOP_BAR_ITEM_TITLES = [

    'Titre Single',

    'CTA central',

    'Baseline'

  ];



  const SECTION_HELP_TEXTS = {

    section_modules_title: 'Tu choisis les rubriques que tu veux afficher ou masquer et leur ordre d\'affichage [→ RUBRIQUES].',

    section_top_bar_title: 'C\'est le menu de navigation de ta page. Il est fixe en haut de page et visible en permanence [→ TOP-BAR / HEADER].',

    section_hero_title: 'C\'est la section d\'accroche principale de ta page [→ HERO].',

    section_slider_title: 'C\'est le slider dynamique dans ta section d\'accroche [→ SLIDER HERO].',

    section_stream_title: 'C\'est la section qui affiche tes plateformes de streaming [→ 01/LISTEN].',

    section_social_title: 'C\'est la section qui affiche tes réseaux sociaux [→ 02/FOLLOW].',

    section_video_title: 'C\'est la section qui met en avant une vidéo [→ 03/WATCH].',

    section_release_title: 'C\'est la section qui met en avant le dernier album ou single sorti [→ 04/RELEASE INFOS].',

    section_cta_title: 'C\'est la section qui permet de faire un appel à l\'action clair vers une page de ton site ou un lien externe [→ 05/DON\'T SLEEP ON IT].',

    section_footer_title: 'C\'est la section qui permet de gérer les informations et liens présents en bas de page [→ FOOTER].'

  };

  const SECTION_ACCENT_BY_CLASS = {
    'cmb2-id-section-modules-title': '#4b5563',
    'cmb2-id-section-top-bar-title': 'oklch(0.15 0.08 280)',
    'cmb2-id-section-hero-title': 'oklch(0.68 0.22 45)',
    'cmb2-id-section-slider-title': 'oklch(0.68 0.22 45)',
    'cmb2-id-section-stream-title': '#6a1b78',
    'cmb2-id-section-social-title': 'oklch(0.62 0.28 350)',
    'cmb2-id-section-video-title': 'oklch(0.88 0.19 95)',
    'cmb2-id-section-release-title': 'oklch(0.78 0.10 80)',
    'cmb2-id-section-cta-title': 'oklch(0.68 0.17 182)',
    'cmb2-id-section-footer-title': 'oklch(0.15 0.08 280)'
  };



  const ACTIVE_SECTION_STORAGE_KEY = 'ellene_wp_active_section_after_save';

  const SECTION_ROW_SELECTORS = {};



  let isOverviewMode = true;

  let pendingDeleteControl = null;

  let deleteModalElements = null;



  // Attendre que le DOM soit charge

  if (document.readyState === 'loading') {

    document.addEventListener('DOMContentLoaded', init);

  } else {

    init();

  }



  function init() {

    // Verifier qu'on est sur la page admin Ellene

    if (!document.querySelector('.cmb2-id-section-hero-title')) return;



    removeNativePageHeading();

    setTimeout(removeNativePageHeading, 100);

    setTimeout(removeNativePageHeading, 500);

    setTimeout(removeNativePageHeading, 1200);

    createStickyNav();

    reorderSectionsInDom();

    bindSaveSectionPersistence();

    bindDeleteConfirmationGuard();

    setupAccordion();

    collapseTopBarItemsByDefault();

    collapseSliderItemsByDefault();

    collapseStreamPlatformItemsByDefault();

    collapseReleaseRowsByDefault();

    syncSliderTypeVisibility();

    syncEditableSliderTitles(false);

    syncStreamPlatformTitles();

    syncReleaseRowTitles();

    bindSliderNameEvents();

    bindStreamPlatformNameEvents();

    bindReleaseRowNameEvents();

    observeSliderGroups();

    renameAllMediaUploadButtons();

    observeDynamicMediaButtons();

    renameTopBarItemTitles();

    hideTopBarAddButtons();

    refreshTopBarVisualFieldUi();

    layoutTopBarVisualInlineToggle();

    layoutInlineFieldToggles();

    moveModulesEnabledHelpAboveChoices();

    initModulesOrderAssistant();

    bindRepeatableGroupAccordionEvents();

    bindTopBarVisualEvents();

    bindSliderTypeEvents();

    styleBottomSaveButtons();

    restoreActiveSectionAfterSave();

    applyDefaultModulesNavState();

    addSmoothScroll();

  }



  function getCurrentOpenSectionId() {

    for (let i = 0; i < SECTION_IDS.length; i += 1) {

      const sectionId = SECTION_IDS[i];

      const sectionTitle = getSectionTitleElement(sectionId);

      if (sectionTitle && sectionTitle.classList.contains('ellene-wp-section-open')) {

        return sectionId;

      }

    }

    return '';

  }



  function bindSaveSectionPersistence() {

    const forms = document.querySelectorAll('.cmb2-wrap form, .cmb-form');

    if (!forms.length) {

      return;

    }



    forms.forEach(function(form) {

      if (!form || form.dataset.elleneWpSaveSectionBound === '1') {

        return;

      }



      form.dataset.elleneWpSaveSectionBound = '1';

      form.addEventListener('submit', function() {

        try {

          const sectionId = getCurrentOpenSectionId();

          if (!sectionId) {

            sessionStorage.removeItem(ACTIVE_SECTION_STORAGE_KEY);

            return;

          }

          sessionStorage.setItem(ACTIVE_SECTION_STORAGE_KEY, sectionId);

        } catch (error) {

          // Ignore storage access issues in restricted browser contexts.

        }

      });

    });

  }



  function restoreActiveSectionAfterSave() {

    let sectionId = '';



    try {

      sectionId = sessionStorage.getItem(ACTIVE_SECTION_STORAGE_KEY) || '';

      if (sectionId) {

        sessionStorage.removeItem(ACTIVE_SECTION_STORAGE_KEY);

      }

    } catch (error) {

      return;

    }



    if (!sectionId || SECTION_IDS.indexOf(sectionId) === -1) {

      return;

    }



    if (!getSectionTitleElement(sectionId)) {

      return;

    }



    isOverviewMode = false;

    openSection(sectionId);

    setActiveButtonBySection(sectionId);

    window.setTimeout(function() {

      scrollToSection(sectionId);

    }, 30);

  }



  function bindDeleteConfirmationGuard() {

    const scope = document.querySelector('.cmb2-wrap');

    if (!scope || scope.dataset.elleneWpDeleteGuard === '1') {

      return;

    }



    scope.dataset.elleneWpDeleteGuard = '1';

    ensureDeleteConfirmModal();



    document.addEventListener('click', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      if (target.closest('.ellene-wp-delete-modal, .ellene-wp-delete-modal-backdrop')) {

        return;

      }



      const destructiveControl = findDestructiveControl(target);

      if (!destructiveControl) {

        return;

      }



      if (destructiveControl.dataset.elleneWpDeleteConfirmed === '1') {

        delete destructiveControl.dataset.elleneWpDeleteConfirmed;

        return;

      }



      event.preventDefault();

      if (typeof event.stopImmediatePropagation === 'function') {

        event.stopImmediatePropagation();

      }

      event.stopPropagation();



      openDeleteConfirmModal(destructiveControl);

    }, true);

  }



  function findDestructiveControl(target) {

    const scope = target.closest('.cmb2-wrap');

    if (!scope) {

      return null;

    }



    const control = target.closest('a, button, input[type="button"], input[type="submit"], .cmb-remove-group-row, .cmb-remove-group-row-button, .cmb2-remove-file-button, .cmb-remove-row');

    if (!control) {

      return null;

    }



    if (control.matches('.cmb-remove-group-row, .cmb-remove-group-row-button, .cmb2-remove-file-button, .cmb-remove-row')) {

      return control;

    }



    const signature = [

      control.className,

      control.getAttribute('title') || '',

      control.getAttribute('aria-label') || '',

      control.getAttribute('href') || '',

      control.textContent || '',

      control.value || ''

    ].join(' ').toLowerCase();



    if (signature.indexOf('supprimer') !== -1 || signature.indexOf('delete') !== -1 || signature.indexOf('remove') !== -1) {

      return control;

    }



    return null;

  }



  function ensureDeleteConfirmModal() {

    const existing = document.getElementById('ellene-wp-delete-confirm');

    if (existing) {

      deleteModalElements = {

        root: existing,

        title: existing.querySelector('.ellene-wp-delete-modal-title'),

        message: existing.querySelector('.ellene-wp-delete-modal-message'),

        confirmBtn: existing.querySelector('.ellene-wp-delete-confirm-btn'),

        cancelBtn: existing.querySelector('.ellene-wp-delete-cancel-btn')

      };

      return;

    }



    const root = document.createElement('div');

    root.id = 'ellene-wp-delete-confirm';

    root.className = 'ellene-wp-delete-modal-backdrop';

    root.hidden = true;

    root.innerHTML = '' +

      '<div class="ellene-wp-delete-modal" role="dialog" aria-modal="true" aria-labelledby="ellene-wp-delete-title">' +

        '<h3 id="ellene-wp-delete-title" class="ellene-wp-delete-modal-title">Confirmer la suppression</h3>' +

        '<p class="ellene-wp-delete-modal-message">Cette action est definitive. Veux-tu vraiment supprimer cet element ?</p>' +

        '<div class="ellene-wp-delete-modal-actions">' +

          '<button type="button" class="button ellene-wp-delete-cancel-btn">Annuler</button>' +

          '<button type="button" class="button button-primary ellene-wp-delete-confirm-btn">Oui, supprimer</button>' +

        '</div>' +

      '</div>';



    document.body.appendChild(root);



    const title = root.querySelector('.ellene-wp-delete-modal-title');

    const message = root.querySelector('.ellene-wp-delete-modal-message');

    const confirmBtn = root.querySelector('.ellene-wp-delete-confirm-btn');

    const cancelBtn = root.querySelector('.ellene-wp-delete-cancel-btn');



    cancelBtn.addEventListener('click', closeDeleteConfirmModal);

    confirmBtn.addEventListener('click', function() {

      const control = pendingDeleteControl;

      closeDeleteConfirmModal();

      if (!control) {

        return;

      }

      control.dataset.elleneWpDeleteConfirmed = '1';

      if (typeof control.click === 'function') {

        control.click();

      }

    });



    root.addEventListener('click', function(event) {

      if (event.target === root) {

        closeDeleteConfirmModal();

      }

    });



    document.addEventListener('keydown', function(event) {

      if (event.key === 'Escape' && deleteModalElements && !deleteModalElements.root.hidden) {

        closeDeleteConfirmModal();

      }

    });



    deleteModalElements = {

      root: root,

      title: title,

      message: message,

      confirmBtn: confirmBtn,

      cancelBtn: cancelBtn

    };

  }



  function openDeleteConfirmModal(control) {

    ensureDeleteConfirmModal();

    pendingDeleteControl = control;



    if (!deleteModalElements) {

      return;

    }



    const groupTitle = control.closest('.cmb-repeatable-grouping')?.querySelector('.cmb-group-title > span');

    const label = groupTitle ? String(groupTitle.textContent || '').trim() : '';



    deleteModalElements.title.textContent = 'Confirmer la suppression';

    deleteModalElements.message.textContent = label

      ? 'Tu es sur le point de supprimer "' + label + '". Cette action est definitive.'

      : 'Cette action est definitive. Veux-tu vraiment supprimer cet element ?';



    deleteModalElements.root.hidden = false;

    document.body.classList.add('ellene-wp-delete-modal-open');

    deleteModalElements.cancelBtn.focus();

  }



  function closeDeleteConfirmModal() {

    if (!deleteModalElements) {

      return;

    }



    deleteModalElements.root.hidden = true;

    document.body.classList.remove('ellene-wp-delete-modal-open');

    pendingDeleteControl = null;

  }



  function bindRepeatableGroupAccordionEvents() {

    document.addEventListener('click', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      const toggleControl = target.closest('.cmb-repeatable-grouping .cmbhandlediv, .cmb-repeatable-grouping .handlediv, .cmb-repeatable-grouping .cmb-group-title');

      if (toggleControl) {

        const group = toggleControl.closest('.cmb-repeatable-grouping');

        if (group) {

          window.setTimeout(function() {

            if (!group.classList.contains('closed')) {

              closeSiblingRepeatableGroups(group);

            }

          }, 0);

        }

      }



      if (target.closest('.cmb2-upload-button, .cmb2-remove-file-button, .add-group-row, .cmb-remove-group-row, .cmb-remove-group-row-button, .cmb-shift-rows')) {

        window.setTimeout(function() {

          renameAllMediaUploadButtons();

          collapseStreamPlatformItemsByDefault();

          syncStreamPlatformTitles();

          collapseReleaseRowsByDefault();

          syncReleaseRowTitles();

        }, 0);

      }

    });

  }



  function getDefaultReleaseRowTitle(index) {

    return 'Info ' + (index + 1);

  }



  function setReleaseRowTitle(group, title) {

    const titleSpan = group.querySelector('.cmb-group-title > span');

    if (!titleSpan) {

      return;

    }

    titleSpan.textContent = title;

  }



  function syncReleaseRowTitles() {

    const groups = document.querySelectorAll('.cmb2-id-release-rows .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const labelInput = group.querySelector('input[name*="[key]"]');

      if (!labelInput) {

        return;

      }



      const fallbackTitle = getDefaultReleaseRowTitle(index);

      const value = String(labelInput.value || '').trim();

      setReleaseRowTitle(group, value || fallbackTitle);

    });

  }



  function bindReleaseRowNameEvents() {

    if (document.body.dataset.elleneWpReleaseRowNameBound === '1') {

      return;

    }



    document.body.dataset.elleneWpReleaseRowNameBound = '1';



    const syncFromInput = function(target) {

      if (!target || !target.matches('.cmb2-id-release-rows .cmb-repeatable-grouping input[name*="[key]"]')) {

        return;

      }



      const group = target.closest('.cmb-repeatable-grouping');

      if (!group) {

        return;

      }



      const groups = Array.from(document.querySelectorAll('.cmb2-id-release-rows .cmb-repeatable-grouping'));

      const index = Math.max(groups.indexOf(group), 0);

      const fallbackTitle = getDefaultReleaseRowTitle(index);

      const value = String(target.value || '').trim();

      setReleaseRowTitle(group, value || fallbackTitle);

    };



    document.addEventListener('input', function(event) {

      syncFromInput(event.target);

    });



    document.addEventListener('change', function(event) {

      syncFromInput(event.target);

    });

  }



  function collapseReleaseRowsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-release-rows .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      if (group.dataset.elleneWpReleaseRowInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.elleneWpReleaseRowInit = '1';

    });

  }



  function getDefaultPlatformTitle(index) {

    return 'Plateforme ' + (index + 1);

  }



  function setStreamPlatformTitle(group, title) {

    const titleSpan = group.querySelector('.cmb-group-title > span');

    if (!titleSpan) {

      return;

    }

    titleSpan.textContent = title;

  }



  function syncStreamPlatformTitles() {

    const groups = document.querySelectorAll('.cmb2-id-stream-platforms .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const labelInput = group.querySelector('input[name*="[label]"]');

      if (!labelInput) {

        return;

      }



      const fallbackTitle = getDefaultPlatformTitle(index);

      const value = String(labelInput.value || '').trim();

      setStreamPlatformTitle(group, value || fallbackTitle);

    });

  }



  function bindStreamPlatformNameEvents() {

    if (document.body.dataset.elleneWpStreamPlatformNameBound === '1') {

      return;

    }



    document.body.dataset.elleneWpStreamPlatformNameBound = '1';



    const syncFromInput = function(target) {

      if (!target || !target.matches('.cmb2-id-stream-platforms .cmb-repeatable-grouping input[name*="[label]"]')) {

        return;

      }



      const group = target.closest('.cmb-repeatable-grouping');

      if (!group) {

        return;

      }



      const groups = Array.from(document.querySelectorAll('.cmb2-id-stream-platforms .cmb-repeatable-grouping'));

      const index = Math.max(groups.indexOf(group), 0);

      const fallbackTitle = getDefaultPlatformTitle(index);

      const value = String(target.value || '').trim();

      setStreamPlatformTitle(group, value || fallbackTitle);

    };



    document.addEventListener('input', function(event) {

      syncFromInput(event.target);

    });



    document.addEventListener('change', function(event) {

      syncFromInput(event.target);

    });

  }



  function collapseStreamPlatformItemsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-stream-platforms .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      if (group.dataset.elleneWpStreamPlatformInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.elleneWpStreamPlatformInit = '1';

    });

  }



  function bindSliderTypeEvents() {

    document.addEventListener('change', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      if (target.matches('.cmb2-id-hero-slider .cmb-repeatable-grouping select[name*="[slide_type]"]')) {

        const group = target.closest('.cmb-repeatable-grouping');

        if (group) {

          applySliderTypeVisibility(group);

        }

      }

    });



    document.addEventListener('click', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      const isAddSliderClick = !!target.closest('.cmb2-id-hero-slider .add-group-row');



      if (target.closest('.cmb2-id-hero-slider .add-group-row, .cmb2-id-hero-slider .cmb-remove-group-row, .cmb2-id-hero-slider .cmb-remove-group-row-button, .cmb2-id-hero-slider .cmb-shift-rows')) {

        window.setTimeout(function() {

          syncSliderTypeVisibility();



          if (isAddSliderClick) {

            const groups = document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping');

            const newGroup = groups.length ? groups[groups.length - 1] : null;

            if (newGroup) {

              newGroup.dataset.elleneWpSliderInit = '1';

              openSliderGroup(newGroup);

              closeSiblingRepeatableGroups(newGroup);

              syncEditableSliderTitles(false);

            }

            return;

          }



          collapseSliderItemsByDefault();

          syncEditableSliderTitles(false);

        }, 0);

      }

    });

  }



  function getDefaultSlideTitle(index) {

    return 'Slide ' + (index + 1);

  }



  function setSliderGroupTitle(group, title) {

    const titleSpan = group.querySelector('.cmb-group-title > span');

    if (!titleSpan) {

      return;

    }

    titleSpan.textContent = title;

  }



  function syncEditableSliderTitles(openEditorForNewGroup) {

    const groups = document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const titleInput = group.querySelector('input[name*="[slide_admin_title]"]');

      const titleSpan = group.querySelector('.cmb-group-title > span');

      if (!titleInput || !titleSpan) {

        return;

      }



      const defaultTitle = getDefaultSlideTitle(index);

      const currentValue = String(titleInput.value || '').trim();

      const effectiveTitle = currentValue || defaultTitle;



      if (titleInput.value !== effectiveTitle) {

        titleInput.value = effectiveTitle;

      }

      setSliderGroupTitle(group, effectiveTitle);

    });

  }



  function bindSliderNameEvents() {

    if (document.body.dataset.elleneWpSliderNameBound === '1') {

      return;

    }



    document.body.dataset.elleneWpSliderNameBound = '1';



    const syncFromInput = function(target) {

      if (!target || !target.matches('.cmb2-id-hero-slider .cmb-repeatable-grouping input[name*="[slide_admin_title]"]')) {

        return;

      }



      const group = target.closest('.cmb-repeatable-grouping');

      if (!group) {

        return;

      }



      const groups = Array.from(document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping'));

      const index = Math.max(groups.indexOf(group), 0);

      const fallbackTitle = getDefaultSlideTitle(index);

      const value = String(target.value || '').trim();

      setSliderGroupTitle(group, value || fallbackTitle);

    };



    document.addEventListener('input', function(event) {

      syncFromInput(event.target);

    });



    document.addEventListener('change', function(event) {

      syncFromInput(event.target);

    });

  }



  function observeSliderGroups() {

    const sliderRoot = document.querySelector('.cmb2-id-hero-slider');

    if (!sliderRoot || sliderRoot.dataset.elleneWpSliderObserver === '1' || typeof MutationObserver === 'undefined') {

      return;

    }



    sliderRoot.dataset.elleneWpSliderObserver = '1';

    const observer = new MutationObserver(function(mutations) {

      let hasNewGroup = false;



      mutations.forEach(function(mutation) {

        if (!mutation.addedNodes || !mutation.addedNodes.length) {

          return;

        }



        mutation.addedNodes.forEach(function(node) {

          if (!node || node.nodeType !== 1) {

            return;

          }



          if (node.matches && node.matches('.cmb-repeatable-grouping')) {

            hasNewGroup = true;

            return;

          }



          if (node.querySelector && node.querySelector('.cmb-repeatable-grouping')) {

            hasNewGroup = true;

          }

        });

      });



      if (hasNewGroup) {

        window.setTimeout(function() {

          syncSliderTypeVisibility();

          const groups = document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping');

          const newGroup = groups.length ? groups[groups.length - 1] : null;

          if (newGroup) {

            newGroup.dataset.elleneWpSliderInit = '1';

            openSliderGroup(newGroup);

            closeSiblingRepeatableGroups(newGroup);

            syncEditableSliderTitles(false);

          } else {

            syncEditableSliderTitles(false);

          }

          renameAllMediaUploadButtons();

        }, 0);

      }

    });



    observer.observe(sliderRoot, {

      childList: true,

      subtree: true

    });

  }



  function closeSliderGroup(group) {

    if (!group) {

      return;

    }



    group.classList.add('closed');

    const toggleButton = group.querySelector('.cmbhandlediv button, .handlediv button');

    if (toggleButton) {

      toggleButton.setAttribute('aria-expanded', 'false');

    }

  }



  function openSliderGroup(group) {

    if (!group) {

      return;

    }



    group.classList.remove('closed');

    const toggleButton = group.querySelector('.cmbhandlediv button, .handlediv button');

    if (toggleButton) {

      toggleButton.setAttribute('aria-expanded', 'true');

    }

  }



  function closeSiblingRepeatableGroups(activeGroup) {

    if (!activeGroup) {

      return;

    }



    const parentGroup = activeGroup.closest('.cmb-repeatable-group');

    if (!parentGroup) {

      return;

    }



    const groups = parentGroup.querySelectorAll('.cmb-repeatable-grouping');

    groups.forEach(function(group) {

      if (group === activeGroup) {

        return;

      }

      closeSliderGroup(group);

    });

  }



  function collapseSliderItemsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      if (group.dataset.elleneWpSliderInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.elleneWpSliderInit = '1';

    });

  }



  function setRowVisibility(row, isVisible) {

    if (!row) {

      return;

    }



    row.style.display = isVisible ? '' : 'none';

  }



  function applySliderTypeVisibility(group) {

    const typeSelect = group.querySelector('select[name*="[slide_type]"]');

    if (!typeSelect) {

      return;

    }



    const slideType = String(typeSelect.value || 'image').toLowerCase();



    const imageRow = group.querySelector('.cmb-row[class*="-slide-image"]');

    const youtubeRow = group.querySelector('.cmb-row[class*="-video-url"]');

    const tiktokUrlRow = group.querySelector('.cmb-row[class*="-tiktok-url"]');

    const tiktokMp4Row = group.querySelector('.cmb-row[class*="-tiktok-video-url"]');

    const durationRow = group.querySelector('.cmb-row[class*="-slide-duration"]');



    setRowVisibility(imageRow, slideType === 'image');

    setRowVisibility(youtubeRow, slideType === 'video');

    setRowVisibility(tiktokUrlRow, slideType === 'tiktok');

    setRowVisibility(tiktokMp4Row, slideType === 'tiktok');

    setRowVisibility(durationRow, slideType === 'image');

  }



  function syncSliderTypeVisibility() {

    const groups = document.querySelectorAll('.cmb2-id-hero-slider .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      applySliderTypeVisibility(group);

    });

  }



  function refreshTopBarVisualFieldUi() {

    const row = document.querySelector('.cmb2-id-top-bar-logo-png');

    if (!row) {

      return;

    }



    const uploadButton = row.querySelector('.cmb2-upload-button');

    if (uploadButton) {

      uploadButton.textContent = 'Modifier';

    }

  }



  function renameAllMediaUploadButtons() {

    const uploadButtons = document.querySelectorAll('.cmb2-upload-button');

    if (!uploadButtons.length) {

      return;

    }



    uploadButtons.forEach(function(button) {

      const textLabel = String(button.textContent || '').trim().toLowerCase();

      const valueLabel = String(button.value || '').trim().toLowerCase();

      const ariaLabel = String(button.getAttribute('aria-label') || '').trim().toLowerCase();



      const shouldRename = textLabel.indexOf('ajouter ou mettre un fichier en ligne') !== -1 ||

        valueLabel.indexOf('ajouter ou mettre un fichier en ligne') !== -1 ||

        ariaLabel.indexOf('ajouter ou mettre un fichier en ligne') !== -1 ||

        textLabel === 'ajouter' ||

        valueLabel === 'ajouter';



      if (shouldRename) {

        if ('value' in button) {

          button.value = 'Modifier';

        }

        button.textContent = 'Modifier';

        button.setAttribute('aria-label', 'Modifier');

      }

    });

  }



  function observeDynamicMediaButtons() {

    const scope = document.querySelector('.cmb2-wrap');

    if (!scope || scope.dataset.elleneWpMediaObserver === '1' || typeof MutationObserver === 'undefined') {

      return;

    }



    scope.dataset.elleneWpMediaObserver = '1';

    const observer = new MutationObserver(function() {

      renameAllMediaUploadButtons();

    });



    observer.observe(scope, {

      childList: true,

      subtree: true,

      characterData: true

    });

  }



  function layoutTopBarVisualInlineToggle() {

    const logoRow = document.querySelector('.cmb2-id-top-bar-logo-png');

    const hideRow = document.querySelector('.cmb2-id-top-bar-logo-hidden');

    if (!logoRow || !hideRow) {

      return;

    }



    const logoTd = logoRow.querySelector('.cmb-td');

    if (!logoTd) {

      return;

    }



    const hideInput = hideRow.querySelector('input[type="checkbox"]');

    if (!hideInput) {

      return;

    }



    let inlineWrap = logoTd.querySelector('.ellene-wp-inline-hide-toggle');

    if (!inlineWrap) {

      inlineWrap = document.createElement('span');

      inlineWrap.className = 'ellene-wp-inline-hide-toggle';



      const label = document.createElement('span');

      label.className = 'ellene-wp-inline-hide-label';

      label.textContent = 'Masquer';

      inlineWrap.appendChild(label);



      const mediaButton = logoTd.querySelector('.cmb2-upload-button, .button');

      if (mediaButton && mediaButton.parentNode) {

        mediaButton.insertAdjacentElement('afterend', inlineWrap);

      } else {

        logoTd.appendChild(inlineWrap);

      }

    }



    if (hideInput.parentNode !== inlineWrap) {

      inlineWrap.appendChild(hideInput);

    }



    hideRow.classList.add('ellene-wp-hidden-source-row');

  }



  function layoutInlineFieldToggles() {

    const toggleRows = document.querySelectorAll('.cmb-row.cmb-inline-toggle');

    if (!toggleRows.length) {

      return;

    }



    toggleRows.forEach(function(toggleRow) {

      const fieldRow = toggleRow.previousElementSibling;

      if (!fieldRow || !fieldRow.classList || !fieldRow.classList.contains('cmb-field-with-toggle')) {

        return;

      }



      const fieldTd = fieldRow.querySelector('.cmb-td');

      const toggleInput = toggleRow.querySelector('input[type="checkbox"]');

      if (!fieldTd || !toggleInput) {

        return;

      }



      let inlineWrap = fieldTd.querySelector('.ellene-wp-inline-field-toggle');

      if (!inlineWrap) {

        inlineWrap = document.createElement('span');

        inlineWrap.className = 'ellene-wp-inline-field-toggle';



        const label = document.createElement('span');

        label.className = 'ellene-wp-inline-field-toggle-label';

        label.textContent = 'Masquer';

        inlineWrap.appendChild(label);



        const mediaButton = fieldTd.querySelector('.cmb2-upload-button, .cmb2-remove-file-button, .button');

        if (mediaButton && mediaButton.parentNode) {

          mediaButton.insertAdjacentElement('afterend', inlineWrap);

        } else {

          fieldTd.appendChild(inlineWrap);

        }

      }



      if (toggleInput.parentNode !== inlineWrap) {

        inlineWrap.appendChild(toggleInput);

      }



      toggleRow.classList.add('ellene-wp-hidden-source-row');

    });

  }



  function renameTopBarItemTitles() {

    const groups = document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const titleSpan = group.querySelector('.cmb-group-title > span');

      if (!titleSpan) {

        return;

      }



      const customTitle = TOP_BAR_ITEM_TITLES[index];

      if (customTitle) {

        titleSpan.textContent = customTitle;

      }

    });

  }



  function bindTopBarVisualEvents() {

    document.addEventListener('click', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      if (target.closest('.cmb2-id-top-bar-items .add-group-row, .cmb2-id-top-bar-items .cmb-remove-group-row, .cmb2-id-top-bar-items .cmb-remove-group-row-button, .cmb2-id-top-bar-items .cmb-shift-rows, .cmb2-id-top-bar-items .cmbhandlediv, .cmb2-id-top-bar-items .handlediv')) {

        window.setTimeout(function() {

          renameTopBarItemTitles();

          hideTopBarAddButtons();

          renameAllMediaUploadButtons();

          refreshTopBarVisualFieldUi();

          layoutTopBarVisualInlineToggle();

        }, 0);

      }



      if (target.closest('.cmb2-id-top-bar-logo-png .cmb2-upload-button, .cmb2-id-top-bar-logo-png .cmb2-remove-file-button')) {

        window.setTimeout(function() {

          renameAllMediaUploadButtons();

          refreshTopBarVisualFieldUi();

          layoutTopBarVisualInlineToggle();

        }, 0);

      }

    });

  }



  function collapseTopBarItemsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-top-bar-items .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      group.classList.add('closed');



      const toggleButton = group.querySelector('.cmbhandlediv button, .handlediv button');

      if (toggleButton) {

        toggleButton.setAttribute('aria-expanded', 'false');

      }

    });

  }



  function hideTopBarAddButtons() {

    const root = document.querySelector('.cmb2-id-top-bar-items');

    if (!root) {

      return;

    }




    const controls = root.querySelectorAll('button, a, input[type="button"], input[type="submit"]');

    if (!controls.length) {

      return;

    }



    controls.forEach(function(control) {

      const signature = [

        control.className || '',

        control.textContent || '',

        control.value || '',

        control.getAttribute('title') || '',

        control.getAttribute('aria-label') || ''

      ].join(' ').toLowerCase();



      const isAddControl = signature.indexOf('ajouter') !== -1 || signature.indexOf('add') !== -1;

      const isRemoveControl = signature.indexOf('supprimer') !== -1 || signature.indexOf('remove') !== -1 || signature.indexOf('delete') !== -1;

      if (!isAddControl && !isRemoveControl) {

        return;

      }



      control.style.setProperty('display', 'none', 'important');

    });

  }



  function reorderSectionsInDom() {

    const firstSection = SECTION_IDS

      .map(getSectionTitleElement)

      .find(function(el) { return !!el; });



    if (!firstSection || !firstSection.parentNode) {

      return;

    }



    const container = firstSection.parentNode;

    const fragment = document.createDocumentFragment();



    SECTION_IDS.forEach(function(sectionId) {

      const titleEl = getSectionTitleElement(sectionId);

      if (!titleEl) {

        return;

      }



      const blockRows = [titleEl].concat(getSectionContentRows(sectionId));

      blockRows.forEach(function(row) {

        fragment.appendChild(row);

      });

    });



    container.appendChild(fragment);

  }



  function removeNativePageHeading() {

    const wrap = document.querySelector('.wrap');

    if (!wrap) return;



    const mainHeading = wrap.querySelector('h1');

    if (mainHeading) {

      mainHeading.remove();

    }



    const inlineHeading = wrap.querySelector('.wp-heading-inline');

    if (inlineHeading) {

      inlineHeading.remove();

    }

  }



  function createStickyNav() {

    const sections = [

      { id: 'section_modules_title', label: 'Modules' },

      { id: 'section_top_bar_title', label: 'TOP-BAR' },

      { id: 'section_hero_title', label: 'Hero' },

      { id: 'section_slider_title', label: 'Slider' },

      { id: 'section_stream_title', label: 'Stream' },

      { id: 'section_social_title', label: 'Social' },

      { id: 'section_video_title', label: 'Video' },

      { id: 'section_release_title', label: 'Release' },

      { id: 'section_cta_title', label: 'CTA' },

      { id: 'section_footer_title', label: 'Footer' }

    ];



    // Trouver le conteneur du formulaire

    const formContainer = document.querySelector('.cmb2-wrap');

    if (!formContainer) return;



    // Creer la navigation

    const nav = document.createElement('div');

    nav.id = 'ellene-wp-admin-nav';

    nav.className = 'ellene-wp-admin-nav';

    

    const navInner = document.createElement('div');

    navInner.className = 'ellene-wp-admin-nav-inner';



    // Container des boutons

    const buttonsContainer = document.createElement('div');

    buttonsContainer.className = 'ellene-wp-admin-nav-buttons';



    sections.forEach(section => {

      const sectionEl = getSectionTitleElement(section.id);

      if (!sectionEl) return;



      const btn = document.createElement('a');

      btn.href = '#' + section.id;

      btn.className = 'ellene-wp-nav-btn';

      btn.dataset.section = section.id;

      if (section.id === 'section_modules_title') {

        btn.classList.add('ellene-wp-nav-btn-with-icon');

        btn.innerHTML = '<span class="dashicons dashicons-admin-generic ellene-wp-nav-btn-icon" aria-hidden="true"></span><span>' + section.label + '</span>';

      } else {

        btn.textContent = section.label;

      }



      btn.addEventListener('click', function(e) {

        e.preventDefault();

        isOverviewMode = false;

        openSection(section.id);

        scrollToSection(section.id);

        setActiveButton(btn);

      });



      buttonsContainer.appendChild(btn);

    });



    navInner.appendChild(buttonsContainer);

    

    // Bouton Enregistrer a droite

    const saveButton = document.createElement('button');

    saveButton.type = 'button';

    saveButton.className = 'ellene-wp-save-btn';

    saveButton.innerHTML = '💾 Enregistrer';

    saveButton.style.cssText = 'background: #fff; color: #6b21a8; border: 2px solid #fff; padding: 8px 20px; font-size: 13px; font-weight: 700; border-radius: 6px; cursor: pointer; margin-left: auto; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s;';

    

    saveButton.addEventListener('click', function(e) {

      e.preventDefault();

      const realSubmit = document.querySelector('.cmb-form input[type="submit"], .cmb2-wrap input[type="submit"]');

      if (realSubmit) {

        realSubmit.click();

      }

    });

    

    saveButton.addEventListener('mouseenter', function() {

      this.style.background = '#f0f0f1';

      this.style.transform = 'translateY(-1px)';

      this.style.boxShadow = '0 3px 8px rgba(0,0,0,0.2)';

    });

    

    saveButton.addEventListener('mouseleave', function() {

      this.style.background = '#fff';

      this.style.transform = 'translateY(0)';

      this.style.boxShadow = 'none';

    });

    

    navInner.appendChild(saveButton);

    nav.appendChild(navInner);



    // Inserer avant le formulaire

    formContainer.parentNode.insertBefore(nav, formContainer);



    // Observer le scroll pour mettre a jour le bouton actif

    observeSections(sections);

  }



  function setupAccordion() {

    SECTION_IDS.forEach(sectionId => {

      const sectionTitle = getSectionTitleElement(sectionId);

      if (sectionTitle) {

        sectionTitle.setAttribute('data-ellene-wp-section', sectionId);

        makeSectionTitleInteractive(sectionId);

      }

      closeSection(sectionId);

    });

  }



  function makeSectionTitleInteractive(sectionId) {

    const sectionTitle = getSectionTitleElement(sectionId);

    if (!sectionTitle || sectionTitle.dataset.elleneWpClickable === '1') {

      return;

    }



    sectionTitle.dataset.elleneWpClickable = '1';

    sectionTitle.setAttribute('role', 'button');

    sectionTitle.setAttribute('tabindex', '0');

    sectionTitle.setAttribute('aria-expanded', 'false');

    sectionTitle.classList.add('ellene-wp-section-toggle');

    injectSectionEyeIcon(sectionTitle);

    injectSectionHelpLine(sectionTitle, sectionId);



    sectionTitle.addEventListener('click', function(e) {

      if (e.target && e.target.closest('a, button, input, select, textarea')) {

        return;

      }



      toggleSection(sectionId);

    });



    sectionTitle.addEventListener('keydown', function(e) {

      if (e.key !== 'Enter' && e.key !== ' ') {

        return;

      }



      e.preventDefault();

      toggleSection(sectionId);

    });

  }



  function injectSectionEyeIcon(sectionTitle) {

    const iconHost = sectionTitle.querySelector('.cmb-th') || sectionTitle;

    if (!iconHost || iconHost.querySelector('.ellene-wp-eye-indicator')) {

      return;

    }



    const eye = document.createElement('span');

    eye.className = 'ellene-wp-eye-indicator dashicons dashicons-visibility';

    eye.setAttribute('aria-hidden', 'true');

    iconHost.appendChild(eye);

  }



  function injectSectionHelpLine(sectionTitle, sectionId) {

    const host = sectionTitle.querySelector('.cmb-th') || sectionTitle;

    if (!host || host.querySelector('.ellene-wp-section-helpline')) {

      return;

    }



    const titleNode = sectionTitle.querySelector('.cmb2-metabox-title, .cmb-th h3');

    const rawLabel = titleNode ? titleNode.textContent : '';

    const label = (rawLabel || '')

      .replace(/\s+/g, ' ')

      .trim();



    if (!label) {

      return;

    }



    const helpLine = document.createElement('div');

    helpLine.className = 'ellene-wp-section-helpline';

    helpLine.textContent = SECTION_HELP_TEXTS[sectionId] || ('Aide pour ' + label);

    host.appendChild(helpLine);

  }



  function styleBottomSaveButtons() {

    const selectors = [

      '.cmb2-wrap input[type="submit"]',

      '.cmb-form input[type="submit"]',

      '.wrap p.submit input[type="submit"]',

      '.wrap .cmb2-wrap .button.button-primary'

    ];



    const buttons = document.querySelectorAll(selectors.join(','));

    buttons.forEach(function(btn) {

      if (btn.classList.contains('ellene-wp-save-btn')) {

        return;

      }



      btn.style.background = '#fff';

      btn.style.color = '#6b21a8';

      btn.style.border = '2px solid #fff';

      btn.style.padding = '8px 20px';

      btn.style.fontSize = '13px';

      btn.style.fontWeight = '700';

      btn.style.borderRadius = '6px';

      btn.style.cursor = 'pointer';

      btn.style.textTransform = 'uppercase';

      btn.style.letterSpacing = '0.5px';

      btn.style.boxShadow = 'none';

      btn.style.transition = 'all 0.2s ease';



      if (btn.tagName === 'INPUT') {

        btn.value = '💾 Enregistrer';

      } else {

        btn.textContent = '💾 Enregistrer';

      }



      if (btn.dataset.elleneWpSaveStyled === '1') {

        return;

      }



      btn.dataset.elleneWpSaveStyled = '1';

      btn.addEventListener('mouseenter', function() {

        this.style.background = '#f0f0f1';

        this.style.transform = 'translateY(-1px)';

        this.style.boxShadow = '0 3px 8px rgba(0,0,0,0.2)';

      });



      btn.addEventListener('mouseleave', function() {

        this.style.background = '#fff';

        this.style.transform = 'translateY(0)';

        this.style.boxShadow = 'none';

      });

    });

  }



  function toggleSection(sectionId) {

    const sectionTitle = getSectionTitleElement(sectionId);

    if (!sectionTitle) {

      return;

    }



    const isOpen = sectionTitle.classList.contains('ellene-wp-section-open');



    if (isOpen) {

      closeSection(sectionId);

      setActiveButtonBySection('section_modules_title');

      isOverviewMode = true;

      return;

    }



    isOverviewMode = false;

    openSection(sectionId);

    setActiveButtonBySection(sectionId);

  }



  function setActiveButtonBySection(sectionId) {

    const btn = document.querySelector('.ellene-wp-nav-btn[data-section="' + sectionId + '"]');

    if (!btn) {

      clearActiveButtons();

      return;

    }



    setActiveButton(btn);

  }



  function closeAllSections() {

    SECTION_IDS.forEach(sectionId => {

      closeSection(sectionId);

    });

  }



  function getSectionTitleElement(sectionId) {

    const defaultSelector = '.cmb2-id-' + sectionId.replace(/_/g, '-');

    const defaultMatch = document.querySelector(defaultSelector);

    if (defaultMatch) {

      return defaultMatch;

    }



    const fallbackSelectors = SECTION_ROW_SELECTORS[sectionId] || [];

    for (let i = 0; i < fallbackSelectors.length; i += 1) {

      const fallbackMatch = document.querySelector(fallbackSelectors[i]);

      if (!fallbackMatch) {

        continue;

      }



      if (fallbackMatch.classList.contains('cmb-type-title')) {

        return fallbackMatch;

      }



      let current = fallbackMatch.previousElementSibling;

      while (current) {

        if (current.classList && current.classList.contains('cmb-type-title')) {

          return current;

        }

        current = current.previousElementSibling;

      }

    }



    return null;

  }



  function getSectionContentRows(sectionId) {

    const titleEl = getSectionTitleElement(sectionId);

    if (!titleEl) return [];



    const rows = [];

    let current = titleEl.nextElementSibling;



    while (current) {

      if (isSectionTitleRow(current)) {

        break;

      }



      if (current.classList && current.classList.contains('cmb-row')) {

        rows.push(current);

      }



      current = current.nextElementSibling;

    }



    return rows;

  }



  function isSectionTitleRow(el) {

    if (!el || !el.classList) return false;



    const directMatch = Array.from(el.classList).some(className => {

      return className.indexOf('cmb2-id-section-') === 0 && className.indexOf('-title') !== -1;

    });



    if (directMatch) {

      return true;

    }



    return SECTION_IDS.some(function(sectionId) {

      return getSectionTitleElement(sectionId) === el;

    });

  }



  function parseModulesOrderValue(rawValue) {

    if (!rawValue) {

      return [];

    }



    return rawValue

      .split(',')

      .map(function(item) {

        return item.trim();

      })

      .filter(function(item, index, array) {

        return !!item && array.indexOf(item) === index;

      });

  }



  function getModuleLabelFromSlug(slug) {

    if (!slug) {

      return '';

    }



    const option = document.querySelector('.cmb2-id-modules-enabled input[value="' + slug + '"]');

    if (!option) {

      return slug;

    }



    const wrapper = option.closest('label');

    if (!wrapper) {

      return slug;

    }



    return wrapper.textContent.replace(/\s+/g, ' ').trim() || slug;

  }



  function getEnabledModuleSlugs() {

    const checkboxes = document.querySelectorAll('.cmb2-id-modules-enabled input[type="checkbox"]');

    const enabled = [];



    checkboxes.forEach(function(checkbox) {

      if (!checkbox.checked) {

        return;

      }



      const slug = (checkbox.value || '').trim();

      if (!slug || enabled.indexOf(slug) !== -1) {

        return;

      }



      enabled.push(slug);

    });



    return enabled;

  }



  function getAllModuleSlugs() {

    const checkboxes = document.querySelectorAll('.cmb2-id-modules-enabled input[type="checkbox"]');

    const all = [];



    checkboxes.forEach(function(checkbox) {

      const slug = (checkbox.value || '').trim();

      if (!slug || all.indexOf(slug) !== -1) {

        return;

      }



      all.push(slug);

    });



    return all;

  }



  function getNavbarModuleSlugs() {

    const sectionToSlug = {

      section_top_bar_title: 'top-bar',

      section_hero_title: 'hero',

      section_stream_title: 'stream',

      section_social_title: 'social',

      section_video_title: 'video',

      section_release_title: 'release',

      section_cta_title: 'cta',

      section_footer_title: 'footer'

    };



    const navbarOrder = [];

    const buttons = document.querySelectorAll('.ellene-wp-nav-btn[data-section]');

    buttons.forEach(function(button) {

      const sectionId = (button.dataset.section || '').trim();

      const slug = sectionToSlug[sectionId] || '';

      if (!slug || navbarOrder.indexOf(slug) !== -1) {

        return;

      }

      navbarOrder.push(slug);

    });



    return navbarOrder;

  }



  function getCurrentUiModuleOrder(enabledSlugs) {

    const uiOrder = [];

    const navbarOrder = getNavbarModuleSlugs();



    navbarOrder.forEach(function(slug) {

      if (enabledSlugs.indexOf(slug) === -1 || uiOrder.indexOf(slug) !== -1) {

        return;

      }

      uiOrder.push(slug);

    });



    enabledSlugs.forEach(function(slug) {

      if (uiOrder.indexOf(slug) !== -1) {

        return;

      }

      uiOrder.push(slug);

    });



    return uiOrder;

  }



  function buildEffectiveModulesOrder(enabledSlugs, currentOrder) {

    const effective = [];

    const orderSource = Array.isArray(currentOrder) ? currentOrder : [];

    const uiOrder = getCurrentUiModuleOrder(enabledSlugs);



    orderSource.forEach(function(slug) {

      if (enabledSlugs.indexOf(slug) === -1 || effective.indexOf(slug) !== -1) {

        return;

      }

      effective.push(slug);

    });



    uiOrder.forEach(function(slug) {

      if (effective.indexOf(slug) !== -1) {

        return;

      }

      effective.push(slug);

    });



    return effective;

  }



  function buildPersistentModulesOrder(fullOrder, enabledOrder, enabledSlugs, allSlugs) {

    const allowed = Array.isArray(allSlugs) ? allSlugs : [];

    const normalized = [];



    (Array.isArray(fullOrder) ? fullOrder : []).forEach(function(slug) {

      if (allowed.indexOf(slug) === -1 || normalized.indexOf(slug) !== -1) {

        return;

      }



      normalized.push(slug);

    });



    allowed.forEach(function(slug) {

      if (normalized.indexOf(slug) !== -1) {

        return;

      }



      normalized.push(slug);

    });



    const enabledSet = new Set(Array.isArray(enabledSlugs) ? enabledSlugs : []);

    const orderedEnabled = Array.isArray(enabledOrder) ? enabledOrder.slice() : [];

    let enabledIndex = 0;



    return normalized.map(function(slug) {

      if (!enabledSet.has(slug)) {

        return slug;

      }



      const nextEnabled = orderedEnabled[enabledIndex] || slug;

      enabledIndex += 1;

      return nextEnabled;

    });

  }



  function initModulesOrderAssistant() {

    const row = document.querySelector('.cmb2-id-modules-order');

    if (!row) {

      return;

    }



    const input = row.querySelector('input[type="text"]');

    const td = row.querySelector('.cmb-td');

    const description = td ? td.querySelector('.cmb2-metabox-description') : null;

    if (!input || !td) {

      return;

    }



    input.placeholder = 'top-bar,hero,stream,social,video,release,cta,footer';



    let helper = row.querySelector('.ellene-wp-modules-order-helper');

    if (!helper) {

      helper = document.createElement('div');

      helper.className = 'ellene-wp-modules-order-helper';

      helper.innerHTML = '' +

         '<div class="ellene-wp-modules-order-note">Déplace les rubriques actives (drag & drop) pour définir l\'ordre :</div>' +     

        '<div class="ellene-wp-modules-order-chips"></div>' +

        '<br>';

      td.appendChild(helper);

    }



    let actions = row.querySelector('.ellene-wp-modules-order-actions');

    if (!actions) {

      actions = document.createElement('div');

      actions.className = 'ellene-wp-modules-order-actions';

      actions.innerHTML = '' +

        '<div class="ellene-wp-modules-order-note">Ordre initial : TOP-BAR / HERO / STREAM / SOCIAL / VIDEO / RELEASE / CTA / FOOTER</div>' +

        '<button type="button" class="ellene-wp-order-reset-btn">Réinitialiser les positions</button>';

    }


    let separatorBeforeInput = row.querySelector('.ellene-wp-modules-order-separator-before-input');
    if (!separatorBeforeInput) {
      separatorBeforeInput = document.createElement('div');
      separatorBeforeInput.className = 'ellene-wp-modules-order-separator ellene-wp-modules-order-separator-before-input';
    }
    td.appendChild(separatorBeforeInput);

    if (description) {

      td.appendChild(description);

    }

    // Keep the editable field below the helper and below its description text.
    td.appendChild(input);

    let separator = row.querySelector('.ellene-wp-modules-order-separator-before-actions');
    if (!separator) {
      separator = document.createElement('div');
      separator.className = 'ellene-wp-modules-order-separator ellene-wp-modules-order-separator-before-actions';
    }
    td.appendChild(separator);

    // Keep reset button + default-order help at the end.
    td.appendChild(actions);



    const chipsHost = helper.querySelector('.ellene-wp-modules-order-chips');

    const resetBtn = actions.querySelector('.ellene-wp-order-reset-btn');

    let draggingSlug = '';



    const syncOrder = function(preferredOrder, forceUiOrder) {

      const enabled = getEnabledModuleSlugs();

      const all = getAllModuleSlugs();

      const currentFullOrder = parseModulesOrderValue(input.value).filter(function(slug) {

        return all.indexOf(slug) !== -1;

      });

      const orderSource = forceUiOrder

        ? getCurrentUiModuleOrder(enabled)

        : (Array.isArray(preferredOrder) ? preferredOrder : currentFullOrder);

      const effectiveOrder = buildEffectiveModulesOrder(enabled, orderSource);

      const persistedOrder = buildPersistentModulesOrder(currentFullOrder, effectiveOrder, enabled, all);



      input.value = persistedOrder.join(',');

      chipsHost.innerHTML = '';



      if (!effectiveOrder.length) {

        const empty = document.createElement('div');

        empty.className = 'ellene-wp-modules-order-empty';

        empty.textContent = 'Active au moins un module pour definir un ordre.';

        chipsHost.appendChild(empty);

        return;

      }



      effectiveOrder.forEach(function(slug) {

        const chip = document.createElement('button');

        chip.type = 'button';

        chip.className = 'ellene-wp-modules-order-chip';

        chip.draggable = true;

        chip.dataset.slug = slug;

        chip.textContent = getModuleLabelFromSlug(slug);



        chip.addEventListener('dragstart', function() {

          draggingSlug = slug;

          chip.classList.add('is-dragging');

        });



        chip.addEventListener('dragend', function() {

          draggingSlug = '';

          chip.classList.remove('is-dragging');

        });



        chip.addEventListener('dragover', function(event) {

          event.preventDefault();

          chip.classList.add('is-drop-target');

        });



        chip.addEventListener('dragleave', function() {

          chip.classList.remove('is-drop-target');

        });



        chip.addEventListener('drop', function(event) {

          event.preventDefault();

          chip.classList.remove('is-drop-target');



          if (!draggingSlug || draggingSlug === slug) {

            return;

          }



          const current = parseModulesOrderValue(input.value);

          const fromIndex = current.indexOf(draggingSlug);

          const toIndex = current.indexOf(slug);



          if (fromIndex === -1 || toIndex === -1) {

            return;

          }



          const moved = current.splice(fromIndex, 1)[0];

          current.splice(toIndex, 0, moved);

          syncOrder(current, false);

        });



        chipsHost.appendChild(chip);

      });

    };



    if (resetBtn && resetBtn.dataset.elleneWpBound !== '1') {

      resetBtn.dataset.elleneWpBound = '1';

      resetBtn.addEventListener('click', function() {

        syncOrder([], true);

      });

    }



    if (input.dataset.elleneWpOrderAssistantBound !== '1') {

      input.dataset.elleneWpOrderAssistantBound = '1';

      input.addEventListener('change', function() {

        syncOrder();

      });

    }



    if (document.body.dataset.elleneWpModulesEnabledBound !== '1') {

      document.body.dataset.elleneWpModulesEnabledBound = '1';



      document.addEventListener('change', function(event) {

        if (!event.target || !event.target.matches('.cmb2-id-modules-enabled input[type="checkbox"]')) {

          return;

        }

        syncOrder();

      });



      document.addEventListener('click', function(event) {

        if (!event.target || !event.target.closest('.cmb2-id-modules-enabled .button')) {

          return;

        }

        window.setTimeout(function() {

          syncOrder();

        }, 0);

      });

    }



    // At page load, keep and display the saved order from the option value.
    syncOrder();

  }



  function moveModulesEnabledHelpAboveChoices() {

    const row = document.querySelector('.cmb2-id-modules-enabled');

    if (!row) {

      return;

    }



    const td = row.querySelector('.cmb-td');

    const description = td ? td.querySelector('.cmb2-metabox-description') : null;

    if (!td || !description) {

      return;

    }



    td.insertBefore(description, td.firstChild);

  }



  function closeSection(sectionId) {

    const titleEl = getSectionTitleElement(sectionId);

    const rows = getSectionContentRows(sectionId);



    rows.forEach(row => {

      row.style.display = 'none';

    });



    if (titleEl) {

      titleEl.classList.remove('ellene-wp-section-open');

      titleEl.classList.add('ellene-wp-section-closed');

      titleEl.setAttribute('aria-expanded', 'false');

      applySectionHeaderState(titleEl, false);

    }

  }



  function openSection(sectionId) {

    SECTION_IDS.forEach(id => {

      if (id !== sectionId) {

        closeSection(id);

      }

    });



    const titleEl = getSectionTitleElement(sectionId);

    const rows = getSectionContentRows(sectionId);



    rows.forEach(row => {

      row.style.display = '';

    });



    if (titleEl) {

      titleEl.classList.remove('ellene-wp-section-closed');

      titleEl.classList.add('ellene-wp-section-open');

      titleEl.setAttribute('aria-expanded', 'true');

      applySectionHeaderState(titleEl, true);

    }

  }



  function applySectionHeaderState(titleEl, isOpen) {

    const headerCell = titleEl.querySelector('.cmb-th');

    const heading = titleEl.querySelector('.cmb2-metabox-title') || titleEl.querySelector('h3');

    const eye = titleEl.querySelector('.ellene-wp-eye-indicator');

    const neutralBg = '#f2f2f3';

    const sectionClassName = Object.keys(SECTION_ACCENT_BY_CLASS).find(className =>

      titleEl.classList.contains(className)

    );

    const resolvedAccent = sectionClassName ? SECTION_ACCENT_BY_CLASS[sectionClassName] : '#1f2937';



    if (headerCell) {

      if (isOpen) {

        headerCell.style.setProperty('background-image', 'none', 'important');

        headerCell.style.setProperty('background-color', neutralBg, 'important');

        headerCell.style.setProperty('background', neutralBg, 'important');

        headerCell.style.setProperty('color', resolvedAccent, 'important');

        headerCell.style.setProperty('border-left-color', resolvedAccent, 'important');

      } else {

        headerCell.style.removeProperty('background-image');

        headerCell.style.removeProperty('background-color');

        headerCell.style.removeProperty('background');

        headerCell.style.removeProperty('color');

        headerCell.style.removeProperty('border-left-color');

      }

    }



    if (heading) {

      if (isOpen) {

        heading.style.setProperty('color', resolvedAccent, 'important');

      } else {

        heading.style.removeProperty('color');

      }

    }



    if (eye) {

      if (isOpen) {

        eye.style.setProperty('color', resolvedAccent, 'important');

      } else {

        eye.style.removeProperty('color');

      }

    }

  }



  function scrollToSection(sectionId) {

    const sectionEl = document.querySelector('.cmb2-id-' + sectionId.replace(/_/g, '-'));

    if (!sectionEl) return;



    const nav = document.getElementById('ellene-wp-admin-nav');

    const navHeight = nav ? nav.offsetHeight : 0;

    const offset = 20; // Padding supplementaire



    const elementPosition = sectionEl.getBoundingClientRect().top + window.pageYOffset;

    const offsetPosition = elementPosition - navHeight - offset;



    window.scrollTo({

      top: offsetPosition,

      behavior: 'smooth'

    });

  }



  function setActiveButton(activeBtn) {

    const allBtns = document.querySelectorAll('.ellene-wp-nav-btn');

    allBtns.forEach(btn => btn.classList.remove('active'));

    activeBtn.classList.add('active');

  }



  function clearActiveButtons() {

    const allBtns = document.querySelectorAll('.ellene-wp-nav-btn');

    allBtns.forEach(btn => btn.classList.remove('active'));

  }



  function applyDefaultModulesNavState() {

    const hasActive = !!document.querySelector('.ellene-wp-nav-btn.active');

    if (hasActive) {

      return;

    }



    const modulesBtn = document.querySelector('.ellene-wp-nav-btn[data-section="section_modules_title"]');

    if (!modulesBtn) {

      return;

    }



    setActiveButton(modulesBtn);

  }



  function observeSections(sections) {

    const nav = document.getElementById('ellene-wp-admin-nav');

    const navHeight = nav ? nav.offsetHeight : 0;



    const observerOptions = {

      root: null,

      rootMargin: `-${navHeight + 50}px 0px -60% 0px`,

      threshold: 0

    };



    const observer = new IntersectionObserver(entries => {

      if (isOverviewMode) {

        return;

      }



      entries.forEach(entry => {

        if (entry.isIntersecting) {

          const sectionId = entry.target.classList[0].replace('cmb2-id-', '').replace(/-/g, '_');

          const btn = document.querySelector(`.ellene-wp-nav-btn[data-section="${sectionId}"]`);

          if (btn) setActiveButton(btn);

        }

      });

    }, observerOptions);



    sections.forEach(section => {

      const el = document.querySelector('.cmb2-id-' + section.id.replace(/_/g, '-'));

      if (el) observer.observe(el);

    });

  }



  function addSmoothScroll() {

    // Style general pour smooth scroll

    document.documentElement.style.scrollBehavior = 'smooth';

  }

})();



