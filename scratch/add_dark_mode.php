<?php
$file = 'c:/laragon/www/EquiSplit/templates/GroupSessions/view.php';
$content = file_get_contents($file);

$replacements = [
    // Backgrounds and Panels
    'bg-white' => 'bg-white dark:bg-slate-800',
    'bg-slate-50' => 'bg-slate-50 dark:bg-slate-800/50',
    'bg-slate-100' => 'bg-slate-100 dark:bg-slate-700',
    'bg-slate-200' => 'bg-slate-200 dark:bg-slate-600',
    
    // Text colors
    'text-slate-800' => 'text-slate-800 dark:text-slate-100',
    'text-slate-700' => 'text-slate-700 dark:text-slate-200',
    'text-slate-600' => 'text-slate-600 dark:text-slate-300',
    'text-slate-500' => 'text-slate-500 dark:text-slate-400',
    'text-slate-400' => 'text-slate-400 dark:text-slate-500',
    
    // Border colors
    'border-slate-100' => 'border-slate-100 dark:border-slate-700',
    'border-slate-200' => 'border-slate-200 dark:border-slate-700',
    'border-slate-300' => 'border-slate-300 dark:border-slate-600',
    
    // Hover states
    'hover:bg-slate-50' => 'hover:bg-slate-50 dark:hover:bg-slate-700/50',
    'hover:bg-slate-100' => 'hover:bg-slate-100 dark:hover:bg-slate-700',
    'hover:bg-slate-200' => 'hover:bg-slate-200 dark:hover:bg-slate-600',
    'hover:text-slate-800' => 'hover:text-slate-800 dark:hover:text-slate-200',
    
    // Modals
    'bg-black/50' => 'bg-black/50 dark:bg-black/70',
    
    // Specific colors (green, red, amber, blue)
    'text-green-700' => 'text-green-700 dark:text-green-400',
    'bg-green-100' => 'bg-green-100 dark:bg-green-900/30',
    
    'text-red-700' => 'text-red-700 dark:text-red-500',
    'text-red-600' => 'text-red-600 dark:text-red-400',
    'bg-red-100' => 'bg-red-100 dark:bg-red-900/30',
    'border-red-200' => 'border-red-200 dark:border-red-800',
    
    'text-amber-800' => 'text-amber-800 dark:text-amber-200',
    'text-amber-700' => 'text-amber-700 dark:text-amber-300',
    'text-amber-600' => 'text-amber-600 dark:text-amber-400',
    'bg-amber-100' => 'bg-amber-100 dark:bg-amber-900/30',
    'bg-amber-50' => 'bg-amber-50 dark:bg-amber-900/20',
    'border-amber-200' => 'border-amber-200 dark:border-amber-800',
    
    'text-blue-700' => 'text-blue-700 dark:text-blue-400',
    'text-blue-600' => 'text-blue-600 dark:text-blue-400',
    'bg-blue-100' => 'bg-blue-100 dark:bg-blue-900/30',
    'bg-blue-50' => 'bg-blue-50 dark:bg-blue-900/20',
    'border-blue-100' => 'border-blue-100 dark:border-blue-800',
    'border-blue-200' => 'border-blue-200 dark:border-blue-800',
    
    // Add text inputs style
    'bg-white rounded-lg border border-slate-300' => 'bg-white dark:bg-slate-700 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white',
];

foreach ($replacements as $light => $dark) {
    // Only replace if not already replaced (negative lookahead for dark:)
    $pattern = '/' . preg_quote($light, '/') . '(?!\s*dark:)/';
    $content = preg_replace($pattern, $dark, $content);
}

// Write back
file_put_contents($file, $content);
echo "Updated view.php with dark mode classes.\n";
?>
