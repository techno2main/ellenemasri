(function() {

  function scrollToTarget(selector) {

    var target = selector ? document.querySelector(selector) : null;

    if (!target) {

      return;

    }



    target.scrollIntoView({ behavior: 'smooth', block: 'start' });

  }



  function convertInternalAnchors(root) {

    var scope = root || document;

    var anchors = scope.querySelectorAll('a[href^="#"]');



    anchors.forEach(function(anchor) {

      if (anchor.hasAttribute('data-ellene-wp-scroll-bound')) {

        return;

      }



      var target = anchor.getAttribute('href');

      if (!target || target.charAt(0) !== '#') {

        return;

      }



      if (anchor.getAttribute('href') === '#') {

        return;

      }



      var button = document.createElement('button');

      button.type = 'button';

      button.className = anchor.className;

      button.id = anchor.id;

      button.style.cssText = anchor.style.cssText;

      button.title = anchor.title;

      button.setAttribute('aria-label', anchor.getAttribute('aria-label') || anchor.textContent.trim() || 'Aller a la section');

      button.setAttribute('data-scroll-target', target);

      button.setAttribute('data-ellene-wp-scroll-bound', '1');



      if (anchor.hasAttribute('aria-current')) {

        button.setAttribute('aria-current', anchor.getAttribute('aria-current'));

      }



      if (anchor.hasAttribute('aria-expanded')) {

        button.setAttribute('aria-expanded', anchor.getAttribute('aria-expanded'));

      }



      if (anchor.hasAttribute('target')) {

        button.setAttribute('target', anchor.getAttribute('target'));

      }



      if (anchor.hasAttribute('rel')) {

        button.setAttribute('rel', anchor.getAttribute('rel'));

      }



      button.innerHTML = anchor.innerHTML;



      anchor.replaceWith(button);

    });

  }



  function protectMedia(root) {

    var scope = root || document;

    var media = scope.querySelectorAll('img, video');



    media.forEach(function(el) {

      el.setAttribute('draggable', 'false');

      el.setAttribute('data-ellene-wp-protected-media', '1');

      if (el.tagName === 'VIDEO') {

        el.setAttribute('controlslist', 'nodownload noplaybackrate noremoteplayback');

        el.setAttribute('disablepictureinpicture', '');

      }

    });

  }



  function shouldBlockTarget(target) {

    return target && (target.tagName === 'IMG' || target.tagName === 'VIDEO');

  }



  document.addEventListener('contextmenu', function(event) {

    if (shouldBlockTarget(event.target)) {

      event.preventDefault();

    }

  }, true);



  document.addEventListener('dragstart', function(event) {

    if (shouldBlockTarget(event.target)) {

      event.preventDefault();

    }

  }, true);



  document.addEventListener('selectstart', function(event) {

    if (shouldBlockTarget(event.target)) {

      event.preventDefault();

    }

  }, true);



  document.addEventListener('keydown', function(event) {

    var key = String(event.key || '').toLowerCase();

    if ((event.ctrlKey || event.metaKey) && (key === 's' || key === 'u')) {

      event.preventDefault();

    }

  }, true);



  document.addEventListener('click', function(event) {

    var trigger = event.target.closest ? event.target.closest('button[data-scroll-target]') : null;

    if (!trigger) {

      return;

    }



    var target = trigger.getAttribute('data-scroll-target');

    if (!target) {

      return;

    }



    event.preventDefault();

    scrollToTarget(target);

  }, true);



  if (document.readyState === 'loading') {

    document.addEventListener('DOMContentLoaded', function() {

      protectMedia(document);

      convertInternalAnchors(document);

    });

  } else {

    protectMedia(document);

    convertInternalAnchors(document);

  }



  var observer = new MutationObserver(function(mutations) {

    mutations.forEach(function(mutation) {

      mutation.addedNodes.forEach(function(node) {

        if (node && node.nodeType === 1) {

          if (node.matches && (node.matches('img, video') || node.querySelector('img, video'))) {

            protectMedia(node);

          }



          if (node.matches && node.matches('a[href^="#"]')) {

            convertInternalAnchors(node.parentNode || document);

          } else if (node.querySelector && node.querySelector('a[href^="#"]')) {

            convertInternalAnchors(node);

          }

        }

      });

    });

  });



  observer.observe(document.documentElement, { childList: true, subtree: true });

})();

