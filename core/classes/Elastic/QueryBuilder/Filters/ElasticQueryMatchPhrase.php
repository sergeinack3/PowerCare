<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

/**
 * The match_phrase query analyzes the text and creates a phrase query out of the analyzed text.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase.html Elasticsearch's Documentation
 */
class ElasticQueryMatchPhrase extends ElasticQueryMatch
{
    public function build(): array
    {
        $data = [
            "match_phrase" => [
                $this->field => [
                    "query" => $this->query,
                ],
            ],
        ];

        if ($this->phrase_query !== null) {
            $data["match_phrase"][$this->field]["auto_generate_synonyms_phrase_query"] = $this->phrase_query;
        }
        if ($this->fuzziness !== null) {
            $data["match_phrase"][$this->field]["fuzziness"] = $this->fuzziness;
        }
        if ($this->max_expansions !== null) {
            $data["match_phrase"][$this->field]["max_expansions"] = $this->max_expansions;
        }
        if ($this->minimum_should_match !== null) {
            $data["match_phrase"][$this->field]["minimum_should_match"] = $this->minimum_should_match;
        }
        if ($this->operator !== null) {
            $data["match_phrase"][$this->field]["operator"] = $this->operator;
        }

        return $data;
    }
}
