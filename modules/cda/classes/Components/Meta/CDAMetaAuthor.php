<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CMbArray;
use Ox\Core\CModelObject;
use Ox\Core\CPerson;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedAuthor;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Author;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class CDAMetaAuthor extends CDAMeta
{
    /** @var array */
    public const OPTIONS_DEFAULTS = [
        'time'           => 'now', // option for field 'time'
        'assignedAuthor' => CDAMetaAssignedAuthor::OPTIONS_DEFAULTS,    // options for assignedAuthor
        'functionCode'   => [
            'code' => null // string|null : code for functionCode
        ],    // array|bool : options for functionCode
    ];

    /** @var CPerson */
    protected $person;

    /** @var CCDAPOCD_MT000040_AssignedAuthor */
    private $assigned_author;

    /** @var CStoredObject */
    private $author_of;

    /** @var CPatient */
    private $patient;

    /**
     * CDAMetaAuthor constructor.
     *
     * @param CCDAFactory $factory
     * @param CPerson     $person
     * @param array       $override_options
     */
    public function __construct(CCDAFactory $factory, CPerson $person, array $override_options = [])
    {
        parent::__construct($factory);

        $this->content = new CCDAPOCD_MT000040_Author();
        $this->person  = $person;

        $this->options = $this->mergeOptions($override_options);
        $this->patient = $this->factory->patient;
    }

    /**
     * @return CCDAPOCD_MT000040_Author
     * @throws \Exception
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAPOCD_MT000040_Author $author */
        $author = parent::build();

        // functionCode
        if ($function_code = $this->functionCode()) {
            $author->setFunctionCode($function_code);
        }

        // time
        $time = new CCDATS();
        $time->setValue($this->options['time']);
        $author->setTime($time);

        // assignedAuthor
        $author->setAssignedAuthor($this->assignedAuthor());

        return $author;
    }

    /**
     * @param CCDAPOCD_MT000040_AssignedAuthor $assigned_author
     *
     * @return CDAMetaAuthor
     */
    public function setAssignedAuthor(CCDAPOCD_MT000040_AssignedAuthor $assigned_author): CDAMetaAuthor
    {
        $this->assigned_author = $assigned_author;

        return $this;
    }

    /**
     * @return CCDAPOCD_MT000040_AssignedAuthor
     * @throws \Exception
     */
    private function assignedAuthor(): CCDAPOCD_MT000040_AssignedAuthor
    {
        if (!$assigned_author = $this->assigned_author) {
            $options_assigned_author = $this->options['assignedAuthor'] ?? [];
            $assigned_author         = (new CDAMetaAssignedAuthor(
                $this->factory,
                $this->person,
                $options_assigned_author
            ))
                ->build();
        }

        return $assigned_author;
    }

    /**
     * Représente le rôle fonctionnel joué par l'auteur vis-à-vis du patient lors de la création du document
     *
     * @return CCDACE|null
     * @throws \Exception
     */
    private function functionCode(): ?CCDACE
    {
        return null;
        $options = $this->options['functionCode'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        // Si l'auteur est le patient, ne pas utiliser l'élément
        if ($this->person instanceof CPatient) {
            return null;
        }

        if (!$code = CMbArray::get($options, 'code')) {
            // you need specify about what he is the author and the patient target
            if (!$this->author_of || !$this->patient) {
                return null;
            }

            $code = $this->getForCorrespondent();
            $code = $code ?: $this->getForMedecinTraitant();
            $code = $code ?: $this->getForResponsibleOfCare();
        }

        if (!$code) {
            return null;
        }

        $entries = CANSValueSet::loadEntries('functionCode', $code);
        $ccdace  = new CCDACE();
        $ccdace->setCode(CMbArray::get($entries, 'code'));
        $ccdace->setCodeSystem(CMbArray::get($entries, 'codeSystem'));
        $ccdace->setDisplayName(CMbArray::get($entries, 'displayName'));

        return $ccdace;
    }

    /**
     * @param CStoredObject $author_of
     *
     * @return CDAMetaAuthor
     */
    public function setAuthorOf(?CStoredObject $author_of): CDAMetaAuthor
    {
        $this->author_of = $author_of;

        return $this;
    }

    /**
     * @param CPatient $patient
     *
     * @return CDAMetaAuthor
     */
    public function setPatient(CPatient $patient): CDAMetaAuthor
    {
        $this->patient = $patient;

        return $this;
    }


    /**
     * Search if the author is a patient correspondent
     *
     * @return string|null
     * @throws \Exception
     */
    private function getForCorrespondent(): ?string
    {
        // not a doctor
        if (!$this->isADoctor()) {
            return null;
        }

        /** @var CMedecin|CMediusers $doctor */
        $doctor = $this->person;
        if (!$doctor->rpps) {
            return null;
        }

        $medecin       = new CMedecin();
        $ds            = $medecin->getDS();
        $where_medecin = [];
        $ljoin_medecin = [];

        $ljoin_medecin["correspondant"] = "correspondant.medecin_id = medecin.medecin_id";
        $where_medecin['patient_id']    = $ds->prepare(' = ?', $this->patient->_id);
        $where_medecin['rpps']          = $ds->prepare(' = ?', $doctor->rpps);
        $where_medecin[]                = 'correspondant.correspondant_id IS NOT NULL';

        if ($medecin->countList($where_medecin, 'medecin.medecin_id', $ljoin_medecin) > 0) {
            return 'CORRE';
        }

        return null;
    }

    /**
     * Search if the author is a attending physician
     *
     * @return string|null
     */
    private function getForMedecinTraitant(): ?string
    {
        if (!$this->isADoctor()) {
            return null;
        }

        /** @var CMediusers|CMedecin $doctor */
        $doctor = $this->person;
        if (!$doctor->rpps) {
            return null;
        }

        // Médecin traitant
        $medecin_traitant = $this->patient->loadRefMedecinTraitant();
        if ($medecin_traitant && $medecin_traitant->_id && $medecin_traitant->rpps === $doctor->rpps) {
            return 'PCP';
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isADoctor(): bool
    {
        return $this->person instanceof CMedecin || $this->person instanceof CMediusers;
    }

    /**
     * Search if the author is a responsible of care
     */
    private function getForResponsibleOfCare(): ?string
    {
        if (!$this->isADoctor()) {
            return null;
        }

        // Référent - Responsable du patient dans la structure de soins
        if ($this->author_of instanceof CSejour && $this->person->_id == $this->author_of->praticien_id) {
            return 'ATTPHYS';
        }

        return null;
    }
}
