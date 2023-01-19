<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Repository;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Mediboard\Lpp\CLPPDatedPricing;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;

/**
 * A repository class for the Lpp pricings.
 * Contains different methods for loading the pricings of the Lpp database
 */
class LppPricingRepository extends LppRepository
{
    private const TABLE_NAME = 'histo';

    protected static LppPricingRepository $instance;

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
     * Load the pricing that is active at the given date, for the given code.
     * If no date is given, the current date will be used
     *
     * @param string      $code
     * @param string|null $date
     *
     * @return CLPPDatedPricing
     * @throws LppDatabaseException
     */
    public function loadFromDate(string $code, string $date = null): CLPPDatedPricing
    {

        $data = $this->loadHash($this->getPricingFromDateQuery($code, $date));

        if (!$data) {
            throw LppDatabaseException::invalidRequestResult();
        }

        return new CLPPDatedPricing($data);
    }

    /**
     * Format the SQL query for loading the pricing active at the given date, for the given code
     *
     * @param string $code
     * @param string $date
     *
     * @return CRequest
     */
    public function getPricingFromDateQuery(string $code, string $date = null): CRequest
    {
        if (!$date) {
            $date = CMbDT::date();
        }

        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::TABLE_NAME);
        $query->addWhere(
            [
                $this->ds->prepare('`CODE_TIPS` = ?', $code),
                $this->ds->prepare('`DEBUTVALID` <= ?', $date),
                $this->ds->prepare('`FINHISTO` >= ? OR `FINHISTO` IS NULL', $date),
            ]
        );

        return $query;
    }

    /**
     * Load all the pricings for the given LPP code
     *
     * @param string $code
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function loadListFromCode(string $code): array
    {
        $results = $this->loadList($this->getPricingsForCodeQuery($code));

        $pricings = [];
        if ($results) {
            foreach ($results as $_result) {
                $_pricing                        = new CLPPDatedPricing($_result);
                $pricings[$_pricing->begin_date] = $_pricing;
            }
        }

        return $pricings;
    }

    /**
     * Format the SQL query for loading all the pricings for the given code
     *
     * @param string $code
     *
     * @return CRequest
     */
    public function getPricingsForCodeQuery(string $code): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::TABLE_NAME);
        $query->addWhere($this->ds->prepare('`CODE_TIPS` = ?', $code));
        $query->addOrder('`DEBUTVALID` DESC');

        return $query;
    }

    /**
     * Load the last pricing entry for the given code
     *
     * @param string      $code
     * @param string|null $date
     *
     * @return CLPPDatedPricing
     * @throws LppDatabaseException
     */
    public function loadLastPricingForCode(string $code, string $date = null): CLPPDatedPricing
    {
        $data = $this->loadHash($this->getLastPricingForCodeQuery($code, $date));

        if (!$data) {
            throw LppDatabaseException::invalidRequestResult();
        }

        return new CLPPDatedPricing($data);
    }

    /**
     * Format the SQL query for getting the last pricing entry for the given code
     *
     * @param string      $code
     * @param string|null $date
     *
     * @return CRequest
     */
    public function getLastPricingForCodeQuery(string $code, string $date = null): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::TABLE_NAME);

        $query->addWhere($this->ds->prepare('`CODE_TIPS` = ?', $code));
        if ($date) {
            $query->addWhere($this->ds->prepare('`DEBUTVALID` <= ?', $date));
            $query->addWhere($this->ds->prepare('`FINHISTO` >= ? OR `FINHISTO` IS NULL', $date));
        }

        $query->setLimit('0, 1');
        $query->addOrder('`DEBUTVALID` DESC');

        return $query;
    }
}
