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
<div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 p-4 rounded-xl shadow-sm flex items-start justify-between transition-colors">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-green-800 dark:text-green-200"><?= $message ?></p>
        </div>
    </div>
    <button onclick="this.closest('div').style.display='none'" class="ml-4 flex-shrink-0 text-green-500 hover:text-green-700 dark:hover:text-green-300 focus:outline-none p-1 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>
