{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=soins script=soins}}

{{assign var=detail_atcd_alle     value=$app->user_prefs.detail_atcd_alle}}
{{assign var=show_bedroom_empty   value=$app->user_prefs.show_bedroom_empty}}
{{assign var=show_last_macrocible value=$app->user_prefs.show_last_macrocible}}

<button type="button"
        class="print not-printable"
        style="float: right;" onclick="window.print();"
        {{if $services|@count > 1}}title="{{tr}}CSejour-Print all transmission sheet|pl{{/tr}}"{{/if}}>
  {{if $services|@count > 1}}
    {{tr}}CSejour-Print all{{/tr}}
  {{else}}
    {{tr}}Print{{/tr}}
  {{/if}}
</button>

<div style="margin-top: 22px;">
  <button type="button" style="float: right;" onclick="Soins.selectServices('feuille_transmissions');" class="search">{{tr}}CService|pl{{/tr}}</button>
</div>

{{foreach from=$services item=_service}}
  {{assign var=service_id value=$_service->_id}}
  <h1 style="text-align: center;">
    {{tr}}CService-Service stays{{/tr}} {{$_service}} - {{$dnow|date_format:$conf.date}}
  </h1>
  <table class="tbl feuille_trans" style="page-break-after: always;">
    <tr>
      <th class="narrow">{{tr}}CLit{{/tr}}</th>
      <th class="narrow" colspan="2">{{tr}}CPatient{{/tr}}</th>
      <th class="narrow">{{tr}}CSejour-praticien_id{{/tr}}</th>
      <th class="narrow">{{tr}}CSejour-Hospital pattern-court{{/tr}}</th>

      <th class="narrow" style="border-right: 2px solid #ccc;">{{tr}}Day-court{{/tr}}</th>
      <th style="width: 5%; display: none;" class="type_app">{{tr}}CElementPrescription-title-rubrique_feuille_trans.type_app{{/tr}}</th>
      <th style="width: 5%; display: none;" class="exam">{{tr}}CElementPrescription.rubrique_feuille_trans.exam{{/tr}}</th>
      <th style="width: 5%; display: none;" class="bilan">{{tr}}CElementPrescription.rubrique_feuille_trans.bilan{{/tr}}</th>
      <th style="width: 5%; display: none;" class="soins_ide">{{tr}}CElementPrescription.rubrique_feuille_trans.soins_ide{{/tr}}</th>
      <th style="width: 5%; display: none;" class="soins_as">{{tr}}CElementPrescription.rubrique_feuille_trans.soins_as{{/tr}}</th>
      <th style="width: 5%;" title="{{tr}}CSejour-Enter Exit{{/tr}}">{{tr}}CSejour-Enter Exit-court{{/tr}}</th>
      <th>{{tr}}CMediusers-commentaires{{/tr}}</th>
    </tr>

    {{foreach from=$_service->_ref_chambres item=_chambre}}
      {{foreach from=$_chambre->_ref_lits item=_lit}}

        {{if !$_lit->_ref_affectations|@count && $show_bedroom_empty}}
          <tr style="height: 50px;">
            <td class="lit_empty">
              {{$_lit->_view}}
            </td>
            <td colspan="2"></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="display: none;" class="type_app"></td>
            <td style="display: none;" class="exam"></td>
            <td style="display: none;" class="bilan"></td>
            <td style="display: none;" class="soins_ide"></td>
            <td style="display: none;" class="soins_as"></td>
            <td></td>
            <td></td>
          </tr>
        {{else}}
          {{foreach from=$_lit->_ref_affectations item=_affectation name=affectations}}

            {{assign var=sejour                  value=$_affectation->_ref_sejour}}
            {{assign var=patient                 value=$sejour->_ref_patient}}
            {{assign var=dossier_medical         value=$patient->_ref_dossier_medical}}
            {{assign var=prescription            value=$sejour->_ref_prescription_sejour}}
            {{assign var=operations              value=$sejour->_ref_operations}}
            {{assign var=etablissement_transfert value=$sejour->_ref_etablissement_transfert}}
            <tr>
              <td class="no_border_bottom">
                {{$_affectation}}
              </td>
              <td class="text no_border_bottom" colspan="2">
            <span class="text_bold">
              {{$patient}} <br />
              {{mb_value object=$patient field=_age}}
            </span>
              </td>
              <td class="no_border_bottom">
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien initials=border}}
              </td>
              <td class="text no_border_bottom">
              <span class="text_bold">
                {{mb_value object=$sejour field=_motif_complet}}

                {{foreach from=$operations item=_operation}}
                  {{if $_operation->cote != 'inconnu'}}
                    <br />
                    ({{mb_value object=$_operation field=cote}})
                  {{/if}}
                {{/foreach}}
              </span>
              </td>
              <td style="border-right: 2px solid #ccc;" class="no_border_bottom">
                {{assign var=nb_days_hide_op value="soins dossier_soins nb_days_hide_op"|gconf}}
                {{foreach from=$sejour->_jour_op item=_info_jour_op}}
                  {{if $nb_days_hide_op == 0 || $nb_days_hide_op > $_info_jour_op.jour_op}}
                    <span class="text_bold" onmouseover="ObjectTooltip.createEx(this, '{{$_info_jour_op.operation_guid}}');">
                      J{{$_info_jour_op.jour_op}}
                        {{if $_info_jour_op.jour_op == "0" && $_info_jour_op.heure_operation|date_format:$conf.time}}
                          <br />
                          ({{$_info_jour_op.heure_operation|date_format:$conf.time}})
                        {{/if}}
                    </span>
                  {{/if}}
                {{/foreach}}
              </td>
              {{if $prescription}}
              {{foreach from=$prescription->_ref_prescription_lines_element_rubrique key=_rubrique item=lines_elt}}
                <td class="text {{$_rubrique}}" {{if !$lines_elt|@count}}style="display: none;"{{/if}} rowspan="2">
                  {{foreach from=$lines_elt item=_line_elt}}
                    {{assign var=elt value=$_line_elt->_ref_element_prescription}}
                    <div>
                      {{mb_ternary test=$elt->libelle_court value=$elt->libelle_court other=$elt->libelle}}
                    </div>
                  {{/foreach}}
                </td>
              {{/foreach}}
              {{else}}
                <td rowspan="2" style="display: none;" class="type_app"></td>
                <td rowspan="2" style="display: none;" class="exam"></td>
                <td rowspan="2" style="display: none;" class="bilan"></td>
                <td rowspan="2" style="display: none;" class="soins_ide"></td>
                <td rowspan="2" style="display: none;" class="soins_as"></td>
              {{/if}}
              <td class="narrow no_border_bottom">
                {{mb_value object=$sejour field=entree_prevue}} / <br />

                {{if $sejour->confirme}}
                  <i class="fas fa-check" style="color: green;"
                     title="{{tr var1=$sejour->confirme|date_format:$conf.datetime var2=$sejour->_ref_confirme_user->_view}}CSejour-Authorized output for %s by %s{{/tr}}"></i>
                  {{mb_value object=$sejour field=confirme}}

                  {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
                    <br />
                    {{mb_value object=$etablissement_transfert field="_view"}}
                  {{/if}}

                {{else}}
                  {{mb_value object=$sejour field=sortie_prevue}}
                {{/if}}
              </td>
              <td rowspan="2" colspan="2" class="text_left text">
                {{* Commentaire utilisateur *}}
                {{if $show_last_macrocible}}
                  {{foreach from=$sejour->_ref_transmissions item=_transmission}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_transmission->_guid}}')">
                    <strong>{{$_transmission->type|substr:0:1|upper}}</strong>:{{$_transmission->text}}
                  </span>
                  {{/foreach}}
                {{/if}}
              </td>
            </tr>
            <tr>
              <td {{if $detail_atcd_alle}}class="alle"{{/if}} colspan="3" style="vertical-align: middle">
                {{if $detail_atcd_alle}}
                  {{if $dossier_medical->_ref_allergies|@count}}
                    <strong>Allergies</strong>
                    <ul>
                      {{foreach from=$dossier_medical->_ref_allergies item=_allergie}}
                        <li>
                          {{mb_value object=$_allergie field=rques}}
                        </li>
                      {{/foreach}}
                    </ul>
                  {{/if}}
                {{else}}
                  {{if $dossier_medical->_count_allergies}}
                    <span class="texticon texticon-allergies-warning">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
                  {{/if}}
                {{/if}}
              </td>
              <td {{if $detail_atcd_alle}}class="atcd"{{/if}} colspan="3" style="vertical-align: middle; border-right: 2px solid #ccc;">
                {{if $detail_atcd_alle}}
                  {{foreach from=$dossier_medical->_ref_antecedents_by_type_appareil key=_type item=antecedents_by_appareil}}
                    {{if $antecedents_by_appareil|@count}}
                      {{foreach from=$antecedents_by_appareil key=_appareil item=antecedents name=foreach_atcd}}
                        {{if $antecedents|@count}}
                          <strong>
                            {{tr}}CAntecedent.type.{{$_type}}{{/tr}} &ndash;
                            {{tr}}CAntecedent.appareil.{{$_appareil}}{{/tr}}
                          </strong>
                          <ul>
                            {{foreach from=$antecedents item=_antecedent}}
                              <li>
                                {{if $_antecedent->date}}
                                  {{mb_value object=$_antecedent field="date"}} :
                                {{/if}}
                                {{mb_value object=$_antecedent field=rques}}
                              </li>
                            {{/foreach}}
                          </ul>
                        {{/if}}
                      {{/foreach}}
                    {{/if}}
                  {{/foreach}}

                {{else}}
                  {{if $dossier_medical->_count_antecedents}}
                    <span class="texticon texticon-atcd">{{tr}}CAntecedent-court{{/tr}}</span>
                  {{/if}}
                {{/if}}
              </td>
              <td>
                {{mb_value object=$sejour field=convalescence}}
              </td>
            </tr>
          {{/foreach}}
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  </table>
{{/foreach}}

<script>
  Main.add(function () {
    {{foreach from=$nb_columns key=key_rubrique item=_number}}
      $$(".{{$key_rubrique}}").invoke("show");
    {{/foreach}}
  });
</script>
