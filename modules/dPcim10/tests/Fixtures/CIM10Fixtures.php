<?php

/**
 * @package Mediboard\Ambu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 */
class CIM10Fixtures extends Fixtures implements GroupFixturesInterface
{
    public const FAVORIS_USER            = 'favoris_user';
    public const FAVORIS_USER_NOT_DELETE = 'favoris_user_not_delete';
    public const SEJOUR_WITH_CODE        = 'sejour_with_code';
    public const USER_CIM10              = "user_cim10";
    public const USER_CIM10_WITH_FAVORIS = "user_cim10_with_favoris";

    /**
     * @throws FixturesException|CModelObjectException
     */
    public function load(): void
    {
        $user = $this->getUser(false);
        $this->store($user, self::USER_CIM10);
        $favoris               = new CFavoriCIM10();
        $favoris->favoris_user = $user->_id;
        $favoris->favoris_code = "P00";
        $this->store($favoris, self::FAVORIS_USER);

        $user2 = $this->getUser();
        $this->store($user, self::USER_CIM10_WITH_FAVORIS);
        $favoris               = new CFavoriCIM10();
        $favoris->favoris_user = $user2->_id;
        $favoris->favoris_code = "P00";
        $this->store($favoris, self::FAVORIS_USER_NOT_DELETE);

        $sejour = new CSejour();
        /** @var CPatient $patient */
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $user->_id;
        $sejour->group_id      = CGroups::loadCurrent()->_id;
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->entree        = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime('+1 HOURS');
        $sejour->libelle       = uniqid();
        $sejour->DP            = "A00";
        $this->store($sejour, self::SEJOUR_WITH_CODE);
    }

    public static function getGroup(): array
    {
        return ['favoriCIM10'];
    }
}
