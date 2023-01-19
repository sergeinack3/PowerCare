<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

/**
 * Class CProofAmoType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class CProofAmoType extends CJfseViewModel
{
    /** @var int */
    public $code;
    /** @var string */
    public $label;

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['code']    = 'num min|0';
        $props['label']   = 'str';

        return $props;
    }
}
