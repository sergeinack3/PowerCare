<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;

/**
 * Class CCpsCard
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class CCpsCard extends CJfseViewModel
{
    /** @var int */
    public $type_code;
    /** @var string */
    public $type_label;
    /** @var int */
    public $national_identification_type_code;
    /** @var string */
    public $national_identification_type_label;
    /** @var int */
    public $national_identification_number;
    /** @var int */
    public $national_identification_key;
    /** @var int */
    public $civility_code;
    /** @var string */
    public $civility_label;
    /** @var string */
    public $last_name;
    /** @var string */
    public $first_name;
    /** @var Situation[] */
    public $situations;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['type_code']                          = 'num';
        $props['type_label']                         = 'str';
        $props['national_identification_type_code']  = 'num';
        $props['national_identification_type_label'] = 'str';
        $props['national_identification_number']     = 'num';
        $props['national_identification_key']        = 'num';
        $props['civility_code']                      = 'num';
        $props['civility_label']                     = 'str';
        $props['last_name']                          = 'str';
        $props['first_name']                         = 'str';

        return $props;
    }

    /**
     * @param AbstractEntity $entity
     *
     * @return CCpsCard|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        if (!$entity instanceof Card) {
            return null;
        }

        $cps_card             = parent::getFromEntity($entity);
        $cps_card->situations = [];

        if ($entity->hasSituations()) {
            $situations = $entity->getSituations();
            foreach ($situations as $situation) {
                $cps_card->situations[] = CCpsSituation::getFromEntity($situation);
            }
        }

        return $cps_card;
    }
}
