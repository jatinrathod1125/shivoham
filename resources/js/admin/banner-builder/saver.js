/**
 * Grocery Banner Builder - AJAX Save & Backend Serialization Engine
 * Seamless asynchronous persistence, metadata extraction, and unsaved changes safety guard.
 */

import jQuery from 'jquery';
import Swal from 'sweetalert2';

const $ = window.jQuery || window.$ || jQuery;

class BannerBuilderSaverEngine {
    constructor(stateEngine) {
        this.state = stateEngine || window.BannerBuilderState;
        this.isSaving = false;
    }

    /**
     * Initialize save triggers and beforeunload protection
     */
    init() {
        this.bindSaveButton();
        this.bindScheduleModal();
        this.bindBeforeUnload();
        this.bindDirtyStateIndicator();
    }

    /**
     * Bind Save button click
     */
    bindSaveButton() {
        const self = this;

        $('#btn-save-banner').on('click', function (e) {
            e.preventDefault();
            self.save();
        });
    }

    /**
     * Bind Schedule & Dates modal open/close
     */
    bindScheduleModal() {
        $('#btn-open-schedule-modal').on('click', function () {
            $('#modal-banner-schedule').removeClass('hidden');
        });

        $('#btn-close-schedule-modal, #btn-save-schedule-apply').on('click', function () {
            $('#modal-banner-schedule').addClass('hidden');
            if (window.BannerBuilderState) {
                window.BannerBuilderState.markDirty();
            }
        });
    }

    /**
     * Save the entire visual design config and sync metadata to Laravel backend
     */
    save() {
        if (this.isSaving) return;

        const serverData = window.__BANNER_BUILDER_DATA__;
        if (!serverData || !serverData.saveUrl) {
            console.error('Save URL configuration is missing.');
            return;
        }

        const fullState = this.state.getState();
        const canvas = fullState.canvas;
        const elements = fullState.elements;

        // Extract primary headline, subtitle and link from design elements
        let primaryTitle = $('#builder-banner-title').val() || '';
        let primarySubtitle = '';
        let primaryLink = '';

        elements.forEach(elem => {
            if (elem.type === 'text') {
                if (!primaryTitle && elem.content) {
                    primaryTitle = elem.content;
                } else if (!primarySubtitle && elem.content && elem.content !== primaryTitle) {
                    primarySubtitle = elem.content;
                }
            } else if (elem.type === 'button' && elem.url && !primaryLink) {
                primaryLink = elem.url;
            } else if (elem.type === 'product' && elem.url && !primaryLink) {
                primaryLink = elem.url;
            }
        });

        const payload = {
            _token: serverData.csrfToken || $('meta[name="csrf-token"]').attr('content'),
            title: primaryTitle,
            subtitle: primarySubtitle,
            link: primaryLink,
            position: $('#builder-banner-position').val() || (serverData.banner && serverData.banner.position) || 'home_hero',
            is_active: $('#builder-banner-active').length ? $('#builder-banner-active').is(':checked') : true,
            starts_at: $('#builder-banner-starts-at').val() || null,
            expires_at: $('#builder-banner-expires-at').val() || null,
            sort_order: parseInt($('#builder-banner-sort-order').val(), 10) || 0,
            design_config: {
                version: 2,
                canvas: canvas,
                elements: elements,
                device: fullState.device,
            },
        };

        const $btn = $('#btn-save-banner');
        const originalHtml = $btn.html();

        this.isSaving = true;
        $btn.prop('disabled', true).html(`
            <svg class="animate-spin -ml-0.5 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Saving Design...</span>
        `);

        const self = this;

        $.ajax({
            url: serverData.saveUrl,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': payload._token,
                'Accept': 'application/json',
            },
            success: function (res) {
                self.state.setClean();
                self.updateAutosaveBadge(true);

                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Design Saved!',
                        text: (res && res.message) || 'Banner design and elements stored successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to save banner design. Please check your network connection.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: errorMsg,
                    });
                } else {
                    alert(errorMsg);
                }
            },
            complete: function () {
                self.isSaving = false;
                $btn.prop('disabled', false).html(originalHtml);
                if (window.renderLucideIcons) {
                    window.renderLucideIcons();
                }
            },
        });
    }

    /**
     * Update the auto-save indicator badge in the top bar
     */
    updateAutosaveBadge(isSaved) {
        const $indicator = $('#autosave-indicator');
        if (!$indicator.length) return;

        if (isSaved) {
            $indicator.html(`
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Saved</span>
            `).removeClass('text-amber-400').addClass('text-emerald-400');
        } else {
            $indicator.html(`
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Unsaved changes</span>
            `).removeClass('text-emerald-400').addClass('text-amber-400');
        }
    }

    /**
     * Observe dirty state changes to toggle the indicator
     */
    bindDirtyStateIndicator() {
        const self = this;

        this.state.on('history:changed', () => {
            if (self.state.state.isDirty) {
                self.updateAutosaveBadge(false);
            }
        });
    }

    /**
     * Warn user if leaving page with unsaved modifications
     */
    bindBeforeUnload() {
        const self = this;

        window.addEventListener('beforeunload', function (e) {
            if (self.state.state.isDirty) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes in your banner builder. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    }
}

// Instantiate and expose globally
const BannerBuilderSaver = new BannerBuilderSaverEngine();
window.BannerBuilderSaver = BannerBuilderSaver;

export default BannerBuilderSaver;
