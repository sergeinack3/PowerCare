<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Repository;

use Exception;
use Ox\Core\CRequest;
use Ox\Mediboard\Lpp\CLPPChapter;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;

/**
 * A repository class for the Lpp chapters.
 * Contains different methods for loading the chapters of the Lpp database
 */
class LppChapterRepository extends LppRepository
{
    private const CHAPTERS_TABLE = 'arborescence';

    protected static ?LppChapterRepository $instance;

    /**
     * Returns the singleton instance of the repository
     *
     * Not defined in the parent class for typing purpose
     *
     * @return static
     */
    public static function getInstance(): self
    {
        if (!isset(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Return the LppChapter with the given id from the database
     *
     * @param string $id
     *
     * @return CLPPChapter
     * @throws LppDatabaseException
     */
    public function loadChapter(string $id): ?CLPPChapter
    {
        $data = $this->loadHash($this->getLoadChapterQuery($id));

        if (!$data) {
            return null;
        }

        return new CLPPChapter($data);
    }

    /**
     * Returns the SQL query for loading a LppChapter with the given id
     *
     * @param string $id
     *
     * @return CRequest
     */
    public function getLoadChapterQuery(string $id): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::CHAPTERS_TABLE);
        $query->addWhere($this->ds->prepare('`ID` = ?', $id));

        return $query;
    }

    /**
     * Loads all the LppChapters with the given parent
     *
     * @param string $parent_id
     *
     * @return array
     * @throws LppDatabaseException
     */
    public function loadChaptersFromParent(string $parent_id): array
    {
        $results = $this->loadList($this->getChaptersFromParentQuery($parent_id));

        $chapters = [];
        foreach ($results as $data) {
            $chapters[] = new CLPPChapter($data);
        }

        return $chapters;
    }

    /**
     * Returns the SQL query for loading the LppChapters with the given parent_id
     *
     * @param string $parent_id
     *
     * @return CRequest
     */
    public function getChaptersFromParentQuery(string $parent_id): CRequest
    {
        $query = new CRequest();
        $query->addSelect('*');
        $query->addTable(self::CHAPTERS_TABLE);
        $query->addWhere($this->ds->prepare('`PARENT` = ?', $parent_id));
        $query->addOrder('`INDEX` ASC');

        return $query;
    }
}
