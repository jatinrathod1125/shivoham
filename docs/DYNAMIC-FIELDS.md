# Dynamic Fields & Catalog Binding Specification

## Stable Field Identification Model
Because class names and DOM structures vary wildly across designs, dynamic elements must not rely solely on brittle CSS selectors. The engine generates unique, persistent field definitions with multi-point fallback locators.

### Schema Structure
```json
{
  "field_key": "fld_a7c81d",
  "semantic_role": "headline",
  "label": "Main Headline",
  "field_type": "text",
  "default_value": "Fresh Farm Organic Vegetables",
  "is_editable": true,
  "is_locked": false,
  "confidence_score": 0.98,
  "confidence_status": "auto_accept",
  "dom_path": "/div[1]/section[2]/h1[1]",
  "selector": ".hero-container h1:first-of-type",
  "text_fingerprint": "a3f58992e104",
  "element_fingerprint": "8b51ef94a021",
  "validation_rules": {
    "max_length": 150
  }
}
```

---

## Multi-Point Resilient Locator Resolution
When injecting dynamic values or previewing changes, `DynamicInjector` locates target elements using a 4-tier fallback chain:

1. **Tier 1: Exact XPath (`dom_path`)** — Fast direct node lookup.
2. **Tier 2: CSS Selector Query (`selector`)** — Handles minor wrapper shifts or extra surrounding elements.
3. **Tier 3: Structural Element Fingerprint (`element_fingerprint`)** — Matches tag name, ID, sorted classes, and parent hierarchy even if indices changed.
4. **Tier 4: Text Content Hash (`text_fingerprint`)** — Matches element by its original text signature.

---

## Supported Dynamic Field Types & Value Injection
| Field Type | Target Attributes / Content | Injection Mechanics |
| :--- | :--- | :--- |
| `text` | Text node content | Replaces text nodes while locking and preserving outer tags, classes, styles |
| `rich_text` | Inner HTML markup | Injects safe formatted HTML fragment (`<strong>`, `<em>`, `<br>`) |
| `price` | Monetary text | Replaces price value preserving formatting wrappers |
| `discount` | Promotional badge | Replaces badge percentage / promotional label |
| `image` | `src`, `alt` | Updates image asset URL and accessibility text |
| `video` | `src`, `poster`, `<source>` | Updates video media URL and poster frame |
| `cta` | Button text + `href` | Updates button / anchor text AND link target simultaneously |
| `date` / `timer` | Text + `data-target-date` | Injects display label and ISO timestamp attributes |

---

## Product Catalog Binding Layer
Admins can choose between **Static Value** or **Catalog Data Source**:

```text
┌─────────────────────────┐
│     Banner Field        │ (e.g. fld_a7c81d: headline)
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Mapping Selector      │ (Mode: Dynamic Product Binding)
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│     Product Target      │ (Product #42: "Organic Alphonso Mangoes")
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│   Database Attribute    │ (products.name -> "Organic Alphonso Mangoes")
└─────────────────────────┘
```

### Supported Dynamic Bindings
- `product.name` → Headline or Product Title
- `product.short_description` → Subtitle or Description
- `product.primary_image` → Product Image / Media
- `product.sale_price` / `product.price` → Price / Discounted Price
- `product.cost_price` → Old Price (Strikethrough)
- `product.discount_percentage` → Discount Badge
- `product.category.name` → Eyebrow / Category Tag
- `product.brand.name` → Brand Tag / Logo
- `product.url` → CTA Button Href
