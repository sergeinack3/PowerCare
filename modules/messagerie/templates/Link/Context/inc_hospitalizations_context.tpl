{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* Default *}}
{{mb_default var=hospitalizations value=null}}

{{foreach from=$hospitalizations.result item=hospitalization}}
    <div class="MessagingLinkContext-sectionRoot">
        <input type="radio"
               name="radioItem"
               data-id="{{$hospitalization->_guid}}"
               id="radio-hospitalization-{{$hospitalization->_id}}"
               onchange="MessagingLink.checkLink();">
        <label for="radio-hospitalization-{{$hospitalization->_id}}">
            {{if $hospitalization->libelle}}
                <strong>
                    {{$hospitalization->libelle}}
                </strong>
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$hospitalization->_guid}}')">
                {{if $hospitalization->entree|date_format:$conf.date === $hospitalization->sortie|date_format:$conf.date}}
                    {{tr var1=$hospitalization->entree|date_format:$conf.date}}
                        CMessagingLink-Title-Hospitalization of %s
                    {{/tr}}
                {{else}}

                    {{tr var1=$hospitalization->entree|date_format:$conf.date var2=$hospitalization->sortie|date_format:$conf.date}}
                        CMessagingLink-Title-Hospitalization of %s to %s
                    {{/tr}}
                {{/if}}
            </span>
        </label>
    </div>
    {{foreach from=$hospitalization->_ref_consultations item=consultation}}
        <div class="MessagingLinkContext-sectionChild">
            <input type="radio"
                   name="radioItem"
                   data-id="{{$consultation->_guid}}"
                   id="radio-hospitalization-consultation-{{$consultation->_id}}"
                   onchange="MessagingLink.checkLink();">
            <label for="radio-hospitalization-consultation-{{$consultation->_id}}">
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
    {{foreach from=$hospitalization->_ref_operations item=operation}}
        <div class="MessagingLinkContext-sectionChild">
            <input type="radio"
                   name="radioItem"
                   data-id="{{$operation->_guid}}"
                   id="radio-hospitalization-operation-{{$operation->_id}}"
                   onchange="MessagingLink.checkLink();">
            <label for="radio-hospitalization-operation-{{$operation->_id}}">
                {{if $operation->libelle}}
                  <strong>
                      {{$operation->libelle}}
                  </strong>
                {{/if}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}')">
                    {{tr var1=$operation->date|date_format:$conf.date}}CMessagingLink-Title-Operation of %s{{/tr}}
                </span>
            </label>
        </div>
    {{/foreach}}
{{/foreach}}
