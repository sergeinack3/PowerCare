<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html;

/**
 * Description
 */
interface PurifierInterface
{
    /**
     * @param string $str
     *
     * @return string
     */
    public function purify(string $str): string;

    /**
     * @param string $str
     *
     * @return string
     */
    public function removeHtml(string $str): string;
}
