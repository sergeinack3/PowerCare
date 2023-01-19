{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Resume-accouchement-pathologies-{{$dossier->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-align me-small-form me-no-box-shadow">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=pathologie_accouchement}}</th>
      <td>{{mb_field object=$dossier field=pathologie_accouchement default=""}}</td>
    </tr>
    <tr>
      <th>Si oui,</th>
      <td></td>
    </tr>
    <tr>
      <th>{{mb_field object=$dossier field=fievre_pdt_travail typeEnum=checkbox}}</th>
      <td>{{mb_label object=$dossier field=fievre_pdt_travail}}</td>
    </tr>
  </table>
</form>

{{assign var=constantes value=$dossier->_ref_fievre_travail_constantes}}
{{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:'list_constantes'}}
<form name="Resume-accouchement-constantes-{{$dossier->_guid}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$constantes}}
  {{mb_key   object=$constantes}}
  {{mb_field object=$constantes field=patient_id hidden=true}}
  {{mb_field object=$constantes field=context_class hidden=true}}
  {{mb_field object=$constantes field=context_id hidden=true}}
  {{mb_field object=$constantes field=datetime hidden=true}}
  {{mb_field object=$constantes field=user_id hidden=true}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="_grossesse_id" value="{{$grossesse->_id}}">
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$constantes field=temperature}}</th>
      <td class="halfPane">{{mb_field object=$constantes field=temperature size=3}}</td>
    </tr>
  </table>
</form>

<form name="Resume-accouchement-anomalies-{{$dossier->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <script>
    Main.add(function () {
      Control.Tabs.create('tab-anomalies', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
      var form = getForm("Resume-accouchement-anomalies-{{$dossier->_guid}}");
      [form.__anom_av_trav, form.__anom_pdt_trav, form.__anom_expuls].invoke("observe", "click",
        function (e) {
          var element = Event.element(e);
          (function (checkbox) {
            checkbox.checked = !(checkbox.checked);
          }).defer(element);
        }
      );
    });
  </script>

  <ul id="tab-anomalies" class="control_tabs small">
    <li>
      <a href="#avantTravail">
        {{mb_field object=$dossier field=anom_av_trav typeEnum="checkbox"}}
        {{mb_label object=$dossier field=anom_av_trav}}
      </a>
    </li>
    <li>
      <a href="#pendantTravail">
        {{mb_field object=$dossier field=anom_pdt_trav typeEnum="checkbox"}}
        {{mb_label object=$dossier field=anom_pdt_trav}}
      </a>
    </li>
    <li>
      <a href="#pendantExpulsion">
        {{mb_field object=$dossier field=anom_expuls typeEnum="checkbox"}}
        {{mb_label object=$dossier field=anom_expuls}}
      </a>
    </li>
  </ul>
  <div id="avantTravail" style="display: none;">
    <table class="main layout">
      <tr>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_pres_av_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=anom_pres_av_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_pres_av_trav_siege typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_pres_av_trav_siege}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_pres_av_trav_transverse typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_pres_av_trav_transverse}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_pres_av_trav_face typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_pres_av_trav_face}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_pres_av_trav_anormale typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_pres_av_trav_anormale}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_pres_av_trav_autre typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_pres_av_trav_autre}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_bassin_av_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=anom_bassin_av_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_bassin_av_trav_bassin_retreci typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_bassin_retreci}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_bassin_av_trav_malform_bassin typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_malform_bassin}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_bassin_av_trav_foetus typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_foetus}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_bassin_av_trav_disprop_foetopelv typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_disprop_foetopelv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_bassin_av_trav_disprop_difform_foet typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_disprop_difform_foet}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_bassin_av_trav_disprop_sans_prec typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_bassin_av_trav_disprop_sans_prec}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_genit_hors_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=anom_genit_hors_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_genit_hors_trav_uterus_cicat typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_uterus_cicat}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_genit_hors_trav_rupt_uterine typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_rupt_uterine}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_genit_hors_trav_fibrome_uterin typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_fibrome_uterin}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_genit_hors_trav_malform_uterine typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_malform_uterine}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=anom_genit_hors_trav_anom_vaginales typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_anom_vaginales}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_genit_hors_trav_chir_ant_perinee typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_chir_ant_perinee}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_genit_hors_trav_prolapsus_vaginal typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_genit_hors_trav_prolapsus_vaginal}}</span>
              </td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_plac_av_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=anom_plac_av_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_plac_prae_sans_hemo typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_plac_prae_sans_hemo}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_plac_prae_avec_hemo typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_plac_prae_avec_hemo}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_hrp_avec_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_hrp_avec_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_hrp_sans_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_hrp_sans_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_autre_hemo_avec_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_autre_hemo_avec_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_autre_hemo_sans_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_autre_hemo_sans_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_transf_foeto_mater typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_transf_foeto_mater}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=anom_plac_av_trav_infect_sac_membranes typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=anom_plac_av_trav_infect_sac_membranes}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=rupt_premat_membranes typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=rupt_premat_membranes}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=rupt_premat_membranes_rpm_inf37sa_sans_toco typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=rupt_premat_membranes_rpm_inf37sa_sans_toco}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=rupt_premat_membranes_rpm_inf37sa_avec_toco typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=rupt_premat_membranes_rpm_inf37sa_avec_toco}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=rupt_premat_membranes_rpm_sup37sa typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=rupt_premat_membranes_rpm_sup37sa}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=patho_foet_chron typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=patho_foet_chron}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_retard_croiss typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_retard_croiss}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_macrosom_foetale typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_macrosom_foetale}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_immun_antirh typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_immun_antirh}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_autre_allo_immun typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_autre_allo_immun}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_foet_chron_anasarque_non_immun typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_anasarque_non_immun}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_anasarque_immun typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_anasarque_immun}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_hypoxie_foetale typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_hypoxie_foetale}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_foet_chron_trouble_rcf typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_trouble_rcf}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_foet_chron_mort_foatale_in_utero typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_foet_chron_mort_foatale_in_utero}}</span>
              </td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=patho_mat_foet_av_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=patho_mat_foet_av_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_hta_gravid typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_hta_gravid}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mat_foet_av_trav_preec_moderee typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_preec_moderee}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_preec_severe typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_preec_severe}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_hellp typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_hellp}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_preec_hta typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_preec_hta}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_eclamp typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_eclamp}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_diabete_id typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_diabete_id}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_diabete_nid typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_diabete_nid}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mat_foet_av_trav_steatose_grav typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_steatose_grav}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_herpes_genit typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_herpes_genit}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_condylomes typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_condylomes}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_hep_b typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_hep_b}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_hep_c typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_hep_c}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_vih typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_vih}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_sida typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_sida}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_fievre typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_fievre}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mat_foet_av_trav_gross_prolong typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_gross_prolong}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mat_foet_av_trav_autre typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mat_foet_av_trav_autre}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=autre_motif_cesarienne typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=autre_motif_cesarienne}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=autre_motif_cesarienne_conv typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=autre_motif_cesarienne_conv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=autre_motif_cesarienne_mult typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=autre_motif_cesarienne_mult}}</span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
  <div id="pendantTravail" style="display: none;">
    <table class="main layout">
      <tr>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=hypox_foet_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=hypox_foet_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=hypox_foet_pdt_trav_rcf_isole typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_rcf_isole}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=hypox_foet_pdt_trav_la_teinte typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_la_teinte}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=hypox_foet_pdt_trav_rcf_la typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_rcf_la}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=hypox_foet_pdt_trav_anom_ph_foet typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_anom_ph_foet}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=hypox_foet_pdt_trav_anom_ecg_foet typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_anom_ecg_foet}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=hypox_foet_pdt_trav_procidence_cordon typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=hypox_foet_pdt_trav_procidence_cordon}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=dysto_pres_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=dysto_pres_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_pres_pdt_trav_rot_tete_incomp typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_rot_tete_incomp}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_pres_pdt_trav_siege typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_siege}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_pres_pdt_trav_face typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_face}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_pres_pdt_trav_pres_front typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_pres_front}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_pres_pdt_trav_pres_transv typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_pres_transv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_pres_pdt_trav_autre_pres_anorm typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_pres_pdt_trav_autre_pres_anorm}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=dysto_anom_foet_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=dysto_anom_foet_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_foet_pdt_trav_foetus_macrosome typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_foet_pdt_trav_foetus_macrosome}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_foet_pdt_trav_jumeaux_soudes typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_foet_pdt_trav_jumeaux_soudes}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_foet_pdt_trav_difform_foet typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_foet_pdt_trav_difform_foet}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=echec_decl_travail typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=echec_decl_travail}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=echec_decl_travail_medic typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=echec_decl_travail_medic}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=echec_decl_travail_meca typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=echec_decl_travail_meca}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=echec_decl_travail_sans_prec typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=echec_decl_travail_sans_prec}}</span>
              </td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_deform_pelv typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_deform_pelv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_bassin_retr typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_bassin_retr}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_detroit_sup_retr typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_detroit_sup_retr}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_detroit_moy_retr typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_detroit_moy_retr}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_dispr_foeto_pelv typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_dispr_foeto_pelv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_fibrome_pelv typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_fibrome_pelv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_stenose_cerv typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_stenose_cerv}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_malf_uterine typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_malf_uterine}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_anom_pelv_mat_pdt_trav_autre typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_anom_pelv_mat_pdt_trav_autre}}</span>
              </td>
            </tr>
          </table>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=dysto_dynam_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=dysto_dynam_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_dynam_pdt_trav_demarrage typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_demarrage}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_dynam_pdt_trav_cerv_latence typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_cerv_latence}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_dynam_pdt_trav_arret_dilat typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_arret_dilat}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_dynam_pdt_trav_hypertonie_uter typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_hypertonie_uter}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=dysto_dynam_pdt_trav_dilat_lente_col typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_dilat_lente_col}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_dynam_pdt_trav_echec_travail typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_echec_travail}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=dysto_dynam_pdt_trav_non_engagement typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=dysto_dynam_pdt_trav_non_engagement}}</span>
              </td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=patho_mater_pdt_trav typeEnum="checkbox"}}</td>
              <td class="text">
                <strong>{{mb_label object=$dossier field=patho_mater_pdt_trav}}</strong>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mater_pdt_trav_hemo_sans_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_hemo_sans_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mater_pdt_trav_hemo_avec_trouble_coag typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_hemo_avec_trouble_coag}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_choc_obst typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_choc_obst}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_eclampsie typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_eclampsie}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_rupt_uterine typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_rupt_uterine}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_embolie_amnio typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_embolie_amnio}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_embolie_pulm typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_embolie_pulm}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mater_pdt_trav_complic_acte_obst typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_complic_acte_obst}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_chorio_amnio typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_chorio_amnio}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_infection typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_infection}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_fievre typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_fievre}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">{{mb_field object=$dossier field=patho_mater_pdt_trav_fatigue_mat typeEnum="checkbox"}}</span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_fatigue_mat}}</span>
              </td>
            </tr>
            <tr>
              <td class="narrow">
                <span class="compact">
                  {{mb_field object=$dossier field=patho_mater_pdt_trav_autre_complication typeEnum="checkbox"}}
                </span>
              </td>
              <td class="text">
                <span class="compact">{{mb_label object=$dossier field=patho_mater_pdt_trav_autre_complication}}</span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
  <div id="pendantExpulsion" style="display: none;">
    <table class="main layout">
      <tr>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_non_progr_pres_foetale typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_non_progr_pres_foetale}}</td>
            </tr>
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_dysto_pres_posterieures typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_dysto_pres_posterieures}}</td>
            </tr>
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_dystocie_epaules typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_dystocie_epaules}}</td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_retention_tete typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_retention_tete}}</td>
            </tr>
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_soufrance_foet_rcf typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_soufrance_foet_rcf}}</td>
            </tr>
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_soufrance_foet_rcf_la typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_soufrance_foet_rcf_la}}</td>
            </tr>
          </table>
        </td>
        <td class="thirdPane">
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_echec_forceps_cesar typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_echec_forceps_cesar}}</td>
            </tr>
            <tr>
              <td class="narrow">{{mb_field object=$dossier field=anom_expuls_fatigue_mat typeEnum="checkbox"}}</td>
              <td class="text">{{mb_label object=$dossier field=anom_expuls_fatigue_mat}}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</form>