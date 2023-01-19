<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

/**
 * Description
 */
trait FieldsSIH
{
    /**
     * Compute document fields in key value format
     *
     * @param array $sections
     *
     * @return array
     */
    protected function computeFields(array $sections): array
    {
        $sections = array_map_recursive('utf8_encode', $sections, true);

        $fields = [];

        foreach ($sections as $section) {
            foreach ($section as $subsection) {
                if (isset($subsection['field'])) {
                    $fields[$subsection['field']] = $subsection['valueHTML'];
                    continue;
                }

                foreach ($subsection as $field) {
                    $fields[$field['field']] = $field['valueHTML'];
                }
            }
        }

        return $fields;
    }
}
