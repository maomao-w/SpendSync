<?php
include 'config.php';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $security_question = mysqli_real_escape_string($conn, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($conn, $_POST['security_answer']);

    $sql = "INSERT INTO users (full_name, email, password, security_question, security_answer) VALUES ('$full_name', '$email', '$password', '$security_question', '$security_answer')";
    if (mysqli_query($conn, $sql)) {
        $success = true;
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
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
            'fade-up': 'fadeUp 0.6s ease-out forwards',
            'blob': 'blob 7s infinite',
            'gradient-shift': 'gradientShift 12s ease infinite',
            'float-slow': 'float 8s ease-in-out infinite'
          },
          keyframes: {
            fadeUp: { 'from': { opacity: 0, transform: 'translateY(15px)' }, 'to': { opacity: 1, transform: 'translateY(0)' } },
            blob: { '0%': { transform: 'translate(0px, 0px) scale(1)' }, '33%': { transform: 'translate(30px, -50px) scale(1.1)' }, '66%': { transform: 'translate(-20px, 20px) scale(0.9)' }, '100%': { transform: 'translate(0px, 0px) scale(1)' } },
            gradientShift: { '0%': { backgroundPosition: '0% 50%' }, '50%': { backgroundPosition: '100% 50%' }, '100%': { backgroundPosition: '0% 50%' } },
            float: { '0%': { transform: 'translateY(0px) rotate(0deg)' }, '50%': { transform: 'translateY(-15px) rotate(2deg)' }, '100%': { transform: 'translateY(0px) rotate(0deg)' } }
          }
        }
      }
    }

    function showModal(message) {
      const modal = document.getElementById('customModal');
      const modalMessage = document.getElementById('modalMessage');
      modalMessage.innerText = message;
      modal.classList.add('active');
    }

    function closeModal() {
      document.getElementById('customModal').classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
      const signupForm = document.getElementById('signupForm');
      if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
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

  <div class="w-full h-full lg:h-[92vh] lg:w-[96vw] lg:max-w-6xl flex shadow-2xl lg:rounded-[2.5rem] overflow-hidden bg-white/80 backdrop-blur-sm border border-white/20">
    
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-[#2d3494] via-indigo-700 to-[#1e1a6b] p-16 flex-col justify-center items-center text-white text-center relative overflow-hidden animate-gradient">
      <div class="absolute top-0 -left-10 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob"></div>
      <div class="absolute -bottom-10 right-0 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-2000"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
      <div class="relative z-10 animate-fade-up flex flex-col items-center">
        <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" 
             alt="SpendSync Logo" 
             class="w-36 h-36 mb-8 object-contain drop-shadow-2xl animate-float-slow">
        <h1 class="text-6xl font-black uppercase tracking-tight italic drop-shadow-lg">Sign Up</h1>
        <p class="mt-6 text-indigo-50 text-lg opacity-90 max-w-xs font-medium">Empower your financial future and master responsible spending with SpendSync.</p>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center px-10 lg:px-24 py-12 bg-white/90 backdrop-blur-sm overflow-y-auto">
      <div class="w-full max-w-sm animate-fade-up">
        <div class="text-center lg:text-left mb-8">
          <h2 class="text-4xl lg:text-5xl font-black text-sync-blue uppercase italic">Register</h2>
          <p class="text-zinc-500 font-medium">Let’s build a smarter budget for you.</p>
        </div>

        <form id="signupForm" action="signup.php" method="POST" class="space-y-5">
          <input type="text" name="full_name" placeholder="Full Name" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>
          
          <input type="text" id="email" name="email" placeholder="Email Address" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>
          
          <div class="space-y-3">
            <select name="security_question" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium text-zinc-500" required>
                <option value="" disabled selected>Select a Security Question</option>
                <option value="What city were you born in?">What city were you born in?</option>
                <option value="What is the name of your first pet?">What is the name of your first pet?</option>
                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                <option value="What was the name of your first school?">What was the name of your first school?</option>
            </select>
          </div>

          <input type="text" name="security_answer" placeholder="Security Answer" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>

          <div class="space-y-3">
            <input type="password" id="password" name="password" placeholder="Create Password" 
                   oninput="validatePassword(this.value)"
                   class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-sync-blue/10 focus:border-sync-blue transition-all duration-300 font-medium" required>
            
            <div class="grid grid-cols-1 gap-1.5 px-2">
                <p id="rule-length" class="text-[10px] font-bold text-zinc-400 transition-colors uppercase tracking-wider flex items-center gap-2">
                    <span id="dot-length" class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span> Min. 8 Characters
                </p>
                <p id="rule-upper" class="text-[10px] font-bold text-zinc-400 transition-colors uppercase tracking-wider flex items-center gap-2">
                    <span id="dot-upper" class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span> Starts with Uppercase
                </p>
                <p id="rule-number" class="text-[10px] font-bold text-zinc-400 transition-colors uppercase tracking-wider flex items-center gap-2">
                    <span id="dot-number" class="w-1.5 h-1.5 rounded-full bg-zinc-300"></span> Must include a Number
                </p>
            </div>
          </div>

          <button type="submit" id="submitBtn" class="w-full py-4 bg-zinc-400 text-white rounded-2xl font-bold uppercase tracking-widest shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 cursor-not-allowed" disabled>Register</button>
        </form>

        <p class="mt-10 text-center text-sm font-semibold text-zinc-400">
          Already have an account? <a href="index.php?show=form" class="text-sync-blue underline underline-offset-4 font-bold hover:text-indigo-700 transition-colors">Log In</a>
        </p>
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
          text.classList.replace('text-zinc-400', 'text-emerald-500');
          dot.classList.replace('bg-zinc-300', 'bg-emerald-500');
        } else {
          text.classList.remove('text-emerald-500');
          text.classList.add('text-zinc-400');
          dot.classList.remove('bg-emerald-500');
          dot.classList.add('bg-zinc-300');
        }
      }
      const allPassed = Object.values(checks).every(v => v === true);
      if (allPassed) {
        btn.disabled = false;
        btn.classList.replace('bg-zinc-400', 'bg-[#4f46e5]');
        btn.classList.remove('cursor-not-allowed');
      } else {
        btn.disabled = true;
        btn.classList.replace('bg-[#4f46e5]', 'bg-zinc-400');
        btn.classList.add('cursor-not-allowed');
      }
    }

    <?php if ($success): ?>
      showModal("Account created successfully!");
      const modalBtn = document.querySelector('#customModal .modal-btn');
      if (modalBtn) {
        modalBtn.onclick = function() {
          closeModal();
          window.location.href = 'index.php?show=form';
        };
      }
    <?php endif; ?>
  </script>
</body>
</html>