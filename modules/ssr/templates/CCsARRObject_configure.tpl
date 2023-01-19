{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=configure_dsn dsn=csarr}}

<h2>{{tr}}Import_bdd_.csarr.title{{/tr}}</h2>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  
  <tr>
    <td>
      <button class="tick" onclick="new Url('ssr', 'import_csarr').requestUpdate('csarr');" >
        {{tr}}Import_bdd_.csarr{{/tr}}</button>
      </td>
    <td id="csarr"></td>
  </tr>
</table>
