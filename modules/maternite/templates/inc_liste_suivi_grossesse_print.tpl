{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-letter-spacing-tight">
  <tbody>
  {{foreach from=$grossesse->_ref_consultations item=consult}}
    {{assign var=suivi value=$consult->_ref_suivi_grossesse}}
    <tr>
      <td class="narrow {{if $consult->annule}}cancelled{{/if}}">
        {{if !$offline}}
          <button type="button" class="consultation notext not-printable me-tertiary" title="Modifier la consultation"
                  onclick="Tdb.editConsult('{{$consult->_id}}', refreshListeSuivis);"></button>
          <br />
          <button type="button" class="clock notext not-printable me-tertiary" title="Modifier le RDV"
                  onclick="Tdb.editRdvConsult('{{$consult->_id}}', '{{$grossesse->_id}}', '{{$consult->patient_id}}', refreshListeSuivis);"></button>
        {{/if}}
      </td>
      <td class="narrow {{if $consult->annule}}cancelled{{/if}}">
        {{mb_value object=$consult field=_datetime}} - {{mb_value object=$consult field=_sa}} SA
        + {{mb_value object=$consult field=_ja}} j
        <br />
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_praticien}}
        {{if $suivi && $suivi->_id}}
          <br />
          {{mb_value object=$suivi field=type_suivi}}
        {{/if}}
      </td>
      {{if $consult->annule}}
        <td colspan="50" class="empty">
          {{tr}}Cancelled{{/tr}}
        </td>
      {{elseif $suivi && $suivi->_id}}
        <td>
          {{mb_value object=$suivi field=evenements_anterieurs}}
        </td>
        <td>
          <table class="layout">
            {{if $suivi->metrorragies !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->metrorragies === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=metrorragies}} :
                  {{mb_value object=$suivi field=metrorragies}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->leucorrhees !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->leucorrhees === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=leucorrhees}} :
                  {{mb_value object=$suivi field=leucorrhees}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->contractions_anormales !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->contractions_anormales === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=contractions_anormales}} :
                  {{mb_value object=$suivi field=contractions_anormales}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->mouvements_foetaux !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-times"}}
                  {{if $suivi->mouvements_foetaux === '1'}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=mouvements_foetaux}} :
                  {{mb_value object=$suivi field=mouvements_foetaux}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->mouvements_actifs !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-times"}}
                  {{if $suivi->mouvements_actifs === 'percu'}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=mouvements_actifs}} :
                  {{mb_value object=$suivi field=mouvements_actifs}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->hypertension !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-times"}}
                  {{if $suivi->hypertension === '1'}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=hypertension}} :
                  {{mb_value object=$suivi field=hypertension}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->troubles_digestifs !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->troubles_digestifs === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=troubles_digestifs}} :
                  {{mb_value object=$suivi field=troubles_digestifs}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->troubles_urinaires !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->troubles_urinaires === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=troubles_urinaires}} :
                  {{mb_value object=$suivi field=troubles_urinaires}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->autres_anomalies}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="red"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=autres_anomalies}}
                  {{if $suivi->autres_anomalies}}
                    : {{mb_value object=$suivi field=autres_anomalies}}
                  {{/if}}
                </td>
              </tr>
            {{/if}}
          </table>
        </td>
        <td>
          <table class="layout">
            {{if $suivi->auscultation_cardio_pulm !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->auscultation_cardio_pulm === 'anomalie'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=auscultation_cardio_pulm}} :
                  {{mb_value object=$suivi field=auscultation_cardio_pulm}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->examen_seins !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->examen_seins === 'mamomb' ||
                  $suivi->examen_seins === 'autre'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=examen_seins}} :
                  {{mb_value object=$suivi field=examen_seins}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->circulation_veineuse !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->circulation_veineuse === 'insmod' ||
                  $suivi->circulation_veineuse === 'inssev'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=circulation_veineuse}} :
                  {{mb_value object=$suivi field=circulation_veineuse}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->oedeme_membres_inf !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-times"}}
                  {{if $suivi->oedeme_membres_inf === '1'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=oedeme_membres_inf}} :
                  {{mb_value object=$suivi field=oedeme_membres_inf}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->rques_examen_general}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=rques_examen_general}}
                  {{if $suivi->rques_examen_general}}
                    : {{mb_value object=$suivi field=rques_examen_general}}
                  {{/if}}
                </td>
              </tr>
            {{/if}}
            {{if $consult->_list_constantes_medicales}}
              {{assign var=backgroundQuestion value="black"}}
              {{assign var=iconQuestion value="fa-circle"}}
              {{foreach from=$selection_constantes item=_name_cte}}
                {{if $consult->_list_constantes_medicales->$_name_cte &&
                    ($_name_cte != "temperature" || ($suivi && $suivi->type_suivi == "urg"))}}
                  <tr>
                    <td style="vertical-align: top;">
                      <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                    </td>
                    <td style="color: {{$backgroundQuestion}}">
                      {{mb_label object=$consult->_list_constantes_medicales field=$_name_cte}}:<br />
                      {{if $_name_cte == "ta"}}
                        {{mb_value object=$consult->_list_constantes_medicales field=_ta_systole}} /
                        {{mb_value object=$consult->_list_constantes_medicales field=_ta_diastole}}
                      {{else}}
                        {{mb_value object=$consult->_list_constantes_medicales field=$_name_cte}}
                      {{/if}}
                      {{$liste_unites.$_name_cte.unit}}
                    </td>
                  </tr>
                {{/if}}
              {{/foreach}}
            {{/if}}
          </table>
        </td>
      {{else}}
        <td colspan="50" class="empty">
          {{tr}}CSuiviGrossesse.none{{/tr}}
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th colspan="2">Date</th>
    <th>{{mb_label class=CSuiviGrossesse field=evenements_anterieurs}}</th>
    <th>Signes fonctionnels actuels</th>
    <th>Examen général</th>
  </tr>
  </thead>
