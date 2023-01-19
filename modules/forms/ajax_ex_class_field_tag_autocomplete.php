<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Forms\Tag\AbstractCExClassFieldTag;
use Ox\Mediboard\Forms\Tag\CExClassFieldTagFactory;

CCanDo::checkEdit();

$input_field = CView::get('input_field', 'str notNull');
$keywords    = trim(CView::get($input_field, 'str'));

CView::checkin();

$keywords = ($keywords) ?? '';

$tags = [];

foreach (CExClassFieldTagFactory::getTags() as $_tag) {
  $tags[] = CExClassFieldTagFactory::getTag($_tag);
}

if ($keywords) {
  $tags = array_filter(
    $tags,
    function (AbstractCExClassFieldTag $_tag) use ($keywords) {
      return (strpos($_tag->getName(), $keywords) !== 0);
    }
  );
}

$smarty = new CSmartyDP();
$smarty->assign('keywords', $keywords);
$smarty->assign('tags', $tags);
$smarty->display('inc_ex_class_field_tags_autocomplete');
