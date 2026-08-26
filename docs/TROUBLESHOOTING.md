# Troubleshooting & Diagnostic Guide

## Common Issues & Resolutions

### 1. ZIP Archive Extraction Errors
- **Issue**: "Invalid ZIP archive or entrypoint not found."
- **Root Cause**: The uploaded archive did not contain an `index.html` or `.html` file at the root or within a top-level directory.
- **Resolution**: Ensure the ZIP package contains at least one valid HTML entry file. The engine searches recursively for the first root-level HTML document.

### 2. Relative Asset Broken Links
- **Issue**: Images or CSS not loading inside the sandbox preview.
- **Root Cause**: Hardcoded absolute local paths (e.g. `file:///C:/...` or `/assets/...`) instead of relative paths (`assets/...` or `./style.css`).
- **Resolution**: Use relative asset paths in the uploaded package. The engine automatically rewrites relative paths to sandboxed storage URLs.

### 3. Low Confidence / Unknown Elements
- **Issue**: An element appears under "Needs Review" in the admin panel.
- **Root Cause**: The DOM structure or CSS typography was ambiguous (e.g. styled `<span>` with no semantic tag or ARIA hints).
- **Resolution**: Use the quick one-click semantic role selector in the admin panel to assign the proper role (Headline, Price, CTA, etc.). No code editing required.

### 4. Layout Shift or Text Overflow
- **Issue**: Replaced dynamic text overflows the container at mobile breakpoints.
- **Root Cause**: The original CSS had fixed width/height constraints with `overflow: hidden`.
- **Resolution**: Review the responsive simulation alerts in the admin preview and adjust text length to match original design constraints.
