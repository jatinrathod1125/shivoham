<?php

$sourceProductImg = 'C:\\Users\\jmrat\\.gemini\\antigravity-ide\\brain\\561e3e43-f883-48bd-b13e-0b3f3cf2e3f9\\almonds_product_pack_1787564448747.jpg';
$sourceBgImg = 'C:\\Users\\jmrat\\.gemini\\antigravity-ide\\brain\\561e3e43-f883-48bd-b13e-0b3f3cf2e3f9\\almonds_nature_bg_1787564479119.jpg';

$tempDir = sys_get_temp_dir() . '/california_almonds_' . time();
$assetsDir = $tempDir . '/assets';

if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0777, true);
}

copy($sourceProductImg, $assetsDir . '/california-almonds-pouch.jpg');
copy($sourceBgImg, $assetsDir . '/orchard-sunlight-bg.jpg');

// 1. index.html
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>California Almonds NUTS - Pure Goodness Naturally</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="almonds-hero-section">
        <!-- Background Nature Layer -->
        <div class="hero-nature-bg" style="background-image: url('assets/orchard-sunlight-bg.jpg');"></div>
        <div class="hero-light-gradient"></div>

        <!-- Floating Particles & Leaves -->
        <div class="floating-particle leaf-1">🍃</div>
        <div class="floating-particle leaf-2">🌿</div>
        <div class="floating-particle nut-1">🥜</div>
        <div class="floating-particle nut-2">✨</div>

        <div class="hero-main-container">
            <!-- Left Copy Column -->
            <div class="hero-copy-column">
                <div class="quality-tag">
                    <span class="tag-bar"></span>
                    <span class="tag-text">RICH & FINEST QUALITY</span>
                </div>

                <div class="brand-title-lockup">
                    <span class="script-cursive">California</span>
                    <h1 class="brand-heavy-title">ALMONDS</h1>
                    <div class="sub-divider">
                        <span class="divider-line"></span>
                        <span class="divider-text">NUTS</span>
                        <span class="divider-line"></span>
                    </div>
                </div>

                <h2 class="sub-headline">Pure Goodness. Naturally.</h2>

                <p class="body-description">
                    Handpicked from the finest farms of California, our almonds are packed with nutrition, flavor and natural goodness.
                </p>

                <div class="cta-price-row">
                    <a href="/products/california-almonds-500g" class="shop-now-btn">
                        <span>Shop Now</span>
                        <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>

                    <div class="pricing-pill">
                        <span class="price-symbol">₹</span>
                        <span class="price-amount">499</span>
                        <span class="price-weight">/ 500g</span>
                        <span class="save-tag">SAVE 25%</span>
                    </div>
                </div>

                <!-- Bottom 3 Feature Pills -->
                <div class="feature-badges-grid">
                    <div class="badge-card">
                        <div class="badge-icon-box">🍃</div>
                        <div class="badge-copy">
                            <span class="badge-title">100%</span>
                            <span class="badge-sub">NATURAL</span>
                        </div>
                    </div>

                    <div class="badge-card">
                        <div class="badge-icon-box">💚</div>
                        <div class="badge-copy">
                            <span class="badge-title">RICH IN</span>
                            <span class="badge-sub">NUTRITION</span>
                        </div>
                    </div>

                    <div class="badge-card">
                        <div class="badge-icon-box">🛡️</div>
                        <div class="badge-copy">
                            <span class="badge-title">PREMIUM</span>
                            <span class="badge-sub">QUALITY</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Showcase Column -->
            <div class="hero-showcase-column">
                <div class="product-stage-wrapper">
                    <!-- Circular 100% Pure Stamp -->
                    <div class="circular-stamp">
                        <div class="stamp-leaf">🌿</div>
                        <span class="stamp-bold">100%</span>
                        <span class="stamp-sub">PURE & NATURAL</span>
                    </div>

                    <!-- Main Stand-up Pouch & Bowl Visual -->
                    <div class="product-visual-card">
                        <img src="assets/california-almonds-pouch.jpg" alt="California Almonds 500g Stand-up Pouch and Wooden Bowl" class="product-main-photo" />
                        <div class="pack-badge-pill">
                            <span class="pack-badge-main">500g NET PACK</span>
                            <span class="pack-badge-note">Whole, Unsalted & Crisp</span>
                        </div>
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
@import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Outfit:wght@400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #1a3d24;
    background-color: #f7fee7;
    overflow-x: hidden;
}

.almonds-hero-section {
    position: relative;
    width: 100%;
    min-height: 640px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 50px 24px;
}

/* Background Layers */
.hero-nature-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center bottom;
    z-index: 1;
    filter: brightness(1.02);
}

.hero-light-gradient {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 75% 35%, rgba(254, 252, 232, 0.4) 0%, rgba(247, 254, 231, 0.75) 45%, rgba(236, 252, 203, 0.92) 100%);
    z-index: 2;
}

