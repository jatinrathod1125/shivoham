/**
 * Grocery Banner Builder - Selection, Layer Sync & Inspector Engine
 * Pure jQuery event binding and two-way properties synchronization.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderSelectionEngine {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;
    }

    /**
     * Initialize selection event listeners and two-way inspector bindings
     */
    init() {
        this.bindCanvasSelection();
        this.bindInspectorInputs();
        this.bindLayerTreeEvents();
        this.bindDrawerNavigation();
        this.bindStateObservers();

        // Initial sync of inspector & layer tree
        this.syncInspector();
        this.renderLayerTree();
    }

    /**
     * Observe state changes to keep selection, layer tree, and inspector in sync
     */
    bindStateObservers() {
        this.state.on('element:selected', (elem) => {
            this.syncInspector(elem);
            this.highlightActiveLayer(elem ? elem.id : null);
        });

        this.state.on('element:deselected', () => {
            this.syncInspector(null);
            this.highlightActiveLayer(null);
        });

        this.state.on('state:changed', () => {
            this.renderLayerTree();
        });

        this.state.on('element:updated', (elem) => {
            if (this.state.state.selectedElementId === elem.id) {
                this.populateElementInspectorValues(elem);
            }
        });
    }

    /**
     * Canvas element clicking & background deselection
     */
    bindCanvasSelection() {
        const self = this;

        // Click element on canvas
        $('#canvas-elements-container').on('mousedown click', '.builder-element', function (e) {
            e.stopPropagation();
            const elemId = $(this).attr('data-element-id');
            if (elemId) {
                self.state.selectElement(elemId);
            }
        });

        // Click canvas background or stage to deselect
        $('#builder-stage-wrapper, #banner-canvas').on('click', function (e) {
            if ($(e.target).closest('.builder-element, .handle-point, #canvas-floating-toolbar').length === 0) {
                self.state.clearSelection();
            }
        });
    }

    /**
     * Bind two-way property editing inside the right inspector panel
     */
    bindInspectorInputs() {
        const self = this;

        // --- Canvas Property Controls ---
        $('#prop-canvas-bgcolor').on('input change', function () {
            const val = $(this).val();
            $('#prop-canvas-bgcolor-hex').val(val);
            self.state.updateCanvas({ backgroundColor: val });
        });

        $('#prop-canvas-bgcolor-hex').on('change', function () {
            const val = $(this).val();
            $('#prop-canvas-bgcolor').val(val);
            self.state.updateCanvas({ backgroundColor: val });
        });

        $('#prop-canvas-overlaycolor').on('input change', function () {
            const val = $(this).val();
            $('#prop-canvas-overlaycolor-hex').val(val);
            self.state.updateCanvas({ overlayColor: val });
        });

        $('#prop-canvas-overlaycolor-hex').on('change', function () {
            const val = $(this).val();
            $('#prop-canvas-overlaycolor').val(val);
            self.state.updateCanvas({ overlayColor: val });
        });

        $('#prop-canvas-opacity').on('input change', function () {
            const val = parseInt($(this).val(), 10) || 0;
            $('#prop-canvas-opacity-val').text(`${val}%`);
            self.state.updateCanvas({ overlayOpacity: val });
        });

        // --- Selected Element Property Controls ---
        $('#prop-element-content').on('input', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { content: $(this).val() }, false);
        }).on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { content: $(this).val() }, true);
        });

        $('#prop-element-url').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { url: $(this).val() });
        });

        $('#prop-font-family').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const font = $(this).val();
            if (window.BannerBuilderFonts) {
                window.BannerBuilderFonts.loadFont(font);
            }
            self.state.updateElement(elem.id, { style: { fontFamily: font } });
        });

        $('#prop-font-size').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const size = parseInt($(this).val(), 10) || 24;
            self.state.updateElement(elem.id, { style: { fontSize: size } });
        });

        $('#prop-font-weight').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { style: { fontWeight: $(this).val() } });
        });

        $('#prop-line-height').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const lh = parseFloat($(this).val()) || 1.15;
            self.state.updateElement(elem.id, { style: { lineHeight: lh } });
        });

        $('#prop-letter-spacing').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const ls = parseFloat($(this).val()) || 0;
            self.state.updateElement(elem.id, { style: { letterSpacing: ls } });
        });

        $('#prop-text-shadow').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const preset = $(this).val();
            const shadowVal = (window.BannerBuilderFonts && window.BannerBuilderFonts.textShadowPresets[preset]) || preset;
            self.state.updateElement(elem.id, { style: { textShadow: shadowVal, textShadowPreset: preset } });
        });

        $('#prop-text-color').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const val = $(this).val();
            $('#prop-text-color-hex').val(val);
            self.state.updateElement(elem.id, { style: { color: val } });
        });

        $('#prop-text-color-hex').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const val = $(this).val();
            $('#prop-text-color').val(val);
            self.state.updateElement(elem.id, { style: { color: val } });
        });

        // Text Alignment buttons
        $('.btn-text-align').on('click', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const align = $(this).attr('data-align');
            $('.btn-text-align').removeClass('bg-slate-700 text-white').addClass('text-slate-400');
            $(this).addClass('bg-slate-700 text-white').removeClass('text-slate-400');
            self.state.updateElement(elem.id, { style: { textAlign: align } });
        });

        // Background Color
        $('#prop-bg-color').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const val = $(this).val();
            $('#prop-bg-color-hex').val(val);
            self.state.updateElement(elem.id, { style: { backgroundColor: val } });
        });

        $('#prop-bg-color-hex').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const val = $(this).val();
            $('#prop-bg-color').val(val);
            self.state.updateElement(elem.id, { style: { backgroundColor: val } });
        });

        // Product Properties
        $('#prop-product-theme').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem || elem.type !== 'product') return;
            self.state.updateElement(elem.id, { style: { theme: $(this).val() } });
        });

        $('#prop-product-name').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem || elem.type !== 'product') return;
            const name = $(this).val();
            const productData = Object.assign({}, elem.productData || {}, { name: name });
            self.state.updateElement(elem.id, { content: name, productData: productData });
        });

        $('#prop-product-price').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem || elem.type !== 'product') return;
            const price = $(this).val();
            const productData = Object.assign({}, elem.productData || {}, { price: price });
            self.state.updateElement(elem.id, { productData: productData });
        });

        $('#prop-product-badge').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem || elem.type !== 'product') return;
            const badge = $(this).val();
            const productData = Object.assign({}, elem.productData || {}, { badge: badge });
            self.state.updateElement(elem.id, { productData: productData });
        });

        // Border Radius & Opacity
        $('#prop-border-radius').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const radius = parseInt($(this).val(), 10) || 0;
            self.state.updateElement(elem.id, { style: { borderRadius: radius } });
        });

        $('#prop-opacity').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const opacity = parseInt($(this).val(), 10) || 100;
            self.state.updateElement(elem.id, { style: { opacity: opacity } });
        });

        // Responsive Visibility Checkboxes
        $('#prop-hide-mobile').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { hideOnMobile: $(this).is(':checked') });
        });

        $('#prop-hide-desktop').on('change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { hideOnDesktop: $(this).is(':checked') });
        });

        // Coordinates & Dimensions
        $('#prop-pos-x').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { x: parseFloat($(this).val()) || 0 });
        });

        $('#prop-pos-y').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { y: parseFloat($(this).val()) || 0 });
        });

        $('#prop-pos-w').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { width: parseFloat($(this).val()) || 10 });
        });

        $('#prop-pos-h').on('input change', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { height: parseFloat($(this).val()) || 5 });
        });

        // Alignment Quick Buttons
        $('#btn-align-left').on('click', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { x: 5 });
        });

        $('#btn-align-center-h').on('click', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const centerX = Math.max(0, (100 - elem.width) / 2);
            self.state.updateElement(elem.id, { x: Math.round(centerX) });
        });

        $('#btn-align-right').on('click', function () {
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const rightX = Math.max(0, 95 - elem.width);
            self.state.updateElement(elem.id, { x: Math.round(rightX) });
        });
    }

    /**
     * Synchronize Inspector panels based on active selection
     */
    syncInspector(elem = null) {
        const selected = elem || this.state.getSelectedElement();

        if (!selected) {
            // Show Canvas settings
            $('#inspector-canvas-props').removeClass('hidden');
            $('#inspector-element-props').addClass('hidden');
            $('#inspector-title').text('Canvas Properties');

            const canvas = this.state.getCanvas();
            $('#prop-canvas-bgcolor').val(canvas.backgroundColor || '#f8fafc');
            $('#prop-canvas-bgcolor-hex').val(canvas.backgroundColor || '#f8fafc');
            $('#prop-canvas-overlaycolor').val(canvas.overlayColor || '#000000');
            $('#prop-canvas-overlaycolor-hex').val(canvas.overlayColor || '#000000');
            $('#prop-canvas-opacity').val(canvas.overlayOpacity || 0);
            $('#prop-canvas-opacity-val').text(`${canvas.overlayOpacity || 0}%`);
        } else {
            // Show Element settings
            $('#inspector-canvas-props').addClass('hidden');
            $('#inspector-element-props').removeClass('hidden');
            $('#inspector-title').text(`${selected.type.toUpperCase()} Properties`);

            this.populateElementInspectorValues(selected);
        }
    }

    /**
     * Populate Inspector fields with element properties
     */
    populateElementInspectorValues(elem) {
        const style = elem.style || {};

        $('#prop-element-content').val(elem.content || '');
        $('#prop-element-url').val(elem.url || '');

        $('#prop-font-family').val(style.fontFamily || 'Instrument Sans');
        $('#prop-font-size').val(style.fontSize || 32);
        $('#prop-font-weight').val(style.fontWeight || 600);
        $('#prop-line-height').val(style.lineHeight || 1.15);
        $('#prop-letter-spacing').val(style.letterSpacing || 0);
        $('#prop-text-shadow').val(style.textShadowPreset || 'none');

        $('#prop-text-color').val(style.color || '#ffffff');
        $('#prop-text-color-hex').val(style.color || '#ffffff');

        // Alignment
        $('.btn-text-align').removeClass('bg-slate-700 text-white').addClass('text-slate-400');
        $(`.btn-text-align[data-align="${style.textAlign || 'left'}"]`).addClass('bg-slate-700 text-white').removeClass('text-slate-400');

        $('#prop-bg-color').val(style.backgroundColor || '#16a34a');
        $('#prop-bg-color-hex').val(style.backgroundColor || '#16a34a');
        $('#prop-border-radius').val(style.borderRadius || 0);
        $('#prop-opacity').val(style.opacity !== undefined ? style.opacity : 100);

        $('#prop-pos-x').val(elem.x);
        $('#prop-pos-y').val(elem.y);
        $('#prop-pos-w').val(elem.width);
        $('#prop-pos-h').val(elem.height);

        $('#prop-hide-mobile').prop('checked', Boolean(elem.hideOnMobile));
        $('#prop-hide-desktop').prop('checked', Boolean(elem.hideOnDesktop));

        // Contextually show/hide field groups
        if (elem.type === 'product') {
            const prod = elem.productData || {};
            $('#field-product-group').removeClass('hidden');
            $('#field-content-group').addClass('hidden');
            $('#field-typography-group').addClass('hidden');
            $('#field-appearance-group').removeClass('hidden');
            $('#field-url-group').removeClass('hidden');

            $('#prop-product-theme').val(style.theme || 'dark-glass');
            $('#prop-product-name').val(prod.name || elem.content || '');
            $('#prop-product-price').val(prod.price || '₹149.00');
            $('#prop-product-badge').val(prod.badge || 'FEATURED');
        } else if (elem.type === 'shape') {
            $('#field-product-group').addClass('hidden');
            $('#field-content-group').addClass('hidden');
            $('#field-url-group').addClass('hidden');
            $('#field-typography-group').addClass('hidden');
            $('#field-appearance-group').removeClass('hidden');
        } else if (elem.type === 'image') {
            $('#field-product-group').addClass('hidden');
            $('#field-content-group').addClass('hidden');
            $('#field-typography-group').addClass('hidden');
            $('#field-appearance-group').removeClass('hidden');
        } else {
            $('#field-product-group').addClass('hidden');
            $('#field-content-group').removeClass('hidden');
            $('#field-typography-group').removeClass('hidden');
            $('#field-appearance-group').removeClass('hidden');
        }
    }

    /**
     * Render the Layer Tree list in the left drawer
     */
    renderLayerTree() {
        const $list = $('#layers-tree-list');
        if (!$list.length) return;

        const elements = this.state.getElements();
        const selectedId = this.state.state.selectedElementId;

        $list.empty();

        if (elements.length === 0) {
            $list.html('<p class="text-[11px] text-slate-500 text-center py-4">No element layers on canvas.</p>');
            return;
        }

        // Render in reverse order (topmost layer on top of the list)
        const reversed = [...elements].reverse();

        reversed.forEach(elem => {
            const isSelected = elem.id === selectedId;
            const typeIcon = this.getElementTypeIcon(elem.type);

            const $item = $(`
                <div
                    class="layer-tree-item p-2 rounded-xl border flex items-center justify-between transition-all cursor-pointer ${
                        isSelected
                            ? 'bg-slate-800 border-emerald-500/60 shadow-xs'
                            : 'bg-slate-800/40 hover:bg-slate-800/70 border-slate-700/50'
                    }"
                    data-element-id="${elem.id}"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-slate-400">${typeIcon}</span>
                        <span class="text-[11px] font-semibold text-slate-200 truncate max-w-[130px]">
                            ${elem.content || elem.id}
                        </span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <button
                            type="button"
                            class="btn-layer-toggle-lock p-1 rounded hover:bg-slate-700 text-slate-400 hover:text-amber-400"
                            data-element-id="${elem.id}"
                            title="${elem.locked ? 'Unlock Layer' : 'Lock Layer'}"
                        >
                            <i data-lucide="${elem.locked ? 'lock' : 'unlock'}" class="w-3 h-3"></i>
                        </button>

                        <button
                            type="button"
                            class="btn-layer-toggle-vis p-1 rounded hover:bg-slate-700 text-slate-400 hover:text-sky-400"
                            data-element-id="${elem.id}"
                            title="${elem.visible ? 'Hide Layer' : 'Show Layer'}"
                        >
                            <i data-lucide="${elem.visible ? 'eye' : 'eye-off'}" class="w-3 h-3"></i>
                        </button>

                        <button
                            type="button"
                            class="btn-layer-delete p-1 rounded hover:bg-slate-700 text-slate-400 hover:text-rose-400"
                            data-element-id="${elem.id}"
                            title="Delete Layer"
                        >
                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                        </button>
                    </div>
                </div>
            `);

            $list.append($item);
        });

        if (window.renderLucideIcons) {
            window.renderLucideIcons();
        }
    }

    getElementTypeIcon(type) {
        switch (type) {
            case 'text': return '<i data-lucide="type" class="w-3.5 h-3.5"></i>';
            case 'button': return '<i data-lucide="mouse-pointer-click" class="w-3.5 h-3.5 text-emerald-400"></i>';
            case 'badge': return '<i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>';
            case 'product': return '<i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-sky-400"></i>';
            case 'image': return '<i data-lucide="image" class="w-3.5 h-3.5 text-purple-400"></i>';
            case 'shape': return '<i data-lucide="shapes" class="w-3.5 h-3.5 text-indigo-400"></i>';
            default: return '<i data-lucide="square" class="w-3.5 h-3.5"></i>';
        }
    }

    highlightActiveLayer(elementId) {
        $('.layer-tree-item').removeClass('bg-slate-800 border-emerald-500/60 shadow-xs').addClass('bg-slate-800/40 border-slate-700/50');
        if (elementId) {
            $(`.layer-tree-item[data-element-id="${elementId}"]`)
                .addClass('bg-slate-800 border-emerald-500/60 shadow-xs')
                .removeClass('bg-slate-800/40 border-slate-700/50');
        }
    }

    /**
     * Bind Layer Tree action buttons
     */
    bindLayerTreeEvents() {
        const self = this;

        // Select layer from tree
        $('#layers-tree-list').on('click', '.layer-tree-item', function (e) {
            if ($(e.target).closest('button').length > 0) return;
            const elemId = $(this).attr('data-element-id');
            if (elemId) {
                self.state.selectElement(elemId);
            }
        });

        // Toggle layer lock
        $('#layers-tree-list').on('click', '.btn-layer-toggle-lock', function (e) {
            e.stopPropagation();
            const elemId = $(this).attr('data-element-id');
            const elem = self.state.getElement(elemId);
            if (elem) {
                self.state.updateElement(elemId, { locked: !elem.locked });
            }
        });

        // Toggle layer visibility
        $('#layers-tree-list').on('click', '.btn-layer-toggle-vis', function (e) {
            e.stopPropagation();
            const elemId = $(this).attr('data-element-id');
            const elem = self.state.getElement(elemId);
            if (elem) {
                self.state.updateElement(elemId, { visible: !elem.visible });
            }
        });

        // Delete layer
        $('#layers-tree-list').on('click', '.btn-layer-delete', function (e) {
            e.stopPropagation();
            const elemId = $(this).attr('data-element-id');
            self.state.removeElement(elemId);
        });

        // Floating toolbar actions
        $('#float-btn-bring-front').on('click', () => {
            if (self.state.state.selectedElementId) self.state.bringToFront(self.state.state.selectedElementId);
        });

        $('#float-btn-send-back').on('click', () => {
            if (self.state.state.selectedElementId) self.state.sendToBack(self.state.state.selectedElementId);
        });

        $('#float-btn-duplicate').on('click', () => {
            if (self.state.state.selectedElementId) self.state.duplicateElement(self.state.state.selectedElementId);
        });

        $('#float-btn-lock').on('click', () => {
            const elem = self.state.getSelectedElement();
            if (elem) self.state.updateElement(elem.id, { locked: !elem.locked });
        });

        $('#float-btn-delete').on('click', () => {
            if (self.state.state.selectedElementId) self.state.removeElement(self.state.state.selectedElementId);
        });
    }

    /**
     * Bind drawer tool tabs switching (Templates, Badges, Text, Media, Products, Shapes, Layers)
     */
    bindDrawerNavigation() {
        const tabTitles = {
            templates: 'Templates & Presets',
            elements: 'Promotional Badges & Stickers',
            text: 'Headlines & Typography',
            media: 'Backgrounds & Graphics',
            products: 'Store Catalog Products',
            shapes: 'Shapes & Containers',
            layers: 'Element Layer Hierarchy',
        };

        $('.builder-tool-tab').on('click', function () {
            const panel = $(this).attr('data-panel');

            $('.builder-tool-tab')
                .removeClass('active-tab bg-slate-800 text-emerald-400 border border-emerald-500/30')
                .addClass('text-slate-400');
            $(this)
                .addClass('active-tab bg-slate-800 text-emerald-400 border border-emerald-500/30')
                .removeClass('text-slate-400');

            $('#builder-drawer-panel').removeClass('hidden w-0').addClass('w-80');
            $('#drawer-panel-title').text(tabTitles[panel] || 'Toolbox');

            $('.drawer-tab-content').addClass('hidden');
            $(`#drawer-tab-${panel}`).removeClass('hidden');
        });

        $('#btn-collapse-drawer').on('click', function () {
            $('#builder-drawer-panel').toggleClass('hidden');
        });
    }
}

// Instantiate and expose globally
const BannerBuilderSelection = new BannerBuilderSelectionEngine();
window.BannerBuilderSelection = BannerBuilderSelection;

export default BannerBuilderSelection;
