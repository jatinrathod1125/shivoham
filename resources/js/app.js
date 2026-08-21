import jQuery from 'jquery';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import { createIcons, icons } from 'lucide';

// Make core libraries globally available
window.$ = window.jQuery = jQuery;
window.Swal = Swal;
window.Chart = Chart;
window.lucide = { createIcons, icons };

// Set up CSRF token for jQuery AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Safe icon renderer
export function renderLucideIcons() {
    try {
        if (typeof createIcons === 'function' && icons && Object.keys(icons).length > 0) {
            createIcons({ icons });
        }
    } catch (e) {
        console.error('Error rendering Lucide icons:', e);
    }
}

// Attach to window for global access
window.renderLucideIcons = renderLucideIcons;

// Import Admin utilities, layout, and dashboard scripts
import './admin/common.js';
import './admin/layout.js';
import './admin/dashboard.js';

// Render icons at multiple lifecycle hooks to guarantee rendering
renderLucideIcons();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderLucideIcons);
} else {
    renderLucideIcons();
}

$(function () {
    renderLucideIcons();
});

window.addEventListener('load', renderLucideIcons);
