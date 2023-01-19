<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;

final class CCpsSituation extends CJfseViewModel
{
    /** @var int */
    public $practitioner_id;
    /** @var int */
    public $situation_id;
    /** @var int */
    public $structure_identifier_type;
    /** @var string */
    public $structure_identifier;
    /** @var string */
    public $structure_name;
    /** @var int */
    public $invoicing_number;
    /** @var int */
    public $invoicing_number_key;
    /** @var int */
    public $substitute_number;
    /** @var int */
    public $convention_code;
    /** @var string */
    public $convention_label;
    /** @var int */
    public $speciality_code;
    /** @var string */
    public $speciality_label;
    /** @var string */
    public $speciality_group;
    /** @var int */
    public $price_zone_code;
    /** @var string */
    public $price_zone_label;
    /** @var int */
    public $distance_allowance_code;
    /** @var string */
    public $distance_allowance_label;
    /** @var bool */
    public $fse_signing_authorisation;
    /** @var bool */
    public $lot_signing_authorisation;
    /** @var int */
    public $practice_mode;
    /** @var int */
    public $practice_status;
    /** @var int */
    public $activity_sector;
    /** @var string[] */
    public $approval_codes;
    /** @var string[] */
    public $approval_labels;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['practitioner_id']           = 'num';
        $props['situation_id']              = 'num';
        $props['structure_identifier_type'] = 'num';
        $props['structure_identifier']      = 'str';
        $props['structure_name']            = 'str';
        $props['invoicing_number']          = 'str';
        $props['invoicing_number_key']      = 'str';
        $props['substitute_number']         = 'str';
        $props['convention_code']           = 'num';
        $props['convention_label']          = 'str';
        $props['speciality_code']           = 'num';
        $props['speciality_label']          = 'str';
        $props['speciality_group']          = 'str';
        $props['price_zone_code']           = 'num';
        $props['price_zone_label']          = 'str';
        $props['distance_allowance_code']   = 'num';
        $props['distance_allowance_label']  = 'str';
        $props['fse_signing_authorisation'] = 'bool';
        $props['lot_signing_authorisation'] = 'bool';
        $props['practice_mode']             = 'num';
        $props['practice_status']           = 'num';
        $props['activity_sector']           = 'num';
        $props['approval_labels']           = 'str';

        return $props;
    }

    /**
     * @param AbstractEntity $entity
     *
     * @return CCpsSituation
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        if (!$entity instanceof Situation) {
            return null;
        }

        $situation                  = parent::getFromEntity($entity);
        $situation->approval_codes  = $entity->getApprovalCodes();
        $situation->approval_labels = $entity->getApprovalLabels();

        return $situation;
    }
}
