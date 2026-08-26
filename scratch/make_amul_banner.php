<?php

$sourceProductImg = 'C:\\Users\\jmrat\\.gemini\\antigravity-ide\\brain\\561e3e43-f883-48bd-b13e-0b3f3cf2e3f9\\amul_milk_product_1787563995333.jpg';
$sourceBgImg = 'C:\\Users\\jmrat\\.gemini\\antigravity-ide\\brain\\561e3e43-f883-48bd-b13e-0b3f3cf2e3f9\\dairy_farm_bg_1787564020164.jpg';

$tempDir = sys_get_temp_dir() . '/amul_hero_banner_' . time();
$assetsDir = $tempDir . '/assets';

if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0777, true);
}

// Copy assets
copy($sourceProductImg, $assetsDir . '/amul-milk-pack.jpg');
copy($sourceBgImg, $assetsDir . '/farm-bg.jpg');

// 1. index.html
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amul Fresh Dairy Milk - Hero Banner</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="amul-hero-container">
        <!-- Background Layer -->
        <div class="hero-bg" style="background-image: url('assets/farm-bg.jpg');"></div>
        <div class="hero-overlay"></div>

        <div class="hero-content-wrapper">
            <!-- Left Text Column -->
            <div class="hero-text-col">
                <div class="badge-pill">
                    <span class="badge-dot"></span>
                    <span class="badge-label">100% PURE & FARM FRESH DAILY</span>
                </div>

                <h1 class="main-headline">
                    Pure Dairy Goodness <br />
                    <span class="highlight-text">Amul Taaza Milk</span>
                </h1>

                <p class="hero-description">
                    Handcrafted by India's largest dairy cooperative. Pasteurized, homogenized, and packed with vital vitamins, calcium, and natural protein for healthy mornings.
                </p>

                <div class="pricing-deal-box">
                    <div class="price-stack">
                        <span class="price-currency">₹</span>
                        <span class="price-val">28.00</span>
                        <span class="price-unit">/ 500ml</span>
                    </div>
                    <span class="discount-badge">SAVE 15% ON MONTHLY PASS</span>
                </div>

                <div class="cta-action-row">
                    <a href="/products/amul-taaza-milk" class="primary-cta-btn">
                        <span>Subscribe & Order Fresh</span>
                        <svg class="cta-arrow" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <span class="delivery-guarantee">⏰ Guaranteed Delivery by 7:00 AM</span>
                </div>

                <!-- Feature Badges -->
                <div class="feature-strip">
                    <div class="feature-item">
                        <span class="feature-icon">🥛</span>
                        <span class="feature-text">No Added Preservatives</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🛡️</span>
                        <span class="feature-text">Strict 70+ Quality Tests</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🌿</span>
                        <span class="feature-text">Direct From Grass-Fed Cows</span>
                    </div>
                </div>
            </div>

            <!-- Right Visual Column -->
            <div class="hero-visual-col">
                <div class="visual-card-glass">
                    <div class="glow-ring"></div>
                    <img src="assets/amul-milk-pack.jpg" alt="Amul Taaza Milk Pouch 500ml" class="product-pack-image" />
                    <div class="floating-purity-tag">
                        <span class="purity-title">TASTE OF INDIA</span>
                        <span class="purity-subtitle">Trusted by 100M+ Families</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
HTML;

file_put_contents($tempDir . '/index.html', $html);

// 2. style.css
$css = <<<CSS
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    color: #ffffff;
    background-color: #064e3b;
    overflow-x: hidden;
}

.amul-hero-container {
    position: relative;
    width: 100%;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 60px 24px;
}

.hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center center;
    transform: scale(1.03);
    transition: transform 6s cubic-bezier(0.25, 1, 0.5, 1);
    z-index: 1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(6, 40, 28, 0.94) 0%, rgba(6, 78, 59, 0.82) 45%, rgba(6, 78, 59, 0.4) 100%);
    z-index: 2;
}

.hero-content-wrapper {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1200px;
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 48px;
    align-items: center;
}

/* Left Column Styling */
.hero-text-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 9999px;
    width: fit-content;
}

.badge-dot {
    width: 8px;
    height: 8px;
    background-color: #34d399;
    border-radius: 50%;
    box-shadow: 0 0 10px #34d399;
    animation: pulseDot 2s infinite;
}

@keyframes pulseDot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.badge-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #ecfdf5;
    text-transform: uppercase;
}

