<?php
include 'session_manager.php';
include 'config.php';

// PATAYIN ANG STRICT ERRORS PARA HINDI MAG-500 ERROR SA SERVER NIYO
mysqli_report(MYSQLI_REPORT_OFF);

$user_id = $_SESSION['user_id'];
$toast_msg = '';

// SAFE DATABASE UPDATE: Try-Catch and silent addition
try {
    $check_prefs = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'pref_push'");
    if ($check_prefs && mysqli_num_rows($check_prefs) == 0) {
        @mysqli_query($conn, "ALTER TABLE `users` ADD `phone` VARCHAR(50) NULL, ADD `avatar` VARCHAR(255) NULL, ADD `pref_push` TINYINT(1) DEFAULT 1, ADD `pref_email` TINYINT(1) DEFAULT 1, ADD `pref_milestone` TINYINT(1) DEFAULT 0, ADD `currency` VARCHAR(10) DEFAULT 'PHP', ADD `date_format` VARCHAR(20) DEFAULT 'MM/DD/YYYY', ADD `two_factor_enabled` TINYINT(1) DEFAULT 0");
    }
} catch (Exception $e) { }

// FETCH CURRENT USER DATA (BEFORE UPDATE)
$user_query = @mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
$user_data = $user_query ? mysqli_fetch_assoc($user_query) : [];

// HANDLE FORMS NANG MAAYOS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Profile Update (With Picture)
    if (isset($_POST['update_profile_settings'])) {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';

        $avatar_path = $user_data['avatar'] ?? '';

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $target_file = $upload_dir . 'avatar_' . $user_id . '_' . time() . '.' . $ext;
            if (@move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file) || @copy($_FILES['avatar']['tmp_name'], $target_file)) {
                $avatar_path = $target_file;
            }
        }
        
        @mysqli_query($conn, "UPDATE users SET full_name='$full_name', email='$email', phone='$phone', avatar='$avatar_path' WHERE user_id='$user_id'");
        $_SESSION['full_name'] = $full_name;
        $toast_msg = 'Profile updated successfully!';
        
        // Refresh User Data After Update
        $user_query = @mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
        $user_data = mysqli_fetch_assoc($user_query);
    }
    
    // 2. Notification Preferences
    elseif (isset($_POST['update_notifications'])) {
        $push = isset($_POST['pref_push']) ? 1 : 0;
        $email_pref = isset($_POST['pref_email']) ? 1 : 0;
        $milestone = isset($_POST['pref_milestone']) ? 1 : 0;
        @mysqli_query($conn, "UPDATE users SET pref_push='$push', pref_email='$email_pref', pref_milestone='$milestone' WHERE user_id='$user_id'");
        $toast_msg = 'Notification preferences saved!';
        
        $user_data['pref_push'] = $push;
        $user_data['pref_email'] = $email_pref;
        $user_data['pref_milestone'] = $milestone;
    }
    
    // 3. Security (Gumaganang Password Change!)
    elseif (isset($_POST['update_security'])) {
        $curr_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $conf_pass = $_POST['confirm_password'];

        if (password_verify($curr_pass, $user_data['password'])) {
            if ($new_pass === $conf_pass) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                @mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE user_id='$user_id'");
                $toast_msg = 'Password updated successfully!';
                $user_data['password'] = $hashed;
            } else {
                $toast_msg = 'New passwords do not match!';
            }
        } else {
            $toast_msg = 'Incorrect current password!';
        }
    }

    // 3.5 Security (2FA Toggle)
    elseif (isset($_POST['toggle_2fa'])) {
        $new_2fa = empty($user_data['two_factor_enabled']) ? 1 : 0;
        @mysqli_query($conn, "UPDATE users SET two_factor_enabled='$new_2fa' WHERE user_id='$user_id'");
        $user_data['two_factor_enabled'] = $new_2fa;
        $toast_msg = $new_2fa ? '2FA Enabled successfully!' : '2FA Disabled successfully!';
    }
    
    // 4. App Preferences (Currency / Date Format)
    elseif (isset($_POST['update_preferences'])) {
        $currency = mysqli_real_escape_string($conn, $_POST['currency']);
        $date_format = mysqli_real_escape_string($conn, $_POST['date_format']);
        @mysqli_query($conn, "UPDATE users SET currency='$currency', date_format='$date_format' WHERE user_id='$user_id'");
        $toast_msg = 'App preferences saved!';
        
        $user_data['currency'] = $currency;
        $user_data['date_format'] = $date_format;
    }
}

