<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export\Description;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Logger\LoggerLevels;

/**
 * Generate CXMLPatientExportInstanceDescription for each classes linked from the first instance pass to genereInfos
 * and linked with fw_tree or back_tree.
 * Generate only on CXMLPatientExportInstanceDescription per class.
 */
class CXMLPatientExportInfosGenerator
{
    /** @var array */
    private $handled_classes = [];

    /** @var array */
    private $fw_tree = [];

    /** @var array */
    private $back_tree = [];

    /** @var CXMLPatientExportInstanceDescription[] */
    private $descriptions = [];

    public function __construct(array $fw_tree, array $back_tree)
    {
        $this->fw_tree   = $fw_tree;
        $this->back_tree = $back_tree;
    }

    /**
     * Build the infos for each field of $instance.
     * Generate the infos for fw_refs and back_refs if they are in the fw_tree or back_tree.
     *
     * @throws Exception
     */
    public function generateInfos(CStoredObject $instance): array
    {
        $short_class = CClassMap::getSN($instance);

        // Create data for current instance
        $this->descriptions[$short_class] = $this->generateInfosForClass($instance);

        $this->handled_classes[] = get_class($instance);

        if (isset($this->fw_tree[$short_class])) {
            $this->buildInfosForFwRefs($instance, $this->fw_tree[$short_class]);
        }

        if (isset($this->back_tree[$short_class])) {
            $this->buildInfosForBackRefs($instance, $this->back_tree[$short_class]);
        }

        return $this->descriptions;
    }

    /**
     * Build the infos for a fw_ref.
     * If the fw_ref is a meta ref, add each class from the list. If no list, skip the ref.
     *
     * @throws Exception
     */
    private function buildInfosForFwRefs(CStoredObject $instance, array $fw_tree): void
    {
        foreach ($fw_tree as $field_name) {
            if (!$this->isRefField($instance, $field_name)) {
                continue;
            }

            /** @var CRefSpec $fw_ref */
            $fw_ref = $instance->_specs[$field_name];

            if (!$this->isFiniteMetaRef($instance, $fw_ref)) {
                continue;
            }

            if ($fw_ref->meta) {
                // If $fw_ref is a meta its meta field is an enum (or has been cancemed by isFiniteMetaRef)
                $classes = ($instance->_specs[$fw_ref->meta])->_list;
            } else {
                $classes = [$fw_ref->class];
            }

            // Handle meta specs with finite number of available classes
            foreach ($classes as $class_name) {
                $fw_instance = new $class_name();
                if ($this->isAlreadyHandled($fw_instance)) {
                    continue;
                }

                $this->generateInfos($fw_instance);
            }
        }
    }

    /**
     * Build the infos for a back_ref.
     *
     * @throws Exception
     */
    private function buildInfosForBackRefs(CStoredObject $instance, array $back_tree): void
    {
        $instance->makeAllBackSpecs();
        foreach ($back_tree as $back_name) {
            if (!isset($instance->_backSpecs[$back_name])) {
                continue;
            }

            $back_spec = $instance->_backSpecs[$back_name];

            $back_instance = new $back_spec->class();

            if ($this->isAlreadyHandled($back_instance)) {
                continue;
            }

            $this->generateInfos($back_instance);
        }
    }

    /**
     * Build a CXMLPatientExportInstanceDescription using the instance.
     *
     * @throws Exception
     */
    private function generateInfosForClass(CStoredObject $instance): CXMLPatientExportInstanceDescription
    {
        $description = new CXMLPatientExportInstanceDescription($instance);

        foreach ($instance->getPlainFields() as $field_name => $value) {
            try {
                $description->add($field_name);
            } catch (CMbException $e) {
                CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
                continue;
            }
        }

        return $description;
    }

    private function isRefField(CStoredObject $instance, string $field_name): bool
    {
        return property_exists($instance, $field_name) && $instance->_specs[$field_name] instanceof CRefSpec;
    }

    private function isFiniteMetaRef(CStoredObject $instance, CRefSpec $spec): bool
    {
        if (!$spec->meta) {
            return true;
        }

        return isset($instance->_specs[$spec->meta]) && $instance->_specs[$spec->meta] instanceof CEnumSpec;
    }

    private function isAlreadyHandled(CStoredObject $instance): bool
    {
        return in_array(get_class($instance), $this->handled_classes);
    }
}
