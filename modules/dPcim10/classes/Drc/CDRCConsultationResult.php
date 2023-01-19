<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Drc;

use Ox\Core\Cache;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\CCodeCIM10;

/**
 * Represents a Consultation Result
 */
class CDRCConsultationResult extends CDRC
{
    /** @var int The id of the Consultation Result */
    public $result_id;

    /** @var string The title */
    public $title;

    /** @var string The nature (1 => RC, 3 => DHL) */
    public $nature;

    /** @var int The concerned genre for this result (1 => male, 2 => female, 3 => both) */
    public $sex;

    /** @var string The type of episode (C => chronicle, A => intense, I => intermediate, NC => not concerned) */
    public $episode_type;

    /** @var int The version number */
    public $version;

    /** @var int The state of the result (1 => valid, 2 => removed) */
    public $state;

    /** @var bool Indicate whether this result can be used as a symptom */
    public $symptom;

    /** @var bool Indicate whether this result can be used as a syndrome */
    public $syndrome;

    /** @var bool Indicate whether this result can be used as a disease */
    public $disease;

    /** @var bool Indicate whether this result can be used as a certified diagnosis */
    public $certified_diagnosis;

    /** @var bool Indicate whether this result can be used as non pathological */
    public $unpathological;

    /** @var string The complete information about this result */
    public $details;

    /** @var int The propable duration of the episode */
    public $dur_prob_epis;

    /** @var int The minimum age for selecting this result */
    public $age_min;

    /** @var int The maximum age for selecting this result */
    public $age_max;

    /** @var string The Cim10 code linked to the result */
    public $cim10_code;

    /** @var string The CISP code linked to the result */
    public $cisp_code;

    /** @var CDRCCriterion[] The criteria linked to the result */
    public $_criteria = [];

    /** @var CDRCResultClass The result class to which the result belong */
    public $_class;

    /** @var CDRCCriticalDiagnosis[] The critical diagnoses linked to the result */
    public $_critical_diagnoses = [];

    /** @var CDRCConsultationResult[] The siblings results */
    public $_siblings = [];

    /** @var CDRCTranscoding[] The transcoding between the DRC, Cim10 and CISP */
    public $_transcodings = [];

    /** @var CDRCSynonym[] The synonyms of the result */
    public $_synonyms = [];

    /** @var array The list of the diagnosis positions */
    public static $diagnosis_positions = ['symptom', 'syndrome', 'disease', 'certified_diagnosis', 'unpathological'];

    /**
     * CDRCConsultationResult constructor.
     *
     * @param int $result_id The result id
     * @param int $load      The desired loading level
     */
    public function __construct($result_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->result_id = $result_id;

        switch ($load) {
            case CDRC::LOAD_LITE:
                $this->load();
                break;
            case CDRC::LOAD_FULL:
                $this->load();
                $this->loadReferences();
                break;
            default:
        }
    }

    /**
     * Load the data from the database
     *
     * @return void
     */
    public function load()
    {
        if ($this->result_id && self::exists($this->result_id)) {
            $query = new CRequest();
            $query->addTable('consultation_results');
            $query->addSelect('*');
            $query->addWhereClause('result_id', "= {$this->result_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
                $this->formatDetails();
            }
        }
    }

    /**
     * Load the foreign references
     *
     * @return void
     */
    public function loadReferences()
    {
        if ($this->result_id) {
            $this->loadCriteria();
            $this->loadResultClass();
            $this->loadCriticalDiagnoses();
            $this->loadSiblings();
            $this->loadTranscodings();
            $this->loadSynonyms();
        }
    }

    /**
     * Format the details text into an HTML formated text
     *
     * @return void
     */
    protected function formatDetails()
    {
        $titles = [
            'DENOMINATION',
            'CRITERES D\'INCLUSION',
            'COMPLEMENTS SEMIOLOGIQUES',
            'VOIR AUSSI',
            'DIAGNOSTICS CRITIQUES',
            'POSITIONS DIAGNOSTIQUES',
            'CORRESPONDANCES CIM 10',
        ];

        $text = '';
        foreach ($titles as $i => $title) {
            $text  .= "<strong>{$title}</strong>";
            $start = strpos($this->details, $title) + strlen($title);
            if ($i < count($titles) - 1) {
                $length  = strpos($this->details, $titles[$i + 1]) - $start;
                $content = substr($this->details, $start, $length);
            } else {
                $content = substr($this->details, $start);
            }

            if (
                !in_array(
                    $title,
                    ['VOIR AUSSI', 'DIAGNOSTICS CRITIQUES', 'POSITIONS DIAGNOSTIQUES', 'CORRESPONDANCES CIM 10']
                )
            ) {
                $text .= trim(nl2br($content));
            } else {
                $text .= '<ul>';
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    if (trim($line) != '') {
                        $text .= "<li>{$line}</li>";
                    }
                }
                $text .= '</ul><br>';
            }
        }

