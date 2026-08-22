/**
 * Grocery Banner Builder - Shapes, Badges, Ribbons & SVG Asset Library
 * Predefined grocery stickers, verified shields, delivery tags, and glass containers.
 */

class BannerBuilderShapesLibrary {
    constructor() {
        this.badges = [
            {
                id: 'badge-50-off',
                title: '50% OFF Flash Sale',
                text: '🔥 FLAT 50% OFF',
                bg: '#ef4444',
                color: '#ffffff',
                icon: 'flame',
            },
            {
                id: 'badge-organic',
                title: '100% Certified Organic',
                text: '🌿 100% ORGANIC',
                bg: '#16a34a',
                color: '#ffffff',
                icon: 'leaf',
            },
            {
                id: 'badge-bogo',
                title: 'Buy 1 Get 1 Free',
                text: '🎁 BOGO FREE',
                bg: '#f59e0b',
                color: '#0f172a',
                icon: 'gift',
            },
            {
                id: 'badge-express',
                title: 'Express 2-Hour Delivery',
                text: '⚡ 2HR EXPRESS DELIVERY',
                bg: '#0284c7',
                color: '#ffffff',
                icon: 'zap',
            },
            {
                id: 'badge-shipping',
                title: 'Free Doorstep Shipping',
                text: '🚚 FREE DELIVERY OVER ₹499',
                bg: '#059669',
                color: '#ffffff',
                icon: 'truck',
            },
            {
                id: 'badge-bestprice',
                title: 'Best Price Guarantee',
                text: '🛡️ BEST PRICE GUARANTEE',
                bg: '#d97706',
                color: '#ffffff',
                icon: 'shield-check',
            },
            {
                id: 'badge-vegan',
                title: '100% Vegan / Plant Based',
                text: '🌱 100% VEGAN & PURE',
                bg: '#0d9488',
                color: '#ffffff',
                icon: 'sprout',
            },
            {
                id: 'badge-fresh-daily',
                title: 'Farm Fresh Daily Morning',
                text: '☀️ FRESH DAILY HARVEST',
                bg: '#e11d48',
                color: '#ffffff',
                icon: 'sun',
            },
        ];

        this.shapes = [
            {
                type: 'card',
                name: 'Frosted Glass Container Card',
                bg: 'rgba(15, 23, 42, 0.75)',
                border: 'rgba(255, 255, 255, 0.15)',
                radius: 20,
                width: 38,
                height: 35,
            },
            {
                type: 'pill',
                name: 'Accent Pill Ribbon',
                bg: '#16a34a',
                border: 'transparent',
                radius: 9999,
                width: 24,
                height: 7,
            },
            {
                type: 'circle',
                name: 'Stamped Circular Discount Tag',
                bg: '#ea580c',
                border: 'rgba(255, 255, 255, 0.3)',
                radius: 9999,
                width: 12,
                height: 12,
            },
            {
                type: 'divider',
                name: 'Horizontal Glowing Divider',
                bg: 'linear-gradient(90deg, #10b981, #06b6d4, transparent)',
                border: 'transparent',
                radius: 9999,
                width: 45,
                height: 1,
            },
        ];
    }

    init() {
        // Shapes library ready
    }
}

// Instantiate and expose globally
const BannerBuilderShapes = new BannerBuilderShapesLibrary();
window.BannerBuilderShapes = BannerBuilderShapes;

export default BannerBuilderShapes;
