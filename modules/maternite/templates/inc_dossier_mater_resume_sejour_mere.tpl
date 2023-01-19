{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("Resume-sejour-mere-pathologies-{{$dossier->_guid}}"),
    getForm("Resume-sejour-mere-traitements-{{$dossier->_guid}}"),
    getForm("Resume-sejour-mere-sortie-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="Resume-sejour-mere-pathologies-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset class="me-small">
          <legend class="me-small-input">
            {{mb_label object=$dossier field=pathologies_suite_couches}}
            {{mb_field object=$dossier field=pathologies_suite_couches default=""}}
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="narrow" rowspan="3" style="vertical-align: top;">
                {{mb_field object=$dossier field=infection_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=infection_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_label object=$dossier field=infection_nosoc_suite_couches}}</th>
              <td colspan="2" class="greedyPane">{{mb_field object=$dossier field=infection_nosoc_suite_couches default=""}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=localisation_infection_suite_couches}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=localisation_infection_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.localisation_infection_suite_couches."}}
              </td>
            </tr>
            <tr>
              <th rowspan="2" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_perineales_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_perineales_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=details_compl_perineales_suite_couches}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=details_compl_perineales_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.details_compl_perineales_suite_couches."}}
              </td>
            </tr>
            <tr>
              <th rowspan="2" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_parietales_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_parietales_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=detail_compl_parietales_suite_couches}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=detail_compl_parietales_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.detail_compl_parietales_suite_couches."}}
              </td>
            </tr>
            <tr>
              <th rowspan="2" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_allaitement_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_allaitement_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=details_compl_allaitement_suite_couches}}</th>
              <td class="narrow">
                {{mb_field object=$dossier field=details_compl_allaitement_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.details_compl_allaitement_suite_couches."}}
              </td>
              <td class="greedyPane">
                {{mb_label object=$dossier field=details_comp_compl_allaitement_suite_couches style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-details_comp_compl_allaitement_suite_couches"}}
                {{mb_field object=$dossier field=details_comp_compl_allaitement_suite_couches
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th rowspan="2" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_thrombo_embo_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_thrombo_embo_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=detail_compl_thrombo_embo_suite_couches}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=detail_compl_thrombo_embo_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.detail_compl_thrombo_embo_suite_couches."}}
              </td>
            </tr>
            <tr>
              <th rowspan="2" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_autre_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_autre_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <td colspan="3">
                <table class="main layout">
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=anemie_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=anemie_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=hemorragie_second_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=hemorragie_second_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=eclampsie_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=eclampsie_suite_couches}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=incont_urin_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=incont_urin_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow"><span class="compact">
                        {{mb_field object=$dossier field=retention_urinaire_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=retention_urinaire_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=insuf_reinale_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=insuf_reinale_suite_couches}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=depression_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=depression_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=psychose_puerpuerale_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=psychose_puerpuerale_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=disjonction_symph_pub_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=disjonction_symph_pub_suite_couches}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=fract_obst_coccyx_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="thirdPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=fract_obst_coccyx_suite_couches}}
                      </span>
                    </td>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=autre_comp_suite_couches typeEnum=checkbox}}
                      </span>
                    </th>
                    <td colspan="3">
                      <span class="compact">
                        {{mb_label object=$dossier field=autre_comp_suite_couches}}
                        {{mb_label object=$dossier field=desc_autre_comp_suite_couches style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_comp_suite_couches"}}
                        {{mb_field object=$dossier field=desc_autre_comp_suite_couches
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <th rowspan="3" style="vertical-align: top;">
                {{mb_field object=$dossier field=compl_anesth_suite_couches typeEnum=checkbox}}
              </th>
              <td colspan="3">
                <strong>{{mb_label object=$dossier field=compl_anesth_suite_couches}}</strong>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=compl_anesth_generale_suite_couches}}</th>
              <td>
                {{mb_field object=$dossier field=compl_anesth_generale_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.compl_anesth_generale_suite_couches."}}
              </td>
              <td>
                {{mb_label object=$dossier field=autre_compl_anesth_generale_suite_couches style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_compl_anesth_generale_suite_couches"}}
                {{mb_field object=$dossier field=autre_compl_anesth_generale_suite_couches
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=compl_anesth_locoregion_suite_couches}}</th>
              <td>
                {{mb_field object=$dossier field=compl_anesth_locoregion_suite_couches
                style="width: 20em;" emptyLabel="CGrossesse.compl_anesth_locoregion_suite_couches."}}
              </td>
              <td>
                {{mb_label object=$dossier field=autre_compl_anesth_locoregion_suite_couches style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_compl_anesth_locoregion_suite_couches"}}
                {{mb_field object=$dossier field=autre_compl_anesth_locoregion_suite_couches
                style="width: 20em;" placeholder=$placeholder}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    <td class="halfPane">
      <form name="Resume-sejour-mere-traitements-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset class="me-small">
          <legend class="me-small-input">
            {{mb_label object=$dossier field=traitements_sejour_mere}}
            {{mb_field object=$dossier field=traitements_sejour_mere default=""}}
          </legend>
          <table class="main layout">
            <tr>
              <td class="halfPane">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th rowspan="4" style="vertical-align: top;">
                      {{mb_field object=$dossier field=ttt_preventif_sejour_mere typeEnum=checkbox}}
                    </th>
                    <td colspan="3">
                      <strong>{{mb_label object=$dossier field=ttt_preventif_sejour_mere}}</strong>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=antibio_preventif_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="narrow">
                      <span class="compact">
                        {{mb_label object=$dossier field=antibio_preventif_sejour_mere}}
                      </span>
                    </td>
                    <td class="greedyPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=desc_antibio_preventif_sejour_mere style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_antibio_preventif_sejour_mere"}}
                        {{mb_field object=$dossier field=desc_antibio_preventif_sejour_mere
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=anticoag_preventif_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="narrow">
                      <span class="compact">
                        {{mb_label object=$dossier field=anticoag_preventif_sejour_mere}}
                      </span>
                    </td>
                    <td class="greedyPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=desc_anticoag_preventif_sejour_mere style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_anticoag_preventif_sejour_mere"}}
                        {{mb_field object=$dossier field=desc_anticoag_preventif_sejour_mere
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=antilactation_preventif_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td colspan="2">
                      <span class="compact">
                        {{mb_label object=$dossier field=antilactation_preventif_sejour_mere}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th rowspan="3" style="vertical-align: top;">
                      {{mb_field object=$dossier field=ttt_curatif_sejour_mere typeEnum=checkbox}}
                    </th>
                    <td colspan="3">
                      <strong>{{mb_label object=$dossier field=ttt_curatif_sejour_mere}}</strong>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=antibio_curatif_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="narrow">
                      <span class="compact">
                        {{mb_label object=$dossier field=antibio_curatif_sejour_mere}}
                      </span>
                    </td>
                    <td class="greedyPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=desc_antibio_curatif_sejour_mere style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_antibio_curatif_sejour_mere"}}
                        {{mb_field object=$dossier field=desc_antibio_curatif_sejour_mere
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=anticoag_curatif_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td class="narrow">
                      <span class="compact">
                        {{mb_label object=$dossier field=anticoag_curatif_sejour_mere}}
                      </span>
                    </td>
                    <td class="greedyPane">
                      <span class="compact">
                        {{mb_label object=$dossier field=desc_anticoag_curatif_sejour_mere style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_anticoag_curatif_sejour_mere"}}
                        {{mb_field object=$dossier field=desc_anticoag_curatif_sejour_mere
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
              <td class="halfPane">
                <table class="form">
                  <tr>
                    <th rowspan="3" style="vertical-align: top;">
                      {{mb_field object=$dossier field=vacc_gammaglob_sejour_mere typeEnum=checkbox}}
                    </th>
                    <td colspan="3">
                      <strong>{{mb_label object=$dossier field=vacc_gammaglob_sejour_mere}}</strong>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=gammaglob_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td colspan="2">
                      <span class="compact">
                        {{mb_label object=$dossier field=gammaglob_sejour_mere}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">
                      <span class="compact">
                        {{mb_field object=$dossier field=vacc_sejour_mere typeEnum=checkbox}}
                      </span>
                    </th>
                    <td colspan="2">
                      <span class="compact">
                        {{mb_label object=$dossier field=vacc_sejour_mere}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <th rowspan="2" style="vertical-align: top;">
                      {{mb_field object=$dossier field=transfusion_sejour_mere typeEnum=checkbox}}
                    </th>
                    <td colspan="3">
                      <strong>{{mb_label object=$dossier field=transfusion_sejour_mere}}</strong>
                    </td>
                  </tr>
                  <tr>
                    <th colspan="2" class="narrow">
                      <span class="compact">
                        Si oui, {{mb_label object=$dossier field=nb_unite_transfusion_sejour_mere}}
                      </span>
                    </th>
                    <td class="greedyPane">
                      <span class="compact">
                        {{mb_field object=$dossier field=nb_unite_transfusion_sejour_mere}}
                      </span>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset class="me-small">
          <legend class="me-small-input">
            {{mb_label object=$dossier field=interv_sejour_mere}}
            {{mb_field object=$dossier field=interv_sejour_mere default=""}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th>{{mb_label object=$dossier field=datetime_interv_sejour_mere}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=datetime_interv_sejour_mere
                form="Resume-sejour-mere-traitements-`$dossier->_guid`" register=true}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=revision_uterine_sejour_mere typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=revision_uterine_sejour_mere}}</td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=interv_second_hemorr_sejour_mere typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=interv_second_hemorr_sejour_mere}}</td>
              <td class="greedyPane">
                {{mb_label object=$dossier field=type_interv_second_hemorr_sejour_mere style="display:none"}}
                {{mb_field object=$dossier field=type_interv_second_hemorr_sejour_mere
                style="width: 20em;" emptyLabel="CGrossesse.type_interv_second_hemorr_sejour_mere."}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=autre_interv_sejour_mere typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=autre_interv_sejour_mere}}</td>
              <td class="greedyPane">
                {{mb_label object=$dossier field=type_autre_interv_sejour_mere style="display:none"}}
                {{mb_field object=$dossier field=type_autre_interv_sejour_mere
                style="width: 20em;" emptyLabel="CGrossesse.type_autre_interv_sejour_mere."}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <fieldset class="me-small">
        <legend>Sortie</legend>
        <table class="main layout">
          <tr>
            <td class="halfPane" id="dossier_mater_infos_sortie">
              {{mb_include module=maternite template=inc_dossier_mater_infos_sortie}}
            </td>
            <td class="halfPane">
              <form name="Resume-sejour-mere-sortie-{{$dossier->_guid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this);">
                {{mb_class object=$dossier}}
                {{mb_key   object=$dossier}}
                <input type="hidden" name="_count_changes" value="0" />
                <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="category" colspan="2">Si décès</th>
                  </tr>
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=jour_deces_sejour_mere}}</th>
                    <td>{{mb_field object=$dossier field=jour_deces_sejour_mere}}ème jour</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=deces_cause_obst_sejour_mere}}</th>
                    <td>{{mb_field object=$dossier field=deces_cause_obst_sejour_mere default=""}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=autopsie_sejour_mere}}</th>
                    <td>
                      {{mb_field object=$dossier field=autopsie_sejour_mere
                      style="width: 20em;" emptyLabel="CGrossesse.autopsie_sejour_mere."}}
                    </td>
                  </tr>
                  <tr>
                    <th rowspan="2" style="vertical-align: top;">
                      <span class="compact">
                        Si faite, {{mb_label object=$dossier field=resultat_autopsie_sejour_mere}}
                      </span>
                    </th>
                    <td>
                      <span class="compact">
                      {{mb_field object=$dossier field=resultat_autopsie_sejour_mere
                      style="width: 20em;" emptyLabel="CGrossesse.resultat_autopsie_sejour_mere."}}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <span class="compact">
                        {{mb_label object=$dossier field=anomalie_autopsie_sejour_mere style="display:none"}}
                        {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-anomalie_autopsie_sejour_mere"}}
                        {{mb_field object=$dossier field=anomalie_autopsie_sejour_mere
                        style="width: 20em;" placeholder=$placeholder}}
                      </span>
                    </td>
                  </tr>
                </table>
              </form>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>