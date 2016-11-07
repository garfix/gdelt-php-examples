<?php
/**
 * GDELT example: running the GDELT sample query
 *
 * @author Patrick van Bergen
 */

use Google\Cloud\BigQuery\BigQueryClient;

// show errors while testing
ini_set('display_errors', 1);

// setup Composer autoloading
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/constants.php';

$sql = "SELECT theme, COUNT(*) as count
    FROM (
        select SPLIT(V2Themes,';') theme
        from [gdelt-bq:gdeltv2.gkg]
        where DATE>20150302000000 and DATE < 20150304000000 and AllNames like '%Netanyahu%' and TranslationInfo like '%srclc:heb%'
    )
    group by theme
    ORDER BY 2 DESC
    LIMIT 300
";

$bigQuery = new BigQueryClient([
    // replace this path with a path to your Google Cloud account key
    'keyFilePath' => Constants::ACCOUNT_KEY_FILE,
    // note: no projectId!
]);

// Run a query and inspect the results.
$results = $bigQuery->runQuery($sql);
$info = $results->info();

$tb = ($info['totalBytesProcessed'] / Constants::BYTES_PER_TEBIBYTE);
$cost = $tb * Constants::DOLLARS_PER_TEBIBYTE;

// result rows
foreach ($results->rows() as $row) {
    print_r($row);
}

print_r("Total bytes processed: " . $info['totalBytesProcessed']);
print_r("Costs: " . sprintf("$%0.2f", $cost));
print_r("Cache hit: " . $info['cacheHit']);
print_r("Job complete: " . $info['jobComplete']);
