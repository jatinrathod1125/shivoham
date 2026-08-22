/**
 * Grocery Banner Builder - Typography & Google Web Fonts Dynamic Loader
 * Dynamically loads and caches curated Google Fonts for modern supermarket branding.
 */

class BannerBuilderFontsEngine {
    constructor() {
        this.loadedFonts = new Set();

        this.fontMap = {
            'Instrument Sans': 'Instrument+Sans:ital,wght@0,400..800;1,400..800',
            'Plus Jakarta Sans': 'Plus+Jakarta+Sans:ital,wght@0,400..800;1,400..800',
            'Outfit': 'Outfit:wght@400..900',
            'Bebas Neue': 'Bebas+Neue',
            'Playfair Display': 'Playfair+Display:ital,wght@0,400..900;1,400..900',
            'Montserrat': 'Montserrat:ital,wght@0,400..900;1,400..900',
            'Inter': 'Inter:wght@400..900',
            'Poppins': 'Poppins:ital,wght@0,400..900;1,400..900',
        };

        this.textShadowPresets = {
            'none': 'none',
            'soft': '0 2px 8px rgba(0, 0, 0, 0.4)',
            'strong': '0 4px 16px rgba(0, 0, 0, 0.8)',
            'outline': '-1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000',
            'glow-amber': '0 0 16px rgba(245, 158, 11, 0.8)',
            'glow-emerald': '0 0 16px rgba(16, 185, 129, 0.8)',
            'glow-sky': '0 0 16px rgba(2, 132, 199, 0.8)',
        };
    }

    /**
     * Preload all curated fonts on studio startup so previews are instantaneous
     */
    init() {
        Object.keys(this.fontMap).forEach(font => this.loadFont(font));
    }

    /**
     * Dynamically inject Google Web Font stylesheet into document head
     */
    loadFont(fontFamily) {
        if (!fontFamily || this.loadedFonts.has(fontFamily)) return;

        const spec = this.fontMap[fontFamily];
        if (!spec) return;

        const linkId = `google-font-${fontFamily.toLowerCase().replace(/\s+/g, '-')}`;
        if (document.getElementById(linkId)) {
            this.loadedFonts.add(fontFamily);
            return;
        }

        const link = document.createElement('link');
        link.id = linkId;
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${spec}&display=swap`;
        document.head.appendChild(link);

        this.loadedFonts.add(fontFamily);
    }
}

// Instantiate and expose globally
const BannerBuilderFonts = new BannerBuilderFontsEngine();
window.BannerBuilderFonts = BannerBuilderFonts;

export default BannerBuilderFonts;
