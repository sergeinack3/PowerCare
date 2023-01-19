<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Repository;

use Exception;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;

/**
 * An abstract class for the Lpp repositories
 *
 * For typing purpose in PhpStorm, each
 */
abstract class LppRepository
{
    protected const DSN_NAME = 'lpp';

    protected CSQLDataSource $ds;

    /**
     * @param CSQLDataSource|null $ds
     */
    protected function __construct(CSQLDataSource $ds = null)
    {
        if (!$ds) {
            $ds = CSQLDataSource::get(self::DSN_NAME);
        }

        $this->ds = $ds;
    }

    /**
     * Must return an instance of the repository.
     *
     * @return static
     */
    abstract public static function getInstance();

    /**
     * Overwrite the new singleton instance with a new one that uses the given datasource
     *
     * @param CSQLDataSource $ds
     */
    public static function setDatasource(CSQLDataSource $ds): void
    {
        static::$instance = new static($ds);
    }

    /**
     * Execute the given SQL request and returns the first result as an array
     *
     * @param CRequest $query
     *
     * @return array|null
     * @throws LppDatabaseException
     */
    protected function loadHash(CRequest $query): ?array
    {
        try {
            $data = $this->ds->loadHash($query->makeSelect());
        } catch (Exception $e) {
            throw LppDatabaseException::databaseError($e);
        }

        if (!$data) {
            return null;
        }

        return $data;
    }

    /**
     * Execute the given SQL request and returns the results
     *
     * @param CRequest $query
     *
     * @return array|null
     * @throws LppDatabaseException
     */
    protected function loadList(CRequest $query): ?array
    {
        try {
            $list = $this->ds->loadList($query->makeSelect());
        } catch (Exception $e) {
            throw LppDatabaseException::databaseError($e);
        }

        if ($list === false || !is_array($list)) {
            throw LppDatabaseException::invalidRequestResult();
        }

        return $list;
    }
}
