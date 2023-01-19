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
  <td>{{mb_value object=$_patient field=telephone1}}</td>
  <td>{{mb_value object=$_patient field=telephone2}}</td>
  <td class="button" rowspan="{{$nbSejours+1}}">
    <button class="tick" type="button" onclick="PatientHprim.select('{{$_patient->external_id}}')">
      {{tr}}Select{{/tr}}
    </button>
  </td>
</tr>
{{foreach from=$_patient->_ref_hprim21_sejours item=_sejour}}
<tr>
  <td colspan="3">
    {{$_sejour->_view}}
  </td>
</tr>
{{/foreach}}

</tbody>