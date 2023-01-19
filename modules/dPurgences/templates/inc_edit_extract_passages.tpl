{{*
 * @package Mediboard\dPurgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="form_edit_extract_passages" action="#" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_key object=$passages}}
  {{mb_class object=$passages}}
  <table class="form">
    <tr>
      <th>{{mb_label object=$passages field=date_extract}}</th>
      <td>{{mb_field object=$passages field=date_extract register=true form="form_edit_extract_passages"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$passages field=date_echange}}</th>
      <td>{{mb_field object=$passages field=date_echange register=true form="form_edit_extract_passages"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$passages field=message_valide}}</th>
      <td>{{mb_field object=$passages field=message_valide}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$passages field=nb_tentatives}}</th>
      <td>{{mb_field object=$passages field=nb_tentatives}}</td>
    </tr>

    <tr>
      <td colspan="2" class="me-text-align-center me-padding-top-8">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
