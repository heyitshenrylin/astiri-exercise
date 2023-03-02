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
    public array $m_requests;  // TODO remove public tag after testing
    private float $m_startTime;
    private float $m_endTime;
    private string $m_currentUri;

    /**
    * Initialize requests structure
    */
    function __construct() {
      $this->m_requests = array();
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

    private function addToRequests(): void {
        $time = $this->m_endTime - $this->m_startTime;

        // Check if uri already in completed requests
        if ( array_key_exists( $this->m_currentUri, $this->m_requests ) ) {
          $this->m_requests[$this->m_currentUri][] = $time;
        }
        else {
          $this->m_requests[$this->m_currentUri] = array($time);
        }
    }

    public function getMean(): array {
      $uriAvg = array();
      foreach ( $this->m_requests as $uri => $val ) {
        $filteredResponseArr = array_filter($this->m_requests[$uri]);
        $uriAvg[$uri] = array_sum($filteredResponseArr)/count($filteredResponseArr);
      }
      return $uriAvg;
    }

}
