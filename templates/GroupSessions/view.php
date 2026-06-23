<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var bool $isHost
 * @var \App\Model\Entity\Participant $currentParticipant
 */
$identity = $this->request->getAttribute('identity');
$this->assign('title', h($session->name) . ' - BahagiJe');

$statusColors = [
    'open'   => 'bg-green-100 text-green-700',
    'locked' => 'bg-yellow-100 text-yellow-700',
    'closed' => 'bg-red-100 text-red-700',
];
$statusColor = $statusColors[$session->status] ?? 'bg-slate-100 text-slate-600';

$presetLabels = [
    'dining'    => '🍽️ Food & Dining',
    'road_trip' => '🚗 Road Trip',
    'long_trip' => '✈️ Long Trip',
    'custom'    => '⚙️ Custom',
];
?>

<div class="max-w-4xl mx-auto mt-8 px-4 space-y-6">

    <?php if ($identity): ?>
    <div>
        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>
    <?php endif; ?>
    <?php
$unpaid = false;
if ($session->status === 'closed' && !empty($settlements)) {
    foreach ($settlements as $txn) {
        if ($txn->status !== 'settled') {
            $unpaid = true;
            break;
        }
    }
}
?>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100"><?= h($session->name) ?></h1>
            <div class="flex items-center gap-3 mt-2 flex-wrap">
                <span class="<?= $statusColor ?> text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">
                    <?= ucfirst($session->status) ?>
                </span>
                <span class="text-slate-500 dark:text-slate-400 text-sm"><?= $presetLabels[$session->preset_type] ?? $session->preset_type ?></span>
                <span class="text-slate-400 dark:text-slate-500 text-xs">Created <?= $session->created_at->format('d M Y, H:i') ?></span>
            </div>
        </div>
        
        <?php if ($isHost): ?>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <?php if (!$unpaid): ?>
            <form method="post" action="<?= $this->Url->build(['action' => 'delete', $session->uuid]) ?>" style="display:inline;">
                <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
                <button type="button" onclick="confirmFormSubmit(this, 'Are you sure you want to completely delete this session? This action cannot be undone and will delete all expenses and records.', 'Delete Session')" class="inline-flex justify-center items-center gap-2 w-full sm:w-auto px-4 py-3 bg-white dark:bg-slate-800 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-semibold rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 focus:ring-4 focus:ring-red-50 transition shadow-sm">
                    🗑️ Delete Session
                </button>
            </form>
            <?php else: ?>
            <button type="button" disabled title="Cannot delete unpaid session" class="inline-flex justify-center items-center gap-2 w-full sm:w-auto px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 text-sm font-semibold rounded-lg cursor-not-allowed opacity-60">
                🗑️ Delete Session
            </button>
            <?php endif; ?>

            <?php if ($session->status === 'open'): ?>
            <form method="post" action="<?= $this->Url->build(['action' => 'lockSession', $session->uuid]) ?>" style="display:inline;">
                <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
                <button type="button" onclick="confirmFormSubmit(this, 'This will temporarily lock the session for all participants while you review the calculations. Continue?', 'Lock Session')" class="inline-flex justify-center items-center gap-2 w-full sm:w-auto px-5 py-3 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-100 transition shadow-sm">
                    🔒 Lock & Calculate
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($session->status === 'locked'): ?>
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6 flex items-start gap-3 transition-colors">
        <div class="text-amber-500 text-xl mt-0.5">⚠️</div>
        <div>
            <h3 class="text-sm font-bold text-amber-800 dark:text-amber-200">Session Locked</h3>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">This session is currently locked by the Host to finalize calculations. No new expenses can be added.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: QR & Payment Info -->
        <div class="flex flex-col space-y-6">

            <!-- QR Code Panel -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 flex flex-col items-center text-center space-y-4 transition-colors">
            <h2 class="text-base font-semibold text-slate-700 dark:text-slate-200 self-start">📷 Join QR Code</h2>

            <?php if ($session->status === 'open'): ?>
                <!-- Adding bg-white padding so QR stays scanable in dark mode -->
                <div class="bg-white p-2 rounded-lg">
                    <?= $this->QrCode->generate($session->uuid, 220) ?>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Scan to join this session</p>

                <!-- Copyable Link and Code -->
                <div class="w-full bg-slate-50 dark:bg-slate-800/50 rounded-lg px-3 py-2 flex flex-col gap-2 border border-slate-200 dark:border-slate-700 text-left">
                    
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Invite Link</span>
                            <button onclick="copyJoinLink()" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium transition">Copy</button>
                        </div>
                        <span id="join-link" class="text-xs text-slate-600 dark:text-slate-300 truncate">
                            <?= h(\Cake\Routing\Router::url('/sessions/join/' . $session->uuid, true)) ?>
                        </span>
                    </div>

                    <div class="h-px w-full bg-slate-200 dark:bg-slate-700"></div>

                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Session Code</span>
                            <button onclick="copyJoinCode()" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium transition">Copy</button>
                        </div>
                        <span id="join-code" class="text-xs font-mono text-slate-700 dark:text-slate-200 bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded inline-block truncate max-w-full">
                            <?= h($session->uuid) ?>
                        </span>
                    </div>
                </div>
            <?php else: ?>
                <div class="py-10 text-slate-400 text-sm">QR code not available — session is <?= h($session->status) ?>.</div>
            <?php endif; ?>
        </div>

            <!-- Session Guide Steps -->
                <?php if ($isHost): ?>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-800/50 p-5 transition-colors">
                    <h3 class="text-sm font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        How to use this session
                    </h3>
                    <ol class="list-decimal list-inside space-y-2 text-[13px] text-slate-700 dark:text-slate-300 leading-relaxed">
                        <li><strong>Set up payment details</strong> so guests know how to pay you back. <a href="<?= $this->Url->build(['controller' => 'UserPaymentMethods', 'action' => 'index']) ?>" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Manage</a></li>
                        <li><strong>Upload the master receipt</strong> using the 📷 icon for everyone to see.</li>
                        <li><strong>Lock & Calculate</strong> once everyone has added their items.</li>
                    </ol>
                </div>
                <?php elseif ($identity): ?>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-800/50 p-5 transition-colors">
                    <h3 class="text-sm font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        How to use this session
                    </h3>
                    <ol class="list-decimal list-inside space-y-2 text-[13px] text-slate-700 dark:text-slate-300 leading-relaxed">
                        <li>Look at the master receipt and <strong>add your items</strong>.</li>
                        <li><strong>Wait for the host</strong> to lock the session and calculate taxes.</li>
                        <li>Once calculated, pay your host using their listed payment methods.</li>
                    </ol>
                </div>
                <?php endif; ?>
        </div>

        <!-- Main Content Container -->
        <div class="lg:col-span-2 space-y-5">
            <?php if ($session->status === 'closed'): ?>
                <?= $this->element('closed_session_dashboard') ?>
            <?php endif; ?>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 text-center transition-colors">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= count($session->participants) ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Participants</div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 text-center transition-colors">
                    <div class="text-2xl font-bold text-slate-700 dark:text-slate-200"><?= $session->max_participants ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Max Allowed</div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 text-center transition-colors">
                    <div class="text-2xl font-bold text-slate-700 dark:text-slate-200"><?= count($session->session_charges ?? []) ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Charges</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-900/50 shadow-sm p-4 text-center transition-colors">
                    <div class="text-xl font-bold text-blue-700 dark:text-blue-400">RM <?= number_format($sessionGrandTotal, 2) ?></div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">Group Total</div>
                </div>
            </div>

            <!-- Session Charges -->
            <?php if (!empty($session->session_charges)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 transition-colors">
                <h2 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-3">💰 Session Charges</h2>
                <div class="space-y-2">
                    <?php foreach ($session->session_charges as $charge): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300"><?= h($charge->charge_name) ?></span>
                        <span class="font-medium text-slate-800 dark:text-slate-100">
                            <?= $charge->charge_type === 'percentage'
                                ? h($charge->charge_value) . '%'
                                : 'RM ' . number_format($charge->charge_value, 2) ?>
                            <span class="text-xs text-slate-400 dark:text-slate-500 ml-1">(<?= $charge->applies_to ?>)</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Participants List -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 transition-colors">
                <h2 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-4">👥 Participants</h2>
                <?php if (empty($session->participants)): ?>
                    <p class="text-sm text-slate-400 dark:text-slate-500">No participants yet.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($session->participants as $participant): ?>
                    <div class="flex items-center gap-3">
                        <?php if ($participant->user && $participant->user->avatar_url): ?>
                            <img src="<?= h($participant->user->avatar_url) ?>"
                                 class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700" alt="">
                        <?php else: ?>
                            <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-xs flex items-center justify-center border border-blue-200 dark:border-blue-800">
                                <?= strtoupper(substr($participant->user->name ?? $participant->guest_name ?? '?', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate">
                                <?= h($participant->user->name ?? $participant->guest_name ?? 'Unknown') ?>
                            </div>
                            <div class="text-xs text-slate-400 dark:text-slate-500">Joined <?= $participant->joined_at->format('d M, H:i') ?></div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                RM <?= isset($participantTotals[$participant->id]) ? number_format($participantTotals[$participant->id]['grand_total'], 2) : '0.00' ?>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium inline-block mt-1
                                <?= $participant->role === 'host' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($participant->role === 'guest' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300') ?>">
                                <?= ucfirst($participant->role) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Preset-Specific UI Branching -->
            <?php if ($session->preset_type === 'road_trip'): ?>
                <?= $this->element('roadtrip_ui') ?>
            <?php else: ?>

            <!-- Expenses List -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                        <h2 class="text-base font-semibold text-slate-700 dark:text-slate-200 shrink-0">🧾 Expenses</h2>
                        <span class="text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-1 rounded-md shrink-0">Total: RM <?= number_format($sessionGrandTotal, 2) ?></span>
                    </div>
                    <?php if ($session->status === 'open'): ?>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" onclick="document.getElementById('addExpenseModal').classList.remove('hidden')"
                                class="flex-1 sm:flex-none text-center justify-center whitespace-nowrap text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-100 transition px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg">
                            + Single Expense
                        </button>
                        <button type="button" onclick="document.getElementById('addReceiptModal').classList.remove('hidden')"
                                class="flex-1 sm:flex-none text-center justify-center whitespace-nowrap text-sm font-medium text-white hover:text-white transition px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                            + Itemized Receipt
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <?php 
                    $standaloneExpenses = [];
                    $receiptGroups = [];
                    foreach ($expenses as $expense) {
                        if ($expense->receipt_id && $expense->receipt) {
                            $receiptId = $expense->receipt_id;
                            if (!isset($receiptGroups[$receiptId])) {
                                $receiptGroups[$receiptId] = [
                                    'receipt' => $expense->receipt,
                                    'payer' => $expense->participant, // Assuming the receipt payer is the same as the line item payer (enforced by controller)
                                    'items' => [],
                                    'total_amount' => 0
                                ];
                            }
                            $receiptGroups[$receiptId]['items'][] = $expense;
                            $receiptGroups[$receiptId]['total_amount'] += $expense->total_amount;
                        } else {
                            $standaloneExpenses[] = $expense;
                        }
                    }
                ?>


                <?php if (empty($expenses)): ?>
                    <p class="text-sm text-slate-400">No expenses recorded yet.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        
                        <!-- Render Receipt Groups -->
                        <?php foreach ($receiptGroups as $group): ?>
                            <details class="group bg-slate-50 dark:bg-slate-700/30 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden transition-colors">
                                <summary class="flex items-center justify-between p-3 cursor-pointer select-none hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                🛒 <?= h($group['receipt']->name) ?>
                                                <?php if ($group['receipt']->image_url): ?>
                                                    <button type="button" onclick="openImageViewer('<?= h($group['receipt']->image_url) ?>')" class="ml-1 inline-flex items-center text-blue-500 hover:text-blue-700 focus:outline-none" title="View Physical Receipt">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Paid by: <?= h($group['payer']->user->name ?? $group['payer']->guest_name ?? 'Unknown') ?> • <?= count($group['items']) ?> items
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right flex items-center gap-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">RM <?= number_format($group['total_amount'], 2) ?></div>
                                        <?php if ($isHost && $session->status === 'open'): ?>
                                            <?= $this->Form->postLink(
                                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                                ['controller' => 'Receipts', 'action' => 'delete', $receiptId],
                                                [
                                                    'confirm' => 'Are you sure you want to delete this entire receipt and ALL its line items?',
                                                    'escape' => false,
                                                    'class' => 'text-red-400 hover:text-red-600 p-1 bg-red-50 hover:bg-red-100 rounded transition border border-red-100',
                                                    'title' => 'Delete Receipt'
                                                ]
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                </summary>
                                <div class="px-3 pb-3 pt-1 border-t border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 transition-colors">
                                    <div class="space-y-2 mt-2">
                                        <?php foreach ($group['items'] as $expense): ?>
                                            <div class="flex items-center justify-between py-1.5 text-sm group/item hover:bg-slate-50 dark:hover:bg-slate-700/50 px-2 -mx-2 rounded transition">
                                                <div class="flex-1">
                                                    <span class="text-slate-700 dark:text-slate-200"><?= h($expense->description) ?></span>
                                                    <span class="text-xs text-slate-400 ml-1">(x<?= $expense->quantity ?>)</span>
                                                    <?php if ($expense->image_url): ?>
                                                        <button type="button" onclick="openImageViewer('<?= h($expense->image_url) ?>')" class="ml-1 inline-flex items-center text-blue-500 hover:text-blue-700 focus:outline-none" title="View Physical Receipt">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-slate-600 dark:text-slate-300 font-medium">RM <?= number_format($expense->total_amount, 2) ?></div>
                                                    
                                                    <?php if ($isHost && $session->status === 'open'): ?>
                                                        <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity">
                                                            <?php 
                                                                $consumerAllocs = array_filter($expense->expense_allocations, fn($a) => !$a->is_payer);
                                                                $consumerIds = array_map(fn($a) => $a->participant_id, $consumerAllocs);
                                                            ?>
                                                            <button type="button" 
                                                                onclick='openEditExpenseModal(<?= json_encode([
                                                                    "id" => $expense->id,
                                                                    "description" => $expense->description,
                                                                    "quantity" => $expense->quantity,
                                                                    "total_amount" => $expense->total_amount,
                                                                    "payer_id" => $expense->participant_id,
                                                                    "consumers" => array_values($consumerIds)
                                                                ]) ?>)'
                                                                class="text-blue-400 hover:text-blue-600 p-1 bg-blue-50 hover:bg-blue-100 rounded transition" 
                                                                title="Edit Line Item">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            </button>
                                                            <?= $this->Form->postLink(
                                                                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                                                ['controller' => 'Expenses', 'action' => 'delete', $expense->id],
                                                                [
                                                                    'confirm' => 'Delete this line item?',
                                                                    'escape' => false,
                                                                    'class' => 'text-red-400 hover:text-red-600 p-1 bg-red-50 hover:bg-red-100 rounded transition',
                                                                    'title' => 'Delete Line Item'
                                                                ]
                                                            ) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </details>
                        <?php endforeach; ?>

                        <!-- Render Standalone Expenses -->
                        <?php foreach ($standaloneExpenses as $expense): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-600">
                            <div>
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                    🍽️ <?= h($expense->description) ?>
                                    <?php if ($expense->image_url): ?>
                                        <button type="button" onclick="openImageViewer('<?= h($expense->image_url) ?>')" class="ml-1 inline-flex items-center text-blue-500 hover:text-blue-700 focus:outline-none" title="View Physical Receipt">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Paid by: <?= h($expense->participant->user->name ?? $expense->participant->guest_name ?? 'Unknown') ?>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-3">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100">RM <?= number_format($expense->total_amount, 2) ?></div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500">
                                        <?php 
                                            $consumerAllocs = array_filter($expense->expense_allocations, fn($a) => !$a->is_payer);
                                            $consumerIds = array_map(fn($a) => $a->participant_id, $consumerAllocs);
                                        ?>
                                        <?= count($consumerAllocs) ?> people splitting
                                    </div>
                                </div>
                                <?php if ($isHost && $session->status === 'open'): ?>
                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                            onclick='openEditExpenseModal(<?= json_encode([
                                                "id" => $expense->id,
                                                "description" => $expense->description,
                                                "quantity" => $expense->quantity,
                                                "total_amount" => $expense->total_amount,
                                                "payer_id" => $expense->participant_id,
                                                "consumers" => array_values($consumerIds)
                                            ]) ?>)'
                                            class="text-blue-400 hover:text-blue-600 p-1.5 bg-blue-50 hover:bg-blue-100 rounded-md transition border border-blue-100" 
                                            title="Edit Expense">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <?= $this->Form->postLink(
                                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                            ['controller' => 'Expenses', 'action' => 'delete', $expense->id],
                                            [
                                                'confirm' => 'Are you sure you want to delete this expense? This will reverse all calculations.',
                                                'escape' => false,
                                                'class' => 'text-red-400 hover:text-red-600 p-1.5 bg-red-50 hover:bg-red-100 rounded-md transition border border-red-100',
                                                'title' => 'Delete Expense'
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

            <!-- Current User Totals -->
            <?php if ($currentParticipant && isset($participantTotals[$currentParticipant->id])): ?>
            <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl shadow-sm border border-blue-100 dark:border-blue-900/50 p-5 mt-5 transition-colors">
                <h2 class="text-base font-semibold text-blue-900 dark:text-blue-100 mb-2">My Current Total</h2>
                <div class="flex justify-between text-sm mb-1 text-blue-800 dark:text-blue-200">
                    <span>Subtotal</span>
                    <span>RM <?= number_format($participantTotals[$currentParticipant->id]['subtotal'], 2) ?></span>
                </div>
                <div class="flex justify-between text-sm mb-2 text-blue-800 dark:text-blue-200">
                    <span>Session Charges/Taxes</span>
                    <span>RM <?= number_format($participantTotals[$currentParticipant->id]['total_charges'], 2) ?></span>
                </div>
                <div class="flex justify-between font-bold text-lg text-blue-900 dark:text-blue-100 border-t border-blue-200 dark:border-blue-800 pt-2">
                    <span>Grand Total</span>
                    <span>RM <?= number_format($participantTotals[$currentParticipant->id]['grand_total'], 2) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <?php include('my_settlements_snippet.php'); ?>

        </div>
    </div>
</div>

<script>
function copyJoinLink() {
    const link = document.getElementById('join-link').textContent.trim();
    navigator.clipboard.writeText(link).then(() => {
        alert('Join link copied!');
    });
}
function copyJoinCode() {
    const code = document.getElementById('join-code').textContent.trim();
    navigator.clipboard.writeText(code).then(() => {
        alert('Session code copied!');
    });
}
function openImageViewer(url) {
    document.getElementById('viewer-img').src = url;
    document.getElementById('imageViewerModal').classList.remove('hidden');
}
function closeImageViewer() {
    document.getElementById('imageViewerModal').classList.add('hidden');
    document.getElementById('viewer-img').src = '';
}
function closeModal() {
    document.getElementById('addExpenseModal').classList.add('hidden');
}
function closeEditModal() {
    document.getElementById('editExpenseModal').classList.add('hidden');
}
function openEditExpenseModal(expense) {
    // Set form action dynamically
    const form = document.getElementById('editExpenseForm');
    form.action = "<?= $this->Url->build(['controller' => 'Expenses', 'action' => 'edit']) ?>/" + expense.id;

    // Populate standard fields
    document.getElementById('edit_description').value = expense.description;
    document.getElementById('edit_quantity').value = expense.quantity || 1;
    document.getElementById('edit_total_amount').value = expense.total_amount;
    document.getElementById('edit_payer_id').value = expense.payer_id;

    // Uncheck all consumers first
    document.querySelectorAll('.edit-consumer-checkbox').forEach(cb => cb.checked = false);

    // Check the consumers that are part of this expense
    expense.consumers.forEach(consumerId => {
        const cb = document.getElementById('edit_consumer_' + consumerId);
        if (cb) cb.checked = true;
    });

    document.getElementById('editExpenseModal').classList.remove('hidden');
}

function toggleCustomSplits(mode) {
    const splitType = document.getElementById(mode + '_split_type').value;
    const containers = document.querySelectorAll('.' + mode + '-custom-split-inputs');
    const pctInputs = document.querySelectorAll('.' + mode + '-split-pct');
    const exactInputs = document.querySelectorAll('.' + mode + '-split-exact');
    
    if (splitType === 'equal') {
        containers.forEach(c => c.classList.add('hidden'));
        pctInputs.forEach(i => i.required = false);
        exactInputs.forEach(i => i.required = false);
    } else {
        containers.forEach(c => c.classList.remove('hidden'));
        if (splitType === 'percentage') {
            pctInputs.forEach(i => { i.classList.remove('hidden'); i.required = true; });
            exactInputs.forEach(i => { i.classList.add('hidden'); i.required = false; });
        } else {
            pctInputs.forEach(i => { i.classList.add('hidden'); i.required = false; });
            exactInputs.forEach(i => { i.classList.remove('hidden'); i.required = true; });
        }
    }
    validateSplits(mode);
}

function validateAddExpenseSplits() { validateSplits('add'); }
function validateEditExpenseSplits() { validateSplits('edit'); }

function validateSplits(mode) {
    const splitType = document.getElementById(mode + '_split_type').value;
    const warning = document.getElementById(mode + '_split_warning');
    const modal = document.getElementById(mode + 'ExpenseModal');
    const submitBtn = modal.querySelector('button[type="submit"]');
    
    if (splitType === 'equal') {
        warning.classList.add('hidden');
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        return;
    }
    
    let isValid = false;
    
    if (splitType === 'percentage') {
        let totalPct = 0;
        const checkboxes = modal.querySelectorAll('.' + mode + '-consumer-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = cb.closest('.flex').querySelector('.' + mode + '-split-pct');
                totalPct += parseFloat(input.value) || 0;
            }
        });
        
        if (Math.abs(totalPct - 100) > 0.01) {
            warning.innerText = `Percentages must add up to exactly 100%. Currently: ${totalPct.toFixed(1)}%`;
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
            isValid = true;
        }
    } else if (splitType === 'exact') {
        let totalExact = 0;
        let totalAmountInput;
        if (mode === 'add') {
             totalAmountInput = modal.querySelector('input[name="total_amount"]');
        } else {
             totalAmountInput = document.getElementById('edit_total_amount');
        }
        const targetAmount = parseFloat(totalAmountInput.value) || 0;
        
        const checkboxes = modal.querySelectorAll('.' + mode + '-consumer-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = cb.closest('.flex').querySelector('.' + mode + '-split-exact');
                totalExact += parseFloat(input.value) || 0;
            }
        });
        
        if (Math.abs(totalExact - targetAmount) > 0.01) {
            warning.innerText = `Exact amounts must add up to exactly RM ${targetAmount.toFixed(2)}. Currently: RM ${totalExact.toFixed(2)}`;
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
            isValid = true;
        }
    }
    
    if (isValid) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}
