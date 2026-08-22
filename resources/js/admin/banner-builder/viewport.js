/**
 * Grocery Banner Builder - Viewport, Zoom & Multi-Device Adaptation Engine
 * Responsive canvas previewing across Desktop, Tablet & Mobile with precision zoom scaling.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderViewportEngine {
    constructor(stateEngine, rendererEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.renderer = rendererEngine || window.BannerBuilderRenderer;
    }

    /**
     * Initialize device switcher buttons, zoom controls, and mouse wheel/hotkeys
     */
    init() {
        this.bindDeviceSwitcher();
        this.bindZoomControls();
        this.bindZoomShortcuts();
    }

    /**
     * Bind Desktop / Tablet / Mobile device switcher toolbar buttons
     */
    bindDeviceSwitcher() {
        const self = this;

        $('.device-switch-btn').on('click', function () {
            const device = $(this).attr('data-device') || 'desktop';

            $('.device-switch-btn')
                .removeClass('bg-emerald-600 text-white shadow-xs')
                .addClass('text-slate-400 hover:text-slate-200');

            $(this)
                .addClass('bg-emerald-600 text-white shadow-xs')
                .removeClass('text-slate-400 hover:text-slate-200');

            self.state.setDevice(device);
        });
    }

    /**
     * Bind Zoom In, Zoom Out, and Zoom Reset (Fit) buttons
     */
    bindZoomControls() {
        const self = this;

        // Zoom In (+)
        $('#btn-zoom-in').on('click', function () {
            let currentScale = self.getCurrentScale();
            let newScale = Math.min(1.6, Math.round((currentScale + 0.15) * 100) / 100);
            self.state.setZoom(newScale);
        });

        // Zoom Out (-)
        $('#btn-zoom-out').on('click', function () {
            let currentScale = self.getCurrentScale();
            let newScale = Math.max(0.2, Math.round((currentScale - 0.15) * 100) / 100);
            self.state.setZoom(newScale);
        });

        // Reset to Fit
        $('#btn-zoom-reset').on('click', function () {
            self.state.setZoom('fit');
        });
    }

    /**
     * Get current numeric zoom scale factor
     */
    getCurrentScale() {
        const zoom = this.state.state.zoom;
        if (zoom === 'fit' || !zoom) {
            return this.renderer.scale || 0.65;
        }
        return parseFloat(zoom) || 0.65;
    }

    /**
     * Bind Ctrl+Plus, Ctrl+Minus, and Ctrl+0 zoom hotkeys
     */
    bindZoomShortcuts() {
        const self = this;

        $(document).on('keydown.bannerZoom', function (e) {
            const isCtrl = e.ctrlKey || e.metaKey;
            if (!isCtrl) return;

            if (e.key === '=' || e.key === '+') {
                e.preventDefault();
                $('#btn-zoom-in').trigger('click');
            } else if (e.key === '-' || e.key === '_') {
                e.preventDefault();
                $('#btn-zoom-out').trigger('click');
            } else if (e.key === '0') {
                e.preventDefault();
                $('#btn-zoom-reset').trigger('click');
            }
        });
    }
}

// Instantiate and expose globally
const BannerBuilderViewport = new BannerBuilderViewportEngine();
window.BannerBuilderViewport = BannerBuilderViewport;

export default BannerBuilderViewport;
