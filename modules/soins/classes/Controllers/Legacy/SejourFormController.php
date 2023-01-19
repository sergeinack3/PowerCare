<?php

/**
 * @package Mediboard\soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

class SejourFormController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function displayFormsWithContext(): void
    {
        $this->checkPermRead();

        $sejour_id = CView::get('sejour_id', 'ref class|CSejour');

        CView::checkin();

        $sejour = new CSejour();
        $sejour->load($sejour_id);

        $patient = $sejour->loadRefPatient();

        $this->renderSmarty(
            'vw_sejour_forms.tpl',
            [
                'sejour' => $sejour,
                'patient' => $patient,
            ]
        );
    }
}
