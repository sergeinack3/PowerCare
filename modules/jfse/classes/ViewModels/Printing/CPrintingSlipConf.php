<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Printing;

use Ox\Mediboard\Jfse\Domain\Printing\PrintSlipModeEnum;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Configuration for printing slips
 */
class CPrintingSlipConf extends CJfseViewModel
{
    /** @var int */
    public $mode;

    /** @var bool */
    public $degraded;

    /** @var string */
    public $date_min;

    /** @var string */
    public $date_max;

    /** @var int[] */
    public $batch;

    /** @var int[] */
    public $files;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['mode']     = PrintSlipModeEnum::getProp();
        $props['degraded'] = 'bool';
        $props['date_min'] = 'date';
        $props['date_max'] = 'date';

        return $props;
    }
}