</script>

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="fixed inset-0 z-[70] hidden bg-slate-900/90 backdrop-blur-sm flex flex-col items-center justify-center p-4" onclick="closeImageViewer()">
    <!-- Top close button - always visible -->
    <button type="button" onclick="closeImageViewer()" class="fixed top-4 right-4 z-[71] bg-white/20 hover:bg-white/40 text-white rounded-full w-10 h-10 flex items-center justify-center text-2xl leading-none backdrop-blur-sm transition">&times;</button>
    <div class="relative max-w-4xl w-full flex-1 flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <img id="viewer-img" src="" alt="Receipt Image" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-2xl">
    </div>
    <!-- Bottom close button for mobile -->
    <button type="button" onclick="closeImageViewer()" class="mt-4 px-6 py-2.5 bg-white/20 hover:bg-white/40 text-white font-semibold rounded-full text-sm backdrop-blur-sm transition">Close</button>
</div>

<!-- Add Expense Modal -->
<div id="addExpenseModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Add New Expense</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 text-2xl leading-none">&times;</button>
        </div>
        
        <?= $this->Form->create(null, ['url' => ['controller' => 'Expenses', 'action' => 'add', $session->uuid], 'type' => 'file', 'class' => 'p-6 space-y-4']) ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description <span class="text-red-500">*</span></label>
                <input type="text" name="description" required placeholder="e.g. Dinner at Nando's" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Total Amount (RM) <span class="text-red-500">*</span></label>
                <input type="number" name="total_amount" step="0.01" min="0.01" required placeholder="0.00" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateAddExpenseSplits()">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Optional: Attach Receipt Image</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:bg-slate-700 dark:text-slate-300 dark:file:bg-blue-900/30 dark:file:text-blue-400 dark:hover:file:bg-blue-900/50">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Who Paid? <span class="text-red-500">*</span></label>
                <select name="payer_id" required class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    <option value="">Select participant...</option>
                    <?php foreach ($session->participants as $p): ?>
                        <option value="<?= $p->id ?>" <?= $currentParticipant && $currentParticipant->id === $p->id ? 'selected' : '' ?>>
                            <?= h($p->user->name ?? $p->guest_name ?? 'Unknown') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Split Method</label>
                <select name="split_type" id="add_split_type" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" onchange="toggleCustomSplits('add')">
                    <option value="equal">Split Equally</option>
                    <option value="percentage">By Percentage (%)</option>
                    <option value="exact">By Exact Amount (RM)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Who is splitting this? <span class="text-red-500">*</span></label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-lg p-3 space-y-2 bg-slate-50 dark:bg-slate-700/50">
                    <?php foreach ($session->participants as $p): ?>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer flex-1">
                            <input type="checkbox" name="consumers[]" value="<?= $p->id ?>" checked class="add-consumer-checkbox accent-blue-600 w-4 h-4" onchange="validateAddExpenseSplits()">
                            <span class="text-sm text-slate-700 dark:text-slate-200"><?= h($p->user->name ?? $p->guest_name ?? 'Unknown') ?></span>
                        </label>
                        <div class="add-custom-split-inputs hidden flex items-center gap-2 w-24">
                            <input type="number" name="split_percentages[<?= $p->id ?>]" placeholder="%" step="0.1" min="0" max="100" class="add-split-pct hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateAddExpenseSplits()">
                            <input type="number" name="split_exacts[<?= $p->id ?>]" placeholder="RM" step="0.01" min="0" class="add-split-exact hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateAddExpenseSplits()">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="add_split_warning" class="text-xs text-red-500 mt-2 hidden"></div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeModal()" class="px-5 py-3 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition text-sm">Cancel</button>
                <button type="submit" class="px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">Save Expense</button>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<!-- Edit Expense Modal -->
