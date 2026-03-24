<?php
include 'config.php';

$message = '';
$valid_token = false;

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    $query = "SELECT * FROM users WHERE reset_token = '$token'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $valid_token = true;
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_query = "UPDATE users SET password = '$hashed_password', reset_token = NULL WHERE reset_token = '$token'";
                if (mysqli_query($conn, $update_query)) {
                    $message = "<div class='bg-emerald-50 text-emerald-600 p-4 rounded-xl text-sm font-medium mt-4 text-center'>Password updated successfully! <br><br> <a href='login.php?show=form' class='underline font-bold text-emerald-700'>Click here to login</a></div>";
                    $valid_token = false; 
                } else {
                    $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>Error updating password. Please try again.</div>";
                }
            } else {
                $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4'>Passwords do not match.</div>";
            }
        }
    } else {
        $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4 text-center'>Invalid or expired reset token. <br><br> <a href='forgot_password.php' class='underline font-bold text-rose-700'>Request a new link</a></div>";
    }
} else {
    $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-xl text-sm font-medium mt-4 text-center'>No reset token provided. <br><br> <a href='forgot_password.php' class='underline font-bold text-rose-700'>Go to Forgot Password page</a></div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="bg-zinc-50 min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-zinc-100 p-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-extrabold tracking-tight mb-2">
        <span class="bg-gradient-to-r from-[#4f46e5] to-cyan-500 bg-clip-text text-transparent">SpendSync</span>
      </h1>
      <p class="text-zinc-500 font-medium">Create a new password</p>
    </div>

    <?php echo $message; ?>

    <?php if ($valid_token): ?>
    <form method="POST" action="" class="space-y-6 mt-4">
      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">New Password</label>
        <input type="password" name="new_password" placeholder="Enter new password" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <div class="space-y-2">
        <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] ml-1">Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm new password" class="w-full px-5 py-4 rounded-2xl bg-zinc-50 border border-zinc-200 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-300 font-medium" required>
      </div>

      <button type="submit" class="w-full py-4 bg-[#4f46e5] hover:bg-[#4338ca] text-white rounded-2xl font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 transition-all duration-300 uppercase tracking-widest">
        Update Password
      </button>
    </form>
    <?php endif; ?>

  </div>

</body>
</html>