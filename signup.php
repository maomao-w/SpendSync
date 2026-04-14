<?php
include 'config.php';
$success = false;
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $security_question = mysqli_real_escape_string($conn, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($conn, $_POST['security_answer']);

    // Simpleng check kung existing na yung email para walang error sa database
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if(mysqli_num_rows($check_email) > 0) {
        $errorMsg = "Email is already registered.";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, security_question, security_answer) VALUES ('$full_name', '$email', '$password', '$security_question', '$security_answer')";
        if (mysqli_query($conn, $sql)) {
            $success = true;
        } else {
            $errorMsg = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Sign Up</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

  <style>
    :root {
      --bg-base: #f4f7f9; 
      --primary: #2563eb; 
      --secondary: #10b981; 
      --text-main: #1e293b; 
      --text-muted: #64748b; 
    }

    body {
      background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
      color: var(--text-main);
      font-family: 'Inter', sans-serif;
      overflow: hidden; 
      margin: 0;
      padding: 0;
    }

    h1, h2, h3, .brand-font {
      font-family: 'Poppins', sans-serif;
    }

    #webgl-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: -1; 
      pointer-events: none; 
    }

    /* Enhanced Glassmorphism Card (Same as Login) */
    .login-form-card {
      background: rgba(255, 255, 255, 0.45);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.8);
      box-shadow: 0 25px 50px rgba(37, 99, 235, 0.1), 0 10px 15px rgba(0, 0, 0, 0.05);
      border-radius: 2rem;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }
    .float-anim {
        animation: float 4s ease-in-out infinite;
    }

    .glass-icon-card {
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 1);
    }

    .light-input {
      background-color: rgba(248, 250, 252, 0.7);
      border: 1px solid rgba(226, 232, 240, 0.8);
      color: var(--text-main);
      transition: all 0.3s ease;
    }
    .light-input:focus {
      border-color: var(--primary);
      background-color: #ffffff;
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    
    /* Scrollbar override for the right section */
    .form-scroll-container {
        overflow-y: auto;
        padding-right: 8px; /* space for scrollbar */
    }
    .form-scroll-container::-webkit-scrollbar { width: 6px; }
    .form-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .form-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .modal-overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(5px);
      display: flex; align-items: center; justify-content: center;
      z-index: 1000; opacity: 0; visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    
    /* Updated Modal Container for Glassmorphism */
    .modal-container {
      background: rgba(255, 255, 255, 0.45);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.8);
      box-shadow: 0 25px 50px rgba(37, 99, 235, 0.1), 0 10px 15px rgba(0, 0, 0, 0.05);
      border-radius: 1.5rem; width: 90%; max-width: 400px; padding: 2rem;
      text-align: center;
      transform: scale(0.9); transition: transform 0.2s ease;
    }
    .modal-overlay.active .modal-container { transform: scale(1); }
    .modal-message { color: var(--text-main); font-size: 1rem; line-height: 1.5; margin-bottom: 1.5rem; font-weight: 600;}
    
    /* Updated Modal Button to match the main UI button */
    .modal-btn {
      background: linear-gradient(to right, #2563eb, #4f46e5); 
      color: white; padding: 0.75rem 2rem;
      border-radius: 0.75rem; font-weight: 600; border: none; cursor: pointer;
      box-shadow: 0 8px 20px rgba(37,99,235,0.25);
      transition: all 0.3s ease;
    }
    .modal-btn:hover { 
      background: linear-gradient(to right, #1d4ed8, #4338ca); 
      transform: translateY(-2px); 
      box-shadow: 0 8px 25px rgba(37,99,235,0.4);
    }
  </style>

  <script>
    function showModal(message) {
      const modal = document.getElementById('customModal');
      const modalMessage = document.getElementById('modalMessage');
      modalMessage.innerText = message;
      modal.classList.add('active');
    }

    function closeModal() {
      document.getElementById('customModal').classList.remove('active');
    }

    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
            eyeIcon.classList.add('text-blue-600');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.remove('text-blue-600');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const signupForm = document.getElementById('signupForm');
      if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
          const emailField = document.getElementById('email');
          const emailVal = emailField.value;
          if (!emailVal.includes('@')) {
            e.preventDefault();
            showModal("Please include an '@' in the email address.");
          }
        });
      }

      // GSAP ENTRANCE ANIMATIONS
      gsap.from(".login-form-card", { y: 50, opacity: 0, duration: 1, ease: "power3.out", delay: 0.2 });
      gsap.from(".left-anim", { x: -30, opacity: 0, duration: 1, stagger: 0.2, ease: "power3.out", delay: 0.6 });
      gsap.from(".right-anim", { x: 30, opacity: 0, duration: 1, ease: "power3.out", delay: 0.6 });
    });
  </script>
