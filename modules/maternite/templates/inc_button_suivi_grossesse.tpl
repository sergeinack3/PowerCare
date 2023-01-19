{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite  script=suivi_grossesse ajax=1}}
{{mb_script module=planningOp script=sejour          ajax=1}}

{{assign var=sejour          value=$consult->_ref_sejour}}
{{assign var=suivi_grossesse value=$consult->_ref_suivi_grossesse}}

{{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD}}
  <table class="main">
    <tr>
      <td class="button">
        <form name="retourDomicile" method="post"
              onsubmit="return onSubmitFormAjax(this, function() {
                submitAll();
                submitConsultWithChrono(64);

                if (window.parent && window.parent.Placement) {
                  window.close();
                  window.parent.Placement.refreshCurrPlacement();
                }
              });">
          <input type="hidden" name="m" value="maternite" />
          <input type="hidden" name="dosql" value="do_retour_domicile" />
          <input type="hidden" name="consult_id" value="{{$consult->_id}}" />
          <button type="button" class="undo oneclick" onclick="this.form.onsubmit();"
                  {{if $consult->chrono == 64}}disabled{{/if}}>{{tr}}CSuiviGrossesse-retour_domicile{{/tr}}</button>
        </form>

        {{if $app->_ref_user->isSageFemme()}}
          <form name="consultGyneco" method="post" onsubmit="submitAll(); submitConsultWithChrono(64); return onSubmitFormAjax(this);">
            <input type="hidden" name="m" value="cabinet" />
            <input type="hidden" name="dosql" value="do_consult_now" />
            <input type="hidden" name="callback" value="SuiviGrossesse.afterCreationConsultNow" />
            <input type="hidden" name="patient_id" value="{{$consult->patient_id}}" />
            <input type="hidden" name="grossesse_id" value="{{$consult->grossesse_id}}" />

            <script>
              Main.add(function () {
                var form = getForm("consultGyneco");
                new Url("maternite", "grossesse_praticien_autocomplete")
                  .autoComplete(form.elements._prat_autocomplete, null, {
                    minChars:           2,
                    method:             "get",
                    select:             "view",
                    dropdown:           true,
                    afterUpdateElement: function (field, selected) {
                      $V(field.form._prat_id, selected.getAttribute("id").split("-")[2]);
                      $V(field, selected.down("span.view").getText().trim());
                    },
                    callback:           function (input, queryString) {
                      return queryString;
                    }
                  });
              });
            </script>

            <button type="button" class="new singleclick"
                    onclick="Modal.open('grossesse_praticien',
                    {title: $T('CSuiviGrossesse-Choose a gyneco'),
                    width: '350px', height: '300px',
                    showClose: true});">
              {{tr}}CSuiviGrossesse-consultation_gyneco{{/tr}}
            </button>

            <div id="grossesse_praticien" style="display: none;">
              <table class="main form">
                <tr>
                  <th>{{tr}}CSuiviGrossesse-Gyneco{{/tr}}</th>
                  <td>
                    <input type="text" name="_prat_autocomplete" placeholder="&mdash; {{tr}}Choose{{/tr}}" />
                    <input type="hidden" name="_prat_id" />
                  </td>
                </tr>
                <tr>
                  <td class="button" colspan="2">
                    <button type="button" class="tick singleclick"
                            onclick="if (!$V(form._prat_id)) {
                                      alert($T('CSuiviGrossesse-fill_gyneco'));
                                      return;
                                    }
                      Control.Modal.close();
                      this.form.onsubmit();">
                      {{tr}}Validate{{/tr}}
                    </button>
                  </td>
                </tr>
              </table>
            </div>
          </form>
        {{/if}}

        {{if $consult->sejour_id && ($suivi_grossesse->type_suivi === "urg" || $app->_ref_user->isPraticien() || $app->_ref_user->isSageFemme())}}
          <button type="button" class="right singleclick"
                  onclick="SuiviGrossesse.hospitalize('{{$consult->_id}}');">{{tr}}CSuiviGrossesse-hospitaliser_patiente{{/tr}}</button>
          <button type="button" class="search"
                  onclick="Sejour.editModal('{{$consult->sejour_id}}');">{{tr}}CSejour-See sejour{{/tr}}</button>
          <button type="button" class="tick oneclick"
                  onclick="SuiviGrossesse.declencherAccouchement('{{$consult->_id}}');">{{tr}}CSuiviGrossesse-declencher_accouchement{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
{{/if}}
