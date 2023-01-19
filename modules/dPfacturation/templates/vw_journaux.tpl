{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=journal ajax=true}}
<script>
  Main.add(function() {
    Journal.filesJournal();
  });
</script>
<table class="form">
  <tr>
    <th class="category">
      {{tr}}CEditJournal{{/tr}}
    </th>
    <th class="title">
      {{tr}}CEditJournal.search{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="button" style="width:50%;">
      <button type="button" class="print" onclick="Journal.viewJournal('paiement');">{{tr}}CJournalBill.type.paiement{{/tr}}</button>
      <div class="small-info">
        {{tr}}CEditJournal.print_paiement{{/tr}}
      </div>
    </td>
    <td class="button">
      <form class="form" name="printFiles">
        {{mb_field class=CJournalBill field="type"}}<br/>
        <button type="button" class="save" onclick="Journal.filesJournal();">{{tr}}Validate{{/tr}}</button>
      </form>
    </td>
  </tr>
  <tr>
    <td class="button">
      <button type="button" class="print" onclick="Journal.viewJournal('debiteur');">{{tr}}CJournalBill.type.debiteur{{/tr}}</button>
      <div class="small-info">
        {{tr}}CEditJournal.print_debiteur{{/tr}}
      </div>
    </td>
    <td rowspan="2" id="files_journaux">
      {{mb_script module=files     script=file ajax=true}}
    </td>
  </tr>
  <tr>
    <td class="button">
      <button type="button" class="print" onclick="Journal.viewJournal('rappel');">{{tr}}CJournalBill.type.rappel{{/tr}}</button>
      <div class="small-info">
        {{tr}}CEditJournal.print_rappels{{/tr}}
      </div>
    </td>
  </tr>
</table>