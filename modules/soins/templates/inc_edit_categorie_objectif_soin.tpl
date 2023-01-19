{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close});">
  {{mb_key object=$categorie}}
  {{mb_class object=$categorie}}
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$categorie}}
    <tr>
      {{me_form_field nb_cells=2 mb_object=$categorie mb_field=libelle}}
        {{mb_field object=$categorie field=libelle}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$categorie mb_field=group_id}}
        <select name="group_id">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$groups item=_group}}
            <option value="{{$_group->_id}}" {{if $_group->_id == $categorie->group_id}}selected{{/if}}>
              {{$_group}}
            </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$categorie mb_field=description}}
        {{mb_field object=$categorie field=description}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$categorie mb_field=actif}}
        {{mb_field object=$categorie field=actif}}
      {{/me_form_bool}}
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $categorie->_id}}
          <button id="vw_categorie_button_modif_categorie" class="modify" type="submit">{{tr}}Validate{{/tr}}</button>
          <button id="vw_categorie_button_trash_categorie" class="trash" type="button" onclick="confirmDeletion(
              this.form, {
                ajax: true,
                objName: '{{$categorie->libelle|smarty:nodefaults|JSAttribute}}'
              },
              Control.Modal.close
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button id="vw_categorie_button_create_categorie" class="submit" type="submit">
            {{tr}}Create{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>