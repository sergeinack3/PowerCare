{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.setTabCount("list-{{$object_class}}", {{$params_total}});
  });

  changePage{{$object_class}} = function (page) {
    ParamSurveillance.list('{{$object_class}}', page);
  };
</script>

<button class="new me-primary" onclick="ParamSurveillance.edit('{{$object_class}}-0')" style="float: left;">
  {{tr}}{{$object_class}}-title-create{{/tr}}
</button>

{{mb_include module=system template=inc_pagination current=$start step=30 total=$params_total change_page="changePage$object_class"}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th class="narrow"></th>
    <th class="narrow">{{mb_title class=$object_class field=coding_system}}</th>
    <th class="narrow">{{mb_title class=$object_class field=code}}</th>
    {{if $object_class == "CObservationValueType"}}
      <th class="narrow">{{mb_title class=$object_class field=datatype}}</th>
    {{/if}}
    <th>{{mb_title class=$object_class field=label}}</th>
    {{if $object_class == "CObservationValueUnit"}}
      <th>{{mb_title class=$object_class field=display_text}}</th>
    {{/if}}
    <th>{{mb_title class=$object_class field=desc}}</th>
  </tr>

  {{foreach from=$params item=_param}}
    <tr>
      <td>
        {{if $_param->group_id}}
          <i class="far fa-hospital" title="Ce paramètre appartient à l'établissement courant"></i>
        {{/if}}
      </td>
      <td>
        <button class="edit notext compact" onclick="ParamSurveillance.edit('{{$_param->_guid}}')">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_param field=coding_system}}</td>
      <td>{{mb_value object=$_param field=code}}</td>
      {{if $object_class == "CObservationValueType"}}
        <td>{{mb_value object=$_param field=datatype}}</td>
      {{/if}}
      <td>
        {{if $_param->coding_system == "MD-Stream"}}
            {{assign var=trad_ObservationValue value="label"}}

            {{if $object_class == "CObservationValueUnit"}}
               {{assign var=trad_ObservationValue value="unit"}}
            {{/if}}

            {{tr}}CMonitoringConcentrator-{{$trad_ObservationValue}}-{{$_param->label}}{{/tr}}
        {{else}}
          {{mb_value object=$_param field=label}}
        {{/if}}
      </td>
      {{if $object_class == "CObservationValueUnit"}}
        <td>
        {{mb_value object=$_param field=display_text}}</th>
      {{/if}}
      <td>{{mb_value object=$_param field=desc}}</td>
    </tr>
  {{/foreach}}
</table>
