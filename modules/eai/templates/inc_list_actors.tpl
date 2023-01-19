{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$link_actors item=_link_actor}}
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_link_actor->_ref_actor->_guid}}')">{{$_link_actor->_ref_actor->nom}}</span>
  <button type="button" class="cancel notext"
          onclick="EAITransformationRuleSequence.linkActorToSequence(
            '{{$_link_actor->_ref_actor->_guid}}', '{{$_link_actor->sequence_id}}', '{{$_link_actor->_id}}' )">
  </button>
{{/foreach}}
