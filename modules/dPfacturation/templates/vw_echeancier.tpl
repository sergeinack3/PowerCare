{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$facture->cloture}}
  <div class="small-info">{{tr}}CFacture-msg-close-invoice{{/tr}}</div>
{{else}}
  {{if !$facture->annule && !$facture->extourne}}
    <button type="button" class="new" onclick="Echeance.edit('{{$facture->_id}}', '{{$facture->_class}}');">
      {{tr}}CEcheance-title-create{{/tr}}
    </button>
    {{if $facture->_ref_echeances|@count == 0}}
      <button type="button" class="new"
              onclick="Echeance.Monthly.launchGeneration('{{$facture->_id}}', '{{$facture->_class}}');">
        {{tr}}CEcheance-title-create-mensualite{{/tr}}
      </button>
    {{/if}}
  {{/if}}
  <fieldset style="text-align: left;">
    <legend>{{tr}}CEcheance{{/tr}}</legend>
    <table class="main tbl">
      <tr></tr>
      <tr>
        <th class="narrow">{{mb_label class=CEcheance field=date}}</th>
        <th>{{mb_label class=CEcheance field=montant}}</th>
        <th>{{mb_label class=CEcheance field=description}}</th>
        <th class="narrow">Action</th>
      </tr>
      {{foreach from=$facture->_ref_echeances item=_echeance}}
        <tr style="text-align:center;">
          <td>{{mb_value object=$_echeance field=date}}</a></td>
          <td>{{mb_value object=$_echeance field=montant}}</td>
          <td>{{mb_value object=$_echeance field=description}}</td>
          <td>
            <button class="edit notext" onclick="Echeance.edit('{{$_echeance->_id}}');">
              {{tr}}Edit{{/tr}}
            </button>
          </td>
        </tr>
      {{foreachelse}}
        <tr>
          <td class="empty" colspan="4">{{tr}}CEcheance.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </fieldset>
  <div id="monthly_form">
  </div>
{{/if}}
