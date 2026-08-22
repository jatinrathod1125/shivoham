/**
 * Grocery Banner Builder - Canvas Rendering Engine
 * Pure jQuery renderer for 1920x700 responsive virtual canvas and elements.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderCanvasRenderer {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.$stage = null;
        this.$frame = null;
        this.$canvas = null;
        this.$elementsContainer = null;
        this.$floatingToolbar = null;
        this.scale = 1.0;
        this.resizeDebounceTimer = null;
    }

    /**
     * Initialize DOM references and register state observer listeners
     */
    init() {
        this.$stage = $('#builder-stage-wrapper');
        this.$frame = $('#canvas-viewport-frame');
        this.$canvas = $('#banner-canvas');
        this.$elementsContainer = $('#canvas-elements-container');
        this.$floatingToolbar = $('#canvas-floating-toolbar');

        if (!this.$stage.length || !this.$canvas.length) {
            return;
        }

        // Attach state change listeners
        this.state.on('state:changed', () => this.render());
        this.state.on('element:added', () => this.render());
        this.state.on('element:updated', () => this.render());
        this.state.on('element:removed', () => this.render());
        this.state.on('element:selected', (elem) => this.updateSelection(elem));
        this.state.on('element:deselected', () => this.updateSelection(null));
        this.state.on('canvas:updated', () => this.renderCanvasBase());
        this.state.on('zoom:changed', () => this.updateScale());
        this.state.on('device:changed', () => this.updateDeviceDimensions());
        this.state.on('grid:toggled', (show) => this.toggleGrid(show));

        // Window resize event to auto-fit canvas smoothly
        $(window).on('resize.bannerBuilder', () => {
            clearTimeout(this.resizeDebounceTimer);
            this.resizeDebounceTimer = setTimeout(() => {
                this.updateScale();
            }, 100);
        });

        // Initial render
        this.render();
    }

    /**
     * Complete canvas & element re-render
     */
    render() {
        const fullState = this.state.getState();
        this.updateDeviceDimensions(fullState.device);
        this.renderCanvasBase(fullState.canvas);
        this.renderElements(fullState.elements, fullState.selectedElementId);
        this.updateScale();
        this.updateTelemetry(this.state.getSelectedElement());
        this.updateFloatingToolbar(this.state.getSelectedElement());
    }

    /**
     * Update device preset dimensions
     */
    updateDeviceDimensions(device = 'desktop') {
        let width = 1920;
        let height = 700;

        if (device === 'tablet') {
            width = 1024;
            height = 500;
        } else if (device === 'mobile') {
            width = 480;
            height = 420;
        }

        const sizeStyles = {
            width: `${width}px`,
            height: `${height}px`,
            minWidth: `${width}px`,
            minHeight: `${height}px`,
            maxWidth: `${width}px`,
            maxHeight: `${height}px`,
            flexShrink: 0,
        };

        this.$canvas.css(sizeStyles);
        if (this.$frame && this.$frame.length) {
            this.$frame.css(sizeStyles);
        }

        $('#status-canvas-size').text(`${width} × ${height} px`);
    }

    /**
     * Compute and apply responsive scaling to fit available viewport stage
     */
    updateScale() {
        if (!this.$stage.length || !this.$frame.length) return;

        const stageW = this.$stage.width() || 1000;
        const stageH = this.$stage.height() || 600;

        let canvasW = 1920;
        let canvasH = 700;
        const device = this.state.state.device || 'desktop';
        if (device === 'tablet') {
            canvasW = 1024;
            canvasH = 500;
        } else if (device === 'mobile') {
            canvasW = 480;
            canvasH = 420;
        }

        const padX = 48;
        const padY = 48;

        const availableW = Math.max(stageW - padX, 200);
        const availableH = Math.max(stageH - padY, 150);

        let scale = 1.0;
        const zoomSetting = this.state.state.zoom;

        if (zoomSetting === 'fit' || !zoomSetting) {
            scale = Math.min(availableW / canvasW, availableH / canvasH);
            scale = Math.min(Math.max(scale, 0.15), 1.5); // Clamp scale
            $('#zoom-level-text').text('Fit');
            $('#status-zoom-scale').text(`${Math.round(scale * 100)}% (Fit)`);
        } else {
            scale = parseFloat(zoomSetting);
            $('#zoom-level-text').text(`${Math.round(scale * 100)}%`);
            $('#status-zoom-scale').text(`${Math.round(scale * 100)}%`);
        }

        this.scale = scale;
        this.state.setZoomScale(scale);

        this.$frame.css({
            transform: `scale(${scale})`,
            transformOrigin: 'center center',
        });
    }

    /**
     * Render canvas background color, background image, and overlay dimmer
     */
    renderCanvasBase(canvasData) {
        const canvas = canvasData || this.state.getCanvas();

        // Base background color
        this.$canvas.css('background-color', canvas.backgroundColor || '#f8fafc');

        // Background image
        const $bgLayer = $('#canvas-bg-layer');
        if ($bgLayer.length) {
            if (canvas.backgroundImage) {
                $bgLayer.css({
                    'background-image': `url('${canvas.backgroundImage}')`,
                    'background-size': 'cover',
                    'background-position': 'center center',
                    'background-repeat': 'no-repeat',
                    'display': 'block',
                });
            } else {
                $bgLayer.css({
                    'background-image': 'none',
                    'display': 'none',
                });
            }
        }

        // Overlay dimmer
        const $overlay = $('#canvas-overlay-layer');
        if ($overlay.length) {
            const opacity = (canvas.overlayOpacity !== undefined ? canvas.overlayOpacity : 0) / 100;
            $overlay.css({
                'background-color': canvas.overlayColor || '#000000',
                'opacity': opacity,
            });
        }
    }

    /**
     * Render all interactive canvas element nodes
     */
    renderElements(elementsList, selectedId) {
        const elements = elementsList || this.state.getElements();
        this.$elementsContainer.empty();

        // Sort elements by z-index ascending
        const sorted = [...elements].sort((a, b) => (a.zIndex || 10) - (b.zIndex || 10));

        sorted.forEach(elem => {
            const $elemNode = this.buildElementNode(elem, elem.id === selectedId);
            this.$elementsContainer.append($elemNode);
        });

        // Re-initialize Lucide icons inside rendered components
        if (window.renderLucideIcons) {
            window.renderLucideIcons();
        }
    }

    /**
     * Build single element DOM node
     */
    buildElementNode(elem, isSelected = false) {
        const style = elem.style || {};

        const scaleX = style.flipH ? -1 : 1;
        const scaleY = style.flipV ? -1 : 1;
        const transformStr = `rotate(${elem.rotation || 0}deg) scale(${scaleX}, ${scaleY})`;

        const $wrapper = $('<div></div>')
            .addClass('builder-element')
            .attr('id', `canvas-node-${elem.id}`)
            .attr('data-element-id', elem.id)
            .attr('data-element-type', elem.type)
            .css({
                left: `${elem.x}%`,
                top: `${elem.y}%`,
                width: `${elem.width}%`,
                height: `${elem.height}%`,
                zIndex: elem.zIndex || 10,
                transform: transformStr,
                display: elem.visible ? 'block' : 'none',
            });

        if (isSelected) {
            $wrapper.addClass('is-selected');
        }

        if (elem.locked) {
            $wrapper.addClass('is-locked');
        }

        // Render inner HTML content based on element type
        const $content = this.renderElementContent(elem, style);
        $wrapper.append($content);

        // Append 8-point resize handles if selected
        if (isSelected && !elem.locked) {
            $wrapper.append(this.buildResizeHandlesHTML());
        }

        return $wrapper;
    }

    /**
     * Render specific element inner HTML by type
     */
    renderElementContent(elem, style) {
        const type = elem.type || 'text';

        switch (type) {
            case 'text':
                return $('<div></div>')
                    .text(elem.content || 'Headline Text')
                    .css({
                        fontFamily: style.fontFamily || 'Instrument Sans, sans-serif',
                        fontSize: `${style.fontSize || 32}px`,
                        fontWeight: style.fontWeight || 700,
                        color: style.color || '#ffffff',
                        textAlign: style.textAlign || 'left',
                        lineHeight: style.lineHeight || 1.15,
                        letterSpacing: `${style.letterSpacing || 0}px`,
                        textShadow: style.textShadow || 'none',
                        opacity: (style.opacity !== undefined ? style.opacity : 100) / 100,
                        wordBreak: 'break-word',
                        width: '100%',
                        height: '100%',
                    });

            case 'button':
                return $('<div></div>')
                    .addClass('inline-flex items-center justify-center font-bold transition-all shadow-md')
                    .text(elem.content || 'Shop Now')
                    .css({
                        fontFamily: style.fontFamily || 'Instrument Sans, sans-serif',
                        fontSize: `${style.fontSize || 16}px`,
                        fontWeight: style.fontWeight || 600,
                        backgroundColor: style.backgroundColor || '#16a34a',
                        color: style.color || '#ffffff',
                        borderRadius: `${style.borderRadius || 12}px`,
                        padding: `${style.paddingY || 12}px ${style.paddingX || 24}px`,
                        opacity: (style.opacity !== undefined ? style.opacity : 100) / 100,
                        width: '100%',
                        height: '100%',
                    });

            case 'badge':
                return $('<div></div>')
                    .addClass('inline-flex items-center justify-center font-extrabold uppercase tracking-wider shadow-sm')
                    .text(elem.content || '50% OFF')
                    .css({
                        fontFamily: style.fontFamily || 'Instrument Sans, sans-serif',
                        fontSize: `${style.fontSize || 14}px`,
                        backgroundColor: style.backgroundColor || '#ef4444',
                        color: style.color || '#ffffff',
                        borderRadius: `${style.borderRadius || 9999}px`,
                        padding: '6px 14px',
                        opacity: (style.opacity !== undefined ? style.opacity : 100) / 100,
                        width: '100%',
                        height: '100%',
                    });

            case 'product':
                const prod = elem.productData || {
                    name: elem.content || 'Organic Farm Produce',
                    price: '₹149.00',
                    image: '/images/placeholder.svg',
                    badge: 'FEATURED',
                };
                const theme = style.theme || 'dark-glass';
                let themeClasses = 'bg-slate-900/85 backdrop-blur-md border border-white/15 text-white';
                let badgeClasses = 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                let priceClasses = 'text-emerald-400';
                let titleClasses = 'text-white';

                if (theme === 'light-pill') {
                    themeClasses = 'bg-white/95 backdrop-blur-md border border-slate-200 text-slate-900 shadow-xl';
                    badgeClasses = 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                    priceClasses = 'text-emerald-600';
                    titleClasses = 'text-slate-900';
                } else if (theme === 'flash-deal') {
                    themeClasses = 'bg-gradient-to-r from-amber-500/25 to-rose-500/25 backdrop-blur-md border border-amber-500/40 text-white shadow-xl';
                    badgeClasses = 'bg-amber-500/30 text-amber-300 border border-amber-500/50';
                    priceClasses = 'text-rose-400';
                    titleClasses = 'text-white';
                }

                return $(`
                    <div class="canvas-product-card ${themeClasses}" style="border-radius: ${style.borderRadius || 16}px; opacity: ${(style.opacity !== undefined ? style.opacity : 100) / 100};">
                        <img src="${prod.image || '/images/placeholder.svg'}" alt="${prod.name}" onerror="this.onerror=null;this.src='/images/placeholder.svg';" class="bg-slate-950/20" />
                        <div class="min-w-0 flex-1">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold ${badgeClasses} uppercase tracking-wide">${prod.badge || 'FEATURED'}</span>
                            <h4 class="text-xs sm:text-sm font-bold ${titleClasses} truncate mt-0.5">${prod.name}</h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs sm:text-base font-extrabold ${priceClasses}">${prod.price || '₹199.00'}</span>
                            </div>
                        </div>
                    </div>
                `);

            case 'image':
                return $('<img>')
                    .attr('src', elem.url || '/images/placeholder.svg')
                    .attr('alt', 'Graphic Asset')
                    .attr('onerror', "this.onerror=null;this.src='/images/placeholder.svg';")
                    .css({
                        width: '100%',
                        height: '100%',
                        objectFit: style.objectFit || 'cover',
                        borderRadius: `${style.borderRadius || 0}px`,
                        opacity: (style.opacity !== undefined ? style.opacity : 100) / 100,
                    });

            case 'shape':
                return $('<div></div>')
                    .css({
                        width: '100%',
                        height: '100%',
                        backgroundColor: style.backgroundColor || 'rgba(15, 23, 42, 0.75)',
                        borderRadius: `${style.borderRadius || 16}px`,
                        border: `${style.borderWidth || 1}px solid ${style.borderColor || 'rgba(255,255,255,0.15)'}`,
                        backdropFilter: 'blur(12px)',
                        opacity: (style.opacity !== undefined ? style.opacity : 100) / 100,
                    });

            default:
                return $('<div></div>').text(elem.content || '');
        }
    }

    /**
     * Generate HTML for 8 resize handles plus rotation point
     */
    buildResizeHandlesHTML() {
        return `
            <div class="builder-element-handles">
                <div class="handle-point handle-rot" data-handle="rot" title="Rotate Element"></div>
                <div class="handle-point handle-nw" data-handle="nw"></div>
                <div class="handle-point handle-n" data-handle="n"></div>
                <div class="handle-point handle-ne" data-handle="ne"></div>
                <div class="handle-point handle-e" data-handle="e"></div>
                <div class="handle-point handle-se" data-handle="se"></div>
                <div class="handle-point handle-s" data-handle="s"></div>
                <div class="handle-point handle-sw" data-handle="sw"></div>
                <div class="handle-point handle-w" data-handle="w"></div>
            </div>
        `;
    }

    /**
     * Update active selection highlight
     */
    updateSelection(selectedElem) {
        $('.builder-element').removeClass('is-selected');
        $('.builder-element-handles').remove();

        if (selectedElem) {
            const $node = $(`#canvas-node-${selectedElem.id}`);
            if ($node.length) {
                $node.addClass('is-selected');
                if (!selectedElem.locked) {
                    $node.append(this.buildResizeHandlesHTML());
                }
            }
        }

        this.updateTelemetry(selectedElem);
        this.updateFloatingToolbar(selectedElem);
    }

    /**
     * Update bottom status bar telemetry
     */
    updateTelemetry(selectedElem) {
        const $statusElem = $('#status-selected-elem');
        const $statusCoords = $('#status-telemetry');

        if (selectedElem) {
            $statusElem.text(`${selectedElem.type.toUpperCase()}: ${selectedElem.id}`);
            $statusCoords.text(`X: ${selectedElem.x}% | Y: ${selectedElem.y}% | W: ${selectedElem.width}% | H: ${selectedElem.height}% | Rot: ${selectedElem.rotation || 0}°`);
        } else {
            $statusElem.text('None (Canvas Selected)');
            $statusCoords.text('X: 0 | Y: 0 | W: 100% | H: 100%');
        }
    }

    /**
     * Position floating toolbar above selected element
     */
    updateFloatingToolbar(selectedElem) {
        if (!this.$floatingToolbar.length) return;

        if (!selectedElem) {
            this.$floatingToolbar.addClass('hidden');
            return;
        }

        this.$floatingToolbar.removeClass('hidden');

        // Update lock icon state
        const $lockIcon = $('#float-btn-lock i');
        if ($lockIcon.length) {
            $lockIcon.attr('data-lucide', selectedElem.locked ? 'lock' : 'unlock');
            if (window.renderLucideIcons) window.renderLucideIcons();
        }
    }

    toggleGrid(show) {
        $('#canvas-grid-overlay').toggle(show);
    }
}

// Instantiate and expose globally
const BannerBuilderRenderer = new BannerBuilderCanvasRenderer();
window.BannerBuilderRenderer = BannerBuilderRenderer;

export default BannerBuilderRenderer;
