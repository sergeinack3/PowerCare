{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients  script=patient      ajax=1}}
{{mb_script module=maternite script=grossesse    ajax=1}}
{{mb_script module=maternite script=dossierMater ajax=1}}
{{mb_script module=maternite script=allaitement  ajax=1}}
{{mb_script module=patients  script=antecedent   ajax=1}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=pere    value=$grossesse->_ref_pere}}

<script>
  Main.add(function () {
    DossierMater.getMotherPathologies(getForm('edit_perinatal_folder'), '{{$dossier->_id}}');

    Grossesse.parturiente_id = '{{$patient->_id}}';
    Grossesse.light_view = '1';

    window.onbeforeunload = function() {
      return $T("CDossierPerinat-msg-Data will be lost if you leave the page, are you sure");
    };
  });
</script>

<form name="edit_grossesse_light" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$grossesse}}
  {{mb_key   object=$grossesse}}
  {{mb_field object=$grossesse field=date_dernieres_regles hidden=true}}
  {{mb_field object=$grossesse field=date_debut_grossesse  hidden=true}}
  {{mb_field object=$grossesse field=terme_prevu           hidden=true}}
  {{mb_field object=$grossesse field=multiple              hidden=true}}
  {{mb_field object=$grossesse field=nb_foetus             hidden=true}}
  {{mb_field object=$grossesse field=type_embryons_debut_grossesse hidden=true}}
  {{mb_field object=$grossesse field=nb_grossesses_ant             hidden=true}}
  {{mb_field object=$grossesse field=nb_accouchements_ant          hidden=true}}
</form>

<form name="edit_mother_pathologies" method="post" onsubmit="return onSubmitFormAjax(this, DossierMater.refreshMotherPathologies.curry('{{$dossier->_id}}'));">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}

  {{foreach from=$pathologies_fields key=patho_field item=patho_name}}
    {{mb_field object=$dossier field=$patho_field hidden=true onchange="this.form.onsubmit();"}}
  {{/foreach}}
</form>

<form name="edit_last_screenings" method="post" onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.close()});">
  {{mb_class object=$depistage}}
  {{mb_key   object=$depistage}}

  {{mb_field object=$depistage field=grossesse_id value=$grossesse->_id hidden=true}}
  {{mb_field object=$depistage field=date                               hidden=true}}
  {{mb_field object=$depistage field=rubeole                            hidden=true}}
  {{mb_field object=$depistage field=toxoplasmose                       hidden=true}}
  {{mb_field object=$depistage field=syphilis                           hidden=true}}
  {{mb_field object=$depistage field=vih                                hidden=true}}
  {{mb_field object=$depistage field=hepatite_b                         hidden=true}}
  {{mb_field object=$depistage field=hepatite_c                         hidden=true}}
  {{mb_field object=$depistage field=aci                                hidden=true}}
  {{mb_field object=$depistage field=test_kleihauer                     hidden=true}}
  {{mb_field object=$depistage field=varicelle                          hidden=true}}
  {{mb_field object=$depistage field=parvovirus                         hidden=true}}
  {{mb_field object=$depistage field=cmvg                               hidden=true}}
  {{mb_field object=$depistage field=cmvm                               hidden=true}}
  {{mb_field object=$depistage field=htlv                               hidden=true}}
  {{mb_field object=$depistage field=vrdl                               hidden=true}}
  {{mb_field object=$depistage field=TPHA                               hidden=true}}
  {{mb_field object=$depistage field=strepto_b                          hidden=true}}
  {{mb_field object=$depistage field=parasitobacteriologique            hidden=true}}
  {{mb_field object=$depistage field=groupe_sanguin                     hidden=true}}
  {{mb_field object=$depistage field=rai                                hidden=true}}
  {{mb_field object=$depistage field=rhesus                             hidden=true}}
  {{mb_field object=$depistage field=rhesus_bb                          hidden=true}}
  {{mb_field object=$depistage field=rques_immuno                       hidden=true}}
  {{mb_field object=$depistage field=genotypage                         hidden=true}}
  {{mb_field object=$depistage field=date_genotypage                    hidden=true}}
  {{mb_field object=$depistage field=rques_genotypage                   hidden=true}}
  {{mb_field object=$depistage field=rhophylac                          hidden=true}}
  {{mb_field object=$depistage field=date_rhophylac                     hidden=true}}
  {{mb_field object=$depistage field=quantite_rhophylac                 hidden=true}}
  {{mb_field object=$depistage field=rques_rhophylac                    hidden=true}}
  {{mb_field object=$depistage field=datetime_1_determination           hidden=true}}
  {{mb_field object=$depistage field=datetime_2_determination           hidden=true}}
