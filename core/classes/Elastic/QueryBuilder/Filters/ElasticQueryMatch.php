<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\QueryBuilder\Filters;

/**
 * Returns documents that match a provided text, number, date or boolean value. The provided text is analyzed before matching.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html Elasticsearch's Documentation
 */
class ElasticQueryMatch extends AbstractElasticQueryFilter
{
    protected string  $field;
    protected string  $query;
    protected ?bool   $phrase_query = null;
    protected ?string $fuzziness    = null;
    protected ?int    $max_expansions                      = null;
    protected ?string $operator                            = null;
    protected ?string $minimum_should_match                = null;

    /**
     * @param string $field
     * @param string $query
     */
    public function __construct(string $field, string $query)
    {
        $this->field = $field;
        $this->query = $query;
    }

    /**
     * @param bool $phrase_query
     *
     * @return ElasticQueryMatch
     */
    public function setAutoGenerateSynonymsPhraseQuery(bool $phrase_query): self
    {
        $this->phrase_query = $phrase_query;

        return $this;
    }

    /**
     * @param string $fuzziness
     *
     * @return ElasticQueryMatch
     */
    public function setFuzziness(string $fuzziness): self
    {
        $this->fuzziness = $fuzziness;

        return $this;
    }

    /**
     * @param int $max_expansions
     *
     * @return ElasticQueryMatch
     */
    public function setMaxExpansions(int $max_expansions): self
    {
        $this->max_expansions = $max_expansions;

        return $this;
    }

    /**
     * @param string $minimum_should_match
     *
     * @return ElasticQueryMatch
     */
    public function setMinimumShouldMatch(string $minimum_should_match): self
    {
        $this->minimum_should_match = $minimum_should_match;

        return $this;
    }

    /**
     * @param string $operator
     *
     * @return ElasticQueryMatch
     */
    public function setOperator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function build(): array
    {
        $data = [
            "match" => [
                $this->field => [
                    "query" => $this->query,
                ],
            ],
        ];

        if ($this->phrase_query !== null) {
            $data["match"][$this->field]["auto_generate_synonyms_phrase_query"] = $this->phrase_query;
        }
        if ($this->fuzziness !== null) {
            $data["match"][$this->field]["fuzziness"] = $this->fuzziness;
        }
        if ($this->max_expansions !== null) {
            $data["match"][$this->field]["max_expansions"] = $this->max_expansions;
        }
        if ($this->minimum_should_match !== null) {
            $data["match"][$this->field]["minimum_should_match"] = $this->minimum_should_match;
        }
        if ($this->operator !== null) {
            $data["match"][$this->field]["operator"] = $this->operator;
        }

        return $data;
    }
}
