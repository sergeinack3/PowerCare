{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=interv_en_urgence value=0}}
{{mb_default var=liaison_sejour value=""}}

{{assign var=other_display_plage value=$conf.reservation.other_display_plage}}

{{assign var=sejour  value=$operation->_ref_sejour}}
{{assign var=anesth  value=$operation->_ref_anesth}}
{{assign var=besoins value=$operation->_ref_besoins}}
{{assign var=chir    value=$operation->_ref_chir}}
{{assign var=chir_2  value=$operation->_ref_chir_2}}
{{assign var=chir_3  value=$operation->_ref_chir_3}}
{{assign var=chir_4  value=$operation->_ref_chir_4}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=facture value=$sejour->_ref_facture}}
{{assign var=charge  value=$sejour->_ref_charge_price_indicator}}
{{assign var=lit     value=$sejour->_ref_curr_affectation->_ref_lit}}
{{assign var=count_atcd value=$patient->_ref_dossier_medical->_count_antecedents}}
{{assign var=show_tooltip value=1}}
{{if $conf.reservation.ipp_patient_anonyme && $patient->vip}}
    {{assign var=show_tooltip value=0}}
{{/if}}

<span>
  <span class="data" style="display: none;"
        data-duree="{{$sejour->_duree}}"
        data-entree_prevue='{{$sejour->entree_prevue}}'
        data-sortie_prevue='{{$sejour->sortie_prevue}}'
        data-sejour_id='{{$sejour->_id}}'
        data-preop='{{if $operation->presence_preop}}{{$operation->presence_preop|date_format:"%H:%M"}}{{else}}00:00{{/if}}'
        data-postop='{{if $operation->presence_postop}}{{$operation->presence_postop|date_format:"%H:%M"}}{{else}}00:00{{/if}}'
        data-traitement='{{$charge->_id}}'
        data-pec='{{$sejour->type_pec}}'>
  </span>
    <!-- CADRE DROIT -->
  <span style="float:right; text-align: right">
      {{if $other_display_plage}}
        <!-- bloc allergie & atcd -->

        {{if $patient->_ref_dossier_medical->_count_allergies > 0}}
            <span
              {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'allergies');"{{/if}}>
              {{me_img src="warning.png" icon="warning" class="me-warning" alt=WRN}}
            </span>
        {{/if}}

        {{if $count_atcd > 0}}
            <span class="texticon texticon-atcd"
                  {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_dossier_medical->_guid}}', 'antecedents');"{{/if}}>Atcd</span>
        {{/if}}
    {{/if}}

      {{if $operation->_ref_consult_anesth->_id}}
          <span class="texticon texticon-stup" title="{{tr}}CConsultAnesth-Related pre-anesthetic consultation{{/tr}}"
                id="cpa_{{$operation->_guid}}">{{tr}}CConsultAnesth-_date_consult-court{{/tr}}</span>
      {{/if}}

      {{if $conf.reservation.display_dossierBloc_button}}
          <button class="bistouri notext" onclick="modalDossierBloc('{{$operation->_id}}')">Dossier Bloc</button>
      {{/if}}

    <!-- facture -->
    {{if "dPplanningOp CFactureEtablissement use_facture_etab"|gconf && $conf.reservation.display_facture_button && $facture->_id}}
        {{if $facture->cloture}}
            {{assign var=couleur value="blue"}}
        {{else}}
            {{assign var=couleur value="#FF0"}}
        {{/if}}

        {{if $facture->patient_date_reglement}}
            {{assign var=couleur value="green"}}
        {{/if}}
        <button class="calcul notext" onclick="Facture.edit({{$facture->_id}}, '{{$facture->_class}}')"
                style="border-left: {{$couleur}} 3px solid;">Facture</button>
    {{/if}}

  </span>

  <br/>
  <span {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');"{{/if}}>
    <span
      class="{{if !$sejour->entree_reelle && $conf.reservation.use_color_patient}}patient-not-arrived{{/if}} {{if $sejour->septique}}septique{{/if}}"
      {{if $other_display_plage}}style="font-size: 11px; font-weight: bold;"{{/if}}>
      {{if $conf.reservation.ipp_patient_anonyme && $patient->vip}}
          {{$patient->_IPP}}
      {{else}}
          {{$patient->_view}}
      {{/if}}
      ({{$patient->sexe}})<br/>
    </span>
    [{{mb_value object=$patient field=naissance}}] {{$lit}}
  </span>

  {{if $interv_en_urgence}}
    <span style='float: right' title='{{tr}}COperation-emergency{{/tr}}'>
      <img src='images/icons/attente_fourth_part.png' />
    </span>
  {{/if}}

  <br/>
  {{if $other_display_plage}}
    <span style="font-size: 11px; font-weight: bold;">
  {{/if}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
        {{if $other_display_plage}}
    </span>
  {{/if}}
  <br/>
  <span {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}');{{/if}}">
    <span {{if $other_display_plage}}style="font-size: 11px; font-weight: bold;"{{/if}}>
      {{$debut_op|date_format:$conf.time}} - {{$fin_op|date_format:$conf.time}}
    </span>
    <br/>
    {{$operation->libelle}}
  </span>
  <hr/>

  coté : <strong>{{$operation->cote}}</strong><br/>
  {{if $operation->_ref_type_anesth}}
      Type anest. :
      <strong>{{$operation->_ref_type_anesth}}</strong>
      <br/>
  {{/if}}

    {{if !$other_display_plage}}
        <!-- bloc allergie & atcd -->
        {{if $patient->_ref_dossier_medical->_count_allergies > 0 || $count_atcd > 0 }}
            <hr/>
        {{/if}}

    {{if $patient->_ref_dossier_medical->_count_allergies > 0}}
        <span {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'allergies');"{{/if}}>
        {{me_img src="warning.png" icon="warning" class="me-warning" alt=WRN}}
      </span>
    {{/if}}

    {{if $count_atcd > 0}}
        <span class="texticon texticon-atcd"
              {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_dossier_medical->_guid}}', 'antecedents');"{{/if}}>Atcd</span>
    {{/if}}
    {{/if}}
  <hr/>

  Sejour: <span
      {{if $show_tooltip}}onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');"{{/if}}>{{mb_value object=$sejour field=entree}}</span>
  {{if $operation->materiel}}
      <span>{{mb_value object=$operation field=materiel}}</span>
  {{/if}}
    {{if $operation->exam_per_op}}
        <span>{{mb_value object=$operation field=exam_per_op}}</span>
    {{/if}}


    {{if $chir_2->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir_2}}
    {{/if}}

    {{if $chir_3->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir_3}}
    {{/if}}

    {{if $chir_4->_id}}
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir_4}}
    {{/if}}

    {{if $anesth->_id}}
        <img src="images/icons/anesth.png" alt="WRN"/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$anesth}}
    {{/if}}

    {{if $operation->rques}}
        <hr/>
        <strong>Rques:</strong>
        {{$operation->rques}}
    {{/if}}

    {{if count($besoins)}}
        <span class='compact' style='color: #000'>
      {{foreach from=$besoins item=_besoin}}
          {{$_besoin->_ref_type_ressource->libelle}},
      {{/foreach}}
    </span>
    {{/if}}

</span>
