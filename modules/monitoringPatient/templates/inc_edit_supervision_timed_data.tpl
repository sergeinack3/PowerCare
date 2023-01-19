{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var item = $("list-{{$timed_data->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }

    getForm('edit-supervision-graph-timed-data').column.addSpinner({min: 1, max: 4, step: 1});
  });
</script>

<form name="edit-supervision-graph-timed-data" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$timed_data}}
  {{mb_key object=$timed_data}}
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditTimedData" />
  <input type="hidden" name="datatype" value="ST" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$timed_data colspan=2}}

    <tr>
      <th style="width: 40%;">{{mb_label object=$timed_data field=title}}</th>
      <td>{{mb_field object=$timed_data field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$timed_data field=value_type_id}}</th>
      <td>
        {{mb_field object=$timed_data field=value_type_id autocomplete="true,1,50,true,true" form="edit-supervision-graph-timed-data"}}
      </td>
    </tr>

    {{*
    <tr>
      <th>{{mb_label object=$timed_data field=period}}</th>
      <td>{{mb_field object=$timed_data field=period typeEnum=select emptyLabel="Libre"}}</td>
    </tr>
    *}}

    <tr>
      <th>{{mb_label object=$timed_data field=in_doc_template}}</th>
      <td>{{mb_field object=$timed_data field=in_doc_template typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$timed_data field=disabled}}</th>
      <td>{{mb_field object=$timed_data field=disabled typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$timed_data field=type}}</th>
      <td>
        {{mb_field object=$timed_data field=type typeEnum=select emptyLabel="Choose"
        onchange="this.form.elements.items.up('tr').setVisible(['enum','set'].indexOf(\$V(this))>-1); SupervisionGraph.showColumn(this);"}}
      </td>
    </tr>

    <tr id="column_list" {{if $timed_data->type != "enum" && $timed_data->type != "set"}} style="display: none;" {{/if}}>
      <th>{{mb_label object=$timed_data field=column}}</th>
      <td>{{mb_field object=$timed_data field=column numeric=true}}</td>
    </tr>

    <tr {{if $timed_data->type != "enum" && $timed_data->type != "set"}} style="display: none;" {{/if}}>
      <th>{{mb_label object=$timed_data field=items}}</th>
      <td>{{mb_field object=$timed_data field=items rows=5}}</td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $timed_data->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'', objName:'{{$timed_data->_view|smarty:nodefaults|JSAttribute}}'},
                    SupervisionGraph.callbackEditTimedData
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
