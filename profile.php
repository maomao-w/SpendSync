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
  <title>SpendSync - Profile</title>
  
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
    
    .animate-modal { animation: modalFadeIn 0.2s ease-out forwards; }
    @keyframes modalFadeIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
  </style>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 animate-page-fade">

  <div class="pt-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
    <a href="homepage.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-border hover:bg-zinc-50 rounded-lg transition-colors shadow-sm">
      <i data-feather="arrow-left" class="w-4 h-4"></i>
      Back to Dashboard
    </a>
  </div>
  
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="bg-card rounded-xl shadow-sm border border-border p-6 sm:p-8">
      
      <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center mb-10 pb-10 border-b border-border">
        
        <div class="relative shrink-0 group">
          <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white bg-indigo-100 overflow-hidden shadow-md">
            <img src="https://i.pravatar.cc/150?u=spendSyncUser" alt="Profile" class="w-full h-full object-cover group-hover:opacity-75 transition-opacity">
          </div>
          <button class="absolute bottom-0 right-0 p-2 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors border-2 border-white">
            <i data-feather="camera" class="w-4 h-4"></i>
          </button>
        </div>
        
        <div class="flex-1">
          <h1 class="text-2xl sm:text-3xl font-bold text-foreground">Juan Dela Cruz</h1>
          <p class="text-muted-foreground mt-1">Personal Account</p>
        </div>
        
        <button id="openEditModalBtn" class="flex items-center gap-2 px-4 py-2.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium rounded-lg transition-colors w-full sm:w-auto justify-center">
          <i data-feather="edit" class="w-4 h-4"></i>
          Edit Profile
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div>
          <h3 class="text-lg font-semibold text-foreground mb-4">Personal Information</h3>
          <ul class="space-y-4">
            <li class="flex items-center gap-3">
              <div class="p-2 bg-zinc-100 rounded-md text-zinc-600">
                <i data-feather="mail" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Email Address</p>
                <p class="font-medium text-foreground">juan.delacruz@example.com</p>
              </div>
            </li>
            <li class="flex items-center gap-3">
              <div class="p-2 bg-zinc-100 rounded-md text-zinc-600">
                <i data-feather="map-pin" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Location</p>
                <p class="font-medium text-foreground">Manila, Philippines</p>
              </div>
            </li>
            <li class="flex items-center gap-3">
              <div class="p-2 bg-zinc-100 rounded-md text-zinc-600">
                <i data-feather="calendar" class="w-5 h-5"></i>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">Member Since</p>
                <p class="font-medium text-foreground">October 2023</p>
              </div>
            </li>
          </ul>
        </div>

        <div>
          <h3 class="text-lg font-semibold text-foreground mb-4">Account Security</h3>
          <ul class="space-y-4">
            <li class="flex items-center gap-3 border border-border p-4 rounded-xl bg-zinc-50">
              <div class="p-2 bg-emerald-100 text-emerald-600 rounded-full">
                <i data-feather="shield" class="w-5 h-5"></i>
              </div>
              <div class="flex-1">
                <p class="font-medium text-foreground">Password Setup</p>
                <p class="text-sm text-muted-foreground">Last changed 2 months ago</p>
              </div>
              <a href="settings.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Update</a>
            </li>
            <li class="flex items-center gap-3 border border-border p-4 rounded-xl bg-zinc-50">
              <div class="p-2 bg-zinc-200 text-zinc-600 rounded-full">
                <i data-feather="lock" class="w-5 h-5"></i>
              </div>
              <div class="flex-1">
                <p class="font-medium text-foreground">Two-Factor Authentication</p>
                <p class="text-sm text-muted-foreground">Currently disabled</p>
              </div>
              <a href="settings.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Enable</a>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>

  <div id="modalBackdrop" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div id="editProfileModal" class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden hidden animate-modal">
        <div class="flex justify-between items-center p-5 border-b border-border">
            <h2 class="text-lg font-bold text-foreground">Edit Profile</h2>
            <button id="closeModalBtn" class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="p-5 space-y-4">
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Full Name</label>
                <input type="text" value="Juan Dela Cruz" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Email Address</label>
                <input type="email" value="juan.delacruz@example.com" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
            </div>
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Location</label>
                <input type="text" value="Manila, Philippines" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
            </div>
        </div>

        <div class="p-5 border-t border-border flex justify-end gap-3 bg-zinc-50">
            <button id="cancelModalBtn" class="px-4 py-2 text-sm font-medium text-muted-foreground bg-white border border-border hover:bg-zinc-50 rounded-lg transition-colors">
                Cancel
            </button>
            <button id="saveBtn" class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                <i data-feather="save" class="w-4 h-4"></i>
                Save Changes
            </button>
        </div>
    </div>
  </div>

  <script>
    // Initialize Icons
    feather.replace();

    // Modal Logic
    const openEditModalBtn = document.getElementById('openEditModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const saveBtn = document.getElementById('saveBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const editProfileModal = document.getElementById('editProfileModal');

    function openModal() {
        modalBackdrop.classList.remove('hidden');
        editProfileModal.classList.remove('hidden');
    }

    function closeModal() {
        modalBackdrop.classList.add('hidden');
        editProfileModal.classList.add('hidden');
    }

    openEditModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelModalBtn.addEventListener('click', closeModal);
    saveBtn.addEventListener('click', closeModal);

    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) closeModal();
    });
  </script>
</body>
</html>