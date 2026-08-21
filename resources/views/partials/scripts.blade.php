<script>
    // Trigger session toast if flash session exists
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Admin && typeof window.Admin.refreshIcons === 'function') {
            window.Admin.refreshIcons();
        }

        @if(session('toast_success'))
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'success', title: 'Success', message: '{{ session('toast_success') }}' });
            }
        @endif
        @if(session('toast_error'))
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'error', title: 'Error', message: '{{ session('toast_error') }}' });
            }
        @endif
        @if(session('toast_warning'))
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'warning', title: 'Warning', message: '{{ session('toast_warning') }}' });
            }
        @endif
        @if(session('toast_info'))
            if (window.Admin && window.Admin.toast) {
                Admin.toast({ type: 'info', title: 'Info', message: '{{ session('toast_info') }}' });
            }
        @endif
    });
</script>
