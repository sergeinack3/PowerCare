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
    <th class="title">Légende</th>
  </tr>
  <tr class="ssr-prevu">
    <td>Séjour prévu</td>
  </tr>
  <tr>
    <td>Séjour en cours</td>
  </tr>
  <tr class="ssr-termine">
    <td>Séjour terminé</td>
  </tr>
  <tr class="ssr-annule">
    <td>Séjour annulé</td>
  </tr>
</table>