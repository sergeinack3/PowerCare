<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CEntity;
use Ox\Core\CMbArray;
use Ox\Core\CPerson;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedAuthor;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedEntity;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Organization;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Person;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;

abstract class CDAMetaAssigned extends CDAMeta
{
    /** @var array */
    public const OPTIONS_DEFAULTS = [
        'classCode'               => false,
        'id'                      => [
            'required' => true,
        ],
        'assignedPerson'          => [],
        // array|bool : option for assingedPerson object
        'code'                    => [
            'required' => false // bool
        ],
        // array|bool : option for code object
        'addr'                    => CDAMetaAddress::OPTIONS_DEFAULTS,
        // array|bool : option for addr object
        'telecom'                 => CDAMetaTelecom::OPTIONS_DEFAULTS,
        // array|bool : option for telecom object
        'representedOrganization' => CDAMetaOrganization::OPTIONS_DEFAULTS,
        // array|bool : option for organization object
    ];

    /** @var CPerson|CEntity */
    protected $entity;

    /** @var CPerson */
    protected $person;

    /** @var CCDAPOCD_MT000040_Person */
    protected $assigned_person;

    /** @var CGroups|CEtabExterne|CExercicePlace */
    protected $organization_object;

    /**
     * CDAMetaAssigned constructor.
     *
     * @param CCDAFactory     $factory
     * @param CPerson|CEntity $entity
     * @param array           $override_options
     */
    public function __construct(CCDAFactory $factory, $entity, array $override_options = [])
    {
        parent::__construct($factory);

        if ($entity instanceof CPerson) {
            $this->person = $entity;
        }

        $this->entity = $entity;

        $this->options = $this->mergeOptions($override_options);
    }

    /**
     * @return CCDAPOCD_MT000040_AssignedAuthor|CCDAPOCD_MT000040_AssignedEntity
     * @throws Exception
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAPOCD_MT000040_AssignedAuthor|CCDAPOCD_MT000040_AssignedEntity $author */
        $author = parent::build();

        if ($this->options['classCode']) {
            $author->setClassCode();
        }

        // Id
        if ($ids = $this->entityId()) {
            foreach ($ids as $id) {
                $author->appendId($id);
            }
        }

        // Code
        if ($code = $this->entityCode()) {
            $author->setCode($code);
        }

        // Addr
        if ($addresses = $this->addresses()) {
            foreach ($addresses as $address) {
                $author->appendAddr($address);
            }
        }

        // Telecom
        if ($telecoms = $this->telecoms()) {
            foreach ($telecoms as $telecom) {
                $author->appendTelecom($telecom);
            }
        }

        // AssignedPerson
        if ($assigned_person = $this->assignedPerson()) {
            $author->setAssignedPerson($assigned_person);
        }

        // AssignedAuthoringDevice

        // RepresentedOrganization
        if ($represented_organization = $this->representedOrganisation()) {
            $author->setRepresentedOrganization($represented_organization);
        }

