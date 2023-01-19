{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if 'notifications'|module_active && @$modules.notifications->_can->read}}
  <script type="text/javascript">
    openFieldsModal = function () {
      var url = new Url('compteRendu', 'ajax_fields_template');
      url.addParam('class', 'CEvenementPatient');
      url.requestModal(600, 500);
    };

    insertField = function (elt) {
      var element = $(elt).get('fieldHtml').htmlDecode();
      FieldSelector.insertField(element, 'editTypeEvenement__notification_text_model');
      Control.Modal.close();
    };
  </script>
{{/if}}

<form name="editTypeEvenement" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$type}}
  {{mb_key   object=$type}}
  <input type="hidden" name="del" value="0" />
  <table class="form" style="width: 350px;">
    <tr>
      <th colspan="2" class="title {{if $type->_id}}modify{{/if}}">
        {{if $type->_id}}
          {{tr}}CTypeEvenementPatient-title-modify{{/tr}}
        {{else}}
          {{tr}}CTypeEvenementPatient-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$type field=libelle}}</th>
      <td>{{mb_field object=$type field=libelle style="width: 15em;"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$type field=function_id}}</th>
      <td>
        {{if $app->_ref_user->isAdmin()}}
          <select name="function_id" style="width: 15em;">
            <option value="">&mdash; {{tr}}CFunctions.none{{/tr}}</option>
            {{foreach from=$functions item=_function}}
              <option value="{{$_function->_id}}"
                      {{if $_function->_id == $type->function_id}}selected="selected"{{/if}}>
                {{$_function}}
              </option>
            {{/foreach}}
          </select>
        {{else}}
          {{$app->_ref_user->_ref_function}}
          <input name="function_id" type="hidden" value="{{$app->_ref_user->function_id}}" />
        {{/if}}
      </td>
    </tr>
    {{if 'notifications'|module_active && @$modules.notifications->_can->read}}
      <tr>
        <th>
          {{mb_label object=$type field=notification}}
        </th>
        <td>
          {{mb_field object=$type field=notification typeEnum='checkbox' onchange="\$('notifications').toggle();"}}
        </td>
      </tr>
      <tbody id="notifications"{{if !$type->notification}} style="display: none;"{{/if}}>
      <tr>
        <th>
          {{mb_label object=$type field=_notification_days}}
        </th>
        <td>
          {{mb_field object=$type field=_notification_days}} jours
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label object=$type field=_notification_text_model}}
          <br>
          <button type="button" class="list" onclick="openFieldsModal();">Champs</button>
        </th>
        <td>
          <input type="hidden" name="_store_notification" value="1">
          {{mb_field object=$type field=_notification_text_model rows="15"}}
        </td>
      </tr>
      </tbody>
    {{/if}}

    <tr>
      <th>{{tr}}CTypeEvenementPatient-Mailing{{/tr}}</th>
      <td>
        <input type="checkbox" name="mailing_model" onclick="EvtPatient.selectModel()" {{if $type->mailing_model_id}}checked{{/if}}>
      </td>
    </tr>

    <tr id="mailing_model" {{if !$type->mailing_model_id}}style="display: none;"{{/if}}>
      <th>{{tr}}CTypeEvenementPatient-mailing_model_id-court{{/tr}}</th>
      <td>
        <select name="mailing_model_id">
          <option value="">-- {{tr}}Choose{{/tr}}</option>
          {{foreach from=$mailing_models item=_model}}
            <option value="{{$_model->_id}}" {{if $type->mailing_model_id == $_model->_id}}selected{{/if}}>{{$_model}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
        <button type="button" class="trash" onclick="this.form.del.value = 1; this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>