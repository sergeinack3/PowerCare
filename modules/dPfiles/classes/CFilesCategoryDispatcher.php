<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CFilesCategoryDispatcher
{
    private const TYPE_FILE = 'files';
    private const TYPE_CR   = 'crs';
    private const TYPES     = [self::TYPE_FILE, self::TYPE_CR];

    /** @var CFilesCategory */
    private $cat;

    /** @var array */
    private $stats = [];

    /** @var CFilesCategory */
    private $new_cat;

    /** @var CGroups */
    private $group;

    /** @var array */
    private $msg = [];

    public function __construct(CFilesCategory $cat)
    {
        if (!$cat->getPerm(PERM_EDIT)) {
            throw new CMbException('common-msg-You are not allowed to access this information (%s)', $cat);
        }

        $this->cat = $cat;
    }

    public function getStats(): array
    {
        $files = $this->getDistinctGroupsUsers(self::TYPE_FILE);
        $crs   = $this->getDistinctGroupsUsers(self::TYPE_CR);

        $docs = array_merge($files, $crs);

        $groups = (new CGroups())->loadAll(CMbArray::pluck($docs, 'group_id'));
        $users  = (new CMediusers())->loadAll(CMbArray::pluck($docs, 'user_id'));

        foreach ($docs as $data) {
            if (!isset($this->stats[$data['group_id']])) {
                $this->stats[$data['group_id']] = [
                    'object' => $groups[$data['group_id']],
                    'users'  => [],
                    'count'  => $data['count'],
                ];
            } else {
                $this->stats[$data['group_id']]['count'] += $data['count'];
            }

            if (!isset($this->stats[$data['group_id']]['users'][$data['user_id']])) {
                $this->stats[$data['group_id']]['users'][$data['user_id']] = [
                    'object' => $users[$data['user_id']],
                    'count'  => $data['count'],
                ];
            }
        }

        return $this->stats;
    }

    public function dispatch(CGroups $group): array
    {
        if (!$group->getPerm(PERM_EDIT)) {
            throw new CMbException('common-msg-You are not allowed to access this information (%s)', $group);
        }

        $this->group = $group;

        if (!$this->cloneOldCat()) {
            return $this->msg;
        }

        $this->moveFiles();

        return $this->msg;
    }


    protected function getDistinctGroupsUsers(string $type = 'files', ?CGroups $group = null): array
    {
        if (!in_array($type, self::TYPES)) {
            return [];
        }

        $ds = $this->cat->getDS();

        $query = new CRequest();
        $query->addSelect(['FC.group_id', 'M.user_id', 'COUNT(*) as count']);
        $query->addTable('files_category C');
        $query->addLJoin(
            [
                ($type === 'files' ? 'files_mediboard' : 'compte_rendu') . ' F ON (F.file_category_id = C.file_category_id)',
                'users_mediboard M ON (F.author_id = M.user_id)',
                'functions_mediboard FC ON (M.function_id = FC.function_id)',
            ]
        );
        $query->addWhere(
            [
                'C.file_category_id' => $ds->prepare('= ?', $this->cat->_id),
                'FC.group_id'        => ($group) ? $ds->prepare('= ?', $group->_id) : 'IS NOT NULL',
            ]
        );
        $query->addGroup(['FC.group_id', 'M.user_id']);

        return $ds->loadList($query->makeSelect());
    }

    private function cloneOldCat(): bool
    {
        $this->new_cat           = new CFilesCategory();
        $this->new_cat->nom      = $this->cat->nom;
        $this->new_cat->group_id = $this->group->_id;
        $this->new_cat->class    = $this->cat->class;
        $this->new_cat->loadMatchingObjectEsc();

        if ($this->new_cat->_id) {
            $this->msg[] = ['CFilesCategory-msg-found', UI_MSG_OK];

            return true;
        }

        $this->new_cat->cloneFrom($this->cat);
        $this->new_cat->group_id = $this->group->_id;

        if ($msg = $this->new_cat->store()) {
            $this->msg[] = [$msg, UI_MSG_WARNING];

            return false;
        }

        $this->msg[] = ['CFilesCategory-msg-create', UI_MSG_OK];

        return true;
    }

    private function moveFiles(): void
    {
        // Slave for SELECT queries
        CView::enforceSlave();

        $file_stats = $this->getDistinctGroupsUsers(self::TYPE_FILE, $this->group);
        $cr_stats   = $this->getDistinctGroupsUsers(self::TYPE_CR, $this->group);

        $stats = array_merge($file_stats, $cr_stats);

        $files_to_move = $this->getDocumentsToMove(CMbArray::pluck($stats, 'user_id'), self::TYPE_FILE);
        $crs_to_move   = $this->getDocumentsToMove(CMbArray::pluck($stats, 'user_id'), self::TYPE_CR);

        CView::disableSlave();

        // No risk to errase files with cr from the same ids because array_merge reindex numeric keys
        $this->moveDocuments(array_merge($files_to_move, $crs_to_move));
    }

    /**
     * @param int[]  $user_ids
     * @param string $type
     *
     * @return array
     * @throws Exception
     */
    private function getDocumentsToMove(array $user_ids, string $type = self::TYPE_FILE): array
    {
        $file = ($type === self::TYPE_FILE) ? new CFile() : new CCompteRendu();
        $ds   = $file->getDS();

        $where = [
            'file_category_id' => $ds->prepare('= ?', $this->cat->_id),
            'author_id'        => $ds->prepareIn($user_ids),
        ];

        return $file->loadList($where);
    }

    /**
     * @param CFile[]|CCompteRendu[] $documents
     */
    private function moveDocuments(array $documents): void
    {
        foreach ($documents as $doc) {
            $doc->file_category_id = $this->new_cat->_id;

            if ($msg = $doc->store()) {
                $this->msg[] = [$msg, UI_MSG_WARNING];
                continue;
            }

            $this->msg[] = [$doc->_class . '-msg-modify', UI_MSG_OK];
        }
    }
}
