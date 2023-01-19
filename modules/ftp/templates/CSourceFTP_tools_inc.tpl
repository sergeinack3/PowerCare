{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ftp script=action_ftp ajax=true}}

<!-- Test connexion FTP -->
<button class="search notext compact" onclick="FTP.connexion('{{$_source->name}}')">
  {{tr}}utilities-source-ftp-connexion{{/tr}}
</button>

<!-- Liste des fichiers -->
<button class="list notext compact" onclick="FTP.getFiles('{{$_source->name}}')">
  {{tr}}utilities-source-ftp-getFiles{{/tr}}
</button>