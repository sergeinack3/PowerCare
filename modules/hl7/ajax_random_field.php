<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$field = CValue::get('field');
$class = CValue::get('class');

/** @var CMbObject $object */
$object = new $class;
$ds = $object->getDS();

//R�cup�ration du nombre patient correspondant � nos crit�res
$query = new CRequest;
$query->addSelect("COUNT(*)");
$query->addTable($object->_spec->table);
$query->addWhereClause($field, "IS NOT NULL");
$value = $ds->loadResult($query->makeSelect());

//nombre al�atoire entre 1 et le resultat de notre recherche
$nombre = rand("1", $value);

//R�ucp�ration du champ voulut du patient choisit al�atoirement
$query = new CRequest;
$query->addSelect($field);
$query->addTable($object->_spec->table);
$query->addWhereClause($field, "IS NOT NULL");
$query->setLimit("$nombre, 1");
$value = $ds->loadResult($query->makeSelect());

echo json_encode(
  array(
    "value" => $value,
    "field" => $field,
  )
);