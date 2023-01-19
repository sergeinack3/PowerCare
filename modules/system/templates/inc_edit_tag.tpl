{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  emptyParent = function(parent_id) {
    var form = getForm('edit_tag_' + parent_id);
    $V(form.elements.parent_id, '');
    $V(form.elements._parent_id, '');
  }
</script>

<form method="post" name="edit_tag_{{$tag->_guid}}" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="del" value="0"/>
  {{mb_key object=$tag}}
  {{mb_class object=$tag}}
  {{mb_field object=$tag field=object_class hidden=1}}
  {{mb_field object=$tag field=parent_id hidden=1}}

  <table class="form">
  {{mb_include module=system template=inc_form_table_header object=$tag}}

    <tr>
      <th style="width:50%;">{{mb_label object=$tag field=name}}</th>
      <td>{{mb_field object=$tag field=name}}</td>
    </tr>

    <tr>
      <th>Tag</th>
      <td>
        <input type="text" name="_parent_id" class="autocomplete" size="15" {{if $tag->_ref_parent}}value="{{$tag->_ref_parent}}"{{/if}}/>

        <button type="button" class="notext erase" onclick="emptyParent('{{$tag->_guid}}')">{{tr}}Empty{{/tr}}</button>

        <script>
          Main.add(function () {
            var form = getForm("edit_tag_{{$tag->_guid}}");
            var src = form.elements._parent_id;
            var dst = form.elements.parent_id;
            var url = new Url("system", "ajax_seek_autocomplete");

            url.addParam("object_class", "CTag");
            url.addParam("input_field", src.name);
            url.addParam("where[object_class]", "{{$tag->object_class}}");
            url.autoComplete(src, null, {
              minChars:           2,
              method:             "get",
              select:             "view",
              dropdown:           true,
              afterUpdateElement: function (field, selected) {
                var id = selected.getAttribute("id").split("-")[2];

                $V(dst, id);
              }
            });
          });
        </script>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$tag field=color}}</th>
      <td>
        {{assign var=guid value=$tag->_guid}}
        {{mb_field object=$tag field=color form="edit_tag_$guid"}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $tag->_id}}
          <button class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'le tag',objName:'{{$tag->_view|smarty:nodefaults|JSAttribute}}', ajax:1}, {onComplete: Control.Modal.close});">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="new">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

    {{if $tag->_id}}
      <tr>
        <td colspan="2">
          <div class="small-info">{{mb_label object=$tag field=_nb_items}} : {{mb_value object=$tag field=_nb_items}}</div>
        </td>
      </tr>
    {{/if}}
  </table>

</form>