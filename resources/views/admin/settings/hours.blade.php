@extends('layouts.admin')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header
        title="System Settings"
        subtitle="Manage store identity, currency, taxation rules, delivery zones, and payment gateways."
        :breadcrumbs="[
            ['title' => 'Settings', 'url' => route('admin.settings.index')],
            ['title' => 'Operating Hours', 'url' => '']
        ]"
    />

    <!-- Settings Navigation Tabs -->
    <x-admin.settings-nav active="hours" />

    <!-- Operating Hours Form -->
    <form method="POST" action="{{ route('admin.settings.update-hours') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Weekly Schedule -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Master Status & Announcement -->
                <x-admin.card title="Store Service Availability Status" subtitle="Global master switch controlling customer checkout availability" icon="power">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="p-3.5 rounded-xl border {{ $settings['store_status'] === 'online' ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/50' }} flex items-start gap-3 cursor-pointer hover:border-emerald-400 transition-colors">
                                <input
                                    type="radio"
                                    name="store_status"
                                    value="online"
                                    {{ old('store_status', $settings['store_status']) === 'online' ? 'checked' : '' }}
                                    class="mt-1 text-emerald-600 focus:ring-emerald-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Store Online
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Accepting orders normally</span>
                                </div>
                            </label>

                            <label class="p-3.5 rounded-xl border {{ $settings['store_status'] === 'maintenance' ? 'border-amber-500 bg-amber-50/40' : 'border-slate-200 bg-slate-50/50' }} flex items-start gap-3 cursor-pointer hover:border-amber-400 transition-colors">
                                <input
                                    type="radio"
                                    name="store_status"
                                    value="maintenance"
                                    {{ old('store_status', $settings['store_status']) === 'maintenance' ? 'checked' : '' }}
                                    class="mt-1 text-amber-600 focus:ring-amber-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Maintenance
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Show maintenance banner</span>
                                </div>
                            </label>

                            <label class="p-3.5 rounded-xl border {{ $settings['store_status'] === 'closed' ? 'border-rose-500 bg-rose-50/40' : 'border-slate-200 bg-slate-50/50' }} flex items-start gap-3 cursor-pointer hover:border-rose-400 transition-colors">
                                <input
                                    type="radio"
                                    name="store_status"
                                    value="closed"
                                    {{ old('store_status', $settings['store_status']) === 'closed' ? 'checked' : '' }}
                                    class="mt-1 text-rose-600 focus:ring-rose-500"
                                />
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Store Closed
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Temporarily pause checkout</span>
                                </div>
                            </label>
                        </div>

                        <div>
                            <x-form.input
                                name="notice_message"
                                label="Storefront Announcement / Holiday Notice"
                                placeholder="e.g. Fresh express grocery delivery active 7 days a week!"
                                :value="old('notice_message', $settings['notice_message'])"
                                helper="Displayed at the top of the storefront header."
                            />
                        </div>
                    </div>
                </x-admin.card>

                <!-- Weekly Operating Hours Schedule Table -->
                <x-admin.card title="Weekly Operating Schedule" subtitle="Daily delivery and customer service operational windows" icon="calendar">
                    <div class="overflow-x-auto -mx-5 -my-5">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-y border-slate-100">
                                <tr>
                                    <th class="px-5 py-3">Day of Week</th>
                                    <th class="px-5 py-3">Opening Time</th>
                                    <th class="px-5 py-3">Closing Time</th>
                                    <th class="px-5 py-3 text-center">Closed All Day</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $days = [
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday'
                                    ];
                                @endphp

                                @foreach($days as $key => $dayName)
                                    @php
                                        $dayData = $settings['schedule'][$key] ?? ['open' => '07:00', 'close' => '21:00', 'is_closed' => false];
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-5 py-3 font-bold text-slate-900">
                                            {{ $dayName }}
                                        </td>
                                        <td class="px-5 py-3">
                                            <input
                                                type="time"
                                                name="schedule[{{ $key }}][open]"
                                                value="{{ old("schedule.{$key}.open", $dayData['open'] ?? '07:00') }}"
                                                class="px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500"
                                            />
                                        </td>
                                        <td class="px-5 py-3">
                                            <input
                                                type="time"
                                                name="schedule[{{ $key }}][close]"
                                                value="{{ old("schedule.{$key}.close", $dayData['close'] ?? '21:00') }}"
                                                class="px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-hidden focus:border-emerald-500"
                                            />
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    name="schedule[{{ $key }}][is_closed]"
                                                    value="1"
                                                    {{ !empty($dayData['is_closed']) ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300"
                                                />
                                                <span class="text-xs text-slate-500">Closed</span>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            </div>

            <!-- Right Column: Save -->
            <div class="lg:col-span-4 space-y-6">
                <x-admin.card title="Save Changes" icon="check-circle-2">
                    <p class="text-xs text-slate-500 mb-4">Operating schedule determines active delivery slots and customer order acceptance hours.</p>

                    <x-admin.button
                        type="submit"
                        variant="primary"
                        size="md"
                        icon="check"
                        class="w-full"
                    >
                        Save Operating Schedule
                    </x-admin.button>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
