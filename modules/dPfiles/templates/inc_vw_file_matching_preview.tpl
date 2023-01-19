{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

<script>
  Main.add(function () {
    {{if $next <= $count}}
      var form = getForm('import_files_by_regex');
      $V(form.elements.start, '{{$next}}');

      {{if $import && $continue}}
        $('import_btn').click();
      {{/if}}
    {{/if}}
  });
</script>

<hr />

{{tr}}common-Regular expression{{/tr}} : <code>{{$regex}}</code>

<hr />

<div class="small-info">
  {{tr var1=$count}}common-msg-%d files to import.{{/tr}}
</div>

<table class="main tbl">
  <tr>
    <th class="section">{{tr}}CFile{{/tr}}</th>
    <th class="section">{{tr}}common-Analysis{{/tr}}</th>
    <th class="section">{{tr}}common-Sibling object{{/tr}}</th>
    <th class="section">{{tr}}common-Related object{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$sorted_files key=_filename item=_infos}}
    {{assign var=_fields value=$_infos.fields}}
    {{assign var=_sibling_object value=$sibling_objects.$_filename}}
    {{assign var=_related_object value=$related_objects.$_filename}}

    {{if (!$_related_object || !$_related_object->_id) && (!$_sibling_object || !$_sibling_object->_id)}}
        <script>
          Main.add(function () {
            var form = getForm("bind-patient-{{$_filename}}");
            var element = form.elements._patient_autocomplete_view;
            var url = new Url("system", "ajax_seek_autocomplete");

            url.addParam("object_class", "CPatient");

            {{if !$app->_ref_user->isAdmin() && $function_distinct}}
              {{if $function_distinct == 1}}
                url.addParam('where[function_id]', '{{$app->_ref_user->function_id}}');
              {{else}}
                url.addParam('where[group_id]', '{{$g}}');
              {{/if}}
            {{/if}}

            url.addParam("input_field", element.name);
            url.autoComplete(element, null, {
              minChars:           2,
              method:             "get",
              select:             "view",
              dropdown:           true,
              afterUpdateElement: function (field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(form.elements.patient_id, id);

                if ($V(element) == "") {
                  $V(element, selected.down('.view').innerHTML);
                }
              }
            });
          });
        </script>
    {{/if}}

    {{assign var=css value=warning}}
    {{if ($_sibling_object && $_sibling_object->_id && $_fields) || ($_related_object && $_related_object->_id)}}
      {{assign var=css value=''}}
    {{/if}}
    <tr>
      <td class="narrow {{$css}}">
        <strong>{{$_filename}}</strong>
      </td>

      <td class="{{$css}}">
        {{foreach name=field_loop from=$_fields key=_field item=_value}}
          {{if is_scalar($_field)}}
            <span class="field_preview field_preview_{{$_field}}" title="{{$_field}}">{{$_value}}</span>
            {{if !$smarty.foreach.field_loop.last}}
              &nbsp;
            {{/if}}
          {{/if}}
        {{/foreach}}
      </td>

      <td class="{{$css}}">
        {{if $_sibling_object && $_sibling_object->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sibling_object->_guid}}');">
            {{$_sibling_object}}
          </span>
        {{else}}
          <div class="empty">{{tr}}CMbObject.none{{/tr}}</div>
        {{/if}}
      </td>

      <td class="{{$css}}">
        {{if $_related_object && $_related_object->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_related_object->_guid}}');">
            {{$_related_object}}
          </span>
        {{else}}
          <div class="empty">{{tr}}CMbObject.none{{/tr}}</div>
        {{/if}}
      </td>

      <td>
        {{if (!$_related_object || !$_related_object->_id) && (!$_sibling_object || !$_sibling_object->_id)}}
          <form name="bind-patient-{{$_filename}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            <input type="hidden" name="m" value="dPfiles" />
            <input type="hidden" name="dosql" value="do_bind_file_patient" />
            <input type="hidden" name="filename" value="{{$_filename}}" />
            <input type="hidden" name="patient_id" value="" onchange="this.form.onsubmit();" />

            <input type="text" name="_patient_autocomplete_view" value="" />
          </form>
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>
