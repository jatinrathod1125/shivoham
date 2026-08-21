<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a paginated listing of grocery customer orders with multi-facet filters.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'items.product']);

        // Search Filter (Order Number, Customer Name, Email, Phone)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Status Tab Filter
        if ($status = $request->input('status')) {
            if (in_array($status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])) {
                $query->where('status', $status);
            }
        }

        // Payment Status Filter
        if ($paymentStatus = $request->input('payment_status')) {
            if (in_array($paymentStatus, [Order::PAYMENT_PAID, Order::PAYMENT_UNPAID, Order::PAYMENT_REFUNDED])) {
                $query->where('payment_status', $paymentStatus);
            }
        }

        // Payment Method Filter
        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $orders = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'delivered' => Order::where('status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'total_revenue' => (float) Order::where('payment_status', Order::PAYMENT_PAID)->sum('total'),
        ];

        return view('admin.orders.index', [
            'title' => 'Orders - ' . config('admin.name', 'Grocery Admin'),
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Display detailed order view with line items and customer information.
     */
    public function show(Order $order): View
    {
        $order->load(['customer', 'items.product']);

        return view('admin.orders.show', [
            'title' => 'Order #' . $order->order_number . ' - ' . config('admin.name', 'Grocery Admin'),
            'order' => $order,
        ]);
    }

    /**
     * Display print-optimized grocery receipt and invoice.
     */
    public function invoice(Order $order): View
    {
        $order->load(['customer', 'items.product']);

        $storeInfo = [
            'name' => Setting::get('store_name', 'Fresh Groceries Hub'),
            'tagline' => Setting::get('store_tagline', 'Your Everyday Organic Grocery Partner'),
            'email' => Setting::get('store_email', 'support@grocery.local'),
            'phone' => Setting::get('store_phone', '+1 (800) 555-GROCERY'),
            'address' => Setting::get('store_address', '100 Market Square, Suite 400, Chicago, IL 60601'),
        ];

        return view('admin.orders.invoice', [
            'title' => 'Invoice - #' . $order->order_number,
            'order' => $order,
            'store' => $storeInfo,
        ]);
    }

    /**
     * Quick AJAX order fulfillment status update.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,processing,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $request->input('status');
        $order->status = $newStatus;

        if ($request->filled('notes')) {
            $order->notes = $request->input('notes');
        }

        if ($newStatus === Order::STATUS_DELIVERED && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'message' => "Order #{$order->order_number} status updated to " . ucfirst($order->status) . '.',
        ]);
    }

    /**
     * Quick AJAX order payment status update.
     */
    public function updatePaymentStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'payment_status' => ['required', 'in:paid,unpaid,refunded'],
        ]);

        $order->payment_status = $request->input('payment_status');
        $order->save();

        return response()->json([
            'success' => true,
            'payment_status' => $order->payment_status,
            'message' => "Order #{$order->order_number} payment status marked as " . ucfirst($order->payment_status) . '.',
        ]);
    }
}
