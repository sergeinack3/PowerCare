{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=configure_dsn dsn=cim10}}
{{mb_script module=pmsi script=pmsi}}
<table class="form">
  <tr>
    <th class="title" colspan="2">Import</th>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button class="change" onclick="PMSI.importBaseCim()">{{tr}}Import{{/tr}} la base CIM10 à usage PMSI</button>
      <div id="result-import"></div>
    </td>
  </tr>
</table>
