<?php
session_start();
include 'config.php'; 

$bypassSplash = isset($_GET['show']) && $_GET['show'] == 'form';
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            header("Location: homepage.php");
            exit();
        } else {
            $errorMsg = "Wrong email/password!";
        }
    } else {
        $errorMsg = "You don't have an account. Please register first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* Your existing animations and styles (unchanged) */
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    @keyframes float {
      0% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-15px) rotate(2deg); }
      100% { transform: translateY(0px) rotate(0deg); }
    }
    .animate-gradient {
      background-size: 200% 200%;
      animation: gradientShift 12s ease infinite;
    }
    .animate-float-slow {
      animation: float 8s ease-in-out infinite;
    }

    /* Simple modal – just message and OK button */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s;
    }
    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    .modal-container {
      background: white;
      border-radius: 2rem;
      width: 90%;
      max-width: 400px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
      transform: scale(0.9);
      transition: transform 0.2s ease;
      border: 1px solid rgba(79,70,229,0.2);
    }
    .modal-overlay.active .modal-container {
      transform: scale(1);
    }
    .modal-message {
      color: #1f2937;
      font-size: 1rem;
      line-height: 1.5;
      margin-bottom: 1.5rem;
    }
    .modal-btn {
      background: linear-gradient(135deg, #2d3494, #4f46e5);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 2rem;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .modal-btn:hover {
      transform: scale(1.02);
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
    }
  </style>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { 'sync-blue': '#4f46e5', 'primary': '#030213' },
          animation: {
            'fade-in': 'fadeIn 0.6s ease-out',
            'float': 'float 6s ease-in-out infinite',
            'blob': 'blob 7s infinite',
            'slide-up': 'slideUp 0.5s ease-out forwards',
            'gradient-shift': 'gradientShift 12s ease infinite',
            'float-slow': 'float 8s ease-in-out infinite'
          },
          keyframes: {
            fadeIn: { 'from': { opacity: 0 }, 'to': { opacity: 1 } },
            float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-20px)' } },
            slideUp: { 'from': { opacity: 0, transform: 'translateY(20px)' }, 'to': { opacity: 1, transform: 'translateY(0)' } },
            blob: {
                '0%': { transform: 'translate(0px, 0px) scale(1)' },
                '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                '100%': { transform: 'translate(0px, 0px) scale(1)' },
            },
            gradientShift: { '0%': { backgroundPosition: '0% 50%' }, '50%': { backgroundPosition: '100% 50%' }, '100%': { backgroundPosition: '0% 50%' } },
            customFloat: { '0%': { transform: 'translateY(0px) rotate(0deg)' }, '50%': { transform: 'translateY(-15px) rotate(2deg)' }, '100%': { transform: 'translateY(0px) rotate(0deg)' } }
          }
        }
      }
    }

    // Simple showModal with one argument (message)
    function showModal(message) {
      const modal = document.getElementById('customModal');
      const modalMessage = document.getElementById('modalMessage');
      modalMessage.innerText = message;
      modal.classList.add('active');
    }

    function closeModal() {
      document.getElementById('customModal').classList.remove('active');
    }

    // Missing '@' validation – uses the same modal
    document.addEventListener('DOMContentLoaded', function() {
      const loginForm = document.getElementById('loginForm');
      if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
          const emailField = document.getElementById('email');
          const emailVal = emailField.value;
          if (!emailVal.includes('@')) {
            e.preventDefault();
            showModal("Please include an '@' in the email address. '" + emailVal + "' is missing an '@'.");
          }
        });
      }
    });
  </script>
</head>
<body class="h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 flex items-center justify-center font-['Inter'] overflow-hidden">

  <div class="w-full h-full lg:h-[92vh] lg:w-[96vw] lg:max-w-6xl flex flex-col lg:flex-row shadow-2xl lg:rounded-[2.5rem] overflow-hidden bg-white/80 backdrop-blur-sm border border-white/20">
    
    <!-- Left Branding Section -->
    <div id="brandingSection" class="<?php echo $bypassSplash ? 'hidden lg:flex' : 'flex'; ?> w-full lg:w-1/2 bg-gradient-to-br from-[#2d3494] via-indigo-700 to-[#1e1a6b] p-10 lg:p-16 flex flex-col justify-center items-center text-white text-center h-full shrink-0 relative overflow-hidden animate-gradient">
      <div class="absolute top-0 -left-4 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob"></div>
      <div class="absolute top-0 -right-4 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-8 left-20 w-80 h-80 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-4000"></div>
      <div class="absolute bottom-20 right-20 w-64 h-64 bg-pink-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-6000"></div>
      <div class="relative z-10 animate-fade-in flex flex-col items-center">
        <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" 
             alt="SpendSync Logo" 
             class="w-36 h-36 mb-8 object-contain drop-shadow-2xl animate-float-slow">
        <h1 class="text-5xl lg:text-7xl font-black uppercase tracking-tighter mb-4 italic drop-shadow-lg">SpendSync</h1>
        <p class="text-blue-50 text-sm lg:text-lg opacity-90 max-w-xs mx-auto mb-12">Monitor income, expenses, and achieve financial goals.</p>
        <button id="getStartedBtn" onclick="startApp()" class="lg:hidden px-14 py-4 bg-white text-[#2d3494] rounded-2xl font-bold shadow-2xl hover:shadow-3xl transform hover:scale-105 active:scale-95 transition-all duration-300 uppercase tracking-widest text-sm">Get Started</button>
      </div>
    </div>

    <!-- Right Form Section -->
    <div id="formSection" class="<?php echo $bypassSplash ? 'flex' : 'hidden lg:flex'; ?> w-full lg:w-1/2 flex-col items-center justify-center px-10 lg:px-24 py-12 bg-white/90 backdrop-blur-sm h-full overflow-y-auto">
      <div class="w-full max-w-sm animate-slide-up">
        <div class="text-center lg:text-left mb-12">
          <h2 class="text-4xl lg:text-5xl font-black text-sync-blue mb-3">Welcome</h2>
          <p class="text-zinc-500 font-medium">Please enter your details to sign in.</p>
        </div>

        <form id="loginForm" action="index.php" method="POST" class="space-y-6">
          <div class="space-y-2">
            <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Email Address</label>
            <input type="text" id="email" name="email" placeholder="Email" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>
          </div>
          <div class="space-y-2 relative">
            <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Password</label>
            <input type="password" name="password" placeholder="Password" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>
            <div class="flex justify-end mt-2 pr-1">
              <a href="forgot_password.php" class="text-[10px] font-bold text-[#2d3494] uppercase tracking-widest hover:underline transition-colors">Forgot Password?</a>
            </div>
          </div>
          <button type="submit" class="w-full py-4 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-2xl font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 uppercase tracking-widest">Sign In</button>
        </form>

        <p class="mt-12 text-center text-sm font-semibold text-zinc-400">
          Don't have an account? <a href="signup.php" class="text-sync-blue hover:text-indigo-800 transition-colors underline underline-offset-4">Register here</a>
        </p>
      </div>
    </div>
  </div>

  <!-- Simple Modal -->
  <div id="customModal" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-message" id="modalMessage"></div>
      <button onclick="closeModal()" class="modal-btn">OK</button>
    </div>
  </div>

  <script>
    function startApp() {
      document.getElementById('brandingSection').classList.add('hidden');
      const formSection = document.getElementById('formSection');
      formSection.classList.remove('hidden');
      formSection.classList.add('flex');
    }

    <?php if ($errorMsg): ?>
      showModal("<?php echo addslashes($errorMsg); ?>");
    <?php endif; ?>
  </script>
</body>
</html>