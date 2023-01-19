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
        &horbar; {{if $_nom_service == 'NP'}}{{tr}}CService-Not placed{{/tr}}{{else}}{{$_nom_service}}{{/if}}</th>
    </tr>
    {{foreach from=$naissances item=_naissance}}
      {{assign var=sejour             value=$_naissance->_ref_sejour_enfant}}
      {{assign var=sejour_mere        value=$_naissance->_ref_sejour_maman}}
      {{assign var=grossesse          value=$sejour_mere->_ref_grossesse}}
      {{assign var=patient            value=$sejour->_ref_patient}}
      {{assign var=constantes         value=$patient->_ref_first_constantes}}
      {{assign var=prescription       value=$sejour->_ref_prescription_sejour}}
      {{assign var=last_affecation    value=$sejour->_ref_last_affectation}}
      {{assign var=lit                value=$last_affecation->_ref_lit}}
      <tr>
        <td>{{mb_value object=$lit field=_view}}</td>
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
            {{$grossesse->_semaine_grossesse}} {{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}
          + {{mb_value object=$grossesse field=_reste_semaine_grossesse}} j
        </td>
        <td>
          {{if $_naissance->by_caesarean}}
            {{tr}}CNaissance-by_caesarean-court{{/tr}}
          {{else}}
            {{tr}}CAccouchement-Vaginal delivery{{/tr}}
          {{/if}}
        </td>
        {{if $prescription}}
          {{foreach from=$prescription->_ref_prescription_lines_element_rubrique key=_rubrique item=lines_elt}}
            <td class="text {{$_rubrique}}">
              {{foreach from=$lines_elt item=_line_elt}}
                {{assign var=elt value=$_line_elt->_ref_element_prescription}}
                <div>
                  {{mb_ternary test=$elt->libelle_court value=$elt->libelle_court other=$elt->libelle}}
                </div>
              {{/foreach}}
            </td>
          {{/foreach}}
        {{else}}
          <td class="exam"></td>
          <td class="bilan"></td>
          <td class="soins_ide"></td>
          <td class="soins_as"></td>
        {{/if}}
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
        <td></td>
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
        {{tr}}CNaissance-Transmission sheet{{/tr}}
      </a>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CLit field=nom}}</th>
    <th class="narrow">{{tr}}CPatient{{/tr}}</th>
    <th class="narrow text">{{tr}}CNaissance-Date and time of birth{{/tr}}</th>
    <th class="narrow">{{mb_title class=CPatient field=sexe}}</th>
    <th class="narrow">{{mb_title class=CConstantesMedicales field=poids}} (g)</th>
    <th class="narrow text">{{tr}}CNaissance-Type of breastfeeding{{/tr}}</th>
    <th class="narrow">{{tr}}CNaissance-Term{{/tr}}</th>
    <th class="narrow text">{{tr}}CGrossesseAnt-mode_accouchement{{/tr}}</th>
    <th style="width: 5%;">{{tr}}CElementPrescription.rubrique_feuille_trans.exam{{/tr}}</th>
    <th style="width: 5%;">{{tr}}CElementPrescription.rubrique_feuille_trans.bilan{{/tr}}</th>
    <th style="width: 5%;">{{tr}}CElementPrescription.rubrique_feuille_trans.soins_ide{{/tr}}</th>
    <th style="width: 5%;">{{tr}}CElementPrescription.rubrique_feuille_trans.soins_as{{/tr}}</th>
    <th class="narrow">{{mb_title class=CSejour field=sortie}}</th>
    <th>{{tr}}CMediusers-commentaires{{/tr}}</th>
  </tr>
  </thead>
</table>
