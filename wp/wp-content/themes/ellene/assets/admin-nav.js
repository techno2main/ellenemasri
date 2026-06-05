/**

 * Admin Navigation Sticky - Mayami Landing

 */

(function() {

  'use strict';



  const SECTION_IDS = [

    'section_modules_title',

    'section_marquee_title',

    'section_hero_title',

    'section_slider_title',

    'section_stream_title',

    'section_stream_shared_title',

    'section_social_title',

    'section_video_title',

    'section_release_title',

    'section_cta_title',

    'section_footer_title'

  ];



  const MARQUEE_ITEM_TITLES = [

    'Titre Single',

    'CTA central',

    'Baseline'

  ];



  const ACTIVE_SECTION_STORAGE_KEY = 'mayami_active_section_after_save';

  const SECTION_ROW_SELECTORS = {};



  let isOverviewMode = true;

  let pendingDeleteControl = null;

  let deleteModalElements = null;



  // Attendre que le DOM soit chargé

  if (document.readyState === 'loading') {

    document.addEventListener('DOMContentLoaded', init);

  } else {

    init();

  }



  function init() {

    // Vérifier qu'on est sur la page admin Mayami

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

    collapseMarqueeItemsByDefault();

    collapseSliderItemsByDefault();

    collapseStreamPlatformItemsByDefault();

    collapseSharedStreamPlatformItemsByDefault();

    collapseReleaseRowsByDefault();

    syncSliderTypeVisibility();

    syncEditableSliderTitles(false);

    syncStreamPlatformTitles();

    syncSharedStreamPlatformTitles();

    syncReleaseRowTitles();

    bindSliderNameEvents();

    bindStreamPlatformNameEvents();

    bindSharedStreamPlatformNameEvents();

    bindReleaseRowNameEvents();

    observeSliderGroups();

    renameAllMediaUploadButtons();

    observeDynamicMediaButtons();

    renameMarqueeItemTitles();

    refreshTopBarVisualFieldUi();

    layoutTopBarVisualInlineToggle();

    layoutInlineFieldToggles();

    bindRepeatableGroupAccordionEvents();

    bindTopBarVisualEvents();

    bindSliderTypeEvents();

    styleBottomSaveButtons();

    restoreActiveSectionAfterSave();

    addSmoothScroll();

  }



  function getCurrentOpenSectionId() {

    for (let i = 0; i < SECTION_IDS.length; i += 1) {

      const sectionId = SECTION_IDS[i];

      const sectionTitle = getSectionTitleElement(sectionId);

      if (sectionTitle && sectionTitle.classList.contains('mayami-section-open')) {

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

      if (!form || form.dataset.mayamiSaveSectionBound === '1') {

        return;

      }



      form.dataset.mayamiSaveSectionBound = '1';

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

    if (!scope || scope.dataset.mayamiDeleteGuard === '1') {

      return;

    }



    scope.dataset.mayamiDeleteGuard = '1';

    ensureDeleteConfirmModal();



    document.addEventListener('click', function(event) {

      const target = event.target;

      if (!target) {

        return;

      }



      if (target.closest('.mayami-delete-modal, .mayami-delete-modal-backdrop')) {

        return;

      }



      const destructiveControl = findDestructiveControl(target);

      if (!destructiveControl) {

        return;

      }



      if (destructiveControl.dataset.mayamiDeleteConfirmed === '1') {

        delete destructiveControl.dataset.mayamiDeleteConfirmed;

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

    const existing = document.getElementById('mayami-delete-confirm');

    if (existing) {

      deleteModalElements = {

        root: existing,

        title: existing.querySelector('.mayami-delete-modal-title'),

        message: existing.querySelector('.mayami-delete-modal-message'),

        confirmBtn: existing.querySelector('.mayami-delete-confirm-btn'),

        cancelBtn: existing.querySelector('.mayami-delete-cancel-btn')

      };

      return;

    }



    const root = document.createElement('div');

    root.id = 'mayami-delete-confirm';

    root.className = 'mayami-delete-modal-backdrop';

    root.hidden = true;

    root.innerHTML = '' +

      '<div class="mayami-delete-modal" role="dialog" aria-modal="true" aria-labelledby="mayami-delete-title">' +

        '<h3 id="mayami-delete-title" class="mayami-delete-modal-title">Confirmer la suppression</h3>' +

        '<p class="mayami-delete-modal-message">Cette action est définitive. Veux-tu vraiment supprimer cet élément ?</p>' +

        '<div class="mayami-delete-modal-actions">' +

          '<button type="button" class="button mayami-delete-cancel-btn">Annuler</button>' +

          '<button type="button" class="button button-primary mayami-delete-confirm-btn">Oui, supprimer</button>' +

        '</div>' +

      '</div>';



    document.body.appendChild(root);



    const title = root.querySelector('.mayami-delete-modal-title');

    const message = root.querySelector('.mayami-delete-modal-message');

    const confirmBtn = root.querySelector('.mayami-delete-confirm-btn');

    const cancelBtn = root.querySelector('.mayami-delete-cancel-btn');



    cancelBtn.addEventListener('click', closeDeleteConfirmModal);

    confirmBtn.addEventListener('click', function() {

      const control = pendingDeleteControl;

      closeDeleteConfirmModal();

      if (!control) {

        return;

      }

      control.dataset.mayamiDeleteConfirmed = '1';

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

      ? 'Tu es sur le point de supprimer "' + label + '". Cette action est définitive.'

      : 'Cette action est définitive. Veux-tu vraiment supprimer cet élément ?';



    deleteModalElements.root.hidden = false;

    document.body.classList.add('mayami-delete-modal-open');

    deleteModalElements.cancelBtn.focus();

  }



  function closeDeleteConfirmModal() {

    if (!deleteModalElements) {

      return;

    }



    deleteModalElements.root.hidden = true;

    document.body.classList.remove('mayami-delete-modal-open');

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

          collapseSharedStreamPlatformItemsByDefault();

          syncSharedStreamPlatformTitles();

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

    if (document.body.dataset.mayamiReleaseRowNameBound === '1') {

      return;

    }



    document.body.dataset.mayamiReleaseRowNameBound = '1';



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

      if (group.dataset.mayamiReleaseRowInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.mayamiReleaseRowInit = '1';

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

    if (document.body.dataset.mayamiStreamPlatformNameBound === '1') {

      return;

    }



    document.body.dataset.mayamiStreamPlatformNameBound = '1';



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

      if (group.dataset.mayamiStreamPlatformInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.mayamiStreamPlatformInit = '1';

    });

  }



  function getDefaultSharedStreamPlatformTitle(index) {

    return 'Plateforme partagee ' + (index + 1);

  }



  function syncSharedStreamPlatformTitles() {

    const groups = document.querySelectorAll('.cmb2-id-shared-stream-platforms .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const labelInput = group.querySelector('input[name*="[label]"]');

      if (!labelInput) {

        return;

      }



      const fallbackTitle = getDefaultSharedStreamPlatformTitle(index);

      const value = String(labelInput.value || '').trim();

      setStreamPlatformTitle(group, value || fallbackTitle);

    });

  }



  function bindSharedStreamPlatformNameEvents() {

    if (document.body.dataset.mayamiSharedStreamPlatformNameBound === '1') {

      return;

    }



    document.body.dataset.mayamiSharedStreamPlatformNameBound = '1';



    const syncFromInput = function(target) {

      if (!target || !target.matches('.cmb2-id-shared-stream-platforms .cmb-repeatable-grouping input[name*="[label]"]')) {

        return;

      }



      const group = target.closest('.cmb-repeatable-grouping');

      if (!group) {

        return;

      }



      const groups = Array.from(document.querySelectorAll('.cmb2-id-shared-stream-platforms .cmb-repeatable-grouping'));

      const index = Math.max(groups.indexOf(group), 0);

      const fallbackTitle = getDefaultSharedStreamPlatformTitle(index);

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



  function collapseSharedStreamPlatformItemsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-shared-stream-platforms .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group) {

      if (group.dataset.mayamiSharedStreamPlatformInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.mayamiSharedStreamPlatformInit = '1';

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

              newGroup.dataset.mayamiSliderInit = '1';

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

    if (document.body.dataset.mayamiSliderNameBound === '1') {

      return;

    }



    document.body.dataset.mayamiSliderNameBound = '1';



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

    if (!sliderRoot || sliderRoot.dataset.mayamiSliderObserver === '1' || typeof MutationObserver === 'undefined') {

      return;

    }



    sliderRoot.dataset.mayamiSliderObserver = '1';

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

            newGroup.dataset.mayamiSliderInit = '1';

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

      if (group.dataset.mayamiSliderInit === '1') {

        return;

      }



      closeSliderGroup(group);

      group.dataset.mayamiSliderInit = '1';

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

    const row = document.querySelector('.cmb2-id-marquee-logo-png');

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

    if (!scope || scope.dataset.mayamiMediaObserver === '1' || typeof MutationObserver === 'undefined') {

      return;

    }



    scope.dataset.mayamiMediaObserver = '1';

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

    const logoRow = document.querySelector('.cmb2-id-marquee-logo-png');

    const hideRow = document.querySelector('.cmb2-id-marquee-logo-hidden');

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



    let inlineWrap = logoTd.querySelector('.mayami-inline-hide-toggle');

    if (!inlineWrap) {

      inlineWrap = document.createElement('span');

      inlineWrap.className = 'mayami-inline-hide-toggle';



      const label = document.createElement('span');

      label.className = 'mayami-inline-hide-label';

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



    hideRow.classList.add('mayami-hidden-source-row');

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



      let inlineWrap = fieldTd.querySelector('.mayami-inline-field-toggle');

      if (!inlineWrap) {

        inlineWrap = document.createElement('span');

        inlineWrap.className = 'mayami-inline-field-toggle';



        const label = document.createElement('span');

        label.className = 'mayami-inline-field-toggle-label';

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



      toggleRow.classList.add('mayami-hidden-source-row');

    });

  }



  function renameMarqueeItemTitles() {

    const groups = document.querySelectorAll('.cmb2-id-marquee-items .cmb-repeatable-grouping');

    if (!groups.length) {

      return;

    }



    groups.forEach(function(group, index) {

      const titleSpan = group.querySelector('.cmb-group-title > span');

      if (!titleSpan) {

        return;

      }



      const customTitle = MARQUEE_ITEM_TITLES[index];

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



      if (target.closest('.cmb2-id-marquee-items .add-group-row, .cmb2-id-marquee-items .cmb-remove-group-row, .cmb2-id-marquee-items .cmb-remove-group-row-button, .cmb2-id-marquee-items .cmb-shift-rows, .cmb2-id-marquee-items .cmbhandlediv, .cmb2-id-marquee-items .handlediv')) {

        window.setTimeout(function() {

          renameMarqueeItemTitles();

          renameAllMediaUploadButtons();

          refreshTopBarVisualFieldUi();

          layoutTopBarVisualInlineToggle();

        }, 0);

      }



      if (target.closest('.cmb2-id-marquee-logo-png .cmb2-upload-button, .cmb2-id-marquee-logo-png .cmb2-remove-file-button')) {

        window.setTimeout(function() {

          renameAllMediaUploadButtons();

          refreshTopBarVisualFieldUi();

          layoutTopBarVisualInlineToggle();

        }, 0);

      }

    });

  }



  function collapseMarqueeItemsByDefault() {

    const groups = document.querySelectorAll('.cmb2-id-marquee-items .cmb-repeatable-grouping');

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

      { id: 'section_marquee_title', label: 'TOP-BAR' },

      { id: 'section_hero_title', label: 'Hero' },

      { id: 'section_slider_title', label: 'Slider' },

      { id: 'section_stream_title', label: 'Stream' },

      { id: 'section_stream_shared_title', label: '⧉' },

      { id: 'section_social_title', label: 'Social' },

      { id: 'section_video_title', label: 'Video' },

      { id: 'section_release_title', label: 'Release' },

      { id: 'section_cta_title', label: 'CTA' },

      { id: 'section_footer_title', label: 'Footer' }

    ];



    // Trouver le conteneur du formulaire

    const formContainer = document.querySelector('.cmb2-wrap');

    if (!formContainer) return;



    // Créer la navigation

    const nav = document.createElement('div');

    nav.id = 'mayami-admin-nav';

    nav.className = 'mayami-admin-nav';

    

    const navInner = document.createElement('div');

    navInner.className = 'mayami-admin-nav-inner';



    // Titre

    const title = document.createElement('a');

    title.href = '#';

    title.className = 'mayami-admin-home';

    title.setAttribute('title', 'Mayami Landing Local Settings');

    title.setAttribute('aria-label', 'Mayami Landing Local Settings');

    title.innerHTML = '<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>';

    title.style.cssText = 'display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; color:#fff; text-decoration:none; border-radius:999px; border:2px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.08); transition:all 0.2s ease;';

    title.addEventListener('click', function(e) {

      e.preventDefault();

      isOverviewMode = true;

      closeAllSections();

      clearActiveButtons();



      const nav = document.getElementById('mayami-admin-nav');

      if (!nav) return;



      const navTop = nav.getBoundingClientRect().top + window.pageYOffset;

      const offset = 20;

      window.scrollTo({

        top: Math.max(navTop - offset, 0),

        behavior: 'smooth'

      });

    });

    title.addEventListener('mouseenter', function() {

      this.style.background = 'rgba(255,255,255,0.16)';

      this.style.borderColor = 'rgba(255,255,255,0.35)';

      this.style.transform = 'translateY(-1px)';

    });

    title.addEventListener('mouseleave', function() {

      this.style.background = 'rgba(255,255,255,0.08)';

      this.style.borderColor = 'rgba(255,255,255,0.2)';

      this.style.transform = 'translateY(0)';

    });

    navInner.appendChild(title);



    // Container des boutons

    const buttonsContainer = document.createElement('div');

    buttonsContainer.className = 'mayami-admin-nav-buttons';



    sections.forEach(section => {

      const sectionEl = getSectionTitleElement(section.id);

      if (!sectionEl) return;



      const btn = document.createElement('a');

      btn.href = '#' + section.id;

      btn.textContent = section.label;

      btn.className = 'mayami-nav-btn';

      btn.dataset.section = section.id;



      if (section.id === 'section_modules_title') {

        btn.className += ' mayami-nav-btn--modules';

      }

      if (section.id === 'section_stream_shared_title') {

        btn.className += ' mayami-nav-btn--shared';

        btn.setAttribute('title', 'Stream partage');

        btn.setAttribute('aria-label', 'Stream partage');

      }

      btn.addEventListener('click', function(e) {

        e.preventDefault();

        isOverviewMode = false;

        openSection(section.id);

        scrollToSection(section.id);

        setActiveButton(btn);

      });



      buttonsContainer.appendChild(btn);

      if (section.id === 'section_modules_title') {

        const sep = document.createElement('span');

        sep.className = 'mayami-nav-sep';

        buttonsContainer.appendChild(sep);

      }

    });

    navInner.appendChild(buttonsContainer);

    

    // Bouton Enregistrer à droite

    const saveButton = document.createElement('button');

    saveButton.type = 'button';

    saveButton.className = 'mayami-save-btn';

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



    // Insérer avant le formulaire

    formContainer.parentNode.insertBefore(nav, formContainer);



    // Observer le scroll pour mettre à jour le bouton actif

    observeSections(sections);

  }



  function setupAccordion() {

    SECTION_IDS.forEach(sectionId => {

      const sectionTitle = getSectionTitleElement(sectionId);

      if (sectionTitle) {

        sectionTitle.setAttribute('data-mayami-section', sectionId);

        makeSectionTitleInteractive(sectionId);

      }

      closeSection(sectionId);

    });

  }



  function makeSectionTitleInteractive(sectionId) {

    const sectionTitle = getSectionTitleElement(sectionId);

    if (!sectionTitle || sectionTitle.dataset.mayamiClickable === '1') {

      return;

    }



    sectionTitle.dataset.mayamiClickable = '1';

    sectionTitle.setAttribute('role', 'button');

    sectionTitle.setAttribute('tabindex', '0');

    sectionTitle.setAttribute('aria-expanded', 'false');

    sectionTitle.classList.add('mayami-section-toggle');

    injectSectionEyeIcon(sectionTitle);



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

    if (!iconHost || iconHost.querySelector('.mayami-eye-indicator')) {

      return;

    }



    const eye = document.createElement('span');

    eye.className = 'mayami-eye-indicator dashicons dashicons-visibility';

    eye.setAttribute('aria-hidden', 'true');

    iconHost.appendChild(eye);

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

      if (btn.classList.contains('mayami-save-btn')) {

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



      if (btn.dataset.mayamiSaveStyled === '1') {

        return;

      }



      btn.dataset.mayamiSaveStyled = '1';

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



    const isOpen = sectionTitle.classList.contains('mayami-section-open');



    if (isOpen) {

      closeSection(sectionId);

      clearActiveButtons();

      isOverviewMode = true;

      return;

    }



    isOverviewMode = false;

    openSection(sectionId);

    setActiveButtonBySection(sectionId);

  }



  function setActiveButtonBySection(sectionId) {

    const btn = document.querySelector('.mayami-nav-btn[data-section="' + sectionId + '"]');

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



  function closeSection(sectionId) {

    const titleEl = getSectionTitleElement(sectionId);

    const rows = getSectionContentRows(sectionId);



    rows.forEach(row => {

      row.style.display = 'none';

    });



    if (titleEl) {

      titleEl.classList.remove('mayami-section-open');

      titleEl.classList.add('mayami-section-closed');

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

      titleEl.classList.remove('mayami-section-closed');

      titleEl.classList.add('mayami-section-open');

      titleEl.setAttribute('aria-expanded', 'true');

      applySectionHeaderState(titleEl, true);

    }

  }



  function applySectionHeaderState(titleEl, isOpen) {

    const headerCell = titleEl.querySelector('.cmb-th');

    const heading = titleEl.querySelector('.cmb2-metabox-title') || titleEl.querySelector('h3');

    const eye = titleEl.querySelector('.mayami-eye-indicator');

    const neutralBg = '#f2f2f3';

    const neutralText = '#1f2937';

    const activeBg = 'linear-gradient(135deg, #6a1b78 0%, #410b49 100%)';



    if (headerCell) {

      if (isOpen) {

        headerCell.style.setProperty('background-color', '#5b1b78', 'important');

        headerCell.style.setProperty('background-image', activeBg, 'important');

        headerCell.style.setProperty('background', activeBg, 'important');

        headerCell.style.setProperty('color', '#ffffff', 'important');

        headerCell.style.setProperty('border-left-color', '#13f7bc', 'important');

      } else {

        headerCell.style.setProperty('background-image', 'none', 'important');

        headerCell.style.setProperty('background-color', neutralBg, 'important');

        headerCell.style.setProperty('background', neutralBg, 'important');

        headerCell.style.setProperty('color', neutralText, 'important');

        headerCell.style.setProperty('border-left-color', '#dadde2', 'important');

      }

    }



    if (heading) {

      heading.style.setProperty('color', isOpen ? '#ffffff' : neutralText, 'important');

    }



    if (eye) {

      eye.style.setProperty('color', isOpen ? '#ffffff' : '#6b21a8', 'important');

    }

  }



  function scrollToSection(sectionId) {

    const sectionEl = document.querySelector('.cmb2-id-' + sectionId.replace(/_/g, '-'));

    if (!sectionEl) return;



    const nav = document.getElementById('mayami-admin-nav');

    const navHeight = nav ? nav.offsetHeight : 0;

    const offset = 20; // Padding supplémentaire



    const elementPosition = sectionEl.getBoundingClientRect().top + window.pageYOffset;

    const offsetPosition = elementPosition - navHeight - offset;



    window.scrollTo({

      top: offsetPosition,

      behavior: 'smooth'

    });

  }



  function setActiveButton(activeBtn) {

    const allBtns = document.querySelectorAll('.mayami-nav-btn');

    allBtns.forEach(btn => btn.classList.remove('active'));

    activeBtn.classList.add('active');

  }



  function clearActiveButtons() {

    const allBtns = document.querySelectorAll('.mayami-nav-btn');

    allBtns.forEach(btn => btn.classList.remove('active'));

  }



  function observeSections(sections) {

    const nav = document.getElementById('mayami-admin-nav');

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

          const btn = document.querySelector(`.mayami-nav-btn[data-section="${sectionId}"]`);

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

    // Style général pour smooth scroll

    document.documentElement.style.scrollBehavior = 'smooth';

  }

})();

