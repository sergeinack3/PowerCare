<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

/**
 * Description
 */
class AntiCsrfTokenParameterBagNullObject extends AntiCsrfTokenParameterBag
{
    /**
     * AntiCsrfTokenParameterBagNullObject constructor.
     */
    public function __construct()
    {
        $this->parameters = [];
    }


    /**
     * @inheritDoc
     */
    public function addParam(string $parameter, $value = null): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addParams(array $parameters): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $parameters): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeParam(string $parameter): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeParams(array $parameters): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): AntiCsrfTokenParameterBag
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getToken(?int $ttl = null): string
    {
        return '';
    }
}
