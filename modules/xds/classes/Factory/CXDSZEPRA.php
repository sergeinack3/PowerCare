<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Factory;

use Ox\Core\CMbException;
use Ox\Interop\Xds\Structure\CXDSExtrinsicObject;
use Ox\Interop\Xds\Structure\CXDSRegistryPackage;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CXDSSisra
 *
 * @package Ox\Interop\Xds\Factory
 */
class CXDSZEPRA extends CXDSFactory
{
    /** @var string */
    public const TYPE = self::TYPE_ZEPRA;

    public function extractData()
    {
        parent::extractData();

        $this->ins_patient = $this->getINSNIR($this->patient);
    }

    /**
     * @param CXDSRegistryPackage $registry
     *
     * @return CXDSRegistryPackage
     */
    protected function preparePatientDocument(CXDSRegistryPackage $registry): CXDSRegistryPackage
    {
        $ei_id = &$this->id_external;
        $id    = $this->id_submission;
        if ($ins = $this->ins_patient) {
            $registry->setPatientId("ei$ei_id", $id, $ins);
        }
        else {
            $patient_id = $this->patient_identifier;
            $registry->setPatientId("ei$ei_id", $id, $patient_id);
        }

        return $registry;
    }

    /**
     * @param CMediusers $praticien
     * @param CGroups    $group
     *
     * @return string|null
     */
    public function getPersonEtab(CMediusers $praticien, CGroups $group): ?string
    {
        // Nom, prénom et si on connait RPPS ou ADELI on renseigne (ils ont un algo de rapprochement en fonction des infos qu'on leur donne
        if (!$praticien->rpps && !$praticien->adeli) {
            return parent::getPerson($praticien);
        }

        $comp1 = "";
        if ($praticien->adeli) {
            $comp1 = "0$praticien->adeli";
        }

        if ($praticien->rpps) {
            $comp1 = "8$praticien->rpps";
        }

        $comp2 = $praticien->_p_last_name;
        $comp3 = $praticien->_p_first_name;
        $comp9  = "1.2.250.1.71.4.2.1";
        $comp10 = "D";
        $comp13 = $this->getTypeId($comp1);

        return "$comp1^$comp2^$comp3^^^^^^&$comp9&ISO^$comp10^^^$comp13";
    }

    /**
     * @param CMediusers $praticien
     *
     * @return string|null
     */
    public function getPerson(CMediusers $praticien): ?string
    {
        // Nom, prénom et si on connait RPPS ou ADELI on renseigne (ils ont un algo de rapprochement en fonction des infos qu'on leur donne
        if (!$praticien->rpps && !$praticien->adeli) {
            return parent::getPerson($praticien);
        }

        $comp1 = "";
        if ($praticien->adeli) {
            $comp1 = "0$praticien->adeli";
        }

        if ($praticien->rpps) {
            $comp1 = "8$praticien->rpps";
        }

        $comp2 = $praticien->_p_last_name;
        $comp3 = $praticien->_p_first_name;
        $comp9  = "1.2.250.1.71.4.2.1";
        $comp10 = "D";
        $comp13 = $this->getTypeId($comp1);

        return "$comp1^$comp2^$comp3^^^^^^&$comp9&ISO^$comp10^^^$comp13";
    }

    /**
     * @param false       $forPerson
     * @param null        $group
     *
     * @return string
     * @throws CMbException
     */
    protected function getIdEtablissement($forPerson = false, $group = null): ?string
    {
        // Pour SISRA, il faut obligatoirement le FINESS
        $finess = $forPerson ? "3" : "1";

        if (!$group->finess) {
            throw new CMbException("CGroups-msg-None finess");
        }

        return $finess . $group->finess;
    }

    /**
     * @param CXDSExtrinsicObject $extrinsic
     * @param string|null         $id
     */
    protected function setExtrinsincPatientID(CXDSExtrinsicObject $extrinsic, ?string $id): void
    {
        $ei_id      = &$this->id_external;
        $patient_id = $this->patient_identifier;
        if ($ins = $this->ins_patient) {
            $extrinsic->setPatientId("ei$ei_id", $id, $ins);
        }
        else {
            $extrinsic->setPatientId("ei$ei_id", $id, $patient_id);
        }
    }

    /**
     * @return string
     */
    protected function getTypeSubmissionLot(): string
    {
        return "SISRA";
    }
}
