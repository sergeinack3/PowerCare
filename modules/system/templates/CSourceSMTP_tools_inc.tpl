{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=smtp ajax=true}}

<!-- Test d'envoi SMTP -->
<button class="lookup notext compact" onclick="SMTP.connexion('{{$_source->name}}')">
  {{tr}}utilities-source-smtp-connexion{{/tr}}
</button>

<!-- Test d'envoi SMTP -->
<button class="send notext compact" onclick="SMTP.envoi('{{$_source->name}}');">
  {{tr}}utilities-source-smtp-envoi{{/tr}}
</button>