<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CObjectIndexer;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkAdmin();
$search       = CView::get('search', 'str default|inj');
$remove_index = CView::get('remove_index', 'bool default|0');
CView::checkin();

$class = 'CConsultation';
if ($remove_index) {
    CObjectIndexer::removeIndex($class);
};

$object_indexer = new CObjectIndexer(
    'consult',
    $class,
    '1.0',
    function () {
        $cr = new CConsultation();

        return $cr->loadList(null, 'consultation_id DESC', '1000');
    }
);

$res = $object_indexer->search($search);
CApp::log(count($res));
CApp::log('system object indexer result', $res);
