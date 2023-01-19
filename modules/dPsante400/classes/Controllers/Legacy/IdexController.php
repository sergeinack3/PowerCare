<?php

/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CIdSante400;

class IdexController extends CLegacyController
{
    /**
     * @return void
     * @throws Exception
     */
    public function listIdentifiants(): void
    {
        $this->checkPermRead();

        $idex_id      = CView::get("idex_id", "ref class|CIdSante400");
        $page         = CView::get("page", "num default|0");
        $object_class = CView::get("object_class", "str");
        $object_id    = CView::get("object_id", ($object_class ? "ref class|$object_class" : "num"));
        $tag          = CView::get("tag", "str");
        $id400        = CView::get("id400", "str");

        CView::checkin();
        CView::enableSlave();

        if (!$object_id && !$object_class && !$tag && !$id400) {
            CAppUI::stepMessage(UI_MSG_WARNING, "No filter");
            CApp::rip();
        }

        $idSante400 = new CIdSante400();
        $ds         = $idSante400->getDS();

        // Chargement de la liste des id4Sante400 pour le filtre
        $filter               = new CIdSante400();
        $filter->object_id    = $object_id;
        $filter->object_class = $object_class;
        $filter->nullifyEmptyFields();

        // Chargement de la cible si objet unique
        $target = null;
        if ($filter->object_id && $filter->object_class) {
            $target = CMbObject::getInstance($filter->object_class);
            $target->load($filter->object_id);
        }

        $where = [];
        if ($object_id) {
            $where[] = $ds->prepare('object_id = ?', $object_id);
        }

        if ($object_class) {
            $where[] = $ds->prepare('object_class = ?', $object_class);
        }
        if ($id400) {
            $where[] = $ds->prepare('id400 = ?', $id400);
        }

        if ($tag) {
            $where[] = $ds->prepare('tag = ?', $tag);
        }

        $step = 25;

        $idexs = $idSante400->loadList($where, null, "$page, $step");

        CStoredObject::massLoadFwdRef($idexs, "object_id");
        foreach ($idexs as $_idex) {
            $_idex->getSpecialType();
        }

        $total_idexs = count($idexs);

        $this->renderSmarty('inc_list_identifiants', [
            'idexs'                 => $idexs,
            'total_idexs'           => $total_idexs,
            'filter'                => $filter,
            'idex_id'               => $idex_id,
            'page'                  => $page,
            'target'                => $target,
            'looking_for_duplicate' => false,
        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function listDuplicated(): void
    {
        $this->checkPermRead();

        $idex_id      = CView::get("idex_id", "ref class|CIdSante400");
        $page         = CView::get("page", "num default|0");
        $object_class = CView::get("object_class", "str");
        $object_id    = CView::get("object_id", ($object_class ? "ref class|$object_class" : "num"));
        $tag          = CView::get("tag", "str");
        $id400        = CView::get("id400", "str");

        CView::checkin();
        CView::enableSlave();

        if (!$object_id && !$object_class && !$tag && !$id400) {
            CAppUI::stepMessage(UI_MSG_WARNING, "No filter");
            CApp::rip();
        }

        $idSante400 = new CIdSante400();
        $ds         = $idSante400->getDS();

        // Chargement de la liste des id4Sante400 pour le filtre
        $filter               = new CIdSante400();
        $filter->object_id    = $object_id;
        $filter->object_class = $object_class;
        $filter->nullifyEmptyFields();

        // Chargement de la cible si objet unique
        $target = null;
        if ($filter->object_id && $filter->object_class) {
            $target = CMbObject::getInstance($filter->object_class);
            $target->load($filter->object_id);
        }

        $where = [];
        if ($object_id) {
            $where[] = $ds->prepare('object_id = ?', $object_id);
        }

        if ($object_class) {
            $where[] = $ds->prepare('object_class = ?', $object_class);
        }
        if ($id400) {
            $where[] = $ds->prepare('id400 = ?', $id400);
        }

        if ($tag) {
            $where[] = $ds->prepare('tag = ?', $tag);
        }

        $group_by = 'object_class, object_id, tag';

        $step = 25;

        $request = new CRequest();
        $request->addSelect(
            "id_sante400_id,
             object_class,
              object_id,
              tag,
              GROUP_CONCAT(last_update SEPARATOR '|') AS last_update,
              GROUP_CONCAT(datetime_create SEPARATOR '|') AS datetime_create,
              GROUP_CONCAT(id400 SEPARATOR '|') AS id400,
              COUNT(*) as nb_idex"
        );
        $request->addTable($idSante400->getSpec()->table);
        $request->addWhere($where);
        $request->addGroup($group_by);
        $request->setLimit("$page, $step");
        $request->addOrder('last_update DESC');
        $request->addHaving('nb_idex > 1');

        $results = $ds->loadList($request->makeSelect());
        $idexs   = [];

        foreach ($results as $_idex) {
            $new_id400                   = new CIdSante400();
            $new_id400->id_sante400_id   = $_idex['id_sante400_id'];
            $new_id400->object_class     = $_idex['object_class'];
            $new_id400->object_id        = $_idex['object_id'];
            $new_id400->tag              = $_idex['tag'];
            $new_id400->_datetime_create = explode('|', $_idex['datetime_create']);
            $new_id400->_id400           = explode('|', $_idex['id400']);
            $new_id400->_last_update     = explode('|', $_idex['last_update']);
            $new_id400->_nb_idex         = $_idex['nb_idex'];
            $idexs[]                     = $new_id400;
        }

        CStoredObject::massLoadFwdRef($idexs, "object_id");
        foreach ($idexs as $_idex) {
            $_idex->getSpecialType();
        }

        $total_idexs = count($idexs);

        $this->renderSmarty('inc_list_identifiants', [
            'idexs'                 => $idexs,
            'total_idexs'           => $total_idexs,
            'filter'                => $filter,
            'idex_id'               => $idex_id,
            'page'                  => $page,
            'target'                => $target,
            'looking_for_duplicate' => true,
        ]);
    }
}
