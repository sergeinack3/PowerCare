{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=style_default value="width: 12em;"}}
{{assign var=name_form value="Resume-accouchement-accouchement-`$accouchement->_guid`"}}
<form name="{{$name_form}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$accouchement}}
  {{mb_key   object=$accouchement}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="dossier_perinat_id" value="{{$dossier->_id}}" />
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>
            {{mb_title object=$accouchement field=date}} :{{mb_field object=$accouchement field=date register=true form=$name_form}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$accouchement field=sage_femme_resp_id}}</th>
              <td class="quarterPane">
                {{mb_field object=$accouchement field=sage_femme_resp_id style=$style_default options=$sagefemmes}}
              </td>
              <th class="quarterPane">{{mb_label object=$accouchement field=medecin_resp_id}}</th>
              <td class="quarterPane">
                {{mb_field object=$accouchement field=medecin_resp_id style=$style_default options=$praticiens}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$accouchement field=effectue_par_type}}</th>
              <td colspan="3">
                {{mb_field object=$accouchement field=effectue_par_type style=$style_default
                emptyLabel="CAccouchement.effectue_par_type."}}
                {{mb_field object=$accouchement field=effectue_par_type_autre style=$style_default}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$accouchement field=presentation}}</th>
              <td colspan="3">
                {{mb_field object=$accouchement field=presentation style=$style_default emptyLabel="CAccouchement.presentation."}}
              </td>
            </tr>
            <tr>
              <th class="category" colspan="4">{{tr}}CSuiviGrossesse-membranes{{/tr}}</th>
            </tr>
            <tr>
              <th>{{mb_label object=$accouchement field=moment_rupt_membranes}}</th>
              <td>
                {{mb_field object=$accouchement field=moment_rupt_membranes
                style=$style_default emptyLabel="CAccouchement.moment_rupt_membranes."}}
              </td>
              <th>{{mb_label object=$accouchement field=qte_liquide_rupt_membranes}}</th>
              <td>
                {{mb_field object=$accouchement field=qte_liquide_rupt_membranes
                style=$style_default emptyLabel="CAccouchement.qte_liquide_rupt_membranes."}}
              </td>
            </tr>
            <tr>
              <th style="vertical-align: top;">{{mb_label object=$accouchement field=aspect_liquide_rupt_membranes}}</th>
              <td>
                {{mb_field object=$accouchement field=aspect_liquide_rupt_membranes
                style=$style_default emptyLabel="CAccouchement.aspect_liquide_rupt_membranes."}}
                <br />
                {{mb_field object=$accouchement field=aspect_liquide_rupt_membranes_desc style=$style_default}}
              </td>
              <th style="vertical-align: top;">{{mb_label object=$accouchement field=aspect_liquide_post_rupt_membranes}}</th>
              <td>
                {{mb_field object=$accouchement field=aspect_liquide_post_rupt_membranes
                style=$style_default emptyLabel="CAccouchement.aspect_liquide_post_rupt_membranes."}}
                <br />
                {{mb_field object=$accouchement field=aspect_liquide_post_rupt_membranes_desc style=$style_default}}
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{tr}}CAccouchement-durees{{/tr}}</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_ouverture_oeuf_jours}}</td>
              <td>
                {{mb_field object=$accouchement field=duree_ouverture_oeuf_jours}} j et
                {{mb_field object=$accouchement field=duree_ouverture_oeuf_heures}} h
              </td>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_deambulation_heures}}</td>
              <td>
                {{mb_field object=$accouchement field=duree_deambulation_heures}} h
                {{mb_field object=$accouchement field=duree_deambulation_minutes}} min
              </td>
            </tr>
            <tr>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_travail_heures}}</td>
              <td>{{mb_field object=$accouchement field=duree_travail_heures}} h</td>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_entre_dilat_efforts_expuls}}</td>
              <td>{{mb_field object=$accouchement field=duree_entre_dilat_efforts_expuls}} min</td>
            </tr>
            <tr>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_travail_de_5cm_heures}}</td>
              <td>{{mb_field object=$accouchement field=duree_travail_de_5cm_heures}} h</td>
              <td class="text" style="text-align: right;">{{mb_label object=$accouchement field=duree_efforts_expuls}}</td>
              <td>{{mb_field object=$accouchement field=duree_efforts_expuls}} min</td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CGrossesseAnt-mode_accouchement{{/tr}}</legend>
          <table class="main layout">
            <tr>
              <td>
                <table class="form me-no-align me-no-box-shadow">
                  <tr>
                    <th class="narrow">{{mb_field object=$accouchement field=voie_basse_spont typeEnum=checkbox}}</th>
                    <td colspan="3">
                      <strong>{{mb_label object=$accouchement field=voie_basse_spont}}</strong>
                      {{mb_field object=$accouchement field=pos_voie_basse_spont
                      style=$style_default emptyLabel="CAccouchement.pos_voie_basse_spont."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_field object=$accouchement field=interv_voie_basse typeEnum=checkbox}}</th>
                    <td colspan="3"><strong>{{mb_label object=$accouchement field=interv_voie_basse}}</strong></td>
                  </tr
                  <tr>
                    <th class="narrow compact">{{mb_field object=$accouchement field=interv_voie_basse_forceps typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_forceps}}</td>
                    <th
                      class="narrow compact">{{mb_field object=$accouchement field=interv_voie_basse_ventouse typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_ventouse}}</td>
                  </tr>
                  <tr>
                    <th class="compact">{{mb_field object=$accouchement field=interv_voie_basse_spatules typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_spatules}}</td>
                    <th class="compact">{{mb_field object=$accouchement field=interv_voie_basse_pet_extr_siege typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_pet_extr_siege}}</td>
                  </tr>
                  <tr>
                    <th class="compact">{{mb_field object=$accouchement field=interv_voie_basse_grd_extr_siege typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_grd_extr_siege}}</td>
                    <th
                      class="compact">{{mb_field object=$accouchement field=interv_voie_basse_autre_man_siege typeEnum=checkbox}}</th>
                    <td class="compact">{{mb_label object=$accouchement field=interv_voie_basse_autre_man_siege}}</td>
                  </tr>
                  <tr>
                    <th
                      class="compact">{{mb_field object=$accouchement field=interv_voie_basse_man_dyst_epaules typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">
                      {{mb_label object=$accouchement field=interv_voie_basse_man_dyst_epaules}}
                      {{mb_field object=$accouchement field=interv_voie_basse_man_dyst_epaules_desc style=$style_default}}
                    </td>
                  </tr>
                  <tr>
                    <th class="compact">{{mb_field object=$accouchement field=interv_voie_basse_autre_man typeEnum=checkbox}}</th>
                    <td class="compact" colspan="3">
                      {{mb_label object=$accouchement field=interv_voie_basse_autre_man}}
                      {{mb_field object=$accouchement field=interv_voie_basse_autre_man_desc style=$style_default}}
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">{{mb_field object=$accouchement field=cesar_avt_travail typeEnum=checkbox}}</th>
                    <td colspan="3">
                      <strong>{{mb_label object=$accouchement field=cesar_avt_travail}}</strong>
                      {{mb_field object=$accouchement field=cesar_avt_travail_type
                      style=$style_default emptyLabel="CAccouchement.cesar_avt_travail_type."}}
                    </td>
                  </tr>
                  <tr>
                    <th class="narrow">{{mb_field object=$accouchement field=cesar_pdt_travail typeEnum=checkbox}}</th>
                    <td colspan="3">
                      <strong>{{mb_label object=$accouchement field=cesar_pdt_travail}}</strong>
                      {{mb_field object=$accouchement field=cesar_pdt_travail_type
                      style=$style_default emptyLabel="CAccouchement.cesar_pdt_travail_type."}}
                    </td>
                  </tr>
                </table>
              </td>
              <td>
                <table class="form me-no-align me-no-box-shadow">
                  <tr>
                    <th class="category" colspan="2">{{tr}}CAccouchement-interv_voie_basse{{/tr}}</th>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=interv_voie_basse_motif}}</th>
                    <td>
                      {{mb_field object=$accouchement field=interv_voie_basse_motif
                      style=$style_default emptyLabel="CAccouchement.interv_voie_basse_motif."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=interv_voie_basse_motif_asso}}</th>
                    <td>{{mb_field object=$accouchement field=interv_voie_basse_motif_asso style=$style_default}}</td>
                  </tr>
                  <tr>
                    <th class="category" colspan="2">{{tr}}CAccouchement-cesarienne{{/tr}}</th>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=cesar_motif}}</th>
                    <td>
                      {{mb_field object=$accouchement field=cesar_motif style=$style_default emptyLabel="CAccouchement.cesar_motif."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=cesar_motif_asso}}</th>
                    <td>{{mb_field object=$accouchement field=cesar_motif_asso style=$style_default}}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <table class="form me-no-align me-no-box-shadow">
                  <tr>
                    <th class="narrow"><strong>{{mb_label object=$accouchement field=endroit_action_interv_voie_basse}}</strong></th>
                    <td colspan="4">
                      {{mb_field object=$accouchement field=endroit_action_interv_voie_basse
                      style=$style_default emptyLabel="CAccouchement.endroit_action_interv_voie_basse."}}
                    </td>
                  </tr>
                  <tr>
                    <th><strong>{{tr}}CAccouchement-if_cesarienne{{/tr}}</strong></th>
                    <td colspan="4"></td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=type_cesar}}</th>
                    <td colspan="3">
                      {{mb_field object=$accouchement field=type_cesar  style=$style_default emptyLabel="CAccouchement.type_cesar."}}
                    </td>
                    <td>{{mb_label object=$accouchement field=remarques_cesar}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$accouchement field=actes_associes_cesar}}</th>
                    <td colspan="3">{{mb_field object=$accouchement field=actes_associes_cesar default=""}}</td>
                    <td rowspan="5">
                      {{if !$print}}
                        {{mb_field object=$accouchement field=remarques_cesar form=$name_form}}
                      {{else}}
                        {{mb_value object=$accouchement field=remarques_cesar}}
                      {{/if}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{tr}}If_yes{{/tr}}</th>
                    <td colspan="3"></td>
                  </tr>
                  <tr>
                    <th class="compact narrow">
                      {{mb_field object=$accouchement field=actes_associes_cesar_hysterectomie_hemostase typeEnum=checkbox}}
                    </th>
                    <td class="compact narrow">
                      {{mb_label object=$accouchement field=actes_associes_cesar_hysterectomie_hemostase}}
                    </td>
                    <th class="compact narrow">
                      {{mb_field object=$accouchement field=actes_associes_cesar_kystectomie_ovarienne typeEnum=checkbox}}
                    </th>
                    <td class="compact narrow">
                      {{mb_label object=$accouchement field=actes_associes_cesar_kystectomie_ovarienne}}
                    </td>
                  </tr>
                  <tr>
                    <th class="compact">
                      {{mb_field object=$accouchement field=actes_associes_cesar_myomectomie_unique typeEnum=checkbox}}
                    </th>
                    <td class="compact">{{mb_label object=$accouchement field=actes_associes_cesar_myomectomie_unique}}</td>
                    <th class="compact">
                      {{mb_field object=$accouchement field=actes_associes_cesar_ste_tubaire typeEnum=checkbox}}
                    </th>
                    <td class="compact">{{mb_label object=$accouchement field=actes_associes_cesar_ste_tubaire}}</td>
                  </tr>
                  <tr>
                    <th class="compact">
                      {{mb_field object=$accouchement field=actes_associes_cesar_interv_gross_abd typeEnum=checkbox}}
                    </th>
                    <td class="compact" colspan="3">{{mb_label object=$accouchement field=actes_associes_cesar_interv_gross_abd}}</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>
            {{mb_label object=$accouchement field=pb_cordon}}
            {{mb_field object=$accouchement field=pb_cordon default=""}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th>{{tr}}If_yes{{/tr}}</th>
              <td colspan="5"></td>
            </tr>
            <tr>
              <th class="narrow">{{mb_field object=$accouchement field=pb_cordon_procidence typeEnum=checkbox}}</th>
              <td>{{mb_label object=$accouchement field=pb_cordon_procidence}}</td>
              <th class="narrow">{{mb_field object=$accouchement field=pb_cordon_circ_serre typeEnum=checkbox}}</th>
              <td>{{mb_label object=$accouchement field=pb_cordon_circ_serre}}</td>
              <th class="narrow">{{mb_field object=$accouchement field=pb_cordon_noeud_vrai typeEnum=checkbox}}</th>
              <td>{{mb_label object=$accouchement field=pb_cordon_noeud_vrai}}</td>
            </tr>
            <tr>
              <th>{{mb_field object=$accouchement field=pb_cordon_brievete typeEnum=checkbox}}</th>
              <td>{{mb_label object=$accouchement field=pb_cordon_brievete}}</td>
              <th>{{mb_field object=$accouchement field=pb_cordon_insert_velament typeEnum=checkbox}}</th>
              <td>{{mb_label object=$accouchement field=pb_cordon_insert_velament}}</td>
              <th>{{mb_field object=$accouchement field=pb_cordon_autre typeEnum=checkbox}}</th>
              <td>
                {{mb_label object=$accouchement field=pb_cordon_autre}}
                {{mb_field object=$accouchement field=pb_cordon_autre_desc style=$style_default}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  </table>
</form>
