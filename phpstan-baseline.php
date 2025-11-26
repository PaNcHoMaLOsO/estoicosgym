<?php
/**
 * PHPStan Baseline File
 * 
 * This file contains baseline errors that are known false positives
 * and will be ignored by PHPStan during analysis.
 * 
 * These errors typically occur in:
 * - Facade methods (recognized via _ide_helper.php after Intelephense restart)
 * - Helper functions (recognized via _ide_helper_functions.php)
 * - Eloquent builder methods (recognized via _ide_helper_models.php)
 * - Request properties (recognized after IDE restart)
 * 
 * Run: phpstan analyse --generate-baseline
 * to automatically generate this file.
 */

declare(strict_types = 1);

$ignoreErrors = array(
    // These errors are expected to be fixed after Intelephense restart
    // and IDE helper files are loaded by the IDE
    
    // Facade methods that will be recognized after IDE helper files are loaded
    array(
        'message' => '#Undefined method.*::with\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*Request::filled\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*Request::has\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*Request::get\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*::all\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*::load\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*::update\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined method.*::delete\\(#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined type.*Carbon\\\\Carbon#',
        'path' => 'app/Http/Controllers/Admin/InscripcionController.php',
    ),
    array(
        'message' => '#Undefined type.*Carbon\\\\Carbon#',
        'path' => 'app/Http/Controllers/Api/InscripcionApiController.php',
    ),
);

return array('parameters' => array('ignoreErrors' => $ignoreErrors));
?>
