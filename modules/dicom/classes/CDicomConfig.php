<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\CMbFieldSpec;
use Ox\Interop\Eai\CExchangeDataFormatConfig;

/**
 * Description
 */
class CDicomConfig extends CExchangeDataFormatConfig {
  /**
   * @var array Config fields
   */
  public static $config_fields = array(
    'send_0032_1032',
    'value_0008_0060',
    'physician_separator',
    'uid_0020_000d'
  );

  /**
   * @var array Categories
   */
  public $_categories = array(
    'fields' => array(
      'send_0032_1032',
      'physician_separator'
    ),
    'values' => array(
      'value_0008_0060',
      'uid_0020_000d'
    )
  );

  /**
   * @var integer Primary key
   */
  public $dicom_config_id;

  public $send_0032_1032;
  public $value_0008_0060;
  public $physician_separator;
  public $uid_0020_000d;

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "dicom_configs";
    $spec->key    = "dicom_config_id";
    $spec->uniques['uniques'] = array('sender_id', 'sender_class');

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props['sender_class']        = 'enum list|CDicomSender show|0 default|CDicomSender';
    $props['sender_id']          .= ' back|config_dicom';
    $props['send_0032_1032']      = 'bool default|0';
    $props['value_0008_0060']     = 'str';
    $props['physician_separator'] = 'str';
    $props['uid_0020_000d']       = 'bool default|1';

    return $props;
  }

  /**
   * Get config fields
   *
   * @return array
   */
  function getConfigFields() {
    return $this->_config_fields = self::$config_fields;
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    if (!$this->_id) {
      $this->physician_separator = ' ';
    }

    parent::updateFormFields();
  }
}
