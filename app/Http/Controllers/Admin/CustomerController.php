<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\StoreCustomerRequest;
use App\Http\Requests\Admin\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a paginated listing of customers with search and metrics.
     */
    public function index(Request $request): View
    {
        $query = Customer::with(['addresses' => function ($q) {
            $q->where('is_default', true);
        }]);

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'spent_desc':
                $query->orderBy('total_spent', 'desc');
                break;
            case 'orders_desc':
                $query->orderBy('total_orders_count', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $customers = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $totalCust = Customer::count();
        $totalSpent = (float) Customer::sum('total_spent') ?: 0;
        $stats = [
            'total' => $totalCust,
            'active' => Customer::where('status', Customer::STATUS_ACTIVE)->count(),
            'total_spent' => $totalSpent,
            'avg_ltv' => $totalCust > 0 ? round($totalSpent / $totalCust, 2) : 0,
        ];

        return view('admin.customers.index', [
            'title' => 'Customers - ' . config('admin.name', 'Grocery Admin'),
            'customers' => $customers,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('admin.customers.create', [
            'title' => 'Add Customer - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['address_line1'])) {
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'type' => 'home',
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'] ?? 'City',
                'state' => $validated['state'] ?? 'State',
                'postal_code' => $validated['postal_code'] ?? '00000',
                'country' => $validated['country'] ?? 'US',
                'is_default' => true,
            ]);
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('toast_success', "Customer '{$customer->name}' profile created successfully.");
    }

    /**
     * Display the specified customer profile and order history.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['addresses', 'orders' => function ($q) {
            $q->with('items')->latest();
        }]);

        $ordersCount = $customer->orders->count();
        $totalSpent = (float) $customer->orders->where('payment_status', 'paid')->sum('total');
        $aov = $ordersCount > 0 ? round($totalSpent / $ordersCount, 2) : 0;

        return view('admin.customers.show', [
            'title' => $customer->name . ' - Customer Profile',
            'customer' => $customer,
            'ordersCount' => $ordersCount,
            'totalSpent' => $totalSpent,
            'aov' => $aov,
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $defaultAddress = $customer->addresses()->where('is_default', true)->first();

        return view('admin.customers.edit', [
            'title' => 'Edit Customer - ' . $customer->name,
            'customer' => $customer,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validated();

        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['address_line1'])) {
            $defaultAddress = $customer->addresses()->where('is_default', true)->first();

            if ($defaultAddress) {
                $defaultAddress->update([
                    'address_line1' => $validated['address_line1'],
                    'address_line2' => $validated['address_line2'] ?? null,
                    'city' => $validated['city'] ?? 'City',
                    'state' => $validated['state'] ?? 'State',
                    'postal_code' => $validated['postal_code'] ?? '00000',
                    'country' => $validated['country'] ?? 'US',
                ]);
            } else {
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'type' => 'home',
                    'address_line1' => $validated['address_line1'],
                    'address_line2' => $validated['address_line2'] ?? null,
                    'city' => $validated['city'] ?? 'City',
                    'state' => $validated['state'] ?? 'State',
                    'postal_code' => $validated['postal_code'] ?? '00000',
                    'country' => $validated['country'] ?? 'US',
                    'is_default' => true,
                ]);
            }
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('toast_success', "Customer '{$customer->name}' profile updated successfully.");
    }

    /**
     * Remove the specified customer from storage with order history protection.
     */
    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        $ordersCount = $customer->orders()->count();

        if ($ordersCount > 0) {
            $msg = "Cannot delete customer '{$customer->name}'. They have {$ordersCount} recorded order invoice(s). To restrict access, please set status to Inactive or Blocked instead.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $name = $customer->name;
        $customer->addresses()->delete();
        $customer->delete();

        $successMsg = "Customer '{$name}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.customers.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle between active and inactive.
     */
    public function toggleStatus(Customer $customer): JsonResponse
    {
        $customer->status = ($customer->status === Customer::STATUS_ACTIVE)
            ? Customer::STATUS_INACTIVE
            : Customer::STATUS_ACTIVE;

        $customer->save();

        return response()->json([
            'success' => true,
            'status' => $customer->status,
            'is_active' => $customer->status === Customer::STATUS_ACTIVE,
            'message' => "Customer '{$customer->name}' status changed to " . ucfirst($customer->status) . '.',
        ]);
    }
}
