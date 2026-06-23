<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array $hostedSessions
 * @var array $joinedSessions
 * @var array $myDebts
 * @var array $owedToMe
 * @var array $myStats
 */
$this->assign('title', 'Dashboard - Bahagije');

$statusColors = [
    'open'   => 'bg-green-100 text-green-700',
    'locked' => 'bg-yellow-100 text-yellow-700',
    'closed' => 'bg-red-100 text-red-700',
];
$presetIcons = [
    'dining'    => '🍽️',
    'road_trip' => '🚗',
    'long_trip' => '✈️',
    'grocery'   => '🛒',
    'custom'    => '⚙️',
];
$presetMeta = [
    'dining'    => [
        'icon'  => '🍽️',
        'label' => 'Food & Dining',
        'short' => 'For restaurants & cafes',
        'desc'  => 'Automatically applies SST (6%) and Service Charge (10%) with a proportional split. Perfect for group dinners.',
        'color' => 'border-orange-200 hover:border-orange-400 bg-orange-50',
        'badge' => 'bg-orange-100 text-orange-700',
    ],
    'grocery'   => [
        'icon'  => '🛒',
        'label' => 'Grocery Run',
        'short' => 'For supermarket shops',
        'desc'  => 'Itemized receipt entry with no service charge. Great for household shopping where each item is split differently.',
        'color' => 'border-green-200 hover:border-green-400 bg-green-50',
        'badge' => 'bg-green-100 text-green-700',
    ],
    'road_trip' => [
        'icon'  => '🚗',
        'label' => 'Road Trip',
        'short' => 'For fuel, tolls & stays',
        'desc'  => 'Flat amounts split equally across all participants. Best for fuel, toll charges, and shared accommodation.',
        'color' => 'border-blue-200 hover:border-blue-400 bg-blue-50',
        'badge' => 'bg-blue-100 text-blue-700',
    ],
    'long_trip' => [
        'icon'  => '✈️',
        'label' => 'Long Trip',
        'short' => 'For multi-day journeys',
        'desc'  => 'A collaborative ledger that records all expenses across multiple days and minimizes the total number of repayments using net-debt settlement.',
        'color' => 'border-purple-200 hover:border-purple-400 bg-purple-50',
        'badge' => 'bg-purple-100 text-purple-700',
    ],
    'custom'    => [
        'icon'  => '⚙️',
        'label' => 'Custom',
        'short' => 'Full control',
        'desc'  => 'Manually configure every rule — charges, split method, and more. For power users who know exactly what they need.',
        'color' => 'border-slate-200 hover:border-slate-400 bg-slate-50',
        'badge' => 'bg-slate-100 text-slate-700',
    ],
];

$openHosted = [];
$closedHosted = [];
foreach ($hostedSessions as $s) {
    if ($s->status === 'closed') {
        $closedHosted[] = $s;
    } else {
        $openHosted[] = $s;
    }
}

$openJoined = [];
$closedJoined = [];
foreach ($joinedSessions as $s) {
    if ($s->status === 'closed') {
        $closedJoined[] = $s;
    } else {
        $openJoined[] = $s;
    }
}

$totalOwed = array_sum(array_map(fn($d) => $d->amount, $myDebts));
$totalOwedToMe = array_sum(array_map(fn($d) => $d->amount, $owedToMe));
?>

