(function (window) {
    'use strict';

    function SlideSortable(container, options) {
        this.container = container;
        this.handleSelector = (options && options.handle) || '.em-site-slide-sortable__handle';
        this.itemSelector = (options && options.item) || '[data-slide-item]';
        this.onEnd = options && options.onEnd ? options.onEnd : null;
        this.dragging = null;
        this.placeholder = null;
        this.pointerId = null;
        this.dragOffsetY = 0;
        this.dragWidth = 0;

        this.onPointerDown = this.onPointerDown.bind(this);
        this.onPointerMove = this.onPointerMove.bind(this);
        this.onPointerUp = this.onPointerUp.bind(this);

        this.container.addEventListener('pointerdown', this.onPointerDown);
    }

    SlideSortable.prototype.destroy = function () {
        this.container.removeEventListener('pointerdown', this.onPointerDown);
        this.cleanup();
    };

    SlideSortable.prototype.getItems = function () {
        return Array.from(this.container.querySelectorAll(this.itemSelector)).filter(function (node) {
            return !node.classList.contains('em-site-slide-sortable__placeholder');
        });
    };

    SlideSortable.prototype.resetDraggingStyles = function () {
        if (!this.dragging) {
            return;
        }

        this.dragging.classList.remove('is-dragging');
        this.dragging.style.position = '';
        this.dragging.style.left = '';
        this.dragging.style.top = '';
        this.dragging.style.width = '';
        this.dragging.style.zIndex = '';
        this.dragging.style.transform = '';
        this.dragging.style.boxSizing = '';
    };

    SlideSortable.prototype.cleanup = function () {
        if (this.dragging) {
            if (typeof this.dragging.releasePointerCapture === 'function' && this.pointerId !== null) {
                try {
                    this.dragging.releasePointerCapture(this.pointerId);
                } catch (error) {
                    // Ignore if capture was already released.
                }
            }

            this.resetDraggingStyles();
            this.dragging = null;
        }

        if (this.placeholder && this.placeholder.parentNode) {
            this.placeholder.parentNode.removeChild(this.placeholder);
        }

        this.placeholder = null;
        this.pointerId = null;
        this.dragOffsetY = 0;
        this.dragWidth = 0;
        document.body.classList.remove('em-site-slide-sortable-active');
        window.removeEventListener('pointermove', this.onPointerMove);
        window.removeEventListener('pointerup', this.onPointerUp);
        window.removeEventListener('pointercancel', this.onPointerUp);
    };

    SlideSortable.prototype.onPointerDown = function (event) {
        var handle = event.target.closest(this.handleSelector);
        if (!handle || !this.container.contains(handle)) {
            return;
        }

        var item = handle.closest(this.itemSelector);
        if (!item || !this.container.contains(item)) {
            return;
        }

        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        this.cleanup();
        this.dragging = item;
        this.pointerId = event.pointerId;

        var rect = this.dragging.getBoundingClientRect();
        this.dragOffsetY = event.clientY - rect.top;
        this.dragWidth = rect.width;

        this.placeholder = document.createElement('div');
        this.placeholder.className = 'em-site-slide-sortable__placeholder';
        this.placeholder.style.height = rect.height + 'px';
        this.container.insertBefore(this.placeholder, this.dragging);

        this.dragging.classList.add('is-dragging');
        this.dragging.style.position = 'fixed';
        this.dragging.style.left = rect.left + 'px';
        this.dragging.style.top = rect.top + 'px';
        this.dragging.style.width = this.dragWidth + 'px';
        this.dragging.style.zIndex = '100000';
        this.dragging.style.boxSizing = 'border-box';

        if (typeof this.dragging.setPointerCapture === 'function') {
            this.dragging.setPointerCapture(event.pointerId);
        }

        document.body.classList.add('em-site-slide-sortable-active');
        window.addEventListener('pointermove', this.onPointerMove, { passive: false });
        window.addEventListener('pointerup', this.onPointerUp);
        window.addEventListener('pointercancel', this.onPointerUp);
    };

    SlideSortable.prototype.updatePlaceholderPosition = function (clientY) {
        var items = this.getItems().filter(function (node) {
            return node !== this.dragging;
        }, this);

        var target = null;

        for (var i = 0; i < items.length; i++) {
            var rect = items[i].getBoundingClientRect();
            var midpoint = rect.top + rect.height / 2;

            if (clientY < midpoint) {
                target = items[i];
                break;
            }
        }

        if (target) {
            this.container.insertBefore(this.placeholder, target);
        } else {
            this.container.appendChild(this.placeholder);
        }
    };

    SlideSortable.prototype.onPointerMove = function (event) {
        if (!this.dragging || event.pointerId !== this.pointerId) {
            return;
        }

        event.preventDefault();

        var nextTop = event.clientY - this.dragOffsetY;
        this.dragging.style.top = nextTop + 'px';
        this.updatePlaceholderPosition(event.clientY);
    };

    SlideSortable.prototype.onPointerUp = function (event) {
        if (!this.dragging || event.pointerId !== this.pointerId) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (this.placeholder && this.placeholder.parentNode) {
            this.container.insertBefore(this.dragging, this.placeholder);
        }

        this.cleanup();

        if (typeof this.onEnd === 'function') {
            this.onEnd();
        }
    };

    window.EmWpSlideSortable = SlideSortable;
})(window);
