/**
 * Grocery Banner Builder - Alignment, Distribution & Spacing Engine
 * Precise alignment tools for virtual elements relative to canvas and sibling layers.
 */

class BannerBuilderAlignerEngine {
    constructor() {
        this.state = window.BannerBuilderState;
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const self = this;

        $(document).on('click', '#btn-align-left', () => self.alignLeft());
        $(document).on('click', '#btn-align-center-h', () => self.alignCenterH());
        $(document).on('click', '#btn-align-right', () => self.alignRight());
        $(document).on('click', '#btn-align-top', () => self.alignTop());
        $(document).on('click', '#btn-align-center-v', () => self.alignCenterV());
        $(document).on('click', '#btn-align-bottom', () => self.alignBottom());
        $(document).on('click', '#btn-align-center-both', () => self.centerBoth());
    }

    getSelected() {
        if (!window.BannerBuilderState) return null;
        return window.BannerBuilderState.getSelectedElement();
    }

    pushHistory() {
        if (window.BannerBuilderHistory) {
            window.BannerBuilderHistory.pushSnapshot();
        }
    }

    alignLeft() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        window.BannerBuilderState.updateElement(elem.id, { x: 5 });
    }

    alignCenterH() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        const newX = parseFloat(((100 - elem.width) / 2).toFixed(2));
        window.BannerBuilderState.updateElement(elem.id, { x: newX });
    }

    alignRight() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        const newX = parseFloat((100 - elem.width - 5).toFixed(2));
        window.BannerBuilderState.updateElement(elem.id, { x: Math.max(0, newX) });
    }

    alignTop() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        window.BannerBuilderState.updateElement(elem.id, { y: 5 });
    }

    alignCenterV() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        const newY = parseFloat(((100 - elem.height) / 2).toFixed(2));
        window.BannerBuilderState.updateElement(elem.id, { y: newY });
    }

    alignBottom() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        const newY = parseFloat((100 - elem.height - 5).toFixed(2));
        window.BannerBuilderState.updateElement(elem.id, { y: Math.max(0, newY) });
    }

    centerBoth() {
        const elem = this.getSelected();
        if (!elem) return;
        this.pushHistory();
        const newX = parseFloat(((100 - elem.width) / 2).toFixed(2));
        const newY = parseFloat(((100 - elem.height) / 2).toFixed(2));
        window.BannerBuilderState.updateElement(elem.id, { x: newX, y: newY });
    }
}

// Instantiate and expose globally
const BannerBuilderAligner = new BannerBuilderAlignerEngine();
window.BannerBuilderAligner = BannerBuilderAligner;

export default BannerBuilderAligner;
