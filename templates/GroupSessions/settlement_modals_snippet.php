<?php if ($session->status === 'closed'): ?>

<!-- Participant Personal Settlement Modal (Auto-Appears ONCE) -->
<?php if ($currentParticipant): ?>
<?php 
    $settlementsArray = is_array($settlements) ? $settlements : $settlements->toArray();
    $participantId = $currentParticipant->id;
    $myDebts = array_filter($settlementsArray, fn($s) => $s->debtor_id === $participantId);
    $myCredits = array_filter($settlementsArray, fn($s) => $s->creditor_id === $participantId);
?>
<?php $isModalHidden = isset($_COOKIE['settlement_shown_' . $session->id]) ? 'hidden' : ''; ?>
<div id="participantSettlementModal" class="fixed inset-0 z-[70] <?= $isModalHidden ?> bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeParticipantModal()">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative transform transition-all" onclick="event.stopPropagation()">
        <!-- Confetti / Header -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-center text-white relative">
            <button type="button" onclick="closeParticipantModal()" class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl leading-none">&times;</button>
            <div class="text-4xl mb-2">🎉</div>
            <h3 class="text-xl font-bold">Session Closed!</h3>
            <p class="text-blue-100 text-sm mt-1">Here is your final settlement</p>
        </div>
        
        <div class="p-6">
            <?php if (empty($myDebts) && empty($myCredits)): ?>
                <div class="text-center py-6">
                    <div class="text-5xl mb-4">🙌</div>
                    <p class="text-slate-700 dark:text-slate-200 font-semibold text-lg">You're all good!</p>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">You have zero outstanding debts or credits.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-2">
                    <!-- Debts -->
                    <?php foreach ($myDebts as $debt): ?>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 border border-slate-100 dark:border-slate-600 shadow-sm transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">You owe</div>
                                <div class="font-bold text-slate-800 dark:text-slate-200"><?= h($debt->creditor->user->name ?? $debt->creditor->guest_name) ?></div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-lg text-red-600 dark:text-red-400">RM <?= number_format($debt->amount, 2) ?></div>
                            </div>
                        </div>
                        
                        <?php if ($debt->creditor->user && !empty($debt->creditor->user->user_payment_methods)): ?>
                            <div class="bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg p-3">
                                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">How would you like to pay?</div>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($debt->creditor->user->user_payment_methods as $pm): ?>
                                    <button onclick="showPaymentDetails(this, <?= htmlspecialchars(json_encode([
                                        'type' => $pm->method_type,
                                        'label' => h($pm->label),
                                        'bank' => h($pm->bank_name),
                                        'name' => h($pm->account_name),
                                        'value' => h($pm->account_value),
                                        'qr' => h($pm->qr_image_url)
                                    ])) ?>)" class="text-xs font-medium px-3 py-1.5 rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                                        <?= match($pm->method_type) {
                                            'bank_transfer' => '🏦 ' . ($pm->bank_name ?: 'Bank'),
                                            'duitnow_qr' => '📱 QR',
                                            'duitnow_id' => '📲 ID',
                                            'tng' => '💳 TnG',
                                            'paypal' => '🌐 PayPal',
                                            default => 'Pay'
                                        } ?>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="payment-details-container mt-3 hidden bg-slate-50 dark:bg-slate-800 p-2 rounded text-sm text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-slate-500 dark:text-slate-400 italic">This person hasn't saved any payment details. Please contact them directly.</div>
                        <?php endif; ?>
                        
                        <div class="mt-4 flex gap-2">
                            <button type="button" onclick="openUploadProofModal(<?= $debt->id ?>)" class="flex-1 px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition text-center shadow-sm">
                                📎 Upload Receipt
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Credits (Money owed to me) -->
                    <?php if (!empty($myCredits)): ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 rounded-xl p-4 mt-6">
                        <div class="text-sm font-semibold text-green-700 dark:text-green-400 mb-2">Others owe you a total of</div>
                        <?php 
                            $totalCredits = 0;
                            foreach ($myCredits as $c) { $totalCredits += $c->amount; }
                        ?>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-500 mb-1">RM <?= number_format($totalCredits, 2) ?></div>
                        <div class="text-xs text-green-600/80 dark:text-green-400/80">Check "My Settlements" below to manage them.</div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <button type="button" onclick="document.getElementById('participantSettlementModal').style.display='none'" class="w-full mt-6 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                Got it — View Session
            </button>
        </div>
    </div>
