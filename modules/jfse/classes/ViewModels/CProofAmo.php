<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoService;

/**
 * Class CProofAmoType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class CProofAmo extends CJfseViewModel
{
    /** @var string */
    public $invoice_id;
    /** @var string */
    public $date;
    /** @var int */
    public $nature;
    /** @var int */
    public $origin;
    /** @var string */
    public $label;

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['invoice_id'] = 'str notNull';
        $props['date']       = 'date';
        $props['nature']     = 'enum list|0|1|2|4 notNull';
        $props['origin']     = 'num';
        $props['label']      = 'str';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        $view_model = parent::getFromEntity($entity);

        if ($view_model->nature > -1) {
            $types = (new ProofAmoService())->listProofTypes();

            foreach ($types as $type) {
                if ($type->getCode() == $view_model->nature) {
                    $view_model->label = $type->getLabel();
                    break;
                }
            }
        }

        return $view_model;
    }
}
