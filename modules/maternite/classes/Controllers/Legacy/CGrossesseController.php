<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;

class CGrossesseController extends CLegacyController
{
    /**
     * Close pregnancies at 43 WA (week of amenorrhea)
     */
    public function ajax_close_pregnancies(): void
    {
        CCanDo::checkEdit();
        $group_id = CView::get("group_id", "ref class|CGroups");
        CView::checkin();

        $grossesse = new CGrossesse();
        $ds = $grossesse->getDS();

        $where = [
            "group_id" => $ds->prepare('= ?', $group_id),
            "active"   => $ds->prepare('= ?', '1'),
        ];

        $grossesses = $grossesse->loadList($where);

        $counter = 0;

        foreach ($grossesses as $_grossesse) {
            $_grossesse->updateFormFields();

            if ($_grossesse->_semaine_grossesse >= 43) {
                $_grossesse->active = 0;
                $_grossesse->datetime_cloture = $_grossesse->datetime_accouchement ?: CMbDT::dateTime();
                $_grossesse->store();

                $counter++;
            }
        }

        CAppUI::setMsg(CAppUI::tr("CGrossesse-msg-modify") . " x $counter", UI_MSG_OK);
        echo CAppUI::getMsg();
        $this->rip();
    }

    /**
     * @throws Exception
     */
    public function updateResuscitatorsList(): void
    {
        CCanDo::checkEdit();

        $naissance_id = CView::get('naissance_id', "ref class|CNaissance");

        CView::checkin();

        $naissance = new CNaissance();
        $naissance->load($naissance_id);
        $naissance->loadRefsResuscitators();

        $this->renderSmarty('inc_resuscitators', [
            'naissance' => $naissance,
        ]);
    }

    /**
     * Display Pregnancy Bind view
     *
     * @return void
     * @throws Exception
     */
    public function bindPregnancy(): void
    {
        $this->checkPermRead();

        $object_guid    = CView::get("object_guid", "str");
        $parturiente_id = CView::get("parturiente_id", "ref class|CPatient");
        $grossesse_id   = CView::get("grossesse_id", "ref class|CGrossesse");

        CView::checkin();

        $this->renderSmarty(
            "inc_bind_grossesse",
            [
                "object_guid"    => $object_guid,
                "parturiente_id" => $parturiente_id,
                "grossesse_id"   => $grossesse_id,
            ]
        );
    }
}
