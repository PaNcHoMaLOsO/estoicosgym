<?php
/**
 * IDE Helper Helper Script
 * 
 * This file provides a simple way to regenerate IDE helper files
 * Run from command line: php helpers/ide_helper.php
 */

echo "🔄 Regenerating Laravel IDE Helper files...\n";

// Run IDE helper generation commands
$commands = [
    'php artisan ide-helper:generate',
    'php artisan ide-helper:models --nowrite',
    'php artisan ide-helper:eloquent',
];

foreach ($commands as $command) {
    echo "\n▶️  Running: {$command}\n";
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "❌ Error running command: {$command}\n";
        print_r($output);
    } else {
        echo "✅ Success!\n";
        if (!empty($output)) {
            foreach ($output as $line) {
                echo "   {$line}\n";
            }
        }
    }
}

echo "\n";
echo "=====================================\n";
echo "✨ IDE Helper files regenerated!\n";
echo "=====================================\n";
echo "\nNow restart VS Code to apply the changes.\n";
echo "\nGenerated files:\n";
echo "  - _ide_helper.php (Facades and helper functions)\n";
echo "  - _ide_helper_models.php (Eloquent model methods)\n";
echo "  - _ide_helper_functions.php (Custom helper functions)\n";
echo "  - phpstan.neon (Static analysis configuration)\n";
echo "  - larastan.neon (Larastan configuration)\n";
echo "  - .phpstorm.meta.php (PhpStorm meta information)\n";
echo "  - .vscode/settings.json (VS Code settings)\n";
?>