<div class="max-w-7xl mx-auto mt-8 px-4 space-y-6">

    <!-- Welcome Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4 min-w-0">
            <?php if ($user->avatar_url): ?>
                <img src="<?= h($user->avatar_url) ?>" class="h-14 w-14 rounded-full border-2 border-blue-400 shadow-sm shrink-0">
            <?php else: ?>
                <div class="h-14 w-14 rounded-full bg-blue-100 text-blue-600 font-bold text-xl flex items-center justify-center border-2 border-blue-400 shrink-0">
                    <?= strtoupper(substr(h($user->name), 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 truncate" title="Welcome, <?= h($user->name) ?>">Welcome, <?= h($user->name) ?> 👋</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm truncate"><?= h($user->email) ?></p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto shrink-0">
            <button onclick="openJoinModal()" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-4 focus:ring-slate-100 transition shadow-sm">
                🔗 Join Session
            </button>
            <a href="<?= $this->Url->build(['controller' => 'GroupSessions', 'action' => 'create']) ?>"
               class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition shadow-sm">
                ＋ New Session
            </a>
        </div>
    </div>

    <!-- Onboarding / Quick Start Banner for new users -->
    <?php
    $totalSessionsCount = count($hostedSessions) + count($joinedSessions);
    if ($totalSessionsCount === 0): 
    ?>
    <div id="onboarding-banner" class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row items-center gap-6 justify-between transition-all">
        <!-- Decor -->
        <div class="absolute top-[-50%] right-[-10%] w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-[-50%] left-[-10%] w-64 h-64 bg-black/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex-1 text-center md:text-left">
            <h2 class="text-xl sm:text-2xl font-extrabold mb-2">Welcome to BahagiJe! 🎉</h2>
            <p class="text-blue-100 text-sm sm:text-base leading-relaxed max-w-2xl">
                You don't have any active sessions yet. The easiest way to get started is to scroll down and pick a <strong class="text-white">Preset</strong> (like Food & Dining or Groceries). We'll instantly generate a session and provide you with a code to share with your friends!
            </p>
        </div>
        <div class="relative z-10 shrink-0">
            <button onclick="document.getElementById('onboarding-banner').style.display='none'" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white text-sm font-bold rounded-lg backdrop-blur-sm transition focus:outline-none border border-white/30">
                Got it, thanks!
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Two-Column Layout: Main + Sidebar -->
    <div class="flex flex-col-reverse lg:flex-row gap-6 lg:items-start">

        <!-- ═══════════════════════════════════════════════════ -->
        <!-- MAIN COLUMN                                         -->
        <!-- ═══════════════════════════════════════════════════ -->
        <div class="flex-1 min-w-0 space-y-6">

            <!-- Tabs Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700 overflow-x-auto hide-scrollbar">
                <nav class="-mb-px flex gap-4 sm:gap-8 min-w-max pb-1" aria-label="Tabs">
                    <button onclick="switchTab('open')" id="tab-open" class="tab-btn active-tab border-blue-500 text-blue-600 dark:text-blue-400 whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm flex items-center gap-2 transition">
                        🟢 Open Sessions
                        <span class="bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 py-0.5 px-2.5 rounded-full text-xs font-bold"><?= count($openHosted) + count($openJoined) ?></span>
                    </button>
                    <button onclick="switchTab('closed')" id="tab-closed" class="tab-btn inactive-tab border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition">
                        🔒 Closed Sessions
                        <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 py-0.5 px-2.5 rounded-full text-xs font-bold"><?= count($closedHosted) + count($closedJoined) ?></span>
                    </button>
                </nav>
            </div>

            <!-- TAB CONTENT: OPEN SESSIONS -->
            <div id="content-open" class="tab-content space-y-10 block">
                <!-- Open Hosted -->
                <div>
                    <?= $this->Form->create(null, ['url' => ['controller' => 'GroupSessions', 'action' => 'bulkDelete'], 'id' => 'bulkDeleteFormOpen']) ?>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200">🎯 Sessions I'm Hosting</h2>
                        <div class="flex items-center gap-3">
                            <button type="button" id="deleteSelectedBtnOpen" class="hidden px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-200 hover:bg-red-100 transition shadow-sm" onclick="confirmFormSubmit(this, 'Are you sure you want to completely delete the selected sessions? This cannot be undone.', 'Delete Sessions')">
                                🗑️ Delete Selected (<span id="deleteCountOpen">0</span>)
                            </button>
                            <?php if (!empty($openHosted)): ?>
                                <button type="button" class="toggleDeleteModeBtn text-slate-400 hover:text-red-500 transition focus:outline-none" title="Select Sessions to Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (empty($openHosted)): ?>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center">
                            <div class="text-3xl mb-2">📋</div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mb-5">You haven't hosted any open sessions yet.</p>
                            <!-- Preset Cards -->
                            <?= $this->element('preset_picker') ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            <?php foreach ($openHosted as $s): ?>
                                <?= $this->element('session_card', ['s' => $s, 'presetIcons' => $presetIcons, 'statusColors' => $statusColors, 'isHost' => true]) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?= $this->Form->end() ?>
                </div>

                <!-- Open Joined -->
                <div>
                    <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-4">🤝 Sessions I've Joined</h2>
                    <?php if (empty($openJoined)): ?>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center">
                            <div class="text-3xl mb-2">🔍</div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mb-5">You haven't joined any open sessions yet.</p>
                            <button onclick="openJoinModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm">
                                🔗 Join a Session
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            <?php foreach ($openJoined as $s): ?>
                                <?= $this->element('session_card', ['s' => $s, 'presetIcons' => $presetIcons, 'statusColors' => $statusColors, 'isHost' => false]) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB CONTENT: CLOSED SESSIONS -->
            <div id="content-closed" class="tab-content space-y-10 hidden">
                <!-- Closed Hosted -->
                <div>
                    <?= $this->Form->create(null, ['url' => ['controller' => 'GroupSessions', 'action' => 'bulkDelete'], 'id' => 'bulkDeleteFormClosed']) ?>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200">🎯 Hosted (Closed)</h2>
                        <div class="flex items-center gap-3">
                            <button type="button" id="deleteSelectedBtnClosed" class="hidden px-4 py-2 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-200 hover:bg-red-100 transition shadow-sm" onclick="confirmFormSubmit(this, 'Are you sure you want to completely delete the selected sessions? This cannot be undone.', 'Delete Sessions')">
                                🗑️ Delete Selected (<span id="deleteCountClosed">0</span>)
                            </button>
                            <?php if (!empty($closedHosted)): ?>
                                <button type="button" class="toggleDeleteModeBtn text-slate-400 hover:text-red-500 transition focus:outline-none" title="Select Sessions to Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (empty($closedHosted)): ?>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center">
                            <div class="text-3xl mb-2">📋</div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No closed sessions hosted yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            <?php foreach ($closedHosted as $s): ?>
                                <?= $this->element('session_card', ['s' => $s, 'presetIcons' => $presetIcons, 'statusColors' => $statusColors, 'isHost' => true]) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?= $this->Form->end() ?>
                </div>

                <!-- Closed Joined -->
                <div>
                    <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-4">🤝 Joined (Closed)</h2>
                    <?php if (empty($closedJoined)): ?>
                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center">
                            <div class="text-3xl mb-2">🔍</div>
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No closed sessions joined yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                            <?php foreach ($closedJoined as $s): ?>
                                <?= $this->element('session_card', ['s' => $s, 'presetIcons' => $presetIcons, 'statusColors' => $statusColors, 'isHost' => false]) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /main column -->

        <!-- ═══════════════════════════════════════════════════ -->
        <!-- SIDEBAR                                             -->
        <!-- ═══════════════════════════════════════════════════ -->
        <div class="w-full lg:w-80 shrink-0 space-y-4 lg:sticky lg:top-6">

            <!-- Money Summary Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">💰 Money Summary</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Across all sessions</p>
                </div>

                <?php if (empty($myDebts) && empty($owedToMe)): ?>
                    <!-- All settled state -->
                    <div class="p-5 text-center">
                        <div class="text-4xl mb-2">✅</div>
                        <p class="text-sm font-semibold text-green-700">You're all settled up!</p>
                        <p class="text-xs text-slate-400 mt-1">No outstanding payments.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">

                        <?php if (!empty($myDebts)): ?>
                        <!-- You Owe -->
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">💸 You Owe</span>
                                <span class="text-sm font-black text-red-700 dark:text-red-500">RM <?= number_format($totalOwed, 2) ?></span>
                            </div>
                            <div class="space-y-2">
                                <?php foreach ($myDebts as $debt): ?>
                                    <?php
                                        $creditorName = $debt->creditor->user->name ?? $debt->creditor->guest_name ?? 'Unknown';
                                        $initial      = strtoupper(substr($creditorName, 0, 1));
                                        $hasProof     = !empty($debt->payment_proofs);
                                    ?>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800 flex items-center justify-center text-xs font-bold shrink-0">
                                            <?= h($initial) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate"><?= h($creditorName) ?></p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate"><?= h($debt->group_session->name ?? '') ?></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs font-bold text-red-600 dark:text-red-400">RM <?= number_format($debt->amount, 2) ?></p>
                                            <?php if ($hasProof): ?>
                                                <p class="text-[10px] text-amber-600 font-medium">Claimed</p>
                                            <?php else: ?>
                                                <p class="text-[10px] text-red-400">Pending</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($owedToMe)): ?>
                        <!-- Owed to You -->
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wider">🤝 Owed to You</span>
                                <span class="text-sm font-black text-green-700 dark:text-green-500">RM <?= number_format($totalOwedToMe, 2) ?></span>
                            </div>
                            <div class="space-y-2">
                                <?php foreach ($owedToMe as $owed): ?>
                                    <?php
                                        $debtorName = $owed->debtor->user->name ?? $owed->debtor->guest_name ?? 'Unknown';
                                        $initial    = strtoupper(substr($debtorName, 0, 1));
                                        $hasProof   = !empty($owed->payment_proofs);
                                    ?>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800 flex items-center justify-center text-xs font-bold shrink-0">
                                            <?= h($initial) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate"><?= h($debtorName) ?></p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate"><?= h($owed->group_session->name ?? '') ?></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs font-bold text-green-600 dark:text-green-400">RM <?= number_format($owed->amount, 2) ?></p>
                                            <?php if ($hasProof): ?>
                                                <p class="text-[10px] text-amber-600 font-medium">Proof sent</p>
                                            <?php else: ?>
                                                <p class="text-[10px] text-slate-400">Waiting</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-sm font-bold text-slate-700 dark:text-slate-200">📊 Your Stats</h2>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3 text-center transition-colors">
                        <div class="text-xl font-black text-slate-800 dark:text-slate-200"><?= $myStats['total_sessions'] ?></div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wide">Sessions</div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3 text-center transition-colors">
                        <div class="text-xl font-black text-blue-700 dark:text-blue-400"><?= $myStats['hosted'] ?></div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wide">Hosted</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center col-span-2 transition-colors">
                        <div class="text-lg font-black text-blue-800 dark:text-blue-400">RM <?= number_format($myStats['total_split'], 2) ?></div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wide">Total Split</div>
                    </div>
                    <?php if ($myStats['biggest_expense'] > 0): ?>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3 text-center col-span-2 transition-colors">
                        <div class="text-base font-black text-slate-700 dark:text-slate-200">RM <?= number_format($myStats['biggest_expense'], 2) ?></div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wide">Biggest Single Expense</div>
                    </div>
                    <?php endif; ?>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3 text-center col-span-2 transition-colors">
                        <div class="text-base font-black text-slate-700 dark:text-slate-200"><?= $myStats['total_expenses'] ?></div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium uppercase tracking-wide">Total Expenses Logged</div>
                    </div>
                </div>
            </div>

        </div><!-- /sidebar -->

    </div><!-- /two-column -->

</div>

<!-- Join Session Modal -->
<div id="joinSessionModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Join a Session</h3>
            <button type="button" onclick="closeJoinModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Invite Link or Session Code</label>
                <input type="text" id="joinCodeInput" placeholder="e.g. 5f4dcc3b5aa765d6... or full invite link" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 outline-none text-sm transition">
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">Ask the host for the full Invite Link or the short Session Code to join.</p>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="closeJoinModal()" class="px-5 py-3 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition text-sm">Cancel</button>
                <button type="button" onclick="processJoinCode()" class="px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">Join</button>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active-tab', 'border-blue-500', 'text-blue-600');
        btn.classList.add('inactive-tab', 'border-transparent', 'text-slate-500');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('block');
        content.classList.add('hidden');
    });
    const activeBtn = document.getElementById('tab-' + tabId);
    activeBtn.classList.remove('inactive-tab', 'border-transparent', 'text-slate-500');
    activeBtn.classList.add('active-tab', 'border-blue-500', 'text-blue-600');
    document.getElementById('content-' + tabId).classList.remove('hidden');
    document.getElementById('content-' + tabId).classList.add('block');
}

