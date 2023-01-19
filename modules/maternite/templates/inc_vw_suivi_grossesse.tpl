{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite  script=dossierMater    ajax=1}}
{{mb_script module=maternite  script=suivi_grossesse ajax=1}}
{{mb_script module=planningOp script=sejour          ajax=1}}
{{mb_script module=planningOp script=operation       ajax=1}}

{{assign var=suivi_grossesse value=$consult->_ref_suivi_grossesse}}

<script>
  Main.add(function () {
    SuiviGrossesse.suivi_grossesse_id = '{{$suivi_grossesse->_id}}';
    var textarea = $("motif_grossesse");
    textarea.setResizable();
  });
</script>

<form name="editConstante" method="post">
  {{mb_key   object=$constante}}
  {{mb_class object=$constante}}
  {{mb_field object=$constante field=user_id value=$app->user_id hidden=true}}
  {{mb_field object=$constante field=hauteur_uterine hidden=true}}
  <input type="hidden" name="datetime" value="now">
  <input type="hidden" name="patient_id" value="{{$consult->patient_id}}">
  <input type="hidden" name="context_class" value="{{$consult->_class}}">
  <input type="hidden" name="context_id" value="{{$consult->_id}}">
</form>

<table class="main layout">
  <tr>
    <td colspan="2">
      <table class="form">
        <tr>
          <th>{{mb_label object=$suivi_grossesse field=type_suivi}}</th>
          <td>{{mb_field object=$suivi_grossesse field=type_suivi rows=1 onchange="SuiviGrossesse.updateHiddenInput(this, '`$suivi_grossesse->_guid`', 'type_suivi');"}}</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td {{if !"forms"|module_active}}colspan="2"{{else}}class="halfPane"{{/if}}>
      <fieldset>
        <legend>{{mb_label object=$consult field=motif}}</legend>
        {{mb_field object=$consult field=motif id="motif_grossesse"
        aidesaisie="validateOnBlur: 0" onchange="SuiviGrossesse.updateHiddenInput(this, '`$suivi_grossesse->_guid`', 'motif');"}}
      </fieldset>
    </td>
    {{if "forms"|module_active}}
    <td>
        {{unique_id var=unique_id_exam_forms}}

        <script>
          Main.add(function(){
            ExObject.loadExObjects("{{$consult->_class}}", "{{$consult->_id}}", "{{$unique_id_exam_forms}}", 0.5);
          });
        </script>

        <fieldset id="list-ex_objects">
          <legend>{{tr}}CExClass|pl{{/tr}}</legend>
          <div id="{{$unique_id_exam_forms}}"></div>
        </fieldset>
    </td>
    {{/if}}
  </tr>
</table>

