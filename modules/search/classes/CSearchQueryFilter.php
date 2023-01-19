<?php

/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Mediboard\PlanningOp\CSejour;
use stdClass;

class CSearchQueryFilter implements IShortNameAutoloadable
{
    /** @var string */
    private $words;

    /** @var int */
    private $start = 0;

    /** @var int */
    private $limit = 15;

    /** @var string[] */
    private $names_types;

    /** @var bool */
    private $aggregation = false;

    /** @var int */
    private $sejour_id;

    /** @var int */
    private $specific_user;

    /** @var string */
    private $details;

    /** @var string */
    private $date_min;

    /** @var string */
    private $date_max;

    /** @var string */
    private $date;

    /** @var bool */
    private $fuzzy_search;

    /** @var int */
    private $patient_id;

    /** @var string */
    private $reference;

    /**
     * @return mixed
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @param mixed $words Words to set
     *
     * @return CSearchQueryFilter
     */
    public function setWords($words): CSearchQueryFilter
    {
        $this->words = utf8_encode($words);

        return $this;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start Start of the search
     *
     * @return CSearchQueryFilter
     */
    public function setStart(int $start): CSearchQueryFilter
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit Search's limit
     *
     * @return CSearchQueryFilter
     */
    public function setLimit(int $limit): CSearchQueryFilter
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamesTypes()
    {
        return $this->names_types;
    }

    /**
     * @param array $names_types Types names
     *
     * @return CSearchQueryFilter
     */
    public function setNamesTypes(array $names_types): CSearchQueryFilter
    {
        $this->names_types = $names_types;

        // Prescription
        if (in_array("CPrescriptionLineMedicament", $names_types)) {
            $this->names_types[] = "CPrescriptionLineMix";
            $this->names_types[] = "CPrescriptionLineElement";
        }

        // Formulaire
        if (in_array("CExObject", $names_types)) {
            $key                     = array_search('CExObject', $this->names_types);
            $this->names_types[$key] = "CExObject*";
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getAggregation(): bool
    {
        return $this->aggregation;
    }

    /**
     * @param bool $aggregation Use results aggregation
     *
     * @return CSearchQueryFilter
     */
    public function setAggregation(bool $aggregation): CSearchQueryFilter
    {
        $this->aggregation = $aggregation;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSejourId()
    {
        return $this->sejour_id;
    }

    /**
     * @param int $sejour_id Sejour ID
     *
     * @return CSearchQueryFilter
     */
    public function setSejourId(int $sejour_id): CSearchQueryFilter
    {
        $this->sejour_id = $sejour_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpecificUser()
    {
        return $this->specific_user;
    }

    /**
     * @param mixed $specific_user Specific user for the search
     *
     * @return CSearchQueryFilter
     */
    public function setSpecificUser($specific_user): CSearchQueryFilter
    {
        $this->specific_user = $specific_user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details Details to set
     *
     * @return CSearchQueryFilter
     */
    public function setDetails($details): CSearchQueryFilter
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateMin(): string
    {
        return $this->date_min;
    }

    /**
     * @param string $date_min Minimum date for the search
     *
     * @return CSearchQueryFilter
     */
    public function setDateMin(string $date_min): CSearchQueryFilter
    {
        $this->date_min = $date_min;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateMax(): string
    {
        return $this->date_max;
    }

    /**
     * @param string $date_max Maximum date for the search
     *
     * @return CSearchQueryFilter
     */
    public function setDateMax(string $date_max): CSearchQueryFilter
    {
        $this->date_max = $date_max;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date Set a specific date
     *
     * @return CSearchQueryFilter
     */
    public function setDate(string $date): CSearchQueryFilter
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFuzzySearch()
    {
        return $this->fuzzy_search;
    }

    /**
     * @param mixed $fuzzy_search Fuzzy search
     *
     * @return CSearchQueryFilter
     */
    public function setFuzzySearch($fuzzy_search): CSearchQueryFilter
    {
        $this->fuzzy_search = (int)$fuzzy_search;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id Patient_id to search for
     *
     * @return void
     */
    public function setPatientId($patient_id): void
    {
        $this->patient_id = $patient_id;
    }

    /**
     * @return string GUID Object ref
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference GUID Object ref
     *
     * @return void
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }


    /**
     * @return array
     */
    public function getBodyToElastic(): array
    {
        $must = [];
        $born = [];

        // keywords
        if ($this->words) {
            // The query string is parsed into a series of terms and operators
            if (substr_count($this->words, ' ') && !$this->isAdvancedPattern()) {
                // A term can be a single word or a phrase, surrounded by double quotes
                $this->words = '"' . $this->words . '"';
            }

            // AND OR NOT * ~ ? + -
            if ($this->isAdvancedPattern()) {
                // The query_string query parses the input and splits text around operators
                $words_query = [
                    'query_string' => [
                        'query'            => $this->words,
                        // The actual query to be parsed
                        'fields'           => ['body', 'title'],
                        // The query_string query can also run against multiple fields
                        'fuzziness'        => $this->fuzzy_search ? 'AUTO' : 0,
                        // Levenshtein Edit Distance?
                        'default_operator' => 'AND',
                        // the default_operator above which allows you to force all terms to be required
                    ],
                ];
            } else {
                //  The multi_match query builds on the match query to allow multi-field queries
                $words_query = [
                    'multi_match' => [
                        'query'    => $this->words, // The actual query to be parsed
                        'fields'   => ['body', 'title'], // The query_string query can also run against multiple fields
                        'operator' => 'AND', // To force all terms to be required
                    ],
                ];
                // Fuzziness not allowed for type [phrase]
                if (!$this->fuzzy_search) {
                    // Runs a match_phrase query on each field and combines the _score from each field
                    $words_query['multi_match']['type'] = 'phrase';
                } else {
                    // Allows fuzzy matching based on the type of field being queried
                    $words_query['multi_match']['fuzziness'] = 'AUTO';
                }
            }
            $must[] = $words_query;
        }

        // types
        if (!empty($this->names_types)) {
            $names_types = implode(" ", $this->names_types);
            $must[]      = [
                'query_string' => [
                    'query'            => $names_types,
                    'fields'           => ['type'],
                    "analyze_wildcard" => true,
                    "default_operator" => "OR",
                ],
            ];
        }

        // Patient
        if (!empty($this->patient_id)) {
            $must[] = [
                'match' => [
                    'patient_id' => $this->patient_id,
                ],
            ];
        }

        // date
        if ($this->date_min) {
            $date_min    = CMbDT::format($this->date_min, "%Y/%m/%d");
            $born['gte'] = $date_min;
        }
        if ($this->date_max) {
            $date_max    = CMbDT::format($this->date_max, "%Y/%m/%d");
            $born['lte'] = $date_max;
        }
        if (!empty($born)) {
            $must[] = [
                'range' => [
                    'date' => $born,
                ],
            ];
        }

        // intervenant
        if ($this->specific_user) {
            $users = explode('|', $this->specific_user);
            $users = array_unique($users);
            //$users  = implode(" ", $users);
            $must[] = [
                'terms' => [
                    'author_id' => $users,
                ],
            ];
        }

        // sejour
        if ($this->sejour_id) {
            $CSejour = new CSejour();
            $must[]  = [
                'match' => [
                    'object_ref_class' => $CSejour->_class,
                ],
            ];
            $must[]  = [
                'match' => [
                    'object_ref_id' => $this->sejour_id,
                ],
            ];
        }

        // reference
        if ($reference = $this->reference) {
            $reference = explode('-', $reference);
            $must[]    = [
                'match' => [
                    'object_ref_class' => $reference[0],
                ],
            ];
            $must[]    = [
                'match' => [
                    'object_ref_id' => $reference[1],
                ],
            ];
        }

        // retour
        $retour = [
            "explain"   => true,
            'size'      => $this->aggregation ? 0 : CSearch::REQUEST_SIZE,
            'from'      => $this->start,
            'query'     => [
                'bool' => [
                    'must' => $must,
                ],
            ],
            'highlight' => [
                'fields' => [
                    ['body' => new stdClass()],
                ],
            ],
        ];

        // Aggregation
        if ($this->aggregation) {
            $retour['aggs'] = [
                'reference' => [
                    'terms' => [
                        'script' => "doc['object_ref_class.keyword'].value + '-' + doc['object_ref_id'].value",
                        "size"   => CSearch::REQUEST_AGG_SIZE,
                    ],
                ],
            ];
        }

        return $retour;
    }

    /**
     * @return bool
     */
    private function isAdvancedPattern(): bool
    {
        return !(
            false === strpos($this->words, 'AND') // Must be present
            && false === strpos($this->words, 'OR') // May be present
            && false === strpos($this->words, 'NOT') // Must not be present
            && false === strpos($this->words, '*') // Wildcard  to replace zero or more characters
            && false === strpos($this->words, '~') // Fuzzy operator
            && false === strpos($this->words, '?') // Wildcard to replace a single character
            && false === strpos($this->words, '+') // This term must be present
            && false === strpos($this->words, '-') // This term must not be present
            && false === strpos($this->words, '"') // This term must not be present
        );
    }
}
