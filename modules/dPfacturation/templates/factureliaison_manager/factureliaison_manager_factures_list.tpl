{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=facture_count_alert value=false}}
{{mb_default var=facture_nb_element  value=false}}
<table class="main">
  <tbody>
  <tr>
    <td class="factureliaison-container">
      {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_count_info"
                   count_info=$facture_count_alert nb_element=$facture_nb_element}}
      {{foreach from=$factures item=_facture}}
        {{assign var=patient value=$_facture->_ref_patient}}
        {{assign var=praticien value=$_facture->_ref_praticien}}
        {{assign var=facture_class
                 value="factureliaison-facture factureliaison-`$patient->_id`-`$praticien->_id`-`$_facture->_class`"}}
        {{assign var=type value="FC"}}
        {{if $_facture|instanceof:'Ox\Mediboard\Facturation\CFactureEtablissement'}}
          {{assign var=type value="FE"}}
        {{/if}}

        {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_element"
          type=$type praticien=$praticien label=$patient sublabel=$_facture r_action_icon="fa-list" l_action_icon="fa-link"
          r_action_callback="FactuTools.FactuLiaisonManager.showChildren(this)"
          l_action_callback="FactuTools.FactuLiaisonManager.selectFacture(this, '`$_facture->_guid`')"
          l_action_state="disabled" type_callback="Facture.edit('`$_facture->_id`', '`$_facture->_class`')"
          container_class="$facture_class factureliaison-`$_facture->_guid`" type_title=$_facture->_class
          r_action_title="CFactureLiaison.Manager show children" l_action_title="CFactureLiaison.Manager link to the selected object"
          r_action_sec_title="CFactureLiaison.Manager unlink all" r_action_sec_icon="fa-unlink"
          r_action_sec_callback="FactuTools.FactuLiaisonManager.unlinkAllByFacture(this, '`$_facture->_guid`')"}}
        <div class="factureliaison-children">
          {{foreach from=$_facture->_ref_consults item=_consultation}}
            {{assign var=consultation_selected value=false}}
            {{if $_consultation->_guid === $selected_guid}}
              {{assign var=consultation_selected value=true}}
            {{/if}}
            {{assign var=type value="C"}}
            {{if $_consultation->sejour_id}}
              {{assign var=type value="CS"}}
            {{/if}}
            {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_element"
              type=$type praticien=$_consultation->_ref_praticien label=$_consultation->_ref_patient
              sublabel=$_consultation->_ref_plageconsult r_action_icon="fa-unlink"
              r_action_callback="FactuTools.FactuLiaisonManager.unlinkObject(this, '`$_consultation->_guid`', '`$_facture->_guid`')"
              type_callback="Consultation.editModal('`$_consultation->_id`', 'facturation')" container_class=$facture_class
              selected=$consultation_selected type_title=$_consultation->_class
              r_action_title="CFactureLiaison.Manager unlink `$_consultation->_class`"}}
          {{/foreach}}
          {{foreach from=$_facture->_ref_evts item=_evt}}
            {{assign var=evt_selected value=false}}
            {{if $_evt->_guid === $selected_guid}}
              {{assign var=evt_selected value=true}}
            {{/if}}
            {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_element"
              type="E" praticien=$_evt->_ref_praticien label=$_evt->_ref_patient
              sublabel=$_evt r_action_icon="fa-unlink"
              r_action_callback="FactuTools.FactuLiaisonManager.unlinkObject(this, '`$_evt->_guid`', '`$_facture->_guid`')"
              type_callback="Consultation.editModal('`$_evt->_id`', 'facturation')" container_class=$facture_class
              selected=$evt_selected type_title=$_evt->_class r_action_title="CFactureLiaison.Manager unlink `$_evt->_class`"}}
          {{/foreach}}
        </div>
      {{foreachelse}}
        <div class="empty">
          {{tr}}CFactureCabinet.none{{/tr}}
        </div>
      {{/foreach}}
    </td>
  </tr>
  </tbody>
  <thead>
  <tr>
    <th class="title me-text-align-center">
      {{tr}}CFacture{{/tr}}
    </th>
  </tr>
  </thead>
</table>
