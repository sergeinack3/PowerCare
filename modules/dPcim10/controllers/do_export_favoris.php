<?php 
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Ccam\CFavoriCCAM;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$user_id = CView::post('user_id', 'ref class|CMediusers notNull');

CView::checkin();

$favori = new CFavoriCIM10();
$favori->favoris_user = $user_id;

$type = 'Utilisateur';
$user = CMediusers::get($user_id);
$name = "$user->_user_last_name $user->_user_first_name";

/** @var CFavoriCCAM[] $favoris */
$favoris = $favori->loadMatchingList();
$tag_items = CMbObject::massLoadBackRefs($favoris, 'tag_items');
CMbObject::massLoadFwdRef($tag_items, 'tag_id');

$file = new CCSVFile();

$file->writeLine(
  array(
    'Propriétaire',
    'Tag',
    'Code'
  )
);

foreach ($favoris as $favori) {
  $favori->loadRefsTagItems();

  $tags = array();
  foreach ($favori->_ref_tag_items as $tag) {
    $tags[] = $tag->_view;
  }

  $file->writeLine(
    array(
      $user_id,
      implode('|', $tags),
      $favori->favoris_code
    )
  );
}

$file->stream('favoris_cim10_' . str_replace(' ', '_', $name));
CApp::rip();