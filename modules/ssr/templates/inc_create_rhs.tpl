{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$rhs->_id}}
  {{assign var=last_rhs value=$sejour->_ref_last_rhs}}
{{elseif isset($rhs->_ref_sejour->_ref_last_rhs|smarty:nodefaults)}}
  {{assign var=last_rhs value=$rhs->_ref_sejour->_ref_last_rhs}}
{{/if}}

<form name="Edit-CRHS-{{$rhs->_date_sunday}}" action="?m={{$m}}" method="post" onsubmit="return CotationRHS.onSubmitRHS(this)">
  {{mb_key object=$rhs}}
  {{mb_class object=$rhs}}
  {{mb_field object=$rhs field=sejour_id  hidden=1}}
  <input type="hidden" name="del" value="0" />

  {{if $last_rhs && $last_rhs->_id}}
    <input type="hidden" name="FPP" value="{{$last_rhs->FPP}}" />
    <input type="hidden" name="MMP" value="{{$last_rhs->MMP}}" />
    <input type="hidden" name="AE" value="{{$last_rhs->AE}}" />
    <input type="hidden" name="DAS" value="{{$last_rhs->DAS}}" />
    <input type="hidden" name="DAD" value="{{$last_rhs->DAD}}" />
  {{/if}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$rhs field=date_monday}}</th>
      <td>{{mb_field object=$rhs field=date_monday readonly=1}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$rhs field=_date_sunday}}</th>
      <td>{{mb_field object=$rhs field=_date_sunday readonly=1}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button class="new" type="submit">
          {{tr}}CRHS-title-create{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
