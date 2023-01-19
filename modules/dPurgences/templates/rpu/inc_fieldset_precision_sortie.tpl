{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $view_mode == "infirmier" || !$rpu->_id}}
  {{mb_return}}
{{/if}}

{{mb_default var=suffixe_form value=""}}

<fieldset class="me-small">
  <legend>Précisions sur la sortie</legend>
  <form name="editRPUDest{{$suffixe_form}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$rpu}}
    {{mb_key   object=$rpu}}
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="sejour_id" value="{{$rpu->sejour_id}}" />
    <input type="hidden" name="_bind_sejour" value="1" />
    <table class="form me-no-align me-no-box-shadow me-small-form" style="width: 100%;">
      <tr>
        {{assign var=notNull value=""}}
        {{if "dPurgences Display check_gemsa"|gconf == "2"}}
          {{assign var=notNull value="notNull"}}
        {{/if}}

        <th style="width: 10em;">{{mb_label object=$rpu field="gemsa" class=$notNull}}</th>
        <td>{{mb_field object=$rpu field="gemsa" class=$notNull style="width: 20em;" emptyLabel="Choose" onchange="this.form.onsubmit();"}}</td>
      </tr>


        <tr>
          <th>{{mb_label object=$rpu field="orientation"}}</th>
          <td>{{mb_field object=$rpu field="orientation" style="width: 20em;" emptyLabel="Choose" onchange="this.form.onsubmit();"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$rpu field="_destination"}}</th>
          <td>{{mb_field object=$rpu field="_destination" style="width: 20em;" emptyLabel="Choose" onchange="this.form.onsubmit();"}}</td>
        </tr>

    </table>
  </form>
</fieldset>