<div id="editExpenseModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Edit Expense</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 text-2xl leading-none">&times;</button>
        </div>
        
        <?= $this->Form->create(null, ['id' => 'editExpenseForm', 'type' => 'file', 'class' => 'p-6 space-y-4']) ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description <span class="text-red-500">*</span></label>
                <input type="text" id="edit_description" name="description" required placeholder="e.g. Dinner at Nando's" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" id="edit_quantity" name="quantity" min="1" required placeholder="1" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Total Amount (RM) <span class="text-red-500">*</span></label>
                    <input type="number" id="edit_total_amount" name="total_amount" step="0.01" min="0.01" required placeholder="0.00" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateEditExpenseSplits()">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Optional: Replace Receipt Image</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:bg-slate-700 dark:text-slate-300 dark:file:bg-blue-900/30 dark:file:text-blue-400 dark:hover:file:bg-blue-900/50">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Leave empty to keep current image.</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Who Paid? <span class="text-red-500">*</span></label>
                <select id="edit_payer_id" name="payer_id" required class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                    <option value="">Select participant...</option>
                    <?php foreach ($session->participants as $p): ?>
                        <option value="<?= $p->id ?>">
                            <?= h($p->user->name ?? $p->guest_name ?? 'Unknown') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Split Method</label>
                <select name="split_type" id="edit_split_type" class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" onchange="toggleCustomSplits('edit')">
                    <option value="equal">Split Equally</option>
                    <option value="percentage">By Percentage (%)</option>
                    <option value="exact">By Exact Amount (RM)</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Who is splitting this? <span class="text-red-500">*</span></label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 dark:border-slate-600 rounded-lg p-3 space-y-2 bg-slate-50 dark:bg-slate-700/50">
                    <?php foreach ($session->participants as $p): ?>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer flex-1">
                            <input type="checkbox" id="edit_consumer_<?= $p->id ?>" name="consumers[]" value="<?= $p->id ?>" class="edit-consumer-checkbox accent-blue-600 w-4 h-4" onchange="validateEditExpenseSplits()">
                            <span class="text-sm text-slate-700 dark:text-slate-200"><?= h($p->user->name ?? $p->guest_name ?? 'Unknown') ?></span>
                        </label>
                        <div class="edit-custom-split-inputs hidden flex items-center gap-2 w-24">
                            <input type="number" id="edit_split_pct_<?= $p->id ?>" name="split_percentages[<?= $p->id ?>]" placeholder="%" step="0.1" min="0" max="100" class="edit-split-pct hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateEditExpenseSplits()">
                            <input type="number" id="edit_split_exact_<?= $p->id ?>" name="split_exacts[<?= $p->id ?>]" placeholder="RM" step="0.01" min="0" class="edit-split-exact hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateEditExpenseSplits()">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="edit_split_warning" class="text-xs text-red-500 mt-2 hidden"></div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditModal()" class="px-5 py-3 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition text-sm">Cancel</button>
                <button type="submit" class="px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">Update Expense</button>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<?php include('settlement_modals_snippet.php'); ?>

