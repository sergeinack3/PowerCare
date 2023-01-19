<?php

/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

/**
 * Fixtures for PMSI Exporter Module
 */
class PSMIExporterFixtures extends Fixtures
{
    public const AAFA001 = "AAFA001";
    public const AAFA002 = "AAFA002";

    public const TAG_PSMI_OPERATION = "TAG_PMSI_OP_1";
    public const TAG_PSMI_SEJOUR    = "TAG_PMSI_SEJ_1";
    public const TAG_PSMI_PATIENT   = "TAG_PMSI_PAT_1";

    public const TAG_PSMI_OPERATION_2 = "TAG_PMSI_OP_2";
    public const TAG_PSMI_SEJOUR_2    = "TAG_PMSI_SEJ_2";
    public const TAG_PSMI_PATIENT_2   = "TAG_PMSI_PAT_2";

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function load(): void
    {
        $patient = $this->generatePatient(self::TAG_PSMI_PATIENT);
        $sejour  = $this->generateSejour($patient, self::TAG_PSMI_SEJOUR);
        $this->generatePMSIOperations($sejour, self::TAG_PSMI_OPERATION, self::AAFA001, "1");


        $patient_2 = $this->generatePatient(self::TAG_PSMI_PATIENT_2);
        $sejour_2  = $this->generateSejour($patient_2, self::TAG_PSMI_SEJOUR_2);
        $this->generatePMSIOperations($sejour_2, self::TAG_PSMI_OPERATION_2, self::AAFA002, "", "1");
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function generatePatient(string $tag): CPatient
    {
        /** @var CPatient $patient */
        $patient = CStoredObject::getSampleObject(CPatient::class);
        $this->store($patient, $tag);

        return $patient;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function generateSejour(CPatient $patient, string $tag): CSejour
    {
        /** @var CSejour $sejour */
        $sejour                = CStoredObject::getSampleObject(CSejour::class);
        $sejour->type          = "comp";
        $sejour->group_id      = CGroups::getCurrent()->_id;
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $this->getUser()->_id;
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+1 day");
        $this->store($sejour, $tag);

        return $sejour;
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function generatePMSIOperations(
        CSejour $sejour,
        string $tag,
        string $actes = "",
        string $labo = "?",
        string $anapath = "?"
    ): void {
        /** @var COperation $operation */
        $operation             = CStoredObject::getSampleObject(COperation::class);
        $operation->sejour_id  = $sejour->_id;
        $operation->sejour_id  = $sejour->_id;
        $operation->chir_id    = $this->getUser()->_id;
        $operation->codes_ccam = $actes;
        $operation->date       = CMbDT::date();
        $operation->labo       = $labo;
        $operation->anapath    = $anapath;

        $this->store($operation, $tag);
    }
}
