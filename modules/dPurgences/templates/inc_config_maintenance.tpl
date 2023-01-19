{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPadmissions script=identito_vigilance}}
{{mb_script module=dPurgences script=urgences_maintenance}}
<br/>
<table class="tbl">
  <tr>
    <th colspan="2" class="title">
      {{tr}}Action{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="narrow" colspan="2">
      <button type="button" class="search" onclick="urgencesMaintenance.checkRPU()">{{tr}}Check_rpu{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <td class="narrow">
      <button type="button" class="add" onclick="urgencesMaintenance.importMotif()">{{tr}}Import-Motif{{/tr}}</button>
    </td>
    <td id="import_sfmu">
    </td>
  </tr>
</table>