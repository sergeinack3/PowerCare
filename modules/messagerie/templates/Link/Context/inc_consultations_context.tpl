{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* Default *}}
{{mb_default var=consultations value=null}}

{{foreach from=$consultations.result item=consultation}}
    <div class="MessagingLinkContext-sectionRoot">
        <input type="radio"
               name="radioItem"
               data-id="{{$consultation->_guid}}"
               id="radio-consultation-{{$consultation->_id}}"
               onchange="MessagingLink.checkLink();">
        <label for="radio-consultation-{{$consultation->_id}}">
            {{if $consultation->motif}}
                <strong>
                    {{$consultation->motif}}
                </strong>
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$consultation->_guid}}')">
                {{tr var1=$consultation->_ref_plageconsult->date|date_format:$conf.date}}CMessagingLink-Title-Consultation of %s{{/tr}}
            </span>
        </label>
    </div>
{{/foreach}}
