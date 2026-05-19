<?php
require_once 'core/functions.php';

// Already logged in → redirect to catalogue
if (isLoggedIn()) {
    redirect('views/catalogue.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Library - Arabic Books Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── Hero Section ────────────────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-primary) 0%, rgba(108, 99, 255, 0.05) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(108, 99, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 700px;
            padding: 2rem;
        }

        .hero-content h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, var(--accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-buttons .btn {
            padding: 0.8rem 2rem;
            font-size: 1rem;
        }

        /* ── Features Section ────────────────────────────────────────────────── */
        .features {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 2rem 1.5rem;
        }

        .features h2 {
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            color: #fff;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            transition: all var(--transition);
        }

        .feature-card:hover {
            border-color: var(--accent);
            box-shadow: 0 0 30px var(--accent-glow);
            transform: translateY(-4px);
        }

        .feature-card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #fff;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* ── Stats Section ────────────────────────────────────────────────── */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* ── CTA Section ──────────────────────────────────────────────────────── */
        .cta-section {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 3rem;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.1) 0%, rgba(108, 99, 255, 0.05) 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #fff;
        }

        .cta-section p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        /* ── Footer ──────────────────────────────────────────────────────── */
        .footer {
            margin-top: 4rem;
            padding: 2rem 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-secondary);
        }

        .footer p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .hero::before {
                width: 300px;
                height: 300px;
                right: -50px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-buttons .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="navbar__inner">
        <a href="index.php" class="navbar__brand">📚 <span>Library</span></a>
        <div class="navbar__links">
            <a href="views/login.php" class="btn btn-secondary">Sign In</a>
            <a href="views/register.php" class="btn btn-primary">Register</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>📚 Welcome to Library</h1>
        <p>Explore thousands of Arabic books, rent or buy at your convenience. Your gateway to the world of Arabic literature.</p>
        <div class="hero-buttons">
            <a href="views/login.php" class="btn btn-primary">Sign In</a>
            <a href="views/register.php" class="btn btn-secondary">Create Account</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <h2>✨ Why Choose Library?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-card-icon">📖</div>
            <h3>Vast Collection</h3>
            <p>Browse thousands of Arabic books covering all genres and categories.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card-icon">💰</div>
            <h3>Flexible Options</h3>
            <p>Buy books permanently or rent them monthly at affordable prices.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card-icon">🚚</div>
            <h3>Easy Checkout</h3>
            <p>Streamlined checkout process with secure payment options.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card-icon">⭐</div>
            <h3>Quality Content</h3>
            <p>Curated selection of popular and award-winning Arabic books.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card-icon">📱</div>
            <h3>Responsive Design</h3>
            <p>Access from any device - perfectly optimized for mobile, tablet & desktop.</p>
        </div>
        <div class="feature-card">
            <div class="feature-card-icon">🔒</div>
            <h3>Secure & Safe</h3>
            <p>Your data is protected with industry-standard security practices.</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="stat-item">
        <div class="stat-number">5000+</div>
        <div class="stat-label">Books Available</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">10K+</div>
        <div class="stat-label">Happy Readers</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">50+</div>
        <div class="stat-label">Authors</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">4.8★</div>
        <div class="stat-label">User Rating</div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <h2>🚀 Ready to Start Reading?</h2>
    <p>Join thousands of readers enjoying Arabic literature on our platform</p>
    <a href="views/register.php" class="btn btn-primary">Get Started Now</a>
</section>

<!-- Footer -->
<footer class="footer">
    <p>&copy; 2026 Library Platform. All rights reserved. | Crafted with ❤️ for Arabic readers</p>
</footer>

</body>
</html>
