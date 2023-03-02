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
        // Convert to milliseconds
        $time = ($this->m_endTime - $this->m_startTime) * 1000;


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
            $numResponses = count($this->m_requests[$uri]);
            $uriAvg[$uri] = array_sum($this->m_requests[$uri])/$numResponses;
        }
        return $uriAvg;
    }

    /**
    * Returns a table of URI => Standard Deviation
    *
    * Runs in O(n*m) time, where n is number of unique URIs, m is number of
    * times the URI is requested
    *
    * @return array $uriSD Table of URI => Standard Deviation key-value pairs
    */
    public function getSD(): array {
        $uriAvg = $this->getMean();
        $uriSD = array();

        foreach ( $this->m_requests as $uri => $val ) { // For each URI
            $variance = 0.0;  // Reset variance after each URI

            // calculated variance = sum of squared differences to the mean
            foreach ( $this->m_requests[$uri] as $response ) {
                $variance += pow(($response - $uriAvg[$uri]), 2);
            }

            $numResponses = count($this->m_requests[$uri]);
            // SD = sqrt(variance/size of pop.)
            $uriSD[$uri] = (float)sqrt($variance/$numResponses);
        }
        return $uriSD;
    }

    /**
    * Creates a 0-100 normalized histogram
    *
    * @return array $histograms Table of URI => (Table of histogram
    * label => count)
    */
    public function getHistogram(): array {
        $histograms = array();
        foreach ( $this->m_requests as $uri => $val ) { // For each URI
            // Normalize to 0-100
            $normResponseArr = array_map(
                fn($data) => $this->normalize(
                    $data,
                    min($this->m_requests[$uri]),
                    max($this->m_requests[$uri])
                ),
                $this->m_requests[$uri]
            );

            // Create Histogram
            $maxBins = $this->m_bins;
            $min = min($normResponseArr);  // Normalized = 0
            $max = max($normResponseArr); // Normalized = 100

            $hist = array();

            $valuePerBin = ($max - $min) / $maxBins;

            $totalBins = $maxBins;


            sort($normResponseArr);

            for ($i = 0; $i < $totalBins; $i++) {  // For each bin
                $count = 0;
                if ($i == $totalBins - 1) {  // At last bin
                    foreach ($normResponseArr as $key => $number) {
                        $count++;
                        unset($normResponseArr[$key]);
                    }
                } else {
                    foreach ($normResponseArr as $key => $number) {
                        // Check if the value falls within the bin
                        if ($number <=
                            (($min + ($valuePerBin - 1)) + ($i * $valuePerBin)))
                        {  // i.e less than max value of the bin label
                            $count++;
                            unset($normResponseArr[$key]);
                        } else {  // Value was larger than the max of the bin
                            break;  // Skip to next bin because normResponseArr is sorted
                        }
                    }
                }

                // Apply label and count
                if (count($normResponseArr) > 0 || $count > 0) {
                    $rangeMax = ceil(
                            ($min + $valuePerBin + ($i * $valuePerBin)));
                    $label = floor($min + ($i * $valuePerBin))
                        . '-'
                        . $rangeMax;

                    $hist[$label] = $count;
                }


            }

            $histograms[$uri] = $hist;
        }
        return $histograms;
    }

    /**
    * 0-100 normalizes a value to a minimum and maximum range
    * @param float $data The value to normalize
    * @param float $min The minimum value in the dataset
    * @param float $max The maximum value in the dataset
    */
    protected function normalize(float $data, float $min, float $max): float {
        return 100*(($data-$min)/($max-$min));
    }
}
