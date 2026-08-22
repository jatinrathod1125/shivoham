/**
 * Grocery Banner Builder - Drag, Drop, Resize & Rotation Transformer Engine
 * Pure jQuery & standard pointer events for smooth 1920x700 virtual canvas transformations.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderTransformerEngine {
    constructor(stateEngine, rendererEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.renderer = rendererEngine || window.BannerBuilderRenderer;

        this.isInteracting = false;
        this.mode = 'none'; // 'drag', 'resize', 'rotate'
        this.activeHandle = null;
        this.activeElementId = null;

        // Pointer start coordinates (screen pixels)
        this.startPointerX = 0;
        this.startPointerY = 0;

        // Element starting geometry (percentage & degrees)
        this.startElemX = 0;
        this.startElemY = 0;
        this.startElemW = 0;
        this.startElemH = 0;
        this.startRotation = 0;

        // Cached element center for rotation
        this.elemCenterScreenX = 0;
        this.elemCenterScreenY = 0;

        // Snapping indicators
        this.$snapGuideX = null;
        this.$snapGuideY = null;
    }

    /**
     * Initialize pointer event listeners on canvas elements and resize handles
     */
    init() {
        this.$snapGuideX = $('#snap-guide-x');
        this.$snapGuideY = $('#snap-guide-y');

        this.bindEvents();
    }

    /**
     * Bind mousedown / pointerdown handlers
     */
    bindEvents() {
        const self = this;

        // 1. Initiate Element Dragging
        $('#canvas-elements-container').on('pointerdown mousedown', '.builder-element', function (e) {
            // Ignore if clicking on a resize handle or right click
            if ($(e.target).closest('.handle-point').length > 0 || e.which === 3) {
                return;
            }

            const elemId = $(this).attr('data-element-id');
            const elem = self.state.getElement(elemId);
            if (!elem || elem.locked) return;

            e.preventDefault();
            e.stopPropagation();

            self.startInteraction('drag', elem, e);
        });

        // 2. Initiate Handle Resize / Rotation
        $('#canvas-elements-container').on('pointerdown mousedown', '.handle-point', function (e) {
            if (e.which === 3) return;

            const handle = $(this).attr('data-handle');
            const elemId = $(this).closest('.builder-element').attr('data-element-id');
            const elem = self.state.getElement(elemId);
            if (!elem || elem.locked) return;

            e.preventDefault();
            e.stopPropagation();

            if (handle === 'rot') {
                self.startInteraction('rotate', elem, e, handle);
            } else {
                self.startInteraction('resize', elem, e, handle);
            }
        });
    }

    /**
     * Start a drag, resize, or rotate transformation
     */
    startInteraction(mode, elem, e, handle = null) {
        this.isInteracting = true;
        this.mode = mode;
        this.activeHandle = handle;
        this.activeElementId = elem.id;

        this.startPointerX = e.clientX;
        this.startPointerY = e.clientY;

        this.startElemX = elem.x;
        this.startElemY = elem.y;
        this.startElemW = elem.width;
        this.startElemH = elem.height;
        this.startRotation = elem.rotation || 0;

        // Calculate element screen center for rotation
        const $elemNode = $(`#canvas-node-${elem.id}`);
        if ($elemNode.length) {
            const offset = $elemNode.offset();
            this.elemCenterScreenX = offset.left + $elemNode.outerWidth() / 2;
            this.elemCenterScreenY = offset.top + $elemNode.outerHeight() / 2;
        }

        // Attach global document move and up listeners
        const self = this;
        $(document)
            .on('pointermove.bannerTransform mousemove.bannerTransform', (ev) => self.onPointerMove(ev))
            .on('pointerup.bannerTransform mouseup.bannerTransform pointercancel.bannerTransform', () => self.onPointerUp());
    }

    /**
     * Process pointer move delta based on active transformation mode
     */
    onPointerMove(e) {
        if (!this.isInteracting || !this.activeElementId) return;

        const scale = this.renderer.scale || 1.0;
        const $canvas = $('#banner-canvas');
        const canvasW = $canvas.outerWidth() || 1920;
        const canvasH = $canvas.outerHeight() || 700;

        // Delta in virtual pixels
        const deltaScreenX = e.clientX - this.startPointerX;
        const deltaScreenY = e.clientY - this.startPointerY;

        const deltaVirtX = deltaScreenX / scale;
        const deltaVirtY = deltaScreenY / scale;

        // Delta in virtual percentage
        const deltaPctX = (deltaVirtX / canvasW) * 100;
        const deltaPctY = (deltaVirtY / canvasH) * 100;

        if (this.mode === 'drag') {
            this.handleDrag(deltaPctX, deltaPctY, e);
        } else if (this.mode === 'resize') {
            this.handleResize(deltaPctX, deltaPctY, e);
        } else if (this.mode === 'rotate') {
            this.handleRotate(e);
        }
    }

    /**
     * Handle Drag Movement & Smart Snapping
     */
    handleDrag(deltaPctX, deltaPctY, e) {
        let newX = this.startElemX + deltaPctX;
        let newY = this.startElemY + deltaPctY;

        const elemW = this.startElemW;
        const elemH = this.startElemH;

        // Smart Snapping to Canvas Centers (50%)
        let snappedX = false;
        let snappedY = false;

        const centerOffsetX = newX + elemW / 2;
        const centerOffsetY = newY + elemH / 2;

        const snapTolerance = 1.0; // 1% snap threshold

        // Horizontal Center Snap
        if (Math.abs(centerOffsetX - 50) <= snapTolerance) {
            newX = 50 - elemW / 2;
            snappedX = true;
            if (this.$snapGuideX) {
                this.$snapGuideX.css({ left: '50%' }).show();
            }
        } else {
            if (this.$snapGuideX) this.$snapGuideX.hide();
        }

        // Vertical Center Snap
        if (Math.abs(centerOffsetY - 50) <= snapTolerance) {
            newY = 50 - elemH / 2;
            snappedY = true;
            if (this.$snapGuideY) {
                this.$snapGuideY.css({ top: '50%' }).show();
            }
        } else {
            if (this.$snapGuideY) this.$snapGuideY.hide();
        }

        // Boundary Clamping (keep inside canvas)
        newX = Math.max(-5, Math.min(newX, 105 - elemW));
        newY = Math.max(-5, Math.min(newY, 105 - elemH));

        newX = Math.round(newX * 10) / 10;
        newY = Math.round(newY * 10) / 10;

        this.state.updateElement(this.activeElementId, { x: newX, y: newY }, false);
    }

    /**
     * Handle 8-Direction Handle Resizing
     */
    handleResize(deltaPctX, deltaPctY, e) {
        const handle = this.activeHandle;
        let newX = this.startElemX;
        let newY = this.startElemY;
        let newW = this.startElemW;
        let newH = this.startElemH;

        const minW = 4; // minimum 4% width
        const minH = 2; // minimum 2% height

        // Calculate resize adjustments by handle direction
        if (handle.includes('e')) {
            newW = Math.max(minW, this.startElemW + deltaPctX);
        }
        if (handle.includes('s')) {
            newH = Math.max(minH, this.startElemH + deltaPctY);
        }
        if (handle.includes('w')) {
            const proposedW = this.startElemW - deltaPctX;
            if (proposedW >= minW) {
                newW = proposedW;
                newX = this.startElemX + deltaPctX;
            }
        }
        if (handle.includes('n')) {
            const proposedH = this.startElemH - deltaPctY;
            if (proposedH >= minH) {
                newH = proposedH;
                newY = this.startElemY + deltaPctY;
            }
        }

        // Proportional constraint when Shift key is pressed
        if (e.shiftKey && (handle === 'nw' || handle === 'ne' || handle === 'se' || handle === 'sw')) {
            const initialRatio = this.startElemW / (this.startElemH || 1);
            newH = newW / initialRatio;
        }

        newX = Math.round(newX * 10) / 10;
        newY = Math.round(newY * 10) / 10;
        newW = Math.round(newW * 10) / 10;
        newH = Math.round(newH * 10) / 10;

        this.state.updateElement(this.activeElementId, {
            x: newX,
            y: newY,
            width: newW,
            height: newH,
        }, false);
    }

    /**
     * Handle Free Angle Rotation with 45° Shift Snap
     */
    handleRotate(e) {
        const radians = Math.atan2(e.clientY - this.elemCenterScreenY, e.clientX - this.elemCenterScreenX);
        let degrees = Math.round(radians * (180 / Math.PI)) + 90;

        if (degrees < 0) {
            degrees += 360;
        }
        degrees = degrees % 360;

        // Snap to 45° angles when holding Shift
        if (e.shiftKey) {
            degrees = Math.round(degrees / 45) * 45;
            degrees = degrees % 360;
        }

        this.state.updateElement(this.activeElementId, { rotation: degrees }, false);
    }

    /**
     * Complete transformation, hide guides, and commit to history
     */
    onPointerUp() {
        if (!this.isInteracting) return;

        // Hide snapping guide lines
        if (this.$snapGuideX) this.$snapGuideX.hide();
        if (this.$snapGuideY) this.$snapGuideY.hide();

        // Push completed action to history stack
        if (this.activeElementId) {
            this.state.pushHistory(`Transform element ${this.activeElementId}`);
        }

        this.isInteracting = false;
        this.mode = 'none';
        this.activeHandle = null;
        this.activeElementId = null;

        // Cleanup global event listeners to prevent memory leaks
        $(document).off('.bannerTransform');
    }
}

// Instantiate and expose globally
const BannerBuilderTransformer = new BannerBuilderTransformerEngine();
window.BannerBuilderTransformer = BannerBuilderTransformer;

export default BannerBuilderTransformer;