</table>

<table class="tbl me-letter-spacing-tight">
  <tbody>
  {{foreach from=$grossesse->_ref_consultations item=consult}}
    {{assign var=suivi value=$consult->_ref_suivi_grossesse}}
    <tr>
      <td class="narrow {{if $consult->annule}}cancelled{{/if}}">
        {{if !$offline}}
          <button type="button" class="consultation notext not-printable me-tertiary" title="Modifier la consultation"
                  onclick="Tdb.editConsult('{{$consult->_id}}', refreshListeSuivis);"></button>
          <br />
          <button type="button" class="clock notext not-printable me-tertiary" title="Modifier le RDV"
                  onclick="Tdb.editRdvConsult('{{$consult->_id}}', '{{$grossesse->_id}}', '{{$consult->patient_id}}', refreshListeSuivis);"></button>
        {{/if}}
      </td>
      <td class="narrow {{if $consult->annule}}cancelled{{/if}}">
        {{mb_value object=$consult field=_datetime}} - {{mb_value object=$consult field=_sa}} SA
        + {{mb_value object=$consult field=_ja}} j
        <br />
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_praticien}}
        {{if $suivi && $suivi->_id}}
          <br />
          {{mb_value object=$suivi field=type_suivi}}
        {{/if}}
      </td>
      {{if $consult->annule}}
        <td colspan="50" class="empty">
          {{tr}}Cancelled{{/tr}}
        </td>
      {{elseif $suivi && $suivi->_id}}
        <td>
          <table class="layout">
            {{if $suivi->bruit_du_coeur !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-times"}}
                  {{if $suivi->bruit_du_coeur === 'percu'}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=bruit_du_coeur}} :
                  {{mb_value object=$suivi field=bruit_du_coeur}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->col_normal !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->col_normal === 'n'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=col_normal}} :
                  {{mb_value object=$suivi field=col_normal}}
                </td>
              </tr>
              {{if $suivi->longueur_col !== null}}
                <tr>
                  <td>
                  </td>
                  <td class="compact">
                    {{mb_label object=$suivi field=longueur_col}} :
                    {{mb_value object=$suivi field=longueur_col}}
                  </td>
                </tr>
              {{/if}}
              {{if $suivi->position_col !== null}}
                <tr>
                  <td>
                  </td>
                  <td class="compact">
                    {{mb_label object=$suivi field=position_col}} :
                    {{mb_value object=$suivi field=position_col}}
                  </td>
                </tr>
              {{/if}}
              {{if $suivi->dilatation_col !== null}}
                <tr>
                  <td>
                  </td>
                  <td class="compact">
                    {{mb_label object=$suivi field=dilatation_col}} :
                    {{mb_value object=$suivi field=dilatation_col}} {{if $suivi->dilatation_col_num}}({{$suivi->dilatation_col_num}} cm){{/if}}
                  </td>
                </tr>
              {{/if}}
              {{if $suivi->consistance_col !== null}}
                <tr>
                  <td>
                  </td>
                  <td class="compact">
                    {{mb_label object=$suivi field=consistance_col}} :
                    {{mb_value object=$suivi field=consistance_col}}
                  </td>
                </tr>
              {{/if}}
            {{/if}}
            {{if $suivi->col_commentaire}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=col_commentaire}}
                  : {{mb_value object=$suivi field=col_commentaire}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->presentation_position !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-question"}}
                  {{if $suivi->presentation_position === 'som'}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{elseif $suivi->presentation_position === 'sie' ||
                  $suivi->presentation_position === 'tra'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-check"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=presentation_position}} :
                  {{mb_value object=$suivi field=presentation_position}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->presentation_etat !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->presentation_etat === 'amo' ||
                  $suivi->presentation_etat === 'fix' ||
                  $suivi->presentation_etat === 'eng'}}
                    {{assign var=backgroundQuestion value="red"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=presentation_etat}} :
                  {{mb_value object=$suivi field=presentation_etat}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->segment_inferieur !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->segment_inferieur === 'namp'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=segment_inferieur}} :
                  {{mb_value object=$suivi field=segment_inferieur}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->membranes !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->membranes === 'romp' ||
                  $suivi->membranes === 'susrupt'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=membranes}} :
                  {{mb_value object=$suivi field=membranes}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->bassin !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->bassin === 'anomalie'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=bassin}} :
                  {{mb_value object=$suivi field=bassin}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->examen_genital !== null}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->examen_genital === 'anomalie'}}
                    {{assign var=backgroundQuestion value="red"}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=examen_genital}} :
                  {{mb_value object=$suivi field=examen_genital}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->hauteur_uterine}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=hauteur_uterine}} :
                  {{mb_value object=$suivi field=hauteur_uterine}} cm
                </td>
              </tr>
            {{/if}}
            {{if $suivi->rques_exam_gyneco_obst}}
              <tr>
                <td style="vertical-align: top;">
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=rques_exam_gyneco_obst}}
                  : {{mb_value object=$suivi field=rques_exam_gyneco_obst}}
                </td>
              </tr>
            {{/if}}
          </table>
        </td>
        <td>
          <table class="layout">
            {{if $suivi->frottis !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->frottis === 'nfait'}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=frottis}} :
                  {{mb_value object=$suivi field=frottis}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->echographie !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->echographie === 'nfait'}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=echographie}} :
                  {{mb_value object=$suivi field=echographie}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->prelevement_bacterio !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  {{if $suivi->prelevement_bacterio === 'nfait'}}
                    {{assign var=iconQuestion value="fa-times"}}
                  {{/if}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=prelevement_bacterio}} :
                  {{mb_value object=$suivi field=prelevement_bacterio}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->glycosurie !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=glycosurie}} :
                  {{mb_value object=$suivi field=glycosurie}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->leucocyturie !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=leucocyturie}} :
                  {{mb_value object=$suivi field=leucocyturie}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->albuminurie !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=albuminurie}} :
                  {{mb_value object=$suivi field=albuminurie}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->nitrites !== null}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-check"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=nitrites}} :
                  {{mb_value object=$suivi field=nitrites}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->autre_exam_comp}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=autre_exam_comp}}
                  : {{mb_value object=$suivi field=autre_exam_comp}}
                </td>
              </tr>
            {{/if}}
            {{if $suivi->jours_arret_travail}}
              <tr>
                <td>
                  {{assign var=backgroundQuestion value="black"}}
                  {{assign var=iconQuestion value="fa-circle"}}
                  <i class="fa {{$iconQuestion}}" style="color: {{$backgroundQuestion}};"></i>
                </td>
                <td style="color: {{$backgroundQuestion}}">
                  {{mb_label object=$suivi field=jours_arret_travail}}
                  : {{mb_value object=$suivi field=jours_arret_travail}}
                </td>
              </tr>
            {{/if}}
          </table>
        </td>
        {{if $prescription_installed}}
          <td style="vertical-align: top;">
            {{if isset($consult->_ref_prescriptions.externe|smarty:nodefaults)}}
              {{assign var=prescription value=$consult->_ref_prescriptions.externe}}
              {{if $prescription->_ref_prescription_lines|@count}}
                {{tr}}CPrescription._chapitres.med{{/tr}} :
                <ul>
                  {{foreach from=$prescription->_ref_prescription_lines item=_line_med}}
                    <li>
                      <strong>
                        {{$_line_med->_ucd_view}}
                      </strong>
                    </li>
                  {{/foreach}}
                </ul>
              {{/if}}
              {{if $prescription->_ref_prescription_lines_element_by_chap|@count}}
                {{foreach from=$prescription->_ref_prescription_lines_element_by_chap key=chap item=lines_elt}}
                  {{tr}}CPrescription._chapitres.{{$chap}}{{/tr}} :
                  <ul>
                    {{foreach from=$lines_elt item=_line_elt}}
                      <li>
                        <strong>
                          {{$_line_elt}}
                        </strong>
                      </li>
                    {{/foreach}}
                  </ul>
                {{/foreach}}
              {{/if}}
            {{/if}}
          </td>
        {{/if}}
        <td class="text">
          {{mb_value object=$suivi field=conclusion}}
        </td>
      {{else}}
        <td colspan="50" class="empty">
          {{tr}}CSuiviGrossesse.none{{/tr}}
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th colspan="2">Date</th>
    <th>Examen obstétrical</th>
    <th>Examens complémentaires</th>
    {{if $prescription_installed}}
      <th>
        Prescription
      </th>
    {{/if}}
    <th>{{mb_label class=CSuiviGrossesse field=conclusion}}</th>
  </tr>
  </thead>
</table>
