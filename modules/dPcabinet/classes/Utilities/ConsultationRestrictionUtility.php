<?php

/**
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Utilities;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;

/**
 * Utilities based on the new management : town consultations are no longer allowed on Mediboard
 */
class ConsultationRestrictionUtility
{
    /**
     * Check if the consultation creation is allowed
     * Rules, city consultations are only allowed for :
     * - anesthesists
     * - hospital stays
     * - editing (/= creating)
     * - estab with config allowing city consultations
     * - TAMM
     * @param CConsultation $consultation
     *
     * @return bool
     */
    public static function isConsultationAllowed(CConsultation $consultation): bool
    {
        if (!isset($consultation->_ref_praticien->_id)) {
            $consultation->loadRefPraticien();
        }

        if (
            !$consultation->_id &&
            !CModule::getActive("oxCabinet") &&
            !$consultation->_ref_praticien->isAnesth() &&
            !$consultation->sejour_id &&
            !CAppUI::gconf("dPcabinet CConsultation allow_city_consultation")
        ) {
            return false;
        }

        return true;
    }
}
