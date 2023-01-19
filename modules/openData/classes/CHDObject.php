<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;

/**
 * Description
 */
class CHDObject extends CMbObject {

  public $hd_etablissement_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'hospi_diag';
    $spec->loggable = false;

    return $spec;
  }


  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['hd_etablissement_id'] = 'ref class|CHDEtablissement notNull';

    return $props;
  }

  /**
   * Return the fields to display
   *
   * @return array
   */
  function getDisplayFields() {
    $this->updateFormFields();

    $fields = $this->getPlainFields();
    unset($fields['hd_etablissement_id']);
    unset($fields[$this->_spec->key]);

    return $fields;
  }

  /**
   * Add all the years from $annees not presents in $array
   *
   * @param array $array         Array to add years to
   * @param array $annees        Years to add
   * @param array $object_fields Fields for each year
   *
   * @return array
   */
  static function addAllYears($array, $annees, $object_fields) {
    $object_annees = CMbArray::pluck($array, 'annee');

    foreach ($annees as $_annee) {
      if (!in_array($_annee, $object_annees)) {
        $new_act = array();
        foreach ($object_fields as $_field) {
          $new_act[$_field] = '';
        }
        $new_act['annee'] = $_annee;
        $new_act['empty'] = true;
        $array[] = $new_act;
      }
    }

    CMbArray::pluckSort($array, SORT_DESC, 'annee');

    return $array;
  }

  /**
   * Get the last year imported
   *
   * @return null|string
   */
  function getLastYear() {
    $query = new CRequest();
    $query->addSelect('distinct annee');
    $query->addTable($this->_spec->table);
    $query->addOrder('annee DESC');

    return $this->getDS()->loadResult($query->makeSelect());
  }
}