<form name="Suivi-Grossesse-{{$suivi_grossesse->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);" onchange="onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="maternite"/>
  <input type="hidden" name="dosql" value="do_aed_suivi_grossesse"/>
  {{mb_key   object=$consult}}
  {{mb_key   object=$suivi_grossesse}}

  <input type="hidden" name="motif" id="Suivi-Grossesse-{{$suivi_grossesse->_guid}}_motif" value="{{$consult->motif}}" onchange="this.form.onchange();" />
  <input type="hidden" name="type_suivi" id="Suivi-Grossesse-{{$suivi_grossesse->_guid}}_type_suivi" value="{{$suivi_grossesse->type_suivi}}" onchange="this.form.onchange();" />
  <table class="main layout">
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CSuiviGrossesse-exam_general{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              {{me_form_field nb_cells=2 mb_object=$suivi_grossesse mb_field="auscultation_cardio_pulm" class="quarterPane"}}
                {{mb_field object=$suivi_grossesse field=auscultation_cardio_pulm
                style="width: 12em;" emptyLabel="CSuiviGrossesse.auscultation_cardio_pulm."}}
              {{/me_form_field}}

              {{me_form_field nb_cells=2 rowspan="2" mb_object=$suivi_grossesse mb_field="evenements_anterieurs" class="quarterPane"}}
                {{mb_field object=$suivi_grossesse field=evenements_anterieurs
                  form="Suivi-Grossesse-`$suivi_grossesse->_guid`" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 mb_object=$suivi_grossesse mb_field=examen_seins class="quarterPane"}}
                {{mb_field object=$suivi_grossesse field=examen_seins
                style="width: 12em;" emptyLabel="CSuiviGrossesse.examen_seins."}}
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_field nb_cells=2 mb_object=$suivi_grossesse mb_field=circulation_veineuse class="quarterPane"}}
                {{mb_field object=$suivi_grossesse field=circulation_veineuse
                style="width: 12em;" emptyLabel="CSuiviGrossesse.circulation_veineuse."}}
              {{/me_form_field}}

              {{me_form_field nb_cells=2 rowspan="2" mb_object=$suivi_grossesse mb_field="rques_examen_general" class="quarterPane"}}
                {{mb_field object=$suivi_grossesse field=rques_examen_general
                form="Suivi-Grossesse-`$suivi_grossesse->_guid`" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
              {{/me_form_field}}
            </tr>
            <tr>
              {{me_form_bool nb_cells=2 class="quarterPane" mb_object=$suivi_grossesse mb_field="oedeme_membres_inf"}}
                {{mb_field object=$suivi_grossesse field=oedeme_membres_inf default=""}}
              {{/me_form_bool}}
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{tr}}CSuiviGrossesse-exam_genico{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=bruit_du_coeur}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=bruit_du_coeur
                style="width: 12em;" emptyLabel="CSuiviGrossesse.bruit_du_coeur."}}
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=presentation_position}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=presentation_position
                style="width: 12em;" emptyLabel="CSuiviGrossesse.presentation_position."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=col_normal}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=col_normal style="width: 12em;" emptyLabel="CSuiviGrossesse.col_normal."}}
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=presentation_etat}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=presentation_etat
                style="width: 12em;" emptyLabel="CSuiviGrossesse.presentation_etat."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">
                <span class="compact">{{mb_label object=$suivi_grossesse field=longueur_col}}</span>
              </th>
              <td class="quarterPane">
                <span>
                  {{mb_field object=$suivi_grossesse field=longueur_col
                  style="width: 12em;" emptyLabel="CSuiviGrossesse.longueur_col."}}
                </span>
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=segment_inferieur}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=segment_inferieur
                style="width: 12em;" emptyLabel="CSuiviGrossesse.segment_inferieur."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">
                <span class="compact">{{mb_label object=$suivi_grossesse field=position_col}}</span>
              </th>
              <td class="quarterPane">
                <span>
                  {{mb_field object=$suivi_grossesse field=position_col
                  style="width: 12em;" emptyLabel="CSuiviGrossesse.position_col."}}
                </span>
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=membranes}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=membranes
                style="width: 12em;" emptyLabel="CSuiviGrossesse.membranes."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">
                <span class="compact">{{mb_label object=$suivi_grossesse field=dilatation_col}}</span>
              </th>
              <td class="quarterPane">
                <span>
                  {{mb_field object=$suivi_grossesse field=dilatation_col
                  style="width: 12em;" emptyLabel="CSuiviGrossesse.dilatation_col."}}
                  {{mb_field object=$suivi_grossesse field=dilatation_col_num form="Suivi-Grossesse-`$suivi_grossesse->_guid`" size=2 increment=true min=0}} cm
                </span>
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=bassin}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=bassin
                style="width: 12em;" emptyLabel="CSuiviGrossesse.bassin."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">
                <span class="compact">{{mb_label object=$suivi_grossesse field=consistance_col}}</span>
              </th>
              <td class="quarterPane">
                <span>
                  {{mb_field object=$suivi_grossesse field=consistance_col
                  style="width: 12em;" emptyLabel="CSuiviGrossesse.consistance_col."}}
                </span>
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=examen_genital}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=examen_genital
                style="width: 12em;" emptyLabel="CSuiviGrossesse.examen_genital."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane"></th>
              <td class="quarterPane"></td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=hauteur_uterine}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=hauteur_uterine form="Suivi-Grossesse-`$suivi_grossesse->_guid`" size=2 increment=true min=0 onchange="SuiviGrossesse.submitConstante(this);"}}
                cm
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$suivi_grossesse field=col_commentaire}}</th>
              <td colspan="3">{{mb_field object=$suivi_grossesse field=col_commentaire
                form="Suivi-Grossesse-`$suivi_grossesse->_guid`" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$suivi_grossesse field=rques_exam_gyneco_obst}}</th>
              <td colspan="3">{{mb_field object=$suivi_grossesse field=rques_exam_gyneco_obst
                form="Suivi-Grossesse-`$suivi_grossesse->_guid`" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}</td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{tr}}CSuiviGrossesse-functionnal_signs{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=metrorragies}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=metrorragies default=""}}</td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=troubles_digestifs}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=troubles_digestifs default=""}}</td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=leucorrhees}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=leucorrhees default=""}}</td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=troubles_urinaires}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=troubles_urinaires default=""}}</td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=contractions_anormales}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=contractions_anormales default=""}}</td>
              <th class="quarterPane" rowspan="3">{{mb_label object=$suivi_grossesse field=autres_anomalies}}</th>
              <td class="quarterPane" rowspan="3">{{mb_field object=$suivi_grossesse field=autres_anomalies
                form="Suivi-Grossesse-`$suivi_grossesse->_guid`" aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}</td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=mouvements_foetaux}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=mouvements_foetaux default=""}}</td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=mouvements_actifs}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=mouvements_actifs
                style="width: 12em;" emptyLabel="CSuiviGrossesse.presentation_position."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=hypertension}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=hypertension default=""}}</td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CSuiviGrossesse-exam_comp{{/tr}}</legend>
          <table class="form me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=frottis}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=frottis
                style="width: 12em;" emptyLabel="CSuiviGrossesse.frottis."}}
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=glycosurie}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=glycosurie
                style="width: 12em;" emptyLabel="CSuiviGrossesse.glycosurie."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=echographie}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=echographie style="width: 12em;" emptyLabel="CSuiviGrossesse.echographie."}}
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=leucocyturie}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=leucocyturie style="width: 12em;" emptyLabel="CSuiviGrossesse.leucocyturie."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=prelevement_bacterio}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=prelevement_bacterio
                style="width: 12em;" emptyLabel="CSuiviGrossesse.prelevement_bacterio."}}
              </td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=albuminurie}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=albuminurie style="width: 12em;" emptyLabel="CSuiviGrossesse.albuminurie."}}
              </td>
            </tr>
            <tr>
              <th class="quarterPane"></th>
              <td class="quarterPane"></td>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=nitrites}}</th>
              <td class="quarterPane">
                {{mb_field object=$suivi_grossesse field=nitrites style="width: 12em;" emptyLabel="CSuiviGrossesse.nitrites."}}
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$suivi_grossesse field=autre_exam_comp}}</th>
              <td
                colspan="3">{{mb_field object=$suivi_grossesse field=autre_exam_comp  form="Suivi-Grossesse-`$suivi_grossesse->_guid`"
                aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}</td>
            </tr>
            <tr>
              <th class="quarterPane">{{mb_label object=$suivi_grossesse field=jours_arret_travail}}</th>
              <td class="quarterPane">{{mb_field object=$suivi_grossesse field=jours_arret_travail}}</td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{mb_label object=$consult field=rques}}</legend>
          {{mb_field object=$consult field=rques
          aidesaisie="validateOnBlur: 0"}}
        </fieldset>
        <fieldset>
          <legend>{{mb_label object=$suivi_grossesse field=conclusion}}</legend>
          {{mb_field object=$suivi_grossesse field=conclusion rows=10  form="Suivi-Grossesse-`$suivi_grossesse->_guid`"
          aidesaisie="filterWithDependFields: false, validateOnBlur: 0"}}
        </fieldset>
      </td>
    </tr>
  </table>
</form>
