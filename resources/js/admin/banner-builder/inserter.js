/**
 * Grocery Banner Builder - Toolbox Insertion & Media Upload Engine
 * Pure jQuery insertion logic for Text, Badges, Buttons, Products, Shapes, and Media Assets.
 */

import jQuery from 'jquery';
import Swal from 'sweetalert2';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderInserterEngine {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;
    }

    /**
     * Initialize all toolbox insertion buttons and search filter
     */
    init() {
        this.bindTextInserters();
        this.bindBadgeInserters();
        this.bindProductInserters();
        this.bindShapeInserters();
        this.bindMediaUploadAndStock();
    }

    /**
     * Compute a staggered, comfortable auto-placement position
     */
    getSmartPlacement(width = 30, height = 12) {
        const elements = this.state.getElements();
        const count = elements.length;

        const baseStartX = 10;
        const baseStartY = 20;

        const staggerStep = (count * 4) % 24;

        let x = Math.min(baseStartX + staggerStep, 95 - width);
        let y = Math.min(baseStartY + staggerStep, 95 - height);

        x = Math.max(5, Math.round(x));
        y = Math.max(5, Math.round(y));

        return { x, y };
    }

    /**
     * 1. Text & Typography Inserters
     */
    bindTextInserters() {
        const self = this;

        // Big Headline
        $('#btn-add-headline').on('click', function () {
            const pos = self.getSmartPlacement(50, 18);
            self.state.addElement({
                type: 'text',
                content: 'Special Grocery Headline',
                x: pos.x,
                y: pos.y,
                width: 50,
                height: 18,
                rotation: 0,
                style: {
                    fontFamily: 'Instrument Sans',
                    fontSize: 52,
                    fontWeight: 800,
                    color: '#ffffff',
                    textAlign: 'left',
                    lineHeight: 1.15,
                    letterSpacing: -0.5,
                    opacity: 100,
                },
            });
        });

        // Subtitle
        $('#btn-add-subtitle').on('click', function () {
            const pos = self.getSmartPlacement(45, 12);
            self.state.addElement({
                type: 'text',
                content: 'Fresh quality ingredients delivered fast to your home.',
                x: pos.x,
                y: pos.y,
                width: 45,
                height: 12,
                rotation: 0,
                style: {
                    fontFamily: 'Instrument Sans',
                    fontSize: 22,
                    fontWeight: 500,
                    color: '#cbd5e1',
                    textAlign: 'left',
                    lineHeight: 1.4,
                    opacity: 100,
                },
            });
        });

        // CTA Button
        $('#btn-add-cta-btn').on('click', function () {
            const pos = self.getSmartPlacement(18, 9);
            self.state.addElement({
                type: 'button',
                content: 'Shop Now →',
                url: '/shop',
                x: pos.x,
                y: pos.y,
                width: 18,
                height: 9,
                rotation: 0,
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
            });
        });
    }

    /**
     * 2. Promotional Badges & Stickers Inserters
     */
    bindBadgeInserters() {
        const self = this;

        $('.btn-insert-badge').on('click', function () {
            const text = $(this).attr('data-badge-text') || '50% OFF';
            const bg = $(this).attr('data-bg') || '#ef4444';
            const color = $(this).attr('data-color') || '#ffffff';

            const pos = self.getSmartPlacement(22, 7);

            self.state.addElement({
                type: 'badge',
                content: text,
                x: pos.x,
                y: pos.y,
                width: 22,
                height: 7,
                rotation: 0,
                style: {
                    fontFamily: 'Instrument Sans',
                    fontSize: 13,
                    fontWeight: 800,
                    backgroundColor: bg,
                    color: color,
                    borderRadius: 9999,
                    opacity: 100,
                },
            });
        });
    }

    /**
     * 3. Catalog Products Inserter & Real-Time Filter
     */
    bindProductInserters() {
        const self = this;

        // Search Filter
        $('#product-search-input').on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            $('#product-picker-list .product-insert-card').each(function () {
                const name = ($(this).attr('data-product-name') || '').toLowerCase();
                $(this).toggle(name.includes(query));
            });
        });

        // Insert Product Card
        $('#product-picker-list').on('click', '.product-insert-card', function () {
            const prodId = $(this).attr('data-product-id');
            const name = $(this).attr('data-product-name');
            const price = parseFloat($(this).attr('data-product-price')) || 0;
            const specialPrice = parseFloat($(this).attr('data-product-special')) || null;
            const image = $(this).attr('data-product-image');

            const formattedPrice = specialPrice ? `₹${specialPrice.toFixed(2)}` : `₹${price.toFixed(2)}`;

            const pos = self.getSmartPlacement(28, 16);

            self.state.addElement({
                type: 'product',
                content: name,
                url: `/products/${prodId}`,
                productData: {
                    id: prodId,
                    name: name,
                    price: formattedPrice,
                    image: image || '/images/placeholder.svg',
                },
                x: pos.x,
                y: pos.y,
                width: 28,
                height: 16,
                rotation: 0,
                style: {
                    borderRadius: 16,
                    opacity: 100,
                },
            });

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Product Added',
                    text: `Placed "${name}" onto the banner canvas.`,
                    timer: 1400,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            }
        });
    }

    /**
     * 4. Shapes & Containers Inserters
     */
    bindShapeInserters() {
        const self = this;

        $('.btn-insert-shape').on('click', function () {
            const shape = $(this).attr('data-shape') || 'card';
            const bg = $(this).attr('data-bg') || 'rgba(15, 23, 42, 0.75)';

            let width = 35;
            let height = 30;
            let radius = 16;

            if (shape === 'pill') {
                width = 24;
                height = 8;
                radius = 9999;
            } else if (shape === 'circle') {
                width = 14;
                height = 14;
                radius = 9999;
            } else if (shape === 'divider') {
                width = 40;
                height = 1;
                radius = 0;
            }

            const pos = self.getSmartPlacement(width, height);

            self.state.addElement({
                type: 'shape',
                content: shape,
                x: pos.x,
                y: pos.y,
                width: width,
                height: height,
                rotation: 0,
                zIndex: 5, // place shapes below text by default
                style: {
                    backgroundColor: bg,
                    borderRadius: radius,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 255, 255, 0.15)',
                    opacity: 100,
                },
            });
        });
    }

    /**
     * 5. Media Asset Upload & Curated Stock Replacement
     */
    bindMediaUploadAndStock() {
        const self = this;

        // Stock photo click -> update canvas background
        $('.stock-media-item').on('click', function () {
            const imgUrl = $(this).attr('data-img');
            if (!imgUrl) return;

            self.state.updateCanvas({ backgroundImage: imgUrl });

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Background Updated',
                    text: 'Applied stock supermarket photography to canvas background.',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            }
        });

        // Trigger file input dialog
        $('#builder-upload-zone').on('click', function () {
            $('#builder-file-uploader').trigger('click');
        });

        // Handle uploaded file
        $('#builder-file-uploader').on('change', function () {
            const file = this.files[0];
            if (!file) return;

            const uploadUrl = window.__BANNER_BUILDER_DATA__ ? window.__BANNER_BUILDER_DATA__.uploadAssetUrl : '';
            if (!uploadUrl) {
                console.error('Upload URL is missing.');
                return;
            }

            const formData = new FormData();
            formData.append('asset', file);
            formData.append('_token', window.__BANNER_BUILDER_DATA__.csrfToken || $('meta[name="csrf-token"]').attr('content'));

            if (window.Swal) {
                Swal.fire({
                    title: 'Uploading Graphic...',
                    text: 'Optimizing and storing banner asset.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            }

            $.ajax({
                url: uploadUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res && res.url) {
                        // Ask user whether to set as background or insert as placed sticker
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Asset Uploaded!',
                                text: 'How would you like to use this image?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Set as Background',
                                cancelButtonText: 'Insert as Graphic Sticker',
                                confirmButtonColor: '#10b981',
                                cancelButtonColor: '#6366f1',
                            }).then((choice) => {
                                if (choice.isConfirmed) {
                                    self.state.updateCanvas({ backgroundImage: res.url });
                                } else {
                                    const pos = self.getSmartPlacement(25, 25);
                                    self.state.addElement({
                                        type: 'image',
                                        url: res.url,
                                        x: pos.x,
                                        y: pos.y,
                                        width: 25,
                                        height: 25,
                                        rotation: 0,
                                        style: {
                                            borderRadius: 8,
                                            opacity: 100,
                                        },
                                    });
                                }
                            });
                        } else {
                            self.state.updateCanvas({ backgroundImage: res.url });
                        }
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to upload asset. Please try again.';
                    if (window.Swal) {
                        Swal.fire('Upload Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                },
                complete: function () {
                    // Reset input
                    $('#builder-file-uploader').val('');
                },
            });
        });
    }
}

// Instantiate and expose globally
const BannerBuilderInserter = new BannerBuilderInserterEngine();
window.BannerBuilderInserter = BannerBuilderInserter;

export default BannerBuilderInserter;
