<?php
/**
 * This example shows all "current" violent activities in the world, as reported by GDELT.
 *
 * example code from https://www.patrick-wied.at/static/heatmapjs/example-heatmap-googlemaps.html
 *
 * @author Patrick van Bergen
 */

use Google\Cloud\BigQuery\BigQueryClient;

// show errors while testing
ini_set('display_errors', 1);

// setup Composer autoloading
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/constants.php';

// We need to use a cache for the query results; this is a simple technique to create a 1-day cache
$cachedResultsFile = sys_get_temp_dir() . "/" . date('Y-m-d') . '.json';

if (!file_exists($cachedResultsFile)) {

    $from = (new DateTimeImmutable("5 days ago"))->format("Ymd");
    $to = (new DateTimeImmutable("now"))->format("Ymd");

    // select the location of all events with codes indicating aggression
    // reported in the last 3 days
    $sql = "
        SELECT AVG(ActionGeo_Lat) as lat, AVG(ActionGeo_Long) as lng, COUNT(*) as count, MAX(SOURCEURL) as source
        FROM [gdelt-bq:gdeltv2.events] 
        WHERE SqlDate > {$from} AND SqlDate < {$to}
        AND EventCode in ('190', '191','192', '193', '194', '195', '1951', '1952', '196')
        AND IsRootEvent = 1
        AND ActionGeo_Lat IS NOT NULL AND ActionGeo_Long IS NOT NULL
        GROUP BY ActionGeo_Lat, ActionGeo_Long            
    ";

    $bigQuery = new BigQueryClient([
        // replace this path with a path to your Google Cloud account key
        'keyFilePath' => Constants::ACCOUNT_KEY_FILE,
        // note: no projectId!
    ]);

    // Run a query and inspect the results.
    $results = $bigQuery->runQuery($sql, array('timeoutMs' => 30000, 'useQueryCache' => true));
    $info = $results->info();

    // collect locations
    $locations = array();
    foreach ($results->rows() as $row) {
        $locations[] = array(
            'lat' => $row['lat'],
            'lng' => $row['lng'],
            'count' => $row['count'],
            'source' => $row['source'],
        );
    }

    $data = array(
        'data' => $locations
    );

    $testDataJson = json_encode($data);
    file_put_contents($cachedResultsFile, $testDataJson);

} else {
    $testDataJson = file_get_contents($cachedResultsFile);
}

$mapsApiKey = Constants::MAPS_API_KEY;

//echo $testDataJson;exit;

?>
<html>
    <head>
        <!-- Google Maps -->
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $mapsApiKey ?>"></script>
        <!-- Heatmap.js -->
        <script type="text/javascript" src="js/heatmap.js"></script>
        <script type="text/javascript" src="js/heatmap-gmaps.js"></script>
    </head>
    <body>
        <div id="map-canvas" style="height: 900px">
            <!-- Here the map will be rendered -->
        </div>
        <!-- initialization -->
        <script>
            var dataSet = <?= $testDataJson ?>;
            var myLatlng = new google.maps.LatLng(25.6586, 10.3568);
            var markers = [];
            var MARKER_ZOOM_LEVEL = 6;
            var ZOOM_LEVEL = 3;

            // map options,
            var myOptions = {
                zoom: ZOOM_LEVEL,
                center: myLatlng
            };

            // standard map
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
            // heatmap layer
            heatmap = new HeatmapOverlay(map,
                {
                    // radius should be small ONLY if scaleRadius is true (or small radius is intended)
                    "radius": 2,
                    "maxOpacity": 1,
                    // scales the radius based on map zoom
                    "scaleRadius": true,
                    // if set to false the heatmap uses the global maximum for colorization
                    // if activated: uses the data maximum within the current map boundaries
                    //   (there will always be a red spot with useLocalExtremas true)
                    "useLocalExtrema": true,
                    // which field name in your data represents the latitude - default "lat"
                    latField: 'lat',
                    // which field name in your data represents the longitude - default "lng"
                    lngField: 'lng',
                    // which field name in your data represents the data value - default "value"
                    valueField: 'count'
                }
            );

            heatmap.setData(dataSet);

            // add clickable link markers (hide initially)
            for (var i = 0; i < dataSet['data'].length; i++) {
                var row = dataSet['data'][i];
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(row['lat'], row['lng']),
                    url: row['source'],
                    title: '[' + row['count'] + 'x] ' + row['source'],
                    map: map,
                    visible: false
                });
                google.maps.event.addListener(marker, 'click', function() {
                    window.open(this.url);
                });

                markers.push(marker);
            }

            // show markers at the right zoom level
            google.maps.event.addListener(map, 'zoom_changed', function() {
                var zoom = map.getZoom();

                // iterate over markers and call setVisible
                for (var i = 0; i < markers.length; i++) {
                    markers[i].setVisible(zoom > MARKER_ZOOM_LEVEL);
                }
            });

        </script>
    </body>
</html>
