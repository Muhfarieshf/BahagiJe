<?php
$identity = $this->request->getAttribute('identity');
$isLoggedIn = $identity !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($this->fetch('title')) ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <script>
        // Init dark mode immediately to prevent flash
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $this->Url->image('divideicon.png') ?>" />
    
    <!-- Hide Scrollbar Utility -->
    <style>
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-sans antialiased transition-colors duration-200">
    
    <!-- Global Navigation Bar -->
    <nav class="w-full bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-40 shadow-sm transition-colors duration-200">
        <div class="container mx-auto px-4 h-20 flex items-center justify-between">
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>" class="flex items-center hover:opacity-80 transition">
                <img src="<?= $this->Url->image('bahagije.png') ?>" alt="BahagiJe Logo" class="h-16 w-auto">
            </a>
            
            <div>
                <?php if ($isLoggedIn): ?>
                    <!-- Desktop Nav -->
                    <div class="hidden md:flex items-center gap-5">
                        <a href="#" onclick="openHowItWorksModal(); return false;" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> How it Works</a>
                        <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                        <div class="text-sm text-slate-600 dark:text-slate-300">
                            Hi, <span class="font-semibold text-slate-800 dark:text-slate-100"><?= h($identity->name) ?></span>
                        </div>
                        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition">Settings</a>
                        <a href="<?= $this->Url->build(['controller' => 'Auth', 'action' => 'logout']) ?>" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 px-3 py-1.5 rounded-lg transition border border-transparent hover:border-red-100 dark:hover:border-red-800/50">Logout</a>
                    </div>
                    <!-- Mobile Nav Actions -->
                    <div class="md:hidden flex items-center">
                        <a href="#" onclick="openHowItWorksModal(); return false;" class="p-2 -mr-2 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <a href="#" onclick="openHowItWorksModal(); return false;" class="flex text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition items-center gap-1.5">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                            <span class="hidden sm:inline">How it Works</span>
                        </a>
                        <div class="text-sm font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800/50 px-3 py-1.5 rounded-lg">
                            Guest Mode
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Theme Prompt Toast -->
    <div id="theme-prompt-toast" class="hidden fixed top-24 right-4 z-50 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl rounded-xl p-4 w-72 transition-colors duration-200">
        <div class="flex items-start justify-between">
            <div class="pr-4">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-1">Didn't prefer dark mode?</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Change back to light mode right away.</p>
                
                <!-- Quick Toggle -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="quickDarkModeToggle" class="sr-only peer" checked>
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <button type="button" onclick="dismissThemePrompt()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <main class="max-w-6xl mx-auto py-8 px-4 pb-24 md:pb-8">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>

    <!-- Global Footer -->
    <footer class="max-w-6xl mx-auto py-6 mt-8 border-t border-slate-200 dark:border-slate-800 text-center">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            &copy; <?= date('Y') ?> BahagiJe. All rights reserved.
        </p>
        <div class="mt-2 flex justify-center gap-4 text-xs font-medium">
            <a href="<?= $this->Url->build('/terms') ?>" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">Terms of Service</a>
            <a href="<?= $this->Url->build('/privacy') ?>" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors">Privacy Policy</a>
        </div>
    </footer>

    <?php if ($isLoggedIn): ?>
    <!-- Mobile Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] md:hidden transition-colors pb-[env(safe-area-inset-bottom)]">
        <div class="flex items-center justify-around h-16 px-2">
            <!-- Home -->
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>" class="flex-1 flex flex-col items-center justify-center h-full text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-semibold mt-1">Home</span>
            </a>
            <!-- Create (Popped out) -->
            <a href="<?= $this->Url->build(['controller' => 'GroupSessions', 'action' => 'create']) ?>" class="flex-1 flex flex-col items-center justify-start h-full relative group">
                <div class="absolute -top-5 w-14 h-14 rounded-full bg-blue-600 dark:bg-blue-500 border-4 border-white dark:border-slate-800 text-white flex items-center justify-center shadow-lg shadow-blue-600/30 group-hover:bg-blue-700 dark:group-hover:bg-blue-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-[10px] font-semibold mt-9 text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Create</span>
            </a>
            <!-- Profile -->
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'profile']) ?>" class="flex-1 flex flex-col items-center justify-center h-full text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-semibold mt-1">Profile</span>
            </a>
            <!-- Logout -->
            <a href="<?= $this->Url->build(['controller' => 'Auth', 'action' => 'logout']) ?>" class="flex-1 flex flex-col items-center justify-center h-full text-slate-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-semibold mt-1">Logout</span>
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <?= $this->fetch('script') ?>

    <!-- How It Works Modal -->
    <div id="howItWorksModal" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 dark:bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="howItWorksBackdrop" onclick="closeHowItWorksModal()"></div>
        
        <!-- Modal Content -->
        <div class="absolute inset-x-0 bottom-0 sm:static sm:inset-auto sm:flex sm:min-h-screen sm:items-center sm:justify-center p-0 sm:p-4 pointer-events-none">
            <div class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl w-full max-w-2xl mx-auto shadow-2xl transform transition-all translate-y-full sm:translate-y-0 sm:scale-95 opacity-0 pointer-events-auto flex flex-col max-h-[90vh]" id="howItWorksPanel">
                
                <!-- Header -->
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        How BahagiJe Works
                    </h2>
                    <button onclick="closeHowItWorksModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <!-- Body (Scrollable) -->
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 text-slate-600 dark:text-slate-300 space-y-6">
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                        <p class="font-medium text-blue-800 dark:text-blue-300">BahagiJe is designed to handle the messy math of group expenses, so you don't have to fight over the bill.</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">🎯 1. Choose the Right Preset</h3>
                        <div class="space-y-4 text-sm">
                            <div class="flex gap-3">
                                <div class="shrink-0 text-2xl">🍽️</div>
                                <div><strong class="text-slate-700 dark:text-slate-200 block">Food & Dining (Proportional)</strong> Automatically applies SST (e.g. 6%) and Service Charge (e.g. 10%) across everyone's subtotal. If you ordered an expensive steak, you pay a proportionally higher share of the tax.</div>
                            </div>
                            <div class="flex gap-3">
                                <div class="shrink-0 text-2xl">🚗</div>
                                <div><strong class="text-slate-700 dark:text-slate-200 block">Road Trip (Equal Split)</strong> Perfect for tolls and fuel. Enter the total amount, and it divides it equally among all participants automatically.</div>
                            </div>
                            <div class="flex gap-3">
                                <div class="shrink-0 text-2xl">✈️</div>
                                <div><strong class="text-slate-700 dark:text-slate-200 block">Long Trip (Collaborative Ledger)</strong> Keeps a running tally over multiple days. At the end of the trip, it uses <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300 px-1 rounded">Net-Debt Settlement</span> to minimize the number of bank transfers needed.</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">📱 2. Guests Join Instantly</h3>
                        <p class="text-sm">You (the Host) create the session. Your friends do NOT need an account or an app. They just scan your QR code or click your link, type their name, and they're in the live session.</p>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">💸 3. Settle Up Seamlessly</h3>
                        <p class="text-sm">Once the bill is fully added, the Host clicks <strong>"Lock & Calculate"</strong>. BahagiJe instantly generates a settlement list detailing exactly who owes who, and provides the Host's bank or QR pay details for one-click payments.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast Container -->
    <div id="global-toast-container" class="fixed bottom-4 right-4 z-[200] flex flex-col gap-2 pointer-events-none"></div>

    <!-- Global Confirm Modal -->
    <div id="global-confirm-modal" class="fixed inset-0 z-[200] hidden">
        <div class="absolute inset-0 bg-slate-900/40 dark:bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="global-confirm-backdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-sm shadow-2xl transform transition-all scale-95 opacity-0 pointer-events-auto overflow-hidden" id="global-confirm-panel">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2" id="global-confirm-title">Confirm Action</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400" id="global-confirm-message">Are you sure?</p>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex gap-3 justify-end border-t border-slate-100 dark:border-slate-700">
                    <button id="global-confirm-cancel" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-100 transition">Cancel</button>
                    <button id="global-confirm-ok" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 shadow-sm transition">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openHowItWorksModal() {
            const modal = document.getElementById('howItWorksModal');
            const backdrop = document.getElementById('howItWorksBackdrop');
            const panel = document.getElementById('howItWorksPanel');
            
            modal.classList.remove('hidden');
            // Trigger reflow
            void modal.offsetWidth;
            
            backdrop.classList.remove('opacity-0');
            if (window.innerWidth < 640) {
                panel.classList.remove('translate-y-full', 'opacity-0');
                panel.classList.add('opacity-100');
            } else {
                panel.classList.remove('opacity-0', 'sm:scale-95');
                panel.classList.add('opacity-100', 'sm:scale-100');
            }
        }

        function closeHowItWorksModal() {
            const modal = document.getElementById('howItWorksModal');
            const backdrop = document.getElementById('howItWorksBackdrop');
            const panel = document.getElementById('howItWorksPanel');
            
            backdrop.classList.add('opacity-0');
            if (window.innerWidth < 640) {
                panel.classList.remove('opacity-100');
                panel.classList.add('translate-y-full', 'opacity-0');
            } else {
                panel.classList.remove('opacity-100', 'sm:scale-100');
                panel.classList.add('opacity-0', 'sm:scale-95');
            }
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // --- Custom Toast Notifications ---
        function showToast(message, type = 'info') {
            const container = document.getElementById('global-toast-container');
            if (!container) return;
            
            const toast = document.createElement('div');
            
            let bgClass = 'bg-slate-800 text-white';
            let icon = `<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
            
            if (type === 'success') {
                bgClass = 'bg-slate-800 text-white border-l-4 border-green-500';
                icon = `<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            } else if (type === 'error') {
                bgClass = 'bg-slate-800 text-white border-l-4 border-red-500';
                icon = `<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
            }
            
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg pointer-events-auto transition-all duration-300 transform translate-y-full opacity-0 ${bgClass}`;
            toast.innerHTML = `
                ${icon}
                <span class="text-sm font-medium">${message}</span>
            `;
            
            container.appendChild(toast);
            
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-full', 'opacity-0');
            });
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Override native window.alert globally
        window.alert = function(message) {
            showToast(message, 'info');
        };

        // --- Custom Confirm Modal ---
        function showConfirm(message, title = 'Confirm Action', onConfirm) {
            const modal = document.getElementById('global-confirm-modal');
            const backdrop = document.getElementById('global-confirm-backdrop');
            const panel = document.getElementById('global-confirm-panel');
            const titleEl = document.getElementById('global-confirm-title');
            const messageEl = document.getElementById('global-confirm-message');
            const cancelBtn = document.getElementById('global-confirm-cancel');
            const okBtn = document.getElementById('global-confirm-ok');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            modal.classList.remove('hidden');
            void modal.offsetWidth;
            
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('scale-95', 'opacity-0');
            
            const cleanup = () => {
                backdrop.classList.add('opacity-0');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => modal.classList.add('hidden'), 300);
                
                cancelBtn.removeEventListener('click', onCancel);
                okBtn.removeEventListener('click', onOk);
            };
            
            const onCancel = () => cleanup();
            const onOk = () => {
                cleanup();
                if (onConfirm) onConfirm();
            };
            
            cancelBtn.addEventListener('click', onCancel);
            okBtn.addEventListener('click', onOk);
        }

        // Helper for forms
        function confirmFormSubmit(button, message, title = 'Confirm Action') {
            showConfirm(message, title, () => {
                // If it's a CakePHP form postLink, it might be a hidden form
                if (button.form) {
                    button.form.submit();
                } else if (button.closest('form')) {
                    button.closest('form').submit();
                }
            });
        }
        
        // Setup existing logic
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('theme-prompt-toast');
            const quickToggle = document.getElementById('quickDarkModeToggle');
            const isDark = document.documentElement.classList.contains('dark');
            const dismissed = localStorage.getItem('theme_prompt_dismissed');
            const hasExplicitTheme = localStorage.getItem('theme') !== null;

            if (isDark && !dismissed && !hasExplicitTheme) {
                toast.classList.remove('hidden');
            }

            if (quickToggle) {
                quickToggle.addEventListener('change', function() {
                    if (!this.checked) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                        dismissThemePrompt();
                        
                        const profileToggle = document.getElementById('darkModeToggle');
                        if (profileToggle) profileToggle.checked = false;
                    }
                });
            }
        });

        function dismissThemePrompt() {
            localStorage.setItem('theme_prompt_dismissed', 'true');
            document.getElementById('theme-prompt-toast').classList.add('hidden');
        }
    </script>
</body>
</html>
