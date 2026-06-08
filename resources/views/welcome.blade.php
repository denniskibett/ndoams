<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ App\Helpers\SystemHelper::metaDescription() ?: 'National Digital Online Archival Marriage System - Digitizing Kenya\'s marriage records for faster, secure, and reliable access.' }}">
    
    <title>{{ App\Helpers\SystemHelper::appName() }} | {{ App\Helpers\SystemHelper::slogan() ?: 'Digital Marriage Records Archive' }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ App\Helpers\SystemHelper::faviconUrl() }}" type="image/x-icon">
    
    <!-- Outfit Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-color: {{ App\Helpers\SystemHelper::primaryColor() }};
            --secondary-color: {{ App\Helpers\SystemHelper::secondaryColor() }};
        }
        
        body {
            font-family: 'Outfit', sans-serif;
        }
        
        .bg-primary { background-color: var(--primary-color); }
        .bg-secondary { background-color: var(--secondary-color); }
        .text-primary { color: var(--primary-color); }
        .text-secondary { color: var(--secondary-color); }
        .border-primary { border-color: var(--primary-color); }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .grid-pattern {
            background-image: radial-gradient(circle at 1px 1px, #e5e7eb 1px, transparent 0);
            background-size: 40px 40px;
        }
        
        /* Hero overlay for text readability */
        .hero-overlay {
            background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.7) 50%, rgba(0,0,0,0.85) 100%);
        }

        /* Glass button style */
        .glass-button {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .glass-button:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }

        /* Glass effect when scrolled */
        .nav-glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.15);
        }

        /* Partner logo styles */
        .partner-logo-wrapper {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .partner-logo-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.2);
        }
    </style>
    
    @if(App\Helpers\SystemHelper::customCss())
        <style>{!! App\Helpers\SystemHelper::customCss() !!}</style>
    @endif
