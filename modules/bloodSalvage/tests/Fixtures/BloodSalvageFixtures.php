<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage\Tests\Fixtures;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\CCellSaver;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Personnel\CAffectationPersonnel;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Qualite\CEiCategorie;
use Ox\Mediboard\Qualite\CEiItem;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Fixture for rosp stats
 */
class BloodSalvageFixtures extends Fixtures implements GroupFixturesInterface
{
    public const TAG_BLOOD_SALVAGE1         = 'blood_salvage-blood_salvage-1';
    public const TAG_BLOOD_SALVAGE2         = 'blood_salvage-blood_salvage-2';
    public const TAG_AFFECTATION_PERSONNEL1 = 'blood_salvage-affectation_personnel-1';
    public const TAG_AFFECTATION_PERSONNEL2 = 'blood_salvage-affectation_personnel-2';
    public const TAG_AFFECTATION_PERSONNEL3 = 'blood_salvage-affectation_personnel-3';
    public const TAG_AFFECTATION_PERSONNEL4 = 'blood_salvage-affectation_personnel-4';
    public const TAG_USER                   = 'blood_salvage-user-1';
    public const TAG_OPERATION              = 'blood_salvage-operation-1';
    public const TAG_PATIENT                = 'blood_salvage-patient-1';
    public const TAG_CELL_SAVER             = 'blood_salvage-cell_saver-1';
    public const TAG_TYPE_EI                = 'blood_salvage-type_ei-1';
    public const TAG_ITEM_EI1               = 'blood_salvage-item_ei-1';
    public const TAG_ITEM_EI2               = 'blood_salvage-item_ei-2';

    /** @var string|null */
    public $date = null;
    /** @var string|null */
    public $datetime = null;
    /** @var int|string|null */
    public $year = null;

    public static function getGroup(): array
    {
        return ['blood_salvage'];
    }

    /**
     * @inheritDoc
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $user           = $this->generateUser(self::TAG_USER);
        $patient        = $this->generatePatient(self::TAG_PATIENT);
        $sejour         = $this->generateSejour($patient, $user);
        $operation      = $this->generateOperation($sejour, $user, self::TAG_OPERATION);
        $categorie      = $this->generateEiCategorie();
        $items          = [
            $this->generateEiItem($categorie, self::TAG_ITEM_EI1),
            $this->generateEiItem($categorie, self::TAG_ITEM_EI2),
        ];
        $type_ei        = $this->generateTypeEi($items, self::TAG_TYPE_EI);
        $cell_saver     = $this->generateCellSaver(self::TAG_CELL_SAVER);
        $blood_salvage1 = $this->generateBloodSalvage($operation, $cell_saver, $type_ei, self::TAG_BLOOD_SALVAGE1);
        $blood_salvage2 = $this->generateBloodSalvage($operation, $cell_saver, $type_ei, self::TAG_BLOOD_SALVAGE2);
        $personnel      = $this->generatePersonnel($user);
        // éviter une collision du personnel
        $debut = CMbDT::dateTime();
        $fin   = CMbDT::dateTime('+2 HOURS');
        $this->generateAffectationPersonnel(
            $blood_salvage1,
            $personnel,
            $debut,
            $fin,
            self::TAG_AFFECTATION_PERSONNEL3
        );
        $debut = CMbDT::dateTime('+3 HOURS');
        $fin   = CMbDT::dateTime('+5 HOURS');
        $this->generateAffectationPersonnel(
            $blood_salvage1,
            $personnel,
            $debut,
            $fin,
            self::TAG_AFFECTATION_PERSONNEL4
        );

        $this->generateAffectationPersonnel($blood_salvage2, $personnel, null, null, self::TAG_AFFECTATION_PERSONNEL1);
        $this->generateAffectationPersonnel($blood_salvage2, $personnel, null, null, self::TAG_AFFECTATION_PERSONNEL2);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     * @throws Exception
     */
    private function generateUser(?string $tag = null): CMediusers
    {
        /** @var CMediusers $user */
        $user = $this->getUser(false);
        $this->store($user, $tag);

        return $user;
    }

