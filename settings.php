<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?show=form");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Settings</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  
  <script src="https://unpkg.com/feather-icons"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            background: '#ffffff',
            foreground: '#111827',
            card: '#ffffff',
            primary: '#030213',
            muted: '#ececf0',
            'muted-foreground': '#717182',
            border: 'rgba(0, 0, 0, 0.1)',
            indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
            emerald: { 50: '#ecfdf5', 600: '#059669' },
            rose: { 50: '#fff1f2', 600: '#e11d48' }
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] }
        }
      }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base {
      body { @apply bg-zinc-50 text-foreground font-sans antialiased; }
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-zinc-300 rounded-full; }
    ::-webkit-scrollbar-thumb:hover { @apply bg-zinc-400; }
    
    .animate-page-fade { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .sidebar-link { @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-muted-foreground font-medium transition-all duration-200 ease-in-out; }
    .sidebar-link:hover { @apply bg-indigo-50 text-indigo-700; }
    .sidebar-link.active { @apply bg-indigo-50 text-indigo-600; }
    
    /* Settings Tab Links */
    .settings-tab { @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors w-full text-left cursor-pointer; }
    .settings-tab.active { @apply bg-indigo-50 text-indigo-700; }
    .settings-tab:not(.active) { @apply text-muted-foreground hover:bg-zinc-100 hover:text-foreground; }

    /* Custom Toggle Switch Animation */
    .toggle-checkbox:checked { @apply right-0 border-indigo-600; right: 0; }
    .toggle-checkbox:checked + .toggle-label { @apply bg-indigo-600; }
    .toggle-checkbox:checked + .toggle-label:after { transform: translateX(100%); border-color: white; }
  </style>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="flex h-screen overflow-hidden bg-zinc-50">

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 md:relative md:flex w-64 flex-col bg-white border-r border-border h-full transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center justify-between px-6 border-b border-border">
      <div class="flex items-center gap-2 font-bold text-xl text-primary">
        <div class="p-1.5 bg-indigo-600 rounded-lg shadow-inner"><i data-feather="repeat" class="w-5 h-5 text-white"></i></div>
        SpendSync
      </div>
      <button id="closeSidebar" class="md:hidden p-1.5 text-muted-foreground hover:bg-zinc-100 rounded-md"><i data-feather="x" class="w-5 h-5"></i></button>
    </div>
    
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="credit-card" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="target" class="w-5 h-5"></i> Budgets</a>
      <a href="goals.php" class="sidebar-link"><i data-feather="award" class="w-5 h-5"></i> Goals</a>
      <a href="settings.php" class="sidebar-link active"><i data-feather="settings" class="w-5 h-5"></i> Settings</a>
    </nav>
    
    <div class="p-4 border-t border-border">
      <a href="logout.php" class="sidebar-link hover:bg-rose-50 hover:text-rose-600"><i data-feather="log-out" class="w-5 h-5"></i> Logout</a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative">
    
    <header class="h-16 flex items-center justify-between px-4 sm:px-6 bg-white border-b border-border shrink-0 z-10 relative">
      <button id="menuButton" class="md:hidden p-2 text-muted-foreground hover:bg-zinc-100 rounded-md transition-colors"><i data-feather="menu" class="w-5 h-5"></i></button>
      
      <div class="flex items-center gap-4 ml-auto">
        <button class="p-2 text-muted-foreground hover:bg-zinc-100 rounded-full relative transition-colors">
          <i data-feather="bell" class="w-5 h-5"></i>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full border border-white"></span>
        </button>
        <a href="profile.php" class="h-8 w-8 rounded-full bg-indigo-100 border border-indigo-200 overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-300 transition-all">
          <img src="https://i.pravatar.cc/150?u=spendSyncUser" alt="Profile" class="w-full h-full object-cover">
        </a>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-4 sm:p-6 lg:p-8 animate-page-fade">
      
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Settings</h1>
        <p class="text-muted-foreground text-sm mt-1">Manage your account preferences and security.</p>
      </div>

      <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[500px]">
        
        <div class="w-full md:w-64 border-b md:border-b-0 md:border-r border-border p-4 sm:p-6 bg-zinc-50/50">
          <nav class="space-y-1">
            <button class="settings-tab active" data-target="tab-profile">
              <i data-feather="user" class="w-4 h-4"></i> Profile Details
            </button>
            <button class="settings-tab" data-target="tab-notifications">
              <i data-feather="bell" class="w-4 h-4"></i> Notifications
            </button>
            <button class="settings-tab" data-target="tab-security">
              <i data-feather="shield" class="w-4 h-4"></i> Security
            </button>
            <button class="settings-tab" data-target="tab-preferences">
              <i data-feather="sliders" class="w-4 h-4"></i> Preferences
            </button>
          </nav>
        </div>

        <div class="flex-1 p-6 sm:p-8">
          
          <div id="tab-profile" class="tab-content block animate-page-fade">
            <h2 class="text-lg font-semibold text-foreground mb-6">Profile Information</h2>
            
            <div class="flex items-center gap-6 mb-8">
              <div class="h-20 w-20 rounded-full bg-indigo-100 border-2 border-indigo-200 overflow-hidden shrink-0">
                <img src="https://i.pravatar.cc/150?u=spendSyncUser" alt="Profile" class="w-full h-full object-cover">
              </div>
              <div>
                <button class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors mb-2">Change Avatar</button>
                <p class="text-xs text-muted-foreground">JPG, GIF or PNG. 1MB max.</p>
              </div>
            </div>

            <form class="space-y-5 max-w-lg" onsubmit="event.preventDefault(); showToast('Profile updated successfully!');">
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Full Name</label>
                <input type="text" value="Brett Michael R. Pagdanganan" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Email Address</label>
                <input type="email" value="pagdangananbrettmichael@gmail.com" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Phone Number</label>
                <input type="tel" value="0950-606-9129" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              
              <div class="pt-4 border-t border-border mt-6">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                  <i data-feather="save" class="w-4 h-4"></i> Save Changes
                </button>
              </div>
            </form>
          </div>

          <div id="tab-notifications" class="tab-content hidden animate-page-fade">
            <h2 class="text-lg font-semibold text-foreground mb-2">Notification Preferences</h2>
            <p class="text-sm text-muted-foreground mb-6">Choose what updates you want to receive.</p>

            <div class="space-y-6 max-w-2xl">
              <div class="flex items-center justify-between py-4 border-b border-border">
                <div class="flex-1 pr-4">
                  <h4 class="text-sm font-medium text-foreground">Push Notifications</h4>
                  <p class="text-sm text-muted-foreground mt-0.5">Receive alerts when you exceed your budget limits.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" checked>
                  <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between py-4 border-b border-border">
                <div class="flex-1 pr-4">
                  <h4 class="text-sm font-medium text-foreground">Weekly Summary Email</h4>
                  <p class="text-sm text-muted-foreground mt-0.5">Get a weekly email detailing your spending habits.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer" checked>
                  <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between py-4 border-b border-border">
                <div class="flex-1 pr-4">
                  <h4 class="text-sm font-medium text-foreground">Goal Milestones</h4>
                  <p class="text-sm text-muted-foreground mt-0.5">Alert me when I reach certain percentages of my goals.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" class="sr-only peer">
                  <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
              </div>
            </div>
          </div>

          <div id="tab-security" class="tab-content hidden animate-page-fade">
            <h2 class="text-lg font-semibold text-foreground mb-6">Security Settings</h2>
            
            <form class="space-y-5 max-w-lg mb-10" onsubmit="event.preventDefault(); showToast('Password updated successfully!');">
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Current Password</label>
                <input type="password" placeholder="••••••••" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">New Password</label>
                <input type="password" placeholder="••••••••" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Confirm New Password</label>
                <input type="password" placeholder="••••••••" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
              </div>
              
              <div class="pt-4 border-t border-border mt-6">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                  <i data-feather="key" class="w-4 h-4"></i> Update Password
                </button>
              </div>
            </form>

            <div class="pt-8 border-t border-border max-w-2xl">
              <h3 class="text-base font-semibold text-foreground mb-2">Two-Factor Authentication (2FA)</h3>
              <p class="text-sm text-muted-foreground mb-4">Add an extra layer of security to your account. We'll ask for a code in addition to your password when you log in.</p>
              <button type="button" class="px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-lg transition-colors">
                Enable 2FA
              </button>
            </div>
          </div>

          <div id="tab-preferences" class="tab-content hidden animate-page-fade">
            <h2 class="text-lg font-semibold text-foreground mb-6">App Preferences</h2>
            
            <div class="space-y-5 max-w-lg">
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Default Currency</label>
                <select class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                  <option>PHP - Philippine Peso (₱)</option>
                  <option>USD - US Dollar ($)</option>
                  <option>EUR - Euro (€)</option>
                  <option>JPY - Japanese Yen (¥)</option>
                </select>
              </div>
              
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Date Format</label>
                <select class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                  <option>MM/DD/YYYY (10/25/2023)</option>
                  <option>DD/MM/YYYY (25/10/2023)</option>
                  <option>MMM DD, YYYY (Oct 25, 2023)</option>
                </select>
              </div>

              <div class="pt-4 border-t border-border mt-6">
                <button type="button" onclick="showToast('Preferences saved!')" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                  Save Preferences
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <div id="toast" class="fixed bottom-4 right-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transition-all duration-300 translate-y-20 opacity-0 z-50">
    <i data-feather="check-circle" class="w-5 h-5 text-emerald-600"></i>
    <span id="toastMessage" class="text-sm font-medium">Success!</span>
  </div>

  <script>
    feather.replace();

    // Mobile Sidebar Logic
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
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

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Tab Navigation Logic
    const tabs = document.querySelectorAll('.settings-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Hide all content
            tabContents.forEach(content => content.classList.add('hidden'));

            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Show corresponding content
            const targetId = tab.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');
        });
    });

    // Toast Notification Logic
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }
  </script>
</body>
</html>