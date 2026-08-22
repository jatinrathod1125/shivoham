/**
 * Grocery Banner Builder - High-Res Export Engine (PNG / JPEG / JSON) & Template Importer
 * Rasterizes virtual canvas coordinates to 1920x700 HTML5 Canvas and exports design JSON schemas.
 */

class BannerBuilderExporterEngine {
    constructor() {
        this.state = window.BannerBuilderState;
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const self = this;

        // Toggle Export Dropdown
        $(document).on('click', '#btn-export-dropdown', function (e) {
            e.stopPropagation();
            $('#export-dropdown-menu').toggleClass('hidden');
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#export-dropdown-container').length) {
                $('#export-dropdown-menu').addClass('hidden');
            }
        });

        // Export JSON Schema
        $(document).on('click', '#btn-export-json', function () {
            $('#export-dropdown-menu').addClass('hidden');
            self.exportJSON();
        });

        // Export High-Res PNG
        $(document).on('click', '#btn-export-png', function () {
            $('#export-dropdown-menu').addClass('hidden');
            self.exportRaster('png', 1.0);
        });

        // Export High-Res JPEG
        $(document).on('click', '#btn-export-jpg', function () {
            $('#export-dropdown-menu').addClass('hidden');
            self.exportRaster('jpeg', 0.92);
        });

        // Import JSON Schema
        $(document).on('click', '#btn-import-json', function () {
            $('#export-dropdown-menu').addClass('hidden');
            $('#import-json-file').trigger('click');
        });

