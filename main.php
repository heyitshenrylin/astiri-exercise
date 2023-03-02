<?php
include('StatisticRequest.php');

$request = new StatisticRequest(10);
for ($i = 0; $i <= 1000; $i++) {
    $request->process("uri1");
    $request->process("uri2");
}

$means = $request->getMean();
echo "\n\nURI\tMean Time";
foreach ( $means as $key => $val ) {
    echo "\n$key\t$val";
}

$stddev = $request->getSD();
echo "\n\nURI\tStandard Deviation";
foreach ( $stddev as $key => $val ) {
    echo "\n$key\t$val";
}

$histogram = $request->getHistogram();

foreach ( $histogram as $uri => $hist) {
    echo "\n URI: $uri \n";
    foreach ( $hist as $label => $count) {
        echo "$label \t $count \n";
    }
}
