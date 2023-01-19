<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CAideSaisie;

CCanDo::check();

$user_id        = CView::get("user_id", "ref class|CMediusers");
$object_class   = CView::get("object_class", "str notNull");
$field          = CView::get("field", "str");

CView::checkin();
CView::enableSlave();

/** @var CMbObject $object */
$object = new $object_class;
CAideSaisie::$_load_lite = true;

$object->loadAides($user_id, null, null, null, $field, true);

$aides = array();
$by_tokens = array();
$owners = array(
  "g" => array(),
  "f" => array(),
  "u" => array(),
);

$fields = array(
  // Champs pour la recherche et l'affichage
  "n"     => "name",
  "t"     => "text",
  "_vd1"  => "_vw_depend_field_1",
  "_vd2"  => "_vw_depend_field_2",
  // Champs pour le tri
  "gid"   => "group_id",
  "fid"   => "function_id",
  "uid"   => "user_id",
  "d1"    => "depend_value_1",
  "d2"    => "depend_value_2",
  "f"     => "field"
);

CStoredObject::massLoadBackRefs($object->_aides_new, "hypertext_links");

$stop_words = array_flip(explode(" ", CAppUI::tr("CAideSaisie-stop_words")));

/** @var CAideSaisie $_aide */
foreach ($object->_aides_new as $_aide) {
  $_aide_lite = array();

  $owner_view = $_aide->loadRefOwner() ? $_aide->loadRefOwner()->_view : null;

  if ($_aide->group_id) {
    $owners["g"][$_aide->group_id] = $owner_view;
  }
  elseif ($_aide->function_id) {
    $owners["f"][$_aide->function_id] = $owner_view;
  }
  elseif ($_aide->user_id) {
    $owners["u"][$_aide->user_id] = $owner_view;
  }

  foreach ($fields as $abbrev => $_field) {
    if ($_aide->$_field === null) {
      continue;
    }

    // On ne rajoute la propriété text que si elle est différente de son nom
    if ($_field === "text") {
      if ($_aide->name !== $_aide->text) {
        $_aide_lite[$abbrev] = $_aide->text . "\n";
      }
      continue;
    }

    if ($_field === "name") {
      $_aide_lite[$abbrev] = $_aide->name . "\n";
      continue;
    }

    $_aide_lite[$abbrev] = $_aide->$_field;
  }

  $_links = $_aide->loadRefsHyperTextLink();
  if (count($_links)) {
    $_aide_lite["links"] = array();
    foreach ($_links as $_link) {
      $_aide_lite["links"][] = array(
        "id"   => (int)$_link->_id,
        "name" => $_link->name,
        "link" => $_link->link,
      );
    }
  }

  $aides[$_aide->_id] = $_aide_lite;

  $tokens = ($_aide->name !== $_aide->text ? "$_aide->name " : null) . $_aide->text;
  $tokens = CMbString::canonicalize($tokens);

  // Liste récupérée depuis http://www.regular-expressions.info/posixbrackets.html (Character class :punct:)
  $tokens = array_unique(preg_split("/[\s!\"\#$%&'()*+,\-\.\/:;<=>?@\[\]\\^_`{|}~]+/", $tokens));

  $nom_aide = CMbString::canonicalize($_aide->name);

  foreach ($tokens as $_token) {
    if ($_token === "") {
      continue;
    }

    if ($_token == $nom_aide || !array_key_exists($_token, $stop_words)) {
      if (!isset($by_tokens[$_token])) {
        $by_tokens[$_token] = array();
      }

      $by_tokens[$_token][] = (int)$_aide->_id;
    }
  }
}

$expire = time() + 3600;

CApp::json(
  array(
    "expire" => $expire,
    "data" => array(
      "aides"    => $aides,
      "by_token" => $by_tokens,
      "owners"   => $owners,
    )
  )
);
