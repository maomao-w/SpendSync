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
            $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>No account found with that email address.</div>";
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
            $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>Incorrect answer. Please try again.</div>";
        }
    } elseif (isset($_POST['reset_password'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE email = '$email'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "<div class='bg-emerald-50 text-emerald-600 p-4 rounded-xl text-sm font-medium mt-4 text-center'>Password updated successfully! <br><br> <a href='index.php?show=form' class='underline font-bold text-emerald-700'>Click here to login</a></div>";
                $step = 4;
            } else {
                $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>Error updating password. Please try again.</div>";
                $step = 3;
            }
        } else {
            $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>Passwords do not match.</div>";
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
  <title>SpendSync - Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-zinc-50 min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-zinc-100 p-8">
    
    <div class="text-center mb-10 flex flex-col items-center">
      <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">
        SpendSync
      </h1>
      <p class="text-zinc-500 font-medium mt-1">Reset your password</p>
    </div>

    <?php echo $message; ?>

    <?php if ($step == 1): ?>
    <form method="POST" action="" class="space-y-6 mt-4">
      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Registered Email</label>
        <input type="email" name="email" placeholder="Enter your email" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <button type="submit" name="check_email" class="w-full py-4 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-2xl font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 uppercase tracking-widest">
        Continue
      </button>
    </form>
    <?php endif; ?>

    <?php if ($step == 2): ?>
    <form method="POST" action="" class="space-y-6 mt-4">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
      <input type="hidden" name="question" value="<?php echo htmlspecialchars($question); ?>">
      
      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Security Question</label>
        <div class="w-full px-5 py-4 rounded-2xl bg-zinc-100 border border-zinc-200 text-zinc-700 font-medium">
            <?php echo htmlspecialchars($question); ?>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Your Answer</label>
        <input type="text" name="answer" placeholder="Enter your answer" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <button type="submit" name="verify_answer" class="w-full py-4 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-2xl font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 uppercase tracking-widest">
        Verify Answer
      </button>
    </form>
    <?php endif; ?>

    <?php if ($step == 3): ?>
    <form method="POST" action="" class="space-y-6 mt-4">
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
      
      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">New Password</label>
        <input type="password" name="new_password" placeholder="Enter new password" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm new password" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <button type="submit" name="reset_password" class="w-full py-4 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-2xl font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 uppercase tracking-widest">
        Update Password
      </button>
    </form>
    <?php endif; ?>

    <?php if ($step != 4): ?>
    <p class="mt-8 text-center text-sm font-semibold text-zinc-400">
      Remembered your password? <a href="index.php?show=form" class="text-[#4f46e5] hover:text-indigo-800 transition-colors underline underline-offset-4">Log in here</a>
    </p>
    <?php endif; ?>

  </div>

</body>
</html>