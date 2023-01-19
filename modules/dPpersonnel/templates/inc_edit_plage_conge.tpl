{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changeRemplacant = function(elt) {
    if ($V(elt)) {
      $('see_retrocession').show();
    }
    else {
      $('see_retrocession').hide();
    }
  }
</script>
{{assign var=see_retrocession value="personnel global see_retrocession"|gconf}}
{{unique_id var=editplage_id}}
<form name="editplage-{{$editplage_id}}" action="" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {
        {{if !$is_modal}}
          PlageConge.loadUser('{{$plageconge->user_id}}', '{{$plageconge->plage_id}}');
          changedate('');
        {{else}}
          Control.Modal.close();
          if ($('vw_user')) {
            PlageConge.loadUser('{{$plageconge->user_id}}', '{{$plageconge->plage_id}}');
          }
          else {
            document.location.reload();
          }
        {{/if}}
});">
  {{mb_key object=$plageconge}}
  {{mb_class object=$plageconge}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="user_id" value="{{$plageconge->user_id}}" />
  {{if !$is_modal}}
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />
    <input type="hidden" name="callback" value="PlageConge.edit" />
  {{/if}}
  <table class="form">
    {{if !$is_modal}}
      <tr>
        <td colspan="2">
          <button class="new" type="button" onclick="PlageConge.edit('','{{$plageconge->user_id}}');">
            {{tr}}CPlageConge-title-create{{/tr}}
          </button>
        </td>
      </tr>
    {{/if}}
    {{if $plageconge->_id}}
      <tr>
        <th class = "title modify text" colspan="6">
          {{mb_include module=system template=inc_object_notes   object=$plageconge}}
          {{mb_include module=system template=inc_object_history object=$plageconge}}
          {{tr}}CPlageConge-title-modify{{/tr}} {{$plageconge}}
        </th>
      </tr>
    {{else}}
      <tr>
        <th class = "title text" colspan="6">
         {{tr}}CPlageConge-title-create{{/tr}} {{tr}}For{{/tr}} {{$user->_user_last_name}} {{$user->_user_first_name}}
        </th>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$plageconge field=libelle}}</th>
      <td>{{mb_field object=$plageconge field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$plageconge field=date_debut}}</th>
      <td>
        {{mb_field object=$plageconge field=date_debut form="editplage-$editplage_id" register=true}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$plageconge field=date_fin}}</th>
      <td>
        {{mb_field object=$plageconge field=date_fin form="editplage-$editplage_id" register=true}}
      </td>
    </tr>

    {{if "personnel CPlageConge show_replacer"|gconf}}
    <tr>
      <th>{{mb_label object=$plageconge field=replacer_id}}</th>
      <td>
        <select name="replacer_id" class="{{$plageconge->_specs.replacer_id}}"
                {{if $see_retrocession}}onchange="changeRemplacant(this);"{{/if}}>
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$replacers selected=$plageconge->replacer_id}}
        </select>
      </td>
    </tr>
    {{/if}}

    {{if $see_retrocession}}
      <tr id="see_retrocession" {{if !$plageconge->replacer_id}}style="display:none;"{{/if}}>
        <th>{{mb_label object=$plageconge field=pct_retrocession}}</th>
        <td>{{mb_field object=$plageconge field=pct_retrocession size="2" increment=true form="editplage-$editplage_id"}}</td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="6" class="button">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
        {{if $plageconge->_id}}
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {typeName: 'la plage', objName:'{{$plageconge->_view|smarty:nodefaults|JSAttribute}}', ajax :true},
                                           PlageConge.loadUser.curry({{$plageconge->user_id}}))">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
   