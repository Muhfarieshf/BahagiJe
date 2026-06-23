<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var array $settlements
 * @var array $paymentProofs
 * @var \App\Model\Entity\Participant $currentParticipant
 * @var bool $isHost
 */

// Build a quick lookup map for proofs (debtor_id => proof)
$proofMap = [];
foreach ($paymentProofs as $proof) {
    // Overwrites older proofs, so the latest one per debtor is used
    $proofMap[$proof->participant_id] = $proof;
}
?>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6 transition-colors">
    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-1">💸 Final Settlements</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">This session is closed. Please settle your debts below.</p>

    <?php if (empty($settlements)): ?>
        <div class="py-6 text-center text-slate-500 dark:text-slate-400">No debts to settle. Everything is balanced! 🎉</div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($settlements as $txn): ?>
                <?php 
                $proof = $proofMap[$txn->debtor_id] ?? null;
                $isMyDebt = ($currentParticipant && $currentParticipant->id === $txn->debtor_id);
                $isMyCredit = ($currentParticipant && $currentParticipant->id === $txn->creditor_id);
                ?>
                <div class="p-4 rounded-xl border transition-colors <?= $txn->status === 'settled' ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800/50' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600' ?>">
                    
                    <!-- Top Row: Who owes whom -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[140px] sm:max-w-[200px]"><?= h($txn->debtor->user->name ?? $txn->debtor->guest_name) ?></span>
                            <span class="text-slate-400 dark:text-slate-500 text-sm shrink-0">➔</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[140px] sm:max-w-[200px]"><?= h($txn->creditor->user->name ?? $txn->creditor->guest_name) ?></span>
                        </div>
                        <div class="font-bold text-lg text-slate-800 dark:text-slate-200 shrink-0">
                            RM <?= number_format($txn->amount, 2) ?>
                        </div>
                    </div>

                    <!-- Bottom Row: Status & Actions -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-4 pt-3 border-t <?= $txn->status === 'settled' ? 'border-green-200 dark:border-green-800/50' : 'border-slate-200 dark:border-slate-600' ?>">
                        
                        <!-- Status Badge -->
                        <div>
                            <?php if ($txn->status === 'settled'): ?>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-700 bg-green-200 px-2.5 py-1 rounded-full">
                                    ✅ Settled
                                </span>
                            <?php elseif ($proof): ?>
                                <?php if ($proof->status === 'pending'): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full whitespace-nowrap">
                                        ⏳ Verifying
                                    </span>
                                <?php elseif ($proof->status === 'rejected'): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-red-700 bg-red-100 px-2.5 py-1 rounded-full whitespace-nowrap">
                                        ❌ Rejected
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 px-2.5 py-1 rounded-full whitespace-nowrap">
                                    Awaiting Payment
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center flex-wrap gap-3">
                            <?php if ($isMyDebt && (!$proof || $proof->status === 'rejected')): ?>
                                <button type="button" onclick="openUploadProofModal(<?= $txn->id ?>)" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    <?= $proof && $proof->status === 'rejected' ? 'Re-upload' : 'Upload Receipt' ?>
                                </button>
                            <?php endif; ?>

                            <?php if ($proof): ?>
                                <button type="button" onclick="openProofModal('<?= h($proof->proof_url) ?>')" class="text-sm text-blue-600 dark:text-blue-400 hover:underline font-medium">View Receipt</button>
                                
                                <?php if ($proof->status === 'pending' && $isMyCredit): ?>
                                    <?= $this->Form->create(null, ['url' => ['controller' => 'PaymentProofs', 'action' => 'verify', $proof->id, 'approve'], 'class' => 'inline']) ?>
                                        <button type="submit" class="text-sm text-green-600 dark:text-green-400 hover:underline font-medium">Approve</button>
                                    <?= $this->Form->end() ?>
                                    
                                    <?= $this->Form->create(null, ['url' => ['controller' => 'PaymentProofs', 'action' => 'verify', $proof->id, 'reject'], 'class' => 'inline']) ?>
                                        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:underline font-medium">Reject</button>
                                    <?= $this->Form->end() ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Receipt Image Modal -->
<div id="proofModal" class="fixed inset-0 z-[70] hidden bg-slate-900/90 backdrop-blur-sm flex flex-col items-center justify-center p-4" onclick="closeProofModal()">
    <div class="relative max-w-4xl w-full flex-1 flex flex-col items-center justify-center" onclick="event.stopPropagation()">
        <img id="proofModalImage" src="" alt="Transfer Receipt" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain bg-slate-100">
    </div>
    <!-- Bottom close button -->
    <button type="button" onclick="closeProofModal()" class="mt-4 px-8 py-3 bg-white/20 hover:bg-white/40 text-white font-semibold rounded-full text-sm backdrop-blur-sm transition shadow-lg">Close Image</button>
</div>

<script>
function openProofModal(url) {
    document.getElementById('proofModalImage').src = url;
    document.getElementById('proofModal').classList.remove('hidden');
}
function closeProofModal() {
    document.getElementById('proofModal').classList.add('hidden');
    // Clear src to stop image loading if closed quickly
    setTimeout(() => { document.getElementById('proofModalImage').src = ''; }, 200);
}
</script>
