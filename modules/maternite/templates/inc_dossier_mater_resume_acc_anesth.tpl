{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form name="Resume-accouchement-anesthesie-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <table class="main layout">
    <tr>
      <td>
        <fieldset>
          <legend>
            {{mb_label object=$dossier field=anesth_avant_naiss}}
            {{mb_label object=$dossier field=anesth_avant_naiss default=""}}
          </legend>
          <table class="main layout">
            <tr>
              <td colspan="2">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="halfPane">Si oui,</th>
                    <td></td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=datetime_anesth_avant_naiss}}</th>
                    <td>
                      {{mb_field object=$dossier field=datetime_anesth_avant_naiss
                      form=Resume-accouchement-anesthesie-`$dossier->_guid` register=true}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=anesth_avant_naiss_par_id}}</th>
                    <td>
                      {{mb_field object=$dossier field=anesth_avant_naiss_par_id style="width: 12em;"
                      options=$anesth}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=suivi_anesth_avant_naiss_par}}</th>
                    <td>{{mb_field object=$dossier field=suivi_anesth_avant_naiss_par}}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="category" colspan="8">Type d'anesthésie</th>
                  </tr>
                  <tr>
                    <th class="narrow">{{mb_field object=$dossier field=alr_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="7"><strong>{{mb_label object=$dossier field=alr_avant_naiss}}</strong></td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="narrow">{{mb_field object=$dossier field=alr_peri_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="6">{{mb_label object=$dossier field=alr_peri_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th colspan="2"></th>
                    <th
                      class="narrow compact">{{mb_field object=$dossier field=alr_peri_avant_naiss_inj_unique typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=alr_peri_avant_naiss_inj_unique}}</td>
                    <th class="narrow compact">{{mb_field object=$dossier field=alr_peri_avant_naiss_reinj typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">{{mb_label object=$dossier field=alr_peri_avant_naiss_reinj}}</td>
                  </tr>
                  <tr>
                    <th colspan="2"></th>
                    <th class="compact">{{mb_field object=$dossier field=alr_peri_avant_naiss_cat_autopousse typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=alr_peri_avant_naiss_cat_autopousse}}</td>
                    <th class="compact">{{mb_field object=$dossier field=alr_peri_avant_naiss_cat_pcea typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">{{mb_label object=$dossier field=alr_peri_avant_naiss_cat_pcea}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th>{{mb_field object=$dossier field=alr_rachi_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="6">{{mb_label object=$dossier field=alr_rachi_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th colspan="2"></th>
                    <th class="compact">{{mb_field object=$dossier field=alr_rachi_avant_naiss_inj_unique typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=alr_rachi_avant_naiss_inj_unique}}</td>
                    <th class="compact">{{mb_field object=$dossier field=alr_rachi_avant_naiss_cat typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">{{mb_label object=$dossier field=alr_rachi_avant_naiss_cat}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th>{{mb_field object=$dossier field=alr_peri_rachi_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="6">{{mb_label object=$dossier field=alr_peri_rachi_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=ag_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="7"><strong>{{mb_label object=$dossier field=ag_avant_naiss}}</strong></td>
                  </tr>
                  <tr>
                    <th colspan="2"></th>
                    <th class="compact">{{mb_field object=$dossier field=ag_avant_naiss_directe typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=ag_avant_naiss_directe}}</td>
                    <th class="compact">{{mb_field object=$dossier field=ag_avant_naiss_apres_peri typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=ag_avant_naiss_apres_peri}}</td>
                    <th class="compact narrow">{{mb_field object=$dossier field=ag_avant_naiss_apres_rachi typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=ag_avant_naiss_apres_rachi}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=al_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="7"><strong>{{mb_label object=$dossier field=al_avant_naiss}}</strong></td>
                  </tr>
                  <tr>
                    <th colspan="2"></th>
                    <th class="compact">{{mb_field object=$dossier field=al_bloc_avant_naiss typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=al_bloc_avant_naiss}}</td>
                    <th class="compact">{{mb_field object=$dossier field=al_autre_avant_naiss typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">
                      {{mb_label object=$dossier field=al_autre_avant_naiss}}
                      {{mb_label object=$dossier field=al_autre_avant_naiss_desc style="display: none;"}}
                      {{mb_field object=$dossier field=al_autre_avant_naiss_desc}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=autre_analg_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="7">
                      <strong>{{mb_label object=$dossier field=autre_analg_avant_naiss}}</strong>
                      {{mb_label object=$dossier field=autre_analg_avant_naiss_desc style="display: none;"}}
                      {{mb_field object=$dossier field=autre_analg_avant_naiss_desc style="width: 12em;"}}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8">
                      {{mb_label object=$dossier field=fibro_laryngee}}
                      {{mb_field object=$dossier field=fibro_laryngee default=""}}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8">
                      {{mb_label object=$dossier field=asa_anesth_avant_naissance}}
                      {{mb_field object=$dossier field=asa_anesth_avant_naissance default=""}}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8">
                      {{mb_label object=$dossier field=moment_anesth_avant_naissance}}
                      {{mb_field object=$dossier field=moment_anesth_avant_naissance
                      style="width: 12em;" emptyLabel="CDossierPerinatal.moment_anesth_avant_naissance."}}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8">
                      {{mb_label object=$dossier field=anesth_spec_2eme_enfant}}
                      {{mb_field object=$dossier field=anesth_spec_2eme_enfant
                      style="width: 12em;" emptyLabel="CDossierPerinatal.anesth_spec_2eme_enfant."}}
                      {{mb_label object=$dossier field=anesth_spec_2eme_enfant_desc style="display: none;"}}
                      {{mb_field object=$dossier field=anesth_spec_2eme_enfant_desc style="width: 12em;"}}
                    </td>
                  </tr>
                </table>
              </td>
              <td>
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="category">{{mb_label object=$dossier field=rques_anesth_avant_naiss}}</th>
                  </tr>
                  <tr>
                    <td>
                      {{if !$print}}
                        {{mb_field object=$dossier field=rques_anesth_avant_naiss form=Resume-accouchement-anesthesie-`$dossier->_guid`}}
                      {{else}}
                        {{mb_value object=$dossier field=rques_anesth_avant_naiss}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th colspan="7" class="category">
                      {{mb_label object=$dossier field=comp_anesth_avant_naiss}}
                      {{mb_field object=$dossier field=comp_anesth_avant_naiss default=""}}
                    </th>
                  </tr>
                  <tr>
                    <th>Si oui,</th>
                    <td colspan="6"></td>
                  </tr>
                  <tr>
                    <th rowspan="2">Anesthésie loco-régionale</th>
                    <th class="narrow">{{mb_field object=$dossier field=hypotension_alr_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="5">{{mb_label object=$dossier field=hypotension_alr_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=autre_comp_alr_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="5">
                      {{mb_label object=$dossier field=autre_comp_alr_avant_naiss}}
                      {{mb_label object=$dossier field=autre_comp_alr_avant_naiss_desc style="display: none;"}}
                      {{mb_field object=$dossier field=autre_comp_alr_avant_naiss_desc style="width: 12em;"}}
                    </td>
                  </tr>
                  <tr>
                    <th rowspan="3">Anesthésie générale</th>
                    <th>{{mb_field object=$dossier field=mendelson_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td>{{mb_label object=$dossier field=mendelson_ag_avant_naiss}}</td>
                    <th class="narrow">{{mb_field object=$dossier field=comp_pulm_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td>{{mb_label object=$dossier field=comp_pulm_ag_avant_naiss}}</td>
                    <th class="narrow">{{mb_field object=$dossier field=comp_card_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td>{{mb_label object=$dossier field=comp_card_ag_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=comp_cereb_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td>{{mb_label object=$dossier field=comp_cereb_ag_avant_naiss}}</td>
                    <th>{{mb_field object=$dossier field=comp_allerg_tox_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="3">{{mb_label object=$dossier field=comp_allerg_tox_ag_avant_naiss}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=autre_comp_ag_avant_naiss typeEnum=checkbox}}</th>
                    <td colspan="5">
                      {{mb_label object=$dossier field=autre_comp_ag_avant_naiss}}
                      {{mb_label object=$dossier field=autre_comp_ag_avant_naiss_desc style="display: none;"}}
                      {{mb_field object=$dossier field=autre_comp_ag_avant_naiss_desc style="width: 12em;"}}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="thirdPane me-w40">
        <fieldset class="me-small">
          <legend>
            {{mb_label object=$dossier field=anesth_apres_naissance}}
            {{mb_field object=$dossier field=anesth_apres_naissance
            style="width: 12em;" emptyLabel="CDossierPerinatal.anesth_apres_naissance."}}
            {{mb_label object=$dossier field=anesth_apres_naissance_desc style="display: none;"}}
            {{mb_field object=$dossier field=anesth_apres_naissance_desc style="width: 12em;"}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td>{{mb_label object=$dossier field=rques_anesth_apres_naissance}}</td>
            </tr>
            <tr>
              <td>
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_anesth_apres_naissance form=Resume-accouchement-anesthesie-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_anesth_apres_naissance}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</form>
