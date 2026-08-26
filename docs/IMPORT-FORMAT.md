# Banner Import Format Specification

## Supported Import Modes

### Mode A: ZIP Archive Package
A comprehensive ZIP package containing HTML, CSS, JavaScript, media assets, and fonts.

#### Standard Package Structure
```text
banner-package.zip
├── index.html                (Primary entrypoint)
├── style.css                 (Primary stylesheet or in subfolder)
├── script.js                 (Optional animations / interactivity)
└── assets/
    ├── hero-product.png      (Product imagery)
    ├── background.webp       (High-resolution background)
    ├── promo-video.mp4       (Optional video background)
    └── fonts/
        └── custom-font.woff2 (Optional web font)
```

#### Implemented Resolution Engine
- **Entrypoint Discovery**: The `ZipImportService` scans the root and normalized subfolders for standard entrypoints (`index.html`, `index.htm`, `banner.html`, `hero.html`, `template.html`, `default.html` or the first `.html` file).
- **Directory Normalization**: Archives wrapped in a single root folder are transparently unwrapped without requiring manual repackaging.
- **Asset Rewriting**: All relative path references inside HTML (`src`, `href`, `poster`) and CSS (`url(...)`) are extracted, stored on the configured storage disk, and rewritten to resolved asset URLs.
- **Asset Manifest**: All discovered media, scripts, stylesheets, and fonts are indexed in `asset_manifest` with original path, disk path, MIME type, file size, SHA256 hash, and asset category.
- **Security Protections**:
  - ZipSlip directory traversal defense using strict `realpath()` verification against isolated temporary extraction directories.
  - Automatic filtering of OS metadata files (`__MACOSX`, `.DS_Store`, `Thumbs.db`).
  - Strict blocklist of executable formats (`.php`, `.phtml`, `.exe`, `.sh`, `.bat`, `.cmd`, `.py`, `.pl`, `.cgi`).
  - Total extraction quota and file count bounds.

---

### Mode B: Raw HTML/CSS/JS Snippets
For rapid imports where code is pasted directly:
- **HTML**: Main structural markup.
- **CSS**: Scoped styling or global stylesheet snippet.
- **JavaScript**: Interactive animation or canvas logic.

The `HtmlImportService` safely extracts embedded `<style>` and `<script>` blocks from raw markup, combines them with external inputs, sanitizes tags and attributes, and stores a unified `BannerTemplate`.

---

### Mode C: Image Visual Ingestion (Planned for Phase 9)
For designs uploaded as single images (PNG, JPG, WebP):
- Ingested via OCR & Computer Vision pipeline.
- Identifies visual bounding boxes for headlines, prices, badges, and focal products.
- Generates an approximate structural template with confidence ratings.

---

## Sandbox Preview Rendering
The `SandboxedRenderer` generates a complete standalone HTML document with:
- Standardized Content Security Policy (CSP) meta header.
- Viewport and typography resets.
- Isolated script execution boundary (`(function(){ try { ... } catch(e){} })()`).
- Responsive iframe embed helper (`renderIframeTag`) with `sandbox="allow-scripts allow-same-origin"` and viewport width simulations (`desktop`, `tablet`, `mobile`).
