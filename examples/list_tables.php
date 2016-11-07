<?php
/**
 * GDELT example: list the datasets of project 'gdelt-bq'
 *
 * @author Patrick van Bergen
 */

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Table;

// show errors while testing
ini_set('display_errors', 1);

// setup Composer autoloading
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/constants.php';

$bigQuery = new BigQueryClient([
    // replace this path with a path to your Google Cloud account key
    'keyFilePath' => Constants::ACCOUNT_KEY_FILE,
    'projectId' => 'gdelt-bq'
]);

/** @var  Table[] $tables */
$tables = $bigQuery->dataset('full')->tables();

$names = array();
foreach ($tables as $table) {
    $names[] = $table->id();
}

print_r($names);
