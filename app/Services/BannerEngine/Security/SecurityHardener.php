<?php

namespace App\Services\BannerEngine\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SecurityHardener
{
    /**
     * Blocked executable extensions that must never be uploaded or extracted.
     */
    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin',
        'cgi', 'pl', 'py', 'vbs', 'scr', 'dll', 'so',
        'htaccess', 'htpasswd', 'env', 'git', 'svn',
    ];

    /**
     * Private IP ranges to block against SSRF attacks.
     */
    protected const BLOCKED_IP_PATTERNS = [
        '/^127\./',                         // 127.0.0.0/8 (Loopback)
        '/^10\./',                          // 10.0.0.0/8 (Private)
        '/^192\.168\./',                    // 192.168.0.0/16 (Private)
        '/^172\.(1[6-9]|2[0-9]|3[0-1])\./', // 172.16.0.0/12 (Private)
        '/^169\.254\./',                    // 169.254.0.0/16 (Link Local / Cloud Metadata)
        '/^0\./',                           // 0.0.0.0/8
        '/^localhost$/i',                   // localhost
    ];

    /**
     * Sanitize dynamic field value against XSS, javascript: schemes, and event injection.
     *
     * @param mixed $value
     * @return mixed
     */
    public function sanitizeFieldValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $cleaned = [];
            foreach ($value as $k => $v) {
                $cleaned[$k] = $this->sanitizeFieldValue($v);
            }
            return $cleaned;
        }

        if (!is_string($value)) {
            return $value;
        }

        $clean = $value;

        // 1. Strip script tags
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean);

        // 2. Neutralize javascript:, vbscript:, and data:text/html URIs
        $clean = preg_replace('/javascript\s*:/i', 'blocked-js:', $clean);
        $clean = preg_replace('/vbscript\s*:/i', 'blocked-vbs:', $clean);
        $clean = preg_replace('/data\s*:\s*text\/html/i', 'blocked-data:', $clean);

        // 3. Strip dangerous HTML event handlers (onload, onerror, onclick, etc.)
        $clean = preg_replace('/\s+on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);

        // 4. Strip iframe, object, embed tags
        $clean = preg_replace('/<\/?(iframe|object|embed|applet|meta|link|base)\b[^>]*>/i', '', $clean);

        return trim($clean);
    }

    /**
     * Validate URL against SSRF attacks targeting internal IPs or cloud metadata.
     *
     * @param string $url
     * @return bool
     */
    public function validateUrlSafety(string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        // Relative URLs are safe
        if (Str::startsWith($url, '/') || Str::startsWith($url, '#') || Str::startsWith($url, './')) {
            return true;
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);
        $scheme = strtolower($parsed['scheme'] ?? '');

        // Only allow http and https
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        // Check against blocked IP patterns
        foreach (self::BLOCKED_IP_PATTERNS as $pattern) {
            if (preg_match($pattern, $host)) {
                return false;
            }
        }

        // Resolve DNS and check resulting IP
        if (!app()->runningUnitTests()) {
            $ip = gethostbyname($host);
            if ($ip && $ip !== $host) {
                foreach (self::BLOCKED_IP_PATTERNS as $pattern) {
                    if (preg_match($pattern, $ip)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Verify safety of an uploaded file against executable execution and malicious SVG entities.
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function verifyUploadSafety(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
            return false;
        }

        // Check for double extension attacks: image.php.png
        $name = strtolower($file->getClientOriginalName());
        foreach (self::DANGEROUS_EXTENSIONS as $dangerous) {
            if (str_contains($name, '.' . $dangerous . '.')) {
                return false;
            }
        }

        // Deep SVG Inspection
        if ($ext === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            $content = file_get_contents($file->getRealPath());
            if ($this->hasMaliciousSvgContent($content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if SVG content contains script tags, external entities (XXE), or dangerous handlers.
     *
     * @param string $svgContent
     * @return bool
     */
    public function hasMaliciousSvgContent(string $svgContent): bool
    {
        // 1. Check for XXE Entity definitions
        if (preg_match('/<!ENTITY/i', $svgContent) || preg_match('/<!DOCTYPE/i', $svgContent)) {
            return true;
        }

        // 2. Check for script tags
        if (preg_match('/<script\b/i', $svgContent)) {
            return true;
        }

        // 3. Check for event handlers
        if (preg_match('/\bon[a-zA-Z]+\s*=/i', $svgContent)) {
            return true;
        }

        // 4. Check for foreignObject
        if (preg_match('/<foreignObject\b/i', $svgContent)) {
            return true;
        }

        // 5. Check for javascript: URLs in attributes
        if (preg_match('/(?:href|xlink:href)\s*=\s*["\']\s*javascript:/i', $svgContent)) {
            return true;
        }

        return false;
    }

    /**
     * Verify ZIP archive against ZIP bombs (uncompressed ratio limit) and path traversal.
     *
     * @param string $zipPath
     * @param int $maxFiles
     * @param int $maxTotalBytes
     * @return bool
     */
    public function verifyZipSafety(string $zipPath, int $maxFiles = 500, int $maxTotalBytes = 104857600): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        if ($zip->numFiles > $maxFiles) {
            $zip->close();
            return false;
        }

        $totalSize = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat) {
                continue;
            }

            $entryName = $stat['name'];

            // 1. Path traversal check (ZipSlip)
            if (
                str_contains($entryName, '../') ||
                str_contains($entryName, '..\\') ||
                Str::startsWith($entryName, '/') ||
                Str::startsWith($entryName, '\\') ||
                preg_match('/^[a-zA-Z]:/', $entryName)
            ) {
                $zip->close();
                return false;
            }

            // 2. Executable check
            $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if (in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
                $zip->close();
                return false;
            }

            // 3. Zip bomb size accumulation check
            $totalSize += $stat['size'];
            if ($totalSize > $maxTotalBytes) {
                $zip->close();
                return false;
            }
        }

        $zip->close();
        return true;
    }
}
