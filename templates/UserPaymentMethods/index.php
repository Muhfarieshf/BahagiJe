<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface $methods
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'My Payment Details - BahagiJe');

$methodIcons = [
    'bank_transfer' => '🏦',
    'duitnow_qr'    => '📱',
    'duitnow_id'    => '📲',
    'tng'           => '💳',
    'paypal'        => '🌐',
];
$methodLabels = [
    'bank_transfer' => 'Bank Transfer',
    'duitnow_qr'    => 'DuitNow QR',
    'duitnow_id'    => 'DuitNow ID',
    'tng'           => "Touch 'n Go",
    'paypal'        => 'PayPal',
];
?>

<div class="max-w-2xl mx-auto mt-8 px-4 space-y-6">

    <!-- Back -->
    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>"
       class="inline-flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Dashboard
    </a>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">💳 My Payment Details</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                These are shown to others when they need to pay you in a session.
            </p>
        </div>
        <?php if (count($methods) < 5): ?>
            <a href="<?= $this->Url->build(['action' => 'add']) ?>"
               class="w-full sm:w-auto text-center justify-center inline-flex items-center gap-2 px-4 py-3 sm:py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Method
            </a>
        <?php endif; ?>
    </div>

    <!-- Flash Messages -->
    <?= $this->Flash->render() ?>

    <!-- Method List -->
    <?php if ($methods->isEmpty()): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center transition-colors">
            <div class="text-4xl mb-3">💳</div>
            <h3 class="font-semibold text-slate-700 dark:text-slate-200 mb-1">No payment methods saved yet</h3>
            <p class="text-slate-400 dark:text-slate-500 text-sm mb-5">Add one so others in a session know how to pay you back.</p>
            <a href="<?= $this->Url->build(['action' => 'add']) ?>"
               class="inline-flex items-center gap-2 px-5 py-3 sm:py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition shadow-sm w-full sm:w-auto justify-center">
                + Add Your First Method
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($methods as $m): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 flex items-center justify-between gap-4 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="text-2xl w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-100 dark:border-slate-600 shrink-0">
                            <?= $methodIcons[$m->method_type] ?? '💳' ?>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200 text-sm">
                                <?= h($m->label ?: $methodLabels[$m->method_type]) ?>
                            </div>
                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                <?= $methodLabels[$m->method_type] ?>
                                <?php if ($m->bank_name): ?>
                                    · <?= h($m->bank_name) ?>
                                <?php endif; ?>
                                <?php if ($m->account_value): ?>
                                    · <?= h($m->account_value) ?>
                                <?php endif; ?>
                                <?php if ($m->method_type === 'duitnow_qr'): ?>
                                    · QR saved ✓
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="<?= $this->Url->build(['action' => 'edit', $m->id]) ?>"
                           class="text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Edit
                        </a>
                        <?= $this->Form->postLink(
                            '🗑',
                            ['action' => 'delete', $m->id],
                            [
                                'confirm' => 'Delete this payment method?' . ($m->method_type === 'duitnow_qr' ? ' Your saved QR will also be removed from Cloudinary.' : ''),
                                'class'   => 'text-sm px-2.5 py-1.5 rounded-lg border border-red-100 dark:border-red-900/50 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition',
                            ]
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($methods) >= 5): ?>
            <p class="text-xs text-slate-400 dark:text-slate-500 text-center">You have reached the maximum of 5 payment methods.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>
