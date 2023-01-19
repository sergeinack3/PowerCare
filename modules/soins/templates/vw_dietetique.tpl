{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tab-dietetique', true);
    {{if "forms"|module_active}}
      ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "list-ex_objects-nutrition", 0.5);
    {{/if}}
  });
</script>

<ul id="tab-dietetique" class="control_tabs">
  <li>
    <a href="#nutrition">{{tr}}soins.tab.nutrition{{/tr}}</a>
  </li>
</ul>
<div id="nutrition" style="display: none;">
  <table class="main">
    <tr>
      <td class="halfPane">
        <fieldset>
          <legend>{{tr}}CMediusers-back-prescription_elements{{/tr}}</legend>
          {{if $hide_old_lines}}
            <button type="button" class="search me-tertiary" style="float: right;" onclick="loadDietetique('{{$sejour->_id}}', 0);">
              {{tr}}CPrescription-action-Display completed prescription|pl{{/tr}} ({{$hidden_lines_count}})
            </button>
          {{else}}
            <button type="button" class="search me-tertiary" style="float: right;" onclick="loadDietetique('{{$sejour->_id}}', 1);">
              {{tr}}pref-hide_old_lines{{/tr}} ({{$hidden_lines_count}})
            </button>
          {{/if}}
          <table class="tbl">
            <tr>
              <th style="width:25%;" colspan="2">{{tr}}CElementPrescription-libelle-court{{/tr}}</th>
              <th style="width:35%;">
                {{tr}}CMomentUnitaire-back-prises{{/tr}}
              </th>
              <th style="width: 10%; vertical-align: middle;">
                {{tr}}common-Start{{/tr}}
              </th>
              <th style="width:10%;">{{tr}}CPrescriptionLineElement-duree{{/tr}}</th>
              <th style="width:{{if $prescription->object_id}}10{{else}}8{{/if}}%;">
                {{tr}}CActeCCAM-executant_id{{/tr}}
              </th>
              <th style="width:10%;">{{tr}}CActeCCAM-executant_id-court{{/tr}}.</th>
            </tr>
            {{foreach from=$prescription->_ref_prescription_lines_element item=line_element}}
              {{assign var=category_id value=$line_element->_ref_element_prescription->category_prescription_id}}
              {{assign var=category value=$line_element->_ref_element_prescription->_ref_category_prescription}}
              {{assign var=element value=$category->chapitre}}
              {{mb_include module=prescription template=inc_vw_line_element_lite _line_element=$line_element
                  nodebug=true readonly=true perop=true without_table=true}}
              {{foreachelse}}
              <tr>
                <td class="empty" colspan="7">
                  {{tr}}CPrescriptionLineElement.none{{/tr}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        </fieldset>
      </td>
      <td>
        {{if "forms"|module_active}}
          <fieldset>
            <legend>{{tr}}CExClass|pl{{/tr}}</legend>
            <div id="list-ex_objects-nutrition"></div>
          </fieldset>
        {{/if}}
        <fieldset>
          <legend>{{tr}}CPrescription-Transmission|pl{{/tr}} / {{tr}}soins.tab.obs{{/tr}}</legend>
          <div id="suivi_nutrition">
            {{assign var=user value=$app->_ref_user}}
            {{assign var=isPraticien value=$user->isPraticien()}}
            {{mb_include module=hospi template=inc_add_trans_obs count_macrocibles=0 dietetique=1}}

            <table class="tbl">
              <tr>
                <th rowspan="2" class="narrow">{{tr}}Type{{/tr}}</th>
                <th rowspan="2">{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
                <th rowspan="2">{{mb_title class=CTransmissionMedicale field=object_class}}</th>
                <th colspan="3" style="width: 50%">{{mb_title class=CTransmissionMedicale field=text}}</th>
                <th rowspan="2" class="narrow"></th>
              </tr>
              <tr>
                <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.data{{/tr}}</th>
                <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.action{{/tr}}</th>
                <th class="section" style="width: 17%">{{tr}}CTransmissionMedicale.type.result{{/tr}}</th>
              </tr>
              {{foreach from=$sejour->_ref_suivi_medical item=_suivi}}
                <tr class="{{if is_array($_suivi)}}
               print_transmission {{if $_suivi.0->cancellation_date}}hatching{{/if}}
             {{if $_suivi.0->degre == "high"}}
               transmission_haute
             {{/if}}
             {{if $_suivi.0->object_class}}
               {{$_suivi.0->_ref_object->_guid}}
             {{/if}}
           {{else}}
             {{$_suivi->_guid}}
             {{if $_suivi|instanceof:'Ox\Mediboard\Hospi\CTransmissionMedicale'}}
               {{if $_suivi->cancellation_date}}hatching{{/if}}
               {{if $_suivi->degre == "high"}}
                 transmission_haute
               {{/if}}
             {{elseif $_suivi|instanceof:'Ox\Mediboard\Cabinet\CConsultation' && $_suivi->type == "entree"}}
               print_observation
               consultation_entree
             {{elseif $_suivi|instanceof:'Ox\Mediboard\Hospi\CObservationMedicale'}}
               print_observation {{if $_suivi->cancellation_date}}hatching{{/if}}
               {{if $_suivi->degre == "info"}}
                 observation_info
               {{elseif $_suivi->degre == "high"}}
                 observation_urgente
               {{/if}}
             {{/if}}
           {{/if}}">
                  {{mb_include module=hospi template=inc_line_suivi show_patient=false nodebug=true readonly=false}}
                </tr>
              {{foreachelse}}
                <tr>
                  <td colspan="9" class="empty">{{tr}}CTransmissionMedicale.none{{/tr}}</td>
                </tr>
              {{/foreach}}
            </table>
          </div>
        </fieldset>
      </td>
    </tr>
  </table>
</div>