</form>

<form name="edit_folder_light" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <!--current_pregnancy-->
  {{mb_field object=$dossier field=ant_obst_nb_gr_cesar       hidden=true}}
  {{mb_field object=$dossier field=souhait_grossesse          hidden=true}}
  {{mb_field object=$dossier field=grossesse_apres_traitement hidden=true}}
  {{mb_field object=$dossier field=type_traitement_grossesse  hidden=true}}
  {{mb_field object=$dossier field=rques_traitement_grossesse hidden=true}}
  <!--antecedents-->
  {{mb_field object=$dossier field=facteur_risque             hidden=true}}
  {{mb_field object=$dossier field=pathologie_grossesse       hidden=true}}
  <!--screenings-->
  {{mb_field object=$dossier field=resultat_prelevement_vaginal  hidden=true}}
  {{mb_field object=$dossier field=date_validation_synthese      hidden=true}}
  {{mb_field object=$dossier field=rques_prelevement_vaginal     hidden=true}}
  {{mb_field object=$dossier field=resultat_prelevement_urinaire hidden=true}}
  {{mb_field object=$dossier field=date_validation_synthese      hidden=true}}
  {{mb_field object=$dossier field=rques_prelevement_urinaire    hidden=true}}
  <!--toxics-->
  {{mb_field object=$dossier field=tabac_avant_grossesse        hidden=true}}
  {{mb_field object=$dossier field=tabac_debut_grossesse        hidden=true}}
  {{mb_field object=$dossier field=alcool_debut_grossesse       hidden=true}}
  {{mb_field object=$dossier field=canabis_debut_grossesse      hidden=true}}
  {{mb_field object=$dossier field=subst_avant_grossesse        hidden=true}}
  {{mb_field object=$dossier field=mode_subst_avant_grossesse   hidden=true}}
  {{mb_field object=$dossier field=nom_subst_avant_grossesse    hidden=true}}
  {{mb_field object=$dossier field=subst_subst_avant_grossesse  hidden=true}}
  {{mb_field object=$dossier field=subst_debut_grossesse        hidden=true}}
  {{mb_field object=$dossier field=mode_subst_avant_grossesse   hidden=true}}
  {{mb_field object=$dossier field=nom_subst_avant_grossesse    hidden=true}}
  {{mb_field object=$dossier field=subst_subst_avant_grossesse  hidden=true}}
  <!--ultrasounds-->
  {{mb_field object=$dossier field=resultat_echo_1er_trim           hidden=true}}
  {{mb_field object=$dossier field=resultat_autre_echo_1er_trim     hidden=true}}
  {{mb_field object=$dossier field=ag_echo_1er_trim                 hidden=true}}
  {{mb_field object=$dossier field=resultat_echo_2e_trim            hidden=true}}
  {{mb_field object=$dossier field=resultat_autre_echo_2e_trim      hidden=true}}
  {{mb_field object=$dossier field=ag_echo_2e_trim                  hidden=true}}
  {{mb_field object=$dossier field=resultat_echo_3e_trim            hidden=true}}
  {{mb_field object=$dossier field=resultat_autre_echo_3e_trim      hidden=true}}
  {{mb_field object=$dossier field=ag_echo_3e_trim                  hidden=true}}
  {{mb_field object=$dossier field=est_pond_fin_grossesse           hidden=true}}
  {{mb_field object=$dossier field=sa_echo_fin_grossesse            hidden=true}}
  {{mb_field object=$dossier field=est_pond_2e_foetus_fin_grossesse hidden=true}}
  <!--pelvimetries-->
  {{mb_field object=$dossier field=pelvimetrie                       hidden=true}}
  {{mb_field object=$dossier field=desc_pelvimetrie                  hidden=true}}
  {{mb_field object=$dossier field=diametre_transverse_median        hidden=true}}
  {{mb_field object=$dossier field=diametre_promonto_retro_pubien    hidden=true}}
  {{mb_field object=$dossier field=indice_magnin                     hidden=true}}
  {{mb_field object=$dossier field=diametre_bisciatique              hidden=true}}
  {{mb_field object=$dossier field=bip_fin_grossesse                 hidden=true}}
  {{mb_field object=$dossier field=date_echo_fin_grossesse           hidden=true}}
  {{mb_field object=$dossier field=appreciation_clinique_etat_bassin hidden=true}}
  <!--fetal_samples-->
  {{mb_field object=$dossier field=indication_prelevements_foetaux hidden=true}}
  {{mb_field object=$dossier field=biopsie_trophoblaste            hidden=true}}
  {{mb_field object=$dossier field=resultat_biopsie_trophoblaste   hidden=true}}
  {{mb_field object=$dossier field=rques_biopsie_trophoblaste      hidden=true}}
  {{mb_field object=$dossier field=amniocentese                    hidden=true}}
  {{mb_field object=$dossier field=resultat_amniocentese           hidden=true}}
  {{mb_field object=$dossier field=rques_amniocentese              hidden=true}}
  {{mb_field object=$dossier field=cordocentese                    hidden=true}}
  {{mb_field object=$dossier field=resultat_cordocentese           hidden=true}}
  {{mb_field object=$dossier field=rques_cordocentese              hidden=true}}
  <!--birth plan-->
  {{mb_field object=$dossier field=projet_analgesie_peridurale hidden=true}}
  {{mb_field object=$dossier field=projet_allaitement_maternel hidden=true}}
  {{mb_field object=$dossier field=motif_conduite_a_tenir_acc  hidden=true}}
  <!--general information-->
  {{mb_field object=$dossier field=activite_pro             hidden=true}}
  {{mb_field object=$dossier field=fatigue_travail          hidden=true}}
  {{mb_field object=$dossier field=situation_accompagnement hidden=true}}
  {{mb_field object=$dossier field=rques_accompagnement     hidden=true}}
  {{mb_field object=$dossier field=pere_ant_autre           hidden=true}}