</head>
<body class="antialiased bg-white min-h-screen flex flex-col">

    <!-- Simple Navigation with Mobile Menu -->
    <nav id="mainNav" class="w-full py-4 md:py-6 bg-transparent transition-all duration-300 ease-in-out fixed top-0 z-50">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-2 md:space-x-3">
                    <img src="{{ App\Helpers\SystemHelper::logoUrl() }}" alt="{{ App\Helpers\SystemHelper::appName() }}" class="h-8 md:h-10 w-auto">
                    <span class="text-lg md:text-xl font-semibold text-white">{{ App\Helpers\SystemHelper::appName() }}</span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
                    <a href="#about" class="text-white/90 hover:text-white transition text-sm lg:text-base">About</a>
                    <a href="#partners" class="text-white/90 hover:text-white transition text-sm lg:text-base">Partners</a>
                    <a href="{{ route('login') }}" class="glass-button text-white px-5 lg:px-6 py-2 rounded-full text-sm font-medium flex items-center space-x-2"><span>Login</span></a>
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="flex items-center space-x-3 md:hidden">
                    <!-- Hamburger Menu Only - Login removed -->
                    <button id="mobileMenuBtn" class="text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu Dropdown -->
            <div id="mobileMenu" class="hidden mt-4 md:hidden bg-white/10 backdrop-blur-lg rounded-2xl p-4 border border-white/20">
                <div class="flex flex-col space-y-3">
                    <a href="#about" class="text-white/90 hover:text-white transition py-2 px-3 rounded-lg hover:bg-white/10 text-sm">
                        <i class="fas fa-info-circle mr-2 w-5"></i>About
                    </a>
                    <a href="#partners" class="text-white/90 hover:text-white transition py-2 px-3 rounded-lg hover:bg-white/10 text-sm">
                        <i class="fas fa-handshake mr-2 w-5"></i>Partners
                    </a>
                <a href="{{ route('login') }}" class="glass-button text-white px-5 lg:px-6 py-2 rounded-full text-sm font-medium flex items-center space-x-2">
                        <span>Login</span>
                    </a>
                    <!-- Login link removed from mobile menu -->
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Full Screen with Marriage Image -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Image - Marriage/Couple Theme -->
        <div class="absolute inset-0">
             <img src="https://images.unsplash.com/photo-1469371670807-013ccf25f16a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                 alt="Kenyan Wedding Celebration" 
                 class="w-full h-full object-cover">
            <!-- Dark Overlay for Text Readability -->
            <div class="absolute inset-0 hero-overlay"></div>
        </div>
        
        <!-- Gradient Orbs -->
        <div class="absolute top-20 left-10 w-96 h-96 bg-blue-500/20 rounded-full mix-blend-overlay filter blur-3xl floating"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-cyan-500/20 rounded-full mix-blend-overlay filter blur-3xl floating" style="animation-delay: 2s;"></div>
        
        <div class="container mx-auto px-6 py-16 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge -->
                <div class="inline-flex items-center bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium text-white border border-white/20 mb-8">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                    Phase 1 Deployment • 2026
                </div>
                
                <!-- Main Title -->
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
                    National Digital Online
                    <span class="gradient-text bg-gradient-to-r from-blue-300 via-white to-cyan-300 bg-clip-text text-transparent">Archival Marriage System</span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl md:text-2xl text-gray-200 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Digitizing, verifying, and securely archiving Kenya's marriage records from independence to present day. 
                    Transforming service delivery from weeks to minutes.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-4 justify-center mb-16">
                    <a href="#about" class="gradient-bg text-white px-8 py-4 rounded-full font-medium text-lg shadow-lg shadow-blue-500/30 hover:shadow-xl transition-all hover:-translate-y-1">
                        <i class="fas fa-play-circle mr-2"></i>
                        Our Mission
                    </a>
                    <a href="#partners" class="bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-full font-medium text-lg border border-white/30 hover:bg-white/20 transition-all hover:-translate-y-1">
                        <i class="fas fa-handshake mr-2"></i>
                        Our Partners
                    </a>
                </div>
                
                <!-- Simple Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">1963</div>
                        <div class="text-sm text-gray-300">Independence Year</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">60+</div>
                        <div class="text-sm text-gray-300">Years of Records</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">20M+</div>
                        <div class="text-sm text-gray-300">Records to Archive</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">Minutes</div>
                        <div class="text-sm text-gray-300">vs Weeks Before</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Digitize Now Section - Historical Importance -->
    <!-- Historical Timeline Section -->