        $this->details = $text;
    }

    /**
     * Load the criteria linked to the consultation result
     *
     * @return void
     */
    protected function loadCriteria()
    {
        $this->_criteria = [];

        $query = new CRequest();
        $query->addTable('criteria');
        $query->addSelect('*');
        $query->addWhereClause('result_id', "= {$this->result_id}");
        $query->addWhereClause('validity', "= '1'");
        $query->addOrder('`order` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $criterion = new CDRCCriterion();
                $criterion->map($row);
                $criterion->loadReferences();

                $this->_criteria[$criterion->order] = $criterion;
            }
        }
    }

    /**
     * Load the result class
     *
     * @return void
     */
    protected function loadResultClass()
    {
        $query = new CRequest();
        $query->addTable('result_classes');
        $query->addSelect('result_classes.*');
        $query->addLJoinClause('results_to_classes', 'results_to_classes.class_id = result_classes.class_id');
        $query->addWhereClause('results_to_classes.result_id', "= {$this->result_id}");
        $query->addOrder('result_classes.`class_id` DESC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            $this->_class = new CDRCResultClass();
            $data         = self::$source->fetchAssoc($result);
            if ($data) {
                $this->_class->map($data);
            }
        }
    }

    /**
     * Load the critical diagnoses linked to the result
     *
     * @return void
     */
    protected function loadCriticalDiagnoses()
    {
        $this->_critical_diagnoses = [];

        $query = new CRequest();
        $query->addTable('critical_diagnoses');
        $query->addSelect('critical_diagnoses.*');
        $query->addLJoinClause(
            'results_to_diagnoses',
            'results_to_diagnoses.diagnosis_id = critical_diagnoses.diagnosis_id'
        );
        $query->addWhereClause('results_to_diagnoses.result_id', "= {$this->result_id}");
        $query->addOrder('critical_diagnoses.`criticality` DESC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $diagnosis = new CDRCCriticalDiagnosis();
                $diagnosis->map($row);

                $this->_critical_diagnoses[] = $diagnosis;
            }
        }
    }

    /**
     * Load the siblings results linked to the result
     *
     * @return void
     */
    protected function loadSiblings()
    {
        $this->_siblings = [];

        $query = new CRequest();
        $query->addTable('consultation_results');
        $query->addSelect('consultation_results.*');
        $query->addLJoinClause('siblings', 'siblings.sibling_id = consultation_results.result_id');
        $query->addWhereClause('siblings.result_id', "= {$this->result_id}");
        $query->addOrder('consultation_results.`title` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $consultation_result = new CDRCConsultationResult();
                $consultation_result->map($row);

                $this->_siblings[] = $consultation_result;
            }
        }
    }

    /**
     * Load the transcodings linked to the result
     *
     * @return void
     */
    protected function loadTranscodings()
    {
        $this->_transcodings = [];

        $query = new CRequest();
        $query->addTable('transcodings');
        $query->addSelect('*');
        $query->addWhereClause('result_id', "= {$this->result_id}");
        $query->addOrder('`code_cim_1` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $transcoding = new CDRCTranscoding();
                $transcoding->map($row);
                $transcoding->loadReferences();

                $this->_transcodings[$transcoding->code_cim_1] = $transcoding;
            }
        }

        if ($this->cim10_code) {
            $query = new CRequest();
            $query->addTable('transcodings');
            $query->addSelect('*');
            $query->addWhereClause('result_id', "= {$this->result_id}");
            $query->addWhereClause('code_cim_1', "= '{$this->cim10_code}'");
            $result = self::$source->exec($query->makeSelect());
            if (!$result || !self::$source->fetchRow($result)) {
                $code = CCodeCIM10::get(str_replace('.', '', $this->cim10_code));
                if ($code->exist) {
                    $transcoding                = new CDRCTranscoding();
                    $transcoding->code_cim_1    = $this->cim10_code;
                    $transcoding->libelle_cim_1 = $code->libelle;

                    $this->_transcodings[$transcoding->code_cim_1] = $transcoding;
                }
            }
        }

        ksort($this->_transcodings);
    }

    /**
     * Load the synonyms linked to the result
     *
     * @return void
     */
    protected function loadSynonyms()
    {
        $this->_synonyms = [];

        $query = new CRequest();
        $query->addTable('synonyms');
        $query->addSelect('*');
        $query->addWhereClause('result_id', "= {$this->result_id}");
        $query->addOrder('`libelle` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $transcoding = new CDRCSynonym();
                $transcoding->map($row);

                $this->_synonyms[] = $transcoding;
            }
        }
    }

    /**
     * Load the consultation result from the cache or the database
     *
     * @param int $result_id The consultation result id
     * @param int $load      The load level
     *
     * @return CDRCConsultationResult
     */
    public static function get($result_id, $load = self::LOAD_LITE)
    {
        $cache = new Cache('CDRCConsultationResult.get', [$result_id], self::$cache_layers);

        if ($cache->exists()) {
            $result = $cache->get();
        } else {
            $result = new self($result_id, $load);
            $cache->put($result, true);
        }

        return $result;
    }

    /**
     * Search the given keywords in the DRC database
     *
     * @param string          $keywords   The keywords to search
     * @param string          $sex        The sex concerned by the result (1 => male, 2 => female, 3 => mixt)
     * @param integer         $age        The age concerned by the result
     * @param CDRCResultClass $class      The result class
     * @param bool            $only_title If true, the search will only be made in the title of the results
     * @param integer         $start      The start
     * @param integer         $limit      The number of results to fetch
     *
     * @return array
     */
    public static function search(
        $keywords = '',
        $sex = null,
        $age = null,
        $class = null,
        $only_title = false,
        $start = 0,
        $limit = 100
    ) {
        self::getDatasource();

        $results = [];
        $query   = new CRequest();
        $query->addTable('consultation_results');
        $query->addSelect('DISTINCT consultation_results.result_id');
        $query->addWhereClause('consultation_results.state', "= 1");

        if ($class && $class instanceof CDRCResultClass) {
            $query->addLJoinClause(
                'results_to_classes',
                'results_to_classes.result_id = consultation_results.result_id'
            );
            $query->addWhereClause('results_to_classes.class_id', "= {$class->class_id}");
        }

        if ($sex) {
            $query->addWhereClause('consultation_results.sex', "= '$sex'");
        }

        if ($age) {
            $query->addWhere("(consultation_results.age_min <= $age OR consultation_results.age_min IS NULL)");
            $query->addWhere("(consultation_results.age_max >= $age OR consultation_results.age_max IS NULL)");
        }

        $result = false;
        /* If the search is only made on the title, we don't need other request */
        if ($only_title) {
            $query->addSelect('consultation_results.title');
            $query->addWhere("consultation_results.title LIKE '%{$keywords}%'");
            $result = self::$source->exec($query->makeSelect());
        } /* Otherwise, we must make several requests for performance's sake */
        else {
            /* We first make a request for filtering with the class, state, age and sex */
            $results_id = [];
            $prefilters = self::$source->loadColumn($query->makeSelect());
            if ($prefilters) {
                /* Then we filter by title */
                $query = new CRequest();
                $query->addTable('consultation_results');
                $query->addSelect('DISTINCT result_id');
                $query->addWhereClause('title', "LIKE '%{$keywords}%'");
                $query->addWhereClause('result_id', CSQLDataSource::prepareIn($prefilters));
                $title_filters = self::$source->loadColumn($query->makeSelect());
                if ($title_filters) {
                    $results_id = array_merge($results_id, $title_filters);
                }

                /* Then we filter by criteria */
                $query = new CRequest();
                $query->addTable('criteria');
                $query->addSelect('DISTINCT result_id');
                $query->addWhereClause('libelle', "LIKE '%{$keywords}%'");
                $query->addWhereClause('result_id', CSQLDataSource::prepareIn($prefilters));
                $criteria_filters = self::$source->loadColumn($query->makeSelect());
                if ($criteria_filters) {
                    $results_id = array_merge($results_id, $criteria_filters);
                }

                /* Then we filter by synonyms */
                $query = new CRequest();
                $query->addTable('synonyms');
                $query->addSelect('DISTINCT result_id');
                $query->addWhereClause('libelle', "LIKE '%{$keywords}%'");
                $query->addWhereClause('result_id', CSQLDataSource::prepareIn($prefilters));
                $synonym_filters = self::$source->loadColumn($query->makeSelect());
                if ($synonym_filters) {
                    $results_id = array_merge($results_id, $synonym_filters);
                }

                /* And we merge all the results and get the data */
                if (count($results_id)) {
                    $query = new CRequest();
                    $query->addTable('consultation_results');
                    $query->addSelect(['DISTINCT result_id', 'title']);
                    $query->addWhereClause('result_id', CSQLDataSource::prepareIn($results_id));
                    $query->addOrder('consultation_results.title ASC');
                    if ($start !== null & $limit !== null) {
                        $query->setLimit("{$start}, {$limit}");
                    }
                    $result = self::$source->exec($query->makeSelect());
                }
            }
        }

        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $results[] = [
                    'result_id' => $row['result_id'],
                    'title'     => $row['title'],
                ];
            }
        }

        return $results;
    }

    /**
     * Check if the consultation result with the given id exists
     *
     * @param integer $result_id The consultation result id
     *
     * @return bool
     */
    public static function exists($result_id)
    {
        self::getDatasource();
        $result_id = intval($result_id);
        $exists    = false;

        $query = new CRequest();
        $query->addTable('consultation_results');
        $query->addSelect('COUNT(`result_id`) AS total');
        $query->addWhereClause('result_id', "= {$result_id}");
        $result = self::$source->exec($query->makeSelect());

        if ($result) {
            $row = self::$source->fetchAssoc($result);

            if ($row['total'] !== 0) {
                $exists = true;
            }
        }

        return $exists;
    }
}
