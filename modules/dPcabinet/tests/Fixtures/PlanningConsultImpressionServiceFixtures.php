<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Tests\Fixtures\Fixtures;

class PlanningConsultImpressionServiceFixtures extends Fixtures
{
    public const PLANNING_CONSULT_IMPRESSION_SERVICE_PLAGE_CONSULT = "planning_consult_impression_service_plage_consult";
    public const PLANNING_CONSULT_IMPRESSION_SERVICE_CONSULT       = "planning_consult_impression_service_consult";

    /**
     * @inheritDoc
     */
    public function load()
    {
        $chir = $this->getUser(false);

        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $chir->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:15:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "12:00:00";
        $plage_consult->libelle = "planning consult impression service";
        $this->store($plage_consult, self::PLANNING_CONSULT_IMPRESSION_SERVICE_PLAGE_CONSULT);

        $consult                  = new CConsultation();
        $consult->plageconsult_id = $plage_consult->_id;
        $consult->heure           = "10:00:00";
        $consult->chrono          = "32";
        $consult->owner_id        = $chir->_id;
        $consult->facture         = 1;
        $this->store($consult, self::PLANNING_CONSULT_IMPRESSION_SERVICE_CONSULT);
    }
}
