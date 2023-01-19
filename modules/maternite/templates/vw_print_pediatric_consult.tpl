{{*
* @package Mediboard\Maternite
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    window.print();
  });
</script>

<table class="main tbl">
  <tbody>
  {{foreach from=$services_selected key=_nom_serice item=naissances}}
    <tr>
      <th class="section" colspan="18">{{tr}}CService{{/tr}} &horbar; {{$_nom_serice}}</th>
    </tr>
    {{foreach from=$naissances item=_naissance}}
      {{assign var=sejour            value=$_naissance->_ref_sejour_enfant}}
      {{assign var=sejour_mere       value=$_naissance->_ref_sejour_maman}}
      {{assign var=grossesse         value=$sejour_mere->_ref_grossesse}}
      {{assign var=dossier_perinat   value=$grossesse->_ref_dossier_perinat}}
      {{assign var=patient           value=$sejour->_ref_patient}}
      {{assign var=last_affecation   value=$sejour->_ref_last_affectation}}
      {{assign var=lit               value=$last_affecation->_ref_lit}}
      {{assign var=constantes        value=$patient->_ref_first_constantes}}
      {{assign var=prescription      value=$sejour->_ref_prescription_sejour}}
      {{assign var=examen_nouveau_ne value=$_naissance->_ref_last_examen_nouveau_ne}}
      {{assign var=oea_exam               value=$examen_nouveau_ne->_oea_exam}}
      <tr>
        <td>{{mb_value object=$lit field=_view}}</td>
        <td>{{mb_value object=$_naissance field=num_naissance}}</td>
        <td>
          {{if $_naissance->_service_neonatalogie}}
            {{tr}}common-Yes{{/tr}}
          {{/if}}
        </td>
        <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                {{mb_value object=$patient field=_view}}
              </span>
        </td>
        <td>{{mb_value object=$_naissance field=date_time}}</td>
        <td>{{mb_value object=$patient field=sexe}}</td>
        <td>{{mb_value object=$constantes field=_poids_g}}</td>
        <td>{{mb_value object=$_naissance field=rques}}</td>
        <td>{{$grossesse->_semaine_grossesse}} {{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}</td>
        <td>
          {{if $_naissance->by_caesarean}}
            {{tr}}CNaissance-by_caesarean-court{{/tr}}
          {{else}}
            {{tr}}CAccouchement-Vaginal delivery{{/tr}}
          {{/if}}
        </td>
        <td>
          {{if $examen_nouveau_ne->guthrie_datetime && $examen_nouveau_ne->guthrie_user_id}}
              {{assign var=administrateur_guthrie value=$examen_nouveau_ne->_ref_guthrie_user_id}}
            <i class="fa fa-check" style="color: #078227"></i>
            <span onmouseover="ObjectTooltip.createDOM(this, 'guthrie_{{$_naissance->_id}}');">{{tr}}common-Yes{{/tr}}</span>
            <div id="guthrie_{{$_naissance->_id}}" style="display: none;">
              <table class="tbl">
                <tr>
                  <th colspan="2">{{tr}}CNaissance-Guthrie{{/tr}}</th>
                </tr>
                <tr>
                  <th>{{tr}}common-Date{{/tr}}</th>
                  <th>{{tr}}Who{{/tr}}</th>
                </tr>
                <tr>
                  <td>{{$examen_nouveau_ne->guthrie_datetime|date_format:$conf.datetime}}</td>
                  <td>
                    <span
                      onmouseover="ObjectTooltip.createEx(this, '{{$administrateur_guthrie->_guid}}');">{{$administrateur_guthrie->_view}}</span>
                  </td>
                </tr>
              </table>
            </div>
          {{else}}
            <i class="fa fa-times" style="color: #820001"></i>
              {{tr}}common-No{{/tr}}
          {{/if}}
        </td>
        <td>
         <span id="oea-{{$_naissance->_id}}">
            {{mb_include module=maternite template=inc_oea object=$_naissance}}
         </span>
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
            {{if $sejour->sortie_reelle}}
              <i class="fas fa-check" style="color: green;"></i>
              {{mb_value object=$sejour field=sortie_reelle}}
            {{else}}
              {{mb_value object=$sejour field=sortie_prevue}}
            {{/if}}
          </span>
        </td>
        <td>{{mb_value object=$dossier_perinat field=info_lien_pmi}}</td>
        <td class="narrow">{{$_naissance->_consult_pediatre}}</td>
      </tr>
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="20">{{tr}}CNaissance.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="20">
      <a href="#" onclick="window.print();">
        {{tr}}CNaissance-action-List of pediatric consultation|pl{{/tr}}
      </a>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CLit field=nom}}</th>
    <th class="narrow text">{{mb_title class=CNaissance field=num_naissance}}</th>
    <th class="text">{{mb_title class=CService field=neonatalogie}}</th>
    <th class="narrow">{{tr}}CPatient{{/tr}}</th>
    <th class="narrow text">{{tr}}CNaissance-Date and time of birth{{/tr}}</th>
    <th class="narrow">{{mb_title class=CPatient field=sexe}}</th>
    <th>{{mb_title class=CConstantesMedicales field=poids}} (g)</th>
    <th class="text">{{tr}}CNaissance-Type of breastfeeding{{/tr}}</th>
    <th class="narrow">{{tr}}CNaissance-Term{{/tr}}</th>
    <th class="narrow">{{tr}}CGrossesseAnt-mode_accouchement{{/tr}}</th>
    <th class="narrow text">{{tr}}CNaissance-GUTHRIE realized{{/tr}}</th>
    <th class="narrow text">{{tr}}CNaissance-OEA realized{{/tr}}</th>
    <th class="narrow">{{mb_title class=CSejour field=sortie}}</th>
    <th>{{tr}}CNaissance-PMI link{{/tr}}</th>
    <th class="narrow text">{{tr}}CNaissance-Consultation with pediatrician{{/tr}}</th>
  </tr>
  </thead>
</table>
