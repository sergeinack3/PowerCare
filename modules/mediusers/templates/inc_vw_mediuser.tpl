{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=initials}}
{{mb_default var=classe value=""}}
{{mb_default var=show_adeli value=0}}
{{mb_default var=use_chips value=1}}
{{mb_default var=show_specialite value=0}}

{{if $mediuser->_id}}
    <div class="me-user-chips
       {{if $initials === "block" || $initials === "border"}}me-user-chips-initials {{/if}}
       {{if $show_specialite}}expand {{/if}}
       {{if !$use_chips}}me-no-chips {{/if}}
       {{if $classe}}{{$classe}}{{/if}}"
         onmouseover="ObjectTooltip.createEx(this, '{{$mediuser->_guid}}');">
        <div>
            <div class="me-user-chips-icon" style="background-color: #{{$mediuser->_color}}">
                <div class="me-user-chips-black" style="border-color: #{{$mediuser->_color}}">
                    {{$mediuser->_shortview}}
                </div>
                <div class="me-user-chips-border" style="background-color: #{{$mediuser->_color}}"></div>
            </div>
            {{if $initials !== "block" && $initials !== "border"}}
                <div class="me-user-chips-content">
                    <div class="me-user-chips-name">
                        {{$mediuser}}
                    </div>
                    {{if $show_specialite}}
                        <div class="me-user-chips-speciality">
                            {{$mediuser->loadRefSpecCPAM()|substr:4}}
                        </div>
                    {{/if}}
                </div>
            {{/if}}
        </div>
    </div>
{{/if}}
