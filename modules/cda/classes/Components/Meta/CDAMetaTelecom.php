<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CEntity;
use Ox\Core\CPerson;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CExercicePlace;

class CDAMetaTelecom extends CDAMeta
{
    /** @var string[][]  */
    public const OPTIONS_DEFAULTS = [
      'list' => self::TYPES_TELECOM
    ];

    /** @var string */
    public const TYPE_TELECOM_EMAIL = 'email';
    /** @var string */
    public const TYPE_TELECOM_TEL = 'tel';
    /** @var string */
    public const TYPE_TELECOM_MOBILE = 'mobile';
    /** @var string */
    public const TYPE_TELECOM_FAX = 'fax';

    /** @var string[] */
    private const TYPES_TELECOM = [
        self::TYPE_TELECOM_EMAIL,
        self::TYPE_TELECOM_TEL,
        self::TYPE_TELECOM_MOBILE,
        self::TYPE_TELECOM_FAX,
    ];

    /** @var CPerson|CEntity */
    protected $entity;
    /** @var string */
    protected $type;

    /**
     * CDAMetaTelecom constructor.
     *
     * @param CCDAFactory     $factory
     * @param CPerson|CEntity $entity
     * @param string          $type
     * @param array           $override_options
     */
    public function __construct(CCDAFactory $factory, $entity, string $type, array $override_options = [])
    {
        parent::__construct($factory);

        $this->content = new CCDATEL();
        $this->entity  = $entity;
        $this->type    = $type;
        $this->options = $this->mergeOptions($override_options);
    }

    /**
     * @return CCDATEL
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDATEL $telecom */
        $telecom = parent::build();

        switch ($this->type) {
            case self::TYPE_TELECOM_EMAIL:
                if (!$email = self::email($this->entity)) {
                    $telecom->setNullFlavor("UNK");

                    return $telecom;
                }
                $telecom->setValue("mailto:$email");
                break;

            case self::TYPE_TELECOM_MOBILE:
                if (!$mobile_phone = self::mobilePhone($this->entity)) {
                    $telecom->setNullFlavor("UNK");

                    return $telecom;
                }
                $telecom->setValue("tel:$mobile_phone");
                $telecom->setUse(['MC']);
                break;

            case self::TYPE_TELECOM_TEL:
                if (!$phone_number = self::phoneNumber($this->entity)) {
                    $telecom->setNullFlavor("UNK");

                    return $telecom;
                }
                $telecom->setValue("tel:$phone_number");
                $telecom->setUse(['H']);
                break;

            case self::TYPE_TELECOM_FAX:
                if (!$fax = self::fax($this->entity)) {
                    $telecom->setNullFlavor("UNK");

                    return $telecom;
                }
                $telecom->setValue("fax:$fax");
                $telecom->setUse(['WP']);
                break;
        }

        return $telecom;
    }

    /**
     * @param CPerson|CEntity|CExercicePlace|CEtabExterne $entity
     *
     * @return string|null
     */
    protected static function email($entity): ?string
    {
        if ($entity instanceof CPerson) {
            return $entity->_p_email;
        } elseif ($entity instanceof CGroups) {
            return $entity->mail;
        } elseif ($entity instanceof CExercicePlace) {
            return $entity->email;
        }

        return null;
    }

    /**
     * @param CPerson|CEntity|CExercicePlace|CEtabExterne $entity
     *
     * @return string|null
     */
    protected static function mobilePhone($entity): ?string
    {
        if ($entity instanceof CPerson) {
            return $entity->_p_mobile_phone_number;
        }

        return null;
    }

    /**
     * @param CPerson|CEntity|CExercicePlace|CEtabExterne $entity
     *
     * @return string|null
     */
    private static function phoneNumber($entity): ?string
    {
        if ($entity instanceof CPerson) {
            return $entity->_p_phone_number;
        } elseif ($entity instanceof CGroups) {
            return $entity->tel;
        } elseif ($entity instanceof CExercicePlace) {
            return $entity->tel;
        } elseif ($entity instanceof CEtabExterne) {
            return $entity->tel;
        }

        return null;
    }

    /**
     * @param CPerson|CEntity|CExercicePlace|CEtabExterne $entity
     *
     * @return string|null
     */
    private static function fax($entity): ?string
    {
        if ($entity instanceof CPerson) {
            return $entity->_p_fax_number;
        } elseif ($entity instanceof CGroups) {
            return $entity->fax;
        } elseif ($entity instanceof CExercicePlace) {
            return $entity->fax;
        } elseif ($entity instanceof CEtabExterne) {
            return $entity->fax;
        }

        return null;
    }

    /**
     * Give types and values when telecom is available
     *
     * @param CPerson|CEntity|CExercicePlace|CEtabExterne $entity
     * @param string                                      $types
     *
     * @return array
     */
    public static function filterTelecoms($entity, array $types): array
    {
        if (!$types) {
            return [];
        }

        $values = [];
        foreach ($types as $type) {
            switch ($type) {
                case self::TYPE_TELECOM_EMAIL:
                    $values[$type] = self::email($entity);
                    break;
                case self::TYPE_TELECOM_FAX:
                    $values[$type] = self::fax($entity);
                    break;
                case self::TYPE_TELECOM_MOBILE:
                    $values[$type] = self::mobilePhone($entity);
                    break;
                case self::TYPE_TELECOM_TEL:
                    $values[$type] = self::phoneNumber($entity);
                    break;
            }
        }

        return array_filter($values);
    }
}
