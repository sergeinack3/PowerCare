{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editDrawCategory" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="group_id" value="{{$cat->group_id}}"/>
  <input type="hidden" name="function_id" value="{{$cat->function_id}}"/>
  <input type="hidden" name="user_id" value="{{$cat->user_id}}"/>
  {{mb_key object=$cat}}
  {{mb_class object=$cat}}

  <table class="form">
    <tr>
      <th colspan="2" class="title text">
        {{mb_include module=system template=inc_object_idsante400 object=$cat}}
        {{mb_include module=system template=inc_object_history object=$cat}}
        {{mb_include module=system template=inc_object_notes object=$cat}}
        {{$cat}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$cat field=name}}</th>
      <td>{{mb_field object=$cat field=name}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$cat field=description}}</th>
      <td>{{mb_field object=$cat field=description}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $cat->_id}}
          <button class="save">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="return confirmDeletion(this.form, {ajax:true}, {onComplete: Control.Modal.close})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="save">{{tr}}Edit{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>