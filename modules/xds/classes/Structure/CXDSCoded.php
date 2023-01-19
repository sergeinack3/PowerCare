<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Ox\Core\CMbArray;

class CXDSCoded implements XDSElementInterface
{
    /** @var string */
    public $code;

    /** @var string */
    public $display_name;

    /** @var string */
    public $code_system;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCodeSystem(): string
    {
        return $this->code_system;
    }

    /**
     * @param string $code_system
     */
    public function setCodeSystem(string $code_system): void
    {
        $this->code_system = $code_system;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->display_name;
    }

    /**
     * @param string $display_name
     */
    public function setDisplayName(string $display_name): void
    {
        $this->display_name = $display_name;
    }

    /**
     * @param array $values
     *
     * @return static
     */
    public static function fromValues(array $values)
    {
        $element               = new static();
        $element->code         = CMbArray::get($values, 'code');
        $element->code_system  = CMbArray::get($values, 'codeSystem');
        $element->display_name = CMbArray::get($values, 'displayName');

        return $element;
    }
}
