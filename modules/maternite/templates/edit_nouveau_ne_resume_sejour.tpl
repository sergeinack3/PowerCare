{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=sejour value=$naissance->_ref_sejour_enfant}}
{{assign var=enfant value=$sejour->_ref_patient}}

<script>
  listForms = [
    getForm("Pathologies-{{$naissance->_guid}}"),
    getForm("Actes-{{$naissance->_guid}}"),
    getForm("Prophylaxie-{{$naissance->_guid}}"),
    getForm("Sortie-enfant-{{$naissance->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
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
      <script>
        Main.add(function () {
          Control.Tabs.create('tab-resume-sejour', true);
        });
      </script>
      <ul id="tab-resume-sejour" class="control_tabs">
        <li><a href="#pathologies">Pathologies</a></li>
        <li><a href="#actes_effectues">Actes</a></li>
        <li><a href="#mesures_prophylactiques">Mesures Prophylactiques</a></li>
        <li><a href="#sortie_enfant">Sortie de l'enfant de la maternité</a></li>
      </ul>

      <div id="pathologies" style="display: none;">
        <form name="Pathologies-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <table class="main layout">
            <tr>
              <td colspan="2">
                <table class="form">
                  <tr>
                    <th class="halfPane"><strong>{{mb_label object=$naissance field=pathologies}}</strong></th>
                    <td><strong>{{mb_field object=$naissance field=pathologies default=""}}</strong></td>
                  </tr>
                  <tr>
                    <th>Si oui,</th>
                    <td></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td class="halfPane">
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=lesion_traumatique typeEnum=checkbox}}
                    {{mb_label object=$naissance field=lesion_traumatique}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=lesion_faciale typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=lesion_faciale}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=paralysie_faciale typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=paralysie_faciale}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=cephalhematome typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=cephalhematome}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=paralysie_plexus_brachial_sup typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=paralysie_plexus_brachial_sup}}</td>
                      <th>{{mb_field object=$naissance field=paralysie_plexus_brachial_inf typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=paralysie_plexus_brachial_inf}}</td>
                      <th>{{mb_field object=$naissance field=lesion_cuir_chevelu typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=lesion_cuir_chevelu}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=fracture_clavicule typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=fracture_clavicule}}</td>
                      <th>{{mb_field object=$naissance field=autre_lesion typeEnum=checkbox}}</th>
                      <td colspan="3">
                        {{mb_label object=$naissance field=autre_lesion}}
                        {{mb_label object=$naissance field=autre_lesion_desc style="display: none;"}}
                        {{mb_field object=$naissance field=autre_lesion_desc}}
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=infection typeEnum=checkbox}}
                    {{mb_label object=$naissance field=infection}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th colspan="2">{{mb_label object=$naissance field=infection_degre}}</th>
                      <td colspan="4">
                        {{mb_field object=$naissance field=infection_degre
                        style="width: 20em;" emptyLabel="CGrossesse.infection_degre."}}
                      </td>
                      <th colspan="2">{{mb_label object=$naissance field=infection_origine}}</th>
                      <td colspan="4">
                        {{mb_field object=$naissance field=infection_origine
                        style="width: 20em;" emptyLabel="CGrossesse.infection_origine."}}
                      </td>
                    </tr>
                    <tr>
                      <th class="category" colspan="12">Localisation</th>
                    </tr>
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=infection_sang typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_sang}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=infection_lcr typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_lcr}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=infection_poumon typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_poumon}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=infection_urines typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_urines}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=infection_digestif typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_digestif}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=infection_ombilic typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_ombilic}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_oeil typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_oeil}}</td>
                      <th>{{mb_field object=$naissance field=infection_os_articulations typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_os_articulations}}</td>
                      <th>{{mb_field object=$naissance field=infection_peau typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_peau}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre typeEnum=checkbox}}</th>
                      <td colspan="5">
                        {{mb_label object=$naissance field=infection_autre}}
                        {{mb_label object=$naissance field=infection_autre_desc style="display: none;"}}
                        {{mb_field object=$naissance field=infection_autre_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th class="category" colspan="12">Germe</th>
                    </tr>
                    <tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_strepto_b typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_strepto_b}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre_strepto typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_autre_strepto}}</td>
                      <th>{{mb_field object=$naissance field=infection_staphylo_dore typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_staphylo_dore}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre_staphylo typeEnum=checkbox}}</th>
                      <td colspan="5">{{mb_label object=$naissance field=infection_autre_staphylo}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_haemophilus typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_haemophilus}}</td>
                      <th>{{mb_field object=$naissance field=infection_listeria typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_listeria}}</td>
                      <th>{{mb_field object=$naissance field=infection_pneumocoque typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_pneumocoque}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre_gplus typeEnum=checkbox}}</th>
                      <td colspan="5">{{mb_label object=$naissance field=infection_autre_gplus}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_coli typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_coli}}</td>
                      <th>{{mb_field object=$naissance field=infection_proteus typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_proteus}}</td>
                      <th>{{mb_field object=$naissance field=infection_klebsiele typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_klebsiele}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre_gmoins typeEnum=checkbox}}</th>
                      <td colspan="5">{{mb_label object=$naissance field=infection_autre_gmoins}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_chlamydiae typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_chlamydiae}}</td>
                      <th>{{mb_field object=$naissance field=infection_mycoplasme typeEnum=checkbox}}</th>
                      <td colspan="9">{{mb_label object=$naissance field=infection_mycoplasme}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_candida typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_candida}}</td>
                      <th>{{mb_field object=$naissance field=infection_toxoplasme typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_toxoplasme}}</td>
                      <th>{{mb_field object=$naissance field=infection_autre_parasite typeEnum=checkbox}}</th>
                      <td colspan="7">{{mb_label object=$naissance field=infection_autre_parasite}}</td>
                    </tr>
                    <tr>
                      <th style="vertical-align: top;">{{mb_field object=$naissance field=infection_cmv typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_cmv}}</td>
                      <th style="vertical-align: top;">{{mb_field object=$naissance field=infection_rubeole typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_rubeole}}</td>
                      <th style="vertical-align: top;">{{mb_field object=$naissance field=infection_herpes typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_herpes}}</td>
                      <th style="vertical-align: top;">{{mb_field object=$naissance field=infection_varicelle typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_varicelle}}</td>
                      <th style="vertical-align: top;">{{mb_field object=$naissance field=infection_vih typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=infection_vih}}</td>
                      <th
                        style="vertical-align: top;">{{mb_field object=$naissance field=infection_autre_virus typeEnum=checkbox}}</th>
                      <td>
                        {{mb_label object=$naissance field=infection_autre_virus}}
                        <br />
                        {{mb_label object=$naissance field=infection_autre_virus_desc style="display: none;"}}
                        {{mb_field object=$naissance field=infection_autre_virus_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=infection_germe_non_trouve typeEnum=checkbox}}</th>
                      <td colspan="11">{{mb_label object=$naissance field=infection_germe_non_trouve}}</td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=ictere typeEnum=checkbox}}
                    {{mb_label object=$naissance field=ictere}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="category" colspan="4">Origine</th>
                    </tr>
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=ictere_prema typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=ictere_prema}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=ictere_intense_terme typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=ictere_intense_terme}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=ictere_allo_immun_abo typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=ictere_allo_immun_abo}}</td>
                      <th>{{mb_field object=$naissance field=ictere_allo_immun_rh typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=ictere_allo_immun_rh}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=ictere_allo_immun_autre typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=ictere_allo_immun_autre}}</td>
                      <th>{{mb_field object=$naissance field=ictere_autre_origine typeEnum=checkbox}}</th>
                      <td>
                        {{mb_label object=$naissance field=ictere_autre_origine}}
                        {{mb_label object=$naissance field=ictere_autre_origine_desc style="display: none;"}}
                        {{mb_field object=$naissance field=ictere_autre_origine_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th colspan="4" class="category halfPane">
                        {{mb_label object=$naissance field=ictere_phototherapie}}
                        {{mb_field object=$naissance field=ictere_phototherapie default=""}}
                      </th>
                    </tr>
                    <tr>
                      <th colspan="2">Si oui, {{mb_label object=$naissance field=ictere_type_phototherapie}}</th>
                      <td colspan="2">
                        {{mb_field object=$naissance field=ictere_type_phototherapie
                        style="width: 20em;" emptyLabel="CGrossesse.ictere_type_phototherapie."}}
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=trouble_regul_thermique typeEnum=checkbox}}
                    {{mb_label object=$naissance field=trouble_regul_thermique}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=hyperthermie typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=hyperthermie}}</td>
                      <th class="narrow">{{mb_field object=$naissance field=hypothermie_grave typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=hypothermie_grave}}</td>
                    </tr>
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=hypothermie_legere typeEnum=checkbox}}</th>
                      <td colspan="3">{{mb_label object=$naissance field=hypothermie_legere}}</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
              <td>
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=anom_cong typeEnum=checkbox}}
                    {{mb_label object=$naissance field=anom_cong}}
                  </legend>
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=anom_cong_isolee typeEnum=checkbox}}</th>
                      <td class="thirdPane"><strong>{{mb_label object=$naissance field=anom_cong_isolee}}</strong></td>
                      <th class="narrow">{{mb_field object=$naissance field=anom_cong_synd_polyformatif typeEnum=checkbox}}</th>
                      <td class="thirdPane"><strong>{{mb_label object=$naissance field=anom_cong_synd_polyformatif}}</strong></td>
                      <td class="thirdPane">{{mb_label object=$naissance field=anom_cong_description_clair}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_tube_neural typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=anom_cong_tube_neural}}</td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_fente_labio_palatine typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=anom_cong_fente_labio_palatine}}</td>
                      <td rowspan="20">{{mb_field object=$naissance field=anom_cong_description_clair}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_atresie_oesophage typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=anom_cong_atresie_oesophage}}</td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_omphalocele typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=anom_cong_omphalocele}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_reduc_absence_membres typeEnum=checkbox}}</th>
                      <td class="compact text">{{mb_label object=$naissance field=anom_cong_reduc_absence_membres}}</td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_hydrocephalie typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_hydrocephalie}}
                        {{mb_label object=$naissance field=anom_cong_hydrocephalie_type style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_hydrocephalie_type
                        style="width: 10em;" emptyLabel="CGrossesse.anom_cong_hydrocephalie_type."}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_malform_card typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_malform_card}}
                        {{mb_label object=$naissance field=anom_cong_malform_card_type style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_malform_card_type
                        style="width: 10em;" emptyLabel="CGrossesse.anom_cong_malform_card_type."}}
                      </td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_malform_reinale typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_malform_reinale}}
                        {{mb_label object=$naissance field=anom_cong_malform_reinale_type style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_malform_reinale_type
                        style="width: 10em;" emptyLabel="CGrossesse.anom_cong_malform_reinale_type."}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_hanches_luxables typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_hanches_luxables}}
                        {{mb_label object=$naissance field=anom_cong_hanches_luxables_type style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_hanches_luxables_type
                        style="width: 10em;" emptyLabel="CGrossesse.anom_cong_hanches_luxables_type."}}
                      </td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_autre typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_autre}}
                        {{mb_label object=$naissance field=anom_cong_autre_desc style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_autre_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=anom_cong_chromosomique typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=anom_cong_chromosomique}}</strong></td>
                      <th class="narrow">{{mb_field object=$naissance field=anom_cong_genique typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=anom_cong_genique}}</strong></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_trisomie_21 typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_trisomie_21}}
                        {{mb_label object=$naissance field=anom_cong_trisomie_type style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_trisomie_type
                        style="width: 10em;" emptyLabel="CGrossesse.anom_cong_trisomie_type."}}
                      </td>
                      <th class="compact">{{mb_field object=$naissance field=anom_cong_chrom_gen_autre typeEnum=checkbox}}</th>
                      <td class="compact">
                        {{mb_label object=$naissance field=anom_cong_chrom_gen_autre}}
                        {{mb_label object=$naissance field=anom_cong_chrom_gen_autre_desc style="display: none;"}}
                        {{mb_field object=$naissance field=anom_cong_chrom_gen_autre_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th colspan="2">{{mb_label object=$naissance field=anom_cong_moment_diag}}</th>
                      <td colspan="2">
                        {{mb_field object=$naissance field=anom_cong_moment_diag
                        style="width: 20em;" emptyLabel="CGrossesse.anom_cong_moment_diag."}}
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>
                    {{mb_field object=$naissance field=autre_pathologie typeEnum=checkbox}}
                    {{mb_label object=$naissance field=autre_pathologie}}
                  </legend>
                  <table class="main layout">
                    <tr>
                      <td class="thirdPane">
                        <table class="form">
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_resp typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_resp}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=tachypnee typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=tachypnee}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=autre_detresse_resp_neonat typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=autre_detresse_resp_neonat}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=acces_cyanose typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=acces_cyanose}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=apnees_prema typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=apnees_prema}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=apnees_autre typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=apnees_autre}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=inhalation_meco_sans_pneumopath typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=inhalation_meco_sans_pneumopath}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=inhalation_meco_avec_pneumopath typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=inhalation_meco_avec_pneumopath}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=inhalation_lait typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=inhalation_lait}}</td>
                          </tr>
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_cardiovasc typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_cardiovasc}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=trouble_du_rythme typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=trouble_du_rythme}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hypertonie_vagale typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypertonie_vagale}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=souffle_a_explorer typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=souffle_a_explorer}}</td>
                          </tr>
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_neuro typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_neuro}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hypothonie typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypothonie}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hypertonie typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypertonie}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=irrit_cerebrale typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=irrit_cerebrale}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=mouv_anormaux typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=mouv_anormaux}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=convulsions typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=convulsions}}</td>
                          </tr>
                        </table>
                      </td>
                      <td class="thirdPane">
                        <table class="form">
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_dig typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_dig}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=alim_sein_difficile typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=alim_sein_difficile}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=alim_lente typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=alim_lente}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=stagnation_pond typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=stagnation_pond}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=perte_poids_sup_10_pourc typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=perte_poids_sup_10_pourc}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=regurgitations typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=regurgitations}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=vomissements typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=vomissements}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=reflux_gatro_eoso typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=reflux_gatro_eoso}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=oesophagite typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=oesophagite}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hematemese typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hematemese}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=synd_occlusif typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=synd_occlusif}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=trouble_succion_deglut typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=trouble_succion_deglut}}</td>
                          </tr>
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_hemato typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_hemato}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=anemie_neonat typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=anemie_neonat}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=anemie_transf_foeto_mat typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=anemie_transf_foeto_mat}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=anemie_transf_foeto_foet typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=anemie_transf_foeto_foet}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=drepano_positif typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=drepano_positif}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=maladie_hemo typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=maladie_hemo}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=thrombopenie typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=thrombopenie}}</td>
                          </tr>
                        </table>
                      </td>
                      <td class="thirdPane">
                        <table class="form">
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=patho_metab typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=patho_metab}}</strong></td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=hypogly_diab_mere_gest typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypogly_diab_mere_gest}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=hypogly_diab_mere_nid typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypogly_diab_mere_nid}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hypogly_diab_mere_id typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypogly_diab_mere_id}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=hypogly_neonat_transitoire typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypogly_neonat_transitoire}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=hypocalcemie typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=hypocalcemie}}</td>
                          </tr>
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=intoxication typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=intoxication}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=synd_sevrage_toxico typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=synd_sevrage_toxico}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=synd_sevrage_medic typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=synd_sevrage_medic}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=tabac_maternel typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=tabac_maternel}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=alcool_maternel typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=alcool_maternel}}</td>
                          </tr>
                          <tr>
                            <th class="narrow">{{mb_field object=$naissance field=autre_patho_autre typeEnum=checkbox}}</th>
                            <td class="text"><strong>{{mb_label object=$naissance field=autre_patho_autre}}</strong></td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=rhinite_neonat typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=rhinite_neonat}}</td>
                          </tr>
                          <tr>
                            <th class="narrow compact">{{mb_field object=$naissance field=patho_dermato typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=patho_dermato}}</td>
                          </tr>
                          <tr>
                            <th
                              class="narrow compact">{{mb_field object=$naissance field=autre_atho_autre_thesaurus typeEnum=checkbox}}</th>
                            <td class="compact text">{{mb_label object=$naissance field=autre_atho_autre_thesaurus}}</td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
          </table>
        </form>
      </div>
      <div id="actes_effectues" style="display: none;">
        <form name="Actes-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <fieldset>
            <legend>
              {{mb_label object=$naissance field=actes_effectues}}
              {{mb_field object=$naissance field=actes_effectues default=""}}
            </legend>
            <table class="form">
              <tr>
                <th colspan="3">Si oui,</th>
                <td colspan="3"></td>
              </tr>
              <tr>
                <th class="narrow">{{mb_field object=$naissance field=caryotype typeEnum=checkbox}}</th>
                <td class="thirdPane">{{mb_label object=$naissance field=caryotype}}</td>
                <th class="narrow">{{mb_field object=$naissance field=echographie_cardiaque typeEnum=checkbox}}</th>
                <td class="thirdPane">{{mb_label object=$naissance field=echographie_cardiaque}}</td>
                <th class="narrow">{{mb_field object=$naissance field=incubateur typeEnum=checkbox}}</th>
                <td class="thirdPane">{{mb_label object=$naissance field=incubateur}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=etf typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=etf}}</td>
                <th>{{mb_field object=$naissance field=echographie_cerebrale typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=echographie_cerebrale}}</td>
                <th>{{mb_field object=$naissance field=injection_gamma_globulines typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=injection_gamma_globulines}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=eeg typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=eeg}}</td>
                <th>{{mb_field object=$naissance field=echographie_hanche typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=echographie_hanche}}</td>
                <th>{{mb_field object=$naissance field=togd typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=togd}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=ecg typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=ecg}}</td>
                <th>{{mb_field object=$naissance field=echographie_hepatique typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=echographie_hepatique}}</td>
                <th>{{mb_field object=$naissance field=radio_thoracique typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=radio_thoracique}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=fond_oeil typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=fond_oeil}}</td>
                <th>{{mb_field object=$naissance field=echographie_reinale typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=echographie_reinale}}</td>
                <th>{{mb_field object=$naissance field=reeducation typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=reeducation}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=antibiotherapie typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=antibiotherapie}}</td>
                <th>{{mb_field object=$naissance field=exsanguino_transfusion typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=exsanguino_transfusion}}</td>
                <th rowspan="2" style="vertical-align: top;">{{mb_field object=$naissance field=autre_acte typeEnum=checkbox}}</th>
                <td rowspan="2">
                  {{mb_label object=$naissance field=autre_acte}}
                  <br />
                  {{mb_label object=$naissance field=autre_acte_desc style="display: none;"}}
                  {{mb_field object=$naissance field=autre_acte_desc}}
                </td>
              </tr>
              <tr>
                <th>{{mb_field object=$naissance field=oxygenotherapie typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=oxygenotherapie}}</td>
                <th>{{mb_field object=$naissance field=intubation typeEnum=checkbox}}</th>
                <td>{{mb_label object=$naissance field=intubation}}</td>
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
          <fieldset>
            <legend>
              {{mb_label object=$naissance field=mesures_prophylactiques}}
              {{mb_field object=$naissance field=mesures_prophylactiques default=""}}
            </legend>
            <table class="main layout">
              <tr>
                <td colspan="3" class="button">Si oui,</td>
              </tr>
              <tr>
                <td class="thirdPane">
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=hep_b_injection_immunoglob typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=hep_b_injection_immunoglob}}</strong></td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=vaccinations typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=vaccinations}}</strong></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=vacc_hep_b typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=vacc_hep_b}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=vacc_hepp_bcg typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=vacc_hepp_bcg}}</td>
                    </tr>
                  </table>
                </td>
                <td class="thirdPane">
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=depistage_sanguin typeEnum=checkbox}}</th>
                      <td><strong>{{mb_label object=$naissance field=depistage_sanguin}}</strong></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=hyperphenylalanemie typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=hyperphenylalanemie}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=hypothyroidie typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=hypothyroidie}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=hyperplasie_cong_surrenales typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=hyperplasie_cong_surrenales}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=drepanocytose typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=drepanocytose}}</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_field object=$naissance field=mucoviscidose typeEnum=checkbox}}</th>
                      <td class="compact">{{mb_label object=$naissance field=mucoviscidose}}</td>
                    </tr>
                  </table>
                </td>
                <td class="thirdPane">
                  <table class="form">
                    <tr>
                      <th class="narrow">{{mb_field object=$naissance field=test_audition typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=test_audition}}</td>
                      <td>
                        {{mb_label object=$naissance field=etat_test_audition style="display: none;"}}
                        {{mb_field object=$naissance field=etat_test_audition
                        style="width: 20em;" emptyLabel="CGrossesse.etat_test_audition."}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=supp_vitaminique typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=supp_vitaminique}}</td>
                      <td>
                        {{mb_label object=$naissance field=supp_vitaminique_desc style="display: none;"}}
                        {{mb_field object=$naissance field=supp_vitaminique_desc style="width: 20em;"}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_field object=$naissance field=autre_mesure_proph typeEnum=checkbox}}</th>
                      <td>{{mb_label object=$naissance field=autre_mesure_proph}}</td>
                      <td>
                        {{mb_label object=$naissance field=autre_mesure_proph_desc style="display: none;"}}
                        {{mb_field object=$naissance field=autre_mesure_proph_desc style="width: 20em;"}}
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </fieldset>
        </form>
      </div>
      <div id="sortie_enfant" style="display: none;">
        <form name="Sortie-enfant-{{$naissance->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$naissance}}
          {{mb_key   object=$naissance}}
          <input type="hidden" name="_count_changes" value="0" />
          <table class="main layout">
            <tr>
              <td class="halfPane">
                <fieldset>
                  <legend>Sortie</legend>
                  <table class="main layout">
                    <tr>
                      <td id="dossier_mater_infos_sortie">
                        {{mb_include module=maternite template=inc_dossier_mater_infos_sortie}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <table class="form">
                          <tr>
                            <th class="halfPane">{{mb_label object=$naissance field=mode_sortie_mater}}</th>
                            <td>
                              {{mb_field object=$naissance field=mode_sortie_mater
                              style="width: 20em;" emptyLabel="CGrossesse.mode_sortie_mater."}}
                              <br />
                              {{mb_label object=$naissance field=mode_sortie_mater_autre style="display: none;"}}
                              {{mb_field object=$naissance field=mode_sortie_mater_autre style="width: 20em;"}}
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>Si transfert ou mutation différée</legend>
                  <table class="form">
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=jour_vie_transmut_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=jour_vie_transmut_mater}} j
                        {{mb_label object=$naissance field=heure_vie_transmut_mater style="display: none;"}}
                        {{mb_field object=$naissance field=heure_vie_transmut_mater}} h de vie
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=resp_transmut_mater_id}}</th>
                      <td>
                        {{mb_field object=$naissance field=resp_transmut_mater_id style="width: 20em;"
                        options=$praticiens}}
                      </td>
                    </tr>
                    <tr>
                      <th rowspan="2" style="vertical-align: top;">{{mb_label object=$naissance field=motif_transmut_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=motif_transmut_mater
                        style="width: 20em;" emptyLabel="CNaissance.motif_transmut_mater."}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        {{mb_label object=$naissance field=detail_motif_transmut_mater style="display: none;"}}
                        {{mb_field object=$naissance field=detail_motif_transmut_mater}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=lieu_transf_mater}}</th>
                      <td>{{mb_field object=$naissance field=lieu_transf_mater}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=type_etab_transf_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=type_etab_transf_mater
                        style="width: 20em;" emptyLabel="CNaissance.type_etab_transf_mater."}}
                      </td>
                    </tr>
                    <tr>
                      <th rowspan="2" style="vertical-align: top;">{{mb_label object=$naissance field=dest_transf_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=dest_transf_mater
                        style="width: 20em;" emptyLabel="CNaissance.dest_transf_mater."}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        {{mb_label object=$naissance field=dest_transf_mater_autre style="display: none;"}}
                        {{mb_field object=$naissance field=dest_transf_mater_autre}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=mode_transf_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=mode_transf_mater
                        style="width: 20em;" emptyLabel="CNaissance.mode_transf_mater."}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=delai_appel_arrivee_transp_mater}}</th>
                      <td>{{mb_field object=$naissance field=delai_appel_arrivee_transp_mater}} min</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=dist_mater_transf_mater}}</th>
                      <td>{{mb_field object=$naissance field=dist_mater_transf_mater}} km</td>
                    </tr>
                    <tr>
                      <th rowspan="2" style="vertical-align: top;">{{mb_label object=$naissance field=raison_transf_mater_report}}</th>
                      <td>
                        {{mb_field object=$naissance field=raison_transf_mater_report
                        style="width: 20em;" emptyLabel="CNaissance.raison_transf_mater_report."}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        {{mb_label object=$naissance field=raison_transf_report_mater_autre style="display: none;"}}
                        {{mb_field object=$naissance field=raison_transf_report_mater_autre}}
                      </td>
                    </tr>
                    <tr>
                      <th rowspan="2" style="vertical-align: top;">{{mb_label object=$naissance field=surv_part_sortie_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=surv_part_sortie_mater
                        style="width: 20em;" emptyLabel="CNaissance.surv_part_sortie_mater."}}
                      </td>
                    </tr>
                    <tr>
                      <td>
                        {{mb_label object=$naissance field=surv_part_sortie_mater_desc style="display: none;"}}
                        {{mb_field object=$naissance field=surv_part_sortie_mater_desc}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=remarques_transf_mater}}</th>
                      <td>{{mb_field object=$naissance field=remarques_transf_mater}}</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
              <td class="halfPane">
                <fieldset>
                  <legend>Alimentation</legend>
                  <table class="form">
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=poids_fin_sejour}}</th>
                      <td>{{mb_field object=$naissance field=poids_fin_sejour}} grammes</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=alim_fin_sejour}}</th>
                      <td>
                        {{mb_field object=$naissance field=alim_fin_sejour
                        style="width: 20em;" emptyLabel="CNaissance.alim_fin_sejour."}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=comp_alim_fin_sejour}}</th>
                      <td>{{mb_field object=$naissance field=comp_alim_fin_sejour default=""}}</td>
                    </tr>
                    <tr>
                      <th>Si oui,</th>
                      <td></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=nature_comp_alim_fin_sejour}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=nature_comp_alim_fin_sejour
                        style="width: 20em;" emptyLabel="CNaissance.nature_comp_alim_fin_sejour."}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=moyen_comp_alim_fin_sejour}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=moyen_comp_alim_fin_sejour
                        style="width: 20em;" emptyLabel="CNaissance.moyen_comp_alim_fin_sejour."}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=indic_comp_alim_fin_sejour}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=indic_comp_alim_fin_sejour
                        style="width: 20em;" emptyLabel="CNaissance.indic_comp_alim_fin_sejour."}}
                        <br />
                        {{mb_label object=$naissance field=indic_comp_alim_fin_sejour_desc style="display: none;"}}
                        {{mb_field object=$naissance field=indic_comp_alim_fin_sejour_desc}}
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>
                    {{mb_label object=$naissance field=retour_mater}}
                    {{mb_field object=$naissance field=retour_mater default=""}}
                  </legend>
                  <table class="form">
                    <tr>
                      <td colspan="2">(si transfert ou mutation immédait ou différé)</td>
                    </tr>
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=date_retour_mater}}</th>
                      <td>
                        {{mb_field object=$naissance field=date_retour_mater form="Sortie-enfant-`$naissance->_guid`" register=true}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$naissance field=duree_transfert}}</th>
                      <td>{{mb_field object=$naissance field=duree_transfert}}</td>
                    </tr>
                  </table>
                </fieldset>
                <fieldset>
                  <legend>Décès (synthèse)</legend>
                  <table class="form">
                    <tr>
                      <th class="halfPane">{{mb_label object=$naissance field=moment_deces}}</th>
                      <td>
                        {{mb_field object=$naissance field=moment_deces
                        style="width: 20em;" emptyLabel="CNaissance.moment_deces."}}
                      </td>
                    </tr>
                    <tr>
                      <th>Si oui,</th>
                      <td></td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=date_deces}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=date_deces form="Sortie-enfant-`$naissance->_guid`" register=true}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=age_deces_jours}}</th>
                      <td class="compact">{{mb_field object=$naissance field=age_deces_jours}} jours</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=age_deces_heures}}</th>
                      <td class="compact">{{mb_field object=$naissance field=age_deces_heures}} heures</td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=cause_deces}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=cause_deces
                        style="width: 20em;" emptyLabel="CNaissance.cause_deces."}}
                        <br />
                        {{mb_label object=$naissance field=cause_deces_desc style="display: none;"}}
                        {{mb_field object=$naissance field=cause_deces_desc style="width: 20em;"}}
                      </td>
                    </tr>
                    <tr>
                      <th class="compact">{{mb_label object=$naissance field=autopsie}}</th>
                      <td class="compact">
                        {{mb_field object=$naissance field=autopsie
                        style="width: 20em;" emptyLabel="CNaissance.autopsie."}}</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </td>
  </tr>
</table>