        return $author;
    }

    /**
     * Identifiant de l'auteur
     *
     * @return CCDAII[]
     * @throws Exception
     */
    protected function entityId(): array
    {
        $options = $this->options['id'];
        if ($options === false || !is_array($options)) {
            return [];
        }

        $ids = [];

        // INS
        if ($this->person instanceof CPatient && ($patient_ins_nir = $this->person->loadRefPatientINSNIR())) {
            if ($patient_ins_nir->_id) {
                $default_ins_oid = CAppUI::conf('instance_role') === 'qualif' ?
                    CPatientINSNIR::OID_INS_NIR_TEST : CPatientINSNIR::OID_INS_NIR;
                $oid_ins         = $patient_ins_nir->oid ?: $default_ins_oid;
                $ids[]           = $ii = new CCDAII();
                $ii->setRoot($oid_ins);
                $ii->setExtension($patient_ins_nir->ins_nir);
            }

            return $ids;
        }

        $rpps = $adeli = null;
        if ($this->person instanceof CMediusers || $this->person instanceof CMedecin) {
            $rpps  = $this->person->rpps;
            $adeli = $this->person->adeli;
        }

        // rpps
        if ($rpps) {
            $ids[] = $ii = new CCDAII();
            $ii->setRoot(CMedecin::OID_IDENTIFIER_NATIONAL);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("8$rpps");
        }

        // adeli
        if ($adeli && !$ids) {
            $ids[] = $ii = new CCDAII();
            $ii->setRoot(CMedecin::OID_IDENTIFIER_NATIONAL);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("0$adeli");
        }

        if ($options['required'] === true && !$ids) {
            $praticien_name = $this->person->_p_last_name . ' ' . $this->person->_p_first_name;
            $this->factory->report->addData(
                CAppUI::tr('CReport-msg-Doctor doesnt have RPPS and ADELI', [$praticien_name]),
                CItemReport::SEVERITY_ERROR
            );
        }

        return $ids;
    }

    /**
     * Contient les informations de l'auteur personne physique
     *
     * @return CCDAPOCD_MT000040_Person|null
     */
    protected function assignedPerson(): ?CCDAPOCD_MT000040_Person
    {
        $options = $this->options['assignedPerson'];
        if (!$this->person || $options === false || !is_array($options)) {
            return null;
        }

        if (!$assigned_person = $this->assigned_person) {
            $name            = (new CDAMetaName($this->factory, $this->person, $options))->build();
            $assigned_person = new CCDAPOCD_MT000040_Person();
            $assigned_person->appendName($name);
        }

        return $assigned_person;
    }

    /**
     * Adresse de l'auteur
     *
     * @return CCDAAD[]
     * @throws CCDAException
     */
    protected function addresses(): array
    {
        $options = $this->options['addr'];
        if ($options === false || !is_array($options)) {
            return [];
        }

        $addresses = [];
        if ($this->person) {
            $addresses[] = (new CDAMetaAddress($this->factory, $this->person, $options))->build();
        }

        return $addresses;
    }

    /**
     * Coordonnées télécom de l'auteur
     *
     * @return CCDATEL[]
     */
    protected function telecoms(): array
    {
        $options = $this->options['telecom'];
        if ($options === false || !is_array($options)) {
            return [];
        }

        $telecoms      = [];
        $telecoms_data = array_filter(
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL  => $this->person->_p_email,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE => $this->person->_p_mobile_phone_number,
                CDAMetaTelecom::TYPE_TELECOM_TEL    => $this->person->_p_phone_number,
            ]
        ) ?: [CDAMetaTelecom::TYPE_TELECOM_TEL => null];

        foreach ($telecoms_data as $type => $value) {
            $telecoms[] = (new CDAMetaTelecom($this->factory, $this->person, $type))->build();
        }

        return $telecoms;
    }

    /**
     * @return CCDACE|null
     * @throws CCDAException
     */
    protected function entityCode(): ?CCDACE
    {
        $options = $this->options['code'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        $ce = null;
        if ($this->person instanceof CMediusers) {
            $spec = $this->person->loadRefOtherSpec();
            if ($spec->_id) {
                $ce = new CCDACE();
                $ce->setCode($spec->code);
                $ce->setDisplayName($spec->libelle);
                $ce->setCodeSystem($spec->oid);
            }
        } elseif ($this->person instanceof CMedecin) {
            throw new CCDAException('CDAMetaAssingedAuthor::entityCode() not implemented for CMedecin');
        }

        if ($options['required'] === true && !$ce) {
            $praticien_name = $this->person->_p_last_name . ' ' . $this->person->_p_first_name;
            $this->factory->report->addData(
                CAppUI::tr('CMediusers-msg-None ASIP specialty', [$praticien_name]),
                CItemReport::SEVERITY_ERROR
            );
        }

        return $ce;
    }

    /**
     *  Organisation pour le compte de laquelle l'auteur a contribué au document
     *
     * @return CCDAPOCD_MT000040_Organization|null
     */
    private function representedOrganisation(): ?CCDAPOCD_MT000040_Organization
    {
        $options = $this->options['representedOrganization'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        if (!$organization_object = $this->organization_object) {
            $organization_object = CMbArray::get($this->options, 'representedOrganizationObject');
            if (!$organization_object || !$organization_object instanceof CStoredObject) {
                return null;
            }
        }

        return (new CDAMetaOrganization($this->factory, $organization_object, $options))->build();
    }

    /**
     * @param CEtabExterne|CGroups|CExercicePlace $organization_object
     *
     * @return CDAMetaAssignedAuthor
     */
    public function setOrganizationObject($organization_object)
    {
        $this->organization_object = $organization_object;

        return $this;
    }
}
