@extends('layouts.admin')

@section('content')
    <x-admin.page-header
        title="Admin Layout & Design System"
        subtitle="Complete responsive master shell with dark sidebar, topbar, and design components"
        :breadcrumbs="['Admin' => '#', 'Layout' => '#']"
    >
        <x-admin.button variant="secondary" icon="download" onclick="Admin.toast({ type: 'info', message: 'Export initiated' })">Export Data</x-admin.button>
        <x-admin.button variant="primary" icon="plus" onclick="Admin.modal.open('test-modal')">Open Modal</x-admin.button>
    </x-admin.page-header>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-admin.stat-card
            title="Total Orders"
            value="1,482"
            icon="shopping-bag"
            iconColor="emerald"
            trend="+14.2%"
            :trendUp="true"
        />
        <x-admin.stat-card
            title="Total Sales"
            value="$38,450.00"
            icon="dollar-sign"
            iconColor="blue"
            trend="+8.7%"
            :trendUp="true"
        />
        <x-admin.stat-card
            title="Active Customers"
            value="3,290"
            icon="users"
            iconColor="purple"
            trend="-1.5%"
            :trendUp="false"
        />
        <x-admin.stat-card
            title="Low Stock Items"
            value="18"
            icon="alert-triangle"
            iconColor="amber"
            badge="18 Items"
        />
    </div>

    <!-- Main Components Showcase -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Recent Orders Overview" subtitle="Preview of table and status badges">
                <x-admin.table :headers="['Order ID', 'Customer', 'Items', 'Total', 'Status', 'Actions']">
                    <tr>
                        <td class="px-5 py-3.5 font-semibold text-slate-900">#ORD-9021</td>
                        <td class="px-5 py-3.5 text-xs text-slate-700">Eleanor Vance</td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">6 items</td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-slate-900">$74.20</td>
                        <td class="px-5 py-3.5">
                            <x-admin.badge variant="success" :dot="true">Delivered</x-admin.badge>
                        </td>
                        <td class="px-5 py-3.5">
                            <x-admin.button size="xs" variant="outline">View</x-admin.button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3.5 font-semibold text-slate-900">#ORD-9022</td>
                        <td class="px-5 py-3.5 text-xs text-slate-700">Marcus Chen</td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">3 items</td>
                        <td class="px-5 py-3.5 text-xs font-semibold text-slate-900">$32.50</td>
                        <td class="px-5 py-3.5">
                            <x-admin.badge variant="warning" :dot="true">Processing</x-admin.badge>
                        </td>
                        <td class="px-5 py-3.5">
                            <x-admin.button size="xs" variant="outline">View</x-admin.button>
                        </td>
                    </tr>
                </x-admin.table>
            </x-admin.card>

            <x-admin.card title="Form Controls Preview" subtitle="Inputs, Selects, Toggles, and Uploads">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="preview_name" label="Grocery Item Name" placeholder="e.g. Organic Strawberries" icon="tag" />
                    <x-form.select name="preview_category" label="Category" :options="['fruits' => 'Fresh Fruits', 'veg' => 'Organic Vegetables', 'dairy' => 'Dairy & Milk']" />
                    <x-form.switch name="preview_status" label="In Stock & Active" description="Visible to customers in the catalog" :checked="true" />
                    <div class="flex items-center gap-3 pt-4">
                        <x-admin.button variant="primary" onclick="Admin.toast({ type: 'success', message: 'Form sample submitted' })">Save Item</x-admin.button>
                        <x-admin.button variant="secondary">Reset</x-admin.button>
                    </div>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="Interactive Controls">
                <div class="space-y-3">
                    <x-admin.button variant="primary" class="w-full" onclick="Admin.modal.open('test-modal')">Open Modal Dialog</x-admin.button>
                    <x-admin.button variant="secondary" class="w-full" onclick="Admin.toast({ type: 'success', title: 'Order Created', message: 'New order #ORD-9843 placed!' })">Trigger Toast Alert</x-admin.button>
                    <x-admin.button variant="danger" class="w-full" onclick="Admin.confirm({ title: 'Delete Item?', text: 'Are you sure you want to remove this item?', onConfirm: () => Admin.toast({ type: 'info', message: 'Item deleted.' }) })">Trigger Confirm Dialog</x-admin.button>
                </div>
            </x-admin.card>

            <x-admin.card title="Quick Empty State">
                <x-admin.empty-state
                    icon="package"
                    title="No Items Out of Stock"
                    description="Your grocery inventory is currently healthy."
                />
            </x-admin.card>
        </div>
    </div>

    <!-- Demo Modal -->
    <x-admin.modal id="test-modal" title="Demo Modal Window" size="md">
        <p class="text-sm text-slate-600 mb-4">
            This modal is fully integrated with our dark sidebar, topbar, and keyboard navigation.
        </p>
        <x-form.input name="modal_demo_text" label="Sample Tag" placeholder="Enter tag name" />

        <x-slot:footer>
            <x-admin.button variant="secondary" onclick="Admin.modal.close('test-modal')">Cancel</x-admin.button>
            <x-admin.button variant="primary" onclick="Admin.modal.close('test-modal'); Admin.toast({ type: 'success', message: 'Saved successfully!' })">Save Changes</x-admin.button>
        </x-slot:footer>
    </x-admin.modal>
@endsection