function openJoinModal() {
    document.getElementById('joinSessionModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('joinCodeInput').focus(), 100);
}
function closeJoinModal() {
    document.getElementById('joinSessionModal').classList.add('hidden');
    document.getElementById('joinCodeInput').value = '';
}
function processJoinCode() {
    const input = document.getElementById('joinCodeInput').value.trim();
    if (!input) return;
    let code = input;
    try {
        const url = new URL(input);
        const parts = url.pathname.split('/').filter(Boolean);
        code = parts[parts.length - 1];
    } catch (e) {}
    window.location.href = "<?= $this->Url->build('/sessions/join/') ?>" + encodeURIComponent(code);
}

// Bulk Delete Logic
document.addEventListener('DOMContentLoaded', function() {
    const setupBulkDelete = (containerId, btnId, countId) => {
        const container = document.getElementById(containerId);
        if (!container) return;
        const deleteBtn = document.getElementById(btnId);
        const countSpan = document.getElementById(countId);
        const toggleBtn = container.querySelector('.toggleDeleteModeBtn');
        const checkboxes = container.querySelectorAll('.session-checkbox');
        const checkboxWrappers = container.querySelectorAll('.session-checkbox-wrapper');
        if (toggleBtn && checkboxWrappers.length > 0) {
            toggleBtn.addEventListener('click', () => {
                const isHidden = checkboxWrappers[0].classList.contains('hidden');
                if (isHidden) {
                    checkboxWrappers.forEach(w => w.classList.remove('hidden'));
                    toggleBtn.classList.add('text-red-500');
                    toggleBtn.classList.remove('text-slate-400');
                } else {
                    checkboxWrappers.forEach(w => w.classList.add('hidden'));
                    toggleBtn.classList.remove('text-red-500');
                    toggleBtn.classList.add('text-slate-400');
                    checkboxes.forEach(cb => cb.checked = false);
                    deleteBtn.classList.add('hidden');
                    countSpan.textContent = '0';
                }
            });
        }
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const checkedCount = container.querySelectorAll('.session-checkbox:checked').length;
                if (checkedCount > 0) {
                    deleteBtn.classList.remove('hidden');
                    countSpan.textContent = checkedCount;
                } else {
                    deleteBtn.classList.add('hidden');
                }
            });
        });
    };
    setupBulkDelete('content-open', 'deleteSelectedBtnOpen', 'deleteCountOpen');
    setupBulkDelete('content-closed', 'deleteSelectedBtnClosed', 'deleteCountClosed');
});
</script>
