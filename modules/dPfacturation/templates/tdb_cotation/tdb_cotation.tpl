{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet  script=edit_consultation}}

<table class="main">
  <thead>
  <tr>
    <th>{{tr}}mod-dPfacturation-tab-vw_tdb_cotation-long{{/tr}}</th>
  </tr>
  </thead>
</table>

{{mb_include template="tdb_cotation/tdb_cotation_filter"}}

<div id="consultations_list"></div>
