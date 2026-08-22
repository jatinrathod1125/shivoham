/**
 * Grocery Banner Builder - Templates & Presets Engine
 * Pre-designed high-converting supermarket theme presets with one-click application.
 */

import jQuery from 'jquery';
import Swal from 'sweetalert2';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderTemplatesEngine {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;

        this.templates = {
            organic_fresh: {
                name: 'Organic Fresh Harvest',
                title: '100% Certified Organic Farm Harvest Daily',
                subtitle: 'Directly from certified local organic farms straight to your doorstep in 2 hours.',
                link: '/categories/fruits-vegetables',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#064e3b',
                    backgroundImage: '/images/banners/hero-grocery-1.jpg',
                    overlayColor: '#022c22',
                    overlayOpacity: 35,
                },
                elements: [
                    {
                        id: 'elem-badge-organic',
                        type: 'badge',
                        content: '🌿 100% CERTIFIED ORGANIC',
                        x: 8,
                        y: 15,
                        width: 24,
                        height: 7,
                        rotation: 0,
                        zIndex: 10,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 14,
                            fontWeight: 800,
                            backgroundColor: '#16a34a',
                            color: '#ffffff',
                            borderRadius: 9999,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-headline-organic',
                        type: 'text',
                        content: 'Fresh Farm Harvest Daily',
                        x: 8,
                        y: 25,
                        width: 55,
                        height: 22,
                        rotation: 0,
                        zIndex: 11,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 56,
                            fontWeight: 800,
                            color: '#ffffff',
                            textAlign: 'left',
                            lineHeight: 1.15,
                            letterSpacing: -0.5,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-subtitle-organic',
                        type: 'text',
                        content: 'Handpicked crisp greens, juicy fruits and farm-fresh veggies at peak nutrition.',
                        x: 8,
                        y: 49,
                        width: 48,
                        height: 12,
                        rotation: 0,
                        zIndex: 12,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 20,
                            fontWeight: 500,
                            color: '#a7f3d0',
                            textAlign: 'left',
                            lineHeight: 1.4,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-cta-organic',
                        type: 'button',
                        content: 'Shop Fresh Produce →',
                        url: '/categories/fruits-vegetables',
                        x: 8,
                        y: 64,
                        width: 20,
                        height: 10,
                        rotation: 0,
                        zIndex: 13,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 16,
                            fontWeight: 700,
                            backgroundColor: '#16a34a',
                            color: '#ffffff',
                            borderRadius: 12,
                            paddingX: 24,
                            paddingY: 12,
                            opacity: 100,
                        },
                    },
                ],
            },

            dairy_delight: {
                name: 'Morning Dairy & Bakery',
                title: 'Pure Farm Milk & Fresh Oven Bakery',
                subtitle: 'Cold-chain milk, artisan cheeses, butter & sourdough delivered before 7:00 AM.',
                link: '/categories/dairy-bakery',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#0c4a6e',
                    backgroundImage: '/images/banners/hero-grocery-2.jpg',
                    overlayColor: '#082f49',
                    overlayOpacity: 30,
                },
                elements: [
                    {
                        id: 'elem-badge-dairy',
                        type: 'badge',
                        content: '⚡ MORNING 7:00 AM DELIVERY',
                        x: 8,
                        y: 15,
                        width: 26,
                        height: 7,
                        rotation: 0,
                        zIndex: 10,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 14,
                            fontWeight: 800,
                            backgroundColor: '#0284c7',
                            color: '#ffffff',
                            borderRadius: 9999,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-headline-dairy',
                        type: 'text',
                        content: 'Pure Farm Dairy & Artisan Cheese',
                        x: 8,
                        y: 25,
                        width: 56,
                        height: 22,
                        rotation: 0,
                        zIndex: 11,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Plus Jakarta Sans',
                            fontSize: 54,
                            fontWeight: 800,
                            color: '#ffffff',
                            textAlign: 'left',
                            lineHeight: 1.15,
                            letterSpacing: -0.5,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-subtitle-dairy',
                        type: 'text',
                        content: 'Pasteurized farm milk, butter, and fresh paneer delivered daily to your doorstep.',
                        x: 8,
                        y: 49,
                        width: 50,
                        height: 12,
                        rotation: 0,
                        zIndex: 12,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 20,
                            fontWeight: 500,
                            color: '#bae6fd',
                            textAlign: 'left',
                            lineHeight: 1.4,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-cta-dairy',
                        type: 'button',
                        content: 'Order Breakfast Essentials',
                        url: '/categories/dairy-bakery',
                        x: 8,
                        y: 64,
                        width: 22,
                        height: 10,
                        rotation: 0,
                        zIndex: 13,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 16,
                            fontWeight: 700,
                            backgroundColor: '#0284c7',
                            color: '#ffffff',
                            borderRadius: 12,
                            paddingX: 24,
                            paddingY: 12,
                            opacity: 100,
                        },
                    },
                ],
            },

            flash_deals: {
                name: 'Weekend Supermarket Flash Sale',
                title: 'Mega Supermarket Deals Week: Flat 50% Off',
                subtitle: 'Massive grocery savings across pantry staples, snacks, beverages and household essentials.',
                link: '/offers',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#450a0a',
                    backgroundImage: '/images/banners/hero-grocery-1.jpg',
                    overlayColor: '#18181b',
                    overlayOpacity: 45,
                },
                elements: [
                    {
                        id: 'elem-badge-flash',
                        type: 'badge',
                        content: '🔥 FLAT 50% OFF WEEKEND FLASH',
                        x: 8,
                        y: 15,
                        width: 28,
                        height: 7,
                        rotation: 0,
                        zIndex: 10,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 14,
                            fontWeight: 900,
                            backgroundColor: '#dc2626',
                            color: '#ffffff',
                            borderRadius: 9999,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-headline-flash',
                        type: 'text',
                        content: 'Mega Supermarket Deals Week',
                        x: 8,
                        y: 25,
                        width: 58,
                        height: 22,
                        rotation: 0,
                        zIndex: 11,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Bebas Neue',
                            fontSize: 62,
                            fontWeight: 800,
                            color: '#fde047',
                            textAlign: 'left',
                            lineHeight: 1.1,
                            letterSpacing: 1,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-subtitle-flash',
                        type: 'text',
                        content: 'Massive discounts on top brands, pantry staples, snacks & household cleaning essentials.',
                        x: 8,
                        y: 49,
                        width: 52,
                        height: 12,
                        rotation: 0,
                        zIndex: 12,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 20,
                            fontWeight: 500,
                            color: '#fed7aa',
                            textAlign: 'left',
                            lineHeight: 1.4,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-cta-flash',
                        type: 'button',
                        content: 'Claim Supermarket Deals →',
                        url: '/offers',
                        x: 8,
                        y: 64,
                        width: 22,
                        height: 10,
                        rotation: 0,
                        zIndex: 13,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 16,
                            fontWeight: 800,
                            backgroundColor: '#ea580c',
                            color: '#ffffff',
                            borderRadius: 12,
                            paddingX: 24,
                            paddingY: 12,
                            opacity: 100,
                        },
                    },
                ],
            },

            exotic_fruits: {
                name: 'Exotic Summer Fruit Fest',
                title: 'Exotic Tropical Fruits Festival (BOGO)',
                subtitle: 'Imported fresh berries, sweet dragonfruit, avocados and kiwi packs at wholesale prices.',
                link: '/categories/fruits',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#4a044e',
                    backgroundImage: '/images/banners/hero-grocery-1.jpg',
                    overlayColor: '#3b0764',
                    overlayOpacity: 35,
                },
                elements: [
                    {
                        id: 'elem-badge-exotic',
                        type: 'badge',
                        content: '🍇 BUY 1 GET 1 FREE (BOGO)',
                        x: 8,
                        y: 15,
                        width: 24,
                        height: 7,
                        rotation: 0,
                        zIndex: 10,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 14,
                            fontWeight: 800,
                            backgroundColor: '#d946ef',
                            color: '#ffffff',
                            borderRadius: 9999,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-headline-exotic',
                        type: 'text',
                        content: 'Exotic Summer Fruits Fiesta',
                        x: 8,
                        y: 25,
                        width: 56,
                        height: 22,
                        rotation: 0,
                        zIndex: 11,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Outfit',
                            fontSize: 56,
                            fontWeight: 800,
                            color: '#ffffff',
                            textAlign: 'left',
                            lineHeight: 1.15,
                            letterSpacing: -0.5,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-subtitle-exotic',
                        type: 'text',
                        content: 'Imported berries, dragonfruit, avocados & sweet kiwis at unbelievable wholesale rates.',
                        x: 8,
                        y: 49,
                        width: 50,
                        height: 12,
                        rotation: 0,
                        zIndex: 12,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 20,
                            fontWeight: 500,
                            color: '#f5d0fe',
                            textAlign: 'left',
                            lineHeight: 1.4,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-cta-exotic',
                        type: 'button',
                        content: 'Explore Fruit Fiesta →',
                        url: '/categories/fruits',
                        x: 8,
                        y: 64,
                        width: 20,
                        height: 10,
                        rotation: 0,
                        zIndex: 13,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 16,
                            fontWeight: 700,
                            backgroundColor: '#a855f7',
                            color: '#ffffff',
                            borderRadius: 12,
                            paddingX: 24,
                            paddingY: 12,
                            opacity: 100,
                        },
                    },
                ],
            },

            meat_saver: {
                name: 'Prime Cut Meat & Seafood',
                title: 'Prime Cut Tender Meat & Fresh Catch Seafood',
                subtitle: 'Antibiotic-free chicken, tender mutton, fresh sea fish & marinated gourmet BBQ cuts.',
                link: '/categories/meat-seafood',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#1c1917',
                    backgroundImage: '/images/banners/hero-grocery-2.jpg',
                    overlayColor: '#09090b',
                    overlayOpacity: 45,
                },
                elements: [
                    {
                        id: 'elem-badge-meat',
                        type: 'badge',
                        content: '🥩 100% FRESH & HYGIENIC CUTS',
                        x: 8,
                        y: 15,
                        width: 26,
                        height: 7,
                        rotation: 0,
                        zIndex: 10,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 14,
                            fontWeight: 800,
                            backgroundColor: '#e11d48',
                            color: '#ffffff',
                            borderRadius: 9999,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-headline-meat',
                        type: 'text',
                        content: 'Prime Cut Meat & Fresh Seafood',
                        x: 8,
                        y: 25,
                        width: 56,
                        height: 22,
                        rotation: 0,
                        zIndex: 11,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 54,
                            fontWeight: 800,
                            color: '#ffffff',
                            textAlign: 'left',
                            lineHeight: 1.15,
                            letterSpacing: -0.5,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-subtitle-meat',
                        type: 'text',
                        content: 'Antibiotic-free tender chicken, fresh fish & ready-to-cook gourmet marinated steaks.',
                        x: 8,
                        y: 49,
                        width: 50,
                        height: 12,
                        rotation: 0,
                        zIndex: 12,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 20,
                            fontWeight: 500,
                            color: '#cbd5e1',
                            textAlign: 'left',
                            lineHeight: 1.4,
                            opacity: 100,
                        },
                    },
                    {
                        id: 'elem-cta-meat',
                        type: 'button',
                        content: 'Shop Fresh Meat & Fish →',
                        url: '/categories/meat-seafood',
                        x: 8,
                        y: 64,
                        width: 22,
                        height: 10,
                        rotation: 0,
                        zIndex: 13,
                        visible: true,
                        locked: false,
                        style: {
                            fontFamily: 'Instrument Sans',
                            fontSize: 16,
                            fontWeight: 700,
                            backgroundColor: '#be123c',
                            color: '#ffffff',
                            borderRadius: 12,
                            paddingX: 24,
                            paddingY: 12,
                            opacity: 100,
                        },
                    },
                ],
            },
        };
    }

    /**
     * Initialize template click handlers
     */
    init() {
        const self = this;

        $('#drawer-tab-templates').on('click', '.template-card', function () {
            const templateKey = $(this).attr('data-template');
            if (templateKey) {
                self.confirmAndApplyTemplate(templateKey);
            }
        });
    }

    /**
     * Confirm with user before replacing canvas layout
     */
    confirmAndApplyTemplate(templateKey) {
        const tpl = this.templates[templateKey];
        if (!tpl) return;

        const executeApply = () => {
            // Update canvas base
            this.state.state.canvas = $.extend(true, {}, tpl.canvas);

            // Replace elements
            this.state.state.elements = $.extend(true, [], tpl.elements);
            this.state.state.selectedElementId = null;

            // Update top bar banner title input
            if (tpl.title) {
                $('#builder-banner-title').val(tpl.title);
            }

            this.state.pushHistory(`Apply ${tpl.name} Preset`);
            this.state.emit('canvas:updated', this.state.getCanvas());
            this.state.emit('state:changed', this.state.getState());

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Template Applied',
                    text: `Loaded "${tpl.name}" theme preset onto canvas.`,
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            }
        };

        if (this.state.state.isDirty && window.Swal) {
            Swal.fire({
                title: `Apply "${tpl.name}" Theme?`,
                text: 'This will replace your current elements and styling with this pre-configured layout.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Apply Theme',
                cancelButtonText: 'Keep Current Design',
            }).then((result) => {
                if (result.isConfirmed) {
                    executeApply();
                }
            });
        } else {
            executeApply();
        }
    }
}

// Instantiate and expose globally
const BannerBuilderTemplates = new BannerBuilderTemplatesEngine();
window.BannerBuilderTemplates = BannerBuilderTemplates;

export default BannerBuilderTemplates;
