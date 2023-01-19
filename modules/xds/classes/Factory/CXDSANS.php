<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Factory;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Interop\Dmp\CDMPTools;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Interop\Xds\Structure\CXDSExtrinsicObject;
use Ox\Interop\Xds\Structure\CXDSRegistryPackage;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CXDSANS
 *
 * @package Ox\Interop\Xds\Factory
 */
class CXDSANS extends CXDSFactory
{
    /** @var string */
    public const TYPE = self::TYPE_ANS;

    /**
     * @throws Exception
     */
    public function extractData()
    {
        parent::extractData();

        $this->ins_patient = $this->getINSNIR($this->patient);
    }

    /**
     * @return CValueSet
     */
    protected function getValueSet(): CValueSet
    {
        return new CANSValueSet();
    }

    /**
     * @param CMediusers $praticien
     * @param CGroups    $group
     *
     * @return string|null
     */
    public function getPersonEtab(CMediusers $praticien, CGroups $group): ?string
    {
        if (!$praticien->adeli && !$praticien->rpps) {
            return null;
        }

        $id_etab_mediuser  = CDMPTools::getIdEtablissement(true, $group);

        $comp1 = "$id_etab_mediuser/$praticien->_id";
        $comp2 = $praticien->_p_last_name;
        $comp3 = $praticien->_p_first_name;
        $comp9 = "1.2.250.1.71.4.2.1";
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
        if (!$praticien->adeli && !$praticien->rpps) {
            return null;
        }

        $comp2 = $praticien->_p_last_name;
        $comp3 = $praticien->_p_first_name;
        $comp9 = "1.2.250.1.71.4.2.1";
        $comp10 = "D";

        $comp1 = "";
        if ($praticien->adeli) {
            $comp1 = "0$praticien->adeli";
        }

        if ($praticien->rpps) {
            $comp1 = "8$praticien->rpps";
        }
        $comp13 = $this->getTypeId($comp1);

        return "$comp1^$comp2^$comp3^^^^^^&$comp9&ISO^$comp10^^^$comp13";
    }

    /**
     * @param CXDSRegistryPackage $registry
     *
     * @return CXDSRegistryPackage
     */
    protected function preparePatientDocument(CXDSRegistryPackage $registry): CXDSRegistryPackage
    {
        $ei_id = &$this->id_external;
        $id    = $this->name_submission;
        $ins   = $this->ins_patient;
        $registry->setPatientId("ei$ei_id", $id, $ins);

        return $registry;
    }

    /**
     * @param false $forPerson
     * @param null  $group
     *
     * @return string|null
     * @throws CMbException
     */
    protected function getIdEtablissement($forPerson = false, $group = null): ?string
    {
        if (CAppUI::gconf('xds general use_siret_finess_ans', $group->_id) == 'siret') {
            $siret = $forPerson ? "5" : "3";
            if (!$group->siret) {
                throw new CMbException("CGroups-msg-None siret");
            }

            return $siret . $group->siret;
        }

        if (CAppUI::gconf('xds general use_siret_finess_ans', $group->_id) == 'finess') {
            $finess = $forPerson ? "3" : "1";
            if (!$group->finess) {
                throw new CMbException("CGroups-msg-None finess");
            }

            return $finess . $group->finess;
        }

        return parent::getIdEtablissement();
    }

    /**
     * @param String $id
     * @param null   $lid
     * @param bool   $hide
     * @param null   $metadata
     * @param null   $status
     *
     * @return CXDSExtrinsicObject
     * @throws Exception
     */
    protected function createExtrinsicObject(
        $id,
        $lid = null,
        $hide = true,
        $metadata = null,
        $status = null
    ): CXDSExtrinsicObject {
        $extrinsic = parent::createExtrinsicObject($id, $lid, $hide, $metadata, $status);

        $ins = $this->ins_patient;
        $extrinsic->setSlot("sourcePatientId", [$ins]);

        return $extrinsic;
    }

    /**
     * @param CXDSExtrinsicObject $extrinsic
     * @param string|null         $id
     */
    protected function setExtrinsincPatientID(CXDSExtrinsicObject $extrinsic, ?string $id): void
    {
        $ei_id = &$this->id_external;
        $ins   = $this->ins_patient;
        $extrinsic->setPatientId("ei$ei_id", $id, $ins);
    }

    /**
     * @return string
     */
    protected function getTypeSubmissionLot(): string
    {
        return self::TYPE_DMP;
    }
}
