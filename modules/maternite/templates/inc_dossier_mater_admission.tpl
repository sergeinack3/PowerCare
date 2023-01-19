{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  listForms = [
    getForm("Admission-complement-{{$dossier->_guid}}"),
    getForm("Admission-constantes-{{$dossier->_guid}}"),
    getForm("Admission-examen-entree-{{$dossier->_guid}}"),
    getForm("Admission-examen-comp-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  calculateScoreBishop = function (form) {
    var dilatation = form.score_bishop_dilatation.value ? form.score_bishop_dilatation.value : 0;
    var longueur = form.score_bishop_longueur.value ? form.score_bishop_longueur.value : 0;
    var consistance = form.score_bishop_consistance.value ? form.score_bishop_consistance.value : 0;
    var position = form.score_bishop_position.value ? form.score_bishop_position.value : 0;
    var presentation = form.score_bishop_presentation.value ? form.score_bishop_presentation.value : 0;

    var total = parseInt(dilatation) + parseInt(longueur) + parseInt(consistance) + parseInt(position) + parseInt(presentation);

    $V(form.exam_entree_indice_bishop, total);
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
      <fieldset class="me-small">
        <legend>
          Informations sur l'admission
        </legend>
        <div id="dossier_mater_infos_admission">
          {{mb_include module=maternite template=inc_dossier_mater_infos_admission}}
        </div>
      </fieldset>
      <form name="Admission-complement-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset class="me-small">
          <legend>Complément sur l'admission</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=adm_sage_femme_resp_id}}</th>
              <td>{{mb_field object=$dossier field=adm_sage_femme_resp_id options=$listSageFemme}}</td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$dossier field=ag_admission}}
                {{mb_label object=$dossier field=ag_jours_admission style="display: none;"}}
              </th>
              <td>
                {{mb_field object=$dossier field=ag_admission}} SA
                {{mb_field object=$dossier field=ag_jours_admission}} j
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=motif_admission}}</th>
              <td>
                {{mb_field object=$dossier field=motif_admission
                style="width: 16em;" emptyLabel="CDossierPerinat.motif_admission."}}
              </td>
            </tr>
            <tr>
              <th>Si membranes rompues à l'admission,</th>
              <td></td>
            </tr>
            <tr>
              <th>
                <span class="compact">
                  {{mb_label object=$dossier field=ag_ruptures_membranes}}
                  {{mb_label object=$dossier field=ag_jours_ruptures_membranes style="display: none;"}}
                </span>
              </th>
              <td>
                <span class="compact">
                  {{mb_field object=$dossier field=ag_ruptures_membranes}} SA
                  {{mb_field object=$dossier field=ag_jours_ruptures_membranes}} j
                </span>
              </td>
            </tr>
            <tr>
              <th>
                <span class="compact">
                  {{mb_label object=$dossier field=delai_rupture_travail_jours}}
                  {{mb_label object=$dossier field=delai_rupture_travail_heures style="display: none;"}}
                </span>
              </th>
              <td>
                <span class="compact">
                  {{mb_field object=$dossier field=delai_rupture_travail_jours}} j
                  {{mb_field object=$dossier field=delai_rupture_travail_heures}} h
                </span>
              </td>
            </tr>
            <tr>
              <th><span class="compact">{{mb_label object=$dossier field=date_ruptures_membranes}}</span></th>
              <td>
                <span class="compact">
                  {{mb_field object=$dossier field=date_ruptures_membranes form=Admission-complement-`$dossier->_guid` register=true}}
                </span>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=rques_admission}}</th>
              <td>
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_admission form=Admission-complement-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_admission}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
    <td class="halfPane">
      <fieldset class="me-small">
        <legend>Examen d'entrée</legend>
        {{assign var=constantes value=$dossier->_ref_adm_mater_constantes}}
        {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:'list_constantes'}}
        <form name="Admission-constantes-{{$dossier->_guid}}" action="?" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$constantes}}
          {{mb_key   object=$constantes}}

          {{mb_field object=$constantes field=patient_id hidden=true}}
          {{mb_field object=$constantes field=context_class hidden=true}}
          {{mb_field object=$constantes field=context_id hidden=true}}
          {{mb_field object=$constantes field=datetime hidden=true}}
          {{mb_field object=$constantes field=user_id hidden=true}}
          {{mb_field object=$constantes field=_unite_ta hidden=1}}

          <input type="hidden" name="_count_changes" value="0" />
          <input type="hidden" name="_object_guid" value="{{$dossier->_guid}}">
          <input type="hidden" name="_object_field" value="adm_mater_constantes_id">

          <table class="form me-no-box-shadow me-no-align me-small-form">
            <tr>
              <th class="quarterPane">
                {{mb_label object=$constantes field=poids}}
                <small class="opacity-50">(kg)</small>
              </th>
              <td class="quarterPane">
                {{mb_field object=$constantes field=poids size=3}}
              </td>
              <th class="quarterPane">
                {{mb_label object=$constantes field=variation_poids}}
                <small class="opacity-50">(kg)</small>
              </th>
              <td class="quarterPane">
                {{mb_field object=$constantes field=variation_poids size=3}}
              </td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$constantes field=co_expire}}
                <small class="opacity-50">(ppm)</small>
              </th>
              <td>
                {{mb_field object=$constantes field=co_expire size=3}}
              </td>
              <th>
                {{mb_label object=$constantes field=ta}}
                <small class="opacity-50">({{$constants_list.ta.unit}})</small>
              </th>
              <td>
                {{mb_field object=$constantes field=_ta_systole size=3}}
                /
                {{mb_field object=$constantes field=_ta_diastole size=3}}
              </td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$constantes field=temperature}}
              </th>
              <td>
                {{mb_field object=$constantes field=temperature size=3}}
              </td>
              <th>
                {{mb_label object=$constantes field=hauteur_uterine}}
                <small class="opacity-50">(cm)</small>
              </th>
              <td>
                {{mb_field object=$constantes field=hauteur_uterine size=3}}
              </td>
            </tr>
          </table>
        </form>
        <form name="Admission-examen-entree-{{$dossier->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this);">
          {{mb_class object=$dossier}}
          {{mb_key   object=$dossier}}
          <input type="hidden" name="_count_changes" value="0" />
          <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
          <table class="form me-no-box-shadow me-no-align me-small-form">
            <tr>
              <th class="quarterPane">{{mb_label object=$dossier field=exam_entree_oedeme}}</th>
              <td class="quarterPane">{{mb_field object=$dossier field=exam_entree_oedeme}}</td>
              <th>{{mb_label object=$dossier field=exam_entree_indice_bishop}}</th>
              <td>
                {{mb_field object=$dossier field=exam_entree_indice_bishop}}
                <button type="button" class="change not-printable me-tertiary"
                        onclick="calculateScoreBishop(this.form);">{{tr}}CDossierPerinat-action-Update score{{/tr}}</button>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_bruits_du_coeur}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_bruits_du_coeur}}</td>
              <td colspan="2" rowspan="6">
                <table class="form me-no-align me-no-box-shadow">
                  <tr>
                    <td>
                      <table class="tbl">
                        <tr>
                          <th class="title" colspan="5">
                            {{mb_label object=$dossier field=score_bishop}}
                          </th>
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-Bishop score coefficient{{/tr}}</th>
                          <th class="category">0</th>
                          <th class="category">1</th>
                          <th class="category">2</th>
                          <th class="category">3</th>
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-score_bishop_dilatation{{/tr}}</th>
                          {{foreach from=$dossier->_specs.score_bishop_dilatation->_list item=_dilatation}}
                            <td>
                              <label>
                                <input type="radio" name="score_bishop_dilatation-view"
                                       onchange="$V(this.form.score_bishop_dilatation, this.value);"
                                       value="{{$_dilatation}}"
                                       {{if $_dilatation == $dossier->score_bishop_dilatation}}checked{{/if}}/>
                                {{tr}}CDossierPerinat.score_bishop_dilatation.{{$_dilatation}}{{/tr}}
                              </label>
                            </td>
                          {{/foreach}}
                          <input type="hidden" name="score_bishop_dilatation" value="{{$dossier->score_bishop_dilatation}}" />
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-score_bishop_longueur{{/tr}}</th>
                          {{foreach from=$dossier->_specs.score_bishop_longueur->_list item=_longueur}}
                            <td>
                              <label>
                                <input type="radio" name="score_bishop_longueur-view"
                                       onchange="$V(this.form.score_bishop_longueur, this.value);"
                                       value="{{$_longueur}}"
                                       {{if $_longueur == $dossier->score_bishop_longueur}}checked{{/if}}/>
                                {{tr}}CDossierPerinat.score_bishop_longueur.{{$_longueur}}{{/tr}}
                              </label>
                            </td>
                          {{/foreach}}
                          <input type="hidden" name="score_bishop_longueur" value="{{$dossier->score_bishop_longueur}}" />
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-score_bishop_consistance{{/tr}}</th>
                          {{foreach from=$dossier->_specs.score_bishop_consistance->_list item=_consistance}}
                            <td>
                              <label>
                                <input type="radio" name="score_bishop_consistance-view"
                                       onchange="$V(this.form.score_bishop_consistance, this.value);"
                                       value="{{$_consistance}}"
                                       {{if $_consistance == $dossier->score_bishop_consistance}}checked{{/if}}/>
                                {{tr}}CDossierPerinat.score_bishop_consistance.{{$_consistance}}{{/tr}}
                              </label>
                            </td>
                          {{/foreach}}
                          <td></td>
                          <input type="hidden" name="score_bishop_consistance" value="{{$dossier->score_bishop_consistance}}" />
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-score_bishop_position{{/tr}}</th>
                          {{foreach from=$dossier->_specs.score_bishop_position->_list item=_position}}
                            <td>
                              <label>
                                <input type="radio" name="score_bishop_position-view"
                                       onchange="$V(this.form.score_bishop_position, this.value);"
                                       value="{{$_position}}"
                                       {{if $_position == $dossier->score_bishop_position}}checked{{/if}}/>
                                {{tr}}CDossierPerinat.score_bishop_position.{{$_position}}{{/tr}}
                              </label>
                            </td>
                          {{/foreach}}
                          <td></td>
                          <input type="hidden" name="score_bishop_position" value="{{$dossier->score_bishop_position}}" />
                        </tr>
                        <tr>
                          <th class="category">{{tr}}CDossierPerinat-score_bishop_presentation{{/tr}}</th>
                          {{foreach from=$dossier->_specs.score_bishop_presentation->_list item=_presentation}}
                            <td>
                              <label>
                                <input type="radio" name="score_bishop_presentation-view"
                                       onchange="$V(this.form.score_bishop_presentation, this.value);"
                                       value="{{$_presentation}}"
                                       {{if $_presentation == $dossier->score_bishop_presentation}}checked{{/if}}/>
                                {{tr}}CDossierPerinat.score_bishop_presentation.{{$_presentation}}{{/tr}}
                              </label>
                            </td>
                          {{/foreach}}
                          <input type="hidden" name="score_bishop_presentation" value="{{$dossier->score_bishop_presentation}}" />
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_mvt_actifs_percus}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_mvt_actifs_percus}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_contractions}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_contractions}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_presentation}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_presentation}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_col}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_col}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_liquide_amnio}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_liquide_amnio}}</td>
            </tr>
          </table>
        </form>
      </fieldset>
      <form name="Admission-examen-comp-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset class="me-small">
          <legend>Examen complémentaires</legend>
          <table class="form me-no-align me-no-box-shadow me-small-form">
            <tr>
              <th rowspan="2" class="quarterPane">{{mb_label object=$dossier field=exam_entree_prelev_urine}}</th>
              <td rowspan="2" class="narrow">{{mb_field object=$dossier field=exam_entree_prelev_urine default=""}}</td>
              <th class="narrow"><span class="compact">{{mb_label object=$dossier field=exam_entree_proteinurie}}</span></th>
              <td>{{mb_field object=$dossier field=exam_entree_proteinurie}}</td>
            </tr>
            <tr>
              <th><span class="compact">{{mb_label object=$dossier field=exam_entree_glycosurie}}</span></th>
              <td>{{mb_field object=$dossier field=exam_entree_glycosurie}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_prelev_vaginal}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_prelev_vaginal default=""}}</td>
              <td colspan="2">
                {{mb_label object=$dossier field=exam_entree_prelev_vaginal_desc style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-exam_entree_prelev_vaginal_desc"}}
                {{mb_field object=$dossier field=exam_entree_prelev_vaginal_desc
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_rcf}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_rcf default=""}}</td>
              <td colspan="2">
                {{mb_label object=$dossier field=exam_entree_rcf_desc style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-exam_entree_rcf_desc"}}
                {{mb_field object=$dossier field=exam_entree_rcf_desc
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_amnioscopie}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_amnioscopie default=""}}</td>
              <td colspan="2">
                {{mb_label object=$dossier field=exam_entree_amnioscopie_desc style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-exam_entree_amnioscopie_desc"}}
                {{mb_field object=$dossier field=exam_entree_amnioscopie_desc
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=exam_entree_autres}}</th>
              <td>{{mb_field object=$dossier field=exam_entree_autres default=""}}</td>
              <td colspan="2">
                {{mb_label object=$dossier field=exam_entree_autres_desc style="display:none"}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-exam_entree_autres_desc"}}
                {{mb_field object=$dossier field=exam_entree_autres_desc
                style="width: 16em;" placeholder=$placeholder}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$dossier field=rques_exam_entree}}</th>
              <td colspan="3">
                {{if !$print}}
                  {{mb_field object=$dossier field=rques_exam_entree form=Admission-examen-comp-`$dossier->_guid`}}
                {{else}}
                  {{mb_value object=$dossier field=rques_exam_entree}}
                {{/if}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
  </tr>
</table>
