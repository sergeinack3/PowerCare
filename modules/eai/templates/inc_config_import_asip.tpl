{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr}}Import_asip{{/tr}}</h2>

{{mb_include module=system template=configure_dsn dsn=ASIP}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="2">
      {{tr}}Import_tables{{/tr}}
    </th>
  <tr>
    <td class="narrow"><button onclick="importAsipTable()" class="change">{{tr}}Import{{/tr}}</button></td>
    <td id="import-log"></td>
  </tr>
  <tr>
    <td class="narrow"><button onclick="seeAsipDB()" class="lookup">{{tr}}CSpecialtyAsip|pl{{/tr}}</button></td>
    <td></td>
  </tr>
  <tr>
    <td class="narrow"><button onclick="updateASIPDB()" class="lookup">{{tr}}CSpecialtyAsip-msg-update db{{/tr}}</button></td>
    <td id="report-update"></td>
  </tr>
</table>
