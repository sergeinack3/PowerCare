<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Components\CDAComponent;

class CDAMeta extends CDAComponent
{
    /** @var array */
    public const OPTIONS_DEFAULTS = [];

    /** @var CCDAClasseCda */
    protected $content;

    /** @var array */
    protected $options;

    /**
     * @return CCDAClasseCda
     */
    public function build(): CCDAClasseBase
    {
        return $this->content;
    }

    /**
     * @param array $override_options
     *
     * @return array
     */
    protected function mergeOptions(array $override_options = []): array
    {
        return array_replace_recursive($this::OPTIONS_DEFAULTS, $override_options);
    }
}
