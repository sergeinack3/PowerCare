<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;


use DateTime;
use DateTimeImmutable;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

final class SearchResultsFactory
{
    private function __construct()
    {
    }

    public static function fromResponse(array $response): SearchResults
    {
        $results = array_map(
            function (array $hit): SearchResult {
                return self::fromHit($hit);
            },
            $response['hits']['hits']
        );

        $search_results = new SearchResults($response['took'], $response['hits']['total']['value']);
        $search_results->setResults($results);

        return $search_results;
    }

    private static function fromHit(array $hit): SearchResult
    {
        $source = $hit['_source'];
        $author = null;
        if (isset($source["author_id"]) && $source['author_id']) {
            $author = CMediusers::findOrFail($source["author_id"]);
            $author->loadRefFunction();
        }

        $patient = null;
        if (isset($source["patient_id"]) && $source['patient_id']) {
            $patient = CPatient::findOrFail($source['patient_id']);
        }

        $body = $hit['highlight'] ?? [];
        if (count($body) <= 0) {
            $body = CMbString::normalizeUtf8($source["body"]);

            if (strlen($body) > 500) {
                $body = CMbString::truncate($body, 500, '');
                $body = preg_replace('/\s+?(\S+)?$/', '', $body) . '...';
            }
        } else {
            $body = implode(" [...] ", $body['body']);
        }

        $result = new SearchResult();
        $result->setTitle($source['title'])
            ->setGuid($source['guid'])
            ->setType($source['type'])
            ->setDate(new DateTime($source['date']))
            ->setAuthor($author)
            ->setPatient($patient)
            ->setBody($body);

        return $result;
    }

    public static function fromAggregate(array $response): SearchResults
    {
        $results = array_map(
            function (array $result): SearchResult {
                return self::fromAggregateResult($result);
            },
            $response['aggregations']['reference']['buckets']
        );

        $search_results = new SearchResults($response['took'], $response['hits']['total']['value']);
        $search_results->setResults($results);

        return $search_results;
    }

    private static function fromAggregateResult(array $result): SearchAggregateResult
    {
        $object = CStoredObject::loadFromGuid($result['key']);

        // Default
        $titre     = CAppUI::tr('mod-search-results-error_object');
        $praticien = null;
        $patient   = null;

        if ($object->_id) {
            // Titre
            if ($object instanceof CConsultAnesth) {
                $object->loadRefConsultation();
            }
            $titre = $object->_view;
            // Praticien
            if (method_exists($object, 'loadRefPraticien')) {
                $praticien = $object->loadRefPraticien();
            } elseif (method_exists($object, 'getIndexablePraticien')) {
                $praticien = $object->getIndexablePraticien();
            }
            // Patient
            if (method_exists($object, 'loadRelPatient')) {
                $patient = $object->loadRelPatient();
            }
        }

        $aggregate_result = new SearchAggregateResult();
        $aggregate_result->setPatient($patient)
            ->setAuthor($praticien)
            ->setTitle($titre)
            ->setKey($result['key'])
            ->setCount($result['doc_count']);

        return $aggregate_result;
    }
}
