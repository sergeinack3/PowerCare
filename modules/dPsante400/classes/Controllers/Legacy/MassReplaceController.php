<?php

/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CIdSante400;

class MassReplaceController extends CLegacyController
{
    /**
     * @return void
     * @throws Exception
     */
    public function countTags(): void
    {
        $this->checkPermRead();

        $object_class = CView::get("object_class", "str");
        $tag          = CView::get("tag", "str");
        $values       = CView::get("values", "str");

        CView::checkin();

        if (!$object_class || !$tag || !$values) {
            $this->renderJson(['error_tag' => true]);
        }

        $values = array_filter(explode(',', $values));
        $values = array_map('trim', $values);

        $tags = $this->loadFilteredIdSante400($object_class, $tag, $values);
        $this->renderJson(['nb_tags' => count($tags)]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function editTags(): void
    {
        $this->checkPermRead();

        $object_class = CView::get("object_class", "str");
        $tag          = CView::get("tag", "str");
        $values       = CView::get("values", "str");
        $new_tag      = CView::get("new_tag", "str");

        CView::checkin();

        if (!$object_class || !$tag || !$values || !$new_tag) {
            $this->renderJson(['error_tag' => true]);
        }

        $success = [];
        $error   = [];

        $values = array_filter(explode(',', $values));
        $values = array_map('trim', $values);

        $tags = $this->loadFilteredIdSante400($object_class, $tag, $values);

        foreach ($tags as $_tag) {
            $_tag->tag = $new_tag;
            if ($_tag->store()) {
                $error[] = $_tag;
                continue;
            }
            $success[] = $_tag;
        }

        $this->renderJson(['nb_tags' => count($tags), 'nb_success' => count($success), 'nb_error' => count($error)]);
    }

    /**
     * @param string $object_class
     * @param string $tag
     * @param array  $values
     *
     * @return array
     * @throws Exception
     */
    private function loadFilteredIdSante400(string $object_class, string $tag, array $values): array
    {
        $id_sante400 = new CIdSante400();
        $ds          = $id_sante400->getDS();

        $where = [];

        if ($object_class) {
            $where[] = $ds->prepare('object_class = ?', $object_class);
        }

        if ($tag) {
            $where[] = $ds->prepare('tag = ?', $tag);
        }

        if (!empty($values)) {
            $where['id400'] = $ds->prepareIn($values);
        }

        return $id_sante400->loadList($where);
    }
}
