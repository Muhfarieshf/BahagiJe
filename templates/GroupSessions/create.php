<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var array $presets
 */
$this->assign('title', 'Create Session - BahagiJe');
?>

<div class="max-w-2xl mx-auto mt-8 px-4">
    <div class="mb-4">
        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>" class="inline-flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Cancel
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Create New Session</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Set up a group expense session and invite participants via QR code.</p>
    </div>

    <?= $this->Form->create($session, [
        'url'  => ['controller' => 'GroupSessions', 'action' => 'create'],
        'id'   => 'create-session-form',
        'class' => 'space-y-6'
    ]) ?>

    <!-- Session Name -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 space-y-5 transition-colors">
        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-700 pb-3">Session Details</h2>

        <div>
            <label for="session-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Session Name <span class="text-red-500">*</span></label>
            <input type="text" id="session-name" name="name" required
                   placeholder="e.g. Dinner at Fatty Crab, KL Trip 2026"
                   value="<?= h($session->name ?? '') ?>"
                   class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm transition">
        </div>

        <div>
            <label for="max-participants" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Max Participants <span class="text-red-500">*</span></label>
            <input type="number" id="max-participants" name="max_participants" required min="2" max="50"
                   value="<?= h($session->max_participants ?? 10) ?>"
                   class="w-full px-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm transition">
            <p class="text-xs text-slate-400 mt-1">Between 2 and 50 participants.</p>
        </div>
    </div>

    <!-- Preset Selector -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 space-y-4 transition-colors">
        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-700 pb-3">Session Preset</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Choose a preset to configure the calculation logic for your session.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="preset-cards">

            <?php
            $presetMeta = [
                'dining'    => ['icon' => '🍽️', 'label' => 'Food & Dining',   'desc' => 'SST% + Service Charge, proportional split'],
                'grocery'   => ['icon' => '🛒', 'label' => 'Grocery Run',      'desc' => 'Itemized receipt entry, 0% service charge'],
                'road_trip' => ['icon' => '🚗', 'label' => 'Road Trip',        'desc' => 'Flat amounts, equal split between all'],
                'long_trip' => ['icon' => '✈️', 'label' => 'Long Trip',        'desc' => 'Collaborative ledger, net-debt settlement'],
                'custom'    => ['icon' => '⚙️', 'label' => 'Custom',           'desc' => 'Manually configure all rules'],
            ];
            foreach ($presetMeta as $value => $meta):
            ?>
            <label class="preset-card cursor-pointer rounded-xl border-2 border-slate-200 dark:border-slate-700 p-4 flex items-start gap-3 hover:border-blue-400 dark:hover:border-blue-500 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/30">
                <input type="radio" name="preset_type" value="<?= $value ?>" class="mt-1 accent-blue-600 hidden" onchange="updateChargePanel(this.value)" <?= ($session->preset_type ?? '') === $value ? 'checked' : '' ?>>
                <div class="text-2xl"><?= $meta['icon'] ?></div>
                <div>
                    <div class="font-semibold text-sm text-slate-800 dark:text-slate-200"><?= $meta['label'] ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $meta['desc'] ?></div>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Dynamic Charge Panel (shown for dining and custom) -->
    <div id="charge-panel" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 space-y-4 hidden transition-colors">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200">Session Charges</h2>
            <button type="button" id="add-charge-btn"
                    onclick="addCharge()"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition">
                + Add Charge
            </button>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400" id="charge-hint">Add SST or service charges that apply to the entire session.</p>
        <div id="charges-container" class="space-y-3"></div>
    </div>

    <!-- Submit -->
    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
        <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']) ?>"
           class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
            ← Back to Dashboard
        </a>
        <button type="submit"
                class="w-full sm:w-auto text-center justify-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition shadow-sm">
            Create Session & Get QR Code →
        </button>
    </div>

    <?= $this->Form->end() ?>
</div>

<script>
let chargeIndex = 0;

function updateChargePanel(preset) {
    const panel = document.getElementById('charge-panel');
    const hint  = document.getElementById('charge-hint');

    // Mark the selected card visually
    document.querySelectorAll('.preset-card input[type=radio]').forEach(r => {
        r.checked = (r.value === preset);
    });

    if (preset === 'dining') {
        panel.classList.remove('hidden');
        hint.textContent = 'Add SST (6%) and Service Charge (10%) for this dining session.';
        if (document.getElementById('charges-container').children.length === 0) {
            addCharge('SST', 'percentage', '6', 'proportional');
            addCharge('Service Charge', 'percentage', '10', 'proportional');
        }
    } else if (preset === 'custom') {
        panel.classList.remove('hidden');
        hint.textContent = 'Manually add any charges that apply to this session.';
    } else {
        panel.classList.add('hidden');
        document.getElementById('charges-container').innerHTML = '';
        chargeIndex = 0;
    }
}

function addCharge(name = '', type = 'percentage', value = '', appliesTo = 'proportional') {
    const container = document.getElementById('charges-container');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-12 gap-2 items-end bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3';
    div.innerHTML = `
        <div class="col-span-4">
            <label class="text-xs text-slate-500 dark:text-slate-400 mb-1 block">Charge Name</label>
            <input type="text" name="charges[${chargeIndex}][charge_name]" value="${name}"
                   placeholder="e.g. SST" required
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:border-blue-400 outline-none">
        </div>
        <div class="col-span-3">
            <label class="text-xs text-slate-500 dark:text-slate-400 mb-1 block">Type</label>
            <select name="charges[${chargeIndex}][charge_type]" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm outline-none">
                <option value="percentage" ${type === 'percentage' ? 'selected' : ''}>Percentage (%)</option>
                <option value="flat"       ${type === 'flat'       ? 'selected' : ''}>Flat (RM)</option>
            </select>
        </div>
        <div class="col-span-2">
            <label class="text-xs text-slate-500 dark:text-slate-400 mb-1 block">Value</label>
            <input type="number" name="charges[${chargeIndex}][charge_value]" value="${value}" step="0.01" min="0" required
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm outline-none">
        </div>
        <div class="col-span-2">
            <label class="text-xs text-slate-500 dark:text-slate-400 mb-1 block">Applies To</label>
            <select name="charges[${chargeIndex}][applies_to]" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm outline-none">
                <option value="proportional" ${appliesTo === 'proportional' ? 'selected' : ''}>Proportional</option>
                <option value="equal"        ${appliesTo === 'equal'        ? 'selected' : ''}>Equal</option>
            </select>
        </div>
        <div class="col-span-1 flex justify-center">
            <button type="button" onclick="this.closest('div.grid').remove()"
                    class="text-red-400 hover:text-red-600 dark:hover:text-red-300 text-lg font-bold leading-none transition">×</button>
        </div>
    `;
    container.appendChild(div);
    chargeIndex++;
}

// Activate the default preset visually on load
document.querySelectorAll('.preset-card input[type=radio]').forEach(r => {
    r.addEventListener('change', () => updateChargePanel(r.value));
    r.closest('label').addEventListener('click', () => {
        r.checked = true;
        updateChargePanel(r.value);
    });
    if (r.checked) updateChargePanel(r.value);
});

// If a preset was pre-selected via query param (?preset=...), trigger the panel
<?php if (!empty($preSelectedPreset)): ?>
document.addEventListener('DOMContentLoaded', () => {
    updateChargePanel('<?= h($preSelectedPreset) ?>');
});
<?php endif; ?>
</script>
