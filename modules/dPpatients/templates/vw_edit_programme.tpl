{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit_program" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {
        Control.Modal.close();
      });">
  {{mb_key object=$programme}}
  {{mb_class object=$programme}}
  <input type="hidden" name="del" value="" />

  <fieldset>
    <legend>{{tr}}CProgrammeClinique-Adding a program{{/tr}}</legend>

    <table class="main form">
      <tr>
        <th>{{mb_label class=$programme field=nom}}</th>
        <td>{{mb_field object=$programme field=nom}}</td>
      </tr>

      <tr>
        <th>{{mb_label class=$programme field=coordinateur_id}}</th>
        <td>
          <select name="coordinateur_id">
            {{mb_include module=mediusers template=inc_options_mediuser selected=$prat_id list=$praticiens}}
          </select>
        </td>
      </tr>

      <tr>
        <th>{{mb_label class=$programme field=description}}</th>
        <td>{{mb_field object=$programme field=description}}</td>
      </tr>

      <tr>
        <th>{{mb_label class=$programme field=annule}}</th>
        <td>{{mb_field object=$programme field=annule}}</td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          {{if !$programme->_id}}
            <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
          {{else}}
            <button type="submit" class="save">{{tr}}Edit{{/tr}}</button>
            <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true}, {onComplete: Control.Modal.close})">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    </table>
  </fieldset>
</form>
