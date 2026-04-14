<?php
// index.php - Enhanced Landing Page for SpendSync
require_once 'config.php';

// Get current page for active nav state
$current_page = 'home';

// Optional: Fetch site stats from database if needed
$total_users = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_users = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <title>SpendSync - Premium Clean UI</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #10b981;
            --accent: #f59e0b;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        h1, h2, h3, .logo {
            font-family: 'Poppins', sans-serif;
        }

        /* 3D Canvas Background */
        #webgl-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        /* Navigation Bar */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.4s ease;
        }

        @media (min-width: 1200px) {
            nav { padding: 20px 8%; }
        }

        nav.scrolled {
            padding: 12px 5%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        @media (min-width: 1200px) {
            nav.scrolled { padding: 12px 8%; }
        }

        .logo { 
            font-size: 1.4rem; 
            font-weight: 800; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            letter-spacing: -0.5px; 
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        @media (min-width: 480px) {
            .logo { font-size: 1.6rem; gap: 10px; }
        }

        .logo-img { 
            height: 32px; 
            width: auto; 
            object-fit: contain; 
        }

        @media (min-width: 480px) {
            .logo-img { height: 38px; }
        }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 20px;
        }

        @media (min-width: 768px) {
            .nav-links { gap: 30px; }
        }

        .nav-links a { 
            color: var(--text-main); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 600; 
            transition: color 0.3s ease; 
            position: relative;
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .nav-links a { font-size: 0.95rem; }
        }

        .nav-links a:hover, 
        .nav-links a.active {
            color: var(--primary);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .btn-glow {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white !important;
            padding: 8px 20px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-block;
            white-space: nowrap;
        }

        @media (min-width: 480px) {
            .btn-glow { padding: 10px 28px; font-size: 0.95rem; }
        }

        .btn-glow:hover { 
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4); 
            transform: translateY(-2px); 
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            font-size: 1.6rem;
            background: none;
            border: none;
            color: var(--text-main);
            cursor: pointer;
            z-index: 1001;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 75%;
                max-width: 300px;
                height: 100vh;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                justify-content: center;
                gap: 2rem;
                transition: 0.3s ease-in-out;
                box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
                z-index: 999;
                padding: 2rem;
            }
            
            .nav-links.active {
                right: 0;
            }
            
            .nav-links a {
                font-size: 1.1rem;
                white-space: normal;
            }
            
            .btn-glow {
                padding: 12px 30px;
                font-size: 1rem;
                margin-top: 10px;
            }
        }

        /* Hero Section */
        .hero {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 100vh;
            padding: 100px 5% 60px;
            z-index: 2;
        }

        @media (min-width: 768px) {
            .hero { padding: 0 8%; padding-top: 80px; }
        }

        .hero-text { 
            max-width: 100%;
            pointer-events: auto;
        }

        @media (min-width: 768px) {
            .hero-text { max-width: 650px; }
        }

        .hero-text h1 {
            font-size: 2.5rem;
            line-height: 1.2;
            margin-bottom: 20px;
            letter-spacing: -1px;
            color: #0f172a;
        }

        @media (min-width: 480px) {
            .hero-text h1 { font-size: 3rem; }
        }

        @media (min-width: 768px) {
            .hero-text h1 { font-size: 3.8rem; }
        }

        @media (min-width: 1024px) {
            .hero-text h1 { font-size: 4.8rem; }
        }

        .hero-text h1 span { 
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-text p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 400;
        }

        @media (min-width: 768px) {
            .hero-text p { font-size: 1.15rem; margin-bottom: 40px; line-height: 1.7; }
        }

        @media (min-width: 1024px) {
            .hero-text p { font-size: 1.25rem; margin-bottom: 45px; line-height: 1.8; }
        }

        /* Stats Badge */
        .stats-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(37, 99, 235, 0.1);
            padding: 6px 16px;
            border-radius: 40px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
        }

        @media (min-width: 768px) {
            .stats-badge { gap: 8px; padding: 8px 20px; font-size: 0.9rem; margin-bottom: 25px; }
        }

        /* Features Section */
        .features { 
            padding: 60px 5%; 
            position: relative; 
            z-index: 2; 
        }

        @media (min-width: 768px) {
            .features { padding: 80px 8%; }
        }

        @media (min-width: 1024px) {
            .features { padding: 100px 8%; }
        }

        .features-header { 
            text-align: center; 
            margin-bottom: 50px; 
        }

        @media (min-width: 768px) {
            .features-header { margin-bottom: 80px; }
        }

        .features-header h2 { 
            font-size: 2rem; 
            margin-bottom: 15px; 
            font-weight: 800; 
            letter-spacing: -1px; 
            color: #0f172a;
        }

        @media (min-width: 480px) {
            .features-header h2 { font-size: 2.5rem; }
        }

        @media (min-width: 768px) {
            .features-header h2 { font-size: 3rem; }
        }

        @media (min-width: 1024px) {
            .features-header h2 { font-size: 3.5rem; }
        }

        .features-header p { 
            color: var(--text-muted); 
            font-size: 0.95rem; 
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
            padding: 0 15px;
        }

        @media (min-width: 768px) {
            .features-header p { font-size: 1.05rem; line-height: 1.7; }
        }

        @media (min-width: 1024px) {
            .features-header p { font-size: 1.15rem; }
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 30px;
            max-width: 100%;
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .features-list { gap: 50px; max-width: 900px; }
        }

        @media (min-width: 1024px) {
            .features-list { gap: 60px; max-width: 1000px; }
        }

        .feature-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 30px 20px;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            text-align: center;
        }

        @media (min-width: 768px) {
            .feature-card {
                flex-direction: row;
                text-align: left;
                gap: 40px;
                padding: 40px;
            }
            .feature-card:nth-child(even) {
                flex-direction: row-reverse;
            }
        }

        @media (min-width: 1024px) {
            .feature-card { gap: 50px; padding: 50px; }
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
        }

        .feature-image-container {
            flex-shrink: 0;
            width: 120px;
            text-align: center;
        }

        @media (min-width: 480px) {
            .feature-image-container { width: 150px; }
        }

        @media (min-width: 768px) {
            .feature-image-container { width: 180px; flex: 0 0 180px; }
        }

        @media (min-width: 1024px) {
            .feature-image-container { width: 220px; flex: 0 0 220px; }
        }

        .feature-image-container img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15));
            transition: transform 0.5s ease;
        }

        .feature-card:hover .feature-image-container img {
            transform: scale(1.05) translateY(-5px);
        }

        .feature-text { 
            flex: 1; 
        }

        .feature-text h3 { 
            font-size: 1.5rem; 
            margin-bottom: 10px; 
            color: var(--text-main);
            font-weight: 700;
        }

        @media (min-width: 480px) {
            .feature-text h3 { font-size: 1.8rem; }
        }

        @media (min-width: 768px) {
            .feature-text h3 { font-size: 2rem; margin-bottom: 12px; }
        }

        @media (min-width: 1024px) {
            .feature-text h3 { font-size: 2.2rem; margin-bottom: 15px; }
        }

        .feature-text p { 
            color: var(--text-muted); 
            font-size: 0.9rem; 
            line-height: 1.6; 
        }

        @media (min-width: 768px) {
            .feature-text p { font-size: 1rem; line-height: 1.7; }
        }

        @media (min-width: 1024px) {
            .feature-text p { font-size: 1.15rem; line-height: 1.8; }
        }

        /* About Section */
        .about-section {
            padding: 60px 5%;
            position: relative;
            z-index: 2;
            text-align: center;
        }

        @media (min-width: 768px) {
            .about-section { padding: 100px 8%; }
        }

        @media (min-width: 1024px) {
            .about-section { padding: 150px 8%; }
        }

        .about-content {
            max-width: 100%;
            margin: 0 auto;
            padding: 40px 25px;
        }

        @media (min-width: 768px) {
            .about-content { max-width: 800px; padding: 50px 40px; }
        }

        @media (min-width: 1024px) {
            .about-content { max-width: 900px; padding: 70px 50px; }
        }

        .about-content h2 { 
            font-size: 2rem; 
            margin-bottom: 20px; 
            font-weight: 800; 
            letter-spacing: -1px; 
            color: #0f172a;
        }

        @media (min-width: 480px) {
            .about-content h2 { font-size: 2.5rem; }
        }

        @media (min-width: 768px) {
            .about-content h2 { font-size: 3rem; }
        }

        @media (min-width: 1024px) {
            .about-content h2 { font-size: 4rem; }
        }

        .about-content h2 span { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            -webkit-background-clip: text; 
            background-clip: text; 
            color: transparent; 
        }

        .about-content p { 
            color: var(--text-muted); 
            font-size: 0.95rem; 
            line-height: 1.7; 
            margin-bottom: 20px; 
        }

        @media (min-width: 768px) {
            .about-content p { font-size: 1.1rem; line-height: 1.8; margin-bottom: 25px; }
        }

        @media (min-width: 1024px) {
            .about-content p { font-size: 1.25rem; line-height: 2; }
        }

        .highlight-text { 
            color: var(--primary); 
            font-weight: 600; 
        }

        /* Footer */
        footer { 
            text-align: center; 
            padding: 30px 20px; 
            color: var(--text-muted); 
            border-top: 1px solid rgba(0,0,0,0.05); 
            font-size: 0.8rem; 
        }

        @media (min-width: 768px) {
            footer { padding: 50px 20px; font-size: 0.9rem; }
        }

        @media (min-width: 1024px) {
            footer { font-size: 1rem; }
        }

        /* Glass Panel */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            border-radius: 20px;
        }

        @media (min-width: 768px) {
            .glass-panel { border-radius: 25px; }
        }

        @media (min-width: 1024px) {
            .glass-panel { border-radius: 30px; }
        }
    </style>
