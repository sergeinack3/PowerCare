{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $phase_name === "hot"}}
    {{assign var="color" value="red"}}
    {{assign var="order" value="1"}}
{{elseif $phase_name === "warm"}}
    {{assign var="color" value="orange"}}
    {{assign var="order" value="2"}}
{{elseif $phase_name === "cold"}}
    {{assign var="color" value="deepskyblue"}}
    {{assign var="order" value="3"}}
{{else}}
    {{assign var="color" value="#EEEEEE"}}
    {{assign var="order" value="4"}}
{{/if}}

<div class="card" style="flex: 1; order: {{$order}};">
  <div class="card_title" style="background-color: {{$color}};">
    <p>{{$phase_name|@ucfirst}}</p>
  </div>
    {{foreach from=$phase key=_key item=_value}}
        {{if $_key === "actions"}}
            {{foreach from=$_value key=_actions_key item=_action_value}}
                {{if $_actions_key === "rollover"}}
                  <div class="card" style="width: 80%; margin: 0 auto;">
                    <div class="card_title">
                      <p>Rollover</p>
                    </div>
                      {{foreach from=$_action_value key=_key item=_value}}
                          {{if $_key === "actions"}}

                          {{else}}
                            <div class="card_body">
                              <div class="card_field">
                                <h4 class="card_field_title">{{$_key}}</h4>
                                <p>{{$_value}}</p>
                              </div>
                            </div>
                          {{/if}}
                      {{/foreach}}
                  </div>
                {{/if}}
            {{/foreach}}
        {{else}}
          <div class="card_body">
            <div class="card_field">
              <h4 class="card_field_title">{{$_key}}</h4>
              <p>{{$_value}}</p>
            </div>
          </div>
        {{/if}}
    {{/foreach}}
</div>

