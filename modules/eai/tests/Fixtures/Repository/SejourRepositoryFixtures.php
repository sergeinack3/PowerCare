<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Fixtures\Repository;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Use for test algorithms used in interop to record the Patient
 */
class SejourRepositoryFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const REF_SEJOUR = 'ref_primary_sejour_repository';
    /** @var string */
    public const REF_MULTIPLE_SEJOUR = 'ref_multiple_sejour_repository';

    /** @var string */
    public const PRIMARY_SEJOUR_DATE_ENTREE = "2022-01-20 10:00:00";

    /** @var string */
    public const TAG_NDA = 'tag_nda';

    /** @var CMediusers */
    public static $mediusers;

    /**
     * @inheritDoc
     * @throws FixturesException|CModelObjectException
     */
    public function load()
    {
        // Patient
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        // sejour with NDA | Object_id | Current date | date extended
        $primary_sejour = self::makePrimarySejour($patient->_id);
        $this->store($primary_sejour, self::REF_SEJOUR);

        // add nda to sejour
        $this->generateNDA($primary_sejour);

        // multiple sejour in same date
        $patient = CPatient::getSampleObject();
        $this->store($patient);

        $sejour_1 = self::makePrimarySejour($patient->_id, null, true);
        $this->store($sejour_1, self::REF_MULTIPLE_SEJOUR);
        $this->generateNDA($sejour_1);

        $sejour_2         = self::makePrimarySejour($patient->_id);
        $sejour_2->entree = $sejour_2->entree_prevue = CMbDT::dateTime('-1 DAYS', $sejour_1->entree);
        $sejour_2->sortie = $sejour_2->sortie_prevue = CMbDT::dateTime('+6 HOURS', $sejour_2->entree);
        $this->store($sejour_2);
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['eai-repository'];
    }

    /**
     * @return CSejour
     * @throws Exception
     */
    public static function makePrimarySejour(string $patient_id, ?string $datetime = self::PRIMARY_SEJOUR_DATE_ENTREE, bool $new_mediusers = false): CSejour
    {
        if (!$datetime) {
            $datetime = self::PRIMARY_SEJOUR_DATE_ENTREE;
        }

        $sejour               = new CSejour();
        $sejour->patient_id   = $patient_id;
        $sejour->group_id     = CGroups::loadCurrent()->_id;
        $sejour->praticien_id = self::getMediusers($new_mediusers)->_id;
        $sejour->entree       = $sejour->entree_prevue = $datetime;
        $sejour->sortie       = $sejour->sortie_prevue = CMbDT::dateTime('+2 DAYS', $datetime);
        $sejour->type         = 'comp';

        return $sejour;
    }

    /**
     * @param CSejour $sejour
     *
     * @return void
     * @throws FixturesException
     */
    public function generateNDA(CSejour $sejour): void
    {
        $idex = CIdSante400::getMatch($sejour->_class, self::TAG_NDA, null, $sejour->_id);
        if (!$idex->_id) {
            $idex->id400 = CMbSecurity::generateUUID();
            $this->store($idex);
        }
    }

    /**
     * @return CMediusers
     * @throws FixturesException
     */
    public static function getMediusers(bool $new = false): CMediusers
    {
        if (!self::$mediusers || $new) {
            self::$mediusers = (new self())->getUser(false);
        }

        return self::$mediusers;
    }
}
