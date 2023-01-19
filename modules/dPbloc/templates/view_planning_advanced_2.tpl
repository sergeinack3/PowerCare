{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
    @media print, screen {
        table.tbl th.bloc_section {
            background-color: #ddd;
            color: black;
            font-size: 1.5em;
        }
    }
</style>

{{assign var=show_anesth_alerts value="dPbloc printing show_anesth_alerts"|gconf}}

{{assign var=same_day value=false}}
{{if $filter->_datetime_min|date_format:$conf.date == $filter->_datetime_max|date_format:$conf.date}}
    {{assign var=same_day value=true}}
{{/if}}

{{mb_include module=bloc template=inc_offline_button_print_view_planning}}

<h1 style="margin: auto; text-align: center;">
    <a href="#" onclick="window.print()">
        {{if $same_day}}
            Planning du {{$filter->_datetime_min|date_format:$conf.date}}
        {{else}}
            Planning du {{$filter->_datetime_min|date_format:$conf.date}} {{$filter->_datetime_min|date_format:$conf.time}}
            au {{$filter->_datetime_max|date_format:$conf.date}} {{$filter->_datetime_max|date_format:$conf.time}}
        {{/if}}

        &ndash;
        {{$numOp}} intervention(s)
        {{if $operations|@count && $_hors_plage}}
            (dont {{$operations|@count}} hors plage)
        {{/if}}
    </a>
</h1>

<br/>
{{if $_page_break}}
  {{foreach from=$listDates key=curr_date item=listSalles}}
    {{foreach from=$listSalles key=salle_id item=listPlages name=date_loop}}
      {{foreach from=$listPlages key=curr_plage_id item=curr_plageop name=plage_loop}}
        <table class="tbl" style="page-break-after: always">
          <tr class="clear">
            <td colspan="20">
              {{if $curr_plage_id == "hors_plage"}}
                <h2>
                  {{if !$same_day}}
                    {{$curr_date|date_format:"%a %d/%m/%Y"}}
                  {{/if}}
                </h2>
              {{else}}
                <h2>
                  <strong>
                    {{$curr_plageop->_ref_salle->nom}}
                    &ndash;
                    {{if $curr_plageop->chir_id}}
                      Dr {{$curr_plageop->_ref_chir}}
                    {{else}}
                      {{$curr_plageop->_ref_spec}}
                    {{/if}}
                    {{if $curr_plageop->anesth_id}}
                      &ndash; Anesthésiste : Dr {{$curr_plageop->_ref_anesth}}
                    {{/if}}
                  </strong>

                  <div style="font-size: 70%">
                    {{if !$same_day}}
                      {{$curr_plageop->date|date_format:"%a %d/%m/%Y"}}
                    {{/if}}

                    {{$curr_plageop->_ref_salle}}
                    de {{$curr_plageop->debut|date_format:$conf.time}}
                    à {{$curr_plageop->fin|date_format:$conf.time}}
                    {{assign var="plageOp_id" value=$curr_plageop->_id}}

                    <!-- Affichage du personnel prevu pour la plage operatoire -->
                    {{foreach from=$affectations_plage.$plageOp_id key=type_affect item=_affectations}}
                      {{if $_affectations|@count}}
                        <strong>{{tr}}CPersonnel.emplacement.{{$type_affect}}{{/tr}} :</strong>
                        {{foreach from=$_affectations item=_personnel}}
                          {{$_personnel->_ref_personnel->_ref_user}};
                        {{/foreach}}
                      {{/if}}
                    {{/foreach}}
                  </div>
                </h2>
              {{/if}}
            </td>
          </tr>
          <tr>
            <th>H. entrée /<br/>Durée op.</th>
            <th class="text">Nom / Prénom</th>
            <th class="text">Âge</th>
            <th>Sexe</th>
            {{if $_coordonnees}}
              <th>Téléphone</th>
            {{/if}}
            {{if $_display_main_doctor}}
              <th>Médecin traitant</th>
            {{/if}}
            {{if $_display_allergy}}
              <th>{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
            {{/if}}
            <th class="text">Libellé</th>
            <th class="text">{{mb_title class="COperation" field="materiel"}}</th>
            <th class="text">Chirurgien</th>
            <th>Ordre de<br/>passage</th>
            <th>Anesth.</th>
            <th>Remarques</th>
            <th class="narrow">Hosp. / Cls</th>
            {{if $_examens_perop}}
              <th>{{tr}}COperation-exam_per_op{{/tr}}</th>
            {{/if}}
            {{if $offline}}
              <th class="narrow not-printable"></th>
            {{/if}}
          </tr>
          {{if $curr_plage_id == "hors_plage"}}
            {{assign var=listOperations value=$curr_plageop}}
          {{else}}
            {{assign var=listOperations value=$curr_plageop->_ref_operations}}
          {{/if}}

          {{assign var=salle_id value=""}}

          {{foreach name=listOp from=$listOperations item=_op}}
            {{assign var=sejour value=$_op->_ref_sejour}}
            {{assign var=patient value=$sejour->_ref_patient}}
            {{assign var=op_id value=$_op->_id}}

            {{if $salle_id != $_op->salle_id && $curr_plage_id == "hors_plage"}}
              {{if !$smarty.foreach.listOp.first}}
                <tr>
                  <td colspan="20">&nbsp;</td>
                </tr>
              {{/if}}

              {{assign var=salle_id value=$_op->salle_id}}
              <tr>
                <th class="bloc_section" colspan="20">
                  <strong>{{$_op->_ref_salle}}</strong>
                </th>
              </tr>
            {{/if}}
            <tr {{if $_op->annulee}}class="hatching"{{/if}}>
              {{if $_op->annulee}}
                <td class="cancelled">ANNULEE</td>
              {{else}}
                <td style="text-align: center;">
                  {{$sejour->entree|date_format:$conf.time}}<br/>
                  {{$_op->temp_operation|date_format:$conf.time}}
                </td>
              {{/if}}

              <td class="text">
                <strong>
                  {{mb_value object=$patient field=nom}} {{mb_value object=$patient field=prenom}}
                </strong>

                {{mb_include module=patients template=inc_icon_bmr_bhre}}
              </td>

              <td>
                {{mb_value object=$patient field=_age}}<br/>
                ({{mb_value object=$patient field=naissance}})
              </td>

              <td style="text-align: center;">
                {{$patient->sexe|strtoupper}}
              </td>

              {{if $_coordonnees}}
                <td>
                  {{if $patient->tel}}
                    {{mb_value object=$patient field="tel"}}
                    <br/>
                  {{/if}}
                  {{if $patient->tel2}}
                    {{mb_value object=$patient field="tel2"}}
                  {{/if}}
                </td>
              {{/if}}

              {{if $_display_main_doctor}}
                <td>
                  {{$patient->_ref_medecin_traitant}}
                </td>
              {{/if}}

              {{if $_display_allergy}}
                <td class="text">
                  {{if $patient->_ref_dossier_medical}}
                    <ul>
                      {{foreach from=$patient->_ref_dossier_medical->_ref_allergies item=_allergie}}
                        <li>{{$_allergie->rques|spancate}}</li>
                      {{/foreach}}
                    </ul>
                  {{/if}}
                </td>
              {{/if}}

              <td class="text">
                {{if $_op->libelle}}
                  <strong>{{$_op->libelle}}</strong>
                  <br/>
                {{/if}}

                {{foreach from=$_op->_ext_codes_ccam item=_code}}
                  {{if !$_op->libelle}}
                    {{if !$_code->_code7}}<strong>{{/if}}
                    <em>{{$_code->code}}</em>
                    {{if $filter->_ccam_libelle}}
                      : {{$_code->libelleLong|truncate:60:"...":false}}
                      <br/>
                    {{else}}
                      ;
                    {{/if}}

                    {{if !$_code->_code7}}</strong>{{/if}}
                  {{/if}}
                {{/foreach}}
              </td>

              <td class="text">
                {{if $_op->materiel}}
                  {{mb_value object=$_op field=materiel}}
                {{/if}}
              </td>

              <td class="text">
                {{if $curr_plageop|is_array || $curr_plageop->spec_id}}
                  Dr {{$_op->_ref_chir}}
                  <br/>
                {{/if}}
              </td>

              <td style="text-align: center;">
                <div class="rank">{{$ordre_passage.$op_id}}</div>
              </td>

              <td class="text">
                {{if $_op->type_anesth != null}}
                  {{$_op->_lu_type_anesth}}
                {{/if}}

                {{if $_op->anesth_id}}
                  <br/>
                  {{$_op->_ref_anesth->_view}}
                {{/if}}
              </td>

              <td class="text">
                {{if $_op->exam_extempo}}
                  <strong>{{mb_title object=$_op field=exam_extempo}}</strong>
                  <br/>
                {{/if}}

                {{assign var=consult_anesth value=$_op->_ref_consult_anesth}}
                {{mb_include module=bloc template=inc_rques_intub operation=$_op}}
              </td>

              <td style="text-align: center;">
                {{$sejour->type|truncate:1:""|capitalize}}

                {{if $sejour->type == "comp"}}
                  - {{$sejour->_duree_prevue}} j
                {{/if}}

                {{if $_op->_liaisons_prestation}}
                  <br/>
                  {{$_op->_liaisons_prestation}}
                {{/if}}
              </td>
              {{if $_examens_perop}}
                <td>{{$_op->exam_per_op}}</td>
              {{/if}}
              {{mb_include module=bloc template=inc_offline_button_dossier_view_planning curr_op=$_op}}
            </tr>
          {{/foreach}}
        </table>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
{{else}}
<table class="tbl">
    {{foreach from=$listDates key=curr_date item=listSalles}}
        {{foreach from=$listSalles key=salle_id item=listPlages name=date_loop}}
            {{foreach from=$listPlages key=curr_plage_id item=curr_plageop name=plage_loop}}
                <tr class="clear">
                    <td colspan="20">
                        {{if $curr_plage_id == "hors_plage"}}
                            <h2>
                                {{if !$same_day}}
                                    {{$curr_date|date_format:"%a %d/%m/%Y"}}
                                {{/if}}
                            </h2>
                        {{else}}
                            <h2>
                                <strong>
                                    {{$curr_plageop->_ref_salle->nom}}
                                    &ndash;
                                    {{if $curr_plageop->chir_id}}
                                        Dr {{$curr_plageop->_ref_chir}}
                                    {{else}}
                                        {{$curr_plageop->_ref_spec}}
                                    {{/if}}
                                    {{if $curr_plageop->anesth_id}}
                                        &ndash; Anesthésiste : Dr {{$curr_plageop->_ref_anesth}}
                                    {{/if}}
                                </strong>

                                <div style="font-size: 70%">
                                    {{if !$same_day}}
                                        {{$curr_plageop->date|date_format:"%a %d/%m/%Y"}}
                                    {{/if}}

                                    {{$curr_plageop->_ref_salle}}
                                    de {{$curr_plageop->debut|date_format:$conf.time}}
                                    à {{$curr_plageop->fin|date_format:$conf.time}}
                                    {{assign var="plageOp_id" value=$curr_plageop->_id}}

                                    <!-- Affichage du personnel prevu pour la plage operatoire -->
                                    {{foreach from=$affectations_plage.$plageOp_id key=type_affect item=_affectations}}
                                        {{if $_affectations|@count}}
                                            <strong>{{tr}}CPersonnel.emplacement.{{$type_affect}}{{/tr}} :</strong>
                                            {{foreach from=$_affectations item=_personnel}}
                                                {{$_personnel->_ref_personnel->_ref_user}};
                                            {{/foreach}}
                                        {{/if}}
                                    {{/foreach}}
                                </div>
                            </h2>
                        {{/if}}
                    </td>
                </tr>
                <tr>
                    <th>H. entrée /<br/>Durée op.</th>
                    <th class="text">Nom / Prénom</th>
                    <th class="text">Âge</th>
                    <th>Sexe</th>
                    {{if $_coordonnees}}
                        <th>Téléphone</th>
                    {{/if}}
                    {{if $_display_main_doctor}}
                        <th>Médecin traitant</th>
                    {{/if}}
                    {{if $_display_allergy}}
                        <th>{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
                    {{/if}}
                    <th class="text">Libellé</th>
                    <th class="text">{{mb_title class="COperation" field="materiel"}}</th>
                    <th class="text">Chirurgien</th>
                    <th>Ordre de<br/>passage</th>
                    <th>Anesth.</th>
                    <th>Remarques</th>
                    <th class="narrow">Hosp. / Cls</th>
                    {{if $_examens_perop}}
                        <th>{{tr}}COperation-exam_per_op{{/tr}}</th>
                    {{/if}}
                    {{if $offline}}
                        <th class="narrow not-printable"></th>
                    {{/if}}
                </tr>
                {{if $curr_plage_id == "hors_plage"}}
                    {{assign var=listOperations value=$curr_plageop}}
                {{else}}
                    {{assign var=listOperations value=$curr_plageop->_ref_operations}}
                {{/if}}

                {{assign var=salle_id value=""}}

                {{foreach name=listOp from=$listOperations item=_op}}
                    {{assign var=sejour value=$_op->_ref_sejour}}
                    {{assign var=patient value=$sejour->_ref_patient}}
                    {{assign var=op_id value=$_op->_id}}

                    {{if $salle_id != $_op->salle_id && $curr_plage_id == "hors_plage"}}
                        {{if !$smarty.foreach.listOp.first}}
                            <tr>
                                <td colspan="20">&nbsp;</td>
                            </tr>
                        {{/if}}

                        {{assign var=salle_id value=$_op->salle_id}}
                        <tr>
                            <th class="bloc_section" colspan="20">
                                <strong>{{$_op->_ref_salle}}</strong>
                            </th>
                        </tr>
                    {{/if}}
                    <tr {{if $_op->annulee}}class="hatching"{{/if}}>
                        {{if $_op->annulee}}
                            <td class="cancelled">ANNULEE</td>
                        {{else}}
                            <td style="text-align: center;">
                                {{$sejour->entree|date_format:$conf.time}}<br/>
                                {{$_op->temp_operation|date_format:$conf.time}}
                            </td>
                        {{/if}}

                        <td class="text">
                            <strong>
                                {{mb_value object=$patient field=nom}} {{mb_value object=$patient field=prenom}}
                            </strong>

                            {{mb_include module=patients template=inc_icon_bmr_bhre}}
                        </td>

                        <td>
                            {{mb_value object=$patient field=_age}}<br/>
                            ({{mb_value object=$patient field=naissance}})
                        </td>

                        <td style="text-align: center;">
                            {{$patient->sexe|strtoupper}}
                        </td>

                        {{if $_coordonnees}}
                            <td>
                                {{if $patient->tel}}
                                    {{mb_value object=$patient field="tel"}}
                                    <br/>
                                {{/if}}
                                {{if $patient->tel2}}
                                    {{mb_value object=$patient field="tel2"}}
                                {{/if}}
                            </td>
                        {{/if}}

                        {{if $_display_main_doctor}}
                            <td>
                                {{$patient->_ref_medecin_traitant}}
                            </td>
                        {{/if}}

                        {{if $_display_allergy}}
                            <td class="text">
                                {{if $patient->_ref_dossier_medical}}
                                    <ul>
                                        {{foreach from=$patient->_ref_dossier_medical->_ref_allergies item=_allergie}}
                                            <li>{{$_allergie->rques|spancate}}</li>
                                        {{/foreach}}
                                    </ul>
                                {{/if}}
                            </td>
                        {{/if}}

                        <td class="text">
                            {{if $_op->libelle}}
                                <strong>{{$_op->libelle}}</strong>
                                <br/>
                            {{/if}}

                            {{foreach from=$_op->_ext_codes_ccam item=_code}}
                                {{if !$_op->libelle}}
                                    {{if !$_code->_code7}}<strong>{{/if}}
                                    <em>{{$_code->code}}</em>
                                    {{if $filter->_ccam_libelle}}
                                        : {{$_code->libelleLong|truncate:60:"...":false}}
                                        <br/>
                                    {{else}}
                                        ;
                                    {{/if}}

                                    {{if !$_code->_code7}}</strong>{{/if}}
                                {{/if}}
                            {{/foreach}}
                        </td>

                        <td class="text">
                            {{if $_op->materiel}}
                                {{mb_value object=$_op field=materiel}}
                            {{/if}}
                        </td>

                        <td class="text">
                            {{if $curr_plageop|is_array || $curr_plageop->spec_id}}
                                Dr {{$_op->_ref_chir}}
                                <br/>
                            {{/if}}
                        </td>

                        <td style="text-align: center;">
                            <div class="rank">{{$ordre_passage.$op_id}}</div>
                        </td>

                        <td class="text">
                            {{if $_op->type_anesth != null}}
                                {{$_op->_lu_type_anesth}}
                            {{/if}}

                            {{if $_op->anesth_id}}
                                <br/>
                                {{$_op->_ref_anesth->_view}}
                            {{/if}}
                        </td>

                        <td class="text">
                            {{if $_op->exam_extempo}}
                                <strong>{{mb_title object=$_op field=exam_extempo}}</strong>
                                <br/>
                            {{/if}}

                            {{assign var=consult_anesth value=$_op->_ref_consult_anesth}}
                            {{mb_include module=bloc template=inc_rques_intub operation=$_op}}
                        </td>

                        <td style="text-align: center;">
                            {{$sejour->type|truncate:1:""|capitalize}}

                            {{if $sejour->type == "comp"}}
                                - {{$sejour->_duree_prevue}} j
                            {{/if}}

                            {{if $_op->_liaisons_prestation}}
                                <br/>
                                {{$_op->_liaisons_prestation}}
                            {{/if}}
                        </td>
                        {{if $_examens_perop}}
                            <td>{{$_op->exam_per_op}}</td>
                        {{/if}}
                        {{mb_include module=bloc template=inc_offline_button_dossier_view_planning curr_op=$_op}}
                    </tr>
                {{/foreach}}
                <tr class="clear">
                    <td colspan="20">
                        <hr/>
                    </td>
                </tr>
                {{if $_page_break && !$smarty.foreach.plage_loop.last && !$_by_bloc}}
                    {{* Firefox ne prend pas en compte les page-break sur les div *}}
                    <tr class="clear" style="page-break-after: always;">
                        <td colspan="20" style="border: none;">
                            {{* Chrome ne prend pas en compte les page-break sur les tr *}}
                            <div style="page-break-after: always;"></div>
                        </td>
                    </tr>
                {{/if}}
            {{/foreach}}

            {{if $_page_break && !$smarty.foreach.date_loop.last}}
                {{* Firefox ne prend pas en compte les page-break sur les div *}}
                <tr class="clear" style="page-break-after: always;">
                    <td colspan="20" style="border: none;">
                        {{* Chrome ne prend pas en compte les page-break sur les tr *}}
                        <div style="page-break-after: always;"></div>
                    </td>
                </tr>
            {{/if}}
        {{/foreach}}
    {{/foreach}}
</table>
{{/if}}

{{mb_include module=bloc template=inc_offline_view_planning}}
