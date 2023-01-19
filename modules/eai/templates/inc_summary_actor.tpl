{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset id="summary_actor">
  <legend>{{tr}}Summary{{/tr}}</legend>
  <ul style="list-style: none;">
    <li>
      <span style="color :{{if $actor->_id}}#94dd89{{else}}red{{/if}}">
      <i class="fa fa-chevron-circle-right" ></i> {{tr}}CExchange-type-destinataire{{/tr}} :
      </span>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$actor->_guid}}')">
        {{mb_value object=$actor field="_view"}}
      </span>
    </li>
    <br/>
    <li>
      <span style="color : {{if $actor->_ref_msg_supported_family}}#94dd89{{else}}red{{/if}}">
      <i class="fa fa-chevron-circle-right"></i> {{tr}}CInteropSender-back-messages_supported{{/tr}}
      </span>
      <ul style="list-style: none;">
        {{foreach from=$messages_supported key=_domain_name item=_domains}}
            {{foreach from=$_domains item=_families}}
              {{assign var=_family_name value=$_families|getShortName}}
                {{foreach from=$_families->_categories key=_category_name item=_messages_supported}}
                    <li>
                      {{tr}}{{$_family_name}}{{/tr}}
                      {{if $_category_name != "none"}}
                        - {{tr}}{{$_category_name}}{{/tr}} (<em>{{$_category_name}})</em>
                      {{/if}}
                    </li>
                {{/foreach}}
            {{/foreach}}
        {{/foreach}}
      </ul>
    </li>
    <br/>
    <li>
      <span style="color : {{if $source}}#94dd89{{else}}red{{/if}}">
      <i class="fa fa-chevron-circle-right"></i>
        {{tr}}CInteropActor-_ref_exchanges_sources{{/tr}}
      </span>
      {{if $source}}
        <ul style="list-style: none;">
          {{foreach from=$actor->_ref_msg_supported_family item=_msg_supported}}
            {{unique_id var=uid numeric=true}}
            <li>
              {{tr}}{{$_msg_supported}}{{/tr}}
              {{assign var=source value=$actor->_ref_exchanges_sources.$_msg_supported}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$source->_guid}}')">
              {{mb_value object=$source field="_view"}}
              </span>
              {{if $source && $source->_id}}
                <i class="fa fa-check" style="color : #94dd89"></i>
              {{else}}
                <i class="fa fa-times" style="color : red;"></i>
              {{/if}}
            </li>
          {{/foreach}}
        </ul>
      {{/if}}
    </li>
  </ul>
</fieldset>