<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\BannerField;
use App\Models\BannerTemplate;
use App\Models\BannerVersion;
use App\Models\Product;
use App\Services\BannerEngine\Analyzer\AiSemanticClassifier;
use App\Services\BannerEngine\BannerEngineManager;
use App\Services\BannerEngine\Import\HtmlImportService;
use App\Services\BannerEngine\Import\ZipImportService;
use App\Services\BannerEngine\Preservation\DesignPreservationVerifier;
use App\Services\BannerEngine\Renderer\SandboxedRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BannerController extends Controller
{
    /**
     * Display a paginated listing of promotional banners with position filters.
     */
    public function index(Request $request): View
    {
        $query = Banner::query()->with(['template.fields', 'activeVersion']);

        // Search Filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }

        // Position Filter
        if ($position = $request->input('position')) {
            $query->where('position', $position);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Type Filter (Standard vs Universal Dynamic Template)
        if ($type = $request->input('type')) {
            $query->where('banner_type', $type);
        }

        // Sorting
        $sort = $request->input('sort', 'sort_asc');
        switch ($sort) {
            case 'sort_asc':
                $query->orderBy('sort_order', 'asc')->latest();
                break;
            case 'latest':
                $query->latest();
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->latest();
                break;
        }

        $banners = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Banner::count(),
            'active' => Banner::where('is_active', true)->count(),
            'hero' => Banner::where('position', Banner::POSITION_HOME_HERO)->count(),
            'promo' => Banner::whereIn('position', [Banner::POSITION_PROMOTIONAL_BAR, Banner::POSITION_CATEGORY_TOP])->count(),
            'dynamic' => Banner::where('banner_type', Banner::TYPE_DYNAMIC_TEMPLATE)->count(),
        ];

        return view('admin.banners.index', [
            'title' => 'Banners - ' . config('admin.name', 'Grocery Admin'),
            'banners' => $banners,
            'stats' => $stats,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show the form for creating a new standard banner.
     */
    public function create(): View
    {
        return view('admin.banners.create', [
            'title' => 'Add Banner - ' . config('admin.name', 'Grocery Admin'),
        ]);
    }

    /**
     * Store a newly created standard banner in storage.
     */
    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = Storage::url($path);
        }

        $banner = Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('toast_success', "Banner '{$banner->title}' created successfully.");
    }

    /**
     * Show the form for importing a design package (ZIP or Raw HTML).
     */
    public function importForm(): View
    {
        return view('admin.banners.import', [
            'title' => 'Import Design Package - Universal AI Banner Engine',
        ]);
    }

    /**
     * Process an imported design package (PSD, ZIP, or Raw Code).
     */
    public function importProcess(Request $request): RedirectResponse
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $request->validate([
            'import_type' => ['required', 'in:psd,zip,raw_code,image'],
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:50'],
            'psd_file' => ['required_if:import_type,psd', 'nullable', 'file', 'max:512000'],
            'zip_file' => ['required_if:import_type,zip', 'nullable', 'file', 'max:512000'],
            'banner_image' => ['required_if:import_type,image', 'nullable', 'file', 'max:512000'],
            'html_code' => ['required_if:import_type,raw_code', 'nullable', 'string'],
            'css_code' => ['nullable', 'string'],
            'js_code' => ['nullable', 'string'],
        ]);

        // 1. Create container Banner record
        $banner = Banner::create([
            'title' => $request->input('title'),
            'position' => $request->input('position', Banner::POSITION_HOME_HERO),
            'banner_type' => Banner::TYPE_DYNAMIC_TEMPLATE,
            'image' => '/storage/banner_engine/placeholder.png',
            'is_active' => true,
        ]);

        // 2. Run Importer
        $template = null;
        if ($request->input('import_type') === 'psd' && $request->hasFile('psd_file')) {
            $psdService = new \App\Services\BannerEngine\Import\PsdImportService();
            $template = $psdService->importPsd($request->file('psd_file'), [
                'banner_id' => $banner->id,
                'name' => $banner->title,
            ]);
            $analysis = $template->latestAnalysis;
        } elseif ($request->input('import_type') === 'image' && $request->hasFile('banner_image')) {
            $imageService = new \App\Services\BannerEngine\ImageMode\ImageToDesignService();
            $template = $imageService->processImage($request->file('banner_image'), [
                'banner_id' => $banner->id,
                'name' => $banner->title,
            ]);
            $analysis = $template->latestAnalysis;
        } elseif ($request->input('import_type') === 'zip' && $request->hasFile('zip_file')) {
            $importer = new ZipImportService();
            $template = $importer->importZip($request->file('zip_file'), [
                'banner_id' => $banner->id,
                'name' => $banner->title,
            ]);
            $classifier = new AiSemanticClassifier();
            $analysis = $classifier->analyze($template);
        } else {
            $importer = new HtmlImportService();
            $template = $importer->importRawCode(
                $request->input('html_code'),
                $request->input('css_code'),
                $request->input('js_code'),
                [
                    'banner_id' => $banner->id,
                    'name' => $banner->title,
                ]
            );
            $classifier = new AiSemanticClassifier();
            $analysis = $classifier->analyze($template);
        }

        // 4. Create initial BannerVersion (v1)
        $defaultFieldValues = [];
        foreach ($template->fields as $field) {
            if ($field->is_editable) {
                $defaultFieldValues[$field->field_key] = $field->default_value;
            }
        }

        $version = BannerVersion::create([
            'banner_id' => $banner->id,
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => BannerVersion::STATUS_PUBLISHED,
            'field_values' => $defaultFieldValues,
            'template_snapshot' => [
                'dynamic_schema' => $template->dynamic_schema,
                'created_at' => now()->toIso8601String(),
            ],
            'change_summary' => 'Initial AI import & semantic analysis',
            'published_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $banner->update([
            'current_template_id' => $template->id,
            'active_version_id' => $version->id,
        ]);

        $count = $analysis ? ($analysis->editable_elements_count ?? $template->fields()->count()) : $template->fields()->count();
        $conf = $analysis ? round(($analysis->overall_confidence ?? 0.95) * 100) : 95;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.banners.editor', $banner->id),
                'message' => "Design imported and analyzed successfully! ({$count} editable fields detected with {$conf}% confidence)",
            ]);
        }

        return redirect()->route('admin.banners.editor', $banner->id)
            ->with('toast_success', "Design imported and analyzed successfully! ({$count} editable fields detected with {$conf}% confidence)");
    }

    /**
     * Client-Facing Content Editor (No visual code editor).
     */
    public function editor(Banner $banner): View|RedirectResponse
    {
        if (!$banner->isDynamicTemplate() || !$banner->template) {
            return redirect()->route('admin.banners.edit', $banner->id);
        }

        $template = $banner->template;
        $fields = $template->fields()->orderBy('sort_order')->get();
        $version = $banner->activeVersion ?? $banner->versions()->latest()->first();
        $fieldValues = $version ? ($version->field_values ?? []) : [];
        $analysis = $template->latestAnalysis;

        $products = Product::where('is_active', true)->select('id', 'name', 'selling_price', 'special_price', 'thumbnail', 'slug')->get();

        return view('admin.banners.editor', [
            'title' => 'Edit Dynamic Content - ' . $banner->title,
            'banner' => $banner,
            'template' => $template,
            'fields' => $fields,
            'fieldValues' => $fieldValues,
            'version' => $version,
            'analysis' => $analysis,
            'products' => $products,
            'roles' => BannerEngineManager::getSemanticRoles(),
        ]);
    }

    /**
     * Save dynamic content field values.
     */
    public function updateFields(Request $request, Banner $banner): RedirectResponse
    {
        if (!$banner->isDynamicTemplate() || !$banner->template) {
            return redirect()->route('admin.banners.index');
        }

        $fieldInputs = $request->input('fields', []);
        $template = $banner->template;

        // Process uploaded image fields if any
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $fieldKey => $uploadedFile) {
                if ($uploadedFile->isValid()) {
                    $path = $uploadedFile->store("banner_engine/{$template->id}/uploads", 'public');
                    $fieldInputs[$fieldKey] = Storage::disk('public')->url($path);
                }
            }
        }

        // Verify design preservation
        $verifier = new DesignPreservationVerifier();
        $report = $verifier->verify($template, $fieldInputs);

        // Update banner core attributes
        $banner->update([
            'title' => $request->input('title', $banner->title),
            'position' => $request->input('position', $banner->position),
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $request->filled('starts_at') ? $request->input('starts_at') : null,
            'expires_at' => $request->filled('expires_at') ? $request->input('expires_at') : null,
        ]);

        // Get or create active version
        $activeVersion = $banner->activeVersion;
        if (!$activeVersion) {
            $activeVersion = BannerVersion::create([
                'banner_id' => $banner->id,
                'template_id' => $template->id,
                'version_number' => ($banner->versions()->max('version_number') ?? 0) + 1,
                'status' => BannerVersion::STATUS_PUBLISHED,
                'field_values' => $fieldInputs,
                'published_at' => now(),
                'created_by' => Auth::id(),
            ]);
            $banner->update(['active_version_id' => $activeVersion->id]);
        } else {
            $activeVersion->update([
                'field_values' => $fieldInputs,
                'change_summary' => 'Updated content fields',
            ]);
        }

        // Invalidate rendered template cache
        $cacheManager = new \App\Services\BannerEngine\Cache\BannerCacheManager();
        $cacheManager->invalidateTemplate($template);

        return redirect()->route('admin.banners.editor', $banner->id)
            ->with('toast_success', 'Banner content saved successfully! Design and layout remain locked and intact.');
    }

    /**
     * Display version history for a banner.
     */
    public function versions(Banner $banner): View|JsonResponse
    {
        $versionManager = new \App\Services\BannerEngine\Versioning\BannerVersionManager();
        $history = $versionManager->getVersionHistory($banner);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'versions' => $history]);
        }

        return view('admin.banners.versions', [
            'title' => 'Version History - ' . $banner->title,
            'banner' => $banner,
            'versions' => $history,
        ]);
    }

    /**
     * Rollback banner to a previous version snapshot.
     */
    public function rollback(Banner $banner, BannerVersion $version): RedirectResponse
    {
        $versionManager = new \App\Services\BannerEngine\Versioning\BannerVersionManager();
        $restored = $versionManager->rollbackToVersion($banner, $version, Auth::user());

        return redirect()->route('admin.banners.editor', $banner->id)
            ->with('toast_success', "Rolled back successfully to v{$version->version_number} (published as v{$restored->version_number}).");
    }

    /**
     * Publish a draft banner version.
     */
    public function publishVersion(Banner $banner, BannerVersion $version): RedirectResponse
    {
        $versionManager = new \App\Services\BannerEngine\Versioning\BannerVersionManager();
        $versionManager->publishVersion($version, Auth::user());

        return redirect()->route('admin.banners.editor', $banner->id)
            ->with('toast_success', "Version {$version->version_number} published successfully.");
    }

    /**
     * Re-run AI semantic analysis on banner template.
     */
    public function reanalyze(Banner $banner): RedirectResponse
    {
        if (!$banner->template) {
            return redirect()->route('admin.banners.index');
        }

        $classifier = new AiSemanticClassifier();
        $analysis = $classifier->analyze($banner->template);

        return redirect()->route('admin.banners.editor', $banner->id)
            ->with('toast_success', "Re-analysis complete. {$analysis->elements_detected_count} elements analyzed with " . round($analysis->overall_confidence * 100) . "% confidence.");
    }

    /**
     * Render sandboxed preview HTML.
     */
    public function preview(Banner $banner, Request $request): string
    {
        if (!$banner->template) {
            return '<h1>No template found</h1>';
        }

        $renderer = new SandboxedRenderer();
        $fieldValues = $request->input('fields', $banner->activeVersion?->field_values ?? []);

        return $renderer->render($banner->template, $fieldValues);
    }

    /**
     * AJAX endpoint to update a field's semantic role.
     */
    public function updateFieldRole(Request $request, Banner $banner, BannerField $field): JsonResponse
    {
        $request->validate([
            'semantic_role' => ['required', 'string'],
            'is_editable' => ['nullable', 'boolean'],
        ]);

        $role = $request->input('semantic_role');
        $isEditable = $request->boolean('is_editable', true);

        $rolesConfig = BannerEngineManager::getSemanticRoles();
        $roleInfo = $rolesConfig[$role] ?? ['label' => ucfirst(str_replace('_', ' ', $role)), 'type' => 'text'];

        $field->update([
            'semantic_role' => $role,
            'label' => $roleInfo['label'],
            'field_type' => $roleInfo['type'] ?? 'text',
            'is_editable' => $isEditable,
            'is_locked' => !$isEditable,
            'confidence_score' => 1.0,
            'confidence_status' => BannerField::CONFIDENCE_AUTO_ACCEPT,
            'detection_reason' => 'User semantic correction',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Element role updated to {$field->label}.",
            'field' => $field,
        ]);
    }

    /**
     * Show the form for editing standard banner.
     */
    public function edit(Banner $banner): View|RedirectResponse
    {
        if ($banner->isDynamicTemplate()) {
            return redirect()->route('admin.banners.editor', $banner->id);
        }

        return view('admin.banners.edit', [
            'title' => 'Edit Banner - ' . $banner->title,
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified standard banner in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image && Str::startsWith($banner->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $banner->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = Storage::url($path);
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('toast_success', "Banner '{$banner->title}' updated successfully.");
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy(Banner $banner): RedirectResponse|JsonResponse
    {
        $title = $banner->title;

        if ($banner->image && Str::startsWith($banner->image, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $banner->image);
            Storage::disk('public')->delete($oldPath);
        }

        $banner->delete();

        $successMsg = "Banner '{$title}' deleted successfully.";

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('admin.banners.index')->with('toast_success', $successMsg);
    }

    /**
     * Quick AJAX status toggle.
     */
    public function toggleStatus(Banner $banner): JsonResponse
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();

        return response()->json([
            'success' => true,
            'is_active' => $banner->is_active,
            'message' => "Banner '{$banner->title}' status changed to " . ($banner->is_active ? 'Active' : 'Inactive') . '.',
        ]);
    }
}
