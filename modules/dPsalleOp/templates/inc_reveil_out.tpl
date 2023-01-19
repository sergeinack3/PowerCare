{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=use_sortie_reveil_reel value="dPsalleOp COperation use_sortie_reveil_reel"|gconf}}
{{assign var=use_poste value=$conf.dPplanningOp.COperation.use_poste}}

<script>
  Main.add(function () {
    {{if $isImedsInstalled}}
    ImedsResultsWatcher.loadResults();
    {{/if}}
  });

  orderTabout = function (col, way) {
    orderTabReveil(col, way, 'out');
  };
</script>

<table class="tbl me-no-align me-small">
  <tr>
    <th>{{mb_colonne class="COperation" field="salle_id" order_col=$order_col order_way=$order_way function=orderTabout}}</th>
    <th>{{mb_colonne class="COperation" field="chir_id" order_col=$order_col order_way=$order_way function=orderTabout}}</th>
    <th class="narrow">
        {{mb_colonne class="COperation" field="_patient" order_col=$order_col order_way=$order_way function=orderTabout}}
    </th>
    <th class="narrow me-small-fields">
        {{me_form_field}}
          <input type="text" name="_seek_patient_preop" value="" class="seek_patient" onkeyup="seekPatient(this);" onchange="seekPatient(this);" />
        {{/me_form_field}}
    </th>
    {{if "dPsalleOp SSPI_cell see_ctes"|gconf}}
      <th>{{tr}}SSPI_cell.see_ctes{{/tr}}</th>
    {{/if}}
    <th class="narrow">Dossier</th>
    {{if "dPsalleOp SSPI_cell see_type_anesth"|gconf}}
      <th>{{mb_colonne class="COperation" field="type_anesth" order_col=$order_col order_way=$order_way function=orderTabout}}</th>
    {{/if}}
    {{if $use_poste}}
      <th>{{tr}}SSPI.Poste{{/tr}}</th>
    {{/if}}
    {{if "dPsalleOp SSPI_cell see_localisation"|gconf}}
      <th>{{tr}}SSPI.Chambre{{/tr}}</th>
    {{/if}}
    <th>{{mb_colonne class="COperation" field="sortie_salle" order_col=$order_col order_way=$order_way function=orderTabout}}</th>
    <th>{{mb_colonne class="COperation" field="entree_reveil" order_col=$order_col order_way=$order_way function=orderTabout}}</th>
    {{if $use_sortie_reveil_reel}}
      <th style="width: 15%" class="me-ws-wrap">
          {{mb_colonne class="COperation" field="sortie_reveil_possible" order_col=$order_col order_way=$order_way function=orderTabout}}
      </th>
      <th style="width: 15%">
          {{mb_colonne class="COperation" field="sortie_reveil_reel" order_col=$order_col order_way=$order_way function=orderTabout}}
      </th>
    {{else}}
      <th style="width: 15%">
          {{mb_colonne class="COperation" field="sortie_reveil_reel" order_col=$order_col order_way=$order_way function=orderTabout}}
      </th>
    {{/if}}
    <th style="width: 15%">
        {{mb_colonne class="COperation" field="sortie_sans_sspi" order_col=$order_col order_way=$order_way function=orderTabout}}
    </th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$listOperations key=key item=_operation}}
    {{assign var=patient value=$_operation->_ref_patient}}
    {{assign var=sejour_id value=$_operation->sejour_id}}
    {{assign var=_operation_id value=$_operation->_id}}
    <tr>
      <td>{{$_operation->_ref_salle->_shortview}}</td>

      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir classe="me-wrapped"}}
      </td>

      <td class="text" colspan="2">
        {{mb_include module=system template=inc_object_notes object=$_operation float="right"}}
        <div style="float: right;">
          {{if $isImedsInstalled}}
            {{mb_include module=Imeds template=inc_sejour_labo link="#1" sejour=$_operation->_ref_sejour float="none"}}
          {{/if}}
        </div>

        <span
          class="CPatient-view {{if !$_operation->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_operation->_ref_sejour->septique}}septique{{/if}}"
          onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
          {{$patient}}
        </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </td>
      {{if "dPsalleOp SSPI_cell see_ctes"|gconf}}
        <td class="text" style="text-align: center;">
          {{if $patient->_ref_constantes_medicales->poids}}
            {{mb_value object=$patient->_ref_constantes_medicales field=poids}} kg
          {{else}}
            &mdash;
          {{/if}}
          /
          {{if $patient->_ref_constantes_medicales->taille}}
            {{mb_value object=$patient->_ref_constantes_medicales field=taille}} cm
          {{else}}
            &mdash;
          {{/if}}
        </td>
      {{/if}}
      <td>
        <button class="notext button soins me-tertiary me-small"
                onclick="showDossierSoins('{{$sejour_id}}','{{$_operation_id}}');">Dossier de soins
        </button>
        {{if $isImedsInstalled}}
          <button class="labo button notext me-tertiary me-small"
                  onclick="showDossierSoins('{{$sejour_id}}','{{$_operation_id}}','Imeds');">Labo
          </button>
        {{/if}}
        <button type="button" class="injection notext me-tertiary me-small"
                onclick="Operation.dossierBloc('{{$_operation_id}}', true, 'surveillance_sspi')">Dossier de bloc
        </button>
        {{mb_include module=patients template=inc_antecedents_allergies show_atcd=0 dossier_medical=$patient->_ref_dossier_medical patient_guid=$patient->_guid}}
      </td>
      {{if "dPsalleOp SSPI_cell see_type_anesth"|gconf}}
        <td class="text">{{mb_value object=$_operation field="type_anesth"}}</td>
      {{/if}}
      {{if $use_poste}}
        <td class="me-small-fields">
          {{mb_include module=salleOp template=inc_form_toggle_poste_sspi type="out" sspi_id=$sspi_id}}
        </td>
      {{/if}}
      {{if "dPsalleOp SSPI_cell see_localisation"|gconf}}
        <td class="text">
          {{mb_include module=hospi template=inc_placement_sejour sejour=$_operation->_ref_sejour which="curr"}}
        </td>
      {{/if}}
      <td class="button me-small-fields">
        {{if $can->edit}}
          {{assign var=validate_datetimes    value='Ox\Mediboard\PlanningOp\COperation::getValidatingTimings'|static_call:"`$_operation_id`":"sortie_salle"}}
          {{assign var=validate_datetime_min value=$validate_datetimes.min}}
          {{assign var=validate_datetime_max value=$validate_datetimes.max}}
          {{assign var=last_timing           value=$validate_datetimes.last_timing}}
          <script>
            Main.add(function () {
              var options = {
                datePicker: true,
                timePicker: true,
                minHours:   '{{$validate_datetime_min|date_format:"%H"}}',
                maxHours:   '{{$validate_datetime_max|date_format:"%H"}}',
              };

              var dates = {
                limit: {
                  start: '{{$validate_datetime_min}}',
                  stop:  '{{$validate_datetime_max}}'
                }
              };
              Calendar.regField(getForm('editSortieBlocOutFrm{{$_operation_id}}').sortie_salle, dates, options);
            });
          </script>
          <form name="editSortieBlocOutFrm{{$_operation_id}}" action="?" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_planning_aed"/>
            {{mb_key object=$_operation}}
            <input type="hidden" name="del" value="0"/>
            {{mb_field object=$_operation field=sortie_salle register=true form="editSortieBlocOutFrm$_operation_id" onchange="if (SalleOp.checkTimingOperation('`$_operation->_ref_sejour->entree`', '`$_operation->_ref_sejour->sortie`', this, '`$_operation->_id`', '`$last_timing`')) { submitSortieForm(this.form); }"}}
          </form>
        {{else}}
          {{mb_value object=$_operation field="sortie_salle"}}
        {{/if}}
      </td>
      <td class="button me-small-fields">
        {{if $_operation->entree_reveil}}
          {{if $can->edit && !$_operation->sortie_reveil_possible}}
            <form name="editEntreeReveilOutFrm{{$_operation_id}}" action="?" method="post" class="prepared">
              <input type="hidden" name="m" value="planningOp"/>
              <input type="hidden" name="dosql" value="do_planning_aed"/>
              {{mb_key object=$_operation}}
              <input type="hidden" name="del" value="0"/>
              {{mb_field object=$_operation field=entree_reveil register=true form="editEntreeReveilOutFrm$_operation_id" onchange="submitSortieForm(this.form);"}}
            </form>
          {{else}}
            {{mb_value object=$_operation field="entree_reveil"}}
          {{/if}}
        {{else}}
          pas de passage SSPI
        {{/if}}

        {{foreach from=$_operation->_ref_affectations_personnel.reveil item=curr_affectation}}
          <br/>
          {{$curr_affectation->_ref_personnel->_ref_user}}
        {{/foreach}}
      </td>
      <td class="button me-small-fields">
        <form name="editSortieReveilOutFrm{{$_operation_id}}" action="?" method="post" class="prepared">
          <input type="hidden" name="m" value="planningOp"/>
          <input type="hidden" name="dosql" value="do_planning_aed"/>
          {{mb_key object=$_operation}}
          <input type="hidden" name="del" value="0"/>
          {{mb_field object=$_operation field="entree_reveil" hidden=1}}
          {{mb_field object=$_operation field="sortie_reveil_reel" hidden=1}}
          {{mb_field object=$_operation field="sortie_locker_id" hidden=1}}
          {{if $modif_operation && !$_operation->sortie_locker_id}}
            {{mb_field object=$_operation field=sortie_reveil_possible register=true form="editSortieReveilOutFrm$_operation_id"
            onchange="if (!this.value && !this.form.entree_reveil.value) { \$V(this.form.sortie_reveil_reel, '') } submitSortie(this.form);"}}
          {{else}}
            {{if $_operation->sortie_locker_id && !$use_sortie_reveil_reel}}
              <span onmouseover="ObjectTooltip.createDOM(this, 'info_locker_{{$_operation_id}}')">
                {{mb_field object=$_operation field=sortie_reveil_possible hidden=1}}
                {{mb_value object=$_operation field=sortie_reveil_possible}}
                <button type="button" class="cancel notext me-tertiary me-small" title="Annuler la validation"
                        onclick="$V(this.form.sortie_reveil_possible, ''); $V(this.form.sortie_reveil_reel, ''); $V(this.form.sortie_locker_id, ''); submitSortie(this.form);"></button>
              </span>
              <div id="info_locker_{{$_operation_id}}" style="display: none">
                {{tr}}COperation-sortie_locker_id{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_sortie_locker classe="me-wrapped"}}
                <span style="padding-left: 10px;">
                  {{mb_include module=system template=inc_object_history object=$_operation}}
                </span>
              </div>
            {{else}}
              <span onmouseover="ObjectTooltip.createDOM(this, 'info_locker_{{$_operation_id}}')">
                {{mb_value object=$_operation field=sortie_reveil_possible}}
              </span>
              <div id="info_locker_{{$_operation_id}}" style="display: none">
                {{tr}}COperation-sortie_locker_id{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_sortie_locker classe="me-wrapped"}}
                <span style="padding-left: 10px;">
                  {{mb_include module=system template=inc_object_history object=$_operation}}
                </span>
              </div>
            {{/if}}
          {{/if}}
        </form>
      </td>
      {{if $use_sortie_reveil_reel}}
        <td class="button me-small-fields">
          <form name="editSortieReveilReelOutFrm{{$_operation_id}}" action="?" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_planning_aed"/>
            {{mb_key object=$_operation}}
            <input type="hidden" name="del" value="0"/>
            {{mb_field object=$_operation field=entree_reveil hidden=1}}
            {{mb_field object=$_operation field=sortie_reveil_possible hidden=1}}
            {{if $modif_operation}}
              {{mb_field object=$_operation field=sortie_reveil_reel register=true form="editSortieReveilReelOutFrm$_operation_id"
              onchange="if (!this.value && !this.form.entree_reveil.value) { \$V(this.form.sortie_reveil_possible, ''); } submitSortieForm(this.form);"}}
            {{else}}
              {{mb_value object=$_operation field=sortie_reveil_reel}}
            {{/if}}
          </form>
        </td>
      {{/if}}
      <td class="button me-small-fields">
        <form name="editSortieSansSSPIOutFrm{{$_operation_id}}" action="?" method="post" class="prepared">
          {{mb_class object=$_operation}}
          {{mb_key object=$_operation}}
          {{if $modif_operation}}
            {{mb_field object=$_operation field=sortie_sans_sspi register=true form="editSortieSansSSPIOutFrm$_operation_id"
            onchange="submitSortieForm(this.form);"}}
          {{else}}
            {{mb_value object=$_operation field=sortie_sans_sspi}}
          {{/if}}

          {{if $_operation->sortie_sans_sspi}}
            {{mb_include module=forms template=inc_widget_ex_class_register object=$_operation event_name=sortie_sans_sspi_auto cssStyle="display: inline-block;"}}
          {{/if}}
        </form>
      </td>
      <td>
        <button type="button" class="print notext me-tertiary me-small"
                onclick="printDossier('{{$sejour_id}}', '{{$_operation_id}}')"></button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="23" class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
