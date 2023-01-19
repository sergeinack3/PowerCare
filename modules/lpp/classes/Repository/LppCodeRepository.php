<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Repository;

use Exception;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Lpp\CLPPCode;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;

/**
 * A repository class for the Lpp codes.
 * Contains different methods for loading the codes of the Lpp database
 */
class LppCodeRepository extends LppRepository
{
    private const CODES_TABLE = 'fiche';
    private const COMPATIBILITIES_TABLE = 'comp';
    private const INCOMPATIBILITIES_TABLE = 'incomp';

    private const DS_SESAM_VITALE = 'sesam-vitale';

    protected ?CSQLDataSource $ds_sesam_vitale;

    protected static LppCodeRepository $instance;

    /**
     * @param CSQLDataSource|null $ds
     */
    protected function __construct(CSQLDataSource $ds = null, CSQLDataSource $ds_sesam_vitale = null)
    {
        if (!$ds) {
            $ds = CSQLDataSource::get(self::DSN_NAME);
        }

        $this->ds = $ds;

        if (!$ds_sesam_vitale && CModule::getActive('oxPyxvital')) {
            $ds_sesam_vitale = CSQLDataSource::get(self::DS_SESAM_VITALE);
        }

        $this->ds_sesam_vitale = $ds_sesam_vitale;
    }

    /**
     * Overwrite the new singleton instance with a new one that uses the given datasources
     *
     * @param CSQLDataSource $ds
     */
    public static function setDatasources(CSQLDataSource $ds, CSQLDataSource $ds_sesam_vitale): void
    {
        static::$instance = new static($ds, $ds_sesam_vitale);
    }

    /**
     * Returns the singleton instance of the repository
     *
     * Not defined in the parent class for typing purpose
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load the data for the given LPP code
     *
     * @param string $code
     *
     * @return ?CLPPCode
     * @throws LppDatabaseException
     */
    public function load(string $code): ?CLPPCode
    {
        $result = $this->loadHash($this->getCodeQuery($code));

        if (!$result) {
            return null;
        }

        return new CLPPCode($result);
    }

