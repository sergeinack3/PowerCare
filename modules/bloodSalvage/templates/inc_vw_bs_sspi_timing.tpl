{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="timing{{$blood_salvage->_id}}" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="bloodSalvage" />
  <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
  <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
  <input type="hidden" name="operation_id" value="{{$blood_salvage->operation_id}}" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    <tr>
      <th class="category" colspan="6">{{tr}}msg-CBloodSalvage-timing{{/tr}}</th>
    </tr>
    {{assign var=submit value=submitBloodSalvageTiming}}
    {{assign var=blood_salvage_id value=$blood_salvage->_id}}
    {{assign var=form value=timing$blood_salvage_id}}
    <tr>
      {{mb_include module=salleOp template=inc_field_timing object=$blood_salvage field=_recuperation_end}}
      {{mb_include module=salleOp template=inc_field_timing object=$blood_salvage field=_transfusion_start}}
      {{mb_include module=salleOp template=inc_field_timing object=$blood_salvage field=_transfusion_end}}
    </tr>
  </table>
</form>
