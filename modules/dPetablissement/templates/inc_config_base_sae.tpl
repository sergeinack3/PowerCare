{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Paramètres de la base SAE</h2>
  
  {{mb_include module=system template=configure_dsn dsn=sae}}
 
  <table class="main tbl">
  <tr>
    <th class="title" colspan="2">
      Import de la base
    </th>
  <tr>
    <td class="narrow">
      <button onclick="new Url('dPetablissement', 'ajax_import_sae_base').requestUpdate('import-log');" class="change">
        {{tr}}Import{{/tr}}
      </button>
    </td>
    <td id="import-log"></td>
  </tr>
</table>