<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var mixed $identity Authenticated user or null (guest)
 * @var string|null $participantName Set after successful guest join
 */
$this->assign('title', 'Join Session - BahagiJe');

$presetLabels = [
    'dining'    => '🍽️ Food & Dining',
    'road_trip' => '🚗 Road Trip',
    'long_trip' => '✈️ Long Trip',
    'custom'    => '⚙️ Custom',
];
?>

<div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-900 py-12 px-4 transition-colors">
    <div class="max-w-md w-full">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-4xl mb-2">🤝</div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100">Join Session</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">You've been invited to split expenses</p>
        </div>

        <?php if (isset($participantName)): ?>
        <!-- Guest Join Success -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-100 dark:border-slate-700 p-8 text-center space-y-4 transition-colors">
            <div class="text-5xl">✅</div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">You're in, <?= h($participantName) ?>!</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">You've joined <strong><?= h($session->name) ?></strong> as a guest.<br>The host will share the expense breakdown with you.</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-4">Want to track your history? <a href="<?= $this->Url->build(['controller' => 'Auth', 'action' => 'login']) ?>" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Sign in with Google</a></p>
        </div>

        <?php else: ?>
        <!-- Session Info Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-100 dark:border-slate-700 p-6 mb-5 transition-colors">
            <div class="flex items-start gap-4">
                <div class="text-3xl">
                    <?= ['dining' => '🍽️', 'road_trip' => '🚗', 'long_trip' => '✈️', 'custom' => '⚙️'][$session->preset_type] ?? '📋' ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 truncate"><?= h($session->name) ?></h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?= $presetLabels[$session->preset_type] ?? $session->preset_type ?></p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs text-slate-400 dark:text-slate-500">
                            👥 <?= count($session->participants) ?> / <?= $session->max_participants ?> joined
                        </span>
                        <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-semibold px-2 py-0.5 rounded-full">
                            <?= ucfirst($session->status) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join Form -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md border border-slate-100 dark:border-slate-700 p-6 space-y-5 transition-colors">

            <?php if ($identity): ?>
            <!-- Registered user: auto-join on POST -->
            <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800/50 transition-colors">
                <?php if ($identity->avatar_url): ?>
                    <img src="<?= h($identity->avatar_url) ?>" class="h-10 w-10 rounded-full border border-blue-200 dark:border-blue-700">
                <?php else: ?>
                    <div class="h-10 w-10 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-700 dark:text-blue-300 font-bold flex items-center justify-center">
                        <?= strtoupper(substr($identity->name, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= h($identity->name) ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= h($identity->email) ?></div>
                </div>
            </div>

            <?= $this->Form->create(null, [
                'url'    => ['action' => 'join', $session->uuid],
                'method' => 'post',
            ]) ?>
            <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white font-semibold text-sm rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 transition">
                Join as <?= h($identity->name) ?> →
            </button>
            <?= $this->Form->end() ?>

            <?php else: ?>
            <!-- Guest: name input required -->
            <div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">Join as Guest</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">No account needed — just enter your name.</p>

                <?= $this->Form->create(null, [
                    'url'    => ['action' => 'join', $session->uuid],
                    'method' => 'post',
                ]) ?>
                <div class="mb-4">
                    <label for="guest-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Your Name <span class="text-red-500">*</span></label>
                    <input type="text" id="guest-name" name="guest_name" required
                           placeholder="e.g. Ahmad, Sarah"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50 outline-none text-sm transition placeholder-slate-400 dark:placeholder-slate-500">
                </div>
                <button type="submit"
                        class="w-full py-3 bg-blue-600 text-white font-semibold text-sm rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/50 transition">
                    Join Session →
                </button>
                <?= $this->Form->end() ?>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-4 text-center">
                <p class="text-xs text-slate-400 dark:text-slate-500">Have a Google account?
                    <a href="<?= $this->Url->build(['controller' => 'Auth', 'action' => 'login']) ?>"
                       class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Sign in</a> to track your session history.
                </p>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </div>
</div>
