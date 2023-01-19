<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Profiler;

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Elastic\ElasticClient;
use Ox\Core\Redis\CRedisClient;
use Ox\Mediboard\System\CExchangeSource;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class collect performance datas (times, cache, nosql, transport tiers) to display in sf profiler
 */
class PerformanceCollector extends AbstractDataCollector
{
    private float $transport_time  = 0;
    private float $datasource_time = 0;
    private float $nosql_time      = 0;
    private float $php_time        = 0;
    private float $total_time      = 0;

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->collectCache()
            ->collectObject()
            ->collectNosql()
            ->collectTransport()
            ->collectTime();
    }

    private function collectTime(): PerformanceCollector
    {
        $this->total_time = CApp::$chrono->total;
        foreach (CSQLDataSource::$dataSources as $ds) {
            $this->datasource_time += $ds->chrono->total + $ds->chronoFetch->total;
        }
        $this->php_time = $this->total_time - $this->transport_time - $this->datasource_time - $this->nosql_time;

        $this->data['time'] = [
            'total'     => round($this->total_time * 1000, 2),
            'php'       => [
                'time' => round($this->php_time * 1000, 2),
                'stat' => $this->total_time > 0 ? round($this->php_time * 100 / $this->total_time) : 0,
            ],
            'sql'       => [
                'time' => round($this->datasource_time * 1000, 2),
                'stat' => $this->total_time > 0 ? round($this->datasource_time * 100 / $this->total_time) : 0,
            ],
            'nosql'     => [
                'time' => round($this->nosql_time * 1000, 2),
                'stat' => $this->total_time > 0 ? round($this->nosql_time * 100 / $this->total_time) : 0,
            ],
            'transport' => [
                'time' => round($this->transport_time * 1000, 2),
                'stat' => $this->total_time > 0 ? round($this->transport_time * 100 / $this->total_time) : 0,
            ],
        ];

        return $this;
    }

    private function collectTransport(): PerformanceCollector
    {
        $sources         = [];
        $transport_count = 0;
        foreach (CExchangeSource::$call_traces as $exchange_source => $chronometer) {
            $transport_count      += $chronometer->nbSteps;
            $this->transport_time += $chronometer->total;
            $sources[]            = [
                'name'  => $exchange_source,
                'count' => $chronometer->nbSteps,
                'time'  => round($chronometer->total * 1000, 2),
            ];
        }
        $this->data['transport'] = [
            'time'    => round($this->transport_time * 1000, 2),
            'count'   => $transport_count,
            'sources' => $sources,
        ];

        return $this;
    }

    private function collectNosql(): PerformanceCollector
    {
        $redis_chrono        = CRedisClient::$chrono;
        $elastic_chrono      = ElasticClient::getChrono();
        $redis_time          = $redis_chrono ? $redis_chrono->total : 0;
        $elastic_time        = $elastic_chrono ? $elastic_chrono->total : 0;
        $this->nosql_time    = $redis_time + $elastic_time;
        $this->data['nosql'] = [
            'redis'   => [
                'time'    => round($redis_time * 1000, 2),
                'count'   => $redis_chrono ? $redis_chrono->nbSteps : 0,
                'entries' => CRedisClient::$log_entries,
            ],
            'elastic' => [
                'time'    => round($elastic_time * 1000, 2),
                'count'   => $elastic_chrono ? $elastic_chrono->nbSteps : 0,
                'entries' => ElasticClient::getLogEntries(),
            ],
        ];

        return $this;
    }

    private function collectObject(): PerformanceCollector
    {
        $objects               = CStoredObject::$objectCounts ?? [];
        $this->data['objects'] = $objects;

        return $this;
    }

    private function collectCache(): PerformanceCollector
    {
        $cache           = [];
        $cache['total']  = Cache::getTotal();
        $cache['totals'] = Cache::getTotals();
        foreach (Cache::getTotals() as $_layers) {
            foreach ($_layers as $layer => $count) {
                $layer = strtolower($layer);
                if (!isset($cache[$layer])) {
                    $cache[$layer] = 0;
                }
                $cache[$layer] = $cache[$layer] + $count;
            }
        }
        $cache['shm']        = [
            "engine"  => Cache::getLayerEngine(Cache::OUTER),
            "version" => Cache::getLayerEngineVersion(Cache::OUTER),
        ];
        $cache['dshm']       = [
            "engine"  => Cache::getLayerEngine(Cache::DISTR),
            "version" => Cache::getLayerEngineVersion(Cache::DISTR),
        ];
        $this->data['cache'] = $cache;

        return $this;
    }


    public function getName(): string
    {
        return 'app.performance_collector';
    }

    public static function getTemplate(): ?string
    {
        return 'data_collector/performance.html.twig';
    }

    public function getCache()
    {
        return $this->data['cache'];
    }

    public function getObjects()
    {
        return $this->data['objects'];
    }

    public function getNosql()
    {
        return $this->data['nosql'];
    }

    public function getTime()
    {
        return $this->data['time'];
    }

    public function getTransport()
    {
        return $this->data['transport'];
    }
}
