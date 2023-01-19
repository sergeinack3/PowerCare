{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="search" onclick="FileSystem.connexion('{{$_source->name}}');"
        {{if !$_source->_id}}disabled{{/if}}>
  {{tr}}utilities-source-file_system-connexion{{/tr}}
</button>

<!-- Dépôt d'un fichier -->
<button type="button" class="search" onclick="FileSystem.sendFile('{{$_source->name}}');"
        {{if !$_source->_id}}disabled{{/if}}>
  {{tr}}utilities-source-file_system-sendFile{{/tr}}
</button>

<!-- Liste des fichiers -->
<button type="button" class="search" onclick="FileSystem.getFiles('{{$_source->name}}');"
        {{if !$_source->_id}}disabled{{/if}}>
  {{tr}}utilities-source-file_system-getFiles{{/tr}}
</button>