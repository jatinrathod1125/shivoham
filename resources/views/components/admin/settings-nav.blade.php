@props(['active' => 'general'])

<div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200">
    <a
        href="{{ route('admin.settings.index') }}"
        class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ $active === 'general' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80' }}"
    >
        <i data-lucide="store" class="w-4 h-4 {{ $active === 'general' ? 'text-white' : 'text-slate-400' }}"></i>
        <span>Store Profile</span>
    </a>

    <a
        href="{{ route('admin.settings.localization') }}"
        class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ $active === 'localization' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80' }}"
    >
        <i data-lucide="globe" class="w-4 h-4 {{ $active === 'localization' ? 'text-white' : 'text-slate-400' }}"></i>
        <span>Currency</span>
    </a>

    <a
        href="{{ route('admin.settings.hours') }}"
        class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ $active === 'hours' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80' }}"
    >
        <i data-lucide="clock" class="w-4 h-4 {{ $active === 'hours' ? 'text-white' : 'text-slate-400' }}"></i>
        <span>Operating Hours</span>
    </a>

    <a
        href="{{ route('admin.settings.payments') }}"
        class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ $active === 'payments' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80' }}"
    >
        <i data-lucide="credit-card" class="w-4 h-4 {{ $active === 'payments' ? 'text-white' : 'text-slate-400' }}"></i>
        <span>Payment Gateways</span>
    </a>

    <a
        href="{{ route('admin.settings.inventory') }}"
        class="px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ $active === 'inventory' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-200/80' }}"
    >
        <i data-lucide="boxes" class="w-4 h-4 {{ $active === 'inventory' ? 'text-white' : 'text-slate-400' }}"></i>
        <span>Stock & Alerts</span>
    </a>
</div>
