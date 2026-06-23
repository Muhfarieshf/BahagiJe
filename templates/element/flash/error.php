<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 p-4 rounded-xl shadow-sm flex items-start justify-between transition-colors">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-red-800 dark:text-red-200"><?= $message ?></p>
        </div>
    </div>
    <button onclick="this.closest('div').style.display='none'" class="ml-4 flex-shrink-0 text-red-500 hover:text-red-700 dark:hover:text-red-300 focus:outline-none p-1 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>
