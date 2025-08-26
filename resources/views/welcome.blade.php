<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faxt - Multi-Tenant SaaS Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(98, 98, 65, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(98, 98, 65, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #626241;
            margin-bottom: 20px;
            line-height: 1.1;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-text .subtitle {
            font-size: 1.5rem;
            color: #4b5563;
            margin-bottom: 30px;
            font-weight: 300;
            animation: fadeInUp 1s ease-out 0.2s both;
        }
        
        .hero-text p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 40px;
            animation: fadeInUp 1s ease-out 0.4s both;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            animation: fadeInUp 1s ease-out 0.6s both;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #626241;
            color: white;
            box-shadow: 0 4px 15px rgba(98, 98, 65, 0.3);
        }
        
        .btn-primary:hover {
            background: #4a4a31;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(98, 98, 65, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: #626241;
            border: 2px solid #626241;
        }
        
        .btn-secondary:hover {
            background: #626241;
            color: white;
            transform: translateY(-2px);
        }
        
        .hero-visual {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeInRight 1s ease-out 0.4s both;
        }
        
        /* Features Section */
        .features {
            padding: 100px 0;
            background: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: #626241;
            margin-bottom: 20px;
        }
        
        .section-title p {
            font-size: 1.2rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #626241 0%, #7a7c56 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: white;
            font-size: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #6b7280;
            line-height: 1.7;
        }
        
        /* How It Works Section */
        .how-it-works {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .step {
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: #626241;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .step h3 {
            font-size: 1.2rem;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .step p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: #626241;
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .btn-white {
            background: white;
            color: #626241;
        }
        
        .btn-white:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }
        
        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            margin-bottom: 20px;
            color: #626241;
        }
        
        .footer-section p,
        .footer-section a {
            color: #9ca3af;
            text-decoration: none;
            line-height: 1.8;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #374151;
            color: #9ca3af;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 40px;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .cta-buttons {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .process-steps {
                grid-template-columns: 1fr;
            }
        }
        
        /* Network Animation */
        .network-node {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .network-node:nth-child(2) { animation-delay: 0.2s; }
        .network-node:nth-child(3) { animation-delay: 0.4s; }
        .network-node:nth-child(4) { animation-delay: 0.6s; }
        .network-node:nth-child(5) { animation-delay: 0.8s; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Build Scalable Multi-Tenant SaaS</h1>
                    <p class="subtitle">Powered by Laravel Plugin Architecture</p>
                    <p>Faxt provides a robust foundation for creating and hosting multiple independent SaaS applications on a single codebase. Deploy tenant sites with complete data isolation, custom branding, and modular functionality.</p>
                    <div class="cta-buttons">
                        <a href="#get-started" class="btn btn-primary">Get Started →</a>
                        <a href="#features" class="btn btn-secondary">Learn More</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background -->
                        <rect width="400" height="300" fill="transparent"/>
                        
                        <!-- Main Logo -->
                        <text x="200" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="48" font-weight="bold" fill="#626241">
                            FAXT
                        </text>
                        
                        <!-- Network diagram -->
                        <g transform="translate(200, 150)">
                            <!-- Central hub -->
                            <circle cx="0" cy="0" r="15" fill="#626241" class="network-node"/>
                            
                            <!-- Connected nodes -->
                            <g>
                                <!-- Top node -->
                                <circle cx="0" cy="-50" r="10" fill="#7a7c56" class="network-node"/>
                                <line x1="0" y1="-15" x2="0" y2="-40" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Top right node -->
                                <circle cx="35" cy="-35" r="10" fill="#8e9168" class="network-node"/>
                                <line x1="11" y1="-11" x2="24" y2="-24" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Right node -->
                                <circle cx="50" cy="0" r="10" fill="#7a7c56" class="network-node"/>
                                <line x1="15" y1="0" x2="40" y2="0" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Bottom right node -->
                                <circle cx="35" cy="35" r="10" fill="#8e9168" class="network-node"/>
                                <line x1="11" y1="11" x2="24" y2="24" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Bottom node -->
                                <circle cx="0" cy="50" r="10" fill="#7a7c56" class="network-node"/>
                                <line x1="0" y1="15" x2="0" y2="40" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Bottom left node -->
                                <circle cx="-35" cy="35" r="10" fill="#8e9168" class="network-node"/>
                                <line x1="-11" y1="11" x2="-24" y2="24" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Left node -->
                                <circle cx="-50" cy="0" r="10" fill="#7a7c56" class="network-node"/>
                                <line x1="-15" y1="0" x2="-40" y2="0" stroke="#626241" stroke-width="2"/>
                                
                                <!-- Top left node -->
                                <circle cx="-35" cy="-35" r="10" fill="#8e9168" class="network-node"/>
                                <line x1="-11" y1="-11" x2="-24" y2="-24" stroke="#626241" stroke-width="2"/>
                            </g>
                        </g>
                        
                        <!-- Labels -->
                        <text x="200" y="250" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#626241">
                            Multi-Tenant Architecture
                        </text>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Faxt?</h2>
                <p>Everything you need to build and scale multi-tenant SaaS applications with Laravel</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3>Multi-Tenant Architecture</h3>
                    <p>Complete data isolation and independent configurations for each tenant. Support for custom domains and automated subdomain provisioning.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔧</div>
                    <h3>Laravel Plugin System</h3>
                    <p>Extend functionality through modular Laravel packages. Add features without affecting other tenants or the core platform.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3>SaaS-Ready Foundation</h3>
                    <p>Built-in tenant management, subscription handling, and billing integration. Everything needed to launch your SaaS business.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>API-First Design</h3>
                    <p>RESTful APIs for seamless integration with mobile apps, third-party services, and custom applications.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎛️</div>
                    <h3>Admin Panel Integration</h3>
                    <p>Powered by Filament for intuitive administration across all tenants. Manage everything from a single dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Scalable & Flexible</h3>
                    <p>Deploy new tenant sites with minimal development. Automatic tenant provisioning and growth-ready architecture.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Get your multi-tenant SaaS platform up and running in five simple steps</p>
            </div>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Setup</h3>
                    <p>Deploy the Faxt platform on your infrastructure with multi-tenancy configurations</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Configure</h3>
                    <p>Set up database connections, routing, and install base Laravel packages</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Develop</h3>
                    <p>Create custom plugins and packages for your specific business requirements</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Deploy</h3>
                    <p>Launch tenant sites with customized features and independent branding</p>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <h3>Scale</h3>
                    <p>Grow your SaaS offering with automatic tenant provisioning and updates</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="get-started" class="cta-section">
        <div class="container">
            <h2>Ready to Build Your SaaS Platform?</h2>
            <p>Join developers worldwide who trust Faxt for their multi-tenant Laravel applications</p>
            <div class="cta-buttons">
                <a href="https://github.com/prasso/prasso_api" class="btn btn-white">View on GitHub →</a>
                <a href="mailto:info@faxt.com" class="btn btn-secondary" style="border-color: white; color: white;">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Faxt Platform</h3>
                    <p>Building scalable SaaS solutions with Laravel made simple. From Prasso to Faxt, we continue to innovate in multi-tenant architecture.</p>
                </div>
                <div class="footer-section">
                    <h3>Resources</h3>
                    <p><a href="https://github.com/prasso/prasso_api">GitHub Repository</a></p>
                    <p><a href="https://github.com/prasso/prasso_api/blob/master/docs/contributing.md">Contributing Guide</a></p>
                    <p><a href="#">Documentation</a></p>
                </div>
                <div class="footer-section">
                    <h3>Technologies</h3>
                    <p>Laravel Framework</p>
                    <p>Multi-Tenant Architecture</p>
                    <p>Plugin System</p>
                    <p>Filament Admin</p>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p><a href="mailto:info@faxt.com">info@faxt.com</a></p>
                    <p>Support & Partnerships</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Faxt. Licensed under MIT License. Building the future of multi-tenant SaaS.</p>
            </div>
        </div>
    </footer>
</body>
</html>