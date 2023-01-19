{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* Default *}}
{{mb_default var=events value=null}}

{{foreach from=$events.result item=event}}
    <div class="MessagingLinkContext-sectionRoot">
        <input type="radio"
               name="radioItem"
               data-id="{{$event->_guid}}"
               id="radio-event-{{$event->_id}}"
               onchange="MessagingLink.checkLink();">
        <label for="radio-event-{{$event->_id}}">
            {{if $event->libelle}}
                <strong>
                    {{$event->libelle}}
                </strong>
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$event->_guid}}')">
                {{tr var1=$event->date|date_format:$conf.date}}CMessagingLink-Title-Patient event of %s{{/tr}}
            </span>
        </label>
    </div>
{{/foreach}}

