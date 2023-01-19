{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="ccam_selector"}}
{{mb_script module=cim10          script=CIM}}


<script type="text/javascript">

  Main.add(function () {
    var tabs = Control.Tabs.create('tab-resultats');
  });

  function changePageSejour(page) {
    $V(getForm('listFilterSejour').page_sejour, page);
  }

  function changePageInterv(page) {
    $V(getForm('listFilterInterv').page_interv, page);
  }

  function changePageConsult(page) {
    $V(getForm('listFilterConsult').page_consult, page);
  }

  function changePageAntecedent(page) {
    $V(getForm('listFilterAntecedent').page_antecedent, page);
  }

  function changePageTraitement(page) {
    $V(getForm('listFilterTraitement').page_traitement, page);
  }

  function changePageDossierMed(page) {
    $V(getForm('listFilterDossierMed').page_dossierMedical, page);
  }

</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="recherche" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="new" value="1" />
        <input type="hidden" name="rechercheOpClass" value="COperation" />
        <input type="hidden" name="rechercheChir" value="{{$user_id}}" />

        <table class="form">
          <tr>
            <th class="title" colspan="4">Recherche d'un dossier patient</th>
          </tr>
          
          <!-- Criteres sur les patients -->
          <tr>
            <th class="category" colspan="4">Patient</th>
          </tr>
          <tr>
            <th>{{mb_label object=$ant field="rques"}}</th>
            <td><input type="text" name="antecedent_patient" value="{{$antecedent_patient|stripslashes}}" /></td>
            <th>{{mb_label object=$trait field="traitement"}}</th>
            <td><input type="text" name="traitement_patient" value="{{$traitement_patient|stripslashes}}" /></td>
          </tr>
          <tr>
            <th>{{mb_label object=$dossierMedical field="codes_cim"}}</th>
            <td colspan="4">
              <input type="text" name="diagnostic_patient" value="{{$diagnostic_patient|stripslashes}}" />
              <button class="search notext" type="button"
                      onclick="CIM.viewSearch($V.curry(this.form.elements['diagnostic_patient']), this.form.elements['rechercheChir']);">
                Rechercher
              </button>
            </td>
          </tr>


          {{if $canCabinet->read}}
            <!-- Criteres sur les consultations -->
            <tr>
              <th class="category" colspan="4">Consultation</th>
            </tr>
            <tr>
              <td colspan="4">Au moins un critère <input type="radio" name="recherche_consult" value="or"
                                                         {{if $recherche_consult == "or"}}checked{{/if}} />
                Tous les critères <input type="radio" name="recherche_consult" value="and"
                                         {{if $recherche_consult == "and"}}checked{{/if}} /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$consult field="motif"}}</th>
              <td><input type="text" name="motif_consult" value="{{$motif_consult|stripslashes}}" /></td>

              <th>{{mb_label object=$consult field="rques"}}</th>
              <td><input type="text" name="remarque_consult" value="{{$remarque_consult|stripslashes}}" /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$consult field="examen"}}</th>
              <td><input type="text" name="examen_consult" value="{{$examen_consult|stripslashes}}" /></td>

              <th>{{mb_label object=$consult field="traitement"}}</th>
              <td><input type="text" name="traitement_consult" value="{{$traitement_consult|stripslashes}}" /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$consult field="conclusion"}}</th>
              <td colspan="4"><input type="text" name="conclusion_consult" value="{{$conclusion_consult|stripslashes}}" /></td>
            </tr>
          {{/if}}


          {{if $canPlanningOp->read}}
            <!-- Critères sur les séjours -->
            <tr>
              <th class="category" colspan="4">Séjour</th>
            </tr>
            <tr>
              <td colspan="4">Au moins un critère <input type="radio" name="recherche_sejour" value="or"
                                                         {{if $recherche_sejour == "or"}}checked{{/if}} />
                Tous les critères <input type="radio" name="recherche_sejour" value="and"
                                         {{if $recherche_sejour == "and"}}checked{{/if}} /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$sejour field="type"}}</th>
              <td>{{mb_field object=$sejour field="type"}}</td>
              <th>{{mb_label object=$sejour field="convalescence"}}</th>
              <td><input type="text" name="convalescence_sejour" value="{{$convalescence_sejour|stripslashes}}" /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$sejour field="rques"}}</th>
              <td colspan="4"><input type="text" name="remarque_sejour" value="{{$remarque_sejour|stripslashes}}" /></td>
            </tr>
            <!-- Critères sur les interventions -->
            <tr>
              <th class="category" colspan="4">Intervention</th>
            </tr>
            <tr>
              <td colspan="4">Au moins un critère <input type="radio" name="recherche_intervention" value="or"
                                                         {{if $recherche_intervention == "or"}}checked{{/if}} />
                Tous les critères <input type="radio" name="recherche_intervention" value="and"
                                         {{if $recherche_intervention == "and"}}checked{{/if}} /></td>
            </tr>
            <tr>
              <!-- materiel a prevoir / examens per-op -->
              <th>{{mb_label object=$intervention field="materiel"}}</th>
              <td><input type="text" name="materiel_intervention" value="{{$materiel_intervention|stripslashes}}" /></td>
              <!-- Exam per op -->
              <th>{{mb_label object=$intervention field="exam_per_op"}}</th>
              <td><input type="text" name="examen_per_op" value="{{$examen_per_op|stripslashes}}" /></td>
            </tr>
            <tr>
              <th>{{mb_label object=$intervention field="rques"}}</th>
              <td><input type="text" name="remarque_intervention" value="{{$remarque_intervention|stripslashes}}" /></td>
              <!-- bilan pre-op -->
              <th>{{mb_label object=$intervention field="examen"}}</th>
              <td><input type="text" name="examen_intervention" value="{{$examen_intervention|stripslashes}}" /></td>
            </tr>
            <tr>
              <th>Codes CCAM</th>
              <td>
                <input type="text" name="ccam_intervention" value="{{$ccam_intervention|stripslashes}}" />
                <button class="search notext" type="button" onclick="CCAMSelector.init()">Rechercher</button>

                <script type="text/javascript">
                  CCAMSelector.init = function () {
                    this.sForm = "recherche";
                    this.sClass = "rechercheOpClass";
                    this.sChir = "rechercheChir";
                    this.sView = "ccam_intervention";
                    this.pop();
                  }
                </script>
              </td>
              <th>{{mb_label object=$intervention field="libelle"}}</th>
              <td><input type="text" name="libelle_intervention" value="{{$libelle_intervention|stripslashes}}" /></td>
            </tr>
          {{/if}}

          <tr>
            <td class="button" colspan="4">
              <button class="search" type="submit">Rechercher</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    
    <td class="halfPane">
      <ul id="tab-resultats" class="control_tabs">
        {{if $dossiersMed}}
          <li><a href="#diagnostic">Diagnostics ({{$total_dossierMedicals}})</a></li>{{/if}}
        {{if $traitements}}
          <li><a href="#traitement">Traitements ({{$total_page_traitements}})</a></li>{{/if}}
        {{if $antecedents}}
          <li><a href="#antecedent">{{tr}}CAntecedent.more{{/tr}} ({{$total_antecedents}})</a></li>{{/if}}
        {{if $consultations && $canCabinet->read}}
          <li><a href="#consultation">Consultations ({{$total_consults}})</a></li>{{/if}}
        {{if $interventions && $canPlanningOp->read}}
          <li><a href="#intervention">Interventions ({{$total_intervs}})</a></li>{{/if}}
        {{if $sejours && $canPlanningOp->read}}
          <li><a href="#sejour">Séjours ({{$total_sejours}})</a></li>{{/if}}
        {{if !$antecedents && !$traitements && !$dossiersMed && !$consultations && !$sejours && !$interventions }}
          <li><a href="#noresult">Aucun résultat pour la recherche</a></li>
        {{/if}}
      </ul>

      {{if $dossiersMed}}
        <form name="listFilterDossierMed" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_dossierMedical" value="{{$page_dossierMedical}}" onchange="this.form.submit()" />
          <table class="tbl" id="diagnostic" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_dossierMedicals current=$page_dossierMedical change_page='changePageDossierMed'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$dossiersMed item=_dossier}}
              {{assign var="patient" value=$_dossier->_ref_object}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}

      {{if $traitements}}
        <form name="listFilterTraitement" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_traitement" value="{{$page_traitement}}" onchange="this.form.submit()" />
          <table class="tbl" id="traitement" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_page_traitements current=$page_traitement change_page='changePageTraitement'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$traitements item=_traitement}}
              {{assign var="patient" value=$_traitement->_ref_dossier_medical->_ref_object}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}


      {{if $antecedents}}
        <form name="listFilterAntecedent" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_antecedent" value="{{$page_antecedent}}" onchange="this.form.submit()" />
          <table class="tbl" id="antecedent" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_antecedents current=$page_antecedent change_page='changePageAntecedent'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$antecedents item=_antecedent}}
              {{assign var="patient" value=$curr_antecedent->_ref_dossier_medical->_ref_object}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}

      {{if $consultations && $canCabinet->read}}
        <form name="listFilterInterv" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_consult" value="{{$page_consult}}" onchange="this.form.submit()" />
          <table class="tbl" id="consultation" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_consults current=$page_consult change_page='changePageConsult'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$consultations item=_consultation}}
              {{assign var=patient value=$_consultation->_ref_patient}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}


      {{if $interventions && $canPlanningOp->read}}
        <form name="listFilterInterv" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_interv" value="{{$page_interv}}" onchange="this.form.submit()" />
          <table class="tbl" id="intervention" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_intervs current=$page_interv change_page='changePageInterv'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$interventions item=_intervention}}
              {{assign var=patient value=$_intervention->_ref_sejour->_ref_patient}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}


      {{if $sejours && $canPlanningOp->read}}
        <form name="listFilterSejour" action="?m={{$m}}" method="get">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="page_sejour" value="{{$page_sejour}}" onchange="this.form.submit()" />
          <table class="tbl" id="sejour" style="display: none;">
            <tr>
              <td colspan="3">
                {{mb_include module=system template=inc_pagination total=$total_sejours current=$page_sejour change_page='changePageSejour'}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label class=CPatient field="nom"}}</th>
              <th>{{mb_label class=CPatient field="naissance"}}</th>
              <th>{{mb_label class=CPatient field="adresse"}}</th>
            </tr>
            {{foreach from=$sejours item=_sejour}}
              {{assign var=patient value=$_sejour->_ref_patient}}
              {{mb_include module=patients template=inc_list_dossier_clinique}}
            {{/foreach}}
          </table>
        </form>
      {{/if}}

      {{if !$antecedents && !$traitements && !$dossiersMed && !$consultations && !$sejours && !$interventions }}
        <div id="noresult" style="display: none;"></div>
      {{/if}}
    </td>
</table>