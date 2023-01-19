{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_ref_file && $object->_ref_file->_id}}
  <button class="link" type="button {{if $object->_status != "linked"}}notext{{/if}}" onclick="linkDocument('{{$object->_guid}}');">
    {{if $object->_status == "linked"}}
      (déjà lié)
    {{/if}}
  </button>

  {{if $object->_status == "linked"}}
    <button class="unlink notext" type="button" onclick="unlinkDocument('{{$object->_guid}}')">{{tr}}Unlink{{/tr}}</button>
  {{/if}}

{{else}}
  <div class="warning">Pas de fichier à lier</div>
{{/if}}

{{if $object->starred}}
  <img src="modules/messagerie/images/favorites-1.png" alt="" style="height:1.3em;" />
{{/if}}

{{if $object->archived}}
  <img src="modules/messagerie/images/mail_archive.png" alt="" style="height:1.3em;" />
{{/if}}