</head>
<body>

    <div id="webgl-container"></div>

    <nav id="navbar">
        <div class="logo">
            <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="logo-img">
            SpendSync
        </div>
        <button class="mobile-menu-btn" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="#home" class="active">Home</a>
            <a href="#features">Features</a>
            <a href="#about">About Us</a>
            <a href="login.php" class="btn-glow">Log In</a>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-text reveal">
            <div class="stats-badge">
                <i class="fas fa-users"></i> <?php echo number_format($total_users); ?>+ Trusted Users
            </div>
            <h1>Centralize Your <br><span>Financial Data</span></h1>
            <p>Empower your financial journey with SpendSync. We provide an intuitive platform that allows you to seamlessly monitor your income, track daily expenses, and build your savings securely.</p>
            <a href="#features" class="btn-glow" style="padding: 12px 30px; font-size: 1rem;">Explore System</a>
        </div>
    </section>

    <section id="features" class="features">
        <div class="features-header reveal">
            <h2>System Features</h2>
            <p>We've built everything you need to master your personal and business finances inside one centralized, high-performance web platform.</p>
        </div>

        <div class="features-list">
            
            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/locked_1f512.png" alt="Security Lock">
                </div>
                <div class="feature-text">
                    <h3>User Authentication</h3>
                    <p>Ensure data security and privacy through secure authentication. Enjoy secure signup, seamless login, and robust password recovery designed to keep your sensitive data strictly protected.</p>
                </div>
            </div>

            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/bar-chart_1f4ca.png" alt="Dashboard Chart">
                </div>
                <div class="feature-text">
                    <h3>Interactive Dashboard</h3>
                    <p>Get a comprehensive bird's-eye view of your financial health. Our dynamic dashboard provides real-time updates on account balances and critical financial metrics at a single glance.</p>
                </div>
            </div>

            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/money-with-wings_1f4b8.png" alt="Income and Expenses">
                </div>
                <div class="feature-text">
                    <h3>Income & Expenses</h3>
                    <p>Take full control of your cash flow. Easily add, modify, and manage your income and expense records. Tag them with customizable categories making transaction tracking an absolute breeze.</p>
                </div>
            </div>

            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/direct-hit_1f3af.png" alt="Budget Goal Target">
                </div>
                <div class="feature-text">
                    <h3>Budget & Goals</h3>
                    <p>Turn your financial dreams into reality. Set strict monthly or annual budgets, establish savings goals, and closely monitor your progress to prevent overspending and secure your future.</p>
                </div>
            </div>

            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/chart-increasing_1f4c8.png" alt="Analytics Trending Up">
                </div>
                <div class="feature-text">
                    <h3>Reports & Analytics</h3>
                    <p>Transform raw numbers into actionable insights. Generate stunning, highly visual charts, and detailed summaries to deeply analyze your spending trends and discover where to save.</p>
                </div>
            </div>

            <div class="feature-card glass-panel reveal-row">
                <div class="feature-image-container">
                    <img src="https://em-content.zobj.net/source/microsoft-teams/337/file-folder_1f4c1.png" alt="Data Folder Export">
                </div>
                <div class="feature-text">
                    <h3>Data Export & Import</h3>
                    <p>Your data, your rules. Seamlessly download or upload your entire financial history using standard CSV or Excel formats, ensuring you always have secure offline backups.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="about-section" id="about">
        <div class="about-content glass-panel reveal">
            <h2>The Vision Behind <br><span>SpendSync</span></h2>
            <p>SpendSync isn't just another tracker; it is a <span class="highlight-text">centralized financial revolution</span> engineered to help individuals, students, and businesses take absolute control of their resources. Born from the innovative drive of Bulacan State University's IT 211 Web Systems curriculum, our platform bridges the gap between raw data and actionable financial wisdom.</p>
            <p>Our core mission is straightforward: <span class="highlight-text">Empowerment through transparency.</span> By providing a visually stunning, highly secure, and intuitively mapped environment, we strip away the anxiety of budgeting. We don't just want you to track your money—we want you to master it, building a foundation for lifelong financial freedom and literacy.</p>
        </div>
    </section>

    <footer>
        <p>SpendSync &copy; 2026 | Developed for IT 211 - Web Systems and Technologies | BulSU</p>
    </footer>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinksElem = document.getElementById('navLinks');
        let menuOpen = false;

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                menuOpen = !menuOpen;
                navLinksElem.classList.toggle('active');
                const icon = menuToggle.querySelector('i');
                if (menuOpen) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }

        // Close menu when clicking on a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinksElem.classList.remove('active');
                menuOpen = false;
                const icon = menuToggle?.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });

        // THREE.JS 3D BACKGROUND
        const container = document.getElementById('webgl-container');
        const scene = new THREE.Scene();
        
        scene.fog = new THREE.FogExp2(0xf4f7f9, 0.035);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); 
        container.appendChild(renderer.domElement);

        const ambientLight = new THREE.AmbientLight(0xffffff, 1.2);
        scene.add(ambientLight);

        const mainLight = new THREE.DirectionalLight(0xffffff, 1.5);
        mainLight.position.set(10, 20, 15);
        scene.add(mainLight);

        const goldLight = new THREE.PointLight(0xffd700, 2, 40);
        goldLight.position.set(-10, 5, 5);
        scene.add(goldLight);
        
        const greenLight = new THREE.PointLight(0x10b981, 2, 40);
        greenLight.position.set(10, -10, 5);
        scene.add(greenLight);

        const goldMaterial = new THREE.MeshStandardMaterial({ color: 0xffd700, roughness: 0.2, metalness: 0.9 });
        const cashMaterial = new THREE.MeshStandardMaterial({ color: 0x10b981, roughness: 0.5, metalness: 0.1 });
        const dataMaterial = new THREE.MeshStandardMaterial({ color: 0x2563eb, roughness: 0.3, metalness: 0.5 });

        const bgObjects = [];

        function spawnObject(geo, mat, yPosRange) {
            const mesh = new THREE.Mesh(geo, mat);
            mesh.position.set(
                (Math.random() - 0.5) * 24,
                yPosRange, 
                (Math.random() - 0.5) * 12 - 5
            );
            mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
            scene.add(mesh);
            
            bgObjects.push({
                mesh: mesh,
                rotX: (Math.random() - 0.5) * 0.015,
                rotY: (Math.random() - 0.5) * 0.015,
                floatSpeed: Math.random() * 0.01 + 0.005,
                initialY: mesh.position.y
            });
            return mesh;
        }

        const bigCoin = spawnObject(new THREE.CylinderGeometry(1.8, 1.8, 0.2, 32), goldMaterial, 1);
        bigCoin.position.x = 6;
        bigCoin.rotation.x = Math.PI / 2;

        const bigCash = spawnObject(new THREE.BoxGeometry(2, 1, 0.3), cashMaterial, -1);
        bigCash.position.x = 8;
        bigCash.rotation.y = 0.5;

        for(let i = 0; i < 45; i++) {
            const randomType = Math.random();
            const yPos = 5 - (Math.random() * 55); 

            if(randomType < 0.5) {
                const coin = spawnObject(new THREE.CylinderGeometry(0.6, 0.6, 0.1, 32), goldMaterial, yPos);
                coin.rotation.x = Math.PI / 2;
            } else if (randomType < 0.8) {
                spawnObject(new THREE.BoxGeometry(1.2, 0.6, 0.2), cashMaterial, yPos);
            } else {
                spawnObject(new THREE.BoxGeometry(0.5, Math.random() * 2 + 0.8, 0.5), dataMaterial, yPos);
            }
        }

        camera.position.z = 9;
        camera.position.y = 2; 

        let targetCameraY = 2; 
        const clock = new THREE.Clock();
        
        let mouseX = 0; 
        let mouseY = 0;
        const windowHalfX = window.innerWidth / 2;
        const windowHalfY = window.innerHeight / 2;

        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX - windowHalfX) * 0.001;
            mouseY = (event.clientY - windowHalfY) * 0.001;
        });

        window.addEventListener('scroll', () => {
            const scrollPercent = window.scrollY / (document.body.scrollHeight - window.innerHeight);
            targetCameraY = 2 - (scrollPercent * 40);
        });

        function animate() {
            requestAnimationFrame(animate);
            const elapsedTime = clock.getElapsedTime();

            camera.position.y += (targetCameraY - camera.position.y) * 0.05;
            camera.position.x += (mouseX * 2 - camera.position.x) * 0.05;
            
            bgObjects.forEach((obj) => {
                obj.mesh.rotation.x += obj.rotX;
                obj.mesh.rotation.y += obj.rotY;
                obj.mesh.position.y = obj.initialY + Math.sin(elapsedTime * obj.floatSpeed * 50) * 0.6;
            });

            renderer.render(scene, camera);
        }
        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // GSAP SCROLL ANIMATIONS
        gsap.registerPlugin(ScrollTrigger);

        window.addEventListener("scroll", function() {
            var nav = document.getElementById("navbar");
            if (window.scrollY > 50) {
                nav.classList.add("scrolled");
            } else {
                nav.classList.remove("scrolled");
            }
        });

        gsap.utils.toArray('.reveal').forEach(element => {
            gsap.from(element, {
                scrollTrigger: { trigger: element, start: "top 85%" },
                y: 30, opacity: 0, duration: 0.8, ease: "power3.out"
            });
        });

        gsap.utils.toArray('.reveal-row').forEach((row, i) => {
            const isEven = i % 2 !== 0;
            gsap.from(row, {
                scrollTrigger: { 
                    trigger: row, 
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                },
                x: isEven ? 80 : -80,
                opacity: 0, 
                duration: 1, 
                ease: "power3.out"
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === "#" || href === "") return;
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>