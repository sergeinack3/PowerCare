{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-constant-{{$constant->_id}}" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: function() {refreshConstants(); Control.Modal.close(); }});">
  {{mb_class object=$constant}}
  {{mb_key object=$constant}}

  {{mb_field object=$constant field=_unite_ta hidden=1}}
  {{mb_field object=$constant field=_unite_glycemie hidden=1}}
  {{mb_field object=$constant field=_unite_cetonemie hidden=1}}
  {{mb_field object=$constant field=context_class hidden=1}}
  {{mb_field object=$constant field=context_id hidden=1}}
  {{mb_field object=$constant field=patient_id hidden=1}}
  <input type="hidden" name="del" value="0" />
  {{if !$can_edit}}
    <div class="small-warning">
      {{if $disable_edit_motif == 'timeout'}}
        {{tr var1=$modif_timeout}}CConstantes-Medicales-msg-modif-timeout-%s{{/tr}}
      {{else}}
        {{tr}}CConstantes-Medicales-msg-edit_not_creator{{/tr}}
      {{/if}}
    </div>
  {{/if}}
  <table class="tbl">
    <tr>
      <th class="category" colspan="2">{{tr}}Name{{/tr}}</th>
      <th class="category" colspan="2">{{tr}}Value{{/tr}}</th>
    </tr>

    {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes"}}
    {{foreach from=$constants_list key=_constant item=_params}}
      {{if $_constant.0 != '_' && $constant->$_constant}}
        <tr class="alternate">
          <td style="text-align: left;">
            <label for="{{$_constant}}" title="{{tr}}CConstantesMedicales-{{$_constant}}-desc{{/tr}}">
              {{tr}}CConstantesMedicales-{{$_constant}}{{/tr}}
            </label>
          </td>
          <td class="narrow">
            <button type="button" class="comment notext" tabindex="-1" style="float: right;"
                    onclick="editComment('{{$_constant}}', '{{$constant->_id}}', {{if $_constant|array_key_exists:$constant->_refs_comments}}'{{$comment->_id}}'{{else}}null{{/if}});">
              {{tr}}CConstantComment-action-create{{/tr}}
            </button>
          </td>
          <td style="text-align: center">
            {{if array_key_exists('formfields', $_params) && !array_key_exists('readonly', $_params)}}
              {{foreach from=$_params.formfields item=_formfield_name key=_key name=_formfield}}
                {{assign var=_style value="width:1.7em;"}}
                {{assign var=_size value=2}}
                {{if $_params.formfields|@count == 1}}
                  {{assign var=_style value=""}}
                  {{assign var=_size value=3}}
                {{/if}}

                {{if !$smarty.foreach._formfield.first}}/{{/if}}
                {{mb_field object=$constant field=$_params.formfields.$_key size=$_size style=$_style}}
              {{/foreach}}
            {{else}}
              {{mb_field object=$constant field=$_constant size="3"}}
              {{* Ugly fix for adding the formfield _poids_g. Without this field, it will be impossible to modify the weight *}}
              {{if $_constant == 'poids'}}
                {{mb_field object=$constant field='_poids_g' hidden=true}}
              {{/if}}
            {{/if}}
          </td>

          <td>
            {{if $_params.unit}}
              <span>{{$_params.unit}}</span>
            {{/if}}
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
    <tr>
      <td colspan="4" style="text-align: center;">
        {{assign var=constant_id value=$constant->_id}}
        {{mb_field object=$constant field=datetime form="edit-constant-$constant_id" register=true}}
      </td>
    </tr>
    <tr>
      <td colspan="4" style="text-align: center;">
        {{mb_field object=$constant field=comment placeholder="Commentaire" rows=2 form="edit-constant-$constant_id" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
      </td>
    </tr>
    <tr>
      <td colspan="4" style="text-align: center;">
        {{if $can_edit}}
          <button class="modify singleclick" type="submit">
            {{tr}}Save{{/tr}}
          </button>
          <button class="trash" type="button" onclick="$V(this.form.del, 1); this.form.onsubmit();">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="cancel" type="button" onclick="Control.Modal.close();">
            {{tr}}Close{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>