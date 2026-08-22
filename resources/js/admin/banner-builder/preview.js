/**
 * Grocery Banner Builder - Storefront Live Preview Modal Engine
 * High-fidelity customer view simulation across Desktop & Mobile store layouts.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderPreviewEngine {
    constructor(stateEngine, rendererEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.renderer = rendererEngine || window.BannerBuilderRenderer;
        this.$modal = null;
        this.previewDevice = 'desktop';
    }

    /**
     * Initialize preview modal event triggers and device switcher
     */
    init() {
        this.$modal = $('#modal-live-preview');
        if (!this.$modal.length) return;

        this.bindEvents();
    }

    /**
     * Bind open/close and device mockup switches
     */
    bindEvents() {
        const self = this;

        // Open Modal Button
        $('#btn-preview-modal').on('click', function () {
            self.open();
        });

        // Close Modal Button & Backdrop
        $('#btn-close-preview-modal, #btn-close-preview-modal-x').on('click', function () {
            self.close();
        });

        // Device Switcher inside Modal
        $('.preview-device-switch').on('click', function () {
            const dev = $(this).attr('data-preview-device') || 'desktop';
            self.switchPreviewDevice(dev);
        });

        // Close on Escape key
        $(document).on('keydown.bannerPreview', function (e) {
            if (e.key === 'Escape' && self.$modal.is(':visible')) {
                self.close();
            }
        });
    }

    /**
     * Open and render the live storefront preview
     */
    open() {
        if (!this.$modal.length) return;

        this.renderPreviewBanner();
        this.$modal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
    }

    /**
     * Close the preview modal
     */
    close() {
        if (!this.$modal.length) return;

        this.$modal.addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    }

    /**
     * Switch device mockup between Desktop and Mobile Smartphone
     */
    switchPreviewDevice(device) {
        this.previewDevice = device;

        $('.preview-device-switch')
            .removeClass('bg-emerald-600 text-white shadow-xs')
            .addClass('text-slate-400 hover:text-slate-200');

        $(`.preview-device-switch[data-preview-device="${device}"]`)
            .addClass('bg-emerald-600 text-white shadow-xs')
            .removeClass('text-slate-400 hover:text-slate-200');

        const $wrapper = $('#preview-storefront-wrapper');
        const $phoneFrame = $('#preview-phone-bezel');

        if (device === 'mobile') {
            $wrapper.addClass('max-w-[400px]').removeClass('max-w-6xl');
            $phoneFrame.addClass('border-8 border-slate-800 rounded-[36px] shadow-2xl p-2 bg-slate-900');
        } else {
            $wrapper.removeClass('max-w-[400px]').addClass('max-w-6xl');
            $phoneFrame.removeClass('border-8 border-slate-800 rounded-[36px] shadow-2xl p-2 bg-slate-900');
        }

        this.renderPreviewBanner();
    }

    /**
     * Render the active banner inside the preview storefront container
     */
    renderPreviewBanner() {
        const $container = $('#preview-banner-render-box');
        if (!$container.length) return;

        $container.empty();

        const canvasData = this.state.getCanvas();
        const elements = this.state.getElements();

        // 1. Create outer banner box
        const $bannerBox = $('<div></div>')
            .addClass('relative w-full overflow-hidden rounded-2xl shadow-lg')
            .css({
                aspectRatio: this.previewDevice === 'mobile' ? '480 / 380' : '1920 / 700',
                backgroundColor: canvasData.backgroundColor || '#f8fafc',
            });

        // 2. Background image layer
        if (canvasData.backgroundImage) {
            const $bg = $('<div></div>')
                .addClass('absolute inset-0 bg-cover bg-center')
                .css('background-image', `url('${canvasData.backgroundImage}')`);
            $bannerBox.append($bg);
        }

        // 3. Overlay dimmer layer
        const opacity = (canvasData.overlayOpacity !== undefined ? canvasData.overlayOpacity : 0) / 100;
        const $overlay = $('<div></div>')
            .addClass('absolute inset-0')
            .css({
                backgroundColor: canvasData.overlayColor || '#000000',
                opacity: opacity,
            });
        $bannerBox.append($overlay);

        // 4. Interactive Elements Container
        const $elementsContainer = $('<div></div>').addClass('absolute inset-0 z-10 pointer-events-auto');

        const sorted = [...elements].sort((a, b) => (a.zIndex || 10) - (b.zIndex || 10));

        sorted.forEach(elem => {
            if (!elem.visible) return;

            const style = elem.style || {};
            const scaleX = style.flipH ? -1 : 1;
            const scaleY = style.flipV ? -1 : 1;
            const transformStr = `rotate(${elem.rotation || 0}deg) scale(${scaleX}, ${scaleY})`;

            const $elemNode = $('<div></div>')
                .addClass('absolute')
                .css({
                    left: `${elem.x}%`,
                    top: `${elem.y}%`,
                    width: `${elem.width}%`,
                    height: `${elem.height}%`,
                    zIndex: elem.zIndex || 10,
                    transform: transformStr,
                });

            // Render inner content without edit handles
            const $content = this.renderer.renderElementContent(elem, style);
            $elemNode.append($content);

            $elementsContainer.append($elemNode);
        });

        $bannerBox.append($elementsContainer);
        $container.append($bannerBox);

        if (window.renderLucideIcons) {
            window.renderLucideIcons();
        }
    }
}

// Instantiate and expose globally
const BannerBuilderPreview = new BannerBuilderPreviewEngine();
window.BannerBuilderPreview = BannerBuilderPreview;

export default BannerBuilderPreview;
