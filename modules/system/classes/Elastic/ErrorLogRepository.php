<?php

/**
 * @package Mediboard\System\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Elastic;

use DateTimeImmutable;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectRepositories;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryAggregation;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryBuilder;
use Ox\Core\Elastic\QueryBuilder\Filters\AbstractElasticQueryFilter;
use Ox\Core\Elastic\QueryBuilder\Filters\ElasticQueryMust;
use Ox\Core\Elastic\QueryBuilder\Filters\ElasticQueryMustNot;
use Ox\Core\Elastic\QueryBuilder\RangeOperator;

class ErrorLogRepository extends ElasticObjectRepositories
{
    /**
     * Defines the Object that the repository will manage
     * @return ElasticObject
     */
    public function getElasticObject(): ElasticObject
    {
        return new ErrorLog();
    }

    public function addAggregation(string $group_similar, ElasticQueryBuilder $query): ElasticQueryBuilder
    {
        $query_data = $query->build();
        $size       = $query_data["size"] ?? 10;
        $from       = $query_data["from"] ?? 0;
        if (array_key_exists("body", $query_data) && array_key_exists("sort", $query_data["body"])) {
            $order = array_keys($query_data["body"]["sort"][0])[0];
        } else {
            $order = "date";
        }
        $query->setSize(0);
        $query->setFrom(0);

        if ($order === "count") {
            $order = "total_count";
        } elseif ($order === "date") {
            $order = "date_max";
        }
        $sub_aggregation = new ElasticQueryAggregation();


        $sub_aggregation->addSum("total_count", "count")
            ->addBucketSort("bucket_sort", $size, $from)
            ->addSortOnAggregation("bucket_sort", $order, "desc")
            ->addTopHit("log")
            ->addMin("date_min", "date")
            ->addMax("date_max", "date")
            ->addTerms("similar_ids", "_id", 500)
            ->addTerms("similar_user_ids", "user_id", 10)
            ->addTerms("similar_server_ips", "server_ip.keyword", 10);

        $aggregation = new ElasticQueryAggregation();

        if ($group_similar === 'signature') {
            $aggregation->addCardinality("total", "signature_hash.keyword")
                ->addTerms("signature_hash", "signature_hash.keyword", 2147483647)
                ->addSubAggregation("signature_hash", $sub_aggregation);
        }
        if ($group_similar === 'similar') {
            $aggregation->addCardinalityByScript(
                "total",
                "doc['text.keyword'].value + '#' +
                         doc['stacktrace.keyword'].value + '#' +
                          doc['param_GET.keyword'].value + '#' +
                           doc['param_POST.keyword'].value"
            )
                ->addMultiTermsAggregation(
                    "signature_hash",
                    [
                        "text.keyword",
                        "stacktrace.keyword",
                        "param_GET.keyword",
                        "param_POST.keyword",
                    ],
                    2147483647
                )
                ->addSubAggregation("signature_hash", $sub_aggregation);
        }
        $query->addAggregation($aggregation);

        return $query;
    }

    public function buildQueryFromFormData(array $data, array $robots, int $from, int $number): ElasticQueryBuilder
    {
        $settings = $this->object->getSettings();

        $query = new ElasticQueryBuilder($settings->getAliasName());
        $query->setSize($number)
            ->setFrom($from);

        if (array_key_exists("order_by", $data)) {
            if ($data["order_by"] === "quantity") {
                $query->addSort("count", "desc");
                $query->addSort("date", "desc");
            } elseif ($data["order_by"] === "date") {
                $query->addSort("date", "desc");
                $query->addSort("count", "desc");
            }
        }

        $filters  = AbstractElasticQueryFilter::bool();
        $must     = new ElasticQueryMust();
        $must_not = new ElasticQueryMustNot();
        if (array_key_exists("request_uid", $data) && $data["request_uid"] !== "") {
            $must->beLike("request_uid", $data["request_uid"]);
        }

        if (array_key_exists("human", $data) && array_key_exists("robot", $data)) {
            if (count($robots)) {
                if ($data["human"] && !$data["robot"]) {
                    $must_not->beIn("user_id", $robots);
                } elseif ($data["robot"] && !$data["human"]) {
                    $must->beIn("user_id", $robots);
                }
            }
        }

        if (array_key_exists("error_type", $data) && !empty($data["error_type"])) {
            $error_type = array_keys($data["error_type"]);
            $must->beIn("type", $error_type);
        }

        if (array_key_exists("user_id", $data) && $data["user_id"] !== "") {
            $must->beLike("user_id", $data["user_id"]);
        }

        if (array_key_exists("server_ip", $data) && $data["server_ip"] !== "") {
            $must->beLike("server_ip", $data["server_ip"]);
        }

        if (array_key_exists("text", $data) && $data["text"] !== "") {
            $must->beLike("text", $data["text"]);
        }

        if (
            array_key_exists("datetime_min", $data) &&
            array_key_exists("datetime_max", $data) &&
            $data["datetime_min"] !== "" && $data["datetime_max"] !== ""
        ) {
            $date_min = DateTimeImmutable::createFromFormat(
                "Y-m-d H:i:s",
                $data["datetime_min"]
            )->format(ElasticObject::DATE_TIME_FORMAT);
            $date_max = DateTimeImmutable::createFromFormat(
                "Y-m-d H:i:s",
                $data["datetime_max"]
            )->format(ElasticObject::DATE_TIME_FORMAT);
            $must->beInBetween("date", $date_min, $date_max);
        } elseif (array_key_exists("datetime_min", $data) && $data["datetime_min"] !== "") {
            $date = DateTimeImmutable::createFromFormat(
                "Y-m-d H:i:s",
                $data["datetime_min"]
            )->format(ElasticObject::DATE_TIME_FORMAT);
            $must->beInRange("date", RangeOperator::GREATER_THAN_OR_EQUAL_TO(), $date);
        } elseif (array_key_exists("datetime_max", $data) && $data["datetime_max"] !== "") {
            $date = DateTimeImmutable::createFromFormat(
                "Y-m-d H:i:s",
                $data["datetime_max"]
            )->format(ElasticObject::DATE_TIME_FORMAT);
            $must->beInRange("date", RangeOperator::LESS_THAN_OR_EQUAL_TO(), $date);
        }

        $filters->addMust($must)->addMustNot($must_not);
        $query->addFilter($filters);

        return $query;
    }
}
