import $ from 'jquery';

/**
 * Grocery Admin - Layout & Navigation Controller
 * Handles mobile sidebar drawer, fullscreen toggle, submenus, and keyboard shortcuts
 */

window.Admin = window.Admin || {};

/**
 * Mobile Sidebar Open
 */
window.Admin.openMobileSidebar = function () {
    const drawer = document.getElementById('mobile-sidebar-drawer');
    const backdrop = document.getElementById('mobile-sidebar-backdrop');
    const content = document.getElementById('mobile-sidebar-content');
    if (!drawer) return;

    drawer.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    setTimeout(() => {
        if (backdrop) {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
        }
        if (content) {
            content.classList.remove('-translate-x-full');
            content.classList.add('translate-x-0');
        }
        window.Admin.refreshIcons();
    }, 10);
};

/**
 * Mobile Sidebar Close
 */
window.Admin.closeMobileSidebar = function () {
    const drawer = document.getElementById('mobile-sidebar-drawer');
    const backdrop = document.getElementById('mobile-sidebar-backdrop');
    const content = document.getElementById('mobile-sidebar-content');
    if (!drawer) return;

    if (backdrop) {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
    }
    if (content) {
        content.classList.remove('translate-x-0');
        content.classList.add('-translate-x-full');
    }

    setTimeout(() => {
        drawer.classList.add('hidden');
        if (document.querySelectorAll('[role="dialog"]:not(.hidden)').length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }, 300);
};

/**
 * Fullscreen Toggle
 */
window.Admin.toggleFullscreen = function () {
    const doc = document;
    const docEl = doc.documentElement;
    const requestFs = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
    const exitFs = doc.exitFullscreen || doc.webkitExitFullscreen || doc.mozCancelFullScreen || doc.msExitFullscreen;
    const isFullscreen = doc.fullscreenElement || doc.webkitFullscreenElement || doc.mozFullScreenElement || doc.msFullscreenElement;

    if (!isFullscreen) {
        if (requestFs) {
            requestFs.call(docEl).catch(() => {});
        }
        $('#toggle-fullscreen-btn [data-lucide]').attr('data-lucide', 'minimize');
    } else {
        if (exitFs) {
            exitFs.call(doc).catch(() => {});
        }
        $('#toggle-fullscreen-btn [data-lucide]').attr('data-lucide', 'maximize');
    }
    window.Admin.refreshIcons();
};

// Event delegation bindings
$(function () {
    // Open Mobile Sidebar
    $(document).on('click', '#open-mobile-sidebar-btn', function (e) {
        e.preventDefault();
        window.Admin.openMobileSidebar();
    });

    // Close Mobile Sidebar
    $(document).on('click', '#close-mobile-sidebar-btn, #mobile-sidebar-backdrop', function (e) {
        e.preventDefault();
        window.Admin.closeMobileSidebar();
    });

    // Handle ESC key for mobile drawer
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            const $drawer = $('#mobile-sidebar-drawer');
            if ($drawer.length && !$drawer.hasClass('hidden')) {
                window.Admin.closeMobileSidebar();
            }
        }
    });

    // Fullscreen Toggle
    $(document).on('click', '#toggle-fullscreen-btn', function (e) {
        e.preventDefault();
        window.Admin.toggleFullscreen();
    });

    // Collapsible Sidebar Submenus
    $(document).on('click', '.sidebar-collapsible-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $submenu = $btn.next('.sidebar-submenu');
        const $chevron = $btn.find('.sidebar-chevron');

        $submenu.slideToggle(200);
        $chevron.toggleClass('rotate-90');
    });

    // Global Search focus on (Ctrl + K / Cmd + K)
    $(document).on('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('global-topbar-search');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });
});

export default window.Admin;
