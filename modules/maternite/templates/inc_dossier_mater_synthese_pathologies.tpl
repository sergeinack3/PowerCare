{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-no-align me-no-box-shadow ">
  <tr>
    <th class="halfPane me-padding-2">
      {{mb_label object=$dossier field=pathologie_grossesse}}
      <br />
      <span class="compact">(ne retenir que les pathologies ayant un retentissement sur la grossesse)</span>
    </th>
    <td class="me-padding-2">{{mb_field object=$dossier field=pathologie_grossesse default=""}}</td>
  </tr>
</table>

<ul id="tab-pathologies_grossesse" class="control_tabs small">
  <li><a href="#pathologies_maternelles">{{tr}}CDossierPerinat-pathologie_grossesse_maternelle{{/tr}}</a></li>
  <li><a href="#therapeutiques_maternelles">Thérapeutiques maternelles</a></li>
  <li><a href="#pathologies_foetales">Pathologies foetales diagnostiquées in utero</a></li>
  <li><a href="#therapeutiques_foetales">Thérapeutiques foetales</a></li>
</ul>

<div id="pathologies_maternelles" class="me-padding-2" style="display: none;">
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=pathologie_grossesse_maternelle}}</th>
      <td>{{mb_field object=$dossier field=pathologie_grossesse_maternelle default=""}}</td>
    </tr>
    <tr>
      <th>si oui</th>
      <td></td>
    </tr>
  </table>
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form me-small-form">
            <tr>
              <td colspan="3"></td>
              <td class="compact">AG au diagnostic</td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=metrorragie_1er_trim typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=metrorragie_1er_trim}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_metrorragie_1er_trim}}</span>
                {{mb_field object=$dossier field=type_metrorragie_1er_trim
                style="width: 16em;" emptyLabel="CDossierPerinat.type_metrorragie_1er_trim."}}
              </td>
              <td class="greedyPane">
                {{mb_label object=$dossier field=ag_metrorragie_1er_trim style="display:none"}}
                {{mb_field object=$dossier field=ag_metrorragie_1er_trim}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=metrorragie_2e_3e_trim typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=metrorragie_2e_3e_trim}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_metrorragie_2e_3e_trim}}</span>
                {{mb_field object=$dossier field=type_metrorragie_2e_3e_trim
                style="width: 16em;" emptyLabel="CDossierPerinat.type_metrorragie_2e_3e_trim."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_metrorragie_2e_3e_trim style="display:none"}}
                {{mb_field object=$dossier field=ag_metrorragie_2e_3e_trim}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=menace_acc_premat typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=menace_acc_premat}}</td>
              <td class="compact">
                {{mb_field object=$dossier field=menace_acc_premat_modif_cerv typeEnum=checkbox}}
                {{mb_label object=$dossier field=menace_acc_premat_modif_cerv}}
                <br />
                <span style="display:none">{{mb_label object=$dossier field=pec_menace_acc_premat}}</span>
                {{mb_field object=$dossier field=pec_menace_acc_premat
                style="width: 16em;" emptyLabel="CDossierPerinat.pec_menace_acc_premat."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_menace_acc_premat style="display:none"}}
                {{mb_field object=$dossier field=ag_menace_acc_premat}} SA
                <br />
                <span class="compact">
                  {{mb_label object=$dossier field=ag_hospi_menace_acc_premat style="display:none"}}
                  {{mb_field object=$dossier field=ag_hospi_menace_acc_premat}} SA à l'hospit.
                </span>
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=rupture_premat_membranes typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=rupture_premat_membranes}}</td>
              <td>
              </td>
              <td>
                {{mb_label object=$dossier field=ag_rupture_premat_membranes style="display:none"}}
                {{mb_field object=$dossier field=ag_rupture_premat_membranes}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=anomalie_liquide_amniotique typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=anomalie_liquide_amniotique}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_anomalie_liquide_amniotique}}</span>
                {{mb_field object=$dossier field=type_anomalie_liquide_amniotique
                style="width: 16em;" emptyLabel="CDossierPerinat.type_anomalie_liquide_amniotique."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_anomalie_liquide_amniotique style="display:none"}}
                {{mb_field object=$dossier field=ag_anomalie_liquide_amniotique}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_patho_gravidique typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=autre_patho_gravidique}}</td>
              <td>
              </td>
              <td>
                {{mb_label object=$dossier field=ag_autre_patho_gravidique style="display:none"}}
                {{mb_field object=$dossier field=ag_autre_patho_gravidique}} SA
              </td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_vomissements typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_vomissements}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_herpes_gest typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_herpes_gest}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_dermatose_pup typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_dermatose_pup}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_placenta_praevia_non_hemo typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_placenta_praevia_non_hemo}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_chorio_amniotite typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_chorio_amniotite}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_transf_foeto_mat typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_transf_foeto_mat}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_beance_col typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=patho_grav_beance_col}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=patho_grav_cerclage typeEnum=checkbox}}</th>
              <td class="compact">si béance du col, {{mb_label object=$dossier field=patho_grav_cerclage}}</td>
              <td colspan="2"></td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=hypertension_arterielle typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=hypertension_arterielle}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_hypertension_arterielle}}</span>
                {{mb_field object=$dossier field=type_hypertension_arterielle
                style="width: 16em;" emptyLabel="CDossierPerinat.type_hypertension_arterielle."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_hypertension_arterielle style="display:none"}}
                {{mb_field object=$dossier field=ag_hypertension_arterielle}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=proteinurie typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=proteinurie}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_proteinurie}}</span>
                {{mb_field object=$dossier field=type_proteinurie
                style="width: 16em;" emptyLabel="CDossierPerinat.type_proteinurie."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_proteinurie style="display:none"}}
                {{mb_field object=$dossier field=ag_proteinurie}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=diabete typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=diabete}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_diabete}}</span>
                {{mb_field object=$dossier field=type_diabete
                style="width: 16em;" emptyLabel="CDossierPerinat.type_diabete."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_diabete style="display:none"}}
                {{mb_field object=$dossier field=ag_diabete}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=infection_urinaire typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=infection_urinaire}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_infection_urinaire}}</span>
                {{mb_field object=$dossier field=type_infection_urinaire
                style="width: 16em;" emptyLabel="CDossierPerinat.type_infection_urinaire."}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_infection_urinaire style="display:none"}}
                {{mb_field object=$dossier field=ag_infection_urinaire}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=infection_cervico_vaginale typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=infection_cervico_vaginale}}</td>
              <td>
                <span style="display:none">{{mb_label object=$dossier field=type_infection_cervico_vaginale}}</span>
                {{mb_field object=$dossier field=type_infection_cervico_vaginale
                style="width: 16em;" emptyLabel="CDossierPerinat.type_infection_cervico_vaginale."}}
                <br />
                {{mb_label object=$dossier field=autre_infection_cervico_vaginale style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_infection_cervico_vaginale"}}
                {{mb_field object=$dossier field=autre_infection_cervico_vaginale
                style="width: 16em;" placeholder=$placeholder}}
              </td>
              <td>
                {{mb_label object=$dossier field=ag_infection_cervico_vaginale style="display:none"}}
                {{mb_field object=$dossier field=ag_infection_cervico_vaginale}} SA
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form me-small-form">
            <tr>
              <td colspan="6" class="compact" style="text-align: right;">AG au diagnostic</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_patho_maternelle typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=autre_patho_maternelle}}</td>
              <td class="greedyPane" colspan="3"></td>
              <td style="text-align: right;">
                {{mb_label object=$dossier field=ag_autre_patho_maternelle style="display:none"}}
                {{mb_field object=$dossier field=ag_autre_patho_maternelle}} SA
              </td>
            </tr>
            <tr>
              <td colspan="6">
                <hr />
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=anemie_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=anemie_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=tombopenie_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=tombopenie_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_hemato_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_hemato_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_hemato_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_hemato_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_hemato_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}</td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=faible_prise_poid_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=faible_prise_poid_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=malnut_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=malnut_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_endo_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_endo_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_endo_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_endo_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_endo_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=cholestase_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=cholestase_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=steatose_hep_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=steatose_hep_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_hepato_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_hepato_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_hepato_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_hepato_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_hepato_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=thrombophl_sup_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=thrombophl_sup_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=thrombophl_prof_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=thrombophl_prof_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_vein_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_vein_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_vein_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_vein_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_vein_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=asthme_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=asthme_mat_pdt_grossesse}}</td>
              <td colspan="2" class="compact"></td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_resp_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_resp_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_resp_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_resp_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_resp_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=cardiopathie_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=cardiopathie_mat_pdt_grossesse}}</td>
              <td colspan="2" class="compact"></td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_cardio_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_cardio_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_cardio_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_cardio_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_cardio_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=epilepsie_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=epilepsie_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=depression_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=depression_mat_pdt_grossesse}}</td>
              <th class="narrow">{{mb_field object=$dossier field=autre_patho_neuropsy_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_neuropsy_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_patho_neuropsy_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_neuropsy_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_patho_neuropsy_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=patho_gyneco_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact" colspan="3">
                {{mb_label object=$dossier field=patho_gyneco_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_patho_gyneco_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_patho_gyneco_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_patho_gyneco_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
              <th class="narrow">{{mb_field object=$dossier field=mst_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=mst_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_mst_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_mst_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_mst_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=synd_douleur_abdo_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact" colspan="3">
                {{mb_label object=$dossier field=synd_douleur_abdo_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_synd_douleur_abdo_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_synd_douleur_abdo_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_synd_douleur_abdo_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
              <th class="narrow">{{mb_field object=$dossier field=synd_infect_mat_pdt_grossesse typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=synd_infect_mat_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_synd_infect_mat_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_synd_infect_mat_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_synd_infect_mat_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</div>

<div id="therapeutiques_maternelles" style="display: none;">
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=therapeutique_grossesse_maternelle}}</th>
      <td>{{mb_field object=$dossier field=therapeutique_grossesse_maternelle default=""}}</td>
    </tr>
    <tr>
      <th>si oui</th>
      <td></td>
    </tr>
  </table>
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form me-no-align">
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=antibio_pdt_grossesse typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$dossier field=antibio_pdt_grossesse}}
                {{mb_label object=$dossier field=type_antibio_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-type_antibio_pdt_grossesse"}}
                {{mb_field object=$dossier field=type_antibio_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=tocolyt_pdt_grossesse typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$dossier field=tocolyt_pdt_grossesse}}
                <span style="display:none">{{mb_label object=$dossier field=mode_admin_tocolyt_pdt_grossesse}}</span>
                {{mb_field object=$dossier field=mode_admin_tocolyt_pdt_grossesse
                style="width: 16em;" emptyLabel="CDossierPerinat.mode_admin_tocolyt_pdt_grossesse."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=cortico_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=cortico_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th><span class="compact">{{mb_label object=$dossier field=nb_cures_cortico_pdt_grossesse}}</span></th>
              <td colspan="2">
                <span class="compact" colspan="2">{{mb_field object=$dossier field=nb_cures_cortico_pdt_grossesse}}</span>
              </td>
            </tr>
            <tr>
              <th><span class="compact">{{mb_label object=$dossier field=etat_dern_cure_cortico_pdt_grossesse}}</span></th>
              <td colspan="2">
                <span class="compact">
                  <span style="display:none">{{mb_label object=$dossier field=etat_dern_cure_cortico_pdt_grossesse}}</span>
                  {{mb_field object=$dossier field=etat_dern_cure_cortico_pdt_grossesse
                  style="width: 16em;" emptyLabel="CDossierPerinat.etat_dern_cure_cortico_pdt_grossesse."}}
                </span>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form me-no-align">
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=gammaglob_anti_d_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=gammaglob_anti_d_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=antihyp_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=antihyp_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=aspirine_a_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=aspirine_a_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=barbit_antiepilept_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=barbit_antiepilept_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=psychotropes_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=psychotropes_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=subst_nicotine_pdt_grossesse typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=subst_nicotine_pdt_grossesse}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_therap_mater_pdt_grossesse typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$dossier field=autre_therap_mater_pdt_grossesse}}
                {{mb_label object=$dossier field=desc_autre_therap_mater_pdt_grossesse style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_therap_mater_pdt_grossesse"}}
                {{mb_field object=$dossier field=desc_autre_therap_mater_pdt_grossesse
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</div>

<div id="pathologies_foetales" style="display: none;">
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=patho_foetale_in_utero}}</th>
      <td>{{mb_field object=$dossier field=patho_foetale_in_utero default=""}}</td>
    </tr>
    <tr>
      <th>si oui</th>
      <td></td>
    </tr>
  </table>
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form me-small-form">
            <tr>
              <td colspan="2"></td>
              <td class="compact">AG au diagnostic</td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=anomalie_croiss_intra_uterine typeEnum=checkbox}}</th>
              <td>
                <strong>{{mb_label object=$dossier field=anomalie_croiss_intra_uterine}}</strong>
                <span style="display:none">{{mb_label object=$dossier field=type_anomalie_croiss_intra_uterine}}</span>
                {{mb_field object=$dossier field=type_anomalie_croiss_intra_uterine
                style="width: 16em;" emptyLabel="CDossierPerinat.type_anomalie_croiss_intra_uterine."}}
              </td>
              <td class="narrow">
                {{mb_label object=$dossier field=ag_anomalie_croiss_intra_uterine style="display:none"}}
                {{mb_field object=$dossier field=ag_anomalie_croiss_intra_uterine}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=signes_hypoxie_foetale_chronique typeEnum=checkbox}}</th>
              <td><strong>{{mb_label object=$dossier field=signes_hypoxie_foetale_chronique}}</strong></td>
              <td>
                {{mb_label object=$dossier field=ag_signes_hypoxie_foetale_chronique style="display:none"}}
                {{mb_field object=$dossier field=ag_signes_hypoxie_foetale_chronique}} SA
              </td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=hypoxie_foetale_anomalie_doppler typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=hypoxie_foetale_anomalie_doppler}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=hypoxie_foetale_anomalie_rcf typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=hypoxie_foetale_anomalie_rcf}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=hypoxie_foetale_alter_profil_biophy typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=hypoxie_foetale_alter_profil_biophy}}</td>
              <td></td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=anomalie_constit_foetus typeEnum=checkbox}}</th>
              <td><strong>{{mb_label object=$dossier field=anomalie_constit_foetus}}</strong></td>
              <td>
                {{mb_label object=$dossier field=ag_anomalie_constit_foetus style="display:none"}}
                {{mb_field object=$dossier field=ag_anomalie_constit_foetus}} SA
              </td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=malformation_isolee_foetus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=malformation_isolee_foetus}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=anomalie_chromo_foetus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=anomalie_chromo_foetus}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=synd_polymalform_foetus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=synd_polymalform_foetus}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=anomalie_genique_foetus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=anomalie_genique_foetus}}</td>
              <td></td>
            </tr>
            <tr>
              <th></th>
              <td colspan="2">
                {{mb_label object=$dossier field=rques_anomalies_foetus}}
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_anomalies_foetus style="display: block"}}
                {{else}}
                  {{mb_value object=$dossier field=rques_anomalies_foetus}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset class="me-no-box-shadow me-padding-0">
          <table class="form me-small-form">
            <tr>
              <td colspan="2"></td>
              <td class="compact">AG au diagnostic</td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=foetopathie_infect_acquise typeEnum=checkbox}}</th>
              <td>
                <strong>{{mb_label object=$dossier field=foetopathie_infect_acquise}}</strong>
                <br />
                <span style="display:none">{{mb_label object=$dossier field=type_foetopathie_infect_acquise}}</span>
                {{mb_field object=$dossier field=type_foetopathie_infect_acquise
                style="width: 16em;" emptyLabel="CDossierPerinat.type_foetopathie_infect_acquise."}}
                <br />
                {{mb_label object=$dossier field=autre_foetopathie_infect_acquise style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_foetopathie_infect_acquise"}}
                {{mb_field object=$dossier field=autre_foetopathie_infect_acquise
                style="width: 16em;" placeholder=$placeholder}}
              </td>
              <td class="narrow">
                {{mb_label object=$dossier field=ag_foetopathie_infect_acquise style="display:none"}}
                {{mb_field object=$dossier field=ag_foetopathie_infect_acquise}} SA
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_patho_foetale typeEnum=checkbox}}</th>
              <td><strong>{{mb_label object=$dossier field=autre_patho_foetale}}</strong></td>
              <td>
                {{mb_label object=$dossier field=ag_autre_patho_foetale style="display:none"}}
                {{mb_field object=$dossier field=ag_autre_patho_foetale}} SA
              </td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=allo_immun_anti_rh_foetale typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=allo_immun_anti_rh_foetale}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=autre_allo_immun_foetale typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=autre_allo_immun_foetale}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=anas_foeto_plac_non_immun typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=anas_foeto_plac_non_immun}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=trouble_rcf_foetus typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=trouble_rcf_foetus}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=foetopathie_alcoolique typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=foetopathie_alcoolique}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=grosse_abdo_foetus_viable typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=grosse_abdo_foetus_viable}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=mort_foetale_in_utero_in_22sa typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=mort_foetale_in_utero_in_22sa}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=autre_patho_foetale_autre typeEnum=checkbox}}</th>
              <td class="compact">
                {{mb_label object=$dossier field=autre_patho_foetale_autre}}
                {{mb_label object=$dossier field=desc_autre_patho_foetale_autre style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_patho_foetale_autre"}}
                {{mb_field object=$dossier field=desc_autre_patho_foetale_autre
                style="width: 16em;" placeholder=$placeholder}}
              </td>
              <td></td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=patho_foetale_gross_mult typeEnum=checkbox}}</th>
              <td><strong>{{mb_label object=$dossier field=patho_foetale_gross_mult}}</strong></td>
              <td>
                {{mb_label object=$dossier field=ag_patho_foetale_gross_mult style="display:none"}}
                {{mb_field object=$dossier field=ag_patho_foetale_gross_mult}} SA
              </td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=avort_foetus_gross_mult typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=avort_foetus_gross_mult}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=mort_foetale_in_utero_gross_mutl typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=mort_foetale_in_utero_gross_mutl}}</td>
              <td></td>
            </tr>
            <tr>
              <th class="compact">{{mb_field object=$dossier field=synd_transf_transf_gross_mult typeEnum=checkbox}}</th>
              <td class="compact">{{mb_label object=$dossier field=synd_transf_transf_gross_mult}}</td>
              <td></td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</div>

