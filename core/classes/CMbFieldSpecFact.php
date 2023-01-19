<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\FieldSpecs\CBirthDateSpec;
use Ox\Core\FieldSpecs\CBoolSpec;
use Ox\Core\FieldSpecs\CCodeSpec;
use Ox\Core\FieldSpecs\CColorSpec;
use Ox\Core\FieldSpecs\CCurrencySpec;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CDateTimeSpec;
use Ox\Core\FieldSpecs\CEmailSpec;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CER7Spec;
use Ox\Core\FieldSpecs\CFloatSpec;
use Ox\Core\FieldSpecs\CGuidSpec;
use Ox\Core\FieldSpecs\CHPRSpec;
use Ox\Core\FieldSpecs\CHtmlSpec;
use Ox\Core\FieldSpecs\CIpAddressSpec;
use Ox\Core\FieldSpecs\CNumcharSpec;
use Ox\Core\FieldSpecs\CNumSpec;
use Ox\Core\FieldSpecs\CPasswordSpec;
use Ox\Core\FieldSpecs\CPctSpec;
use Ox\Core\FieldSpecs\CPhoneSpec;
use Ox\Core\FieldSpecs\CPhpSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CSetSpec;
use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Core\FieldSpecs\CTextSpec;
use Ox\Core\FieldSpecs\CTimeSpec;
use Ox\Core\FieldSpecs\CURISpec;
use Ox\Core\FieldSpecs\CURLSpec;
use Ox\Core\FieldSpecs\CXmlSpec;

/**
 * CFieldSpec factory for prop serialized definitions
 * 
 * @todo Memory caching
 */
class CMbFieldSpecFact {
  
  static $classes = array(
    "enum"         => CEnumSpec::class,
    "set"          => CSetSpec::class,
    "str"          => CStrSpec::class,
    "text"         => CTextSpec::class,
    "num"          => CNumSpec::class,
    "float"        => CFloatSpec::class,
    "date"         => CDateSpec::class,
    "time"         => CTimeSpec::class,
    "dateTime"     => CDateTimeSpec::class,
    "bool"         => CBoolSpec::class,
    "code"         => CCodeSpec::class,
    "pct"          => CPctSpec::class,
    "birthDate"    => CBirthDateSpec::class,
    "phone"        => CPhoneSpec::class,
    "ref"          => CRefSpec::class,
    "numchar"      => CNumcharSpec::class,
    "currency"     => CCurrencySpec::class,
    "email"        => CEmailSpec::class,
    "password"     => CPasswordSpec::class,
    "html"         => CHtmlSpec::class,
    "xml"          => CXmlSpec::class,
    "php"          => CPhpSpec::class,       // @todo: Make a sourceCode spec/
    "er7"          => CER7Spec::class,
    "hpr"          => CHPRSpec::class,
    "ipAddress"    => CIpAddressSpec::class,
    "url"          => CURLSpec::class,
    "uri"          => CURISpec::class,
    "color"        => CColorSpec::class,
    "guid"         => CGuidSpec::class,
  );
   
  /**
   * Returns a spec object for an object field's prop
   * @throws Exception
   *
   * @param CModelObject $object  The object
   * @param string       $field   The field name
   * @param string       $prop    The prop string serializing the spec object options
   * @param array        $options Options
   * 
   * @return CMbFieldSpec Corresponding spec instance
   */
  static function getSpec(CModelObject $object, $field, $prop, $options = array()) {
    return self::getSpecWithClassName($object->_class, $field, $prop, $options);
  }

  /**
   * Returns a spec object for an object's field from a class name
   *
   * @param string $class   The class name
   * @param string $field   The field name
   * @param string $prop    The prop string serializing the spec object options
   * @param array  $options Options
   *
   * @throws Exception
   * @return CMbFieldSpec
   */
  static function getSpecWithClassName($class, $field, $prop, $options = array()) {
    /** @var CMbFieldSpec $spec_class */
    $spec_class = CMbFieldSpec::class;

    // Get Spec type
    $first_space = strpos($prop, " ");
    $spec_type = $first_space === false ? $prop : substr($prop, 0, $first_space);

    // Get spec class
    if ($spec_type && (null == $spec_class = CMbArray::get(self::$classes, $spec_type))) {
      throw new Exception("Invalid spec '$prop' for field '$class::$field'");
    }

    return new $spec_class($class, $field, $prop, $options);
  }

  /**
   * Returns a spec object for an object's field from a class name
   *
   * @param string $class   The class name
   * @param string $field   The field name
   * @param array  $prop    The prop array containing the spec object options
   * @param array  $options Options
   *
   * @throws Exception
   * @return CMbFieldSpec
   */
  static function getComplexSpecWithClassName($class, $field, $prop, $options = array()) {
    $spec_class = "CMbFieldSpec";

    // Get Spec type
    $spec_type = array_shift($prop);

    // Get spec class
    if ($spec_type && (null == $spec_class = CMbArray::get(self::$classes, $spec_type))) {
      throw new Exception("Invalid spec '$spec_type' for field '$class::$field'");
    }

    /** @var CMbFieldSpec $spec */
    $spec = new $spec_class($class, $field);
    $vars = get_object_vars($spec);
    foreach ($prop as $name => $value) {
      if (array_key_exists($name, $vars)) {
        $spec->$name = $value;
      }
      else {
        throw new Exception(
          "L'option '$name' trouvée dans '$spec_class::$field' est inexistante dans la spec de classe 'get_class($spec)'"
        );
      }
    }

    return $spec;
  }
}
