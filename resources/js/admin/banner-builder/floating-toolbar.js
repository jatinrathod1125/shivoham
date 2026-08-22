/**
 * Grocery Banner Builder - Floating Context Toolbar & Quick Actions Engine
 * Dynamic contextual docking, mirroring, z-index manipulation, and quick clone actions.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderFloatingToolbarEngine {
    constructor(stateEngine, rendererEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.renderer = rendererEngine || window.BannerBuilderRenderer;
        this.$toolbar = null;
    }

    /**
     * Initialize toolbar positioning and quick action button bindings
     */
    init() {
        this.$toolbar = $('#canvas-floating-toolbar');
        if (!this.$toolbar.length) return;

        this.bindQuickActions();
        this.bindStateObservers();
    }

    /**
     * Observe selection and coordinate updates to reposition toolbar dynamically
     */
    bindStateObservers() {
        const self = this;

        this.state.on('element:selected', (elem) => self.updateToolbar(elem));
        this.state.on('element:updated', (elem) => {
            if (self.state.state.selectedElementId === elem.id) {
                self.updateToolbar(elem);
            }
        });
        this.state.on('element:deselected', () => self.hideToolbar());
        this.state.on('zoom:changed', () => {
            const elem = self.state.getSelectedElement();
            if (elem) self.updateToolbar(elem);
        });
    }

    /**
     * Compute bounding rect and position toolbar above (or below) the element
     */
    updateToolbar(elem) {
        if (!elem) {
            this.hideToolbar();
            return;
        }

        const $node = $(`#canvas-node-${elem.id}`);
        const $stage = $('#builder-stage-wrapper');

        if (!$node.length || !$stage.length) {
            this.hideToolbar();
            return;
        }

        // Show toolbar
        this.$toolbar.removeClass('hidden');

        // Update Lock Icon state
        const $lockIcon = $('#float-btn-lock i');
        if ($lockIcon.length) {
            $lockIcon.attr('data-lucide', elem.locked ? 'lock' : 'unlock');
        }

        // Update Flip button active states
        const style = elem.style || {};
        $('#float-btn-flip-h').toggleClass('text-sky-400 bg-slate-800', !!style.flipH);
        $('#float-btn-flip-v').toggleClass('text-sky-400 bg-slate-800', !!style.flipV);

        if (window.renderLucideIcons) {
            window.renderLucideIcons();
        }

        // Calculate stage-relative position
        const stageOffset = $stage.offset();
        const nodeOffset = $node.offset();
        const nodeW = $node.outerWidth();
        const nodeH = $node.outerHeight();

        const toolbarW = this.$toolbar.outerWidth() || 260;
        const toolbarH = this.$toolbar.outerHeight() || 40;

        const elemCenterX = (nodeOffset.left - stageOffset.left) + (nodeW / 2);
        let toolbarLeft = elemCenterX - (toolbarW / 2);

        // Keep inside stage horizontally
        const stageW = $stage.width();
        toolbarLeft = Math.max(16, Math.min(toolbarLeft, stageW - toolbarW - 16));

        // Position above element, or below if near top
        let toolbarTop = (nodeOffset.top - stageOffset.top) - toolbarH - 12;
        if (toolbarTop < 16) {
            // Flip to bottom of element
            toolbarTop = (nodeOffset.top - stageOffset.top) + nodeH + 12;
        }

        this.$toolbar.css({
            left: `${toolbarLeft}px`,
            top: `${toolbarTop}px`,
            transform: 'none', // override initial translate
        });
    }

    hideToolbar() {
        if (this.$toolbar) {
            this.$toolbar.addClass('hidden');
        }
    }

    /**
     * Bind click events for all quick actions
     */
    bindQuickActions() {
        const self = this;

        // 1. Bring to Front
        $('#float-btn-bring-front').off('click').on('click', function (e) {
            e.stopPropagation();
            const id = self.state.state.selectedElementId;
            if (id) self.state.bringToFront(id);
        });

        // 2. Send to Back
        $('#float-btn-send-back').off('click').on('click', function (e) {
            e.stopPropagation();
            const id = self.state.state.selectedElementId;
            if (id) self.state.sendToBack(id);
        });

        // 3. Flip Horizontal
        $('#float-btn-flip-h').off('click').on('click', function (e) {
            e.stopPropagation();
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const currentFlip = !!(elem.style && elem.style.flipH);
            self.state.updateElement(elem.id, {
                style: { flipH: !currentFlip }
            });
        });

        // 4. Flip Vertical
        $('#float-btn-flip-v').off('click').on('click', function (e) {
            e.stopPropagation();
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            const currentFlip = !!(elem.style && elem.style.flipV);
            self.state.updateElement(elem.id, {
                style: { flipV: !currentFlip }
            });
        });

        // 5. Duplicate
        $('#float-btn-duplicate').off('click').on('click', function (e) {
            e.stopPropagation();
            const id = self.state.state.selectedElementId;
            if (id) self.state.duplicateElement(id);
        });

        // 6. Lock / Unlock
        $('#float-btn-lock').off('click').on('click', function (e) {
            e.stopPropagation();
            const elem = self.state.getSelectedElement();
            if (!elem) return;
            self.state.updateElement(elem.id, { locked: !elem.locked });
        });

        // 7. Delete
        $('#float-btn-delete').off('click').on('click', function (e) {
            e.stopPropagation();
            const id = self.state.state.selectedElementId;
            if (id) self.state.removeElement(id);
        });
    }
}

// Instantiate and expose globally
const BannerBuilderFloatingToolbar = new BannerBuilderFloatingToolbarEngine();
window.BannerBuilderFloatingToolbar = BannerBuilderFloatingToolbar;

export default BannerBuilderFloatingToolbar;
