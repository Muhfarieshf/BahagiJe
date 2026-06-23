            <!-- My Settlements Section (For Closed Sessions) -->
            <?php if ($session->status === 'closed' && $currentParticipant): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5 mt-5 transition-colors">
                <h2 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-4">💸 My Settlements</h2>
                
                <?php
$settlementsArray = is_array($settlements) ? $settlements : $settlements->toArray();
$participantId = $currentParticipant->id;
$myDebts = array_filter($settlementsArray, fn($s) => $s->debtor_id === $participantId);
$myCredits = array_filter($settlementsArray, fn($s) => $s->creditor_id === $participantId);
?>

                
                <?php if (empty($myDebts) && empty($myCredits)): ?>
                    <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-lg text-sm border border-green-100 dark:border-green-800/50 font-medium transition-colors">
                        🎉 You're all good! You don't owe anyone and nobody owes you.
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        
                        <!-- What I Owe (Debts) -->
                        <?php if (!empty($myDebts)): ?>
                        <div>
                            <h3 class="text-sm font-bold text-red-600 dark:text-red-400 mb-3 uppercase tracking-wide">I Owe</h3>
                            <div class="space-y-3">
                                <?php foreach ($myDebts as $debt): ?>
                                <?php 
                                    $proof = array_filter($paymentProofs, fn($p) => $p->settlement_transaction_id === $debt->id);
                                    $proof = reset($proof);
                                ?>
                                <div class="bg-red-50/50 dark:bg-red-900/20 rounded-lg p-4 border border-red-100 dark:border-red-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors">
                                    <div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400">To <span class="font-bold text-slate-800 dark:text-slate-200"><?= h($debt->creditor->user->name ?? $debt->creditor->guest_name) ?></span></div>
                                        <div class="font-bold text-lg text-red-600 dark:text-red-400">RM <?= number_format($debt->amount, 2) ?></div>
                                        
                                        <?php if ($debt->status === 'pending'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase">Pending</span>
                                        <?php elseif ($debt->status === 'claimed'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase">Waiting Confirmation</span>
                                        <?php elseif ($debt->status === 'settled'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase">Settled ✓</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($debt->status !== 'settled'): ?>
                                    <div class="flex flex-col gap-2 shrink-0">
                                        <!-- View Payment Methods Button -->
                                        <button type="button" onclick="openPaymentDetailsModal(<?= $debt->id ?>)" class="text-xs font-semibold px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-sm">
                                            How to pay?
                                        </button>
                                        
                                        <!-- Upload Receipt Button -->
                                        <?php if (!$proof): ?>
                                        <button type="button" onclick="document.getElementById('uploadProofModal_<?= $debt->id ?>').classList.remove('hidden')" class="text-xs font-semibold px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition shadow-sm">
                                            📎 Upload Receipt
                                        </button>
                                        <?php else: ?>
                                            <a href="<?= h($proof->proof_url) ?>" target="_blank" class="text-xs font-semibold px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded text-center hover:bg-slate-200 dark:hover:bg-slate-600 transition">View Receipt</a>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- What I am Owed (Credits) -->
                        <?php if (!empty($myCredits)): ?>
                        <div>
                            <h3 class="text-sm font-bold text-green-600 dark:text-green-400 mb-3 uppercase tracking-wide">Owed to me</h3>
                            <div class="space-y-3">
                                <?php foreach ($myCredits as $credit): ?>
                                <?php 
                                    $proof = array_filter($paymentProofs, fn($p) => $p->settlement_transaction_id === $credit->id);
                                    $proof = reset($proof);
                                ?>
                                <div class="bg-green-50/50 dark:bg-green-900/20 rounded-lg p-4 border border-green-100 dark:border-green-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors">
                                    <div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400">From <span class="font-bold text-slate-800 dark:text-slate-200"><?= h($credit->debtor->user->name ?? $credit->debtor->guest_name) ?></span></div>
                                        <div class="font-bold text-lg text-green-600 dark:text-green-400">RM <?= number_format($credit->amount, 2) ?></div>
                                        
                                        <?php if ($credit->status === 'pending'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase">Pending</span>
                                        <?php elseif ($credit->status === 'claimed'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase">They Claimed Paid</span>
                                        <?php elseif ($credit->status === 'settled'): ?>
                                            <span class="inline-block mt-1 text-[10px] font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded uppercase">Settled ✓</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($credit->status !== 'settled'): ?>
                                    <div class="flex flex-col gap-2 shrink-0">
                                        <?php if ($proof): ?>
                                            <a href="<?= h($proof->proof_url) ?>" target="_blank" class="text-xs font-semibold px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded text-center hover:bg-slate-50 dark:hover:bg-slate-600 transition shadow-sm">View Receipt</a>
                                        <?php endif; ?>
                                        
                                        <!-- Confirm Button -->
                                        <?= $this->Form->postLink(
                                            'Mark as Received ✓',
                                            ['controller' => 'PaymentProofs', 'action' => 'confirmSettlement', $credit->id],
                                            [
                                                'class' => 'text-xs font-semibold px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition shadow-sm text-center',
                                                'confirm' => 'Are you sure you have received this payment?'
                                            ]
                                        ) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
