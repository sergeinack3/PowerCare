{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$affectations item=_affectation}}
  <tr>
    <td>{{mb_value object=$_affectation field=code}}</td>
    <td>{{$_affectation->_ref_code->libelle}}</td>
    <td>
      <button class="remove notext"
              title="{{tr}}Delete{{/tr}}"
              data-affectation-id="{{$_affectation->_id}}"
              data-code="{{$_affectation->code}}"
              data-form="ad_affectation_code"
              onclick="CodesAffectation.deleteCode(this)"></button>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="3">{{tr}}CCodeAffectation.none{{/tr}}</td>
  </tr>
{{/foreach}}
