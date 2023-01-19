<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;

CCanDo::checkRead();

?>

<table class="tbl">
  <tr>
    <th class="title">L�gende</th>
  </tr>
  <tr class="ssr-prevu">
    <td>S�jour pr�vu</td>
  </tr>
  <tr>
    <td>S�jour en cours</td>
  </tr>
  <tr class="ssr-termine">
    <td>S�jour termin�</td>
  </tr>
  <tr class="ssr-annule">
    <td>S�jour annul�</td>
  </tr>
</table>