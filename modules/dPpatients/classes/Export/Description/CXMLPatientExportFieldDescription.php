<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export\Description;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;

/**
 * Description of a field.
 */
class CXMLPatientExportFieldDescription
{
    /** @var CStoredObject */
    private $instance;

    /** @var string */
    private $name;

    /** @var string */
    private $tr;

    /** @var string */
    private $desc;

    /** @var string */
    private $prop;

    /** @var string */
    private $sql_prop;

    /** @var string */
    private $path;

    /**
     * @throws CMbException
     */
    public function __construct(CStoredObject $instance, string $field_name)
    {
        $props = $instance->getPlainProps();
        if (!$field_name || !isset($props[$field_name])) {
            throw new CMbException(
                'CXMLPatientExportFieldDescription-Error-Field must not be null and must be a field of the object',
                $field_name,
                $instance->_class
            );
        }

        $spec = $instance->_specs[$field_name];

        $this->instance   = $instance;
        $short_class_name = CClassMap::getSN($this->instance);

        $this->name     = $field_name;
        $this->tr       = CAppUI::tr($short_class_name . '-' . $this->name);
        $this->desc     = CAppUI::tr($short_class_name . '-' . $this->name . '-desc');
        $this->prop     = $props[$this->name];
        $this->sql_prop = $spec->getDBSpec();
        $this->path     = $this->buildPath();
    }

    private function buildPath(): string
    {
        if ($this->name === $this->instance->_spec->key) {
            return '/@id';
        }

        if ($this->instance->_specs[$this->name] instanceof CRefSpec) {
            return sprintf('/@%s', $this->name);
        }

        {
            return sprintf('/field[@name="%s"]', $this->name);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTr(): string
    {
        return $this->tr;
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * @return string
     */
    public function getProp(): string
    {
        return $this->prop;
    }

    public function getSqlProp(): string
    {
        return $this->sql_prop;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
