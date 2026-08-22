import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderStateEngine {
    constructor() {
            this.state = {
                bannerId: null,
                saveUrl: '',
                uploadAssetUrl: '',
                csrfToken: '',
                canvas: {
                    width: 1920,
                    height: 700,
                    backgroundColor: '#f8fafc',
                    backgroundImage: null,
                    overlayOpacity: 0,
                    overlayColor: '#000000',
                },
                elements: [],
                selectedElementId: null,
                history: [],
                historyIndex: -1,
                maxHistory: 30,
                zoom: 'fit', // 'fit' or numerical 0.25 to 2.0
                zoomScale: 1.0,
                device: 'desktop', // 'desktop', 'tablet', 'mobile'
                showGrid: false,
                isDirty: false,
                isSaving: false,
            };

            this.listeners = {};
        }

        /**
         * Initialize state from server payload
         */
        init(serverData) {
            if (!serverData) return;

            this.state.bannerId = serverData.bannerId || null;
            this.state.saveUrl = serverData.saveUrl || '';
            this.state.uploadAssetUrl = serverData.uploadAssetUrl || '';
            this.state.csrfToken = serverData.csrfToken || '';

            if (serverData.designConfig) {
                if (serverData.designConfig.canvas) {
                    this.state.canvas = $.extend(true, {}, this.state.canvas, serverData.designConfig.canvas);
                }
                if (Array.isArray(serverData.designConfig.elements)) {
                    this.state.elements = $.extend(true, [], serverData.designConfig.elements);
                }
            }

            // Push initial snapshot into history
            this.state.history = [];
            this.state.historyIndex = -1;
            this.pushHistory('Initial load');
            this.state.isDirty = false;

            this.emit('init', this.getState());
            this.emit('state:changed', this.getState());
            return this;
        }

        /**
         * Get full immutable state snapshot
         */
        getState() {
            return $.extend(true, {}, this.state);
        }

        getDesignConfig() {
            return {
                version: 2,
                canvas: this.getCanvas(),
                elements: this.getElements(),
                device: this.state.device || 'desktop',
            };
        }

        getCanvas() {
            return $.extend(true, {}, this.state.canvas);
        }

        getElements() {
            return $.extend(true, [], this.state.elements);
        }

        getElement(id) {
            const elem = this.state.elements.find(e => e.id === id);
            return elem ? $.extend(true, {}, elem) : null;
        }

        getSelectedElement() {
            if (!this.state.selectedElementId) return null;
            return this.getElement(this.state.selectedElementId);
        }

        generateUniqueId(prefix = 'elem') {
            return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 7)}`;
        }

        /**
         * Add a new element to canvas
         */
        addElement(rawElement, pushToHistory = true) {
            const defaults = {
                id: this.generateUniqueId(rawElement.type || 'elem'),
                type: 'text', // 'text', 'button', 'image', 'product', 'shape', 'badge'
                content: 'New Element',
                url: null,
                x: 10,
                y: 20,
                width: 30,
                height: 12,
                rotation: 0,
                zIndex: this.getNextZIndex(),
                visible: true,
                locked: false,
                style: {
                    fontFamily: 'Instrument Sans',
                    fontSize: 24,
                    fontWeight: 600,
                    color: '#ffffff',
                    textAlign: 'left',
                    backgroundColor: 'transparent',
                    borderRadius: 0,
                    opacity: 100,
                },
            };

            const element = $.extend(true, {}, defaults, rawElement);
            this.state.elements.push(element);
            this.state.selectedElementId = element.id;
            this.state.isDirty = true;

            if (pushToHistory) {
                this.pushHistory(`Add ${element.type} element`);
            }

            this.emit('element:added', element);
            this.emit('element:selected', element);
            this.emit('state:changed', this.getState());
            return element;
        }

        /**
         * Update an element by ID with partial properties
         */
        updateElement(id, partialProps, pushToHistory = true) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index === -1) return null;

            const existing = this.state.elements[index];
            if (existing.locked && partialProps.locked === undefined) {
                // If element is locked, only unlock property is allowed to update
                return existing;
            }

            // Deep merge styles if provided
            if (partialProps.style) {
                partialProps.style = $.extend(true, {}, existing.style, partialProps.style);
            }

            const updated = $.extend(true, {}, existing, partialProps);
            this.state.elements[index] = updated;
            this.state.isDirty = true;

            if (pushToHistory) {
                this.pushHistory(`Update element ${updated.id}`);
            }

            this.emit('element:updated', updated);
            this.emit('state:changed', this.getState());
            return updated;
        }

        /**
         * Remove an element from canvas
         */
        removeElement(id, pushToHistory = true) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index === -1) return false;

            const removed = this.state.elements[index];
            this.state.elements.splice(index, 1);

            if (this.state.selectedElementId === id) {
                this.state.selectedElementId = null;
                this.emit('element:deselected', null);
            }

            this.state.isDirty = true;

            if (pushToHistory) {
                this.pushHistory(`Remove element ${removed.id}`);
            }

            this.emit('element:removed', removed);
            this.emit('state:changed', this.getState());
            return true;
        }

        /**
         * Select an element by ID
         */
        selectElement(id) {
            if (this.state.selectedElementId === id) return;

            const elem = this.getElement(id);
            if (elem) {
                this.state.selectedElementId = id;
                this.emit('element:selected', elem);
            } else {
                this.clearSelection();
            }
            this.emit('state:changed', this.getState());
        }

        /**
         * Clear current element selection
         */
        clearSelection() {
            if (this.state.selectedElementId === null) return;
            this.state.selectedElementId = null;
            this.emit('element:deselected', null);
            this.emit('state:changed', this.getState());
        }

        /**
         * Duplicate currently selected or specified element
         */
        duplicateElement(id = null) {
            const targetId = id || this.state.selectedElementId;
            if (!targetId) return null;

            const source = this.getElement(targetId);
            if (!source) return null;

            const duplicate = $.extend(true, {}, source, {
                id: this.generateUniqueId(source.type),
                x: Math.min(source.x + 3, 90),
                y: Math.min(source.y + 3, 90),
                zIndex: this.getNextZIndex(),
                locked: false,
            });

            this.addElement(duplicate, true);
            return duplicate;
        }

        /**
         * Layer ordering methods
         */
        bringForward(id) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index < 0 || index >= this.state.elements.length - 1) return;

            const temp = this.state.elements[index];
            this.state.elements[index] = this.state.elements[index + 1];
            this.state.elements[index + 1] = temp;
            this.recalculateZIndices();
            this.pushHistory('Bring layer forward');
            this.emit('state:changed', this.getState());
        }

        sendBackward(id) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index <= 0) return;

            const temp = this.state.elements[index];
            this.state.elements[index] = this.state.elements[index - 1];
            this.state.elements[index - 1] = temp;
            this.recalculateZIndices();
            this.pushHistory('Send layer backward');
            this.emit('state:changed', this.getState());
        }

        bringToFront(id) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index === -1 || index === this.state.elements.length - 1) return;

            const item = this.state.elements.splice(index, 1)[0];
            this.state.elements.push(item);
            this.recalculateZIndices();
            this.pushHistory('Bring layer to front');
            this.emit('state:changed', this.getState());
        }

        sendToBack(id) {
            const index = this.state.elements.findIndex(e => e.id === id);
            if (index <= 0) return;

            const item = this.state.elements.splice(index, 1)[0];
            this.state.elements.unshift(item);
            this.recalculateZIndices();
            this.pushHistory('Send layer to back');
            this.emit('state:changed', this.getState());
        }

        recalculateZIndices() {
            this.state.elements.forEach((elem, idx) => {
                elem.zIndex = 10 + idx;
            });
        }

        getNextZIndex() {
            if (this.state.elements.length === 0) return 10;
            const maxZ = Math.max(...this.state.elements.map(e => e.zIndex || 10));
            return maxZ + 1;
        }

        /**
         * Update canvas settings
         */
        updateCanvas(partialCanvas, pushToHistory = true) {
            this.state.canvas = $.extend(true, {}, this.state.canvas, partialCanvas);
            this.state.isDirty = true;

            if (pushToHistory) {
                this.pushHistory('Update canvas properties');
            }

            this.emit('canvas:updated', this.getCanvas());
            this.emit('state:changed', this.getState());
        }

        /**
         * History Undo / Redo
         */
        pushHistory(description = 'Action') {
            // Trim any redo states ahead of current index
            if (this.state.historyIndex < this.state.history.length - 1) {
                this.state.history = this.state.history.slice(0, this.state.historyIndex + 1);
            }

            const snapshot = {
                description: description,
                canvas: $.extend(true, {}, this.state.canvas),
                elements: $.extend(true, [], this.state.elements),
                selectedElementId: this.state.selectedElementId,
            };

            this.state.history.push(snapshot);

            if (this.state.history.length > this.state.maxHistory) {
                this.state.history.shift();
            } else {
                this.state.historyIndex++;
            }

            this.emit('history:changed', {
                canUndo: this.canUndo(),
                canRedo: this.canRedo(),
                index: this.state.historyIndex,
                total: this.state.history.length,
            });
        }

        canUndo() {
            return this.state.historyIndex > 0;
        }

        canRedo() {
            return this.state.historyIndex < this.state.history.length - 1;
        }

        undo() {
            if (!this.canUndo()) return;

            this.state.historyIndex--;
            const snapshot = this.state.history[this.state.historyIndex];

            this.state.canvas = $.extend(true, {}, snapshot.canvas);
            this.state.elements = $.extend(true, [], snapshot.elements);
            this.state.selectedElementId = snapshot.selectedElementId;
            this.state.isDirty = true;

            this.emit('history:changed', {
                canUndo: this.canUndo(),
                canRedo: this.canRedo(),
                index: this.state.historyIndex,
                total: this.state.history.length,
            });
            this.emit('state:changed', this.getState());
            if (this.state.selectedElementId) {
                this.emit('element:selected', this.getSelectedElement());
            } else {
                this.emit('element:deselected', null);
            }
        }

        redo() {
            if (!this.canRedo()) return;

            this.state.historyIndex++;
            const snapshot = this.state.history[this.state.historyIndex];

            this.state.canvas = $.extend(true, {}, snapshot.canvas);
            this.state.elements = $.extend(true, [], snapshot.elements);
            this.state.selectedElementId = snapshot.selectedElementId;
            this.state.isDirty = true;

            this.emit('history:changed', {
                canUndo: this.canUndo(),
                canRedo: this.canRedo(),
                index: this.state.historyIndex,
                total: this.state.history.length,
            });
            this.emit('state:changed', this.getState());
            if (this.state.selectedElementId) {
                this.emit('element:selected', this.getSelectedElement());
            } else {
                this.emit('element:deselected', null);
            }
        }

        /**
         * Viewport zoom & device settings
         */
        setZoom(zoom) {
            this.state.zoom = zoom;
            this.emit('zoom:changed', zoom);
            this.emit('state:changed', this.getState());
        }

        setZoomScale(scale) {
            this.state.zoomScale = scale;
            this.emit('zoom:scale', scale);
        }

        setDevice(device) {
            this.state.device = device;
            this.emit('device:changed', device);
            this.emit('state:changed', this.getState());
        }

        toggleGrid(show) {
            this.state.showGrid = (show !== undefined) ? show : !this.state.showGrid;
            this.emit('grid:toggled', this.state.showGrid);
            return this.state.showGrid;
        }

        markClean() {
            this.state.isDirty = false;
            this.emit('save:status', { isDirty: false, isSaving: false });
        }

        markSaving(isSaving) {
            this.state.isSaving = isSaving;
            this.emit('save:status', { isDirty: this.state.isDirty, isSaving: isSaving });
        }

        /**
         * Event Emitter Pattern
         */
        on(event, handler) {
            if (!this.listeners[event]) {
                this.listeners[event] = [];
            }
            this.listeners[event].push(handler);
            return this;
        }

        off(event, handler) {
            if (!this.listeners[event]) return this;
            this.listeners[event] = this.listeners[event].filter(h => h !== handler);
            return this;
        }

        emit(event, payload) {
            if (this.listeners[event]) {
                this.listeners[event].forEach(handler => {
                    try {
                        handler(payload);
                    } catch (err) {
                        console.error(`[BannerBuilderState error in event "${event}"]:`, err);
                    }
                });
            }
    }
}

// Expose global instance
const BannerBuilderState = new BannerBuilderStateEngine();
window.BannerBuilderState = BannerBuilderState;

export default BannerBuilderState;
