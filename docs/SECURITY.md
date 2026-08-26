# Security Model & Sandboxing Specification

## Security Principles
Imported designs contain untrusted client-provided HTML, CSS, JavaScript, and media assets. Under no circumstances may untrusted code execute directly within the main administrative session or the primary storefront window context without strict isolation.

---

## Multi-Layer Defense Architecture

### 1. File Upload & Archive Decompression Safety
- **Zip-Slip & Directory Traversal Protection**: Every file path extracted from ZIP archives is strictly validated to ensure it does not escape the isolated temporary extraction directory (`..` sequences, absolute Windows/Unix root paths are rejected).
- **MIME & Extension Whitelisting**: Strict verification of file magic bytes against allowed image, video, font, CSS, and JS types. Executable extensions (`.php`, `.phtml`, `.phar`, `.exe`, `.sh`, `.bat`, `.cmd`) are permanently blocked.
- **Decompression Bomb Defense**: Maximum uncompressed file size and maximum total extraction quotas enforced.

### 2. HTML & CSS Sanitization
- Malicious HTML attributes (`onerror=`, `onload=`, `onclick=`, `javascript:` URI schemes) are stripped during import analysis.
- Unsafe SVG markup is cleansed of embedded `<script>` tags, foreign objects, and malicious XML entity expansions (XXE).
- CSS `@import` rules pointing to dangerous external protocols or local system paths are sanitized.

### 3. Iframe Sandboxing & Content Security Policy (CSP)
- **Sandboxed Rendering**: Banner previews in the admin panel and storefront embeds are rendered inside an isolated `<iframe>` with strict sandbox directives:
  ```html
  <iframe
      sandbox="allow-scripts allow-same-origin"
      csp="default-src 'self' data: blob: https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: blob: https:; media-src 'self' data: blob: https:; frame-ancestors 'self';"
  ></iframe>
  ```
- **Origin Isolation**: Sandbox communication uses standardized `postMessage` protocol with origin verification.

### 4. Admin Session Protection
- CSRF tokens are enforced on all banner import, update, publish, and rollback actions.
- Administrative authentication and role-based permissions (`admin.auth`) guard all engine endpoints.
