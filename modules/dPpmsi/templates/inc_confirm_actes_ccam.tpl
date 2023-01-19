{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPccam" script="code_ccam" ajax="true"}}
<table class="tbl">
  <tr>
    <th class="title" colspan="9">Actes CCAM</th>
  </tr>
  <tr>
    <th class="category narrow">{{tr}}Delete{{/tr}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=executant_id}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=code_acte}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=code_activite}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=code_phase}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=modificateurs}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=code_association}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
    <th class="category">{{mb_title class=CActeCCAM field=_rembex}}</th>
  </tr>
  {{foreach from=$objet->_ref_actes_ccam item=curr_acte}}
  <!-- Couleur de l'acte -->
  {{if $curr_acte->code_association == $curr_acte->_guess_association}}
    {{assign var=bg_color value=9f9}}
  {{else}}
    {{assign var=bg_color value=fc9}}
  {{/if}}
  <tr>
    <td class="button">
      <form name="formDelActe-{{$curr_acte->_id}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
      <input type="hidden" name="m" value="dPsalleOp" />
      <input type="hidden" name="dosql" value="do_acteccam_aed" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="acte_id" value="{{$curr_acte->acte_id}}" />
      <input type="hidden" name="object_id" value="{{$curr_acte->object_id}}" />
      <input type="hidden" name="object_class" value="{{$curr_acte->object_class}}" />
      <button class="trash notext" type="button" onclick="confirmDeletion(this.form, {typeName:'l\'acte',objName:'{{$curr_acte->code_acte|smarty:nodefaults|JSAttribute}}'}, {onComplete: function(){loadSejour('{{$sejour->_id}}');} })">
        {{tr}}Delete{{/tr}}
      </button>
      </form>
    </td>
    <td class="text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_acte->_ref_executant}}</td>
    <td class="button">
      <a href="#" onclick="CodeCCAM.show('AAFA001', 'CConsultation');">{{mb_value object=$curr_acte field=code_acte}}</a>
     </td>
    <td class="button">{{mb_value object=$curr_acte field=code_activite}}</td>
    <td class="button">{{mb_value object=$curr_acte field=code_phase}}</td>
    <td class="button">{{mb_value object=$curr_acte field=modificateurs}}</td>
    <td class="button" style="background-color: #{{$bg_color}};">
      <form name="formAssoActe-{{$curr_acte->_id}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, { onComplete: function() {reloadListActes({{$objet->_guid}});} })">
      <input type="hidden" name="m" value="dPsalleOp" />
      <input type="hidden" name="dosql" value="do_acteccam_aed" />
      <input type="hidden" name="del" value="0" />
      {{mb_key object=$curr_acte}}
      {{mb_field object=$curr_acte field=code_acte hidden=true}}

      <select name="code_association" onchange="this.form.onsubmit()">
        <option value="" {{if !$curr_acte->code_association}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if !$curr_acte->_guess_association}}9f9{{else}}fc9{{/if}};">
          -
        </option>
        <option value="1" {{if $curr_acte->code_association == 1}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if $curr_acte->_guess_association == 1}}9f9{{else}}fc9{{/if}};">
          1
        </option>
        <option value="2" {{if $curr_acte->code_association == 2}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if $curr_acte->_guess_association == 2}}9f9{{else}}fc9{{/if}};">
          2
        </option>
        <option value="3" {{if $curr_acte->code_association == 3}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if $curr_acte->_guess_association == 3}}9f9{{else}}fc9{{/if}};">
          3
        </option>
        <option value="4" {{if $curr_acte->code_association == 4}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if $curr_acte->_guess_association == 4}}9f9{{else}}fc9{{/if}};">
          4
        </option>
        <option value="5" {{if $curr_acte->code_association == 5}}selected="selected"{{/if}}
        style="border-left: 4px solid #{{if $curr_acte->_guess_association == 5}}9f9{{else}}fc9{{/if}};">
          5
        </option>
      </select>
      </form>
    </td>
    <td class="button">{{mb_value object=$curr_acte field=montant_depassement}}</td>
    <td class="button">{{mb_value object=$curr_acte field=_rembex}}</td>
  </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="9">{{tr}}CActeCCAM.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
<table class="tbl">
  <tr>
    <th class="title" colspan="6">Actes NGAP</th>
  </tr>
  <tr>
    <th class="category narrow">{{tr}}Delete{{/tr}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=executant_id}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=code}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=quantite}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=montant_base}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=montant_depassement}}</th>
  </tr>
  {{foreach from=$objet->_ref_actes_ngap item=acte_ngap}}
    <tr>
      <td class="button">
        <form name="deleteActe-{{$acte_ngap->_id}}" action="?m={{$m}}" method="post">
          {{mb_key    object=$acte_ngap}}
          {{mb_class  object=$acte_ngap}}
          <input type="hidden" name="del" value="0" />
          <button class="trash notext" type="button" onclick="confirmDeletion(this.form, {typeName:'l\'acte',objName:'{{$acte_ngap->code|smarty:nodefaults}}'}, {onComplete: function(){loadSejour('{{$sejour->_id}}');} })">
            {{tr}}Delete{{/tr}}
          </button>
        </form>
      </td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$acte_ngap->_ref_executant}}</td>
      <td class="button">{{mb_value object=$acte_ngap field=code}}</td>
      <td class="button">{{mb_value object=$acte_ngap field=quantite}}</td>
      <td class="button">{{mb_value object=$acte_ngap field=montant_base}}</td>
      <td class="button">{{mb_value object=$acte_ngap field=montant_depassement}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">{{tr}}CActeNGAP.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>