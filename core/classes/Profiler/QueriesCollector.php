<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Profiler;

use Ox\Core\CSQLDataSource;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *  This class collect datasource datas (queries, report) to display in sf profiler
 */
class QueriesCollector extends AbstractDataCollector
{
    /** @var int */
    public const QUERY_MAX_LENGTH = 10000;

    private float $datasource_time = 0;

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $this->collectQueries()
            ->collectReport()
            ->collectDS();
    }

    private function collectDS(): QueriesCollector
    {
        $datasources = [];
        foreach (CSQLDataSource::$dataSources as $ds) {
            $datasources[] = [
                'dsn'  => $ds->dsn,
                'type' => $ds->config['dbtype'],
                'host' => $ds->config['dbhost'],
                'name' => $ds->config['dbname'],
                'user' => $ds->config['dbuser'],
                'link' => get_class($ds->link) . '(' . spl_object_id($ds->link) . ')',
            ];
        }
        $this->data['datasources'] = $datasources;

        return $this;
    }

    private function collectReport(): QueriesCollector
    {
        $reports = [];
        CSQLDataSource::buildReport(10);
        $report_output = CSQLDataSource::displayReport([], false);
        foreach ($report_output as [$ds, $count, $time, $sample, $distribution]) {
            $reports[] = [
                'ds'           => $ds,
                'count'        => $count,
                'time'         => $time,
                'sample'       => strlen($sample) > self::QUERY_MAX_LENGTH ? substr($sample, 0, 100) . '...' : $sample,
                'distribution' => $distribution,
            ];
        }
        $this->data['reports'] = $reports;

        return $this;
    }

    private function collectQueries(): QueriesCollector
    {
        $queries = [];
        foreach (CSQLDataSource::$log_entries as [$query, $time, $ds]) {
            $this->datasource_time += $time;
            $queries[]             = [
                'ds'   => $ds,
                'time' => $time,
                'sql'  => strlen($query) > self::QUERY_MAX_LENGTH ? substr($query, 0, 100) . '...' : $query,
            ];
        }
        $this->data['queries'] = $queries;
        $this->data['stats']   = [
            'dsCount'      => count(CSQLDataSource::$dataSources),
            'dsTime'       => round($this->datasource_time / 1000, 2),
            'queriesCount' => count($queries),
        ];

        return $this;
    }

    public function getName(): string
    {
        return 'app.queries_collector';
    }

    public static function getTemplate(): ?string
    {
        return 'data_collector/queries.html.twig';
    }

    public function getStats()
    {
        return $this->data['stats'];
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getDatasources()
    {
        return $this->data['datasources'];
    }

    public function getReports()
    {
        return $this->data['reports'];
    }
}
