# System Architecture & Data Model

## System Overview
The **Universal AI Banner Engine** integrates seamlessly into Laravel 12/13, utilizing Eloquent ORM, Blade components, modern queue/background jobs, and sandboxed rendering pipelines.

---

## Entity Relationship Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                     Banners                                     │
│  - id                                                                           │
│  - title                                                                        │
│  - position (home_hero, popup, sidebar, category_top, promotional_bar)         │
│  - banner_type (standard, dynamic_template)                                     │
│  - is_active                                                                    │
│  - active_version_id ──────────────────────────────────────────┐                │
│  - current_template_id ────────────────┐                       │                │
└────────────────────────────────────────┼───────────────────────┼────────────────┘
                                         │                       │
                                         ▼                       ▼
┌────────────────────────────────────────┴───────┐   ┌───────────┴────────────────┐
│                Banner Templates                │   │       Banner Versions      │
│  - id                                          │   │  - id                      │
│  - banner_id                                   │   │  - banner_id               │
│  - name                                        │   │  - template_id             │
│  - import_source (zip, html, image)            │   │  - version_number (1, 2..) │
│  - raw_html                                    │   │  - status (draft/published)│
│  - raw_css                                     │   │  - field_values (JSON)     │
│  - raw_js                                      │   │  - template_snapshot (JSON)│
│  - asset_manifest (JSON)                       │   │  - change_summary          │
│  - dynamic_schema (JSON)                       │   └─────────────┬──────────────┘
│  - viewports (JSON)                            │                 │
└────────────────────────┬───────────────────────┘                 │
                         │                                         │
                         ▼                                         ▼
┌────────────────────────┴───────────────────────┐   ┌─────────────┴──────────────┐
│                  Banner Fields                 │   │     Banner Field Mappings  │
│  - id                                          │   │  - id                      │
│  - template_id                                 │   │  - banner_field_id         │
│  - field_key (e.g. fld_8f3a91)                 │   │  - banner_version_id       │
│  - semantic_role (headline, price, cta, etc.)  │   │  - mapping_type (static/   │
│  - label                                       │   │    product/category/brand) │
│  - field_type (text, image, video, url, etc.)  │   │  - static_value            │
│  - default_value                               │   │  - product_id              │
│  - dom_path, selector, text_fingerprint        │   │  - product_attribute       │
│  - confidence_score (0.00 - 1.00)              │   │  - fallback_value          │
│  - is_editable (boolean)                       │   └────────────────────────────┘
└────────────────────────────────────────────────┘
```

### Auxiliary Entities
- `banner_assets`: Ingested media files (images, SVGs, videos, webfonts, 3D glTF/GLB models).
- `banner_analyses`: Complete audit trail of AI model runs, computer vision OCR, bounding boxes, and reviewer overrides.
- `banner_publications`: Storefront rendering schedules, display locations, cached pre-renders, and impression analytics.

---

## Service Layer Architecture
The core business logic resides under `app/Services/BannerEngine/`:

1. **`Import/`**:
   - `ZipImportService`: Validates archive structure, extracts assets safely, and resolves asset paths.
   - `HtmlImportService`: Ingests raw markup and parses embedded styles and scripts.
   - `AssetManager`: Manages file storage, hashes, MIME validation, and URL rewrites.
2. **`Sanitizer/`**:
   - `HtmlSanitizer`: Strips malicious scripts and attributes while retaining structural markup and animations.
   - `CspManager`: Builds tight Content Security Policy headers for preview and production iframes.
3. **`Analyzer/`**:
   - `DomAnalyzer`: Performs node traversal, semantic tag detection, text/image extraction, and fingerprint calculation.
   - `CssAnalyzer`: Parses stylesheets, keyframe animations, media queries, and responsive rules.
   - `ConfidenceScorer`: Evaluates detected elements against heuristic rules and AI predictions.
4. **`Renderer/`**:
   - `SandboxedRenderer`: Prepares isolated HTML documents for preview and storefront delivery.
   - `DynamicInjector`: Replaces dynamic values inside the original DOM without rebuilding or modifying the design.
5. **`Bridge/`**:
   - `ProductDataBridge`: Maps product catalog attributes (name, price, image, sale_price, discounts) to banner fields.

---

## Directory Structure
```text
app/
├── Models/
│   ├── Banner.php (Enhanced)
│   ├── BannerTemplate.php
│   ├── BannerVersion.php
│   ├── BannerField.php
│   ├── BannerFieldMapping.php
│   ├── BannerAsset.php
│   ├── BannerAnalysis.php
│   └── BannerPublication.php
└── Services/
    └── BannerEngine/
        ├── Contracts/
        ├── Import/
        ├── Sanitizer/
        ├── Analyzer/
        ├── Renderer/
        ├── Bridge/
        └── Versioning/
```
