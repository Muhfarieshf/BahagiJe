<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $s
 * @var array $presetIcons
 * @var array $statusColors
 * @var bool $isHost
 */

$totalSpend = 0;
if (!empty($s->expenses)) {
    foreach ($s->expenses as $expense) {
        $totalSpend += $expense->total_amount;
    }
}

// Build payment status map: debtor participant_id => bool (has proof)
$paymentStatus = []; // [ participant_id => true/false ]
if (!empty($s->settlement_transactions)) {
    foreach ($s->settlement_transactions as $txn) {
        $debtorId = $txn->debtor_id;
        $hasPaid  = !empty($txn->payment_proofs);
        // Once marked as paid, keep it
        if (!isset($paymentStatus[$debtorId])) {
            $paymentStatus[$debtorId] = $hasPaid;
        } elseif ($hasPaid) {
            $paymentStatus[$debtorId] = true;
        }
    }
}
$unpaid = false;
if ($s->status === 'closed' && !empty($s->settlement_transactions)) {
    foreach ($s->settlement_transactions as $txn) {
        if ($txn->status !== 'settled') {
            $unpaid = true;
            break;
        }
    }
}
?>
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-blue-200 dark:hover:border-blue-500 transition relative group flex flex-col h-full">
    
    <!-- Bulk Delete Checkbox (Hosted only) -->
    <?php if ($isHost): ?>
        <?php if ($unpaid): ?>
        <div class="session-checkbox-wrapper absolute top-3 right-3 z-10 hidden" title="Cannot delete unpaid session">
            <input type="checkbox" disabled class="w-5 h-5 bg-slate-100 border-slate-200 rounded cursor-not-allowed opacity-50" title="Cannot delete unpaid session">
        </div>
        <?php else: ?>
        <div class="session-checkbox-wrapper absolute top-3 right-3 z-10 hidden">
            <input type="checkbox" name="session_uuids[]" value="<?= h($s->uuid) ?>" class="session-checkbox w-5 h-5 text-red-600 bg-white border-slate-300 rounded focus:ring-red-500 cursor-pointer shadow-sm hover:ring hover:ring-red-100 transition">
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <a href="<?= $this->Url->build(['controller' => 'GroupSessions', 'action' => 'view', $s->uuid]) ?>" class="flex-1 p-4 block">
        <!-- Top Row -->
        <div class="flex items-center justify-between mb-3 pr-6">
            <div class="flex items-center gap-3">
                <div class="text-2xl bg-slate-50 dark:bg-slate-700/50 p-1.5 rounded-lg border border-slate-100 dark:border-slate-600">
                    <?= $presetIcons[$s->preset_type] ?? '📋' ?>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Preset</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $statusColors[$s->status] ?? 'bg-slate-100 text-slate-600' ?>">
                        <?= ucfirst($s->status) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Middle Row -->
        <div class="mb-3">
            <h3 class="font-bold text-base text-slate-800 dark:text-slate-100 line-clamp-1 pr-4" title="<?= h($s->name) ?>"><?= h($s->name) ?></h3>
            <div class="mt-1 flex items-baseline gap-1.5">
                <span class="text-xs font-medium text-slate-400 dark:text-slate-500">Total</span>
                <span class="text-lg font-black text-slate-800 dark:text-slate-100 truncate">RM <?= number_format($totalSpend, 2) ?></span>
            </div>
        </div>

        <!-- Payment Status Avatars (shown when settlement data exists) -->
        <?php if (!empty($paymentStatus)): ?>
        <div class="mb-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Payment Status</p>
            <div class="flex flex-wrap gap-1">
                <?php foreach ($s->settlement_transactions as $txn): ?>
                    <?php
                        $debtor    = $txn->debtor;
                        $name      = $debtor->user->name ?? $debtor->guest_name ?? '?';
                        $initial   = strtoupper(substr($name, 0, 1));
                        $hasPaid   = $paymentStatus[$txn->debtor_id] ?? false;
                        $bgColor   = $hasPaid ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-600 border-red-300';
                        $indicator = $hasPaid ? '✓' : '!';
                    ?>
                    <div class="relative group/avatar">
                        <div class="w-7 h-7 rounded-full border flex items-center justify-center text-xs font-bold <?= $bgColor ?>">
                            <?= h($initial) ?>
                        </div>
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover/avatar:block z-20 pointer-events-none">
                            <div class="bg-slate-800 text-white text-[10px] rounded px-2 py-1 whitespace-nowrap shadow-lg">
                                <?= h($name) ?> — <?= $hasPaid ? 'Paid ✓' : 'Pending' ?>
                            </div>
                            <div class="w-2 h-2 bg-slate-800 rotate-45 mx-auto -mt-1"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom Row -->
        <div class="pt-3 border-t border-slate-50 dark:border-slate-700/50 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <?= count($s->participants) ?> / <?= $s->max_participants ?>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <?= $s->created_at->format('M j, Y') ?>
            </div>
        </div>
    </a>
</div>
