<?php
/**
 * GDelt example: list the tables of dataset 'gdeltv2' of project 'gdelt-bq'
 *
 * @author Patrick van Bergen
 */

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;

// show errors while testing
ini_set('display_errors', 1);

// setup Composer autoloading
require_once __DIR__ . '/../vendor/autoload.php';

$bigQuery = new BigQueryClient([
    // replace this path with a path to your Google Cloud account key
    'keyFilePath' => __DIR__ . '/../GDelt example-1032d7c1cbf3.json',
    'projectId' => 'gdelt-bq'
]);

/** @var  Dataset[] $result */
$result = $bigQuery->datasets();

$names = array();
foreach ($result as $dataset) {
    $names[] = $dataset->id();
}

print_r($names);