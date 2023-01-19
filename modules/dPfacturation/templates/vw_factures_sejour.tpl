{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="6">{{tr}}CFacture-gestion|pl{{/tr}} {{$sejour->_view}}</th>
  </tr>
  <tr>
    {{foreach from=$sejour->_ref_factures item=facture}}
      <td colspan="2">
        <table style="height: 100%;">
          <tr>
            <th class="category" colspan="2">
              {{tr}}CFacture{{/tr}}: {{$facture->_view}}
            </th>
          </tr>
          <tr>
            <td>{{mb_title object=$facture field=type_facture}}</td>
            <td>{{mb_value object=$facture field=type_facture}}</td>
          </tr>
          <tr>
            <td>{{mb_title object=$facture field=cession_creance}}</td>
            <td>{{mb_value object=$facture field=cession_creance}}</td>
          </tr>
          <tr>
            <td>{{mb_title object=$facture field=dialyse}}</td>
            <td>{{mb_value object=$facture field=dialyse}}</td>
          </tr>
          <tr>
            <td>{{mb_title object=$facture field=statut_pro}}</td>
            <td>{{mb_value object=$facture field=statut_pro}}</td>
          </tr>
          <tr>
            <td>{{tr}}CFacture-assurance{{/tr}}</td>
            <td>
              {{if $facture->assurance_maladie && $facture->type_facture != "esthetique"}}
                {{mb_value object=$facture->_ref_assurance_maladie field=nom}}
              {{elseif $facture->type_facture != "esthetique"}}
                {{mb_value object=$facture->_ref_assurance_accident field=nom}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="search" onclick="Facture.edit('{{$facture->_id}}', '{{$facture->_class}}', '0', 1);">
                {{tr}}Show{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </td>
    {{/foreach}}
  </tr>
</table>