/* Floating Particles */
.floating-particle {
    position: absolute;
    z-index: 3;
    pointer-events: none;
    user-select: none;
}

.leaf-1 {
    top: 12%;
    left: 42%;
    font-size: 24px;
    animation: floatLeaf1 6s ease-in-out infinite;
}

.leaf-2 {
    bottom: 22%;
    left: 6%;
    font-size: 28px;
    animation: floatLeaf2 8s ease-in-out infinite;
}

.nut-1 {
    top: 18%;
    right: 12%;
    font-size: 22px;
    animation: floatLeaf1 5s ease-in-out infinite reverse;
}

.nut-2 {
    top: 48%;
    right: 46%;
    font-size: 18px;
    animation: pulseGlow 3s ease-in-out infinite;
}

@keyframes floatLeaf1 {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-16px) rotate(12deg); }
}

@keyframes floatLeaf2 {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(18px) rotate(-15deg); }
}

@keyframes pulseGlow {
    0%, 100% { opacity: 0.4; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.3); }
}

/* Main Content Wrapper */
.hero-main-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 1240px;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 40px;
    align-items: center;
}

/* Left Copy Column */
.hero-copy-column {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.quality-tag {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.tag-bar {
    width: 44px;
    height: 3.5px;
    background-color: #4d7c0f;
    border-radius: 4px;
}

.tag-text {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #365314;
}

/* Title Lockup matching original image */
.brand-title-lockup {
    display: flex;
    flex-direction: column;
    line-height: 0.9;
    margin-top: 4px;
}

.script-cursive {
    font-family: 'Dancing Script', cursive;
    font-size: 52px;
    font-weight: 700;
    color: #274c1f;
    transform: translateY(14px);
    z-index: 2;
}

.brand-heavy-title {
    font-family: 'Playfair Display', serif;
    font-size: 76px;
    font-weight: 900;
    color: #123018;
    letter-spacing: -0.01em;
    text-transform: uppercase;
}

.sub-divider {
    display: flex;
    align-items: center;
    gap: 14px;
    max-width: 320px;
    margin-top: 8px;
}

.divider-line {
    flex: 1;
    height: 1.5px;
    background-color: #123018;
}

.divider-text {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0.28em;
    color: #123018;
}

.sub-headline {
    font-size: 22px;
    font-weight: 800;
    color: #14381e;
    letter-spacing: -0.01em;
    margin-top: 6px;
}

.body-description {
    font-size: 15px;
    line-height: 1.6;
    color: #365314;
    max-width: 480px;
    font-weight: 500;
}

/* CTA & Pricing Row */
.cta-price-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.shop-now-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #4d701a;
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    padding: 13px 32px;
    border-radius: 9999px;
    text-decoration: none;
    box-shadow: 0 10px 20px -3px rgba(77, 112, 26, 0.45);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.shop-now-btn:hover {
    background: #3c5713;
    transform: translateY(-2px);
    box-shadow: 0 14px 26px -3px rgba(77, 112, 26, 0.6);
}

.btn-arrow {
    width: 18px;
    height: 18px;
    transition: transform 0.2s ease;
}

.shop-now-btn:hover .btn-arrow {
    transform: translateX(4px);
}

.pricing-pill {
    display: inline-flex;
    align-items: baseline;
    gap: 4px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(101, 163, 13, 0.3);
    border-radius: 9999px;
}

.price-symbol {
    font-size: 16px;
    font-weight: 800;
    color: #4d701a;
}

.price-amount {
    font-size: 26px;
    font-weight: 900;
    color: #14381e;
    letter-spacing: -0.02em;
}

.price-weight {
    font-size: 12px;
    color: #65a30d;
    font-weight: 600;
}

.save-tag {
    font-size: 10px;
    font-weight: 800;
    background: #e11d48;
    color: #ffffff;
    padding: 2px 7px;
    border-radius: 6px;
    margin-left: 6px;
}

/* Feature Badges Grid */
.feature-badges-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    max-width: 490px;
    margin-top: 14px;
}

.badge-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 18px;
    box-shadow: 0 8px 20px -6px rgba(40, 70, 20, 0.12);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.badge-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -6px rgba(40, 70, 20, 0.2);
}

.badge-icon-box {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border-radius: 10px;
    background: #ecfccb;
}

.badge-copy {
    display: flex;
    flex-direction: column;
}

.badge-title {
    font-size: 12px;
    font-weight: 800;
    color: #14381e;
    line-height: 1.1;
}

.badge-sub {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #4d7c0f;
}

