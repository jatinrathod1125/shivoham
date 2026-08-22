<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerBuilderController extends Controller
{
    /**
     * Display the visual Banner Builder interface for the specified banner.
     */
    public function show(Banner $banner): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'sku', 'selling_price', 'special_price', 'thumbnail'])
            ->orderBy('name')
            ->limit(100)
            ->get();

        return view('admin.banners.builder', [
            'title' => 'Visual Banner Builder - ' . $banner->title,
            'banner' => $banner,
            'designConfig' => $banner->effective_design_config,
            'products' => $products,
        ]);
    }

    /**
     * Save the visual design configuration payload from the Banner Builder.
     */
    public function save(Request $request, Banner $banner): JsonResponse
    {
        $validated = $request->validate([
            'design_config' => ['required', 'array'],
            'design_config.canvas' => ['required', 'array'],
            'design_config.canvas.width' => ['required', 'numeric', 'min:100'],
            'design_config.canvas.height' => ['required', 'numeric', 'min:100'],
            'design_config.elements' => ['nullable', 'array'],
        ]);

        // Save complete design_config array from request
        $banner->design_config = $request->input('design_config');

        // Sync primary headline, subtitle, link, and background image
        if ($request->filled('title')) {
            $banner->title = $request->input('title');
        }
        if ($request->has('subtitle')) {
            $banner->subtitle = $request->input('subtitle');
        }
        if ($request->has('link')) {
            $banner->link = $request->input('link');
        }
        if ($request->has('position')) {
            $banner->position = $request->input('position');
        }
        if ($request->has('is_active')) {
            $banner->is_active = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('starts_at')) {
            $banner->starts_at = $request->filled('starts_at') ? $request->input('starts_at') : null;
        }
        if ($request->has('expires_at')) {
            $banner->expires_at = $request->filled('expires_at') ? $request->input('expires_at') : null;
        }
        if ($request->has('sort_order')) {
            $banner->sort_order = (int) $request->input('sort_order', 0);
        }

        // If a canvas background image was set, sync to banner image field
        $bgImage = $validated['design_config']['canvas']['backgroundImage'] ?? null;
        if ($bgImage && is_string($bgImage)) {
            // Strip leading /storage/ if applicable or keep path
            $banner->image = $bgImage;
        }

        $banner->save();

        return response()->json([
            'success' => true,
            'message' => "Banner '{$banner->title}' visual design saved successfully.",
            'banner' => $banner->fresh(),
            'design_config' => $banner->effective_design_config,
        ]);
    }

    /**
     * Upload an auxiliary graphic or layer asset for use inside the visual builder.
     */
    public function uploadAsset(Request $request, Banner $banner): JsonResponse
    {
        $request->validate([
            'asset' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $path = $request->file('asset')->store('banners/assets', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Asset uploaded successfully.',
        ]);
    }
}
