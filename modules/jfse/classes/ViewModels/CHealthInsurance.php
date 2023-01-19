<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

/**
 * Class CHealthInsurance
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
class CHealthInsurance extends CJfseViewModel
{
    /** @var int */
    public $etablissement_id;

    /** @var string */
    public $code;

    /** @var string */
    public $name;

    /** @var int */
    public $jfse_id;

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['etablissement_id'] = 'num';
        $props['jfse_id']          = 'num notNull';
        $props['code']             = 'str notNull';
        $props['name']             = 'str notNull';

        return $props;
    }
}
