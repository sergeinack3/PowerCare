{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=in_modal_administration value=0}}
{{assign var=acte_id value=$_acte->_id}}
{{assign var=count_codes_csarr value=0}}
{{if isset($_evenement|smarty:nodefaults)}}
  {{assign var=evenement_id value=$_evenement->_id}}
{{else}}
  {{assign var=evenement_id value=0}}
{{/if}}

<div class="editLineCsarr" data-code="{{$_acte->code}}" style="min-height: 22px;">
  <label>
    {{if $in_modal_administration}}
      <input type="checkbox" class="csarr" value="{{$acte_id}}-{{$_acte->code}}" {{if $_acte->default}}checked{{/if}}/>
    {{/if}}
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
      <code>{{$_acte->code}}</code>
    </strong>
  </label>
  {{foreach from=$_acte->_ref_activite_csarr->_ref_modulateurs item=_modulateur}}
    <label title="{{$_modulateur->_libelle}}">
      <input type="checkbox" class="modulateur" id="modulateur_{{$_modulateur->modulateur}}-{{$_acte->code}}-{{$evenement_id}}"
             {{if !$in_modal_administration && in_array($_modulateur->modulateur, $_acte->_modulateurs)}}checked{{/if}}
              {{if !$in_modal_administration && $_evenement->realise}}disabled{{/if}}
             value="{{$acte_id}}-{{$_modulateur->modulateur}}" />
      {{$_modulateur->modulateur}}
    </label>
  {{/foreach}}
  <label>
    <select name="extension" style="width: 150px;" class="extension" id="extension-{{$_acte->code}}-{{$evenement_id}}"
              {{if !$in_modal_administration && $_evenement->realise}}disabled{{/if}}>
      <option value="{{$acte_id}}-">&dash; {{tr}}CActeCsARR-extension{{/tr}}</option>
      {{foreach from=$extensions_doc item=_extension}}
        <option {{if !$in_modal_administration && $_acte->extension == $_extension->code}}selected{{/if}}
                value="{{$acte_id}}-{{$_extension->code}}">
          {{$_extension->_view}}
        </option>
      {{/foreach}}
    </select>
  </label>
  {{if $_acte->_fabrication}}
    &dash; Phases:
    {{foreach from="-"|explode:"A-B-C" item=_phase}}
      <label title="{{tr}}CActiviteCsARR-libelle_phase_{{$_phase}}{{/tr}}">
        <input type="checkbox" class="phase"
               {{if !$in_modal_administration && in_array($_phase, $_acte->_phases)}}checked{{/if}}
               {{if !$in_modal_administration && $_evenement->realise}}disabled{{/if}}
               value="{{$acte_id}}-{{$_phase}}" />
        {{$_phase}}
      </label>
    {{/foreach}}
  {{/if}}
  <button type="button" class="fa fa-edit notext" style="float: left;margin-right: 3px;"
          onclick="ModalValidation.setVisibleField('show_com_{{$acte_id}}');" title="Commentaire">
    {{mb_label object=$_acte field=commentaire}}
  </button>
  <div style="{{if !$_acte->commentaire}}display:none;{{/if}}" id="show_com_{{$acte_id}}">
    <br/><strong>Commentaire pour l'acte {{$_acte->code}}:</strong>
    {{mb_field object=$_acte field=commentaire class="commentaires" id="commentaire_$acte_id"}}
  </div>
</div>
