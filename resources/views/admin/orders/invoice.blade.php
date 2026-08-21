<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->order_number }} - {{ $store['name'] }}</title>

    <!-- Tailwind / Vite Assets -->
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 12px !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 p-4 sm:p-8 min-h-screen">
    <!-- Top Action Bar (Hidden in Print) -->
    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print">
        <a
            href="{{ route('admin.orders.show', $order) }}"
            class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-2xs"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Order</span>
        </a>

        <div class="flex items-center gap-2">
            <button
                onclick="window.print()"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-sm cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Invoice</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-3xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 print-container">
        <!-- Invoice Header -->
        <div class="flex items-start justify-between border-b border-slate-200 pb-6 mb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-black text-sm flex items-center justify-center">
                        F
                    </div>
                    <span class="font-black text-lg tracking-tight text-slate-900">{{ $store['name'] }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">{{ $store['address'] }}</p>
                <p class="text-xs text-slate-500">{{ $store['email'] }} • {{ $store['phone'] }}</p>
            </div>

            <div class="text-right">
                <h1 class="text-xl font-black text-slate-900 tracking-tight">INVOICE</h1>
                <p class="text-xs font-mono font-bold text-emerald-700 mt-1">#{{ $order->order_number }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Date: {{ $order->created_at->format('M d, Y') }}</p>
                <div class="mt-2 inline-block px-2.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    Payment: {{ $order->payment_status }}
                </div>
            </div>
        </div>

        <!-- Billed & Shipped To -->
        <div class="grid grid-cols-2 gap-6 border-b border-slate-200 pb-6 mb-6 text-xs">
            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-1.5">Billed To</h3>
                <p class="font-semibold text-slate-800">{{ $order->customer_name }}</p>
                <p class="text-slate-500">{{ $order->customer_email }}</p>
                <p class="text-slate-500">{{ $order->customer_phone }}</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-[11px] mb-1.5">Shipping Destination</h3>
                <p class="text-slate-700 leading-relaxed">{{ $order->shipping_address ?: 'Same as billing address' }}</p>
                <p class="text-slate-500 mt-1">Payment Method: {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="w-full text-left text-xs mb-6">
            <thead class="border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="py-2.5">Item Description</th>
                    <th class="py-2.5 text-center">Unit Price</th>
                    <th class="py-2.5 text-center">Qty</th>
                    <th class="py-2.5 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3">
                            <span class="font-semibold text-slate-900 block">{{ $item->product_name }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $item->sku }}</span>
                        </td>
                        <td class="py-3 text-center">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="py-3 text-right font-bold text-slate-900">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Calculation Summary -->
        <div class="flex justify-end border-t border-slate-200 pt-4">
            <div class="w-64 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900">${{ number_format($order->subtotal, 2) }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Discount</span>
                        <span class="font-semibold">-${{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-slate-600">
                    <span>Shipping Fee</span>
                    <span class="font-semibold text-slate-900">
                        {{ $order->shipping_fee > 0 ? '$' . number_format($order->shipping_fee, 2) : '$0.00' }}
                    </span>
                </div>

                <div class="flex justify-between text-slate-600">
                    <span>Tax (8%)</span>
                    <span class="font-semibold text-slate-900">${{ number_format($order->tax, 2) }}</span>
                </div>

                <div class="border-t border-slate-300 pt-2 flex justify-between font-black text-sm text-slate-900">
                    <span>Total Amount</span>
                    <span class="text-emerald-700 text-base">${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer / Thank You Note -->
        <div class="border-t border-slate-200 mt-8 pt-6 text-center text-slate-500 text-[11px] space-y-1">
            <p class="font-bold text-slate-700">Thank you for shopping with {{ $store['name'] }}!</p>
            <p>If you have any questions about this invoice, please contact our support team at {{ $store['email'] }}.</p>
        </div>
    </div>
</body>
</html>
