{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=configure_dsn dsn=ccamV2}}

<h2>Import de la base de données CCAM</h2>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>

  <tr>
    <td><button id="update_ccam" class="tick" onclick="startCCAM()" >Importer la base de données CCAM</button></td>
    <td id="ccam"></td>
  </tr>

  <tr>
    <td><button id="update_forfaits" class="tick" onclick="startForfaits()" >Ajouter les types de forfait</button></td>
    <td id="forfaits"></td>
  </tr>

</table>