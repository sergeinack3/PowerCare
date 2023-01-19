<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
interface IConfigurationStrategy
{
    /**
     * Get the stored configurations of a given module
     *
     * @param string        $module Module name
     * @param CMbObjectSpec $spec   Specification object
     * @param bool          $static Get "static" configurations
     *
     * @return array
     */
    public function getStoredConfigurations($module, CMbObjectSpec $spec, bool $static = false);

    /**
     * Get the NULL stored configurations of a given module
     *
     * @param string        $module       Module name
     * @param CMbObjectSpec $spec         Specification object
     * @param string|null   $object_class The object class
     * @param int|null      $object_id    The object ID
     * @param bool          $static       Get the "static" configurations
     *
     * @return array
     */
    public function getNullStoredConfigurations(
        $module,
        CMbObjectSpec $spec,
        $object_class = null,
        $object_id = null,
        $static = false
    );

    /**
     * Change a particular configuration value
     *
     * @param string         $feature Feature
     * @param mixed          $value   Value
     * @param CMbObject|null $object  Host object
     * @param bool           $static  Store as a "static" configuration
     *
     * @return string|null
     * @throws Exception
     */
    public function setConfig($feature, $value, CMbObject $object = null, bool $static = false);

    /**
     * Get the alternative parameterized configurations of a given module for a given context
     *
     * @param string        $module       Module name
     * @param CMbObjectSpec $spec         Specification object
     * @param null          $object_class The object class
     * @param null          $object_id    The object ID
     * @param bool          $static       Get the "static" configurations
     *
     * @return array
     */
    public function getAltFeatures(
        $module,
        CMbObjectSpec $spec,
        $object_class = null,
        $object_id = null,
        $static = false
    );

    /**
     * @param CConfiguration $configuration
     * @param mixed|null     $value
     *
     * @return bool
     */
    public function objectValueModified(CConfiguration $configuration, $value = null): bool;
}