/* Right Showcase Column */
.hero-showcase-column {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.product-stage-wrapper {
    position: relative;
    width: 100%;
    max-width: 480px;
    display: flex;
    justify-content: center;
}

/* Circular Stamp */
.circular-stamp {
    position: absolute;
    top: 25px;
    right: -10px;
    z-index: 20;
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 2px dashed #4d701a;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(40, 70, 20, 0.18);
    animation: rotateStamp 20s linear infinite;
}

@keyframes rotateStamp {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.stamp-leaf {
    font-size: 14px;
}

.stamp-bold {
    font-size: 13px;
    font-weight: 900;
    color: #14381e;
    line-height: 1;
}

.stamp-sub {
    font-size: 7.5px;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: #4d701a;
    text-align: center;
    margin-top: 2px;
}

/* Product Card */
.product-visual-card {
    position: relative;
    width: 100%;
    border-radius: 32px;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(16px);
    border: 1.5px solid rgba(255, 255, 255, 0.85);
    padding: 16px;
    box-shadow: 0 30px 60px -15px rgba(25, 55, 15, 0.25);
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.product-visual-card:hover {
    transform: translateY(-8px);
}

.product-main-photo {
    width: 100%;
    max-height: 440px;
    object-fit: cover;
    border-radius: 22px;
    box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.15);
}

.pack-badge-pill {
    position: absolute;
    bottom: -12px;
    background: #ffffff;
    color: #14381e;
    padding: 8px 24px;
    border-radius: 9999px;
    box-shadow: 0 10px 25px rgba(20, 50, 10, 0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid #d9f99d;
}

.pack-badge-main {
    font-size: 13px;
    font-weight: 900;
    letter-spacing: 0.06em;
    color: #365314;
}

.pack-badge-note {
    font-size: 10px;
    font-weight: 600;
    color: #65a30d;
}

/* Responsive Media Queries */
@media (max-width: 1024px) {
    .hero-main-container {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }

    .hero-copy-column {
        align-items: center;
    }

    .quality-tag {
        align-items: center;
    }

    .sub-divider {
        margin: 8px auto 0;
    }

    .cta-price-row {
        justify-content: center;
    }

    .feature-badges-grid {
        margin: 14px auto 0;
    }

    .brand-heavy-title {
        font-size: 58px;
    }

    .script-cursive {
        font-size: 42px;
    }
}

@media (max-width: 600px) {
    .almonds-hero-section {
        padding: 36px 16px;
    }

    .brand-heavy-title {
        font-size: 42px;
    }

    .script-cursive {
        font-size: 34px;
    }

    .sub-headline {
        font-size: 18px;
    }

    .feature-badges-grid {
        grid-template-columns: 1fr;
        max-width: 280px;
    }

    .shop-now-btn {
        width: 100%;
        justify-content: center;
    }

    .circular-stamp {
        display: none;
    }
}
CSS;

file_put_contents($tempDir . '/style.css', $css);

// 3. script.js
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.product-visual-card');
    const hero = document.querySelector('.almonds-hero-section');

    if (hero && card && window.innerWidth > 1024) {
        hero.addEventListener('mousemove', function(e) {
            const rect = hero.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            card.style.transform = `perspective(1000px) rotateY(\${x * 10}deg) rotateX(\${-y * 10}deg) translateY(-6px)`;
        });

        hero.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(0px)';
        });
    }
});
JS;

file_put_contents($tempDir . '/script.js', $js);

// Destination ZIP locations:
// 1. In root of shivoham folder: c:\Users\jmrat\Desktop\shivoham\california_almonds_hero_banner.zip
$destShivohamRoot = 'c:\\Users\\jmrat\\Desktop\\shivoham\\california_almonds_hero_banner.zip';
// 2. On user's Desktop
$destDesktop = 'c:\\Users\\jmrat\\Desktop\\california_almonds_hero_banner.zip';
// 3. In public folder
$destPublic = 'c:\\Users\\jmrat\\Desktop\\shivoham\\public\\california_almonds_hero_banner.zip';

$zip = new ZipArchive();
if ($zip->open($destShivohamRoot, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $zip->addFile($tempDir . '/index.html', 'index.html');
    $zip->addFile($tempDir . '/style.css', 'style.css');
    $zip->addFile($tempDir . '/script.js', 'script.js');
    $zip->addFile($assetsDir . '/california-almonds-pouch.jpg', 'assets/california-almonds-pouch.jpg');
    $zip->addFile($assetsDir . '/orchard-sunlight-bg.jpg', 'assets/orchard-sunlight-bg.jpg');
    $zip->close();
    
    copy($destShivohamRoot, $destDesktop);
    copy($destShivohamRoot, $destPublic);
    
    echo "ZIP_GENERATED_SUCCESSFULLY:\n";
    echo "1. " . $destShivohamRoot . "\n";
    echo "2. " . $destDesktop . "\n";
    echo "3. " . $destPublic . "\n";
} else {
    echo "ZIP_CREATION_FAILED\n";
}
