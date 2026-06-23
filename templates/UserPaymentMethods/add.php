<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserPaymentMethod $method
 */
$this->assign('title', 'Add Payment Method - BahagiJe');
$isEdit = !$method->isNew();
?>

<div class="max-w-lg mx-auto mt-8 px-4 space-y-6">

    <!-- Back -->
    <a href="<?= $this->Url->build(['action' => 'index']) ?>"
       class="inline-flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Payment Details
    </a>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-5 transition-colors">

        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
            <?= $isEdit ? '✏️ Edit Payment Method' : '➕ Add Payment Method' ?>
        </h1>

        <?= $this->Flash->render() ?>

        <?= $this->Form->create($method, [
            'enctype' => 'multipart/form-data',
            'url'     => $isEdit ? ['action' => 'edit', $method->id] : ['action' => 'add'],
            'class'   => 'space-y-5',
        ]) ?>

        <!-- Method Type (hidden for edit, shown for add) -->
        <?php if (!$isEdit): ?>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Payment Method Type</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" id="method-type-grid">
                    <?php
                    $types = [
                        'bank_transfer' => ['icon' => '🏦', 'label' => 'Bank Transfer'],
                        'duitnow_qr'    => ['icon' => '📱', 'label' => 'DuitNow QR'],
                        'duitnow_id'    => ['icon' => '📲', 'label' => 'DuitNow ID'],
                        'tng'           => ['icon' => '💳', "label" => "Touch 'n Go"],
                        'paypal'        => ['icon' => '🌐', 'label' => 'PayPal'],
                    ];
                    foreach ($types as $value => $info):
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="method_type" value="<?= $value ?>"
                                   class="sr-only peer"
                                   <?= ($method->method_type === $value) ? 'checked' : '' ?>
                                   onchange="switchMethodType('<?= $value ?>')">
                            <div class="flex flex-col items-center gap-1 p-3 rounded-lg border-2 border-slate-100 dark:border-slate-700 text-slate-500 dark:text-slate-400
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 dark:peer-checked:text-blue-300
                                        hover:border-slate-200 dark:hover:border-slate-600 transition text-center h-full">
                                <span class="text-2xl"><?= $info['icon'] ?></span>
                                <span class="text-xs font-semibold"><?= $info['label'] ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if ($method->hasErrors()): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $this->Form->error('method_type') ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?= $this->Form->hidden('method_type', ['value' => $method->method_type]) ?>
        <?php endif; ?>

        <!-- Label -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Label <span class="text-slate-400 font-normal">(optional)</span></label>
            <?= $this->Form->text('label', [
                'placeholder' => 'e.g. My Maybank, Work Account',
                'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
            ]) ?>
        </div>

        <!-- Bank Transfer Fields -->
        <div id="fields-bank_transfer" class="space-y-4 method-fields hidden">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                <?= $this->Form->select('bank_name', [
                    'Maybank' => 'Maybank', 'CIMB' => 'CIMB', 'Public Bank' => 'Public Bank',
                    'RHB' => 'RHB', 'Hong Leong' => 'Hong Leong', 'AmBank' => 'AmBank',
                    'Bank Islam' => 'Bank Islam', 'Bank Rakyat' => 'Bank Rakyat',
                    'BSN' => 'BSN', 'OCBC' => 'OCBC', 'HSBC' => 'HSBC', 'Other' => 'Other',
                ], [
                    'empty'   => '-- Select Bank --',
                    'class'   => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Holder Name</label>
                <?= $this->Form->text('account_name', [
                    'placeholder' => 'Full name as on bank account',
                    'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Account Number</label>
                <?= $this->Form->text('account_value', [
                    'placeholder' => 'e.g. 1234567890',
                    'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
        </div>

        <!-- DuitNow QR Fields -->
        <div id="fields-duitnow_qr" class="space-y-4 method-fields hidden">
            <!-- PDPA Consent Notice -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-xs text-amber-700 dark:text-amber-400 transition-colors">
                <strong>⚠️ Data Storage Notice:</strong> Your QR code image will be stored on Cloudinary servers (USA-based). 
                It will be stored as a private file not accessible to the public. 
                By uploading, you consent to this cross-border transfer. You can delete it anytime.
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">QR Code Image</label>
                <input type="file" name="qr_image" accept="image/*"
                       class="w-full text-sm text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2
                              file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                              file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400
                              hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 focus:outline-none">
                <?php if ($isEdit && $method->qr_image_url): ?>
                    <p class="text-xs text-slate-400 mt-1">A QR code is already saved. Upload a new one to replace it.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- DuitNow ID / TNG Fields -->
        <div id="fields-duitnow_id" class="space-y-4 method-fields hidden">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number / IC Number</label>
                <?= $this->Form->text('account_value', [
                    'placeholder' => 'e.g. 0123456789',
                    'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
        </div>
        <div id="fields-tng" class="space-y-4 method-fields hidden">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Registered Phone Number</label>
                <?= $this->Form->text('account_value', [
                    'placeholder' => 'e.g. 0123456789',
                    'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
        </div>

        <!-- PayPal Fields -->
        <div id="fields-paypal" class="space-y-4 method-fields hidden">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">PayPal Email Address</label>
                <?= $this->Form->email('account_value', [
                    'placeholder' => 'e.g. user@example.com',
                    'class'       => 'w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-3 sm:py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>
        </div>

        <!-- Errors -->
        <?php if ($method->hasErrors()): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 rounded-lg p-3 text-xs text-red-700 dark:text-red-400">
                <?php foreach ($method->getErrors() as $field => $errors): ?>
                    <?php foreach ($errors as $msg): ?>
                        <p>• <?= h($msg) ?></p>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Submit -->
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
            <a href="<?= $this->Url->build(['action' => 'index']) ?>"
               class="w-full sm:w-auto text-center px-5 py-3 sm:py-2.5 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                Cancel
            </a>
            <?= $this->Form->submit($isEdit ? 'Save Changes' : 'Save Method', [
                'class' => 'w-full sm:w-auto text-center justify-center px-5 py-3 sm:py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition',
            ]) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<script>
function switchMethodType(type) {
    // Hide all sections and disable their inputs
    document.querySelectorAll('.method-fields').forEach(el => {
        el.classList.add('hidden');
        el.querySelectorAll('input, select').forEach(input => input.disabled = true);
    });

    // Show the target section and enable its inputs
    const target = document.getElementById('fields-' + type);
    if (target) {
        target.classList.remove('hidden');
        target.querySelectorAll('input, select').forEach(input => input.disabled = false);
    }
}

// On load: show the right fields if already selected (edit mode or pre-selected)
document.addEventListener('DOMContentLoaded', function () {
    const checked = document.querySelector('input[name="method_type"]:checked');
    if (checked) switchMethodType(checked.value);

    <?php if ($isEdit): ?>
    switchMethodType('<?= $method->method_type ?>');
    <?php endif; ?>
});
</script>
