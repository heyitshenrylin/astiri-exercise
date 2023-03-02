<?php
include('Request.php');
/**
 * Resource request processing class
 *
 * Instantiations of this class do state based processing of resource requests.
 * To use, instantiate an object and call process() on a URI to get the response
 * data. Children of this class can augment functionality by overriding start()
 * and finish().
 */
class StatisticRequest extends Request
{
    private array $m_requests;
    private float $m_startTime;
    private float $m_endTime;
    private string $m_currentUri;
    private int $m_bins;

    /**
    * Initialize requests structure
    */
    function __construct(int $maxBins) {
      $this->m_requests = array();
      $this->m_bins = $maxBins;
    }
    /**
     * Start processing the request in the child class
     *
     * @param string $uri The URI of the request endpoint
     */
    protected function start(string $uri): void
    {
        $this->m_startTime = microtime(true);
        $this->m_currentUri = $uri;
    }

    /** Finish processing the request in the child class */
    protected function finish(): void
    {
        $this->m_endTime = microtime(true);
        $this->addToRequests();
    }

    /**
    * Adds current URI and response time to requests table
    *
    */
    private function addToRequests(): void {
        $time = $this->m_endTime - $this->m_startTime;
        $time = $time * 1000; // Convert to milliseconds

        // Check if uri already in completed requests
        if ( array_key_exists( $this->m_currentUri, $this->m_requests ) ) {
          $this->m_requests[$this->m_currentUri][] = $time;
        }
        else {
          $this->m_requests[$this->m_currentUri] = array($time);
        }
    }

    /**
    * Returns a table of URI => Mean
    *
    * @return array $uriAvg Table of URI => Mean key-value pairs
    */
    public function getMean(): array {
      $uriAvg = array();
      foreach ( $this->m_requests as $uri => $val ) {  // For each URI
        $fResponseArr = array_filter($this->m_requests[$uri]);
        $uriAvg[$uri] = array_sum($fResponseArr)/count($fResponseArr);
      }
      return $uriAvg;
    }

    /**
    * Returns a table of URI => Standard Deviation
    *
    * Runs in O(n*m) time, where n is number of unique URIs, m is number of times
    * the URI is requested
    *
    * @return array $uriSD Table of URI => Standard Deviation key-value pairs
    */
    public function getSD(): array {
      $uriAvg = $this->getMean();
      $uriSD = array();

      foreach ( $this->m_requests as $uri => $val ) { // For each URI
        $variance = 0.0;  // Reset variance after each URI

        $fResponseArr = array_filter($this->m_requests[$uri]);
        // calculated variance = sum of squared differences to the mean
        foreach ( $fResponseArr as $response ) {
          $variance += pow(($response - $uriAvg[$uri]), 2);
        }

        // SD = sqrt(variance/size of pop.)
        $uriSD[$uri] = (float)sqrt($variance/count($fResponseArr));
      }
      return $uriSD;
    }

}
