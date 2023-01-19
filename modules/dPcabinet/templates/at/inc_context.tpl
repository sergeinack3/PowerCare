{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAccidentTravail-title-context{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{mb_label object=$at field=type}}
    </th>
    <td>
      {{mb_field object=$at field=type onchange="AvisArretTravail.checkType(this);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$at field=nature}}
    </th>
    <td>
      {{mb_field object=$at field=nature}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$accident_travail field=num_organisme}}</th>
    <td>{{mb_field object=$accident_travail field=num_organisme size=9}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$accident_travail field=feuille_at}}</th>
    <td>{{mb_field object=$accident_travail field=feuille_at typeEnum="checkbox"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$accident_travail field=constatations}}</th>
    <td>{{mb_field object=$accident_travail field=constatations form="createAT$uid" aidesaisie="validateOnBlur: 0"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$accident_travail field=consequences}}</th>
    <td>{{mb_field object=$accident_travail field=consequences}}</td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      {{mb_include module=cabinet template=at/inc_navigation actual='context' next="at_duration$uid"}}
    </td>
  </tr>
</table>