<section id="about" class="py-20 bg-gray-50 overflow-hidden">
    <div class="container mx-auto px-6">
        <div class="max-w-3xl mx-auto text-center mb-16">
            <span class="text-sm font-semibold text-primary uppercase tracking-wider">Our Mission</span>
            <h2 class="text-4xl font-bold text-gray-900 mt-2">Preserving 60+ Years of Heritage</h2>
            <p class="text-xl text-gray-600 mt-4">From independence in 1963 to the present day</p>
        </div>
        
        <div class="max-w-6xl mx-auto">
            <!-- Main Timeline -->
            <div class="relative">
                <!-- Central Line -->
                <div class="absolute left-0 right-0 h-1 bg-gradient-to-r from-amber-300 via-primary to-green-400 top-1/2 transform -translate-y-1/2 rounded-full opacity-30 hidden md:block"></div>
                
                <!-- Timeline Grid -->
                <div class="grid md:grid-cols-5 gap-4 relative">
                    
                    <!-- 1963 - Independence -->
                    <div class="relative group">
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-amber-200">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform">
                                    🇰🇪
                                </div>
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-bold hidden md:block">
                                    START
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">1963</h3>
                                <p class="text-sm font-semibold text-amber-600 mb-2">Independence</p>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Kenya gains independence. Marriage records begin on paper, establishing the foundation of national documentation.
                                </p>
                                <div class="mt-3 w-full h-1 bg-amber-200 rounded-full">
                                    <div class="w-full h-full bg-amber-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 1980s - Paper Records -->
                    <div class="relative group md:mt-12">
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-amber-300">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform">
                                    📜
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">1980s</h3>
                                <p class="text-sm font-semibold text-amber-600 mb-2">Paper Archives</p>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Millions of paper records accumulated. Manual filing systems become the standard, but preservation challenges emerge.
                                </p>
                                <div class="mt-3 w-full h-1 bg-amber-100 rounded-full">
                                    <div class="w-2/3 h-full bg-amber-400 rounded-full"></div>
                                </div>
                                <span class="text-xs text-amber-600 mt-1 font-medium">+2.5M records</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2000s - Partial Scanning -->
                    <div class="relative group">
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-300">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform">
                                    💿
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">2000s</h3>
                                <p class="text-sm font-semibold text-blue-600 mb-2">Partial Digitization</p>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Early scanning efforts begin. Some records digitized, but inconsistent standards and incomplete coverage.
                                </p>
                                <div class="mt-3 w-full h-1 bg-blue-100 rounded-full">
                                    <div class="w-1/3 h-full bg-blue-400 rounded-full"></div>
                                </div>
                                <span class="text-xs text-blue-600 mt-1 font-medium">~30% digitized</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2026 - NDOAMS Phase 1 -->
                    <div class="relative group md:mt-12">
                        <div class="bg-white rounded-2xl p-6 shadow-xl border-2 border-primary relative overflow-hidden">
                            <!-- Active Pulse -->
                            <div class="absolute top-0 right-0 w-20 h-20 bg-primary/10 rounded-full -mr-10 -mt-10 animate-ping"></div>
                            <div class="absolute top-2 right-2 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                            
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center text-3xl mb-4 text-white group-hover:scale-110 transition-transform">
                                    🚀
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">2026</h3>
                                <p class="text-sm font-semibold text-primary mb-2">NDOAMS Phase 1</p>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    National Digital Online Archival Marriage System launches. Comprehensive digitization of all historical records begins.
                                </p>
                                <div class="mt-3 w-full h-1 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="w-3/4 h-full bg-gradient-to-r from-primary to-secondary rounded-full relative">
                                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-primary mt-1 font-medium">Phase 1: 75%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Future - Complete Archive -->
                    <div class="relative group">
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-dashed border-green-300 hover:border-green-500">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-3xl mb-4 group-hover:scale-110 transition-transform">
                                    🔮
                                </div>
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold hidden md:block">
                                    FUTURE
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">2030+</h3>
                                <p class="text-sm font-semibold text-green-600 mb-2">Complete Archive</p>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Every marriage record since 1963 preserved, searchable, and accessible to all Kenyans in minutes.
                                </p>
                                <div class="mt-3 w-full h-1 bg-green-100 rounded-full">
                                    <div class="w-0 h-full bg-green-400 rounded-full"></div>
                                </div>
                                <span class="text-xs text-green-600 mt-1 font-medium">Target: 100%</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <!-- Impact Message -->
            <div class="mt-12 text-center p-8 bg-gradient-to-r from-amber-50 via-white to-green-50 rounded-3xl shadow-inner">
                <p class="text-lg text-gray-700 max-w-3xl mx-auto">
                    <span class="font-bold text-amber-600">1963:</span> A nation is born, records begin on paper. 
                    <span class="font-bold text-primary">2026:</span> We digitize the past to secure the future. 
                    <span class="font-bold text-green-600">Tomorrow:</span> Every Kenyan's marriage story, preserved forever.
                </p>
            </div>
        </div>
    </div>
