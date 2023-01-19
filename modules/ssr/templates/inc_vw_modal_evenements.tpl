{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=evt_ssr register=true}}
{{assign var=use_acte_presta value="ssr general use_acte_presta"|gconf}}

<script>
  Main.add(function(){
    Evt_SSR.restoreSavedElements();
    var evenement_to_validation =$('evenement_to_validation');
    evenement_to_validation.style.height = ($('evenement_to_validation').up('div').getDimensions().height-40)+'px';
  });
</script>

<div style="overflow-y: auto; overflow-x: hidden;" id="evenement_to_validation">

<form name="TreatEvents" method="post" action="?">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="do_treat_evenements" />
  <input type="hidden" name="realise_ids"  value="" />
  <input type="hidden" name="annule_ids"   value="" />
  <input type="hidden" name="modulateurs"  value="" />
  <input type="hidden" name="phases"       value="" />
  <input type="hidden" name="nb_patient"   value="" />
  <input type="hidden" name="nb_interv"    value="" />
  <input type="hidden" name="commentaires" value="" />
  <input type="hidden" name="transmissions" value="" />
  <input type="hidden" name="extensions_doc" value="" />
  <input type="hidden" name="heures" value="" />
  <input type="hidden" name="durees" value="" />
</form>

<table class="tbl" style="margin-right: 10px;" id="list-evenements-modal">
  <tr>
    <th colspan="7" class="title">{{tr}}CEvenementSSR|pl{{/tr}}</th>
    <th class="title narrow">
      <button style="float: right" class="tick notext" type="button" onclick="ModalValidation.toggleAllSejours('realise');">
        {{tr}}Validate-all{{/tr}}
      </button>
    </th>
    <th class="title narrow">
      <button style="float: right" class="cancel notext" type="button" onclick="ModalValidation.toggleAllSejours('annule');">
        {{tr}}Cancel-all{{/tr}}
      </button>
    </th>
  </tr>
  {{foreach from=$evenements item=_evenements_by_sejour key=sejour_id}}
    {{assign var=sejour value=$sejours.$sejour_id}}
    {{assign var=count_traite value=0}}
    {{foreach from=$_evenements_by_sejour item=_evenements_by_element}}
      {{foreach from=$_evenements_by_element item=_evenement}}
        {{if $_evenement->_traite}} {{assign var=count_traite value=$count_traite+1}} {{/if}}
      {{/foreach}}
    {{/foreach}}

    <tr>
      <th colspan="7">
        {{assign var=patient value=$sejour->_ref_patient}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')"
              style="vertical-align: middle;font-size: larger;">
          {{$patient}}
        </span>
        {{if $count_traite}}
          <div class="small-info" style="width:270px;margin: 0;display: inline;">
            {{$count_traite}} {{tr}}ssr-evts_already_treaty_for_patient{{/tr}}.
          </div>
        {{/if}}
      </th>
      <th class="narrow">
        <button style="float: right" class="tick notext" type="button" onclick="ModalValidation.toggleSejour('{{$sejour_id}}', 'realise');">
          {{tr}}Validate{{/tr}}
        </button>
      </th>
      <th class="narrow">
        <button style="float: right" class="cancel notext" type="button" onclick="ModalValidation.toggleSejour('{{$sejour_id}}', 'annule');">
          {{tr}}Cancel{{/tr}}
        </button>
      </th>
    </tr>

    <tr>
      <th class="section">{{tr}}CEvenementSSR{{/tr}}</th>
      <th class="section narrow">{{mb_title class=CEvenementSSR field=debut}}</th>
      <th class="section narrow">{{tr}}Hour{{/tr}}</th>
      <th class="section narrow">{{mb_title class=CEvenementSSR field=duree}}</th>
      {{if $use_acte_presta == 'csarr'}}
        <th class="section">{{tr}}CEvenementSSR-back-actes_csarr{{/tr}}</th>
      {{elseif $use_acte_presta == 'presta'}}
        <th class="section">{{tr}}CEvenementSSR-back-actes_prestas{{/tr}}</th>
      {{else}}
        <th class="section">{{tr}}CSejour-back-transmissions{{/tr}}</th>
      {{/if}}
      <th class="section">{{mb_title class=CEvenementSSR field=equipement_id}}</th>
      <th class="section">{{mb_title class=CEvenementSSR field=seance_collective_id}}</th>
      <th class="section"></th>
      <th class="section"></th>
    </tr>

    {{foreach from=$_evenements_by_sejour item=_evenements_by_element}}
    {{foreach from=$_evenements_by_element item=_evenement}}
      <tr>
        <td class="text">
          {{assign var=line    value=$_evenement->_ref_prescription_line_element}}
          {{assign var=element value=$line->_ref_element_prescription}}
          {{if $line}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}')">
              {{mb_ditto name="element-$sejour_id" value=$line->_id|ternary:$element->_view:'-'|capitalize center=true}}
            </span>
          {{/if}}
        </td>
        <td>
          {{assign var=config_date value=$conf.date}}
          {{mb_ditto name="date-$sejour_id" value=$_evenement->debut|@date_format:"%A $config_date"|capitalize center=true}}
        </td>
        <td>
        {{if $_evenement->seance_collective_id}}
          {{$_evenement->debut|@date_format:$conf.time}}
        {{else}}
          <form name="change_debut-{{$_evenement->_guid}}" data-id_evt="{{$_evenement->_id}}" data-original="{{$_evenement->_heure_deb}}">
            {{mb_field object=$_evenement field=_heure_deb form="change_debut-`$_evenement->_guid`" class="change_time"}}
          </form>
        {{/if}}
        </td>
        <td>
          {{if $_evenement->seance_collective_id}}
            {{mb_value object=$_evenement field=duree}}
          {{else}}
            <form name="change_duree-{{$_evenement->_guid}}" data-id_evt="{{$_evenement->_id}}" data-original="{{$_evenement->duree}}">
              {{mb_field object=$_evenement field=duree form="change_duree-`$_evenement->_guid`" class="change_duree" increment=1 step=10}}
            </form>
          {{/if}} min
        </td>

        <td class="text codes_container" data-evenement_id="{{$_evenement->_id}}">
          <button type="button" class="add notext" style="float: right;"
                  onclick="ModalValidation.setVisibleField('show_transmission_{{$_evenement->_id}}');" title="Ajouter une transmission">
            {{mb_label object=$_evenement field=_transmission}}
          </button>
          {{if $_evenement->seance_collective_id && (($use_acte_presta == 'csarr' && ($_evenement->_ref_actes_cdarr || $_evenement->_ref_actes_csarr))
            || ($use_acte_presta == 'presta' && $_evenement->_refs_prestas_ssr))}}
            <button type="button" class="duplicate notext" style="float:right;" onclick="Evt_SSR.spreadCodes('{{$_evenement->_id}}');">
              {{tr}}Spread codes{{/tr}}
            </button>
          {{/if}}
          {{if !$_evenement->realise && $use_acte_presta != 'aucun'}}
            <button type="button" class="edit" onclick="ModalValidation.editCodesEvenement('{{$_evenement->_id}}');" style="float: right;">{{tr}}CEvenementSSR.editCodesEvenement{{/tr}}</button>
          {{/if}}
          <div style="clear: both;display:none;" id="show_transmission_{{$_evenement->_id}}">
            <strong>Transmission: </strong>
            {{mb_field object=$_evenement field=_transmission class="transmissions" id="transmission_`$_evenement->_id`"}}
          </div>
          {{if $use_acte_presta == 'csarr'}}
            {{if $_evenement->_count_actes}}
              <div style="clear: both;">
                {{foreach from=$_evenement->_ref_actes_cdarr item=_acte}}
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                    <code>{{$_acte->code}}</code>
                  </span>
                {{/foreach}}
              </div>
              {{foreach from=$_evenement->_ref_actes_csarr item=_acte}}
                {{mb_include module=ssr template=inc_edit_line_csarr}}
              {{/foreach}}
            {{elseif $use_acte_presta == 'csarr' && !$_evenement->patient_missing}}
              <div class="small-warning">
                {{tr}}CEvenementSSR-warning-no_code_ssr{{/tr}}
              </div>
            {{/if}}
          {{elseif $use_acte_presta == 'presta'}}
            {{assign var=prestas_ssr         value=$_evenement->_refs_prestas_ssr}}
            {{assign var=counter_prestas_ssr value=$_evenement->_counter_prestas_ssr}}

            <div style="clear: both;">
              <strong>{{tr}}CEvenementSSR-back-actes_prestas{{/tr}}</strong> :
              {{foreach from=$counter_prestas_ssr key=code_presta item=_presta name=prestas}}
                {{assign var=code_presta_ssr value=$counter_prestas_ssr.$code_presta}}

                <code onmouseover="ObjectTooltip.createEx(this, 'CActePrestationSSR-{{$code_presta_ssr.acte_prestation_id}}')">{{$code_presta}}</code>
                {{if $code_presta_ssr.quantity}}(x {{$code_presta_ssr.quantity}}){{/if}}{{if !$smarty.foreach.prestas.last}}, {{/if}}
              {{foreachelse}}
                <span class="empty">{{tr}}CEvenementSSR-back-actes_prestas.empty{{/tr}}</span>
              {{/foreach}}
            </div>
          {{/if}}
        </td>

        <td>
          {{assign var=equipement value=$_evenement->_ref_equipement}}
          {{if $equipement->_id}}
            {{$equipement}}
          {{/if}}
        </td>

        <td class="narrow">
          {{if $_evenement->seance_collective_id}}
            {{assign var=evenement_guid value=$_evenement->_guid}}
            <form name="changeNbPatient_{{$evenement_guid}}" method="post" action="?" class="change-nb-patient" data-evenement_id="{{$_evenement->_id}}" data-seance_collective_id="{{$_evenement->seance_collective_id}}">
              <table class="form">
                <tr>
                  <td style="text-align: right;">{{mb_label object=$_evenement field=nb_patient_seance}}</td>
                  <td><input type="text" name="nb_patient_seance" value="{{$_evenement->nb_patient_seance}}" class="nb_patient notNull" size=3 /></td>
                  <td>
                    <span class="compact">
                      {{$_evenement->_ref_seance_collective->_ref_evenements_seance|@count}}
                      {{tr}}ssr-patient_planned{{/tr}}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td>{{mb_label object=$_evenement field=nb_intervenant_seance}}</td>
                  <td><input type="text" name="nb_intervenant_seance" value="{{$_evenement->nb_intervenant_seance}}" class="nb_interv notNull" size=3 /></td>
                  <td></td>
                </tr>
                {{if "ssr seance_collective nb_patient_interv_auto"|gconf}}
                  <tr>
                    <td colspan="3">
                      <button type="button" class="duplicate" onclick="ModalValidation.applyNbstoSeance(this.form);" title="{{tr}}Seance_collective-applyNbstoSeance{{/tr}}">
                        {{tr}}Seance_collective-applyNbstoSeance-court{{/tr}}
                      </button>
                    </td>
                  </tr>
                {{/if}}
              </table>
            </form>
          {{/if}}
        </td>

        <td>
          <input class="{{$sejour->_guid}} {{$_evenement->_guid}} realise" type="checkbox" value="{{$_evenement->_id}}"
            onchange="if (this.checked) {$$('input.{{$_evenement->_guid}}.annule')[0].checked = false;}
              ModalValidation.eventCollectif(this.checked, 'realise', '{{$_evenement->_guid}}', '{{$_evenement->seance_collective_id}}');"
            {{if (!$_evenement->_count_actes && $use_acte_presta == 'csarr') || $_evenement->_no_validation}} disabled="disabled" {{if $_evenement->_no_validation}}
              title="{{tr}}CEvenementSSR-validation_actes_futur-no{{/tr}}"{{/if}}{{/if}}
            {{if ($_evenement->_count_actes || $use_acte_presta != 'csarr') && ($_evenement->realise || (!$count_traite && !$_evenement->_no_validation))}}  checked="checked" {{/if}}
          />
        </td>
        <td>
          <input class="{{$sejour->_guid}} {{$_evenement->_guid}} annule" type="checkbox" value="{{$_evenement->_id}}"
            onchange="if (this.checked) $$('input.{{$_evenement->_guid}}.realise')[0].checked = false;
              ModalValidation.eventCollectif(this.checked, 'annule', '{{$_evenement->_guid}}', '{{$_evenement->seance_collective_id}}');"
            {{if (!$_evenement->_count_actes && $use_acte_presta == 'csarr') || $_evenement->_no_validation}} disabled="disabled" {{if $_evenement->_no_validation}}
              title="{{tr}}CEvenementSSR-validation_actes_futur-no{{/tr}}"{{/if}}{{/if}}
            {{if ($_evenement->_count_actes || $use_acte_presta != 'csarr') && $_evenement->annule}} checked="checked" {{/if}}
          />
        </td>
      </tr>
    {{/foreach}}
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="9">{{tr}}CEvenementSSR.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
</div>

<hr />

{{if $count_zero_actes && $use_acte_presta != 'aucun'}}
<div class="small-warning">
  {{tr}}CEvenementCdARR-msg-count_zero_actes-{{$use_acte_presta}}{{/tr}} :
  (<strong>{{$count_zero_actes}} {{tr}}CEvenementSSR{{/tr}} </strong>)
</div>
{{/if}}

<table class="form">
  <tr>
    <td colspan="7" class="button">
      <button type="button" class="submit singleclick" onclick="ModalValidation.submitModal();">{{tr}}Validate{{/tr}}</button>
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </td>
  </tr>
</table>
