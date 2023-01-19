{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination step=$nb_facture_par_page total=$facture_count current=$page
change_page="TdbFacturiere.refreshList.curry(getForm('`$facture_switch`-filter'), '`$facture_switch`-list')"}}
<table class="tbl" id="tdb_facturiere_{{$facture_switch}}">
    <thead>
    <tr>
        <th colspan="9" style="text-align: left">
            {{mb_include module=facturation template="tdb_cotation/tdb_cotation_active_filter" filters=$active_filter}}
        </th>
    </tr>
    <tr>
        <th colspan="9" class="narrow tdb-facturiere-multi-actions" id="tdb_facturiere_{{$facture_switch}}_multi_result"
            style="text-align: left">
            <button class="unlock" onclick="TdbFacturiere.multiOpen()" disabled
                    onmousemove="ObjectTooltip.createDOM(this, this.down('.small-info'));">
                {{tr}}CFacture.action reopen selected{{/tr}}
                <div class="small-info" style="display: none;">
                    {{tr}}CFacture.action reopen selected info{{/tr}}
                </div>
            </button>
            <button class="unlock" onclick="TdbFacturiere.multiCotationOpen()" disabled
                    onmousemove="ObjectTooltip.createDOM(this, this.down('.small-info'));">
                {{tr}}CFacture.action cotation reopen selected{{/tr}}
                <div class="small-info" style="display: none;">
                    {{tr}}CFacture.action cotation reopen selected info{{/tr}}
                </div>
            </button>
            <button class="lock" onclick="TdbFacturiere.multiClose()" disabled
                    onmousemove="ObjectTooltip.createDOM(this, this.down('.small-info'));">
                {{tr}}CFacture.action close selected{{/tr}}
                <div class="small-info" style="display: none;">
                    {{tr}}CFacture.action close selected info{{/tr}}
                </div>
            </button>
        </th>
    </tr>
    <tr>
        <th class="narrow">
            <input type="checkbox" class="tdb-facturiere-allcheckbox"
                   onclick="TdbFacturiere.toggleCheckAll(this)"/>
        </th>
        <th>
            {{mb_label class=$facture_switch field=ouverture}}
        </th>
        <th>
            {{mb_label class=$facture_switch field=numero}}
        </th>
        <th>
            {{tr}}CPatient{{/tr}}
        </th>
        <th>
            {{tr}}CFactureCabinet-amount-invoice{{/tr}}
        </th>
        <th>
            {{tr}}CFactureCabinet-amount-paid{{/tr}}
        </th>
        <th>
            {{tr}}CFactureCabinet-amount-unpaid{{/tr}}
        </th>
        <th class="narrow">
            {{mb_label class=$facture_switch field=_statut}}
        </th>
        <th class="narrow"></th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$facture_list item=_facture}}
        <tr>
            <td>
                <input type="checkbox" class="tdb-facturiere-checkbox" data-facture-guid="{{$_facture->_guid}}"
                       onclick="TdbFacturiere.checkControl()" data-facture-id="{{$_facture->_id}}"
                       data-praticien-id="{{$_facture->praticien_id}}"
                       data-facture-cloture="{{if $_facture->cloture}}1{{else}}0{{/if}}"
                       data-statut-envoi="{{$_facture->statut_envoi}}"/>
                <div class="tdb-facturiere-indicator" style="display:none"></div>
            </td>
            <td>
                {{mb_value object=$_facture field=ouverture}}
            </td>
            <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}');">
            {{$_facture->_view}}
          </span>
            </td>
            <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_ref_patient->_guid}}')">
            {{$_facture->_ref_patient}}
          </span>
            </td>
            <td>
                {{mb_value object=$_facture field=_montant_avec_remise}}
            </td>
            <td>
                {{mb_value object=$_facture field=_reglements_total}}
            </td>
            <td>
                {{mb_value object=$_facture field=_du_restant}}
            </td>
            <td>
                {{$_facture->_statut_view}}
            </td>
            <td>
                <button class="search notext" onclick="Facture.edit('{{$_facture->_id}}', '{{$_facture->_class}}')">
                    {{tr}}Show{{/tr}}
                </button>
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td class="empty" colspan="9">
                {{tr}}{{$facture_switch}}.none{{/tr}}
            </td>
        </tr>
    {{/foreach}}
    </tbody>
</table>
