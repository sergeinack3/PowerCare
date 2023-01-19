{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=configure_dsn dsn=presta_ssr}}

<h2>{{tr}}CPrestaSSR-Importing the CPrestaSSR database{{/tr}}</h2>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  
  <tr>
    <td>
      <button class="tick" onclick="new Url('ssr', 'import_presta_ssr').requestUpdate('presta_ssr');" >
        {{tr}}CPrestaSSR-action-Import the CPrestaSSR database{{/tr}}</button>
      </td>
    <td id="presta_ssr"></td>
  </tr>
</table>