</form>

<div class="container_main">
  <div id="perinatal_folder" onscroll="DossierMater.onScroll(this);">
    <table class="main layout">
      <tr id="header">
        <td>
          {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_header}}
        </td>
      </tr>
      <tr>
        <td id="edit_grossesse_folder">
          <form name="edit_perinatal_folder" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_field object=$grossesse field=grossesse_id       hidden=true}}
            {{mb_field object=$dossier   field=dossier_perinat_id hidden=true}}

            <div id="current_pregnancy" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_current_pregnancy}}
            </div>

            <div id="antecedents_traitements" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_antecedents_traitements}}
            </div>

            <div id="immuno_hemotology" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_immuno_hemotology}}
            </div>

            <div id="screenings" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_screenings}}
            </div>

            <div id="toxics" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_toxics}}
            </div>

            <div id="fetal_samples" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_fetal_samples}}
            </div>

            <div id="ultrasounds" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_ultrasounds}}
            </div>

            <div id="pelvimetry" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_pelvimetries}}
            </div>

            <div id="birth_plan" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_birth_plan}}
            </div>

            <div id="general_informations" class="container_card">
              {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_general_informations}}
            </div>
          </form>
        </td>
      </tr>
    </table>
  </div>
  <div class="container_menu" style="width: 25%;">
    {{mb_include module=maternite template=dossier_perinatal_light/inc_vw_menu}}
  </div>
</div>



