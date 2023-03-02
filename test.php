<?php
include('StatisticRequest.php');

$num_buckets = 6;

$request = new StatisticRequest($num_buckets);

echo "Processing 1000 URI requests to uri1, uri2\n";
for ($i = 0; $i <= 1000; $i++) {
    $request->process("uri1");
    $request->process("uri2");
}

testMean($request);
testStddev($request);
testHistogram($request, $num_buckets);

function testMean(StatisticRequest $request) {
    $means = $request->getMean();
    $expectedURI1 = 10;  // 10 milliseconds average
    $expectedURI2 = 20;  // 20 milliseconds average

    echo "\nTesting getMean()";

    echo "\n Expected: uri1 \t $expectedURI1";
    echo "\n Actual: uri1 \t" . $means['uri1'];

    assert(abs($means['uri1'] - $expectedURI1) < 1);

    echo "\n Expected: uri2 \t $expectedURI2";
    echo "\n Actual: uri2 \t" . $means['uri2'];

    assert(abs($means['uri2'] - $expectedURI2) < 1);
    echo "\n\n Test Case Passed \n";
}

function testStddev(StatisticRequest $request) {
    $stddev = $request->getSD();
    $expectedURI1 = 2.5;
    $expectedURI2 = 7.5;

    echo "\nTesting getSD()";

    echo "\n Expected: uri1 \t $expectedURI1";
    echo "\n Actual: uri1 \t" . $stddev['uri1'];

    assert(abs($stddev['uri1'] - $expectedURI1) < 0.5);

    echo "\n Expected: uri2 \t $expectedURI2";
    echo "\n Actual: uri2 \t" . $stddev['uri2'];

    assert(abs($stddev['uri2'] - $expectedURI2) < 0.5);
    echo "\n\n Test Case Passed \n";
}

function testHistogram(StatisticRequest $request, int $num_buckets) {
    $histogram = $request->getHistogram();

    foreach ( $histogram as $uri => $hist) {
        echo "\n URI: $uri \n";
        foreach ( $hist as $label => $count) {
            echo "$label \t $count \n";
        }
        assert(count($hist) == $num_buckets);
    }

    echo "\n\n Test Case Passed \n";
}
