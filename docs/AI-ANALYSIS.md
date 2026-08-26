# AI Semantic Analysis & Taxonomy Specification

## Overview
The Universal AI Banner Engine combines deterministic DOM/CSS heuristics with multimodal AI reasoning to categorize elements into semantic roles without requiring predefined class names or rigid markup structures.

---

## Semantic Role Taxonomy
The role system is extensible and covers standard e-commerce visual components:

| Semantic Role | Description | Editable Default |
| :--- | :--- | :--- |
| `headline` | Primary hero title or prominent message | `true` |
| `subtitle` | Secondary supporting headline or tag line | `true` |
| `description` | Descriptive body copy or promo paragraph | `true` |
| `eyebrow` | Small category tag, badge, or pre-header text | `true` |
| `offer` | Promotional text (e.g. "FLAT 50% OFF", "BOGO") | `true` |
| `discount` | Numeric percentage or monetary discount value | `true` |
| `price` | Main selling price (e.g. "$19.99", "₹450") | `true` |
| `old_price` | Strikethrough / original retail price | `true` |
| `currency` | Currency symbol or code | `true` |
| `cta` | Call to action button text or link target | `true` |
| `product_image`| Main featured product photo / render | `true` |
| `product` | Product title or combined product identifier | `true` |
| `logo` | Brand or store logo | `true` |
| `badge` | Highlight badge (e.g. "Best Seller", "Organic")| `true` |
| `category` | Category name or navigation chip | `true` |
| `brand` | Brand name | `true` |
| `rating` | Star rating or review count snippet | `true` |
| `timer` | Countdown timer target timestamp or label | `true` |
| `date` | Campaign end date or validity period | `true` |
| `background` | Primary background image, gradient, or canvas | `false` (Design locked) |
| `decorative` | Geometric accents, shapes, vector ornaments | `false` (Design locked) |
| `video` | Background or inline promotional video | `true` |
| `animation` | Canvas / WebGL / GSAP animation wrapper | `false` (Design locked) |
| `unknown` | Unclassified element | `false` (Needs review) |

---

## Deterministic Confidence Scoring Tiers
```text
┌─────────────────────────────────────────────────────────────┐
│ 90% – 100% : AUTO ACCEPT                                    │
│ High certainty match (e.g., prominent H1 or CTA button)    │
├─────────────────────────────────────────────────────────────┤
│ 75% – 89%  : REVIEW RECOMMENDED                             │
│ Clear semantic likelihood, minor ambiguity in role naming   │
├─────────────────────────────────────────────────────────────┤
│ 50% – 74%  : NEEDS REVIEW                                   │
│ Ambiguous role (e.g., small text could be badge or eyebrow) │
├─────────────────────────────────────────────────────────────┤
│ Below 50%  : UNKNOWN                                        │
│ Requires explicit admin role selection                      │
└─────────────────────────────────────────────────────────────┘
```

---

## Hybrid Heuristic + AI Engine
1. **Structural Heuristics**: Extracts tag types (`<h1>`, `<button>`, `<a>`, `<img>`, `<video>`), ARIA labels, semantic CSS properties (font-size, z-index, aspect ratio), and text patterns (currency symbols, percentages).
2. **AI Multimodal Analysis**: Ingests viewport screenshots + DOM snippet to resolve ambiguous visual hierarchies.
3. **Execution Timing**: Analysis runs **strictly on import or explicit re-analyze**, never on end-user storefront requests.
