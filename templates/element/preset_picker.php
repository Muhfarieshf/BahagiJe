<?php
/**
 * Preset Picker — shown inside empty state blocks on the dashboard.
 * Clicking a card navigates to /group-sessions/create?preset=<key>
 */
$presetMeta = [
    'dining'    => [
        'icon'  => '🍽️',
        'label' => 'Food & Dining',
        'short' => 'Restaurants & cafes',
        'desc'  => 'Automatically applies SST (6%) and Service Charge (10%) with a proportional split. Perfect for group dinners.',
        'color' => 'border-orange-200 dark:border-orange-900/50 hover:border-orange-400 dark:hover:border-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20',
        'text'  => 'text-orange-700 dark:text-orange-400',
    ],
    'grocery'   => [
        'icon'  => '🛒',
        'label' => 'Grocery Run',
        'short' => 'Supermarket shops',
        'desc'  => 'Itemized receipt entry with no service charge. Great for household shopping where each item is split differently.',
        'color' => 'border-green-200 dark:border-green-900/50 hover:border-green-400 dark:hover:border-green-700 hover:bg-green-50 dark:hover:bg-green-900/20',
        'text'  => 'text-green-700 dark:text-green-400',
    ],
    'road_trip' => [
        'icon'  => '🚗',
        'label' => 'Road Trip',
        'short' => 'Fuel, tolls & stays',
        'desc'  => 'Flat amounts split equally across all participants. Best for fuel, toll charges, and shared accommodation.',
        'color' => 'border-blue-200 dark:border-blue-900/50 hover:border-blue-400 dark:hover:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20',
        'text'  => 'text-blue-700 dark:text-blue-400',
    ],
    'long_trip' => [
        'icon'  => '✈️',
        'label' => 'Long Trip',
        'short' => 'Multi-day journeys',
        'desc'  => 'A collaborative ledger that records all expenses over multiple days and minimizes repayments using net-debt settlement.',
        'color' => 'border-purple-200 dark:border-purple-900/50 hover:border-purple-400 dark:hover:border-purple-700 hover:bg-purple-50 dark:hover:bg-purple-900/20',
        'text'  => 'text-purple-700 dark:text-purple-400',
    ],
    'custom'    => [
        'icon'  => '⚙️',
        'label' => 'Custom',
        'short' => 'Full control',
        'desc'  => 'Manually configure every rule — charges, split method, and more. For power users who know exactly what they need.',
        'color' => 'border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50',
        'text'  => 'text-slate-700 dark:text-slate-300',
    ],
];
$createBase = $this->Url->build(['controller' => 'GroupSessions', 'action' => 'create']);
?>

<p class="text-slate-400 dark:text-slate-500 text-xs mb-4">Start a new session with a preset:</p>
<div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-5 gap-3 text-left">
    <?php foreach ($presetMeta as $key => $meta): ?>
    <a href="<?= $createBase ?>?preset=<?= $key ?>"
       class="group/preset relative flex flex-row sm:flex-col items-start sm:items-center gap-4 sm:gap-2 p-4 sm:p-3 rounded-xl border-2 bg-white dark:bg-slate-800 transition-all cursor-pointer <?= $meta['color'] ?>"
       title="<?= h($meta['desc']) ?>">
        <div class="text-3xl sm:text-2xl shrink-0"><?= $meta['icon'] ?></div>
        <div class="text-left sm:text-center flex-1">
            <p class="text-sm sm:text-xs font-bold <?= $meta['text'] ?>"><?= $meta['label'] ?></p>
            <p class="text-xs sm:text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= $meta['short'] ?></p>
            <!-- Mobile inline description -->
            <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400 mt-2 block sm:hidden"><?= h($meta['desc']) ?></p>
        </div>

        <!-- Desktop Hover Tooltip -->
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-52 hidden sm:group-hover/preset:block z-30 pointer-events-none">
            <div class="bg-slate-800 text-white text-xs rounded-lg px-3 py-2.5 shadow-xl text-left leading-relaxed">
                <p class="font-bold mb-1"><?= $meta['icon'] ?> <?= $meta['label'] ?></p>
                <p class="text-slate-300"><?= h($meta['desc']) ?></p>
                <p class="text-blue-300 mt-1.5 font-semibold">Click to create →</p>
            </div>
            <!-- Arrow -->
            <div class="w-3 h-3 bg-slate-800 rotate-45 mx-auto -mt-1.5"></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
