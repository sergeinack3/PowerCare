{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var item = $("list-{{$picture->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }
  });
</script>

<form name="edit-supervision-graph-timed-picture" method="post" action="?m=dPpatients" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$picture}}
  {{mb_key object=$picture}}
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditTimedPicture" />
  <input type="hidden" name="datatype" value="FIL" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$picture colspan=2}}

    <tr>
      <th>{{mb_label object=$picture field=title}}</th>
      <td>{{mb_field object=$picture field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$picture field=value_type_id}}</th>
      <td>{{mb_field object=$picture field=value_type_id autocomplete="true,1,50,true,true" form="edit-supervision-graph-timed-picture"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$picture field=in_doc_template}}</th>
      <td>{{mb_field object=$picture field=in_doc_template typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$picture field=disabled}}</th>
      <td>{{mb_field object=$picture field=disabled typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $picture->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'', objName:'{{$picture->_view|smarty:nodefaults|JSAttribute}}'},
                    SupervisionGraph.callbackEditTimedPicture
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $picture->_id}}
  <table class="main tbl">
    <tr>
      <th class="category">Images</th>
    </tr>
    <tr>
      <td>
        <button onclick="SupervisionGraph.chosePredefinedPicture({{$picture->_id}})" class="new me-tertiary">Images prédéfinies</button>
      </td>
    </tr>
    <tr id="files_{{$picture->_guid}}">
      <script type="text/javascript">
        File.register('{{$picture->_id}}', '{{$picture->_class}}', 'files_{{$picture->_guid}}');
      </script>
    </tr>
  </table>
{{/if}}
