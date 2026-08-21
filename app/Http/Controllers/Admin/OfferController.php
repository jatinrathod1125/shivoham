<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Offer\StoreOfferRequest;
use App\Http\Requests\Admin\Offer\UpdateOfferRequest;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfferController extends Controller
{
    /**
     * Display a paginated listing of promotional offers with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Offer::query();

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('badge_text', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Discount Type Filter
        if ($discountType = $request->input('discount_type')) {
            $query->where('discount_type', $discountType);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'discount_desc':
                $query->orderBy('discount_value', 'desc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $offers = $query->paginate(12)->withQueryString();

        $now = now();
        $stats = [
            'total' => Offer::count(),
            'active' => Offer::where('is_active', true)
                ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
                ->count(),
            'expired' => Offer::whereNotNull('expires_at')->where('expires_at', '<', $now)->count(),
            'upcoming' => Offer::whereNotNull('starts_at')->where('starts_at', '>', $now)->count(),
        ];

        return view('admin.offers.index', [
            'title' => 'Offers & Deals - ' . config('admin.name', 'Grocery Admin'),
            'offers' => $offers,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new offer.
     */
    public function create(): View
    {
        return view('admin.offers.create', [
            'title' => 'Add Offer - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $baseSlug = $validated['slug'];
        $count = 1;
        while (Offer::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle banner image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers', 'public');
            $validated['image'] = Storage::url($path);
        }

        $offer = Offer::create($validated);

        return redirect()->route('admin.offers.index')
            ->with('toast_success', "Promotional offer '{$offer->title}' created successfully.");
    }

    /**
     * Show the form for editing the specified offer.
     */
    public function edit(Offer $offer): View
    {
        return view('admin.offers.edit', [
            'title' => 'Edit Offer - ' . $offer->title,
            'offer' => $offer,
        ]);
    }

    /**
     * Update the specified offer in storage.
     */
    public function update(UpdateOfferRequest $request, Offer $offer): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $baseSlug = $validated['slug'];
        $count = 1;
        while (Offer::where('slug', $validated['slug'])->where('id', '!=', $offer->id)->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        // Handle banner image replacement
        if ($request->hasFile('image')) {
            if ($offer->image && Str::startsWith($offer->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $offer->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('offers', 'public');
            $validated['image'] = Storage::url($path);
        }

        $offer->update($validated);

        return redirect()->route('admin.offers.index')
            ->with('toast_success', "Offer '{$offer->title}' updated successfully.");
    }

    /**
     * Remove the specified offer from storage.
     */
    public function destroy(Offer $offer): RedirectResponse|JsonResponse
    {
        $title = $offer->title;

        if ($offer->image && Str::startsWith($offer->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $offer->image);
            Storage::disk('public')->delete($oldPath);
        }

        $offer->delete();

        $successMsg = "Offer '{$title}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.offers.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Offer $offer): JsonResponse
    {
        $offer->is_active = !$offer->is_active;
        $offer->save();

        return response()->json([
            'success' => true,
            'is_active' => $offer->is_active,
            'message' => "Offer '{$offer->title}' status changed to " . ($offer->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }
}
