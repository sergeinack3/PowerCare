{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tbody class="hoverable">
{{assign var="trClass" value=""}}
{{assign var=nbSejours value=$_patient->_ref_hprim21_sejours|@count}}

<tr class="{{$trClass}}">
  <td rowspan="{{$nbSejours+1}}">
    {{$_patient->_view}}
  </td>
  <td>{{mb_value object=$_patient field="naissance"}}</td>
  <td>{{$_patient->telephone1}}</td>
  <td>{{$_patient->telephone2}}</td>
  <td></td>
</tr>
{{foreach from=$_patient->_ref_hprim21_sejours item=_sejour}}
<tr>
  <td colspan="3">
    {{$_sejour->_view}}
  </td>
  <td class="button">
    {{if $IPP}}
    <button class="tick" type="button" onclick="Sejour.select('{{$_sejour->external_id}}', null)">
      {{tr}}Select{{/tr}}
    </button>
    {{else}}
    <button class="tick" type="button" onclick="Sejour.select('{{$_sejour->external_id}}', '{{$_patient->external_id}}')">
      {{tr}}Select{{/tr}}
    </button>
    {{/if}}
  </td>
</tr>
{{/foreach}}

</tbody>