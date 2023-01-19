{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=tdb          ajax=true}}
{{mb_script module=maternite script=dossierMater ajax=true}}
{{mb_script module=cerfa script=Cerfa            ajax=true}}

{{mb_default var=show_header      value=false}}
{{mb_default var=is_tdb_maternite value=true}}
{{mb_default var=is_consultation  value=false}}
{{mb_default var=with_buttons     value=1}}

{{assign var=dossier_perinatal value="maternite CGrossesse audipog"|gconf}}
{{assign var=patient           value=$grossesse->_ref_parturiente}}

<script>
  Main.add(function () {
    {{if $grossesse->_id}}
      DossierMater.timelinePregnancy('{{$grossesse->_id}}');
      DossierMater.reloadHistorique('{{$grossesse->_id}}');
    {{/if}}

    {{if $dossier_perinatal}}
      DossierMater.refreshArea('{{$grossesse->_id}}', "dossier_perinatal");
    {{/if}}

    {{if $grossesse->_id}}
      // Si une fois la page chargée, le container est plus grand que l'écran, on adapte la taille de la timeline
      setTimeout(function() {
        var diffHeight = Math.floor($('timeline_container').getBoundingClientRect().height - $('timeline_wrapper').getBoundingClientRect().height);
        var timeline = $('timeline_wrapper').down('.main-timeline');
        if (diffHeight > 4) {
          timeline.style.height = timeline.getBoundingClientRect().height + diffHeight + 'px';
        }
      }, 1500);
    {{/if}}
  });
</script>

<table id="main layout" style="width: 100%;">
  {{if $show_header}}
    <tr>
      <td colspan="2">
        {{mb_include module=maternite template=inc_dossier_mater_header show_buttons=0 with_buttons=0 css="width: 100%;"}}
      </th>
    </tr>
  {{/if}}
  <tr>
    <td id="timeline_container" class="halfPane me-valign-top">
      <table id="timeline_wrapper" class="tbl me-margin-top-0">
        <tr>
          <th class="title">
            <span id="title_timeline">{{tr}}CGrossesse-Timeline{{/tr}}</span>

              {{if !$is_tdb_maternite && $is_consultation}}
                <button id="button_timeline_patient" type="button" class="fa-eye me-float-right"
                        onclick="loadTimeline('{{$consult->_id}}', 'timeline_pregnancy'); DossierMater.showButtonTimeline('button_timeline_prenancy');">
                  {{tr}}CConsultation-Timeline patient{{/tr}}</button>

                <button id="button_timeline_prenancy" type="button" class="fa-eye me-float-right"
                        style="display: none;"
                        onclick="DossierMater.timelinePregnancy('{{$grossesse->_id}}'); DossierMater.showButtonTimeline('button_timeline_patient');">
                  {{tr}}CConsultation-Timeline prenancy{{/tr}}</button>
              {{/if}}

              <button type="button" class="search me-float-right"
                      onclick="DossierMater.printSummary('{{$grossesse->_id}}');">
                {{tr}}CGrossesse-action-Summary sheet{{/tr}}</button>

              <button type="button" class="grossesse me-float-right"
                      onclick="Tdb.editGrossesse('{{$grossesse->_id}}', '{{$grossesse->parturiente_id}}', DossierMater.timelinePregnancy.curry('{{$grossesse->_id}}'));">
                {{tr}}CGrossesse{{/tr}}</button>

              <button type="button" class="edit me-float-right"
                      title="{{tr}}Cerfa-10112-05-desc{{/tr}}"
                      onclick="Cerfa.editCerfa('10112-05', '{{$patient->_class}}', '{{$patient->_id}}');">
                {{tr}}Cerfa{{/tr}}</button>
          </th>
        </tr>
        <tr>
          <td>
            <div id="timeline_pregnancy"></div>
          </td>
        </tr>
      </table>
    </td>
    <td class="halfPane me-valign-top">
      {{if $dossier_perinatal}}
        <table class="tbl me-margin-top-0">
          <tr>
            <th class="title">
              {{tr}}CDossierPerinat{{/tr}}

              <button type="button" class="print me-float-right me-small"
                      onclick="DossierMater.print('{{$grossesse->_id}}');">{{tr}}Print{{/tr}}</button>

              <button type="button" class="edit me-float-right me-small"
                      onclick="DossierMater.updateFolder('{{$grossesse->_id}}');">
                {{tr}}CGrossesse-action-Update perinatal folder{{/tr}}
              </button>
            </th>
          </tr>
          <tr>
            <td>
              <div id="dossier_mater_dossier_perinatal"></div>
            </td>
          </tr>
        </table>
      {{/if}}

      {{if "forms"|module_active}}
        <fieldset>
          <legend>{{tr}}CExClass|pl{{/tr}}</legend>
          <div id="list_formulaires">
            {{unique_id var=forms_uid}}

            <script>
              Main.add(function () {
                ExObject.loadExObjects("{{$grossesse->_class}}", "{{$grossesse->_id}}", "list-ex_objects-{{$forms_uid}}", 0.5);
              });
            </script>
            <div id="list-ex_objects-{{$forms_uid}}"></div>
          </div>
        </fieldset>
      {{/if}}

      {{if $grossesse->_id && ($is_tdb_maternite || $with_buttons)}}
        <table class="tbl">
          <tr>
            <th class="title">{{tr}}common-Action|pl{{/tr}}</th>
          </tr>
          <tr>
            <td class="button">
              <button type="button" class="sejour_create not-printable"
                      onclick="Tdb.editSejour('', '{{$grossesse->_id}}', '{{$grossesse->parturiente_id}}', DossierMater.timelinePregnancy.curry('{{$grossesse->_id}}'));">
                {{tr}}CSejour-title-create{{/tr}}
              </button>
              <br class="me-no-display" />

              <button type="button" class="consultation_create not-printable"
                      onclick="Tdb.editRdvConsult('', '{{$grossesse->_id}}', '{{$grossesse->parturiente_id}}', DossierMater.timelinePregnancy.curry('{{$grossesse->_id}}'));">
                {{tr}}CConsultation-title-create{{/tr}}
              </button>
              <br class="me-no-display" />

              <button type="button" class="accouchement_create not-printable"
                      onclick="Tdb.editAccouchement('', '', '{{$grossesse->_id}}', '{{$grossesse->parturiente_id}}', DossierMater.timelinePregnancy.curry('{{$grossesse->_id}}'));">
                {{tr}}CAccouchement-title-create{{/tr}}
              </button>
              <br />

              <form name="editConsultImm" method="post" onsubmit="return onSubmitFormAjax(this)">
                <input type="hidden" name="m" value="cabinet" />
                <input type="hidden" name="dosql" value="do_consult_now" />
                <input type="hidden" name="callback" value="DossierMater.afterCreationConsultNow" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="grossesse_id" value="" />
                <input type="hidden" name="_prat_id" value="" />
              </form>

              <select name="prat_id" id="selector_prat_imm" style="width:16em;">
                <option value="">&mdash; Choisir un praticien</option>
                {{foreach from=$prats item=_prat}}
                  <option value="{{$_prat->_id}}"
                          {{if $_prat->_id == $user->_id && $user->_is_professionnel_sante}}selected{{/if}}>{{$_prat}}</option>
                {{/foreach}}
              </select>
              <button type="button" onclick="DossierMater.consultNow($V('selector_prat_imm'), '{{$grossesse->_id}}', DossierMater.timelinePregnancy.curry('{{$grossesse->_id}}'));"
                      class="consultation_create not-printable me-tertiary">{{tr}}CConsultation-action-Immediate{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      {{/if}}
    </td>
  </tr>
</table>
