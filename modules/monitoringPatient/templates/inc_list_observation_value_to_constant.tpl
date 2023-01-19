{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new me-primary me-float-left" onclick="ParamSurveillance.editConversion();">
  {{tr}}CObservationValueToConstant-title-create{{/tr}}
</button>
{{mb_include module=system template=inc_pagination total=$total step=30 page=$start changePage='ParamSurveillance.listConversion'}}

<table class="tbl">
  <tr>
    <th></th>
    <th>{{mb_title class=CObservationValueToConstant field=value_type_id}}</th>
    <th>{{mb_title class=CObservationValueToConstant field=value_unit_id}}</th>
    <th>{{mb_title class=CObservationValueToConstant field=constant_name}}</th>
    <th>{{mb_title class=CObservationValueToConstant field=conversion_ratio}}</th>
  </tr>
  {{foreach from=$conversions item=_conversion}}
    <tr class="alternate">
      <td>
        <button class="edit notext" type="button"
                onclick="ParamSurveillance.editConversion('{{$_conversion->_id}}')">{{tr}}Edit{{/tr}}</button>
      </td>
      <td>
        {{mb_value object=$_conversion field=value_type_id tooltip=1}}
      </td>
      <td>
        {{mb_value object=$_conversion field=value_unit_id tooltip=1}}
      </td>
      <td>
        {{tr}}CConstantesMedicales-{{$_conversion->constant_name}}{{/tr}}
      </td>
      <td>
        {{mb_value object=$_conversion field=conversion_operation}}
        {{mb_value object=$_conversion field=conversion_ratio}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">
        {{tr}}CObservationValueToConstant.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>