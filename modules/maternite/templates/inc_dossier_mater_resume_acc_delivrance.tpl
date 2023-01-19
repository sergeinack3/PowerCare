{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form name="Resume-accouchement-delivrance-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>Delivrance</legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="quarterPane">{{mb_label object=$dossier field=deliv_faite_par}}</th>
              <td class="quarterPane">{{mb_field object=$dossier field=deliv_faite_par}}</td>
              <th class="quarterPane">{{mb_label object=$dossier field=datetime_deliv}}</th>
              <td class="quarterPane">
                {{mb_field object=$dossier field=datetime_deliv
                form="Resume-accouchement-delivrance-`$dossier->_guid`" register=true}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=type_deliv}}</th>
              <td colspan="3">
                {{mb_field object=$dossier field=type_deliv
                style="width: 12em;" emptyLabel="CDossierPerinatal.type_deliv."}}
              </td>
            </tr>
            <tr>
              <th>Si dirigée</th>
              <td colspan="3"></td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=prod_deliv}}</th>
              <td>{{mb_field object=$dossier field=prod_deliv}}</td>
              <th>{{mb_label object=$dossier field=dose_prod_deliv}}</th>
              <td>{{mb_field object=$dossier field=dose_prod_deliv}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=datetime_inj_prod_deliv}}</th>
              <td>
                {{mb_field object=$dossier field=datetime_inj_prod_deliv
                form="Resume-accouchement-delivrance-`$dossier->_guid`" register=true}}
              </td>
              <th>{{mb_label object=$dossier field=voie_inj_prod_deliv}}</th>
              <td>{{mb_field object=$dossier field=voie_inj_prod_deliv}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=modalite_deliv}}</th>
              <td colspan="3">
                {{mb_field object=$dossier field=modalite_deliv
                style="width: 12em;" emptyLabel="CDossierPerinatal.modalite_deliv."}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>
            {{mb_label object=$dossier field=comp_deliv}}
            {{mb_field object=$dossier field=comp_deliv default=""}}
          </legend>
          <table class="main layout">
            <tr>
              <td>Si oui,</td>
            </tr>
            <tr>
              <td class="halfPane">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="narrow">{{mb_field object=$dossier field=hemorr_deliv typeEnum=checkbox}}</th>
                    <td colspan="2">{{mb_label object=$dossier field=hemorr_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <td colspan="2" class="compact">Si oui, motif</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact narrow">{{mb_field object=$dossier field=retention_plac_comp_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=retention_plac_comp_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=retention_plac_part_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=retention_plac_part_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=atonie_uterine_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=atonie_uterine_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=trouble_coag_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=trouble_coag_deliv}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$dossier field=transf_deliv typeEnum=checkbox}}</th>
                    <td colspan="2">{{mb_label object=$dossier field=transf_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <td colspan="2" class="compact">
                      Si oui,
                      {{mb_label object=$dossier field=nb_unites_transf_deliv}}
                      {{mb_field object=$dossier field=nb_unites_transf_deliv}}
                    </td>
                  </tr>
                </table>
              </td>
              <td class="halfPane">
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="narrow">{{mb_field object=$dossier field=autre_comp_deliv typeEnum=checkbox}}</th>
                    <td colspan="2">{{mb_label object=$dossier field=autre_comp_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=retention_plac_comp_sans_hemorr_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=retention_plac_comp_sans_hemorr_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=retention_plac_part_sans_hemorr_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=retention_plac_part_sans_hemorr_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=inversion_uterine_deliv typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$dossier field=inversion_uterine_deliv}}</td>
                  </tr>
                  <tr>
                    <th></th>
                    <th class="compact">{{mb_field object=$dossier field=autre_comp_autre_deliv typeEnum=checkbox}}</th>
                    <td class="compact">
                      {{mb_label object=$dossier field=autre_comp_autre_deliv}}
                      {{mb_label object=$dossier field=autre_comp_autre_deliv_desc style="display: none;"}}
                      {{mb_field object=$dossier field=autre_comp_autre_deliv_desc style="width: 12em;"}}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      {{mb_label object=$dossier field=total_pertes_sang_deliv}}
                      {{mb_field object=$dossier field=total_pertes_sang_deliv}} ml
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <fieldset>
          <legend>
            {{mb_label object=$dossier field=actes_pdt_deliv}}
            {{mb_field object=$dossier field=actes_pdt_deliv default=""}}
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="narrow">Si oui,</th>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=deliv_artificielle typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=deliv_artificielle}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=rev_uterine_isolee_deliv typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=rev_uterine_isolee_deliv}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autres_actes_deliv typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=autres_actes_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=ligature_art_hypogast_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=ligature_art_hypogast_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=ligature_art_uterines_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=ligature_art_uterines_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=hysterectomie_hemostase_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=hysterectomie_hemostase_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=embolisation_arterielle_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=embolisation_arterielle_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=reduct_inversion_uterine_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=reduct_inversion_uterine_deliv}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=cure_chir_inversion_uterine_deliv typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=cure_chir_inversion_uterine_deliv}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>Placenta</legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th class="narrow">{{mb_label object=$dossier field=poids_placenta}}</th>
              <td>{{mb_field object=$dossier field=poids_placenta}} g</td>
              <td class="thirdPane">{{mb_label object=$dossier field=rques_placenta}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=anomalie_placenta}}</th>
              <td>
                {{mb_field object=$dossier field=anomalie_placenta
                style="width: 12em;" emptyLabel="CDossierPerinatal.anomalie_placenta."}}
                <br />
                {{mb_label object=$dossier field=anomalie_placenta_desc style="display: none;"}}
                {{mb_field object=$dossier field=anomalie_placenta_desc style="width: 12em;"}}
              </td>
              <td rowspan="20">
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_placenta form=Resume-accouchement-delivrance-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_placenta}}
                {{/if}}
              </td>
            </tr>
            <tr>
              <th>Si grossess multiple</th>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_label object=$dossier field=type_placentation}}</th>
              <td class="compact">
                {{mb_field object=$dossier field=type_placentation
                style="width: 12em;" emptyLabel="CDossierPerinatal.type_placentation."}}
                <br />
                {{mb_label object=$dossier field=type_placentation_desc style="display: none;"}}
                {{mb_field object=$dossier field=type_placentation_desc style="width: 12em;"}}
              </td>
            </tr>
            <tr>
              <th>Si placenta bi-chorial</th>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_label object=$dossier field=poids_placenta_1_bichorial}}</th>
              <td class="compact">{{mb_field object=$dossier field=poids_placenta_1_bichorial}} g</td>
            </tr>
            <tr>
              <th class="compact">{{mb_label object=$dossier field=poids_placenta_2_bichorial}}</th>
              <td class="compact">{{mb_field object=$dossier field=poids_placenta_2_bichorial}} g</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_anapath_placenta_demande}}</th>
              <td>{{mb_field object=$dossier field=exam_anapath_placenta_demande default=""}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <fieldset>
          <legend>
            {{mb_label object=$dossier field=lesion_parties_molles}}
            {{mb_field object=$dossier field=lesion_parties_molles default=""}}
          </legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th>Si oui,</th>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=episiotomie typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=episiotomie}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=dechirure_perineale typeEnum=checkbox}}</th>
              <td colspan="2">
                {{mb_label object=$dossier field=dechirure_perineale}}
                {{mb_label object=$dossier field=dechirure_perineale_liste style="display: none;"}}
                {{mb_field object=$dossier field=dechirure_perineale_liste
                style="width: 12em;" emptyLabel="CDossierPerinatal.dechirure_perineale_liste."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=lesions_traumatiques_parties_molles typeEnum=checkbox}}</th>
              <td colspan="2">{{mb_label object=$dossier field=lesions_traumatiques_parties_molles}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=dechirure_vaginale typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=dechirure_vaginale}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=dechirure_cervicale typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=dechirure_cervicale}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=lesion_urinaire typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=lesion_urinaire}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=rupt_uterine typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=rupt_uterine}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=thrombus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=thrombus}}</td>
            </tr>
            <tr>
              <th></th>
              <th class="compact">{{mb_field object=$dossier field=autre_lesion typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_lesion}}
                {{mb_label object=$dossier field=autre_lesion_desc style="display: none;"}}
                {{mb_field object=$dossier field=autre_lesion_desc style="width: 12em;"}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>{{mb_label object=$dossier field=compte_rendu_delivrance}}</legend>
          {{if !$print}}
            {{mb_field object=$dossier field=compte_rendu_delivrance form=Resume-accouchement-delivrance-`$dossier->_guid`}}
          {{else}}
            {{mb_value object=$dossier field=compte_rendu_delivrance}}
          {{/if}}
        </fieldset>
        <fieldset>
          <legend>{{mb_label object=$dossier field=consignes_suite_couches}}</legend>
          {{if !$print}}
            {{mb_field object=$dossier field=consignes_suite_couches form=Resume-accouchement-delivrance-`$dossier->_guid`}}
          {{else}}
            {{mb_value object=$dossier field=consignes_suite_couches}}
          {{/if}}
        </fieldset>
      </td>
    </tr>
  </table>
</form>
