/**
 * Grocery Banner Builder - Color Palettes & Gradient Presets Engine
 * Curated color schemes and multi-stop gradient presets for supermarket visual banners.
 */

class BannerBuilderPalettesEngine {
    constructor() {
        this.palettes = {
            organic: {
                name: 'Organic & Fresh Greens',
                colors: ['#10b981', '#059669', '#064e3b', '#16a34a', '#047857', '#dcfce7'],
            },
            bakery: {
                name: 'Dairy & Bakery Warmth',
                colors: ['#f59e0b', '#fef3c7', '#d97706', '#b45309', '#78350f', '#fde68a'],
            },
            flash: {
                name: 'Supermarket Flash Sale',
                colors: ['#ef4444', '#dc2626', '#991b1b', '#881337', '#eab308', '#fee2e2'],
            },
            fruits: {
                name: 'Exotic Fruits & Summer',
                colors: ['#f43f5e', '#f97316', '#84cc16', '#a855f7', '#06b6d4', '#ffedd5'],
            },
            meat: {
                name: 'Prime Cut Butcher & Seafood',
                colors: ['#0f172a', '#0e7490', '#1e293b', '#e11d48', '#0284c7', '#334155'],
            },
        };

        this.gradients = [
            { name: 'Emerald Harvest', val: 'linear-gradient(135deg, #064e3b 0%, #047857 100%)' },
            { name: 'Sunset Warmth', val: 'linear-gradient(135deg, #78350f 0%, #d97706 100%)' },
            { name: 'Midnight Slate', val: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)' },
            { name: 'Crimson Surge', val: 'linear-gradient(135deg, #881337 0%, #dc2626 100%)' },
            { name: 'Ocean Seafood', val: 'linear-gradient(135deg, #0c4a6e 0%, #0284c7 100%)' },
            { name: 'Violet Artisan', val: 'linear-gradient(135deg, #581c87 0%, #9333ea 100%)' },
        ];
    }

    init() {
        this.bindPaletteSwatches();
    }

    bindPaletteSwatches() {
        const self = this;

        // Swatch clicks on Canvas Background Swatches
        $(document).on('click', '.canvas-color-swatch', function () {
            const color = $(this).attr('data-color');
            $('#prop-canvas-bgcolor').val(color);
            $('#prop-canvas-bgcolor-hex').val(color);
            if (window.BannerBuilderState) {
                window.BannerBuilderState.updateCanvas({ backgroundColor: color });
            }
        });

        // Swatch clicks on Element Fill Swatches
        $(document).on('click', '.element-color-swatch', function () {
            const color = $(this).attr('data-color');
            $('#prop-bg-color').val(color);
            $('#prop-bg-color-hex').val(color);
            if (window.BannerBuilderState) {
                const elem = window.BannerBuilderState.getSelectedElement();
                if (elem) {
                    window.BannerBuilderState.updateElement(elem.id, { style: { backgroundColor: color } });
                }
            }
        });

        // Swatch clicks on Text Color Swatches
        $(document).on('click', '.text-color-swatch', function () {
            const color = $(this).attr('data-color');
            $('#prop-text-color').val(color);
            $('#prop-text-color-hex').val(color);
            if (window.BannerBuilderState) {
                const elem = window.BannerBuilderState.getSelectedElement();
                if (elem) {
                    window.BannerBuilderState.updateElement(elem.id, { style: { color: color } });
                }
            }
        });
    }
}

// Instantiate and expose globally
const BannerBuilderPalettes = new BannerBuilderPalettesEngine();
window.BannerBuilderPalettes = BannerBuilderPalettes;

export default BannerBuilderPalettes;
