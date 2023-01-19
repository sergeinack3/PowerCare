{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    GroupePatient.form = getForm('gestion_groupe_patients');
    GroupePatient.elementPrescriptionAutocomplete(getForm('gestion_groupe_patients'), 1, '{{$plage_groupe_patient->elements_prescription}}');

    {{if $element->_id}}
      $('gestion_groupe_patients_check_all_patients').checked = true;
      $('gestion_groupe_patients_check_all_patients').onchange();
    {{/if}}
  });
</script>

{{assign var=lock_add_evt_conflit        value="ssr general lock_add_evt_conflit"|gconf}}
{{assign var=elements_prescription_plage value=$plage_groupe_patient->_ref_elements_prescription}}
{{assign var=sejours_associes            value=$plage_groupe_patient->_ref_sejours_associes}}

<div id="view_groupe_patients">
  <form name="gestion_groupe_patients" method="post" class="prepared">
    <input type="hidden" name="plage_date" value="{{$plage_groupe_patient->_date}}"/>
    <input type="hidden" name="plage_groupe_patient_id" value="{{$plage_groupe_patient->_id}}"/>
    <input type="hidden" name="categorie_groupe_patient_id" value="{{$plage_groupe_patient->categorie_groupe_patient_id}}"/>

    <table class="main tbl">
      <tr>
        <td colspan="8">
          <div class="small-warning">
            {{tr}}CPlageGroupePatient-msg-gestionPatient_info{{/tr}}
          </div>
          {{if $plage_groupe_patient->commentaire}}
            <div class="small-info">
              <strong>{{mb_label object=$plage_groupe_patient field=commentaire}} :</strong>
              {{mb_value object=$plage_groupe_patient field=commentaire}}
            </div>
          {{/if}}
        </td>
      </tr>
      <tr>
        <td colspan="8">
          <fieldset>
            <legend><i class="fas fa-filter"></i> {{tr}}Filter|pl{{/tr}}</legend>

            <input type="hidden" name="libelle_element_id" value="{{$element->_id}}" />

            <div>
              <label for="libelle">{{tr}}CElementPrescriptionToCsarr-element_prescription_id-desc{{/tr}}</label>
              <input type="text" name="libelle" placeholder="&mdash; {{tr}}CPrescription.select_element{{/tr}}" value="{{$element->_view}}" class="autocomplete" />
              <button type="button" class="erase notext singleclick"
                      onclick="GroupePatient.managePatients('{{$plage_groupe_patient->_id}}', '{{$plage_groupe_patient->categorie_groupe_patient_id}}', null, 1);">
                {{tr}}Erase{{/tr}}
              </button>
            </div>
          </fieldset>
        </td>
      </tr>
      <tr>
        <th colspan="8" class="title">
          <button type="button" class="print notext not-printable" onclick="this.form.print();" style="float: left;">
            {{tr}}Print{{/tr}}
          </button>
            {{tr}}CPlageGroupePatient-Patients management of the group range{{/tr}} :
            {{$plage_groupe_patient->_date|date_format:"%A %d/%m/%Y"}} de {{mb_value object=$plage_groupe_patient field=heure_debut}} à
            {{mb_value object=$plage_groupe_patient field=heure_fin}}
        </th>
      </tr>
      <tr>
        <th class="category narrow">
          {{if $sejours && $sejours|@count}}
            <input name="check_all_patients" type="checkbox" onchange="GroupePatient.selectCheckboxes(this.form, $V(this), 'groupe_patient');"/>
          {{/if}}
        </th>
        <th class="category" colspan="4">
          {{mb_colonne class=CSejour field=patient_id order_col=$order_col order_way=$order_way function=GroupePatient.sortBy}}
        </th>
        <th class="category" colspan="4">
          {{mb_colonne class=CSejour field=entree order_col=$order_col order_way=$order_way function=GroupePatient.sortBy}}
        </th>
      </tr>
      {{foreach from=$sejours item=_sejour}}
        {{assign var=sejour_id     value=$_sejour->_id}}
        {{assign var=patient       value=$_sejour->_ref_patient}}
        {{assign var=prescription  value=$_sejour->_ref_prescription_sejour}}
        {{assign var=lines_element value=$prescription->_ref_prescription_lines_element}}
        <tr>
          <td style="text-align: center;">
            <input type="checkbox" name="patient_{{$_sejour->_id}}" class="groupe_patient" value="{{$_sejour->_id}}"
                   {{if $_sejour->_id|in_array:$sejours_collisions && $lock_add_evt_conflit}}style="display:none;" disabled{{/if}}
                    data-already_range="{{if in_array($_sejour->_id, array_keys($sejours_associes))}}1{{else}}0{{/if}}"
                   onchange="GroupePatient.showEvents(this, 'elements_line_{{$_sejour->_id}}');"/>

            {{if $_sejour->_id|in_array:$sejours_collisions}}
              <i class="fas fa-exclamation" onmouseover="ObjectTooltip.createDOM(this, 'event_planned_{{$_sejour->_id}}');"
                 style="cursor: help;">
              </i>

              <div id="event_planned_{{$_sejour->_id}}" style="display: none">
                <table class="tbl">
                  <tr>
                    <th>{{tr}}CPlageGroupePatient-Event already planned for this period{{/tr}}</th>
                  </tr>
                  {{foreach from=$evenements item=_evenement}}
                    {{if $_evenement->sejour_id == $_sejour->_id}}
                      <tr>
                        <td>
                          <i class="fas fa-long-arrow-alt-right"></i>
                          <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}')">
                            {{$_evenement->_ref_prescription_line_element->_ref_element_prescription->_view}}
                             - {{mb_value object=$_evenement field=debut}}
                            ({{mb_value object=$_evenement field=_duree}} {{tr}}common-minute|pl{{/tr}})
                          </span>
                        </td>
                      </tr>
                    {{/if}}
                  {{/foreach}}
                </table>
              </div>
            {{/if}}
          </td>
          <td colspan="4">
            <strong>{{mb_include module=system template=inc_vw_mbobject object=$patient}}</strong>
          </td>
          <td colspan="4">
            <div style="float: right;">
              <span id="show_tag_actes_{{$_sejour->_id}}" class="texticon texticon-acte_csarr"
                    style="padding-right: 5px; display: none;">
              </span>

              <i id="arrow_show_actes_{{$_sejour->_id}}" class="fas fa-arrow-circle-up"
                 style="cursor: pointer; display: none;"
                 onclick="GroupePatient.showEventsByArrow(this, 'elements_line_{{$_sejour->_id}}');"></i>
            </div>

            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
              {{$_sejour->_shortview}}
            </span>
          </td>
        </tr>
        {{foreach from=$lines_element item=_line}}
          {{assign var=element_prescription value=$_line->_ref_element_prescription}}

          {{if in_array($element_prescription->_id, array_keys($elements_prescription_plage))}}
            {{assign var=actes_csarr value=$element_prescription->_ref_csarrs}}
            {{assign var=category_prescription_id value=$element_prescription->category_prescription_id}}
            {{assign var=executants  value=$executants_cats.$category_prescription_id}}

            <tr class="elements_line_{{$_sejour->_id}} element_prescription_{{$element_prescription->_id}}_{{$_sejour->_id}}" style="display: none;">
              <th class="section"></th>
              <th colspan="7" class="section" style="line-height: 20px;">
                <button type="button" class="notext duplicate me-tertiary" style="float: right;"
                        onclick="GroupePatient.duplicateElement('{{$_sejour->_id}}', '{{$element_prescription->_id}}');">
                  {{tr}}CPlageGroupePatient-action-Duplicate all selected CsARR from this element to all elements of the same name{{/tr}}
                </button>
                {{mb_include module=system template=inc_vw_mbobject object=$element_prescription}}
              </th>
            </tr>
            <tr class="elements_line_{{$_sejour->_id}} element_prescription_{{$element_prescription->_id}}_{{$_sejour->_id}}" style="display: none;">
              <th class="category"></th>
              <th class="category" style="width: 10%; text-align: left !important; padding-left: 5px;">
                {{if $actes_csarr && $actes_csarr|@count}}
                  <input name="check_all_actes_{{$_sejour->_id}}" type="checkbox" onchange="GroupePatient.selectCheckboxes(this.form, $V(this), 'acte_selected_{{$element_prescription->_id}}_{{$_sejour->_id}}');"/>
                {{/if}}
                {{mb_label class=CElementPrescriptionToCsarr field=code}}
              </th>
              <th class="category" style="width: 25%;">{{mb_label class=CElementPrescriptionToCsarr field=modulateurs}}</th>
              <th class="category" style="width: 10%;">{{mb_label class=CElementPrescriptionToCsarr field=duree}}</th>
              <th class="category" style="width: 10%;">{{tr}}CEvenementSSR-timetable{{/tr}}</th>
              <th class="category" style="width: 15%;">{{mb_label class=CElementPrescriptionToCsarr field=code_ext_documentaire}}</th>
              <th class="category" style="width: 15%;">{{mb_label class=CElementPrescriptionToCsarr field=type_seance}}</th>
              <th class="category" style="width: 15%;">{{mb_label class=CEvenementSSR field=therapeute_id}}</th>
            </tr>
            {{foreach from=$actes_csarr item=_acte name=list_actes}}
              {{assign var=acte_index value=$smarty.foreach.list_actes.index}}

              <tr class="elements_line_{{$_sejour->_id}} element_prescription_{{$element_prescription->_id}}_{{$_sejour->_id}}"
                  style="display: none;" data-plage_heure_debut="{{$plage_groupe_patient->heure_debut}}">
                <td>
                  <div id="order_arrow_{{$_acte->_id}}_{{$_sejour->_id}}" style="display: none;">
                    <span class="arrow_up" style="display: block;">
                        <i class="fas fa-sort-up graph-rank up-rank-graph"
                           style="{{if $smarty.foreach.list_actes.first}}display: none;{{/if}}"
                           title="{{tr}}CElementPrescriptionToCsarr-Go up 1 rank{{/tr}}"
                           onclick="GroupePatient.changeRank(this, 'up', '{{$actes_csarr|@count}}', 'line_csarr_{{$element_prescription->_id}}_{{$_sejour->_id}}');"></i>
                    </span>
                    <span class="arrow_down" style="display: block;">
                        <i class="fas fa-sort-down graph-rank down-rank-graph"
                           style="{{if $smarty.foreach.list_actes.last}}display: none;{{/if}}"
                           title="{{tr}}CElementPrescriptionToCsarr-action-Get off 1 row{{/tr}}"
                           onclick="GroupePatient.changeRank(this, 'down', '{{$actes_csarr|@count}}', 'line_csarr_{{$element_prescription->_id}}_{{$_sejour->_id}}');"></i>
                    </span>
                  </div>
                </td>
                <td colspan="7">
                  {{mb_include module=ssr template=inc_settings_line_csarr}}
                </td>
              </tr>
            {{foreachelse}}
              <tr class="elements_line_{{$_sejour->_id}} element_prescription_{{$element_prescription->_id}}_{{$_sejour->_id}}" style="display: none;">
                <td colspan="7" class="empty">{{tr}}CPlageGroupePatient-No act for this item{{/tr}}</td>
              </tr>
            {{/foreach}}
          {{/if}}
        {{/foreach}}
      {{foreachelse}}
        <tr>
          <td colspan="7" class="empty">{{tr}}CPlageGroupePatient-No stay available for this range{{/tr}}</td>
        </tr>
      {{/foreach}}
      <tr class="not-printable">
        <td colspan="7" class="button">
          {{if $sejours && $sejours|@count}}
            <button type="button" class="tick" onclick="GroupePatient.confirmValidation(this.form);">
                {{tr}}Validate{{/tr}}
            </button>
          {{/if}}
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<script>
  Main.add(function () {
    GroupePatient.editPatientsGroupe('{{$plage_groupe_patient->_id}}', '{{$plage_date}}');
  })
</script>