<div id="therapeutiques_foetales" style="display: none;">
  <table class="form me-no-box-shadow me-no-align me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=therapeutique_foetale}}</th>
      <td>{{mb_field object=$dossier field=therapeutique_foetale default=""}}</td>
    </tr>
    <tr>
      <th>si oui</th>
      <td></td>
    </tr>
  </table>
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form">
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=amnioinfusion typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=amnioinfusion}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=chirurgie_foetale typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=chirurgie_foetale}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=derivation_foetale typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=derivation_foetale}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=tranfusion_foetale_in_utero typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=tranfusion_foetale_in_utero}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=ex_sanguino_transfusion_foetale typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=ex_sanguino_transfusion_foetale}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=autre_therapeutiques_foetales typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=autre_therapeutiques_foetales}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset class="me-padding-0 me-no-box-shadow">
          <table class="form">
            <tr>
              <th class="narrow">{{mb_field object=$dossier field=reduction_embryonnaire typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$dossier field=reduction_embryonnaire}}
                <span style="display:none">{{mb_label object=$dossier field=type_reduction_embryonnaire}}</span>
                {{mb_field object=$dossier field=type_reduction_embryonnaire
                style="width: 16em;" emptyLabel="CDossierPerinat.type_reduction_embryonnaire."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_field object=$dossier field=photocoag_vx_placentaires typeEnum=checkbox}}</th>
              <td>{{mb_label object=$dossier field=photocoag_vx_placentaires}}</td>
            </tr>
            <tr>
              <th></th>
              <td>
                {{mb_label object=$dossier field=rques_therapeutique_foetale}}
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_therapeutique_foetale}}
                {{else}}
                  {{mb_value object=$dossier field=rques_therapeutique_foetale}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