<!-- Add Itemized Receipt Modal -->
<div id="addReceiptModal" class="fixed inset-0 z-[60] hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh] transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50 shrink-0">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Add Itemized Receipt</h3>
            <button type="button" onclick="closeReceiptModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 text-2xl leading-none">&times;</button>
        </div>
        
        <?= $this->Form->create(null, ['url' => ['controller' => 'Receipts', 'action' => 'add', $session->uuid], 'type' => 'file', 'class' => 'flex flex-col overflow-hidden']) ?>
            
            <div class="p-6 overflow-y-auto space-y-6">
                <!-- Receipt Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Receipt Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Tesco Groceries" class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Optional: Attach Receipt Image</label>
                        <input type="file" name="image" accept="image/*" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-300 dark:file:bg-blue-900/30 dark:file:text-blue-400 dark:hover:file:bg-blue-900/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Who Paid? <span class="text-red-500">*</span></label>
                        <select name="payer_id" required class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                            <option value="">Select participant...</option>
                            <?php foreach ($session->participants as $p): ?>
                                <option value="<?= $p->id ?>" <?= $currentParticipant && $currentParticipant->id === $p->id ? 'selected' : '' ?>>
                                    <?= h($p->user->name ?? $p->guest_name ?? 'Unknown') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Line Items Container -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Line Items</label>
                        <button type="button" onclick="addReceiptItem()" class="text-xs font-semibold text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition">+ Add Row</button>
                    </div>
                    <div id="receipt-items-container" class="space-y-4">
                        <!-- Items injected here by JS -->
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center shrink-0">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Total: <span id="receipt-grand-total" class="text-blue-700 dark:text-blue-400 text-lg ml-1">RM 0.00</span>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeReceiptModal()" class="px-5 py-3 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">Save Receipt</button>
                </div>
            </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<script>