    /**
     * @throws FixturesException
     */
    private function generateAffectationPersonnel(
        CBloodSalvage $blood_salvage,
        CPersonnel $personnel,
        ?string $debut = null,
        ?string $fin = null,
        ?string $tag = null
    ): void {
        $affectation_personnel               = new CAffectationPersonnel();
        $affectation_personnel->object_id    = $blood_salvage->_id;
        $affectation_personnel->object_class = $blood_salvage->_class;
        $affectation_personnel->personnel_id = $personnel->_id;
        $affectation_personnel->realise      = '0';
        $affectation_personnel->debut        = $debut;
        $affectation_personnel->fin          = $fin;
        $this->store($affectation_personnel, $tag);
    }

    /**
     * @throws FixturesException
     */
    private function generateBloodSalvage(
        COperation $operation,
        CCellSaver $cell_saver,
        CTypeEi $type_ei,
        ?string $tag = null
    ): CBloodSalvage {
        $blood_salvage                     = new CBloodSalvage();
        $blood_salvage->operation_id       = $operation->_id;
        $blood_salvage->cell_saver_id      = $cell_saver->_id;
        $blood_salvage->type_ei_id         = $type_ei->_id;
        $blood_salvage->wash_volume        = 2;
        $blood_salvage->recuperation_start = CMbDT::dateTime('-2 HOURS');
        $blood_salvage->recuperation_end   = CMbDT::dateTime();
        $blood_salvage->transfusion_start  = CMbDT::dateTime('-2 HOURS');
        $blood_salvage->transfusion_end    = CMbDT::dateTime();
        $this->store($blood_salvage, $tag);

        return $blood_salvage;
    }

    /**
     * @throws FixturesException
     */
    private function generatePersonnel(CMediusers $user, ?string $tag = null): CPersonnel
    {
        $personnel              = new CPersonnel();
        $personnel->user_id     = $user->_id;
        $personnel->emplacement = 'reveil';
        $this->store($personnel, $tag);

        return $personnel;
    }

    /**
     * @throws FixturesException
     */
    private function generateOperation(CSejour $sejour, CMediusers $user, string $tag = null): COperation
    {
        $operation                 = new COperation();
        $operation->sejour_id      = $sejour->_id;
        $operation->chir_id        = $user->_id;
        $operation->date           = CMbDT::date();
        $operation->time_operation = '01:00:00';
        $this->store($operation, $tag);

        return $operation;
    }

    /**
     * @throws FixturesException
     */
    private function generateSejour(CPatient $patient, CMediusers $user): CSejour
    {
        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $user->_id;
        $sejour->group_id      = CGroups::getCurrent()->_id;
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->entree        = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime('+1 DAY');
        $sejour->libelle       = uniqid();
        $this->store($sejour);

        return $sejour;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generatePatient(?string $tag = null): CPatient
    {
        /** @var CPatient $patient */
        $patient            = CPatient::getSampleObject();
        $patient->naissance = CMbDT::date('-15 years');
        $this->store($patient, $tag);

        return $patient;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateCellSaver(?string $tag = null): CCellSaver
    {
        /** @var CCellSaver $cell_saver */
        $cell_saver = CCellSaver::getSampleObject();
        $this->store($cell_saver, $tag);

        return $cell_saver;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateTypeEi(array $items, ?string $tag = null): CTypeEi
    {
        /** @var CTypeEi $type_ei */
        $type_ei             = CTypeEi::getSampleObject();
        $type_ei->evenements = implode('|', CMbArray::pluck($items, '_id'));
        $this->store($type_ei, $tag);

        return $type_ei;
    }

    /**
     * @throws FixturesException
     */
    private function generateEiItem(CEiCategorie $categorie, ?string $tag = null): CEiItem
    {
        $item                  = new CEiItem();
        $item->ei_categorie_id = $categorie->_id;
        $item->nom             = 'BS Fixture';
        $this->store($item, $tag);

        return $item;
    }

    /**
     * @throws FixturesException
     */
    private function generateEiCategorie(): CEiCategorie
    {
        $categorie      = new CEiCategorie();
        $categorie->nom = 'BS Fixture';
        $this->store($categorie);

        return $categorie;
    }
}
