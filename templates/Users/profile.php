<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Profile & Settings - BahagiJe');
?>

<div class="max-w-3xl mx-auto mt-8 px-4 space-y-6">
    <div class="flex items-center gap-4 mb-8">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Profile & Settings</h1>
    </div>

    <?= $this->Flash->render() ?>

    <div class="space-y-6">
        <!-- Section 1: Profile Details -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Profile Details</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Update your display name.</p>
            </div>
            <div class="p-6">
                <?= $this->Form->create($user, ['class' => 'space-y-4']) ?>
                
                <!-- Email (Read Only) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                        </div>
                        <input type="text" value="<?= h($user->email) ?>" disabled class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 text-sm rounded-lg block w-full pl-10 p-2.5 cursor-not-allowed">
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Linked to your Google account.</p>
                </div>

                <!-- Display Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Display Name</label>
                    <?= $this->Form->control('name', [
                        'label' => false,
                        'class' => 'bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5',
                        'placeholder' => 'Enter your display name',
                        'required' => true
                    ]) ?>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto text-center px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition shadow-sm text-sm">
                        Save Changes
                    </button>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Section 2: Payment Methods -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Payment Methods</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Manage your bank details and DuitNow QR codes.</p>
            </div>
            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-sm text-slate-600 dark:text-slate-300">
                    <p>Make it easy for others to pay you back by securely saving your preferred payment details. These are shared automatically when you claim a debt.</p>
                </div>
                <a href="<?= $this->Url->build(['controller' => 'UserPaymentMethods', 'action' => 'index']) ?>" class="w-full sm:w-auto text-center shrink-0 px-5 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-sm text-sm whitespace-nowrap">
                    Manage Payment Details →
                </a>
            </div>
        </div>

        <!-- Section 3: Appearance -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Appearance</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Customize how Bahagije looks on this device.</p>
            </div>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Dark Mode</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Switch to a darker theme for night time viewing.</p>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="darkModeToggle" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <!-- Section 4: Danger Zone -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-red-200 dark:border-red-900/50 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-red-100 dark:border-red-900/30 bg-red-50 dark:bg-red-900/20">
                <h2 class="text-lg font-semibold text-red-700 dark:text-red-400">Danger Zone</h2>
                <p class="text-sm text-red-500 dark:text-red-300/80">Permanent destructive actions.</p>
            </div>
            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-sm text-slate-600 dark:text-slate-300">
                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-1">Delete Account</p>
                    <p>Permanently remove your account and disconnect your Google login. You will be removed from all sessions.</p>
                </div>
                <button type="button" onclick="alert('Account deletion is not yet fully implemented in this demo.')" class="w-full sm:w-auto text-center shrink-0 px-5 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-sm text-sm whitespace-nowrap">
                    Delete Account
                </button>
            </div>
        </div>

        <!-- Section 5: Legal -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Legal</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Terms of Service and Privacy Policy.</p>
            </div>
            <div class="p-6 flex flex-col gap-3">
                <a href="<?= $this->Url->build('/terms') ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Read Terms of Service</a>
                <a href="<?= $this->Url->build('/privacy') ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Read Privacy Policy</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('darkModeToggle');
    
    // Set initial state
    if (document.documentElement.classList.contains('dark')) {
        toggle.checked = true;
    }

    // Listen for toggle
    toggle.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    });
});
</script>
