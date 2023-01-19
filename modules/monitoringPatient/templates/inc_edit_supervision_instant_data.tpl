{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var item = $("list-{{$instant_data->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }
  });
</script>

<div class="small-info">
  Les données instantanées sont affichées sur la droite des graphiques.
</div>

<form name="edit-supervision-graph-instant-data" method="post" action="?m=dPpatients" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$instant_data}}
  {{mb_key object=$instant_data}}
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditInstantData" />
  <input type="hidden" name="datatype" value="NM" />
  <input type="hidden" name="coding_system" value="Kheops-Concentrator" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$instant_data colspan=2}}

    <tr>
      <th>{{mb_label object=$instant_data field=title}}</th>
      <td>{{mb_field object=$instant_data field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$instant_data field=value_type_id}}</th>
      <td>
        {{mb_field object=$instant_data field=value_type_id autocomplete="true,1,50,true,true"
        form="edit-supervision-graph-instant-data"}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$instant_data field=value_unit_id}}</th>
      <td>
        {{mb_field object=$instant_data field=value_unit_id autocomplete="true,1,50,true,true"
        form="edit-supervision-graph-instant-data"}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$instant_data field=size}}</th>
      <td>{{mb_field object=$instant_data field=size increment=true form="edit-supervision-graph-instant-data"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$instant_data field=color}}</th>
      <td>{{mb_field object=$instant_data field=color form="edit-supervision-graph-instant-data"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$instant_data field=disabled}}</th>
      <td>{{mb_field object=$instant_data field=disabled typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $instant_data->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'',objName:'{{$instant_data->_view|smarty:nodefaults|JSAttribute}}'},
                    SupervisionGraph.callbackEditInstantData
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
