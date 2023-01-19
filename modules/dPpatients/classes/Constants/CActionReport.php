<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use ReflectionClass;
use ReflectionException;

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
class CActionReport
{
    // releves
    /** @var CConstantReleve[] */
    private $releves_loaded = [];
    /** @var CConstantReleve[] */
    private $releves_updated = [];
    /** @var CConstantReleve[] */
    private $releves_stored = [];

    // constants
    /** @var CAbstractConstant[] */
    private $constants_loaded = [];
    /** @var CAbstractConstant[] */
    private $constants_stored = [];
    /** @var CAbstractConstant[] */
    private $constants_updated = [];
    /** @var CAbstractConstant[] */
    private $constants_failed = [];

    // constants calculated
    /** @var CAbstractConstant[] */
    private $calculated_stored = [];
    /** @var string[] */
    private $calculated_failed = [];
    /** @var CAbstractConstant[] */
    private $calculated_updated = [];

    // comment
    /** @var CReleveComment[] */
    private $comments_stored = [];

    // exceptions
    /** @var Exception[] */
    private $exceptions = [];


    /**
     * @return array
     * @throws ReflectionException
     */
    public function getReport(): array
    {
        $result           = [];
        $reflection_class = new ReflectionClass($this);
        $properties       = $reflection_class->getProperties();
        foreach ($properties as $property) {
            if ($property === 'exceptions') {
                continue;
            }

            $property_name          = $property->getName();
            $result[$property_name] = $this->$property_name;
        }

        $result['exceptions'] = [];
        foreach ($this->exceptions as $exception) {
            $class = substr(get_class($exception), strrpos(get_class($exception), '\\') + 1);
            $tr    = CAppUI::tr("$class-" . $exception->getCode());
            if (CMbArray::get($result['exceptions'], $tr)) {
                $result['exceptions'][$tr] += 1;
            } else {
                $result['exceptions'][$tr] = 1;
            }
        }

        return $result;
    }

    /**
     * @return Exception[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param Exception $exception
     */
    public function addException(Exception $exception): void
    {
        $this->exceptions[] = $exception;
    }

    /**
     * @return CConstantReleve[]
     */
    public function getRelevesLoaded(): array
    {
        return $this->releves_loaded;
    }

    /**
     * @return CConstantReleve[]
     */
    public function getRelevesUpdated(): array
    {
        return $this->releves_updated;
    }

    /**
     * @return CConstantReleve[]
     */
    public function getRelevesStored(): array
    {
        return $this->releves_stored;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getConstantsLoaded(): array
    {
        return $this->constants_loaded;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getConstantsStored(): array
    {
        return $this->constants_stored;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getConstantsUpdated(): array
    {
        return $this->constants_updated;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getConstantsFailed(): array
    {
        return $this->constants_failed;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getCalculatedStored(): array
    {
        return $this->calculated_stored;
    }

    /**
     * @return string[]
     */
    public function getCalculatedFailed(): array
    {
        return $this->calculated_failed;
    }

    /**
     * @return CAbstractConstant[]
     */
    public function getCalculatedUpdated(): array
    {
        return $this->calculated_updated;
    }

    /**
     * @return CReleveComment[]
     */
    public function getCommentsStored(): array
    {
        return $this->comments_stored;
    }

    /**
     * @param CStoredObject $object
     *
     * @return bool
     */
    public function addLoadedObject(CStoredObject $object): bool
    {
        return $this->addObject($object, 'loaded');
    }

    /**
     * @param CStoredObject $object
     * @param string        $variable
     *
     * @return bool
     */
    private function addObject(CStoredObject $object, string $variable): bool
    {
        if (get_class($object) === CConstantReleve::class) {
            $variable          = 'releves_' . $variable;
            $this->{$variable}[] = $object;

            return true;
        }

        if ($object instanceof CAbstractConstant) {
            $variable          = 'constants_' . $variable;
            $this->{$variable}[] = $object;

            return true;
        }

        if ($object instanceof CReleveComment) {
            $variable          = 'comments_' . $variable;
            $this->{$variable}[] = $object;

            return true;
        }

        return false;
    }

    public function addStoredComment(CReleveComment $comment): bool
    {
        return $this->addObject($comment, 'stored');
    }

    /**
     * @param CActionReport $action_report
     *
     * @return $this
     */
    public function fusion(CActionReport $action_report): self
    {
        // releve
        $this->releves_loaded  = $this->fusionElements($this->releves_loaded, $action_report->releves_loaded);
        $this->releves_stored  = $this->fusionElements($this->releves_stored, $action_report->releves_stored);
        $this->releves_updated = $this->fusionElements($this->releves_updated, $action_report->releves_updated);

        // constants
        $this->constants_failed  = $this->fusionElements($this->constants_failed, $action_report->constants_failed);
        $this->constants_loaded  = $this->fusionElements($this->constants_loaded, $action_report->constants_loaded);
        $this->constants_stored  = $this->fusionElements($this->constants_stored, $action_report->constants_stored);
        $this->constants_updated = $this->fusionElements($this->constants_updated, $action_report->constants_updated);

        // calculated
        $this->calculated_failed  = $this->fusionElements($this->calculated_failed, $action_report->calculated_failed);
        $this->calculated_stored  = $this->fusionElements($this->calculated_stored, $action_report->calculated_stored);
        $this->calculated_updated = $this->fusionElements(
            $this->calculated_updated,
            $action_report->calculated_updated
        );

        // comment
        $this->comments_stored = $this->fusionElements($this->comments_stored, $action_report->comments_stored);

        // exception
        $this->exceptions = array_merge($this->exceptions, $action_report->exceptions);

        return $this;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     */
    private function fusionElements(array $arr1, array $arr2): array
    {
        foreach ($arr2 as $value) {
            if (!in_array($arr2, $value)) {
                $arr1[] = $value;
            }
        }

        return $arr1;
    }

    /**
     * @param CStoredObject $object
     *
     * @return bool
     */
    public function addUpdatedObject(CStoredObject $object): bool
    {
        return $this->addObject($object, 'updated');
    }

    /**
     * @param CStoredObject $object
     *
     * @return bool
     */
    public function addStoredObject(CStoredObject $object): bool
    {
        return $this->addObject($object, 'stored');
    }

    /**
     * @param CStoredObject $object
     *
     * @return bool
     */
    public function addFailedObject(CStoredObject $object): bool
    {
        return $this->addObject($object, 'failed');
    }

    /**
     * @param CAbstractConstant $constant
     *
     * @return bool
     */
    public function addCaculatedFailed(string $str): bool
    {
        $this->calculated_failed[] = $str;

        return true;
    }

    /**
     * @param CAbstractConstant $constant
     *
     * @return bool
     */
    public function addCaculatedStored(CAbstractConstant $constant): bool
    {
        $this->calculated_stored[] = $constant;

        return true;
    }

    /**
     * @param CAbstractConstant $constant
     *
     * @return bool
     */
    public function addCalculatedUpdated(CAbstractConstant $constant): bool
    {
        $this->calculated_updated[] = $constant;

        return true;
    }
}