let receiptItemIndex = 0;

function closeReceiptModal() {
    document.getElementById('addReceiptModal').classList.add('hidden');
}

function updateReceiptTotal() {
    let total = 0;
    const rows = document.querySelectorAll('[id^="receipt-item-row-"]');
    
    rows.forEach(row => {
        const idx = row.id.split('-').pop();
        const qtyInput = row.querySelector('.receipt-item-qty');
        const priceInput = row.querySelector('.receipt-item-price');
        
        if (qtyInput && priceInput) {
            const q = parseFloat(qtyInput.value) || 0;
            const p = parseFloat(priceInput.value) || 0;
            total += (q * p);
            
            const splitTypeEl = document.getElementById('receipt_split_type_' + idx);
            if (splitTypeEl && splitTypeEl.value === 'exact') {
                validateReceiptItemSplits(idx);
            }
        }
    });
    
    document.getElementById('receipt-grand-total').innerText = 'RM ' + total.toFixed(2);
}

function removeReceiptItem(index) {
    const row = document.getElementById('receipt-item-row-' + index);
    if (row) {
        row.remove();
        updateReceiptTotal();
    }
}

function addReceiptItem() {
    const container = document.getElementById('receipt-items-container');
    const idx = receiptItemIndex++;
    
    let consumersHtml = '<div class="mt-2 space-y-2">';
    <?php foreach ($session->participants as $p): ?>
        consumersHtml += `
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer flex-1">
                    <input type="checkbox" name="items[${idx}][consumers][]" value="<?= $p->id ?>" checked class="receipt-consumer-checkbox accent-blue-600 w-4 h-4" onchange="validateReceiptItemSplits(${idx})">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><?= addslashes(h($p->user->name ?? $p->guest_name ?? 'Unknown')) ?></span>
                </label>
                <div class="receipt-custom-split-inputs-${idx} hidden flex items-center gap-2 w-24">
                    <input type="number" name="items[${idx}][split_percentages][<?= $p->id ?>]" placeholder="%" step="0.1" min="0" max="100" class="receipt-split-pct-${idx} hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateReceiptItemSplits(${idx})">
                    <input type="number" name="items[${idx}][split_exacts][<?= $p->id ?>]" placeholder="RM" step="0.01" min="0" class="receipt-split-exact-${idx} hidden w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="validateReceiptItemSplits(${idx})">
                </div>
            </div>
        `;
    <?php endforeach; ?>
    consumersHtml += '</div>';
    consumersHtml += `<div id="receipt_split_warning_${idx}" class="text-xs text-red-500 mt-2 hidden"></div>`;

    const rowHtml = `
        <div id="receipt-item-row-${idx}" data-valid-split="true" class="p-4 border border-slate-200 dark:border-slate-600 rounded-xl relative bg-white dark:bg-slate-800 group shadow-sm">
            <button type="button" onclick="removeReceiptItem(${idx})" class="absolute -top-2.5 -right-2.5 bg-red-100 dark:bg-red-900 hover:bg-red-500 hover:text-white text-red-500 dark:text-red-300 rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-sm transition border border-red-200 dark:border-red-800" title="Remove Item">&times;</button>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="items[${idx}][description]" required placeholder="Item Description" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100">
                </div>
                <div class="w-full sm:w-24">
                    <input type="number" name="items[${idx}][quantity]" value="1" min="1" required placeholder="Qty" class="receipt-item-qty w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="updateReceiptTotal()">
                </div>
                <div class="w-full sm:w-32">
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-slate-400 text-sm">RM</span>
                        <input type="number" name="items[${idx}][unit_price]" step="0.01" min="0.01" required placeholder="0.00" class="receipt-item-price w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" oninput="updateReceiptTotal()">
                    </div>
                </div>
            </div>
            
            <div class="mt-3 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-slate-500 dark:text-slate-400">Who splits this item?</div>
                    <select name="items[${idx}][split_type]" id="receipt_split_type_${idx}" class="px-2 py-1 rounded border border-slate-300 dark:border-slate-600 focus:border-blue-500 outline-none text-xs bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100" onchange="toggleReceiptItemSplits(${idx})">
                        <option value="equal">Split Equally</option>
                        <option value="percentage">By Percentage (%)</option>
                        <option value="exact">By Exact Amount (RM)</option>
                    </select>
                </div>
                ${consumersHtml}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function toggleReceiptItemSplits(idx) {
    const splitType = document.getElementById('receipt_split_type_' + idx).value;
    const containers = document.querySelectorAll('.receipt-custom-split-inputs-' + idx);
    const pctInputs = document.querySelectorAll('.receipt-split-pct-' + idx);
    const exactInputs = document.querySelectorAll('.receipt-split-exact-' + idx);
    
    if (splitType === 'equal') {
        containers.forEach(c => c.classList.add('hidden'));
        pctInputs.forEach(i => i.required = false);
        exactInputs.forEach(i => i.required = false);
    } else {
        containers.forEach(c => c.classList.remove('hidden'));
        if (splitType === 'percentage') {
            pctInputs.forEach(i => { i.classList.remove('hidden'); i.required = true; });
            exactInputs.forEach(i => { i.classList.add('hidden'); i.required = false; });
        } else {
            pctInputs.forEach(i => { i.classList.add('hidden'); i.required = false; });
            exactInputs.forEach(i => { i.classList.remove('hidden'); i.required = true; });
        }
    }
    validateReceiptItemSplits(idx);
}

function validateReceiptItemSplits(idx) {
    const splitType = document.getElementById('receipt_split_type_' + idx).value;
    const warning = document.getElementById('receipt_split_warning_' + idx);
    const row = document.getElementById('receipt-item-row-' + idx);
    
    if (splitType === 'equal') {
        warning.classList.add('hidden');
        row.dataset.validSplit = 'true';
        checkAllReceiptItemsValid();
        return;
    }
    
    let isValid = false;
    
    if (splitType === 'percentage') {
        let totalPct = 0;
        const checkboxes = row.querySelectorAll('.receipt-consumer-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = cb.closest('.flex').querySelector(`.receipt-split-pct-${idx}`);
                totalPct += parseFloat(input.value) || 0;
            } else {
                const input = cb.closest('.flex').querySelector(`.receipt-split-pct-${idx}`);
                input.value = '';
            }
        });
        
        if (Math.abs(totalPct - 100) > 0.01) {
            warning.innerText = `Percentages must add up to exactly 100%. Currently: ${totalPct.toFixed(1)}%`;
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
            isValid = true;
        }
    } else if (splitType === 'exact') {
        let totalExact = 0;
        const qty = parseFloat(row.querySelector('.receipt-item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.receipt-item-price').value) || 0;
        const targetAmount = qty * price;
        
        const checkboxes = row.querySelectorAll('.receipt-consumer-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = cb.closest('.flex').querySelector(`.receipt-split-exact-${idx}`);
                totalExact += parseFloat(input.value) || 0;
            } else {
                const input = cb.closest('.flex').querySelector(`.receipt-split-exact-${idx}`);
                input.value = '';
            }
        });
        
        if (Math.abs(totalExact - targetAmount) > 0.01) {
            warning.innerText = `Exact amounts must add up to exactly RM ${targetAmount.toFixed(2)}. Currently: RM ${totalExact.toFixed(2)}`;
            warning.classList.remove('hidden');
        } else {
            warning.classList.add('hidden');
            isValid = true;
        }
    }
    
    row.dataset.validSplit = isValid ? 'true' : 'false';
    checkAllReceiptItemsValid();
}

function checkAllReceiptItemsValid() {
    const submitBtn = document.querySelector('#addReceiptModal button[type="submit"]');
    const rows = document.querySelectorAll('[id^="receipt-item-row-"]');
    let allValid = true;
    rows.forEach(row => {
        const idx = row.id.split('-').pop();
        const splitTypeEl = document.getElementById('receipt_split_type_' + idx);
        if (splitTypeEl && splitTypeEl.value !== 'equal' && row.dataset.validSplit !== 'true') {
            allValid = false;
        }
    });
    
    if (allValid) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addReceiptItem();
});
</script>