</head>
<body>

  <div id="webgl-container"></div>

  <div class="flex h-screen w-full items-center justify-center p-4 lg:p-8 relative z-10">
    
    <div class="w-full max-w-5xl h-full max-h-[850px] login-form-card flex flex-col lg:flex-row overflow-hidden shadow-[0_20px_60px_rgba(37,99,235,0.15)]">
        
      <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-white/30 to-blue-50/20 p-12 flex-col justify-center items-center relative border-r border-slate-200/40">
        
        <div class="absolute top-8 left-8 flex items-center gap-3 left-anim">
          <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="w-8 h-8 object-contain">
          <span class="brand-font font-bold text-2xl tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
        </div>

        <div class="relative w-full max-w-xs mt-10 h-64 flex justify-center items-center left-anim">
            <div class="absolute z-10 glass-icon-card p-6 rounded-2xl shadow-xl float-anim" style="animation-delay: 0s;">
                <i class="fa-solid fa-money-bill-transfer text-5xl text-blue-600 drop-shadow-md"></i>
            </div>
            <div class="absolute top-2 left-0 glass-icon-card p-4 rounded-xl shadow-lg float-anim" style="animation-delay: 0.5s;">
                <i class="fa-solid fa-chart-line text-3xl text-emerald-500"></i>
            </div>
            <div class="absolute bottom-2 right-0 glass-icon-card p-4 rounded-xl shadow-lg float-anim" style="animation-delay: 1.2s;">
                <i class="fa-solid fa-wallet text-3xl text-indigo-500"></i>
            </div>
            <div class="absolute top-12 right-4 glass-icon-card p-3 rounded-xl shadow-md float-anim" style="animation-delay: 1.8s;">
                <i class="fa-solid fa-shield-halved text-2xl text-rose-400"></i>
            </div>
             <div class="absolute bottom-12 left-4 glass-icon-card p-3 rounded-xl shadow-md float-anim" style="animation-delay: 2.5s;">
                <i class="fa-solid fa-cloud-arrow-up text-2xl text-cyan-500"></i>
            </div>
        </div>

        <div class="mt-12 text-center left-anim">
          <h2 class="brand-font text-2xl font-bold mb-3 text-slate-800">Your financial journey <br>starts here.</h2>
          <p class="text-slate-600 font-medium text-sm leading-relaxed max-w-xs mx-auto">
            Empower your financial future and master responsible spending with SpendSync.
          </p>
        </div>

      </div>

      <div class="w-full lg:w-1/2 p-8 lg:p-12 flex flex-col bg-white/20 right-anim form-scroll-container">
        
        <div class="lg:hidden flex items-center gap-3 mb-6 justify-center mt-4">
          <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="w-10 h-10 object-contain">
          <span class="brand-font font-bold text-3xl tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
        </div>

        <div class="mb-6 text-center lg:text-left pt-2 lg:pt-4">
          <h2 class="text-3xl font-bold mb-2 text-slate-900">Create Account</h2>
          <p class="text-slate-600 font-medium">Let’s build a smarter budget for you.</p>
        </div>

        <div class="flex border-b border-slate-300/50 mb-8 shrink-0">
          <a href="login.php" class="flex-1 pb-3 text-center font-semibold text-slate-500 hover:text-slate-700 transition-colors">Login</a>
          <button class="flex-1 pb-3 text-center font-bold text-blue-600 border-b-2 border-blue-600">Sign Up</button>
        </div>

        <form id="signupForm" action="" method="POST" class="space-y-4 pb-8">
          
          <div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-user text-slate-400"></i>
              </div>
              <input type="text" name="full_name" placeholder="Full Name" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium" required>
            </div>
          </div>

          <div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-envelope text-slate-400"></i>
              </div>
              <input type="text" id="email" name="email" placeholder="Email Address" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium" required>
            </div>
          </div>

          <div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-circle-question text-slate-400"></i>
              </div>
              <select name="security_question" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium text-slate-600 appearance-none" required>
                  <option value="" disabled selected>Select a Security Question</option>
                  <option value="What city were you born in?">What city were you born in?</option>
                  <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                  <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                  <option value="What was the name of your first school?">What was the name of your first school?</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-chevron-down text-slate-400"></i>
              </div>
            </div>
          </div>

          <div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-key text-slate-400"></i>
              </div>
              <input type="text" name="security_answer" placeholder="Security Answer" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium" required>
            </div>
          </div>

          <div>
            <div class="relative mb-2">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-lock text-slate-400"></i>
              </div>
              <input type="password" id="passwordInput" name="password" placeholder="Create Password" 
                     oninput="validatePassword(this.value)"
                     class="w-full pl-11 pr-12 py-3.5 rounded-xl light-input outline-none font-medium" required>
              
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword()">
                <i id="eyeIcon" class="fa-solid fa-eye-slash text-slate-400 hover:text-blue-600 transition-colors text-lg"></i>
              </div>
            </div>
            
            <div class="grid grid-cols-1 gap-1.5 px-2 mt-3">
                <p id="rule-length" class="text-xs font-bold text-slate-400 transition-colors tracking-wide flex items-center gap-2">
                    <span id="dot-length" class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Min. 8 Characters
                </p>
                <p id="rule-upper" class="text-xs font-bold text-slate-400 transition-colors tracking-wide flex items-center gap-2">
                    <span id="dot-upper" class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Starts with Uppercase
                </p>
                <p id="rule-number" class="text-xs font-bold text-slate-400 transition-colors tracking-wide flex items-center gap-2">
                    <span id="dot-number" class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Must include a Number
                </p>
            </div>
          </div>

          <button type="submit" id="submitBtn" class="w-full py-4 mt-6 bg-slate-400 text-white rounded-xl font-bold transition-all duration-300 cursor-not-allowed tracking-wide" disabled>
            Register
          </button>
        </form>

      </div>
    </div>
  </div>

  <div id="customModal" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-message" id="modalMessage"></div>
      <button onclick="closeModal()" class="modal-btn">OK</button>
    </div>
  </div>

  <script>
    // Custom Password Validation Logic Update
    function validatePassword(pw) {
      const btn = document.getElementById('submitBtn');
      const checks = {
        length: pw.length >= 8,
        upper: /^[A-Z]/.test(pw),
        number: /\d/.test(pw),
      };
      
      for (const [key, passed] of Object.entries(checks)) {
        const text = document.getElementById(`rule-${key}`);
        const dot = document.getElementById(`dot-${key}`);
        
        if (passed) {
          text.classList.replace('text-slate-400', 'text-emerald-500');
          dot.classList.replace('bg-slate-300', 'bg-emerald-500');
        } else {
          text.classList.remove('text-emerald-500');
          text.classList.add('text-slate-400');
          dot.classList.remove('bg-emerald-500');
          dot.classList.add('bg-slate-300');
        }
      }
      
      const allPassed = Object.values(checks).every(v => v === true);
      
      if (allPassed) {
        btn.disabled = false;
        // Apply gradient active state
        btn.className = "w-full py-4 mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(37,99,235,0.25)] hover:shadow-[0_8px_25px_rgba(37,99,235,0.4)] hover:-translate-y-0.5 tracking-wide";
      } else {
        btn.disabled = true;
        // Apply disabled state
        btn.className = "w-full py-4 mt-6 bg-slate-300 text-slate-500 rounded-xl font-bold transition-all duration-300 cursor-not-allowed tracking-wide";
      }
    }

    // Modal Triggers for PHP events
    <?php if ($errorMsg): ?>
      showModal("<?php echo addslashes($errorMsg); ?>");
    <?php endif; ?>

    <?php if ($success): ?>
      showModal("Account created successfully!");
      const modalBtn = document.querySelector('#customModal .modal-btn');
      if (modalBtn) {
        modalBtn.onclick = function() {
          closeModal();
          // Pinalitan yung URL papuntang login.php
          window.location.href = 'login.php';
        };
      }
    <?php endif; ?>

    // ==========================================
    // THREE.JS: MONEY/FINANCE 3D BACKGROUND
    // ==========================================
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
        mesh.position.set((Math.random() - 0.5) * 24, yPosRange, (Math.random() - 0.5) * 12 - 5);
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
    bigCoin.position.x = -4; 
    bigCoin.rotation.x = Math.PI / 2;

    const bigCash = spawnObject(new THREE.BoxGeometry(2, 1, 0.3), cashMaterial, -1);
    bigCash.position.x = 3; 
    bigCash.rotation.y = 0.5;

    for(let i = 0; i < 45; i++) {
        const randomType = Math.random();
        const yPos = 5 - (Math.random() * 20); 

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

    const clock = new THREE.Clock();
    let mouseX = 0; 
    let mouseY = 0;
    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;

    document.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX - windowHalfX) * 0.001;
        mouseY = (event.clientY - windowHalfY) * 0.001;
    });

    function animate() {
        requestAnimationFrame(animate);
        const elapsedTime = clock.getElapsedTime();

        camera.position.x += (mouseX * 2 - camera.position.x) * 0.05;
        camera.position.y += (2 + -mouseY * 2 - camera.position.y) * 0.05;
        camera.lookAt(0, 0, 0);
        
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
  </script>
</body>
</html>
