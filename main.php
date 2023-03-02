<?php
include('StatisticRequest.php');

$request = new StatisticRequest;

$request->process("uri1");
$request->process("uri1");
$request->process("uri2");
$request->process("uri1");
$request->process("uri1");
$request->process("uri2");
$request->process("uri2");

// TESTING ONLY
echo "\n\nURI\tTime";
foreach ( $request->m_requests as $key => $val ) {
    foreach ( $request->m_requests[$key] as $var) {
      echo "\n$key\t$var";
    }
}
