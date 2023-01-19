{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if $table->_id}}
    SupervisionGraph.listTableRows({{$table->_id}});
    {{/if}}

    var item = $("list-{{$table->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }
  });
</script>

<form name="edit-supervision-table" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$table}}
  {{mb_key object=$table}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditTable" />

  <table class="main form me-margin-top-0">
    {{mb_include module=system template=inc_form_table_header object=$table colspan=9}}

    <tr>
      <th>{{mb_label object=$table field=title}}</th>
      <td>{{mb_field object=$table field=title}}</td>
      <th>{{mb_label object=$table field=disabled typeEnum=checkbox}}</th>
      <td>{{mb_field object=$table field=disabled typeEnum=checkbox}}</td>

      <th>{{mb_label object=$table field=sampling_frequency}}</th>
      <td>{{mb_field object=$table field=sampling_frequency}}</td>
      <th>{{mb_label object=$table field=automatic_protocol}}</th>
      <td>{{mb_field object=$table field=automatic_protocol emptyLabel=Select}}</td>

      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $table->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {typeName: '', objName: '{{$table->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $table->_id}}
  <table class="main tbl me-margin-bottom-0 me-no-align me-no-border-radius-bottom">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CSupervisionTable-back-rows{{/tr}}
      </th>
    </tr>
  </table>
{{/if}}

<table class="main layout me-margin-top-0 me-no-align" style="height: 240px;">
  <tr>
    <td id="supervision-table-rows-list" style="width: 40%;"></td>
    <td id="supervision-table-row-editor">&nbsp;</td>
  </tr>
</table>