</div>
<script>
    function closeParticipantModal() {
        document.getElementById('participantSettlementModal').style.display = 'none';
    }
    // Set cookie immediately so modal doesn't reappear on page reload
    try {
        document.cookie = "settlement_shown_<?= $session->id ?>=1; path=/; max-age=31536000";
    } catch(e) { console.error("Cookie error", e); }

    function showPaymentDetails(btn, data) {
        const container = btn.closest('.bg-white').querySelector('.payment-details-container');
        let html = '';
        if (data.type === 'duitnow_qr' && data.qr) {
            html = `<div class="text-center"><img src="${data.qr}" class="w-full max-w-[200px] mx-auto rounded-lg shadow-sm border border-slate-200"><p class="text-[10px] mt-2 text-slate-500">Scan to pay</p></div>`;
        } else if (data.type === 'bank_transfer') {
            html = `<div><strong>${data.bank}</strong><br>Acc Name: ${data.name}<br>Acc No: <strong>${data.value}</strong></div>`;
        } else {
            html = `<div><strong>${data.label || 'Details'}</strong><br>Account: <strong>${data.value}</strong></div>`;
        }
        container.innerHTML = html;
        container.classList.remove('hidden');
    }
</script>
<?php endif; ?>

<!-- Payment Proof Upload Modals (One for each debt) -->
<?php if (!empty($myDebts)): ?>
    <?php foreach ($myDebts as $debt): ?>
    <div id="uploadProofModal_<?= $debt->id ?>" class="fixed inset-0 z-[80] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-sm overflow-hidden relative transition-colors">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/50">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Upload Receipt</h3>
                <button type="button" onclick="document.getElementById('uploadProofModal_<?= $debt->id ?>').classList.add('hidden')" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 text-xl leading-none">&times;</button>
            </div>
            <?= $this->Form->create(null, ['url' => ['controller' => 'PaymentProofs', 'action' => 'upload', $debt->id], 'enctype' => 'multipart/form-data', 'class' => 'p-6 space-y-4']) ?>
                
                <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 p-3 rounded-lg text-sm font-medium border border-blue-100 dark:border-blue-900/50">
                    To: <?= h($debt->creditor->user->name ?? $debt->creditor->guest_name) ?> <br>
                    Amount: RM <?= number_format($debt->amount, 2) ?>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-xs text-amber-700 dark:text-amber-300">
                    <strong>⚠️ Note:</strong> Your receipt will be stored securely and shown only to session participants.
                </div>

                <div>
                    <input type="file" name="receipt_file" accept="image/*" required class="w-full text-sm text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 dark:hover:file:bg-blue-900/50">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('uploadProofModal_<?= $debt->id ?>').classList.add('hidden')" class="px-4 py-2 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 text-sm shadow-sm">Upload</button>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    function openUploadProofModal(id) {
        document.getElementById('uploadProofModal_' + id).classList.remove('hidden');
    }
    function openPaymentDetailsModal(id) {
        // If clicking from the My Settlements section below, we can just trigger the same modal
        // But the participant modal already handles this. We will just use the payment details modal inline or open the main modal.
        // Actually for simplicity, if they want to pay later, they can open the main modal again.
        const modal = document.getElementById('participantSettlementModal');
        if (modal) {
            modal.classList.remove('hidden');
        } else {
            alert('Payment details are loaded in the settlement popup.');
        }
    }
</script>

<?php endif; ?>
