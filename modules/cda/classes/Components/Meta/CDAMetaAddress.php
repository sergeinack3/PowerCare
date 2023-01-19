<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CEntity;
use Ox\Core\CMbException;
use Ox\Core\CPerson;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetAddressLine;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CExercicePlace;

class CDAMetaAddress extends CDAMeta
{
    /** @var array */
    public const OPTIONS_DEFAULTS = [
        'use' => 'H' // string : default value for prop use
    ];

    /** @var CPerson|CEntity */
    protected $entity;

    /**
     * CDAMetaAddress constructor.
     *
     * @param CCDAFactory     $factory
     * @param CPerson|CEntity $entity
     * @param array           $override_options
     */
    public function __construct(CCDAFactory $factory, $entity, array $override_options = [])
    {
        parent::__construct($factory);

        $this->content = new CCDAAD();
        $this->entity  = $entity;
        $this->options = $this->mergeOptions($override_options);
    }

    /**
     * @return CCDAAD
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAAD $address */
        $address = parent::build();

        if ($this->entity instanceof CPerson) {
            $this->setAddressPerson($address);
        } elseif ($this->entity instanceof CEntity) {
            $this->setAddressEntity($address);
        } elseif ($this->entity instanceof CExercicePlace) {
            throw new CCDAException('not implemented in CDAMetaAddress::build() for object CExercicePlace');
        } elseif ($this->entity instanceof CEtabExterne) {
            throw new CCDAException('not implemented in CDAMetaAddress::build() for object CEtabExtern');
        }

        return $address;
    }

    /**
     * @param CCDAAD $address
     */
    protected function setAddressPerson(CCDAAD $address): void
    {
        $userCity          = $this->entity->_p_city;
        $userPostalCode    = $this->entity->_p_postal_code;
        $userStreetAddress = $this->entity->_p_street_address;
        if (!$userCity && !$userPostalCode && !$userStreetAddress) {
            $address->setNullFlavor("NAV");

            return;
        }

        if (!is_array($use = $this->options['use'])) {
            $use = [$use];
        }

        $address->setUse($use);

        $addresses = preg_split("#[\t\n\v\f\r]+#", $userStreetAddress, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($addresses as $_addr) {
            $street = new CCDA_adxp_streetAddressLine();
            $street->setData($_addr);
            $address->append("streetAddressLine", $street);
        }

        $street2 = new CCDA_adxp_streetAddressLine();
        $street2->setData($userPostalCode . " " . $userCity);
        $address->append("streetAddressLine", $street2);
    }

    protected function setAddressEntity(CCDAAD $address): void
    {
        if ($this->entity instanceof CGroups) {
            $groups = $this->entity;

            if (!$groups->adresse && !$groups->cp && !$groups->ville) {
                $address->setNullFlavor('NAV');
            } else {
                $street = new CCDA_adxp_streetAddressLine();
                $street->setData($groups->adresse);
                $address->append("streetAddressLine", $street);

                $street2 = new CCDA_adxp_streetAddressLine();
                $street2->setData($groups->cp . " " . $groups->ville);
                $address->append("streetAddressLine", $street2);
            }
        }
    }
}
