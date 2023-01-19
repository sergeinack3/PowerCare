{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="bloodSalvage" script="bloodSalvage"}}
<form name="timing{{$blood_salvage->_id}}" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="bloodSalvage" />
  <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
  <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
  <input type="hidden" name="operation_id" value="{{$blood_salvage->operation_id}}" />
  <input type="hidden" name="del" value="0" />
  <table class="form me-no-box-shadow">
    <tr>
      <th class="category" colspan="2">{{tr}}msg-CBloodSalvage-timing{{/tr}}</th>
    </tr>
    {{assign var=submit value=submitStartTiming ajax=1}}
    {{assign var=blood_salvage_id value=$blood_salvage->_id}}
    {{assign var=form value=timing$blood_salvage_id}}
    <tr>
      {{mb_include module=salleOp template=inc_field_timing object=$blood_salvage field=_recuperation_start}}
    </tr>
  </table>
</form>