.main-headline {
    font-size: 44px;
    font-weight: 800;
    line-height: 1.15;
    color: #ffffff;
    letter-spacing: -0.02em;
}

.highlight-text {
    background: linear-gradient(135deg, #fef08a 0%, #38bdf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-family: 'Playfair Display', serif;
    font-style: italic;
}

.hero-description {
    font-size: 15px;
    line-height: 1.6;
    color: #d1fae5;
    max-width: 540px;
}

/* Pricing & Discount Badge */
.pricing-deal-box {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 4px;
}

.price-stack {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.price-currency {
    font-size: 20px;
    font-weight: 700;
    color: #fef08a;
}

.price-val {
    font-size: 34px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.03em;
}

.price-unit {
    font-size: 13px;
    color: #a7f3d0;
    font-weight: 500;
}

.discount-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #e11d48;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    border-radius: 8px;
    letter-spacing: 0.05em;
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
}

/* CTA Action Row */
.cta-action-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.primary-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    padding: 14px 28px;
    border-radius: 14px;
    text-decoration: none;
    box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
    transition: all 0.25s ease;
}

.primary-cta-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px -5px rgba(16, 185, 129, 0.65);
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.cta-arrow {
    width: 18px;
    height: 18px;
    transition: transform 0.2s ease;
}

.primary-cta-btn:hover .cta-arrow {
    transform: translateX(4px);
}

.delivery-guarantee {
    font-size: 13px;
    font-weight: 600;
    color: #a7f3d0;
}

/* Feature Badges Strip */
.feature-strip {
    display: flex;
    align-items: center;
    gap: 24px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    flex-wrap: wrap;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.feature-icon {
    font-size: 16px;
}

.feature-text {
    font-size: 12px;
    font-weight: 600;
    color: #d1fae5;
}

/* Right Visual Column */
.hero-visual-col {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.visual-card-glass {
    position: relative;
    width: 100%;
    max-width: 420px;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    transition: transform 0.4s ease;
}

.visual-card-glass:hover {
    transform: translateY(-6px);
}

.product-pack-image {
    width: 100%;
    max-height: 380px;
    object-fit: contain;
    filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.45));
    animation: floatPack 4s ease-in-out infinite;
}

@keyframes floatPack {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.floating-purity-tag {
    position: absolute;
    bottom: -15px;
    background: #ffffff;
    color: #064e3b;
    padding: 10px 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid #e2e8f0;
}

.purity-title {
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.05em;
    color: #047857;
}

.purity-subtitle {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
}

/* Responsive Media Queries */
@media (max-width: 960px) {
    .hero-content-wrapper {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }

    .hero-text-col {
        align-items: center;
    }

    .badge-pill, .pricing-deal-box, .cta-action-row, .feature-strip {
        justify-content: center;
    }

    .main-headline {
        font-size: 34px;
    }
}

@media (max-width: 480px) {
    .amul-hero-container {
        padding: 40px 16px;
    }

    .main-headline {
        font-size: 28px;
    }

    .price-val {
        font-size: 28px;
    }

    .primary-cta-btn {
        width: 100%;
        justify-content: center;
    }
}
CSS;

file_put_contents($tempDir . '/style.css', $css);

// 3. script.js
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.visual-card-glass');
    const hero = document.querySelector('.amul-hero-container');

    if (hero && card && window.innerWidth > 960) {
        hero.addEventListener('mousemove', function(e) {
            const rect = hero.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            card.style.transform = `perspective(1000px) rotateY(\${x * 12}deg) rotateX(\${-y * 12}deg) translateY(-4px)`;
        });

        hero.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(0px)';
        });
    }
});
JS;

file_put_contents($tempDir . '/script.js', $js);

// 4. Create ZIP archive in public directory
$destinationZip = 'c:\\Users\\jmrat\\Desktop\\shivoham\\public\\amul_milk_hero_banner.zip';

$zip = new ZipArchive();
if ($zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $zip->addFile($tempDir . '/index.html', 'index.html');
    $zip->addFile($tempDir . '/style.css', 'style.css');
    $zip->addFile($tempDir . '/script.js', 'script.js');
    $zip->addFile($assetsDir . '/amul-milk-pack.jpg', 'assets/amul-milk-pack.jpg');
    $zip->addFile($assetsDir . '/farm-bg.jpg', 'assets/farm-bg.jpg');
    $zip->close();
    echo "ZIP_SUCCESS: " . $destinationZip . "\n";
} else {
    echo "ZIP_ERROR\n";
}
