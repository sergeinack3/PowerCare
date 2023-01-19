<?php

/**
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Interop\Eai\CAsipImport;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use stdClass;

class AdministrationControllerLegacy extends CLegacyController
{
    public function updateASIPDb(): void
    {
        $this->checkPermEdit();

        $codes_asip = CANSValueSet::load('author_specialty_dmp');

        $specialities = [];
        foreach ($codes_asip as $data) {
            $system  = $data['codeSystem'];
            $libelle = $data['displayName'];
            $code    = $data['code'];

            $id                = "$system-$code";
            $specialities[$id] = $object = new stdClass();
            $object->code      = $code;
            $object->oid       = $system;
            $object->libelle   = $libelle;
        }

        $asip   = new CAsipImport();
        $report = $asip->updateDatabase($specialities);

        $this->renderSmarty('report/inc_report', ['report' => $report]);
    }
}
