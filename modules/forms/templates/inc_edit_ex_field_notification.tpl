{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function(){
    var form = getForm("ex_field_notification-form");
    ExFieldPredicate.initAutocomplete(form, '{{$ex_class_id}}');

    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("edit", "1");
    url.addParam("input_field", "target_user_id_autocomplete_view");
    url.autoComplete(form.target_user_id_autocomplete_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.target_user_id, id);
      }
    });
  });
</script>

<form name="ex_field_notification-form" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_key object=$ex_field_notification}}
  {{mb_class object=$ex_field_notification}}
  <input type="hidden" name="callback" value="ExFieldNotification.updateCallback" />

  <table class="main form">
    <tr>
      {{assign var=object value=$ex_field_notification}}
      {{if $object->_id}}
        <th class="title modify text" colspan="4">
          {{mb_include module=system template=inc_object_idsante400}}
          {{mb_include module=system template=inc_object_history}}
          {{tr}}{{$object->_class}}-title-modify{{/tr}}
          '{{$object}}'
        </th>
      {{else}}
        <th class="title text me-th-new" colspan="4">
          {{tr}}{{$object->_class}}-title-create{{/tr}}
        </th>
      {{/if}}
    </tr>
    <tr>
      <th>{{mb_label object=$ex_field_notification field=predicate_id}}</th>
      <td>
        {{mb_field object=$ex_field_notification field=predicate_id hidden=true}}
        <input type="text" name="predicate_id_autocomplete_view" size="100"
               value="{{$ex_field_notification->_ref_predicate->_view}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$ex_field_notification field=target_user_id}}</th>
      <td>
        {{mb_field object=$ex_field_notification field=target_user_id hidden=true}}
        <input type="text" name="target_user_id_autocomplete_view" size="50"
               value="{{$ex_field_notification->_ref_target_user->_view}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$ex_field_notification field=subject}}</th>
      <td>{{mb_field object=$ex_field_notification field=subject size=80 onfocus="ExFieldNotification.focusInput(this)"}}</td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$ex_field_notification field=body}}
        <button type="button" class="help notext" onclick="App.openMarkdownHelp();">
          {{tr}}Markdown-help{{/tr}}
        </button>
      </th>
      <td>
        {{mb_field object=$ex_field_notification field=body rows=15 onfocus="ExFieldNotification.focusInput(this)"}}
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <button type="submit" class="modify">
          {{tr}}Save{{/tr}}
        </button>
        <button type="submit" class="modify me-secondary"
                onclick="return onSubmitFormAjax(this.form, function(){Control.Modal.close();})">
          {{tr}}common-action-Save and close{{/tr}}
        </button>

        {{if $ex_field_notification->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'la notification ',objName:'{{$ex_field_notification->_view|smarty:nodefaults|JSAttribute}}'}, function(){Control.Modal.close();})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="text">
        {{foreach from='Ox\Mediboard\System\Forms\CExClassFieldNotification::getFields'|static_call:"" item=_fields key=_class}}
          <fieldset>
            <legend>{{tr}}{{$_class}}{{/tr}}</legend>
            {{foreach from=$_fields item=_field}}
              <button class="add me-tertiary" value="{{$_field}}" type="button"
                      onclick="ExFieldNotification.insertField(this)">
                {{$_field|lower|ucfirst}}
              </button>
            {{/foreach}}
          </fieldset>
        {{/foreach}}
      </td>
    </tr>
  </table>
</form>
