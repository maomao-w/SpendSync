<?php
include 'config.php';

$message = '';
$step = 1;
$email = '';
$question = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['check_email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $query = "SELECT security_question FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $question = $row['security_question'];
            $step = 2;
        } else {
            $message = "<div class='bg-rose-100/80 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium mb-6 backdrop-blur-sm'>No account found with that email address.</div>";
        }
    } elseif (isset($_POST['verify_answer'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $question = mysqli_real_escape_string($conn, $_POST['question']);
        $answer = mysqli_real_escape_string($conn, $_POST['answer']);

        $query = "SELECT * FROM users WHERE email = '$email' AND security_answer = '$answer'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $step = 3;
        } else {
            $step = 2;
            $message = "<div class='bg-rose-100/80 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium mb-6 backdrop-blur-sm'>Incorrect answer. Please try again.</div>";
        }
    } elseif (isset($_POST['reset_password'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "<div class='bg-emerald-100/80 border border-emerald-200 text-emerald-700 p-4 rounded-xl text-sm font-medium mb-6 text-center backdrop-blur-sm'>Password updated successfully! <br><br> <a href='index.php?show=form' class='underline font-bold hover:text-emerald-900 transition-colors'>Click here to login</a></div>";
                $step = 4;
            } else {
                $message = "<div class='bg-rose-100/80 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium mb-6 backdrop-blur-sm'>Error updating password. Please try again.</div>";
                $step = 3;
            }
        } else {
            $message = "<div class='bg-rose-100/80 border border-rose-200 text-rose-700 p-4 rounded-xl text-sm font-medium mb-6 backdrop-blur-sm'>Passwords do not match.</div>";
            $step = 3;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Account Recovery</title>
  
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

    /* Enhanced Glassmorphism Card */
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
    
    .form-scroll-container {
        overflow-y: auto;
        padding-right: 8px;
    }
    .form-scroll-container::-webkit-scrollbar { width: 6px; }
    .form-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .form-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  </style>

  <script>
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye', 'text-blue-600');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye', 'text-blue-600');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
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
    
    <div class="w-full max-w-5xl min-h-[600px] login-form-card flex flex-col lg:flex-row overflow-hidden shadow-[0_20px_60px_rgba(37,99,235,0.15)]">
        
      <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-white/30 to-blue-50/20 p-12 flex-col justify-center items-center relative border-r border-slate-200/40">
        
        <div class="absolute top-8 left-8 flex items-center gap-3 left-anim">
          <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="w-8 h-8 object-contain">
          <span class="brand-font font-bold text-2xl tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
        </div>

        <div class="relative w-full max-w-xs mt-10 h-64 flex justify-center items-center left-anim">
            <div class="absolute z-10 glass-icon-card p-6 rounded-2xl shadow-xl float-anim" style="animation-delay: 0s;">
                <i class="fa-solid fa-unlock-keyhole text-5xl text-blue-600 drop-shadow-md"></i>
            </div>
            <div class="absolute top-2 left-0 glass-icon-card p-4 rounded-xl shadow-lg float-anim" style="animation-delay: 0.5s;">
                <i class="fa-solid fa-circle-question text-3xl text-emerald-500"></i>
            </div>
            <div class="absolute bottom-2 right-0 glass-icon-card p-4 rounded-xl shadow-lg float-anim" style="animation-delay: 1.2s;">
                <i class="fa-solid fa-key text-3xl text-indigo-500"></i>
            </div>
            <div class="absolute top-12 right-4 glass-icon-card p-3 rounded-xl shadow-md float-anim" style="animation-delay: 1.8s;">
                <i class="fa-solid fa-shield-halved text-2xl text-rose-400"></i>
            </div>
             <div class="absolute bottom-12 left-4 glass-icon-card p-3 rounded-xl shadow-md float-anim" style="animation-delay: 2.5s;">
                <i class="fa-solid fa-envelope-open-text text-2xl text-cyan-500"></i>
            </div>
        </div>

        <div class="mt-12 text-center left-anim">
          <h2 class="brand-font text-2xl font-bold mb-3 text-slate-800">Account Recovery</h2>
          <p class="text-slate-600 font-medium text-sm leading-relaxed max-w-xs mx-auto">
            Answer your security question to securely verify your identity and reset your password.
          </p>
        </div>

      </div>

      <div class="w-full lg:w-1/2 p-8 lg:p-12 flex flex-col justify-center bg-white/20 right-anim form-scroll-container">
        
        <div class="lg:hidden flex items-center gap-3 mb-6 justify-center">
          <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="w-10 h-10 object-contain">
          <span class="brand-font font-bold text-3xl tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
        </div>

        <div class="mb-8 text-center lg:text-left">
          <h2 class="text-3xl font-bold mb-2 text-slate-900">Forgot Password</h2>
          <p class="text-slate-600 font-medium">
            <?php 
              if ($step == 1) echo "Let's find your account.";
              elseif ($step == 2) echo "Verify your identity.";
              elseif ($step == 3) echo "Create a new secure password.";
              elseif ($step == 4) echo "All done!";
            ?>
          </p>
        </div>

        <?php echo $message; ?>

        <?php if ($step == 1): ?>
        <form method="POST" action="" class="space-y-5">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Registered Email</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-envelope text-slate-400"></i>
              </div>
              <input type="email" name="email" placeholder="Enter your email address" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium" required>
            </div>
          </div>

          <button type="submit" name="check_email" class="w-full py-4 mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(37,99,235,0.25)] hover:shadow-[0_8px_25px_rgba(37,99,235,0.4)] hover:-translate-y-0.5 tracking-wide">
            Continue
          </button>
        </form>
        <?php endif; ?>

        <?php if ($step == 2): ?>
        <form method="POST" action="" class="space-y-5">
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
          <input type="hidden" name="question" value="<?php echo htmlspecialchars($question); ?>">
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Security Question</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-circle-question text-blue-500"></i>
              </div>
              <div class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-white/50 border border-blue-100 text-slate-700 font-semibold shadow-inner">
                <?php echo htmlspecialchars($question); ?>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Your Answer</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-comment-dots text-slate-400"></i>
              </div>
              <input type="text" name="answer" placeholder="Enter your answer" class="w-full pl-11 pr-4 py-3.5 rounded-xl light-input outline-none font-medium" required>
            </div>
          </div>

          <button type="submit" name="verify_answer" class="w-full py-4 mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(37,99,235,0.25)] hover:shadow-[0_8px_25px_rgba(37,99,235,0.4)] hover:-translate-y-0.5 tracking-wide">
            Verify Answer
          </button>
        </form>
        <?php endif; ?>

        <?php if ($step == 3): ?>
        <form method="POST" action="" class="space-y-5">
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
          
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">New Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-lock text-slate-400"></i>
              </div>
              <input type="password" id="newPassword" name="new_password" placeholder="Enter new password" class="w-full pl-11 pr-12 py-3.5 rounded-xl light-input outline-none font-medium" required>
              
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword('newPassword', 'eyeIcon1')">
                <i id="eyeIcon1" class="fa-solid fa-eye-slash text-slate-400 hover:text-blue-600 transition-colors text-lg"></i>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Confirm Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-lock text-slate-400"></i>
              </div>
              <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" class="w-full pl-11 pr-12 py-3.5 rounded-xl light-input outline-none font-medium" required>
              
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" onclick="togglePassword('confirmPassword', 'eyeIcon2')">
                <i id="eyeIcon2" class="fa-solid fa-eye-slash text-slate-400 hover:text-blue-600 transition-colors text-lg"></i>
              </div>
            </div>
          </div>

          <button type="submit" name="reset_password" class="w-full py-4 mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-300 shadow-[0_8px_20px_rgba(37,99,235,0.25)] hover:shadow-[0_8px_25px_rgba(37,99,235,0.4)] hover:-translate-y-0.5 tracking-wide">
            Update Password
          </button>
        </form>
        <?php endif; ?>

        <?php if ($step != 4): ?>
        <p class="mt-8 text-center text-sm font-semibold text-slate-500">
          Remembered your password? <a href="login.php?show=form" class="text-blue-600 hover:text-blue-800 transition-colors font-bold">Log in here</a>
        </p>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script>
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
