{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editClassCategory" method="post" action="?" onsubmit="return onSubmitFormAjax(this, function(){location.reload();})">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="del" value="0" />
  {{mb_class object=$category}}
  {{mb_key object=$category}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$category colspan="2"}}

    <tr>
      <th>{{mb_label object=$category field=title}}</th>
      <td>{{mb_field object=$category field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=description}}</th>
      <td>{{mb_field object=$category field=description}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$category field=color}}</th>
      <td>{{mb_field object=$category field=color form=editClassCategory}}</td>
    </tr>

    <tr>
      <th></th>
      <td colspan="1">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $category->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'la catégorie ',objName:'{{$category->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
