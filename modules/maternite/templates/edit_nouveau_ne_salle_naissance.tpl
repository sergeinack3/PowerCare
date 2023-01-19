{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=enfant value=$naissance->_ref_sejour_enfant->_ref_patient}}

<script>
  listForms = [
    getForm("Nouveau-ne-{{$naissance->_guid}}"),
    getForm("Score-Apgar-{{$naissance->_guid}}"),
    getForm("Equilibre-ph-{{$naissance->_guid}}"),
    getForm("Arrivee-nouveau-ne-{{$naissance->_guid}}"),
    getForm("Monitorage-{{$naissance->_guid}}"),
    getForm("Reanimation-{{$naissance->_guid}}"),
    getForm("Prophylaxie-{{$naissance->_guid}}"),
    getForm("Prevention-{{$naissance->_guid}}"),
    getForm("Nouveau-ne-constantes-{{$naissance->_guid}}"),
    getForm("Sortie-{{$naissance->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  calculApgarScore1 = function (form) {
    //1 min
    var score_apgar_1 = parseInt($V(form.apgar_coeur_1)) + parseInt($V(form.apgar_respi_1)) + parseInt($V(form.apgar_tonus_1)) +
      parseInt($V(form.apgar_reflexes_1)) + parseInt($V(form.apgar_coloration_1));

    $('apgar_value_1').innerText = isNaN(score_apgar_1) ? 0 : score_apgar_1;
  };

  calculApgarScore3 = function (form) {
    //3 min
    var score_apgar_3 = parseInt($V(form.apgar_coeur_3)) + parseInt($V(form.apgar_respi_3)) + parseInt($V(form.apgar_tonus_3)) +
      parseInt($V(form.apgar_reflexes_3)) + parseInt($V(form.apgar_coloration_3));

    $('apgar_value_3').innerText = isNaN(score_apgar_3) ? 0 : score_apgar_3;
  };

  calculApgarScore5 = function (form) {
    //5 min
    var score_apgar_5 = parseInt($V(form.apgar_coeur_5)) + parseInt($V(form.apgar_respi_5)) + parseInt($V(form.apgar_tonus_5)) +
      parseInt($V(form.apgar_reflexes_5)) + parseInt($V(form.apgar_coloration_5));

    $('apgar_value_5').innerText = isNaN(score_apgar_5) ? 0 : score_apgar_5;
  };

  calculApgarScore10 = function (form) {
    //10 min
    var score_apgar_10 = parseInt($V(form.apgar_coeur_10)) + parseInt($V(form.apgar_respi_10)) + parseInt($V(form.apgar_tonus_10)) +
      parseInt($V(form.apgar_reflexes_10)) + parseInt($V(form.apgar_coloration_10));

    $('apgar_value_10').innerText = isNaN(score_apgar_10) ? 0 : score_apgar_10;
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    includeForms();
    DossierMater.prepareAllForms();
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<table class="main layout">
  <tr>
    <td>
      <form name="Nouveau-ne-{{$naissance->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$naissance}}
        {{mb_key   object=$naissance}}
        <input type="hidden" name="_count_changes" value="0" />
        <fieldset>
          <legend>Nouveau né</legend>
          <table class="form">
            <tr>
              <th class="narrow">{{mb_label object=$enfant field=nom}}</th>
              <td class="narrow">{{mb_value object=$enfant field=nom}}</td>
              <th class="narrow">Nombre d'enfants nés</th>
              <td class="narrow">{{$grossesse->_count.naissances}}</td>
              <th class="narrow">{{mb_label object=$naissance field=presence_pediatre}}</th>
              <td class="narrow">
                {{mb_field object=$naissance field=presence_pediatre
                style="width: 20em;" emptyLabel="CNaissance.presence_pediatre."}}
              </td>
              <td>
                {{mb_label object=$naissance field=pediatre_id style="display: none;"}}
                {{mb_field object=$naissance field=pediatre_id style="width: 20em;"
                options=$praticiens}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$enfant field=prenom}}</th>
              <td>{{mb_value object=$enfant field=prenom}}</td>
              <th>{{mb_label object=$naissance field=rang}}</th>
              <td>{{mb_value object=$naissance field=rang}}</td>
              <th>{{mb_label object=$naissance field=presence_anesth}}</th>
              <td>
                {{mb_field object=$naissance field=presence_anesth
                style="width: 20em;" emptyLabel="CNaissance.presence_anesth."}}
              </td>
              <td>
                {{mb_label object=$naissance field=anesth_id style="display: none;"}}
                {{mb_field object=$naissance field=anesth_id style="width: 20em;"
                options=$anesths}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$enfant field=sexe}}</th>
              <td>{{mb_value object=$enfant field=sexe}}</td>
              <td colspan="5"></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      <script>
        Main.add(function () {
          Control.Tabs.create('tab-salle_naissance', true);
        });
      </script>
      <ul id="tab-salle_naissance" class="control_tabs">
        <li><a href="#etat_naissance">Etat à la naissance</a></li>
        <li><a href="#monitorage">Monitorage</a></li>
        <li><a href="#reanimation">Reanimation</a></li>
        <li><a href="#mesures_prophylactiques">Mesures prophylactiques</a></li>
        <li><a href="#mesures_preventives">Mesures préventives anténatales ou postnatales</a></li>
        <li><a href="#mensurations">Mensurations</a></li>
        <li><a href="#sortie_salle">Sortie de la salle de naissance</a></li>
      </ul>

      <div id="etat_naissance" style="display: none;">
        <table class="main layout">
          <tr>
            <td class="halfPane">
              <form name="Score-Apgar-{{$naissance->_guid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this);">
                {{mb_class object=$naissance}}
                {{mb_key   object=$naissance}}
                <input type="hidden" name="_count_changes" value="0" />
                <fieldset>
                  <legend>Score d'Apgar</legend>
                  <table class="form">
                    <tr>
                      <th></th>
                      <th class="category" style="width: 10em;">0</th>
                      <th class="category" style="width: 10em;">1</th>
                      <th class="category" style="width: 10em;">2</th>
                      <th class="category" style="width: 5em;"><strong>1 min</strong></th>
                      <th class="category" style="width: 5em;">3 min</th>
                      <th class="category" style="width: 5em;"><strong>5 min</strong></th>
                      <th class="category" style="width: 5em;">10 min</th>
                    </tr>
                    <tr>
                      <th><strong>{{mb_label object=$naissance field=apgar_coeur_1}}</strong></th>
                      <td class="text">Absent (< 60/min)</td>
                      <td class="text">< 100/min</td>
                      <td class="text">> 100/min</td>
                      <td
                        class="button">{{mb_field object=$naissance field=apgar_coeur_1 onchange="calculApgarScore1(this.form);"}}</td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coeur_3 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coeur_3 onchange="calculApgarScore3(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coeur_5 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coeur_5 onchange="calculApgarScore5(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coeur_10 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coeur_10 onchange="calculApgarScore10(this.form);"}}
                      </td>
                    </tr>
                    <tr>
                      <th><strong>{{mb_label object=$naissance field=apgar_respi_1}}</strong></th>
                      <td class="text">Absente</td>
                      <td class="text">Hypoventilation<br />cri faible</td>
                      <td class="text">Bonne<br />cri vigoureux</td>
                      <td
                        class="button">{{mb_field object=$naissance field=apgar_respi_1 onchange="calculApgarScore1(this.form);"}}</td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_respi_3 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_respi_3 onchange="calculApgarScore3(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_respi_5 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_respi_5 onchange="calculApgarScore5(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_respi_10 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_respi_10 onchange="calculApgarScore10(this.form);"}}
                      </td>
                    </tr>
                    <tr>
                      <th><strong>{{mb_label object=$naissance field=apgar_tonus_1}}</strong></th>
                      <td class="text">Flasque</td>
                      <td class="text">Légère flexion des extrémités</td>
                      <td class="text">Bon</td>
                      <td
                        class="button">{{mb_field object=$naissance field=apgar_tonus_1 onchange="calculApgarScore1(this.form);"}}</td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_tonus_3 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_tonus_3 onchange="calculApgarScore3(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_tonus_5 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_tonus_5 onchange="calculApgarScore5(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_tonus_10 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_tonus_10 onchange="calculApgarScore10(this.form);"}}
                      </td>
                    </tr>
                    <tr>
                      <th><strong>{{mb_label object=$naissance field=apgar_reflexes_1}}</strong></th>
                      <td class="text">Pas de réponse</td>
                      <td class="text">Légers mouvements</td>
                      <td class="text">Cri</td>
                      <td
                        class="button">{{mb_field object=$naissance field=apgar_reflexes_1 onchange="calculApgarScore1(this.form);"}}</td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_reflexes_3 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_reflexes_3 onchange="calculApgarScore3(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_reflexes_5 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_reflexes_5 onchange="calculApgarScore5(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_reflexes_10 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_reflexes_10 onchange="calculApgarScore10(this.form);"}}
                      </td>
                    </tr>
                    <tr>
                      <th><strong>{{mb_label object=$naissance field=apgar_coloration_1}}</strong></th>
                      <td class="text">Bleu ou blanc</td>
                      <td class="text">Corps rose, extr. cyanosées</td>
                      <td class="text">Tout rose</td>
                      <td
                        class="button">{{mb_field object=$naissance field=apgar_coloration_1 onchange="calculApgarScore1(this.form);"}}</td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coloration_3 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coloration_3 onchange="calculApgarScore3(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coloration_5 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coloration_5 onchange="calculApgarScore5(this.form);"}}
                      </td>
                      <td class="button">
                        {{mb_label object=$naissance field=apgar_coloration_10 style="display: none;"}}
                        {{mb_field object=$naissance field=apgar_coloration_10 onchange="calculApgarScore10(this.form);"}}
                      </td>
                    </tr>
                    <tr>
                      <th colspan="4"><strong>{{mb_label object=$naissance field=_apgar_1}}</strong></th>
                      <td class="button" id="apgar_value_1">{{mb_value object=$naissance field=_apgar_1}}</td>
                      <td class="button" id="apgar_value_3">{{mb_value object=$naissance field=_apgar_3}}</td>
                      <td class="button" id="apgar_value_5">{{mb_value object=$naissance field=_apgar_5}}</td>
                      <td class="button" id="apgar_value_10">{{mb_value object=$naissance field=_apgar_10}}</td>
                    </tr>
                  </table>
                </fieldset>
              </form>
            </td>
            <td>
              <fieldset>
                <legend>Equilibre acido-basique</legend>
                <form name="Equilibre-ph-{{$naissance->_guid}}" method="post"
                      onsubmit="return onSubmitFormAjax(this);">
                  {{mb_class object=$naissance}}
                  {{mb_key   object=$naissance}}
                  <input type="hidden" name="_count_changes" value="0" />
                  <table class="form">
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=ph_ao}}</th>
                      <td>{{mb_field object=$naissance field=ph_ao}} AO</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=ph_v}}</th>
                      <td>{{mb_field object=$naissance field=ph_v}} V</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=base_deficit}}</th>
                      <td>{{mb_field object=$naissance field=base_deficit}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=pco2}}</th>
                      <td>{{mb_field object=$naissance field=pco2}} mm Hg</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=lactates}}</th>
                      <td>{{mb_field object=$naissance field=lactates}}</td>
                    </tr>
                  </table>
                </form>
              </fieldset>
              <fieldset>
                <form name="Arrivee-nouveau-ne-{{$naissance->_guid}}" method="post"
                      onsubmit="return onSubmitFormAjax(this);">
                  {{mb_class object=$naissance}}
                  {{mb_key   object=$naissance}}
                  <input type="hidden" name="_count_changes" value="0" />
                  <table class="form">
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=nouveau_ne_endormi}}</th>
                      <td>{{mb_field object=$naissance field=nouveau_ne_endormi default=""}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=accueil_peau_a_peau}}</th>
                      <td>{{mb_field object=$naissance field=accueil_peau_a_peau default=""}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=debut_allait_salle_naissance}}</th>
                      <td>{{mb_field object=$naissance field=debut_allait_salle_naissance default=""}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=temp_salle_naissance}}</th>
                      <td>{{mb_field object=$naissance field=temp_salle_naissance}}°</td>
                    </tr>
                  </table>
                </form>
              </fieldset>
            </td>
          </tr>
        </table>
      </div>
      <div id="monitorage" style="display: none;">
        <form name="Monitorage-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <fieldset>
            <legend>
              {{mb_label object=$naissance field=monitorage}}
              {{mb_field object=$naissance field=monitorage default=""}}
            </legend>
            <table class="form">
              <tr>
                <th class="thirdPane">Si oui,</th>
                <td></td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=monit_frequence_cardiaque typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=monit_frequence_cardiaque}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=monit_saturation typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=monit_saturation}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=monit_glycemie typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=monit_glycemie}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=monit_incubateur typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=monit_incubateur}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=monit_remarques}}</th>
                <td>{{mb_field object=$naissance field=monit_remarques}}</td>
              </tr>
            </table>
          </fieldset>
        </form>
      </div>
      <div id="reanimation" style="display: none;">
        <form name="Reanimation-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <fieldset>
            <legend>
              {{mb_label object=$naissance field=reanimation}}
              {{mb_field object=$naissance field=reanimation default=""}}
            </legend>
            <table class="form">
              <tr>
                <th class="thirdPane">Si oui,</th>
                <td colspan="3"></td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance_rea field=rea_par}}</th>
                <td colspan="3">
                  {{mb_field object=$naissance_rea field=rea_par
                  style="width: 20em;" emptyLabel="CNaissanceRea.rea_par."}}
                  {{mb_label object=$naissance_rea field=rea_par_id style="display: none;"}}
                  {{mb_field object=$naissance_rea field=rea_par_id style="width: 20em;" options=$profssante canNull=true}}
                  <button type="button" class="add notext" onclick="DossierMater.addOrDeleteNaissanceReaPrat('Reanimation-{{$naissance->_guid}}', '{{$naissance->_id}}')"></button>
                </td>
              </tr>
              <tr>
                <th> </th>
                <td>
                  <div id="rea_list">
                    <ul>
                        {{foreach from=$naissance->_ref_resuscitators item=_resuscitator}}
                          <li data-id="{{$_resuscitator->_id}}" style="list-style: none;" class='me-margin-top-3'>
                            <button type="button" class="remove notext" onclick="DossierMater.addOrDeleteNaissanceReaPrat('Reanimation-{{$naissance->_guid}}', '{{$naissance->_id}}', '{{$_resuscitator->_id}}', 1)"></button>
                              {{mb_value object=$_resuscitator field=rea_par_id}}
                              {{if $_resuscitator->rea_par}}
                                ({{mb_value object=$_resuscitator field=rea_par}})
                              {{/if}}
                          </li>
                        {{/foreach}}
                    </ul>
                  </div>

                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_aspi_laryngo typeEnum=checkbox}}</th>
                <td colspan="3">{{mb_label object=$naissance field=rea_aspi_laryngo}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_ventil_masque typeEnum=checkbox}}</th>
                <td colspan="3">{{mb_label object=$naissance field=rea_ventil_masque}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_o2_sonde typeEnum=checkbox}}</th>
                <td colspan="3">{{mb_label object=$naissance field=rea_o2_sonde}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_ppc_nasale typeEnum=checkbox}}</th>
                <td class="narrow">{{mb_label object=$naissance field=rea_ppc_nasale}}</td>
                <td colspan="2">
                  {{mb_label object=$naissance field=rea_duree_ppc_nasale}} :
                  {{mb_field object=$naissance field=rea_duree_ppc_nasale}} min
                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_ventil_tube_endo typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=rea_ventil_tube_endo}}</td>
                <td colspan="2">
                  {{mb_label object=$naissance field=rea_duree_ventil_tube_endo}} :
                  {{mb_field object=$naissance field=rea_duree_ventil_tube_endo}} min
                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_intub_tracheale typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=rea_intub_tracheale}}</td>
                <td colspan="2">
                  {{mb_label object=$naissance field=rea_min_vie_intub_tracheale}} :
                  {{mb_field object=$naissance field=rea_min_vie_intub_tracheale}} min
                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_massage_card typeEnum=checkbox}}</th>
                <td colspan="3">{{mb_label object=$naissance field=rea_massage_card}}</td>
              </tr>
              <tr>
                <th rowspan="4"
                    style="vertical-align: top;">{{mb_field object=$naissance field=rea_injection_medic typeEnum=checkbox}}</th>
                <td rowspan="4">{{mb_label object=$naissance field=rea_injection_medic}}</td>
                <th class="narrow compact">{{mb_field object=$naissance field=rea_injection_medic_adre typeEnum=checkbox}}</th>
                <td class="compact">{{mb_label object=$naissance field=rea_injection_medic_adre}}</td>
              </tr>
              <tr>
                <th class="compact">{{mb_field object=$naissance field=rea_injection_medic_surfa typeEnum=checkbox}}</th>
                <td class="compact">{{mb_label object=$naissance field=rea_injection_medic_surfa}}</td>
              </tr>
              <tr>
                <th class="compact">{{mb_field object=$naissance field=rea_injection_medic_gluc typeEnum=checkbox}}</th>
                <td class="compact">{{mb_label object=$naissance field=rea_injection_medic_gluc}}</td>
              </tr>
              <tr>
                <th class="compact">{{mb_field object=$naissance field=rea_injection_medic_autre typeEnum=checkbox}}</th>
                <td class="compact">
                  {{mb_label object=$naissance field=rea_injection_medic_autre}}
                  {{mb_label object=$naissance field=rea_injection_medic_autre_desc style="display: none;"}}
                  {{mb_field object=$naissance field=rea_injection_medic_autre_desc}}
                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=rea_autre_geste typeEnum=checkbox}}</th>
                <td colspan="3">
                  {{mb_label object=$naissance field=rea_autre_geste}}
                  {{mb_label object=$naissance field=rea_autre_geste_desc style="display: none;"}}
                  {{mb_field object=$naissance field=rea_autre_geste_desc}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=duree_totale_rea}}</th>
                <td colspan="3">{{mb_field object=$naissance field=duree_totale_rea}} min</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=temp_fin_rea}}</th>
                <td colspan="3">{{mb_field object=$naissance field=temp_fin_rea}} °</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=gly_fin_rea}}</th>
                <td colspan="3">{{mb_field object=$naissance field=gly_fin_rea}} mmol/l</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=etat_fin_rea}}</th>
                <td colspan="3">
                  {{mb_field object=$naissance field=etat_fin_rea
                  style="width: 20em;" emptyLabel="CNaissance.etat_fin_rea."}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=rea_remarques}}</th>
                <td colspan="3">{{mb_field object=$naissance field=rea_remarques}}</td>
              </tr>
            </table>
          </fieldset>
        </form>
      </div>
      <div id="mesures_prophylactiques" style="display: none;">
        <form name="Prophylaxie-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <table class="form">
            <tr>
              <th class="thirdPane">{{mb_field object=$naissance field=prophy_vit_k typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$naissance field=prophy_vit_k}}
                {{mb_label object=$naissance field=prophy_vit_k style="display: none"}}
                {{mb_field object=$naissance field=prophy_vit_k_type
                style="width: 20em;" emptyLabel="CNaissance.prophy_vit_k_type."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_desinfect_occulaire typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_desinfect_occulaire}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_asp_naso_phar typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_asp_naso_phar}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_perm_choanes typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_perm_choanes}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_perm_oeso typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_perm_oeso}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_perm_anale typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_perm_anale}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_emission_urine typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_emission_urine}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_emission_meconium typeEnum=checkbox}}</th>
              <td>{{mb_label object=$naissance field=prophy_emission_meconium}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$naissance field=prophy_autre typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$naissance field=prophy_autre}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CNaissance-prophy_autre_desc"}}
                {{mb_field object=$naissance field=prophy_autre_desc
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$naissance field=prophy_remarques}}</th>
              <td>{{mb_field object=$naissance field=prophy_remarques}}</td>
            </tr>
          </table>
        </form>
      </div>
      <div id="mesures_preventives" style="display: none;">
        <form name="Prevention-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <table class="main layout">
            <tr>
              <td class="thirdPane">
                <fieldset>
                  <legend>
                    {{mb_label object=$naissance field=cortico}}
                    {{mb_field object=$naissance field=cortico default=""}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="halfPane">Si oui,</th>
                      <td></td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=nb_cures_cortico}}</th>
                      <td>{{mb_field object=$naissance field=nb_cures_cortico}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=dern_cure_cortico}}</th>
                      <td>
                        {{mb_field object=$naissance field=dern_cure_cortico
                        style="width: 20em;" emptyLabel="CNaissance.dern_cure_cortico."}}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        {{mb_label object=$naissance field=delai_cortico_acc_j}}
                        {{mb_label object=$naissance field=delai_cortico_acc_h style="display: none;"}}
                      </th>
                      <td>
                        {{mb_field object=$naissance field=delai_cortico_acc_j}} j
                        {{mb_field object=$naissance field=delai_cortico_acc_h}} h
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=prev_cortico_remarques}}</th>
                      <td>{{mb_field object=$naissance field=prev_cortico_remarques}}</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
              <td class="thirdPane">
                <fieldset>
                  <legend>
                    {{mb_label object=$naissance field=contexte_infectieux}}
                    {{mb_field object=$naissance field=contexte_infectieux default=""}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="narrow">Si oui,</th>
                      <td></td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infect_facteurs_risque_infect typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=infect_facteurs_risque_infect}}</strong></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_rpm_sup_12h typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_rpm_sup_12h}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_liquide_teinte typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_liquide_teinte}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_strepto_b typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_strepto_b}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_fievre_mat typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_fievre_mat}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_maternelle typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_maternelle}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_autre typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=infect_autre}}
                        {{mb_label object=$naissance field=infect_autre_desc style="display: none;"}}
                        {{mb_field object=$naissance field=infect_autre_desc typeEnum=checkbox}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infect_prelev_bacterio typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=infect_prelev_bacterio}}</strong></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_prelev_gatrique typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_prelev_gatrique}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_prelev_autre_periph typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_prelev_autre_periph}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_prelev_placenta typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_prelev_placenta}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=infect_prelev_sang typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=infect_prelev_sang}}</td>
                    </tr>
                    <tr>
                      <th rowspan="2">{{mb_field object=$naissance field=infect_antibio typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=infect_antibio}}</strong></td>
                    </tr>
                    <tr>
                      <td>
                        {{mb_label object=$naissance field=infect_antibio_desc style="display: none;"}}
                        {{mb_field object=$naissance field=infect_antibio_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=infect_remarques}}</th>
                      <td>{{mb_field object=$naissance field=infect_remarques}}</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
              <td class="thirdPane">
                <fieldset>
                  <legend>Rappel chez la mère</legend>
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=prelev_bacterio_mere typeEnum=checkbox}}</th>
                      <td colspan="2">{{mb_label object=$naissance field=prelev_bacterio_mere}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=prelev_bacterio_vaginal_mere typeEnum=checkbox}}</th>
                      <td class="compact narrow">{{mb_label object=$naissance field=prelev_bacterio_vaginal_mere}}</td>
                      <td class="compact">
                        {{mb_label object=$naissance field=prelev_bacterio_vaginal_mere_germe}}
                        {{mb_field object=$naissance field=prelev_bacterio_vaginal_mere_germe}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=prelev_bacterio_urinaire_mere typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=prelev_bacterio_urinaire_mere}}</td>
                      <td class="compact">
                        {{mb_label object=$naissance field=prelev_bacterio_urinaire_mere_germe}}
                        {{mb_field object=$naissance field=prelev_bacterio_urinaire_mere_germe}}
                      </td>
                    </tr>
                    <tr>
                      <th rowspan="2">{{mb_field object=$naissance field=antibiotherapie_antepart_mere typeEnum=checkbox}}</th>
                      <td colspan="2">{{mb_label object=$naissance field=antibiotherapie_antepart_mere}}</td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        {{mb_label object=$naissance field=antibiotherapie_antepart_mere_desc style="display: none;"}}
                        {{mb_field object=$naissance field=antibiotherapie_antepart_mere_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th rowspan="2">{{mb_field object=$naissance field=antibiotherapie_perpart_mere typeEnum=checkbox}}</th>
                      <td colspan="2">{{mb_label object=$naissance field=antibiotherapie_perpart_mere}}</td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        {{mb_label object=$naissance field=antibiotherapie_perpart_mere_desc style="display: none;"}}
                        {{mb_field object=$naissance field=antibiotherapie_perpart_mere_desc}}
                      </td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
          </table>
        </form>
      </div>
      <div id="mensurations" style="display: none;">

        {{assign var=constantes value=$naissance->_ref_nouveau_ne_constantes}}
        {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:'list_constantes'}}
        <form name="Nouveau-ne-constantes-{{$naissance->_guid}}" action="?" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$constantes}}
          {{mb_key   object=$constantes}}

          {{mb_field object=$constantes field=patient_id hidden=true}}
          {{mb_field object=$constantes field=context_class hidden=true}}
          {{mb_field object=$constantes field=context_id hidden=true}}
          {{mb_field object=$constantes field=datetime hidden=true}}
          {{mb_field object=$constantes field=user_id hidden=true}}

          <input type="hidden" name="_count_changes" value="0" />
          <input type="hidden" name="_object_guid" value="{{$naissance->_guid}}">
          <input type="hidden" name="_object_field" value="nouveau_ne_constantes_id">
          <table class="form">
            <tr>
              <th class="thirdPane">
                {{mb_label object=$constantes field=_poids_g}}
                <small class="opacity-50">(g)</small>
              </th>
              <td>{{mb_field object=$constantes field=_poids_g size=3}}</td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$constantes field=taille}}
                <small class="opacity-50">(cm)</small>
              </th>
              <td>{{mb_field object=$constantes field=taille size=3}}</td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$constantes field=perimetre_cranien}}
                <small class="opacity-50">(cm)</small>
              </th>
              <td>{{mb_field object=$constantes field=perimetre_cranien size=3}}</td>
            </tr>
          </table>
        </form>
      </div>
      <div id="sortie_salle" style="display: none;">
        <form name="Sortie-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <table class="form">
            <tr>
              <th class="thirdPane">{{mb_label object=$naissance field=mode_sortie}}</th>
              <td class="narrow">
                {{mb_field object=$naissance field=mode_sortie
                style="width: 20em;" emptyLabel="CNaissance.mode_sortie."}}
              </td>
              <td>
                {{mb_label object=$naissance field=mode_sortie_autre style="display: none;"}}
                {{mb_field object=$naissance field=mode_sortie_autre}}
              </td>
            </tr>
          </table>
          <fieldset>
            <legend>Si transfert ou mutation immédiate</legend>
            <table class="form">
              <tr>
                <th class="thirdPane">{{mb_label object=$naissance field=min_vie_transmut}}</th>
                <td colspan="2">{{mb_field object=$naissance field=min_vie_transmut}} min de vie</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=resp_transmut_id}}</th>
                <td colspan="2">
                  {{mb_field object=$naissance field=resp_transmut_id style="width: 20em;"
                  options=$praticiens}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=motif_transmut}}</th>
                <td class="narrow">
                  {{mb_field object=$naissance field=motif_transmut
                  style="width: 20em;" emptyLabel="CNaissance.motif_transmut."}}
                </td>
                <td>
                  {{mb_label object=$naissance field=detail_motif_transmut style="display: none;"}}
                  {{mb_field object=$naissance field=detail_motif_transmut}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=lieu_transf}}</th>
                <td colspan="2">{{mb_field object=$naissance field=lieu_transf}}</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=type_etab_transf}}</th>
                <td colspan="2">
                  {{mb_field object=$naissance field=type_etab_transf
                  style="width: 20em;" emptyLabel="CNaissance.type_etab_transf."}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=dest_transf}}</th>
                <td>
                  {{mb_field object=$naissance field=dest_transf
                  style="width: 20em;" emptyLabel="CNaissance.dest_transf."}}
                </td>
                <td>
                  {{mb_label object=$naissance field=dest_transf_autre style="display: none;"}}
                  {{mb_field object=$naissance field=dest_transf_autre}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=mode_transf}}</th>
                <td colspan="2">
                  {{mb_field object=$naissance field=mode_transf
                  style="width: 20em;" emptyLabel="CNaissance.mode_transf."}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=delai_appel_arrivee_transp}}</th>
                <td colspan="2">{{mb_field object=$naissance field=delai_appel_arrivee_transp}} min</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=dist_mater_transf}}</th>
                <td colspan="2">{{mb_field object=$naissance field=dist_mater_transf}} km</td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=raison_transf_report}}</th>
                <td>
                  {{mb_field object=$naissance field=raison_transf_report
                  style="width: 20em;" emptyLabel="CNaissance.raison_transf_report."}}
                </td>
                <td>
                  {{mb_label object=$naissance field=raison_transf_report_autre style="display: none;"}}
                  {{mb_field object=$naissance field=raison_transf_report_autre}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$naissance field=remarques_transf}}</th>
                <td colspan="2">{{mb_field object=$naissance field=remarques_transf}}</td>
              </tr>
            </table>
          </fieldset>
        </form>
      </div>
    </td>
  </tr>
</table>
