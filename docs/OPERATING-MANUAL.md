# Universal AI Banner Engine — Client & Administrator Operating Manual

Welcome to the **Universal AI Banner Engine** operating manual. This guide explains how to import designs, edit copy and media, bind e-commerce catalog items, preview across devices, and manage publication schedules without touching code.

---

## 1. Importing a New Design

Navigate to **Promotions > Banners** in the admin panel and click the **Import Design Package** button.

### Option A: Complete Design ZIP Archive (Recommended)
1. Select the **ZIP Archive** tab.
2. Enter a campaign title (e.g. `Summer Flash Sale 2026`).
3. Select placement position (e.g. `Home Hero Slider`, `Category Top Banner`, `Popup Modal`).
4. Upload your ZIP package containing your `index.html`, stylesheet `.css` files, JavaScript `.js` files, and assets (`images/`, `videos/`, `fonts/`).
5. Click **Import & Analyze Design**. The engine will safely extract the files, analyze the layout, and launch the editor.

### Option B: Paste HTML / CSS / JS Code
1. Select the **Paste Code** tab.
2. Enter a campaign title and position.
3. Paste your raw HTML markup into the HTML box, CSS into the CSS box, and any optional JavaScript into the JS box.
4. Click **Import & Analyze Design**.

### Option C: Upload Flattened Banner Image (Image-to-Design Mode)
1. Select the **Flattened Image** tab.
2. Upload your high-resolution banner image (`.png`, `.jpg`, `.webp`).
3. The engine uses OCR and computer vision to decompose the image into text layers, background imagery, and interactive call-to-action buttons.

---

## 2. Editing Banner Content

After import, the system opens the **Dynamic Content Editor**.

- **No Visual Complexity**: You do not need to drag-and-drop or edit CSS. All editable fields are presented as simple input boxes in the left-hand panel:
  - **Headline**: Edit the primary campaign title.
  - **Subtitle / Description**: Edit promotional copy.
  - **Price & Sale Price**: Enter prices or discount badges (e.g. `20% OFF`).
  - **Call to Action**: Edit button label and destination URL.
  - **Images & Video**: Upload replacement images or enter video URLs.

- **Locked Architecture Guarantee**: Your changes modify only text and media content. Layout grids, animations, keyframes, 3D WebGL scenes, and responsive styling remain 100% locked and protected from accidental breakage.

---

## 3. Live Multi-Device Preview

On the right side of the editor, a real-time sandboxed preview renders your banner:
- Click **Desktop (1200px)**, **Tablet (768px)**, or **Mobile (375px)** to inspect how your banner looks across different viewports.
- The preview updates live as you type.

---

## 4. Binding E-Commerce Catalog Products

Instead of typing prices and product names manually, you can bind fields directly to live catalog items:
1. In the editor, choose **Catalog Product Binding**.
2. Select a product from your catalog (e.g. `Organic Devgad Alphonso Mangoes`).
3. The banner will automatically pull the product's live name, thumbnail, selling price, sale price, calculated discount percentage, and direct checkout link.
4. If the product price or stock status changes in your catalog, the banner updates automatically.

---

## 5. Reviewing AI Semantic Roles

If a field was classified with low confidence (e.g. `Needs Review` / `50-74%`):
1. Click the **Review Roles** button in the top toolbar.
2. In the modal, you will see a list of detected elements, their current role, and a dropdown of available roles (`Headline`, `Subtitle`, `Price`, `Discount`, `Product Image`, `CTA Button`, `Badge`).
3. Select the correct role and click **Save Role Corrections**. The form and bindings update immediately.

---

## 6. Version History & One-Click Rollback

Every time you save changes, the engine creates an immutable version snapshot:
1. Click the **Version History** button in the editor header.
2. Browse historical versions (`v1`, `v2`, `v3`) with timestamps, author information, and change summaries.
3. Click **Restore Version** on any past version to instantly roll back your banner to that exact snapshot with 100% fidelity.

---

## 7. Scheduling & Publishing

- **Start & Expiry Dates**: Set optional start and expiration dates on the banner settings panel to schedule automated holiday or flash-sale campaigns.
- **Toggle Live Status**: Turn banners active or inactive with a single click from the banner index table.
