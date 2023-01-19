{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dependances value=$rhs->_ref_dependances->_ref_dependances_rhs_bilan}}
<table class="form">
  <tr>
    <th class="title" colspan="2">{{tr}}CDependancesRHS{{/tr}}</th>
  </tr>
  <tr>
    <th class="category">{{tr}}Category{{/tr}}</th>
    <th class="category">{{tr}}CDependancesRHS.degre{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=habillage}}</th>
    <td>{{mb_value object=$dependances field=habillage}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=deplacement}}</th>
    <td>{{mb_value object=$dependances field=deplacement}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=alimentation}}</th>
    <td>{{mb_value object=$dependances field=alimentation}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=continence}}</th>
    <td>{{mb_value object=$dependances field=continence}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=comportement}}</th>
    <td>{{mb_value object=$dependances field=comportement}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dependances field=relation}}</th>
    <td>{{mb_value object=$dependances field=relation}}</td>
  </tr>
</table>
