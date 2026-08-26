# Universal AI Banner Engine

## Executive Overview
The **Universal AI Banner Engine** is an enterprise-grade subsystem designed to ingest arbitrary, professionally designed hero sections, promotional banners, and visual merchandising assets (delivered as ZIP archives, HTML/CSS/JS snippets, or visual images), analyze them using deterministic DOM/CSS parsing and AI multimodal reasoning, and convert them into reusable, dynamic templates.

### Core Value Proposition
> Any design can be uploaded as raw HTML + CSS + JS + assets. The system analyzes the structure, isolates dynamic text, media, and product attributes, and creates a secure, editable banner model for store administrators **without requiring visual editors, Canva-style canvas manipulation, or HTML/CSS expertise**.

---

## Key Principles & Guarantees
1. **Design Preservation**: The original HTML, CSS, JS animations, 3D canvases, video backgrounds, and responsive media queries remain the single source of truth. The engine injects dynamic values into the original structure instead of regenerating or flattening the layout.
2. **Zero Hardcoded Selectors**: No dependencies on fixed CSS classes (e.g. `.banner-title`), HTML tags, or predefined templates. Structural and semantic roles are discovered dynamically.
3. **Strict Content/Design Separation**: Content fields (headlines, subtitles, prices, discount badges, product imagery, CTA links) are editable; layout, animations, shaders, keyframes, and decorations remain safely locked.
4. **Security & Sandboxing**: All imported HTML/CSS/JS is validated, sanitized, and isolated within secure sandbox contexts (CSP, iframe isolation, origin policies) to prevent XSS, SSRF, and prototype pollution.
5. **Deterministic Confidence System**: Every detected element has a confidence score (90–100% Auto Accept, 75–89% Review Recommended, 50–74% Needs Review, <50% Unknown). Admins are only prompted with simple semantic correction buttons when confidence is uncertain.
6. **E-Commerce Integration**: Dynamic fields map seamlessly to store catalog entities (Products, Categories, Brands, Offers) or remain static text/media.

---

## Architectural Workflow
```
[ Upload ZIP / HTML / Image ]
            │
            ▼
   1. Validation & Extraction (MIME, size, zip traversal safety)
            │
            ▼
   2. Sanitization & Dependency Normalization (CSP, script sandbox)
            │
            ▼
   3. DOM & CSS Structural Analysis (Fingerprinting, hierarchy)
            │
            ▼
   4. Visual Browser Rendering & Viewport Capture (Desktop / Tablet / Mobile)
            │
            ▼
   5. AI Multimodal Semantic Classification & Confidence Scoring
            │
            ▼
   6. Dynamic Schema & Field Binding (Product catalog linking)
            │
            ▼
   7. Storefront Sandboxed Injection & High-Performance Caching
```

---

## Documentation Index
- [Phase Status](PHASE-STATUS.md) — Real-time tracking of implementation phases and tasks.
- [Architecture & Data Model](ARCHITECTURE.md) — Technical database schemas, services, and pipeline design.
- [Import Formats & Packaging](IMPORT-FORMAT.md) — Guidelines for ZIP, HTML/CSS/JS, and Image packages.
- [Security & Sandboxing](SECURITY.md) — Sanitization, CSP headers, asset validation, and iframe isolation rules.
- [AI Analysis & Vision Engine](AI-ANALYSIS.md) — Multimodal prompts, confidence scoring, and semantic role taxonomy.
- [Dynamic Fields & Catalog Binding](DYNAMIC-FIELDS.md) — Field fingerprints, product mappings, and fallback resolution.
- [Troubleshooting & Diagnostics](TROUBLESHOOTING.md) — Error resolution, fallback strategies, and diagnostic checklists.
