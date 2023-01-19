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
  {{foreach from=$services_selected key=_nom_service item=naissances}}
    <tr>
      <th class="section" colspan="18">{{tr}}CService{{/tr}}
        {{if $_nom_service == 'NP'}}
          {{tr}}CService-Not placed{{/tr}}
        {{else}}
          {{$_nom_service}}
        {{/if}}
{{*        &horbar; {{if $_service_id == 'NP'}}{{tr}}CService-Not placed{{/tr}}{{else}}{{$services.$_service_id}}{{/if}}</th>*}}
    </tr>
    {{foreach from=$naissances item=_naissance}}
      {{assign var=sejour             value=$_naissance->_ref_sejour_enfant}}
      {{assign var=patient            value=$sejour->_ref_patient}}
      {{assign var=last_affecation    value=$sejour->_ref_last_affectation}}
      {{assign var=lit                value=$last_affecation->_ref_lit}}
      {{assign var=constantes         value=$patient->_ref_first_constantes}}
      {{assign var=prescription       value=$sejour->_ref_prescription_sejour}}
      {{assign var=examen_nouveau_ne  value=$_naissance->_ref_last_examen_nouveau_ne}}
      {{assign var=oea                value=$examen_nouveau_ne->_oea_exam}}
      <tr>
        <td>{{mb_value object=$lit field=_view}}</td>
        <td>{{mb_value object=$_naissance field=num_naissance}}</td>
        <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                {{mb_value object=$patient field=_view}}
              </span>
        </td>
        <td>{{mb_value object=$_naissance field=date_time}}</td>
        <td>{{mb_value object=$patient field=sexe}}</td>
        <td>{{mb_value object=$constantes field=_poids_g}}</td>
        <td>{{mb_value object=$_naissance field=rques}}</td>
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
        <td colspan="5">
          <table class="form" style="width: 100%">
            <tr>
              {{if $oea && ($oea|@count > 0)}}
              {{foreach from=$oea item=_exam_oea}}
              {{assign var=examinateur_oea value=$_exam_oea->_ref_examinateur}}
            <tr>
              <td style="width: 22%;">{{$_exam_oea->date|date_format:$conf.date}}</td>
              <td style="width: 22%;">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$examinateur_oea->_guid}}');">{{$examinateur_oea->_view}}</span>
              </td>
              <td style="width: 22%;">{{tr}}CExamenNouveauNe.oreille_droite.{{$_exam_oea->oreille_droite}}{{/tr}}</td>
              <td style="width: 22%;">{{tr}}CExamenNouveauNe.oreille_gauche.{{$_exam_oea->oreille_gauche}}{{/tr}}</td>
              <td style="width: 22%;">{{mb_value object=$_exam_oea field=rdv_orl}}</td>
            </tr>
            {{/foreach}}
            {{else}}
            <td style="width: 20%;"></td>
            <td style="width: 20%;"></td>
            <td style="width: 20%;"></td>
            <td style="width: 20%;"></td>
            <td style="width: 20%;"></td>
            {{/if}}
            </tr>
          </table>
        </td>
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
        {{tr}}CNaissance-List for pediatric nurse|pl{{/tr}}
      </a>
    </th>
  </tr>
  <tr>
    <th rowspan="2" class="narrow">{{mb_title class=CLit field=nom}}</th>
    <th rowspan="2" class="narrow text">{{mb_title class=CNaissance field=num_naissance}}</th>
    <th rowspan="2" class="narrow">{{tr}}CPatient{{/tr}}</th>
    <th rowspan="2" class="narrow text">{{tr}}CNaissance-Date and time of birth{{/tr}}</th>
    <th rowspan="2" class="narrow">{{mb_title class=CPatient field=sexe}}</th>
    <th rowspan="2" class="narrow">{{mb_title class=CConstantesMedicales field=poids}} (g)</th>
    <th rowspan="2" class="narrow text">{{tr}}CNaissance-Type of breastfeeding{{/tr}}</th>
    <th rowspan="2" class="narrow">{{mb_title class=CSejour field=sortie}}</th>
    <th class="narrow" colspan="5">{{tr}}CNaissance-oea{{/tr}}</th>
  </tr>
  <tr>
    <th style="width: 20%;">{{tr}}common-Date{{/tr}}</th>
    <th style="width: 20%;">{{tr}}Who{{/tr}}</th>
    <th style="width: 20%;">{{tr}}OD{{/tr}}</th>
    <th style="width: 20%;">{{tr}}OG{{/tr}}</th>
    <th style="width: 20%;">{{tr}}CNaissance-ENT appointment{{/tr}}</th>
  </tr>
  </thead>
</table>
