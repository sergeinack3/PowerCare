{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=object_guid value=$object->_guid}}
{{mb_default var=can_add value=true}}
<script>
  Main.add(function(){
    {{if ($object->_du_restant_patient) > 0 || "dPfacturation CReglement use_lock_acquittement"|gconf}}
      Reglement.updateBanque($('reglement-add-{{$object_guid}}_mode'));
    {{/if}}
  });
</script>

<!-- Formulaire de suppression d'un reglement (car pas possible de les imbriquer) -->
<form name="reglement-delete" action="#" method="post">
  <input type="hidden" name="m" value="dPcabinet" />
  <input type="hidden" name="del" value="1" />
  <input type="hidden" name="@class" value="CReglement" />
  <input type="hidden" name="reglement_id" value="" />
</form>

<form name="reglement-edit-date" action="#" method="post">
  <input type="hidden" name="@class" value="CReglement" />
  <input type="hidden" name="reglement_id" value="" />
  <input type="hidden" name="date" value="" />
</form>

<form name="edit-date-aquittement-{{$object_guid}}" action="#" method="post">
  {{mb_class object=$object}}
  {{mb_key   object=$object}}
  <input type="hidden" name="patient_date_reglement" value="" />
</form>

<form name="reglement-add-{{$object_guid}}" action="" method="post" onsubmit="return Reglement.addReglement(this);">
  <input type="hidden" name="m"             value="dPcabinet" />
  <input type="hidden" name="del"           value="0" />
  <input type="hidden" name="@class" value="CReglement" />
  <input type="hidden" name="emetteur"      value="patient" />
  <input type="hidden" name="object_id"     value="{{$object->_id}}" />
  <input type="hidden" name="object_class"  value="{{$object->_class}}" />
  <input type="hidden" name="reglements"    value="{{$object->_ref_reglements|@count}}" />
  <table class="main tbl">
    <tr>
      <th class="category" style="width: 50%;">
        {{if isset($facture|smarty:nodefaults)}}
          {{mb_include module=system template=inc_object_notes object=$facture}}
        {{/if}}
        {{mb_label class=CReglement field=mode}}
        ({{mb_label class=CReglement field=banque_id}})
      </th>
      <th class="category">{{mb_label class=CReglement field=reference}}</th>
      {{if "dPfacturation CReglement use_debiteur"|gconf}}
        <th class="category narrow">{{mb_label class=CReglement field=debiteur_id}}</th>
        <th class="category narrow">{{mb_label class=CReglement field=debiteur_desc}}</th>
      {{else}}
        <th class="category">{{mb_label class=CReglement field=tireur}}</th>
      {{/if}}
      <th class="category narrow">{{mb_label class=CReglement field=montant}}</th>
      <th class="category narrow">{{mb_label class=CReglement field=date}}</th>
      <th class="category narrow"></th>
    </tr>
    
    <!--  Liste des reglements deja effectués -->
    {{foreach from=$object->_ref_reglements item=_reglement}}
    <tr>
      <td>
        {{mb_value object=$_reglement field=mode}}
        {{if $_reglement->_ref_banque->_id}}
          ({{$_reglement->_ref_banque}})
        {{/if}}
        {{if $_reglement->num_bvr}}( {{$_reglement->num_bvr}} ){{/if}}
      </td>
      <td>{{mb_value object=$_reglement field=reference}}</td>
      {{if "dPfacturation CReglement use_debiteur"|gconf}}
        <td>{{$_reglement->_ref_debiteur}}</td>
        <td>{{mb_value object=$_reglement field=debiteur_desc}}</td>
      {{else}}
        <td>{{mb_value object=$_reglement field=tireur}}</td>
      {{/if}}
      <td style="text-align: right;">
        {{mb_value object=$_reglement field=montant}}
      </td>
      {{if $_reglement->lock}}
        <td>{{mb_value object=$_reglement field=date}}</td>
        <td> <button class="lock notext" disabled>{{mb_label object=$_reglement field=lock}}</button></td>
      {{else}}
        <td>
          {{if $can_add}}
            <input type="hidden" name="date_{{$_reglement->_id}}" class="{{$_reglement->_props.date}}" value="{{$_reglement->date}}" />
            <button type="button" class="submit notext" onclick="Reglement.editReglementDate('{{$_reglement->_id}}', this.up('td').down('input[name=date_{{$_reglement->_id}}]').value, '{{$_reglement->object_class}}', '{{$_reglement->object_id}}');"></button>
            <script>
              Main.add(function(){
                Calendar.regField(getForm('reglement-add-{{$object_guid}}').date_{{$_reglement->_id}});
              });
            </script>
          {{else}}
            {{mb_value object=$_reglement field=date}}
          {{/if}}
        </td>
        <td>
          <button type="button" class="remove notext" onclick="Reglement.delReglement('{{$_reglement->_id}}', '{{$_reglement->object_class}}', '{{$_reglement->object_id}}');"></button>
        </td>
      {{/if}}
    </tr>
    {{/foreach}}
    {{if $can_add}}
      {{if ($object->_du_restant_patient) > 0 || "dPfacturation CReglement use_lock_acquittement"|gconf}}
        {{assign var=banques value='Ox\Mediboard\Cabinet\CBanque::loadAllBanques'|static_call:null}}
        <tr>
          <td>
            <select name="mode" onchange="Reglement.updateBanque(this);" >
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$reglement->_specs.mode->_locales item=num key=key}}
                <option value="{{$key}}" {{if "dPfacturation CReglement use_mode_default"|gconf == $key}}selected{{/if}}>{{$num}}</option>
              {{/foreach}}
            </select>
            {{mb_field object=$reglement field=banque_id options=$banques style="display: none" id="choice_banque"}}
            {{if isset($object->_num_bvr|smarty:nodefaults)}}
              <select name="num_bvr" style="display:none;" onchange="Reglement.modifMontantBVR(this.form, this.value);" id="numero_bvr">
                <option value="0">&mdash; {{tr}}common-Choose a number{{/tr}}</option>
                {{foreach from=$object->_num_bvr item=num}}
                  <option value="{{$num}}">{{$num}}</option>
                {{/foreach}}
              </select>
            {{/if}}
          </td>
          <td>{{mb_field object=$reglement field=reference style="display: none" id="choice_reference"}}</td>
          {{if "dPfacturation CReglement use_debiteur"|gconf}}
            <td>
              <select name="debiteur_id" onchange="Reglement.updateDebiteur(this.value);" style="max-width: 150px;">
                <option value="">&mdash; {{tr}}common-Choose a debtor{{/tr}}</option>
                {{foreach from=$object->_ref_debiteurs item=debiteur}}
                  <option value="{{$debiteur->_id}}">{{$debiteur}}</option>
                {{/foreach}}
              </select>
            </td>
            <td id="reload_debiteur_desc">{{mb_field object=$reglement field=debiteur_desc}}</td>
          {{else}}
            <td>{{mb_field object=$reglement field=tireur id="choice_tireur"}}</td>
          {{/if}}
          <td><input type="text" class="currency notNull" size="4" maxlength="8" name="montant" value="{{$object->_du_restant_patient}}" /></td>
          <td>{{mb_field object=$reglement field=date register=true form="reglement-add-$object_guid" value="now"}}</td>
          <td>
            <button id="reglement_button_add" class="add notext" type="submit">{{tr}}Add{{/tr}}</button>
          </td>
        </tr>
      {{/if}}
      <tr>
        <td colspan="7" style="text-align: center;">
          {{mb_value object=$object field=_reglements_total_patient}} {{tr}}CReglement-regles-total{{/tr}},
          <strong id="reglements_strong_value">{{mb_value object=$object field=_du_restant_patient}} {{tr}}CReglement-restant-patient{{/tr}}</strong>
        </td>
      </tr>
      <tr>
        <td colspan="7" style="text-align: center;">
          <strong>
            {{mb_label object=$object field=patient_date_reglement}}
            {{mb_field object=$object field=patient_date_reglement register=true form="reglement-add-`$object_guid`"}}
            <button type="button" class="submit notext" onclick="Reglement.editAquittementDate(this.up('td').down('input[name=patient_date_reglement]').value, '{{$object->_id}}', '{{$object->_class}}');"></button>
          </strong>
        </td>
      </tr>
    {{/if}}
  </table>
</form>