</section>
    <!-- Partners Section - Clean and Focused with Real Logos -->
    <section id="partners" class="py-20">
        <div class="container mx-auto px-6">
            <div class="flex flex-wrap justify-center items-center gap-8 md:gap-12">
                <!-- Office of the Attorney General -->
                    <img src="https://media.licdn.com/dms/image/v2/C4D0BAQG55OwkdlhSyw/company-logo_200_200/company-logo_200_200/0/1630462707618/office_of_the_attorney_general_and_department_of_justice_logo?e=2147483647&v=beta&t=50pdB4vUaqgvo4PKM7KAOpyD-LcD-dMF68dBGSe69i4"
                        alt="Office of the Attorney General and Department of Justice"
                        class="h-20 w-20 md:h-24 md:w-24 object-contain">
                
                <!-- Directorate of eCitizen -->
                    <img src="https://play-lh.googleusercontent.com/ZeUbGeppUBpXKBU2vZBvxgTPZAgmjbofYSJ0Qd5eQAfayDe6btteTb4Py1raZP7M4b_o"
                        alt="Directorate of eCitizen"
                        class="h-20 w-20 md:h-24 md:w-24 object-contain">
                
                <!-- Konza Technopolis -->
                    <img src="https://konza.go.ke/wp-content/uploads/2024/09/cropped-Konza-Technopolis-logo.png"
                        alt="Konza Technopolis"
                        class="h-20 w-20 md:h-24 md:w-24 object-contain">
                </div>
            </div>
        </div>
    </section>

    <!-- Simple Footer - Centered -->
    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="text-sm text-gray-500">
                    © {{ date('Y') }} All rights reserved
                </div>
            </div>
        </div>
    </footer>

    <!-- Simplified JavaScript -->
<script>
    // Smooth Scroll for all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
    });

    // Navbar elements
    const nav = document.getElementById('mainNav');
    const navTitle = nav.querySelector('span');
    const navLinks = nav.querySelectorAll('a:not(.glass-button):not(.mobile-only)');
    const glassButton = nav.querySelector('.glass-button');
    
    // Mobile menu elements
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    // Mobile menu toggle
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking a link
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
    }

    // Scroll handler for navbar
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            // Desktop navbar glass effect
            nav.classList.add('nav-glass');
            navTitle.classList.remove('text-white');
            navTitle.classList.add('text-gray-900');
            
            // Update desktop nav links
            navLinks.forEach(link => {
                link.classList.remove('text-white/90', 'hover:text-white');
                link.classList.add('text-gray-600', 'hover:text-gray-900');
            });
            
            // Update glass button
            if (glassButton) {
                glassButton.classList.remove('glass-button');
                glassButton.classList.add('gradient-bg', 'text-white');
            }
            
            // Mobile menu styling when scrolled
            if (mobileMenu) {
                mobileMenu.classList.remove('bg-white/10', 'border-white/20');
                mobileMenu.classList.add('bg-white/95', 'border-gray-200');
                mobileMenu.querySelectorAll('a').forEach(link => {
                    link.classList.remove('text-white/90', 'hover:bg-white/10');
                    link.classList.add('text-gray-700', 'hover:bg-gray-100');
                });
            }
        } else {
            // Transparent state
            nav.classList.remove('nav-glass');
            navTitle.classList.remove('text-gray-900');
            navTitle.classList.add('text-white');
            
            // Update desktop nav links
            navLinks.forEach(link => {
                link.classList.remove('text-gray-600', 'hover:text-gray-900');
                link.classList.add('text-white/90', 'hover:text-white');
            });
            
            // Restore glass button
            if (glassButton) {
                glassButton.classList.remove('gradient-bg');
                glassButton.classList.add('glass-button');
            }
            
            // Mobile menu styling when at top
            if (mobileMenu) {
                mobileMenu.classList.remove('bg-white/95', 'border-gray-200');
                mobileMenu.classList.add('bg-white/10', 'border-white/20');
                mobileMenu.querySelectorAll('a').forEach(link => {
                    link.classList.remove('text-gray-700', 'hover:bg-gray-100');
                    link.classList.add('text-white/90', 'hover:bg-white/10');
                });
            }
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
            const isClickInside = mobileMenu.contains(e.target) || mobileMenuBtn.contains(e.target);
            if (!isClickInside) {
                mobileMenu.classList.add('hidden');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) { // md breakpoint
            if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
            }
        }
    });
</script>
    
    @if(App\Helpers\SystemHelper::customJs())
        <script>{!! App\Helpers\SystemHelper::customJs() !!}</script>
    @endif
</body>
</html>