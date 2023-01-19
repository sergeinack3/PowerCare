{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Resume-accouchement-travail-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />

  <script>
    Main.add(function () {
      Control.Tabs.create('tab-travail', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
    });
  </script>

  <ul id="tab-travail" class="control_tabs small">
    <li><a href="#declenchement">Déclenchement</a></li>
    <li><a href="#surveillance">Surveillance du travail</a></li>
    <li><a href="#therapeutique">Thérapeutique au cours du travail</a></li>
  </ul>
  <div id="declenchement" style="display: none;">
    <table class="form me-no-align me-no-box-shadow">
      <tr>
        <th class="halfPane me-padding-2">{{mb_label object=$dossier field=datetime_declenchement}}</th>
        <td class="me-padding-2">
          {{mb_field object=$dossier field=datetime_declenchement form="Resume-accouchement-travail-`$dossier->_guid`" register=true}}
        </td>
      </tr>
    </table>
    <hr class="me-no-display" />
    <table class="main layout">
      <tr>
        <td class="thirdPane">
          <fieldset>
            <legend>Motifs</legend>
            <table class="form me-no-align me-no-box-shadow">
              <tr>
                <th class="narrow">{{mb_field object=$dossier field=motif_decl_conv typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_conv}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_gross_prol typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_gross_prol}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_patho_mat typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_patho_mat}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_patho_foet typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_patho_foet}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_rpm typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_rpm}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_mort_iu typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_mort_iu}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=motif_decl_img typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_img}}</td>
              </tr>
              <tr>
                <th rowspan="2" class="me-valign-top">{{mb_field object=$dossier field=motif_decl_autre typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=motif_decl_autre}}</td>
              </tr>
              <tr>
                <td>
                  {{mb_label object=$dossier field=motif_decl_autre_details style="display:none"}}
                  {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-motif_decl_autre_details"}}
                  {{mb_field object=$dossier field=motif_decl_autre_details
                  style="width: 16em;" placeholder=$placeholder}}
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
        <td class="thirdPane">
          <fieldset>
            <legend>Moyens</legend>
            <table class="form me-no-align me-no-box-shadow">
              <tr>
                <th class="narrow">{{mb_field object=$dossier field=moyen_decl_ocyto typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_ocyto}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=moyen_decl_prosta typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_prosta}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=moyen_decl_autre_medic typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_autre_medic}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=moyen_decl_meca typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_meca}}</td>
              </tr>
              <tr>
                <th>{{mb_field object=$dossier field=moyen_decl_rupture typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_rupture}}</td>
              </tr>
              <tr>
                <th rowspan="2" class="me-valign-top">{{mb_field object=$dossier field=moyen_decl_autre typeEnum=checkbox}}</th>
                <td>{{mb_label object=$dossier field=moyen_decl_autre}}</td>
              </tr>
              <tr>
                <td>
                  {{mb_label object=$dossier field=moyen_decl_autre_details style="display:none"}}
                  {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-moyen_decl_autre_details"}}
                  {{mb_field object=$dossier field=moyen_decl_autre_details
                  style="width: 16em;" placeholder=$placeholder}}
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
        <td class="thirdPane">
          <fieldset>
            <legend>{{mb_label object=$dossier field=score_bishop}}</legend>
            <table class="form me-no-align me-no-box-shadow">
              <tr>
                <td>{{mb_field object=$dossier field=score_bishop}}</td>
              </tr>
            </table>
          </fieldset>
          <fieldset>
            <legend>{{mb_label object=$dossier field=remarques_declenchement}}</legend>
            <table class="form me-no-align me-no-box-shadow">
              <tr>
                <td>
                  {{if !$print}}
                    {{mb_field object=$dossier field=remarques_declenchement form=Resume-accouchement-travail-`$dossier->_guid`}}
                  {{else}}
                    {{mb_value object=$dossier field=remarques_declenchement}}
                  {{/if}}
                </td>
              </tr>
            </table>
          </fieldset>
        </td>
      </tr>
    </table>
  </div>
  <div id="surveillance" class="me-padding-2" style="display: none;">
    <table class="form me-no-align me-no-box-shadow me-small-form">
      <tr>
        <th>{{mb_label object=$dossier field=surveillance_travail}}</th>
        <td>
          {{mb_field object=$dossier field=surveillance_travail
          style="width: 16em;" emptyLabel="CGrossesse.surveillance_travail."}}
        </td>
      </tr>
      <tr>
        <th>Si surveillance para-clinique,</th>
        <td></td>
      </tr>
      <tr>
        <th rowspan="2" class="me-valign-top">{{mb_field object=$dossier field=tocographie typeEnum=checkbox}}</th>
        <td>
          {{mb_label object=$dossier field=tocographie}}
          {{mb_label object=$dossier field=type_tocographie style="display: none;"}}
          {{mb_field object=$dossier field=type_tocographie
          style="width: 16em;" emptyLabel="CGrossesse.type_tocographie."}}
        </td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=anomalie_contractions}}
          {{mb_field object=$dossier field=anomalie_contractions
          style="width: 16em;" emptyLabel="CGrossesse.anomalie_contractions."}}
        </td>
      </tr>
      <tr>
        <th rowspan="3">{{mb_field object=$dossier field=rcf typeEnum=checkbox}}</th>
        <td>
          {{mb_label object=$dossier field=rcf}}
          {{mb_label object=$dossier field=type_rcf style="display: none;"}}
          {{mb_field object=$dossier field=type_rcf
          style="width: 16em;" emptyLabel="CGrossesse.type_rcf."}}
        </td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=desc_trace_rcf}}
          {{mb_field object=$dossier field=desc_trace_rcf
          style="width: 16em;" emptyLabel="CGrossesse.desc_trace_rcf."}}
        </td>
      </tr>
      <tr>
        <td class="compact">
          Si tracé jugé suspect ou pathologique,
          {{mb_label object=$dossier field=anomalie_rcf}}
          {{mb_field object=$dossier field=anomalie_rcf
          style="width: 16em;" emptyLabel="CGrossesse.anomalie_rcf."}}
        </td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=ecg_foetal typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=ecg_foetal}}</td>
      </tr>
      <tr>
        <th class="compact">{{mb_field object=$dossier field=anomalie_ecg_foetal typeEnum=checkbox}}</th>
        <td class="compact">{{mb_label object=$dossier field=anomalie_ecg_foetal}}</td>
      </tr>
      <tr>
        <th rowspan="3">{{mb_field object=$dossier field=prelevement_sang_foetal typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=prelevement_sang_foetal}}</td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=anomalie_ph_sang_foetal}}
          {{mb_field object=$dossier field=anomalie_ph_sang_foetal default=""}}
          {{mb_label object=$dossier field=detail_anomalie_ph_sang_foetal style="display:none"}}
          {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-detail_anomalie_ph_sang_foetal"}}
          {{mb_field object=$dossier field=detail_anomalie_ph_sang_foetal
          style="width: 16em;" placeholder=$placeholder}}
          {{mb_label object=$dossier field=valeur_anomalie_ph_sang_foetal style="display:none"}}
          {{mb_field object=$dossier field=valeur_anomalie_ph_sang_foetal}}
        </td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=anomalie_lactates_sang_foetal}}
          {{mb_field object=$dossier field=anomalie_lactates_sang_foetal default=""}}
          {{mb_label object=$dossier field=detail_anomalie_lactates_sang_foetal style="display:none"}}
          {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-detail_anomalie_lactates_sang_foetal"}}
          {{mb_field object=$dossier field=detail_anomalie_lactates_sang_foetal
          style="width: 16em;" placeholder=$placeholder}}
          {{mb_label object=$dossier field=valeur_anomalie_lactates_sang_foetal style="display:none"}}
          {{mb_field object=$dossier field=valeur_anomalie_lactates_sang_foetal}}
        </td>
      </tr>
      <tr>
        <th rowspan="2">{{mb_field object=$dossier field=oxymetrie_foetale typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=oxymetrie_foetale}}</td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=anomalie_oxymetrie_foetale}}
          {{mb_field object=$dossier field=anomalie_oxymetrie_foetale default=""}}
          {{mb_label object=$dossier field=detail_anomalie_oxymetrie_foetale style="display:none"}}
          {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-detail_anomalie_oxymetrie_foetale"}}
          {{mb_field object=$dossier field=detail_anomalie_oxymetrie_foetale
          style="width: 16em;" placeholder=$placeholder}}
        </td>
      </tr>
      <tr>
        <th rowspan="2">{{mb_field object=$dossier field=autre_examen_surveillance typeEnum=checkbox}}</th>
        <td>
          {{mb_label object=$dossier field=autre_examen_surveillance}}
          {{mb_label object=$dossier field=desc_autre_examen_surveillance style="display:none"}}
          {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_examen_surveillance"}}
          {{mb_field object=$dossier field=desc_autre_examen_surveillance
          style="width: 16em;" placeholder=$placeholder}}
        </td>
      </tr>
      <tr>
        <td class="compact">
          {{mb_label object=$dossier field=anomalie_autre_examen_surveillance}}
          {{mb_field object=$dossier field=anomalie_autre_examen_surveillance default=""}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=rques_surveillance_travail}}</th>
        <td colspan="2">
          {{if !$print}}
            {{mb_field object=$dossier field=rques_surveillance_travail form=Resume-accouchement-travail-`$dossier->_guid`}}
          {{else}}
            {{mb_value object=$dossier field=rques_surveillance_travail}}
          {{/if}}
        </td>
      </tr>
    </table>
  </div>
  <div id="therapeutique" class="me-padding-2" style="display: none;">
    <table class="form me-no-align me-no-box-shadow me-small-form">
      <tr>
        <th>{{mb_label object=$dossier field=therapeutique_pdt_travail}}</th>
        <td>{{mb_field object=$dossier field=therapeutique_pdt_travail default=""}}</td>
      </tr>
      <tr>
        <th class="halfPane">Si oui,</th>
        <td></td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=antibio_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=antibio_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=antihypertenseurs_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=antihypertenseurs_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=antispasmodiques_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=antispasmodiques_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=tocolytiques_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=tocolytiques_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=ocytociques_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=ocytociques_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=opiaces_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=opiaces_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=sedatifs_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=sedatifs_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=amnioinfusion_pdt_travail typeEnum=checkbox}}</th>
        <td>{{mb_label object=$dossier field=amnioinfusion_pdt_travail}}</td>
      </tr>
      <tr>
        <th>{{mb_field object=$dossier field=autre_therap_pdt_travail typeEnum=checkbox}}</th>
        <td>
          {{mb_label object=$dossier field=autre_therap_pdt_travail}}
          {{mb_label object=$dossier field=desc_autre_therap_pdt_travail style="display:none"}}
          {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-desc_autre_therap_pdt_travail"}}
          {{mb_field object=$dossier field=desc_autre_therap_pdt_travail
          style="width: 16em;" placeholder=$placeholder}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$dossier field=rques_therap_pdt_travail}}</th>
        <td
          colspan="2">
          {{if !$print}}
            {{mb_field object=$dossier field=rques_therap_pdt_travail form=Resume-accouchement-travail-`$dossier->_guid`}}
          {{else}}
            {{mb_value object=$dossier field=rques_therap_pdt_travail}}
          {{/if}}
        </td>
      </tr>
    </table>
  </div>
</form>