$display_name = $user_data['full_name'] ?? 'User';
$display_email = $user_data['email'] ?? 'user@example.com';
$display_phone = $user_data['phone'] ?? '';

// DYNAMIC AVATAR
$avatar_url = !empty($user_data['avatar']) ? $user_data['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($display_name).'&background=random';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Settings</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            background: '#f4f7f9', 
            foreground: '#1e293b', 
            card: 'rgba(255, 255, 255, 0.55)', 
            primary: '#2563eb',    
            secondary: '#10b981',  
            muted: '#94a3b8',      
            'muted-foreground': '#64748b', 
            border: 'rgba(255, 255, 255, 0.8)', 
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            title: ['Poppins', 'sans-serif'],
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            'wiggle': 'wiggle 1s ease-in-out infinite',
            'slide-up': 'slideUp 0.4s ease-out forwards',
          },
          keyframes: {
            fadeInUp: {
              '0%': { opacity: 0, transform: 'translateY(20px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
            },
            wiggle: {
              '0%, 100%': { transform: 'rotate(-10deg)' },
              '50%': { transform: 'rotate(10deg)' },
            },
            slideUp: {
              '0%': { opacity: 0, transform: 'translateY(20px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
            }
          }
        }
      }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base {
      body { @apply bg-background text-foreground font-sans antialiased; }
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-blue-200 rounded-full; border: 1px solid rgba(0,0,0,0.02); }

    .dashboard-card {
      @apply bg-card border border-border rounded-[2rem] p-6 transition-all duration-500 ease-out;
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.05), inset 0 1px 0 rgba(255,255,255,0.9);
    }

    .glass-sidebar {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        margin: 1rem 0 1rem 1rem;
        border-radius: 2rem;
        height: calc(100% - 2rem) !important;
        border: 1px solid rgba(255,255,255,0.8);
        box-shadow: 0 10px 40px rgba(37,99,235,0.05);
    }
    
    body > div.flex-1 {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        margin: 1rem 1rem 1rem 1rem;
        border-radius: 2rem;
        height: calc(100% - 2rem) !important;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 10px 40px rgba(37,99,235,0.05);
    }

    @media (max-width: 768px) {
        .glass-sidebar { margin: 0; border-radius: 0; height: 100% !important; }
        body > div.flex-1 { margin: 0; border-radius: 0; height: 100% !important; }
    }

    .sidebar-link { @apply flex items-center gap-3 px-5 py-3.5 mx-4 rounded-2xl text-muted-foreground font-medium transition-all duration-300 ease-in-out; }
    .sidebar-link:hover { @apply bg-white/70 text-primary translate-x-2 shadow-sm border border-white; }
    .sidebar-link.active { @apply bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-bold shadow-lg shadow-blue-500/30 border border-white/20; }

    .settings-tab { @apply flex items-center gap-4 px-5 py-3.5 rounded-2xl text-sm font-bold transition-all duration-300 w-full text-left cursor-pointer border border-transparent; }
    .settings-tab.active { @apply bg-white/80 border-white text-blue-600 shadow-sm shadow-blue-500/10; }
    .settings-tab:not(.active) { @apply text-slate-500 hover:bg-white/50 hover:border-white hover:text-slate-800; }

    .toggle-checkbox:checked { @apply right-0; }
    .toggle-checkbox:checked + .toggle-label { @apply bg-gradient-to-r from-blue-500 to-emerald-400 border-white; }
    .toggle-checkbox:checked + .toggle-label:after { transform: translateX(100%); border-color: white; }

    .bg-blobs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; overflow: hidden; z-index: -2; pointer-events: none; }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: floatBlob 15s infinite alternate; }
    .blob-blue { background: #2563eb; width: 500px; height: 500px; top: -10%; left: -10%; }
    .blob-emerald { background: #10b981; width: 400px; height: 400px; bottom: -10%; right: -10%; animation-delay: -5s; }
    @keyframes floatBlob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }
  </style>
</head>
<body class="flex h-screen overflow-hidden bg-background relative">

  <div class="bg-blobs">
    <div class="blob blob-blue"></div>
    <div class="blob blob-emerald"></div>
  </div>
  <div id="webgl-container" class="absolute inset-0 z-[-1]"></div>

  <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 md:relative md:flex w-64 flex-col glass-sidebar transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-24 flex items-center px-8 border-b border-white/60">
      <div class="flex items-center gap-3 font-bold text-2xl font-title tracking-tight hover:scale-105 transition-transform cursor-pointer">
        <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="h-8 w-auto object-contain">
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
      </div>
    </div>
    
    <nav class="flex-1 py-8 space-y-3 overflow-y-auto overflow-x-hidden">
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
      <a href="categories.php" class="sidebar-link"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>
      <a href="goals.php" class="sidebar-link"><i data-feather="target" class="w-5 h-5"></i> Goals</a>
      
      <a href="export_csv.php" class="sidebar-link"><i data-feather="download" class="w-5 h-5"></i> Download Records CSV</a>
      
      <form action="import_csv.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
        <label class="sidebar-link cursor-pointer w-full flex items-center gap-3 m-0">
          <i data-feather="upload" class="w-5 h-5"></i> <span class="flex-1">Import CSV</span>
          <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="this.form.submit()">
          <input type="hidden" name="import" value="1">
        </label>
      </form>
      
      <div class="pt-6 mt-6 border-t border-white/60">
        <a href="settings.php" class="sidebar-link active"><i data-feather="settings" class="w-5 h-5"></i> Settings</a>
      </div>
    </nav>
    
    <div class="p-4 mb-4">
      <a href="logout.php" class="sidebar-link hover:!bg-rose-50 hover:!text-rose-600 text-rose-500 font-bold">
        <i data-feather="log-out" class="w-5 h-5"></i> Logout
      </a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative z-10">
    
    <header class="h-20 sm:h-24 flex items-center justify-between px-4 sm:px-10 shrink-0 z-10 border-b border-white/40 relative">
      <div class="absolute left-4 sm:left-8 flex items-center gap-4">
        <button id="menuButton" class="md:hidden p-2 sm:p-2.5 bg-white/60 backdrop-blur-md rounded-xl border border-white text-slate-700">
          <i data-feather="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
        </button>
      </div>
      
      <div class="text-center animate-fade-in-up mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold font-title text-slate-800">Settings</h1>
        <p class="hidden sm:block text-muted-foreground text-sm font-medium mt-1">Manage your account preferences and security.</p>
      </div>
      
      <div class="absolute right-4 sm:right-8 flex items-center gap-5">
        
      </div>
    </header>

    <main class="flex-1 overflow-auto p-4 sm:p-8">
      
      <div class="dashboard-card !p-0 overflow-hidden flex flex-col md:flex-row min-h-[500px] animate-fade-in-up delay-100 border-0 mt-2">
        
        <div class="w-full md:w-72 border-b md:border-b-0 md:border-r border-white/40 p-4 sm:p-6 bg-white/20 overflow-x-auto">
          <nav class="flex flex-row md:flex-col gap-2 min-w-max md:min-w-0 pb-2 md:pb-0">
            <button class="settings-tab active shrink-0" data-target="tab-profile">
              <i data-feather="user" class="w-4 h-4"></i> Profile Details
            </button>
            <button class="settings-tab shrink-0" data-target="tab-notifications">
              <i data-feather="bell" class="w-4 h-4"></i> Notifications
            </button>
            <button class="settings-tab shrink-0" data-target="tab-security">
              <i data-feather="shield" class="w-4 h-4"></i> Security
            </button>
            <button class="settings-tab shrink-0" data-target="tab-preferences">
              <i data-feather="sliders" class="w-4 h-4"></i> Preferences
            </button>
          </nav>
        </div>

        <div class="flex-1 p-6 sm:p-10 relative">
          
          <div id="tab-profile" class="tab-content block animate-fade-in-up">
            <h2 class="text-xl font-bold font-title text-slate-800 mb-6">Profile Information</h2>
            
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-5 max-w-lg">
                <div class="flex items-center gap-6 mb-8">
                  <div class="h-24 w-24 rounded-[1.5rem] border-4 border-white overflow-hidden shrink-0 shadow-lg group cursor-pointer relative" onclick="document.getElementById('avatarUpload').click()">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Profile" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                        <i data-feather="camera" class="text-white w-6 h-6"></i>
                    </div>
                  </div>
                  <div>
                    <input type="file" name="avatar" id="avatarUpload" class="hidden" accept="image/*">
                    <button type="button" onclick="document.getElementById('avatarUpload').click()" class="px-5 py-2.5 text-sm font-bold text-slate-700 bg-white/80 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all mb-2">Change Avatar</button>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">JPG, GIF or PNG. 1MB max.</p>
                  </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($display_name); ?>" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($display_email); ?>" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($display_phone); ?>" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm">
                </div>
                
                <div class="pt-6 mt-6">
                    <button type="submit" name="update_profile_settings" class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">
                    <i data-feather="save" class="w-4 h-4"></i> Save Changes
                    </button>
                </div>
            </form>
          </div>

          <div id="tab-notifications" class="tab-content hidden animate-fade-in-up">
            <h2 class="text-xl font-bold font-title text-slate-800 mb-2">Notification Preferences</h2>
            <p class="text-sm font-medium text-slate-500 mb-8">Choose what updates you want to receive.</p>

            <form method="POST" action="">
                <div class="space-y-4 max-w-2xl">
                <div class="flex items-center justify-between p-5 bg-white/40 border border-white hover:bg-white/60 rounded-2xl transition-colors shadow-sm">
                    <div class="flex-1 pr-4">
                    <h4 class="text-sm font-bold text-slate-800">Push Notifications</h4>
                    <p class="text-xs font-medium text-slate-500 mt-1">Receive alerts when you exceed your budget limits.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="pref_push" class="sr-only peer toggle-checkbox" <?php echo (!isset($user_data['pref_push']) || $user_data['pref_push'] == 1) ? 'checked' : ''; ?>>
                    <div class="toggle-label w-12 h-6 bg-slate-200 border border-white rounded-full peer peer-focus:outline-none after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-5 bg-white/40 border border-white hover:bg-white/60 rounded-2xl transition-colors shadow-sm">
                    <div class="flex-1 pr-4">
                    <h4 class="text-sm font-bold text-slate-800">Weekly Summary Email</h4>
                    <p class="text-xs font-medium text-slate-500 mt-1">Get a weekly email detailing your spending habits.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="pref_email" class="sr-only peer toggle-checkbox" <?php echo (!isset($user_data['pref_email']) || $user_data['pref_email'] == 1) ? 'checked' : ''; ?>>
                    <div class="toggle-label w-12 h-6 bg-slate-200 border border-white rounded-full peer peer-focus:outline-none after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-5 bg-white/40 border border-white hover:bg-white/60 rounded-2xl transition-colors shadow-sm">
                    <div class="flex-1 pr-4">
                    <h4 class="text-sm font-bold text-slate-800">Goal Milestones</h4>
                    <p class="text-xs font-medium text-slate-500 mt-1">Alert me when I reach certain percentages of my goals.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="pref_milestone" class="sr-only peer toggle-checkbox" <?php echo (!empty($user_data['pref_milestone'])) ? 'checked' : ''; ?>>
                    <div class="toggle-label w-12 h-6 bg-slate-200 border border-white rounded-full peer peer-focus:outline-none after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                    </label>
                </div>
                </div>
                <div class="pt-6 mt-6">
                    <button type="submit" name="update_notifications" class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">
                    Save Preferences
                    </button>
                </div>
            </form>
          </div>

          <div id="tab-security" class="tab-content hidden animate-fade-in-up">
            <h2 class="text-xl font-bold font-title text-slate-800 mb-6">Security Settings</h2>
            
            <form method="POST" action="" class="space-y-5 max-w-lg mb-10">
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Current Password</label>
                <input type="password" name="current_password" placeholder="••••••••" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">New Password</label>
                <input type="password" name="new_password" placeholder="••••••••" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
              </div>
              
              <div class="pt-6 mt-6">
                <button type="submit" name="update_security" class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-slate-800 hover:bg-slate-700 hover:-translate-y-0.5 rounded-xl shadow-md transition-all">
                  <i data-feather="key" class="w-4 h-4"></i> Update Password
                </button>
              </div>
            </form>

            <div class="pt-8 border-t border-white/60 max-w-2xl">
              <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2"><i data-feather="shield" class="w-4 h-4 text-emerald-500"></i> Two-Factor Authentication (2FA)</h3>
              <p class="text-sm font-medium text-slate-500 mb-6">Add an extra layer of security to your account. We'll ask for a code in addition to your password when you log in.</p>
              
              <form method="POST" action="" class="w-full sm:w-auto">
                  <button type="submit" name="toggle_2fa" class="px-6 py-3 text-sm font-bold <?php echo empty($user_data['two_factor_enabled']) ? 'text-blue-600' : 'text-rose-600'; ?> bg-white/80 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all w-full sm:w-auto text-center">
                    <?php echo empty($user_data['two_factor_enabled']) ? 'Enable 2FA' : 'Disable 2FA'; ?>
                  </button>
              </form>
            </div>
          </div>

          <div id="tab-preferences" class="tab-content hidden animate-fade-in-up">
            <h2 class="text-xl font-bold font-title text-slate-800 mb-6">App Preferences</h2>
            
            <form method="POST" action="" class="space-y-6 max-w-lg">
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Default Currency</label>
                <select name="currency" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm cursor-pointer">
                  <option value="PHP" <?php echo ($user_data['currency'] ?? '') == 'PHP' ? 'selected' : ''; ?>>PHP - Philippine Peso (₱)</option>
                  <option value="USD" <?php echo ($user_data['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar ($)</option>
                  <option value="EUR" <?php echo ($user_data['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro (€)</option>
                  <option value="JPY" <?php echo ($user_data['currency'] ?? '') == 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen (¥)</option>
                </select>
              </div>
              
              <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Date Format</label>
                <select name="date_format" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm cursor-pointer">
                  <option value="MM/DD/YYYY" <?php echo ($user_data['date_format'] ?? '') == 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY (10/25/2026)</option>
                  <option value="DD/MM/YYYY" <?php echo ($user_data['date_format'] ?? '') == 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY (25/10/2026)</option>
                  <option value="MMM DD, YYYY" <?php echo ($user_data['date_format'] ?? '') == 'MMM DD, YYYY' ? 'selected' : ''; ?>>MMM DD, YYYY (Oct 25, 2026)</option>
                </select>
              </div>

              <div class="pt-6 mt-6">
                <button type="submit" name="update_preferences" class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">
                  Save Preferences
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </main>
  </div>

  <div id="toast" class="fixed bottom-6 right-6 bg-white/90 backdrop-blur-md border border-white text-emerald-600 px-5 py-4 rounded-2xl shadow-2xl flex items-center gap-3 transition-all duration-300 translate-y-20 opacity-0 z-50">
    <i data-feather="check-circle" class="w-5 h-5 text-emerald-500"></i>
    <span id="toastMessage" class="text-sm font-bold text-slate-800">Success!</span>
  </div>

  <script>
    feather.replace();

    <?php if(!empty($toast_msg)): ?>
    document.addEventListener('DOMContentLoaded', () => {
        showToast('<?php echo addslashes($toast_msg); ?>');
    });
    <?php endif; ?>

    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            sidebar.classList.remove('-translate-x-full');
        } else {
            overlay.classList.add('opacity-0');
            sidebar.classList.add('-translate-x-full');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    if(menuButton) menuButton.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);

    const tabs = document.querySelectorAll('.settings-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('animate-fade-in-up'); 
            });

            tab.classList.add('active');
            
            const targetId = tab.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            
            targetContent.classList.remove('hidden');
            void targetContent.offsetWidth; 
            targetContent.classList.add('animate-fade-in-up');
        });
    });

    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('animate-slide-up');
        
        setTimeout(() => {
            toast.classList.remove('animate-slide-up');
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }

    try {
        const container = document.getElementById('webgl-container');
        const scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0xf4f7f9, 0.035);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); 
        container.appendChild(renderer.domElement);

        scene.add(new THREE.AmbientLight(0xffffff, 1.2));
        const mainLight = new THREE.DirectionalLight(0xffffff, 1.5);
        mainLight.position.set(10, 20, 15);
        scene.add(mainLight);

        const blueDataMat = new THREE.MeshStandardMaterial({ color: 0x2563eb, roughness: 0.2, metalness: 0.5 });
        const emeraldCashMat = new THREE.MeshStandardMaterial({ color: 0x10b981, roughness: 0.5, metalness: 0.1 });
        const goldCoinMat = new THREE.MeshStandardMaterial({ color: 0xffd700, roughness: 0.2, metalness: 0.9 });

        const bgObjects = [];
        function spawnObject(geo, mat, yPosRange) {
            const mesh = new THREE.Mesh(geo, mat);
            mesh.position.set((Math.random() - 0.5) * 24, yPosRange, (Math.random() - 0.5) * 12 - 5);
            mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
            scene.add(mesh);
            bgObjects.push({
                mesh: mesh, rotX: (Math.random() - 0.5) * 0.02, rotY: (Math.random() - 0.5) * 0.02,
                floatSpeed: Math.random() * 0.01 + 0.005, initialY: mesh.position.y
            });
            return mesh;
        }

        for(let i = 0; i < 40; i++) {
            const randomType = Math.random();
            const yPos = 10 - (Math.random() * 25); 
            
            if(randomType < 0.33) {
                const coin = spawnObject(new THREE.CylinderGeometry(0.5, 0.5, 0.1, 32), goldCoinMat, yPos);
                coin.rotation.x = Math.PI / 2;
            } else if (randomType < 0.66) {
                spawnObject(new THREE.BoxGeometry(1.2, 0.6, 0.1), emeraldCashMat, yPos);
            } else {
                spawnObject(new THREE.OctahedronGeometry(0.6, 0), blueDataMat, yPos);
            }
        }

        camera.position.z = 9; camera.position.y = 2; 

        const clock = new THREE.Clock();
        let mouseX = 0, mouseY = 0;
        const windowHalfX = window.innerWidth / 2, windowHalfY = window.innerHeight / 2;

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
                obj.mesh.position.y = obj.initialY + Math.sin(elapsedTime * obj.floatSpeed * 50) * 0.8;
            });
            renderer.render(scene, camera);
        }
        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    } catch(e) { console.error("Three.js Error:", e); }
  </script>
</body>
</html>