    /**
     * Format the SQL query for loading the data of a given code
     *
     * @param string $code
     *
     * @return CRequest
     */
    public function getCodeQuery(string $code): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::CODES_TABLE);
        $query->addWhere($this->ds->prepare('`CODE_TIPS` = ?', $code));

        return $query;
    }

    /**
     * Load the descendants codes of the given chapter id
     *
     * @param string $chapter_id
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function loadFromParent(string $chapter_id): array
    {
        return $this->loadCodesList($this->getCodesFromParentQuery($chapter_id));
    }

    /**
     * Format the SQL query for getting the codes from the given chapter
     *
     * @param string $chapter_id
     *
     * @return CRequest
     */
    public function getCodesFromParentQuery(string $chapter_id): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::CODES_TABLE);

        $this->getWhereClausesChapter($chapter_id, $query);

        return $query;
    }

    /**
     * Search Lpp codes that matches the given filters
     *
     * @param string|null $code
     * @param string|null $text
     * @param string|null $chapter_id
     * @param string|null $date_valid
     * @param int         $start
     * @param int         $limit
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function search(
        string $code = null,
        string $text = null,
        string $chapter_id = null,
        string $date_valid = null,
        int $start = 0,
        int $limit = 0
    ): array {
        return $this->loadCodesList($this->getSearchQuery($code, $text, $chapter_id, $date_valid, $start, $limit));
    }

    /**
     * Format the SQL query for searching codes
     *
     * @param string|null $code
     * @param string|null $text
     * @param string|null $chapter_id
     * @param string|null $date_valid
     * @param int         $start
     * @param int         $limit
     *
     * @return CRequest
     */
    public function getSearchQuery(
        string $code = null,
        string $text = null,
        string $chapter_id = null,
        string $date_valid = null,
        int $start = 0,
        int $limit = 0
    ): CRequest {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::CODES_TABLE);

        $this->setSearchWhereClause($query, $code, $text, $chapter_id, $date_valid);

        if ($limit) {
            $query->setLimit("$start, $limit");
        }

        $query->addOrder(
            [
                '`ARBO1` ASC',
                '`ARBO2` ASC',
                '`ARBO3` ASC',
                '`ARBO4` ASC',
                '`ARBO5` ASC',
                '`ARBO6` ASC',
                '`ARBO7` ASC',
                '`ARBO8` ASC',
                '`ARBO9` ASC',
                '`ARBO10` ASC',
                '`PLACE` ASC',
            ]
        );

        return $query;
    }

    /**
     * Sets the where clause for searching matching codes
     *
     * @param CRequest    $query
     * @param string|null $code
     * @param string|null $text
     * @param string|null $chapter_id
     * @param string|null $date_valid
     */
    public function setSearchWhereClause(
        CRequest $query,
        string $code = null,
        string $text = null,
        string $chapter_id = null,
        string $date_valid = null
    ): void {
        $whereOr = [];

        if ($code) {
            $whereOr[] = $this->ds->prepare('`CODE_TIPS` LIKE ?', "$code%");
        }
        if ($text) {
            $whereOr[] = $this->ds->prepare('`NOM_COURT` LIKE ?', strtoupper(addslashes("%$text%")));
        }

        if (count($whereOr)) {
            $query->addWhere(implode(' OR ', $whereOr));
        }

        if ($chapter_id) {
            $this->getWhereClausesChapter($chapter_id, $query);
        }

        if ($date_valid) {
            $query->addWhere($this->ds->prepare('`DATE_FIN` IS NULL OR `DATE_FIN` >= ?', $date_valid));
        }
    }

    /**
     * Count the number of codes that matches the given filters
     *
     * @param string|null $code
     * @param string|null $text
     * @param string|null $chapter_id
     * @param string|null $date_valid
     *
     * @return int
     * @throws LppDatabaseException
     */
    public function count(
        string $code = null,
        string $text = null,
        string $chapter_id = null,
        string $date_valid = null
    ): int {
        try {
            $result = $this->ds->loadResult(
                $this->getCountQuery($code, $text, $chapter_id, $date_valid)->makeSelectCount()
            );
        } catch (Exception $e) {
            throw LppDatabaseException::databaseError($e);
        }

        if ($result === false || is_null($result)) {
            throw LppDatabaseException::invalidRequestResult();
        }

        return intval($result);
    }

    /**
     * Format the SQL query for counting the codes matching the given filters
     *
     * @param string|null $code
     * @param string|null $text
     * @param string|null $chapter_id
     * @param string|null $date_valid
     *
     * @return CRequest
     */
    public function getCountQuery(
        string $code = null,
        string $text = null,
        string $chapter_id = null,
        string $date_valid = null
    ): CRequest {
        $query = new CRequest();
        $query->addTable(self::CODES_TABLE);

        $this->setSearchWhereClause($query, $code, $text, $chapter_id, $date_valid);

        return $query;
    }

    /**
     * Load the codes that are compatible with the given code
     *
     * @param string $code
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function loadCompatibleCodes(string $code): array
    {
        return $this->loadCodesList($this->getCompatibleCodesQuery($code));
    }

    /**
     * Format the SQL query for getting the compatible codes
     *
     * @param string $code
     *
     * @return CRequest
     */
    public function getCompatibleCodesQuery(string $code): CRequest
    {
        $query = new CRequest();
        $query->addSelect('`' . self::CODES_TABLE . '`.*');
        $query->addTable(self::CODES_TABLE);
        $query->addRJoinClause(
            self::COMPATIBILITIES_TABLE,
            '`' . self::CODES_TABLE . '`.`CODE_TIPS` = `' . self::COMPATIBILITIES_TABLE . '`.`CODE2`'
        );
        $query->addWhere($this->ds->prepare('`' . self::COMPATIBILITIES_TABLE . '`.`CODE1` = ?', $code));

        return $query;
    }

    /**
     * Load the codes that are incompatible with the given code
     *
     * @param string $code
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function loadIncompatibleCodes(string $code): array
    {
        return $this->loadCodesList($this->getIncompatibleCodesQuery($code));
    }

    /**
     * Format the SQL query for getting the incompatible codes
     *
     * @param string $code
     *
     * @return CRequest
     */
    public function getIncompatibleCodesQuery(string $code): CRequest
    {
        $query = new CRequest();
        $query->addSelect('`' . self::CODES_TABLE . '`.*');
        $query->addTable(self::CODES_TABLE);
        $query->addRJoinClause(
            self::INCOMPATIBILITIES_TABLE,
            '`' . self::CODES_TABLE . '`.`CODE_TIPS` = `' . self::INCOMPATIBILITIES_TABLE . '`.`CODE2`'
        );
        $query->addWhere($this->ds->prepare('`' . self::INCOMPATIBILITIES_TABLE . '`.`CODE1` = ?', $code));

        return $query;
    }

    /**
     * Load a list of codes by executing the given SQL query
     *
     * @param CRequest $query
     *
     * @return array
     * @throws LppDatabaseException
     */
    private function loadCodesList(CRequest $query): array
    {
        $results = $this->loadList($query);

        $codes = [];
        foreach ($results as $_result) {
            $codes[] = new CLPPCode($_result);
        }

        return $codes;
    }

    /**
     * Returns the list of allowed prestation codes for the given medical speciality
     *
     * @param int $speciality
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function getAllowedPrestationCodesForSpeciality(int $speciality): array
    {
        $results = $this->loadList($this->getAllowedPrestationCodesQuery($speciality));


        $codes_prestation = [];
        foreach ($results as $result) {
            $codes_prestation[] = $result['code_prestation'];
        }

        return $codes_prestation;
    }

    /**
     * Returns the SQL query for getting the allowed prestations list for the given speciality
     *
     * @param int $speciality
     *
     * @return CRequest
     */
    public function getAllowedPrestationCodesQuery(int $speciality): CRequest
    {
        $query = new CRequest();
        $query->addTable('code_prestation_to_specialite');
        $query->addSelect('code_prestation');
        $query->addWhere($this->ds->prepare('`specialite` = ?', $speciality));

        return $query;
    }

    /**
     * Load the list of unauthorized expense qualifiers for the given prestation code
     *
     * @param string $code
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function getExpenseQualifiersForCode(string $code): array
    {
        $expense_qualifiers = [];
        if (CModule::getActive('oxPyxvital')) {
            try {
                $results = $this->ds_sesam_vitale->loadHash(
                    $this->getExpenseQualifiersForCodeQuery($code)->makeSelect()
                );
            } catch (Exception $e) {
                throw LppDatabaseException::databaseError($e);
            }

            if (!is_array($results)) {
                throw LppDatabaseException::invalidRequestResult();
            }

            if ($results) {
                foreach ($results as $_qualif => $_value) {
                    if (!$_value) {
                        $expense_qualifiers[] = $_qualif;
                    }
                }
            }
        }

        return $expense_qualifiers;
    }

    /**
     * Format the SQL query for loading the list of unauthorized expense qualifiers for the given prestation code
     *
     * @param string $code
     *
     * @return CRequest
     */
    public function getExpenseQualifiersForCodeQuery(string $code): CRequest
    {
        $query = new CRequest();
        $query->addTable('t7');
        $query->addSelect(['g', 'f', 'e', 'd', 'n', 'a', 'b']);
        $query->addWhere($this->ds_sesam_vitale->prepare('`code` = ?', $code));

        return $query;
    }

    /**
     * Make the where clauses for searching the codes under the given chapter.
     *
     * A chapter id is made by concatenating all the indexes of it's parent chapters
     * (where the indexes superior to 10 are letters, ie B for 11, C for 12)
     *
     * For exemple 034E1
     *
     * In the codes table, there is no chapter id, but columns for each chapter level (ie ARBO1, ARBO2, etc)
     *
     * @param string   $chapter_id
     * @param CRequest $query
     */
    public function getWhereClausesChapter(string $chapter_id, CRequest $query): void
    {
        for ($i = 1; $i < strlen($chapter_id); $i++) {
            switch ($chapter_id[$i]) {
                case 'A':
                    $_chapter = 10;
                    break;
                case 'B':
                    $_chapter = 11;
                    break;
                case 'C':
                    $_chapter = 13;
                    break;
                case 'D':
                    $_chapter = 14;
                    break;
                case 'E':
                    $_chapter = 15;
                    break;
                case 'F':
                    $_chapter = 16;
                    break;
                default:
                    $_chapter = $chapter_id[$i];
            }

            $query->addWhere($this->ds->prepare("`ARBO$i` = ?", $_chapter));
        }
    }
}
