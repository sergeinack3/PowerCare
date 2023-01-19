{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=object_count_alert value=false}}
{{mb_default var=object_nb_element  value=false}}
<table class="main">
  <tbody>
  <tr>
    <td class="factureliaison-container">
      {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_count_info"
                   count_info=$object_count_alert nb_element=$object_nb_element}}
      {{if $consultations|@count === 0 && $evts|@count === 0}}
        <div class="empty">
          {{tr}}CConsultation.none{{/tr}}
        </div>
        <div class="empty">
          {{tr}}CEvenementPatient.none{{/tr}}
        </div>
      {{/if}}
      {{foreach from=$consultations item=_consultation}}
        {{assign var=consultation_selected value=false}}
        {{if $_consultation->_guid === $selected_guid}}
          {{assign var=consultation_selected value=true}}
        {{/if}}
        {{assign var=patient value=$_consultation->_ref_patient}}
        {{assign var=praticien value=$_consultation->_ref_praticien}}
        {{assign var=signature_target value="`$patient->_id`-`$praticien->_id`"}}
        {{assign var=type value="C"}}
        {{if $_consultation->sejour_id}}
          {{assign var=type value="CS"}}
          {{assign var=signature_target value="$signature_target-CFactureEtablissement"}}
        {{else}}
          {{assign var=signature_target value="$signature_target-CFactureCabinet"}}
        {{/if}}
        {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_element"
          type=$type praticien=$praticien label=$patient sublabel=$_consultation->_ref_plageconsult r_action_icon="fa-arrow-right"
          r_action_callback="FactuTools.FactuLiaisonManager.selectObject(this, '`$_consultation->_guid`', '$signature_target')"
          type_callback="Consultation.editModal('`$_consultation->_id`', 'facturation')"
          selected=$consultation_selected type_title=$_consultation->_class r_action_title="CFactureLiaison.Manager link to an invoice"}}
      {{/foreach}}
      {{foreach from=$evts item=_evt}}
        {{assign var=evt_selected value=false}}
        {{if $_evt->_guid === $selected_guid}}
          {{assign var=evt_selected value=true}}
        {{/if}}
        {{assign var=patient value=$_evt->_ref_patient}}
        {{assign var=praticien value=$_evt->_ref_praticien}}
        {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_element"
          type="E" praticien=$praticien label=$patient sublabel=$_evt->libelle r_action_icon="fa-arrow-right"
          r_action_callback="FactuTools.FactuLiaisonManager.selectObject(this, '`$_evt->_guid`', '`$patient->_id`-`$praticien->_id`-CFactureCabinet')"
          type_callback="Facture.editEvt('`$_evt->_guid`')" container_class="factureliaison-`$patient->_id`-`$praticien->_id`"
          selected=$evt_selected type_title=$_evt->_class r_action_title="CFactureLiaison.Manager link to an invoice"}}
      {{/foreach}}
    </td>
  </tr>
  </tbody>
  <thead>
  <tr>
    <th class="title me-text-align-center">
      {{tr}}CConsultation{{/tr}} / {{tr}}CEvenementPatient{{/tr}}
    </th>
  </tr>
  </thead>
</table>
