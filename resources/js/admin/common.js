import Swal from 'sweetalert2';
import $ from 'jquery';

/**
 * Grocery Admin - Common UI & Interaction Utilities (jQuery Powered)
 * Standardized across all admin modules
 */

let Toast = null;
try {
    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            $(toast).on('mouseenter', Swal.stopTimer);
            $(toast).on('mouseleave', Swal.resumeTimer);
        }
    });
} catch (e) {
    console.error('Error initializing SweetAlert2 toast mixin:', e);
}

window.Admin = window.Admin || {};

/**
 * Display a floating Toast notification
 */
window.Admin.toast = function ({ type = 'success', title = '', message = '' }) {
    if (Toast) {
        Toast.fire({
            icon: type,
            title: title ? `${title}: ${message}` : message
        });
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            icon: type,
            title: title ? `${title}: ${message}` : message
        });
    } else {
        alert((title ? `${title}: ` : '') + message);
    }
};

/**
 * Confirmation dialog for destructive / critical actions
 */
window.Admin.confirm = function ({
    title = 'Are you sure?',
    text = 'This action cannot be undone.',
    confirmButtonText = 'Yes, proceed',
    cancelButtonText = 'Cancel',
    icon = 'warning',
    confirmButtonColor = '#dc2626',
    onConfirm = () => {}
}) {
    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-slate-100',
                confirmButton: 'rounded-lg px-4 py-2 text-sm font-medium',
                cancelButton: 'rounded-lg px-4 py-2 text-sm font-medium'
            }
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
            return result.isConfirmed;
        });
    } else {
        if (window.confirm(`${title}\n${text}`)) {
            if (typeof onConfirm === 'function') onConfirm();
            return Promise.resolve(true);
        }
        return Promise.resolve(false);
    }
};

/**
 * Modal Controller with jQuery
 */
window.Admin.modal = {
    open: function (id) {
        const $modal = $('#' + id);
        if (!$modal.length) return;

        $('body').addClass('overflow-hidden');
        $modal.removeClass('hidden');

        setTimeout(() => {
            $modal.find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
            $modal.find('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
    },

    close: function (id) {
        const $modal = $('#' + id);
        if (!$modal.length) return;

        $modal.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
        $modal.find('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');

        setTimeout(() => {
            $modal.addClass('hidden');
            if ($('[role="dialog"]:not(.hidden)').length === 0) {
                $('body').removeClass('overflow-hidden');
            }
        }, 250);
    }
};

/**
 * Dropdown Controller with jQuery
 */
window.Admin.toggleDropdown = function (triggerEl, event) {
    if (event) event.stopPropagation();
    const $container = $(triggerEl).closest('.dropdown-container');
    if (!$container.length) return;
    const $menu = $container.find('.dropdown-menu');
    if (!$menu.length) return;

    const isHidden = $menu.hasClass('hidden');

    // Close all open dropdowns first
    $('.dropdown-menu').addClass('hidden opacity-0 scale-95').removeClass('opacity-100 scale-100');

    if (isHidden) {
        $menu.removeClass('hidden');
        setTimeout(() => {
            $menu.removeClass('opacity-0 scale-95').addClass('opacity-100 scale-100');
        }, 10);
    }
};

/**
 * Toggle button loading state with jQuery
 */
window.Admin.btnLoading = function (btn, loading = true, loadingText = 'Processing...') {
    const $btn = $(btn);
    if (loading) {
        $btn.data('original-content', $btn.html());
        $btn.prop('disabled', true);
        $btn.html(`
            <span class="inline-flex items-center gap-2">
                <span class="animate-spin rounded-full border-2 border-current border-t-transparent w-4 h-4"></span>
                <span>${loadingText}</span>
            </span>
        `);
    } else {
        $btn.prop('disabled', false);
        $btn.html($btn.data('original-content') || $btn.html());
    }
};

/**
 * Re-render Lucide icons dynamically
 */
window.Admin.refreshIcons = function () {
    if (typeof window.renderLucideIcons === 'function') {
        window.renderLucideIcons();
    } else if (typeof window.lucide !== 'undefined' && typeof window.lucide.createIcons === 'function' && window.lucide.icons) {
        window.lucide.createIcons({ icons: window.lucide.icons });
    }
};

// Global click outside to close dropdowns with jQuery
$(document).on('click', function (e) {
    if (!$(e.target).closest('.dropdown-container').length) {
        $('.dropdown-menu').addClass('hidden opacity-0 scale-95').removeClass('opacity-100 scale-100');
    }
});

// Handle ESC key for modals & dropdowns with jQuery
$(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
        const $openModals = $('[role="dialog"]:not(.hidden)');
        const $lastModal = $openModals.last();
        if ($lastModal.length && $lastModal.attr('id')) {
            window.Admin.modal.close($lastModal.attr('id'));
        }
        $('.dropdown-menu').addClass('hidden opacity-0 scale-95').removeClass('opacity-100 scale-100');
    }
});

// File upload preview handler with jQuery
$(document).on('change', '.file-upload-input', function () {
    const input = this;
    const $wrapper = $(this).closest('.file-upload-wrapper');
    const $preview = $wrapper.find('.file-preview');
    const $previewImg = $wrapper.find('.preview-img');
    const $defaultIcon = $wrapper.find('.default-icon');
    const $filename = $wrapper.find('.selected-filename');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        $filename.text(file.name);

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $previewImg.attr('src', e.target.result);
                $preview.removeClass('hidden');
                $defaultIcon.addClass('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
});

export default window.Admin;
