<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\Translation;

/**
 * Description
 */
class TranslationsController extends CLegacyController
{
    public function displayTranslations(): void
    {
        $this->checkPermEdit();

        $module    = CView::get("module", "str default|system");
        $language  = CView::get("language", "str default|" . CAppUI::pref('LOCALE'));
        $reference = CView::get("reference", "str default|" . CAppUI::pref('FALLBACK_LOCALE'));
        $start     = CView::get("start", 'num default|0');
        $step      = CView::get('step', 'num default|500');

        CView::checkin();

        $translation = new Translation($module, $language, $reference);
        $trads       = $translation->getTranslations();

        // liste des dossiers modules + common et styles
        $modules   = array_keys(CModule::getInstalled());
        $modules[] = "common";
        sort($modules);

        $items         = $translation->getItems();
        $counter_total = 0;
        if (isset($items['Other'])) {
            foreach ($items['Other'] as $_item) {
                $counter_total += count($_item);
            }
        }

        $this->renderSmarty(
            'mnt_traduction_classes',
            [
                'total_count'   => $translation->getTotalCount(),
                'local_count'   => $translation->getLocalCount(),
                'completion'    => $translation->getCompletion(),
                'items'         => $translation->getItems(),
                'archives'      => $translation->getArchives(),
                'completions'   => $translation->getCompletions(),
                'locales'       => $translation->getLanguages(),
                'modules'       => $modules,
                'module'        => $module,
                'trans'         => $trads,
                'language'      => $language,
                'reference'     => $reference,
                'ref_items'     => $translation->getRefItems(),
                'start'         => $start,
                'step'          => $step,
                'counter_total' => $counter_total,
            ]
        );
    }
}