        $(document).on('change', '#import-json-file', function (e) {
            const file = e.target.files[0];
            if (file) {
                self.importJSON(file);
            }
            $(this).val('');
        });
    }

    /**
     * Export current canvas design configuration as downloadable JSON file
     */
    exportJSON() {
        if (!window.BannerBuilderState) return;

        const state = window.BannerBuilderState;
        const design = typeof state.getDesignConfig === 'function'
            ? state.getDesignConfig()
            : {
                version: 2,
                canvas: state.getCanvas(),
                elements: state.getElements(),
                device: (state.state && state.state.device) || 'desktop',
            };

        const jsonStr = JSON.stringify(design, null, 2);
        const blob = new Blob([jsonStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);

        const bannerTitle = ($('#builder-banner-title').val() || 'grocery-banner').toLowerCase().replace(/[^a-z0-9]+/g, '-');
        const filename = `${bannerTitle}-template.json`;

        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        if (window.Swal) {
            window.Swal.fire({
                icon: 'success',
                title: 'Template Exported',
                text: `Exported design schema to ${filename}`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
            });
        }
    }

    /**
     * Import and restore design configuration from a local JSON file
     */
    importJSON(file) {
        const self = this;
        const reader = new FileReader();

        reader.onload = function (e) {
            try {
                const data = JSON.parse(e.target.result);
                if (!data || !data.canvas || !Array.isArray(data.elements)) {
                    throw new Error('Invalid banner template JSON structure.');
                }

                if (window.BannerBuilderState) {
                    window.BannerBuilderState.pushHistory('Import template JSON');
                } else if (window.BannerBuilderHistory && typeof window.BannerBuilderHistory.pushSnapshot === 'function') {
                    window.BannerBuilderHistory.pushSnapshot('Import template JSON');
                }

                if (window.BannerBuilderState && window.BannerBuilderState.state) {
                    window.BannerBuilderState.state.canvas = Object.assign({}, window.BannerBuilderState.state.canvas, data.canvas);
                    window.BannerBuilderState.state.elements = data.elements;
                    window.BannerBuilderState.state.selectedElementId = null;
                    window.BannerBuilderState.state.isDirty = true;
                }

                if (window.BannerBuilderRenderer) {
                    window.BannerBuilderRenderer.render();
                }
                if (window.BannerBuilderSelection) {
                    window.BannerBuilderSelection.syncInspector();
                    window.BannerBuilderSelection.renderLayerTree();
                }

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Template Imported',
                        text: `Restored ${data.elements.length} element layers onto canvas.`,
                    });
                }
            } catch (err) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Import Failed',
                        text: err.message || 'Could not parse JSON template file.',
                    });
                } else {
                    alert('Import Failed: ' + (err.message || 'Could not parse JSON template file.'));
                }
            }
        };

        reader.readAsText(file);
    }

    /**
     * High-Resolution 1920x700 Canvas Rasterization (PNG / JPEG)
     */
    exportRaster(format = 'png', quality = 0.95) {
        const self = this;
        const canvas = document.createElement('canvas');
        canvas.width = 1920;
        canvas.height = 700;
        const ctx = canvas.getContext('2d');

        const stateCanvas = window.BannerBuilderState ? window.BannerBuilderState.getCanvas() : {};
        const elements = window.BannerBuilderState ? window.BannerBuilderState.getElements() : [];

        // 1. Draw Base Background Color
        ctx.fillStyle = stateCanvas.backgroundColor || '#f8fafc';
        ctx.fillRect(0, 0, 1920, 700);

        const bannerTitle = ($('#builder-banner-title').val() || 'grocery-banner').toLowerCase().replace(/[^a-z0-9]+/g, '-');
        const ext = format === 'jpeg' ? 'jpg' : 'png';
        const mime = format === 'jpeg' ? 'image/jpeg' : 'image/png';

        const proceedWithElements = () => {
            // 2. Draw Overlay Dimmer
            if ((stateCanvas.overlayOpacity || 0) > 0) {
                ctx.save();
                ctx.fillStyle = stateCanvas.overlayColor || '#000000';
                ctx.globalAlpha = (stateCanvas.overlayOpacity || 0) / 100;
                ctx.fillRect(0, 0, 1920, 700);
                ctx.restore();
            }

            // 3. Draw Elements sorted by z-index
            const sorted = [...elements].sort((a, b) => (a.zIndex || 10) - (b.zIndex || 10));

            sorted.forEach(elem => {
                if (elem.visible === false) return;

                const x = (elem.x / 100) * 1920;
                const y = (elem.y / 100) * 700;
                const w = (elem.width / 100) * 1920;
                const h = (elem.height / 100) * 700;
                const style = elem.style || {};
                const rot = (elem.rotation || 0) * (Math.PI / 180);

                ctx.save();
                ctx.translate(x + w / 2, y + h / 2);
                ctx.rotate(rot);

                const scaleX = style.flipH ? -1 : 1;
                const scaleY = style.flipV ? -1 : 1;
                ctx.scale(scaleX, scaleY);
                ctx.translate(-w / 2, -h / 2);

                ctx.globalAlpha = (style.opacity !== undefined ? style.opacity : 100) / 100;

                if (elem.type === 'shape') {
                    ctx.fillStyle = style.backgroundColor || 'rgba(15, 23, 42, 0.75)';
                    self.roundRect(ctx, 0, 0, w, h, style.borderRadius || 16);
                    ctx.fill();
                } else if (elem.type === 'badge') {
                    ctx.fillStyle = style.backgroundColor || '#ef4444';
                    self.roundRect(ctx, 0, 0, w, h, style.borderRadius || 9999);
                    ctx.fill();
                    ctx.fillStyle = style.color || '#ffffff';
                    ctx.font = `bold ${style.fontSize || 22}px ${style.fontFamily || 'Instrument Sans'}, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(elem.content || '50% OFF', w / 2, h / 2);
                } else if (elem.type === 'button') {
                    ctx.fillStyle = style.backgroundColor || '#16a34a';
                    self.roundRect(ctx, 0, 0, w, h, style.borderRadius || 12);
                    ctx.fill();
                    ctx.fillStyle = style.color || '#ffffff';
                    ctx.font = `${style.fontWeight || 600} ${style.fontSize || 26}px ${style.fontFamily || 'Instrument Sans'}, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(elem.content || 'Shop Now', w / 2, h / 2);
                } else if (elem.type === 'text') {
                    ctx.fillStyle = style.color || '#ffffff';
                    ctx.font = `${style.fontWeight || 700} ${style.fontSize || 48}px ${style.fontFamily || 'Instrument Sans'}, sans-serif`;
                    ctx.textAlign = style.textAlign || 'left';
                    ctx.textBaseline = 'top';

                    const textX = style.textAlign === 'center' ? w / 2 : style.textAlign === 'right' ? w : 0;
                    ctx.fillText(elem.content || '', textX, 0);
                }

                ctx.restore();
            });

            // 4. Download file
            canvas.toBlob(blob => {
                if (!blob) return;
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${bannerTitle}-1920x700.${ext}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Graphic Exported',
                        text: `Downloaded high-res 1920×700 ${format.toUpperCase()}`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }
            }, mime, quality);
        };

        // If background image exists, load first with crossOrigin
        if (stateCanvas.backgroundImage) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function () {
                ctx.drawImage(img, 0, 0, 1920, 700);
                proceedWithElements();
            };
            img.onerror = function () {
                proceedWithElements();
            };
            img.src = stateCanvas.backgroundImage;
        } else {
            proceedWithElements();
        }
    }

    roundRect(ctx, x, y, width, height, radius) {
        if (typeof radius === 'number') {
            radius = { tl: radius, tr: radius, br: radius, bl: radius };
        }
        ctx.beginPath();
        ctx.moveTo(x + radius.tl, y);
        ctx.lineTo(x + width - radius.tr, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
        ctx.lineTo(x + width, y + height - radius.br);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
        ctx.lineTo(x + radius.bl, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
        ctx.lineTo(x, y + radius.tl);
        ctx.quadraticCurveTo(x, y, x + radius.tl, y);
        ctx.closePath();
    }
}

// Instantiate and expose globally
const BannerBuilderExporter = new BannerBuilderExporterEngine();
window.BannerBuilderExporter = BannerBuilderExporter;

export default BannerBuilderExporter;
