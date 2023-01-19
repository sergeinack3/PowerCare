{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" name="editMutuelle">
  {{mb_key object=$mutuelle}}
  {{mb_class object=$mutuelle}}
  <table>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$mutuelle mb_field=code}}
      {{mb_field object=$mutuelle field=code}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$mutuelle mb_field=name}}
      {{mb_field object=$mutuelle field=name}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td colspan="2">
        <button type="button" class="button save" onclick="HealthInsurance.save(this.form);">
          {{tr}}Save{{/tr}}
        </button>
        {{if $mutuelle->code}}
          <button type="button" class="button trash" onclick="HealthInsurance.delete($V('code'));">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
