<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\GroupSession $session
 * @var array $waypoints
 * @var array $expenses
 * @var bool $isHost
 */

// Group expenses by waypoint_id
$waypointExpenses = [];
$unassignedExpenses = [];
foreach ($expenses as $exp) {
    if ($exp->waypoint_id) {
        $waypointExpenses[$exp->waypoint_id][] = $exp;
    } else {
        $unassignedExpenses[] = $exp;
    }
}
?>

<div class="space-y-6">
    <!-- Actions Header -->
    <?php if ($isParticipant && $session->status === 'open'): ?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Road Trip Planner</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Add your stops, tolls, and refuels along the way.</p>
        </div>
        <button type="button" onclick="document.getElementById('addWaypointModal').classList.remove('hidden')" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl shadow-md hover:bg-blue-700 hover:shadow-lg transition">
            + Add Stop
        </button>
    </div>
    <?php endif; ?>

    <!-- The Interactive Map -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div id="roadtrip-map" hx-preserve="true" class="w-full h-[300px] sm:h-[400px] z-0"></div>
    </div>

    <!-- The Waypoint Timeline -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">🛣️ Route Timeline</h2>
        </div>

        <?php if (empty($waypoints)): ?>
            <div class="text-center py-8 text-slate-400">
                <p>No stops added to this road trip yet.</p>
                <?php if ($isParticipant): ?>
                    <p class="text-sm mt-2">Click '+ Add Stop' to start building your route.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="relative pl-4 sm:pl-8 border-l-2 border-slate-200 dark:border-slate-700 space-y-8">
                <?php foreach ($waypoints as $index => $wp): ?>
                    <div class="relative">
                        <!-- Node Marker -->
                        <div class="absolute -left-[21px] sm:-left-[37px] top-1 w-4 h-4 rounded-full border-4 border-white dark:border-slate-800 
                            <?= $wp->type === 'start' ? 'bg-green-500' : ($wp->type === 'destination' ? 'bg-red-500' : ($wp->type === 'toll' ? 'bg-amber-500' : 'bg-blue-500')) ?>">
                        </div>

                        <!-- Node Content -->
                        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-lg border border-slate-200 dark:border-slate-600 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mr-2"><?= h($wp->type) ?></span>
                                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 inline-block"><?= h($wp->name) ?></h3>
                                </div>
                                <?php if ($isHost && $session->status === 'open'): ?>
                                    <?= $this->Form->postLink(
                                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                        ['action' => 'deleteWaypoint', $wp->id],
                                        [
                                            'confirm' => 'Delete this stop?',
                                            'escape' => false,
                                            'class' => 'text-slate-400 hover:text-red-500 transition',
                                            'title' => 'Delete Stop'
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>

                            <!-- Expenses for this node -->
                            <div class="space-y-2 mb-3">
                                <?php if (!empty($waypointExpenses[$wp->id])): ?>
                                    <?php foreach ($waypointExpenses[$wp->id] as $exp): ?>
                                        <div class="flex items-center justify-between text-sm p-2 bg-white dark:bg-slate-800 rounded border border-slate-100 dark:border-slate-700">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-slate-700 dark:text-slate-200">
                                                    <?= h($exp->description) ?>
                                                </span>
                                                <span class="text-[10px] text-slate-500">Paid by <?= h($exp->participant->user->name ?? $exp->participant->guest_name) ?></span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="font-bold text-slate-800 dark:text-slate-100">RM <?= number_format($exp->total_amount, 2) ?></span>
                                                <?php if ($isHost && $session->status === 'open'): ?>
                                                    <?= $this->Form->postLink(
                                                        '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                                        ['controller' => 'Expenses', 'action' => 'delete', $exp->id],
                                                        [
                                                            'confirm' => 'Delete expense?',
                                                            'escape' => false,
                                                            'class' => 'text-red-400 hover:text-red-600',
                                                            'title' => 'Delete Expense'
                                                        ]
                                                    ) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-xs text-slate-400 italic">No expenses added here.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Add Expense Button -->
                            <?php if ($session->status === 'open'): ?>
                                <div class="flex items-center gap-4">
                                    <button type="button" onclick="openAddWaypointExpenseModal(<?= $wp->id ?>)" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                        + Single Expense
                                    </button>
                                    <button type="button" onclick="openAddWaypointReceiptModal(<?= $wp->id ?>)" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                        + Itemized Receipt
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Unassigned Expenses (General) -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200">General Expenses (No Location)</h3>
            <?php if ($session->status === 'open'): ?>
                <div class="flex items-center gap-4">
                    <button type="button" onclick="document.getElementById('addExpenseModal').classList.remove('hidden')" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        + Single Expense
                    </button>
                    <button type="button" onclick="document.getElementById('addReceiptModal').classList.remove('hidden')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        + Itemized Receipt
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($unassignedExpenses)): ?>
            <p class="text-sm text-slate-400 italic">No general expenses.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($unassignedExpenses as $exp): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded border border-slate-100 dark:border-slate-600">
                        <div>
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= h($exp->description) ?></div>
                            <div class="text-xs text-slate-500">Paid by <?= h($exp->participant->user->name ?? $exp->participant->guest_name) ?></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-slate-800 dark:text-slate-100">RM <?= number_format($exp->total_amount, 2) ?></span>
                            <?php if ($isHost && $session->status === 'open'): ?>
                                <?= $this->Form->postLink(
                                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                                    ['controller' => 'Expenses', 'action' => 'delete', $exp->id],
                                    [
                                        'confirm' => 'Delete expense?',
                                        'escape' => false,
                                        'class' => 'text-red-400 hover:text-red-600'
                                    ]
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Waypoint Modal -->
<div id="addWaypointModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-4">Add Route Stop</h3>
            <form method="post" action="<?= $this->Url->build(['action' => 'addWaypoint', $session->uuid]) ?>">
                <input type="hidden" name="_csrfToken" value="<?= $this->request->getAttribute('csrfToken') ?>">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                        <?php
                            $hasStart = false;
                            $hasDestination = false;
                            if (!empty($waypoints)) {
                                foreach ($waypoints as $w) {
                                    if ($w->type === 'start') $hasStart = true;
                                    if ($w->type === 'destination') $hasDestination = true;
                                }
                            }
                        ?>
                        <select name="type" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100">
                            <option value="start" <?= $hasStart ? 'disabled class="text-slate-400 bg-slate-50 dark:bg-slate-800"' : '' ?>>Start Point <?= $hasStart ? '(Already Set)' : '' ?></option>
                            <option value="stop" selected>Pit Stop / POI</option>
                            <option value="toll">Toll Plaza</option>
                            <option value="destination" <?= $hasDestination ? 'disabled class="text-slate-400 bg-slate-50 dark:bg-slate-800"' : '' ?>>Destination <?= $hasDestination ? '(Already Set)' : '' ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Location Name</label>
                        <input type="text" name="name" required id="waypoint-search-input" placeholder="e.g. Ipoh R&R or search location..." class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <input type="hidden" name="lat" id="waypoint-lat">
                        <input type="hidden" name="lng" id="waypoint-lng">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addWaypointModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">Add Stop</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddWaypointExpenseModal(waypointId) {
        // Find the standard add expense modal and inject the waypoint ID
        let modal = document.getElementById('addExpenseModal');
        let form = modal.querySelector('form');
        
        // Remove existing waypoint hidden input if any
        let existing = form.querySelector('input[name="waypoint_id"]');
        if (existing) existing.remove();
        
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'waypoint_id';
        input.value = waypointId;
        form.appendChild(input);
        
        // Update title to be clear
        modal.querySelector('h3').innerText = 'Add Expense to Stop';
        
        modal.classList.remove('hidden');
    }

    function openAddWaypointReceiptModal(waypointId) {
        let modal = document.getElementById('addReceiptModal');
        let form = modal.querySelector('form');
        
        let existing = form.querySelector('input[name="waypoint_id"]');
        if (existing) existing.remove();
        
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'waypoint_id';
        input.value = waypointId;
        form.appendChild(input);
        
        modal.querySelector('h3').innerText = 'Add Receipt to Stop';
        
        modal.classList.remove('hidden');
    }
</script>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<!-- Load the waypoints into JS -->
<?php
$waypointData = [];
if (!empty($waypoints)) {
    foreach ($waypoints as $w) {
        $waypointData[] = [
            'id' => $w->id,
            'name' => $w->name,
            'type' => $w->type,
            'lat' => $w->lat,
            'lng' => $w->lng
        ];
    }
}
?>
<script>
    window.SESSION_WAYPOINTS = <?= json_encode($waypointData); ?>;
</script>
<script src="<?= $this->Url->build('/js/roadtrip_map.js') ?>"></script>
