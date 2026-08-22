/**
 * Grocery Banner Builder - History (Undo / Redo) & Keyboard Shortcuts Engine
 * Global hotkeys, nudge navigation, and undo/redo toolbar state synchronization.
 */

import jQuery from 'jquery';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderHistoryEngine {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;
    }

    /**
     * Push snapshot into state history
     */
    pushSnapshot(description = 'Action') {
        if (window.BannerBuilderState) {
            window.BannerBuilderState.pushHistory(description);
        } else if (this.state) {
            this.state.pushHistory(description);
        }
    }

    pushHistory(description = 'Action') {
        this.pushSnapshot(description);
    }

    /**
     * Initialize history toolbar listeners and global keyboard shortcuts
     */
    init() {
        this.bindToolbarButtons();
        this.bindKeyboardShortcuts();
        this.bindHistoryStateObserver();
    }

    /**
     * Bind Undo and Redo header buttons
     */
    bindToolbarButtons() {
        const self = this;

        $('#btn-undo').on('click', function () {
            self.state.undo();
        });

        $('#btn-redo').on('click', function () {
            self.state.redo();
        });

        $('#btn-toggle-grid').on('click', function () {
            const isGrid = self.state.toggleGrid();
            $(this).toggleClass('text-emerald-400 bg-slate-700', isGrid);
        });
    }

    /**
     * Listen to history changes to enable/disable toolbar buttons
     */
    bindHistoryStateObserver() {
        this.state.on('history:changed', (data) => {
            const $btnUndo = $('#btn-undo');
            const $btnRedo = $('#btn-redo');

            $btnUndo.prop('disabled', !data.canUndo);
            $btnRedo.prop('disabled', !data.canRedo);

            if (data.canUndo) {
                $btnUndo.removeClass('opacity-40 cursor-not-allowed').addClass('hover:text-white cursor-pointer');
            } else {
                $btnUndo.addClass('opacity-40 cursor-not-allowed').removeClass('hover:text-white cursor-pointer');
            }

            if (data.canRedo) {
                $btnRedo.removeClass('opacity-40 cursor-not-allowed').addClass('hover:text-white cursor-pointer');
            } else {
                $btnRedo.addClass('opacity-40 cursor-not-allowed').removeClass('hover:text-white cursor-pointer');
            }
        });
    }

    /**
     * Global keyboard shortcuts handler
     */
    bindKeyboardShortcuts() {
        const self = this;

        $(document).off('keydown.bannerShortcuts').on('keydown.bannerShortcuts', function (e) {
            const isCtrlOrCmd = e.ctrlKey || e.metaKey;
            const targetTag = e.target.tagName ? e.target.tagName.toLowerCase() : '';
            const isInputFocused = targetTag === 'input' || targetTag === 'textarea' || targetTag === 'select' || e.target.isContentEditable;

            // 1. Save (Ctrl+S / Cmd+S)
            if (isCtrlOrCmd && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
                $('#btn-save-banner').trigger('click');
                return;
            }

            // 2. Undo (Ctrl+Z / Cmd+Z)
            if (isCtrlOrCmd && (e.key === 'z' || e.key === 'Z') && !e.shiftKey) {
                if (!isInputFocused) {
                    e.preventDefault();
                    self.state.undo();
                }
                return;
            }

            // 3. Redo (Ctrl+Y / Cmd+Y OR Ctrl+Shift+Z)
            if ((isCtrlOrCmd && (e.key === 'y' || e.key === 'Y')) || (isCtrlOrCmd && e.shiftKey && (e.key === 'z' || e.key === 'Z'))) {
                if (!isInputFocused) {
                    e.preventDefault();
                    self.state.redo();
                }
                return;
            }

            // 4. Duplicate Element (Ctrl+D / Cmd+D)
            if (isCtrlOrCmd && (e.key === 'd' || e.key === 'D')) {
                if (!isInputFocused && self.state.state.selectedElementId) {
                    e.preventDefault();
                    self.state.duplicateElement();
                }
                return;
            }

            // If user is currently typing in a text field, do not hijack other keys
            if (isInputFocused) return;

            // 5. Delete Selected Element (Delete or Backspace)
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (self.state.state.selectedElementId) {
                    e.preventDefault();
                    self.state.removeElement(self.state.state.selectedElementId);
                }
                return;
            }

            // 6. Escape Key (Deselect element / close dialogs)
            if (e.key === 'Escape') {
                self.state.clearSelection();
                return;
            }

            // 7. Toggle Grid (G key)
            if (e.key === 'g' || e.key === 'G') {
                $('#btn-toggle-grid').trigger('click');
                return;
            }

            // 8. Arrow Keys Nudge Navigation
            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
                const elem = self.state.getSelectedElement();
                if (!elem || elem.locked) return;

                e.preventDefault();
                const step = e.shiftKey ? 2.5 : 0.5;

                let newX = elem.x;
                let newY = elem.y;

                if (e.key === 'ArrowLeft') newX = Math.max(0, elem.x - step);
                if (e.key === 'ArrowRight') newX = Math.min(100 - elem.width, elem.x + step);
                if (e.key === 'ArrowUp') newY = Math.max(0, elem.y - step);
                if (e.key === 'ArrowDown') newY = Math.min(100 - elem.height, elem.y + step);

                newX = Math.round(newX * 10) / 10;
                newY = Math.round(newY * 10) / 10;

                self.state.updateElement(elem.id, { x: newX, y: newY });
            }
        });
    }
}

// Instantiate and expose globally
const BannerBuilderHistory = new BannerBuilderHistoryEngine();
window.BannerBuilderHistory = BannerBuilderHistory;

export default BannerBuilderHistory;
