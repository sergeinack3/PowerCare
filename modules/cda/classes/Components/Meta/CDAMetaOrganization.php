<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CAppUI;
use Ox\Core\CEntity;
use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAON;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Organization;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CExercicePlace;

class CDAMetaOrganization extends CDAMeta
{
    public const OPTIONS_DEFAULTS = [
        'id'                        => [ // array|bool : options for id field
            'required' => true, // bool : true : add error in report
            'type'     => 'siret', // string : siret|finess
            'fallback' => 'finess' // string|null : siret|finess
        ],
        'name'                      => [], // array|bool : options for name field
        'telecom'                   => CDAMetaTelecom::OPTIONS_DEFAULTS, // array|bool : options for telecom  field
        'addr'                      => CDAMetaAddress::OPTIONS_DEFAULTS, // array|bool : options for addr field
        'standardIndustryClassCode' => false, // array|bool : options for standardIndustryClassCode field
    ];

    /** @var CExercicePlace|CEntity */
    protected $entity;

    /**
     * CDAMetaOrganization constructor.
     *
     * @param CCDAFactory                         $factory
     * @param CEntity|CExercicePlace|CEtabExterne $entity
     * @param array                               $override_options
     */
    public function __construct(CCDAFactory $factory, $entity, array $override_options = [])
    {
        parent::__construct($factory);

        $this->content = new CCDAPOCD_MT000040_Organization();
        $this->entity  = $entity;
        $this->options = $this->mergeOptions($override_options);
    }

    /**
     * @return CCDAPOCD_MT000040_Organization
     * @throws CCDAException
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAPOCD_MT000040_Organization $organization */
        $organization = parent::build();

        // Id
        if ($id = $this->id()) {
            $organization->appendId($id);
        }

        // Code ?
        if ($standard_industry_classCode = $this->industryClassCode()) {
            $organization->setStandardIndustryClassCode($standard_industry_classCode);
        }

        // Name
        if ($name = $this->name()) {
            $organization->appendName($name);
        }

        // Telecom
        if ($telecoms = $this->telecoms()) {
            foreach ($telecoms as $telecom) {
                $organization->appendTelecom($telecom);
            }
        }

        // Address
        if ($addresses = $this->addresses()) {
            foreach ($addresses as $address) {
                $organization->appendAddr($address);
            }
        }

        return $organization;
    }

    /**
     * @return CCDAII|null
     * @throws CCDAException
     */
    private function id(): ?CCDAII
    {
        $options = $this->options['id'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        $id             = null;
        $oid            = CExercicePlace::OID_IDENTIFIER_NATIONAL;
        $authority_name = "GIP-CPS";
        $identifiant    = $this->findOrganizationIdentifier(($type = $options['type']), $options['fallback']);

        if ($identifiant && $oid) {
            $id = new CCDAII();
            $id->setRoot($oid);
            $id->setExtension($identifiant);
            if ($authority_name) {
                $id->setAssigningAuthorityName($authority_name);
            }
        }

        if ($options['required'] === true && !$id) {
            $class = $this->entity->_class;
            $this->factory->report->addData(
                CAppUI::tr("$class-msg-None $type"),
                CItemReport::SEVERITY_ERROR
            );
        }

        return $id;
    }

    /**
     * @param string      $type
     * @param string|null $fallbacks
     *
     * @return string|null
     * @throws CCDAException
     */
    protected function findOrganizationIdentifier(string $type, ?string $fallbacks): ?string
    {
        $fallbacks = $fallbacks ? explode('|', $fallbacks) : null;

        $finess = $siret = null;
        if (in_array(get_class($this->entity), [CGroups::class, CEtabExterne::class, CExercicePlace::class])) {
            $finess = $this->entity->finess;
            $siret  = $this->entity->siret;
        } else {
            throw new CCDAException("CDAMetaOrganization::id() not implemented for: " . get_class($this->entity));
        }

        // siret
        if (($type === 'siret' && $siret) || ($type === 'siret' && !$fallbacks)) {
            return "3$siret";
        }

        // finess
        if (($type === 'finess' && $finess) || ($type === 'finess' && !$fallbacks)) {
            return "1$finess";
        }

        // fallback
        if ($fallbacks) {
            foreach ($fallbacks as $fallback) {
                if (property_exists($this->entity, $fallback) && $this->entity->{$fallback}) {
                    return $this->entity->{$fallback};
                }
            }
        }

        return null;
    }

    /**
     * Telecom de l'organisation
     *
     * @return CCDATEL[]
     */
    protected function telecoms(): array
    {
        $options = $this->options['telecom'];
        if ($options === false || !is_array($options)) {
            return [];
        }

        if (!is_array($types = $options['list'])) {
            $types = [$types];
        }

        $telecoms      = [];
        $telecoms_data = CDAMetaTelecom::filterTelecoms($this->entity, $types);
        foreach ($telecoms_data as $type => $value) {
            $telecoms[] = (new CDAMetaTelecom($this->factory, $this->entity, $type))->build();
        }

        return $telecoms;
    }

    /**
     * Address de l'organisation
     *
     * @return CCDAAD[]
     */
    protected function addresses(): array
    {
        $options = $this->options['addr'];
        if ($options === false || !is_array($options)) {
            return [];
        }

        return [(new CDAMetaAddress($this->factory, $this->entity, $options))->build()];
    }

    /**
     * Nom de l'organisation
     *
     * @return CCDAON|null
     */
    protected function name(): ?CCDAON
    {
        $options = $this->options['name'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        $name = null;
        if ($this->entity instanceof CGroups) {
            $name = $this->entity->_name;
        } elseif ($this->entity instanceof CExercicePlace) {
            $name = $this->entity->raison_sociale;
        } elseif ($this->entity instanceof CEtabExterne) {
            $name = $this->entity->nom;
        }

        if (!$name) {
            return null;
        }

        $on = new CCDAON();
        $on->setData($name);

        return $on;
    }

    /**
     * @return CCDACE|null
     */
    private function industryClassCode(): ?CCDACE
    {
        $options = $this->options['standardIndustryClassCode'];
        if ($options === false || !is_array($options)) {
            return null;
        }

        $code         = CMbArray::get($options, 'code');
        $display_name = CMbArray::get($options, 'displayName');
        $code_system  = CMbArray::get($options, 'codeSystem');

        $ce = null;
        if ($code && $code_system) {
            $ce = new CCDACE();
            $ce->setCode($code);
            $ce->setDisplayName($display_name);
            $ce->setCodeSystem($code_system);
        }

        return $ce;
    }
}
