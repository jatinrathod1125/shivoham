<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    /**
     * Display a paginated listing of coupon codes with metrics and filters.
     */
    public function index(Request $request): View
    {
        $query = Coupon::query();

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Discount Type Filter
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'usage_desc':
                $query->orderBy('usage_count', 'desc');
                break;
            case 'discount_desc':
                $query->orderBy('value', 'desc');
                break;
            case 'code_asc':
                $query->orderBy('code', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $coupons = $query->paginate(15)->withQueryString();

        $now = now();
        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
                ->count(),
            'total_redemptions' => (int) Coupon::sum('usage_count'),
            'expired' => Coupon::whereNotNull('expires_at')->where('expires_at', '<', $now)->count(),
        ];

        return view('admin.coupons.index', [
            'title' => 'Coupons - ' . config('admin.name', 'Grocery Admin'),
            'coupons' => $coupons,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create(): View
    {
        return view('admin.coupons.create', [
            'title' => 'Add Coupon - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created coupon in storage.
     */
    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $coupon = Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('toast_success', "Coupon code '{$coupon->code}' created successfully.");
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', [
            'title' => 'Edit Coupon - ' . $coupon->code,
            'coupon' => $coupon,
        ]);
    }

    /**
     * Update the specified coupon in storage.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validated();

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('toast_success', "Coupon '{$coupon->code}' updated successfully.");
    }

    /**
     * Remove the specified coupon from storage.
     */
    public function destroy(Coupon $coupon): RedirectResponse|JsonResponse
    {
        $code = $coupon->code;
        $coupon->delete();

        $successMsg = "Coupon '{$code}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.coupons.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Coupon $coupon): JsonResponse
    {
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => "Coupon '{$coupon->code}' status changed to " . ($coupon->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }
}
