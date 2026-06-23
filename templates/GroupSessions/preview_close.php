<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var array $transactions
 */
$this->assign('title', 'Close Session - BahagiJe');

// Create a quick lookup map for participant names
$participantNames = [];
foreach ($session->participants as $p) {
    $participantNames[$p->id] = h($p->user->name ?? $p->guest_name ?? 'Unknown');
}
?>

<div class="max-w-3xl mx-auto mt-12 px-4 space-y-6">

    <div class="mb-4">
        <?= $this->Form->postLink(
            '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Return to Session & Unlock',
            ['action' => 'unlockSession', $session->uuid],
            [
                'escape' => false,
                'class' => 'inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition'
            ]
        ) ?>
    </div>

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-200">Final Settlement Preview</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2">Here is the optimized net-debt calculation for <strong><?= h($session->name) ?></strong>.</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-8 transition-colors">
        <h2 class="text-xl font-bold text-slate-700 dark:text-slate-200 mb-6 flex items-center gap-2">
            💸 Who owes whom?
        </h2>

        <?php if (empty($transactions)): ?>
            <div class="py-10 text-center">
                <div class="text-4xl mb-3">🎉</div>
                <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-200">All settled up!</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1">No one owes anything, or expenses exactly cancel out.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($transactions as $txn): ?>
                    <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= $participantNames[$txn['debtor_id']] ?? 'Unknown' ?></span>
                                <span class="text-xs text-red-500 font-medium">Pays</span>
                            </div>
                            
                            <div class="text-slate-300 dark:text-slate-500">➔</div>
                            
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= $participantNames[$txn['creditor_id']] ?? 'Unknown' ?></span>
                                <span class="text-xs text-green-600 dark:text-green-400 font-medium">Receives</span>
                            </div>
                        </div>
                        <div class="text-lg font-bold text-slate-800 dark:text-slate-200">
                            RM <?= number_format($txn['amount'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 mb-6 transition-colors">
            <h4 class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-1">⚠️ Warning</h4>
            <p class="text-xs text-amber-700 dark:text-amber-400">Closing the session will lock it permanently. Participants will no longer be able to add new expenses or join the session. The settlements shown above will be finalised.</p>
        </div>

        <div class="flex items-center justify-between mt-6">
            <?= $this->Form->postLink(
                '← Back to Session',
                ['action' => 'unlockSession', $session->uuid],
                [
                    'class' => 'text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition'
                ]
            ) ?>
            
            <?= $this->Form->create(null, ['url' => ['action' => 'close', $session->id]]) ?>
                <button type="submit" class="px-6 py-3 bg-red-600 text-white font-bold rounded-lg shadow-sm hover:bg-red-700 focus:ring-4 focus:ring-red-100 transition">
                    Confirm & Close Session
                </button>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
