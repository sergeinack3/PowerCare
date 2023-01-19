{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=l_action_icon value=false}}
{{mb_default var=l_action_state value=""}}
{{mb_default var=r_action_icon value=false}}
{{mb_default var=r_action_sec_icon value=false}}
{{mb_default var=type_callback value=false}}
{{mb_default var=container_class value=""}}
{{mb_default var=selected value=false}}

<div class="factureliaison-element-container {{$container_class}}">
  <div class="factureliaison-element {{if $selected}}factureliaison-element-selected{{/if}}">
    {{if $l_action_icon}}
      <div class="actions l-actions {{$l_action_state}}" style="display: ;" onclick="{{$l_action_callback}}"
           title="{{tr}}{{$l_action_title}}{{/tr}}">
        <i class="fa {{$l_action_icon}}"></i>
      </div>
    {{/if}}
    <div class="type" {{if $type_callback}}onclick="{{$type_callback}}"{{/if}}
         title="{{tr}}{{$type_title}}{{/tr}}">
      {{$type}}
    </div>
    <div class="content">
      <div class="label">
        {{$label}}
      </div>
      <div class="sublabel">
        <div class="sublabel1">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
        </div>
        <div class="sublabel2">
          {{$sublabel}}
        </div>
      </div>
    </div>
    {{if $r_action_sec_icon}}
      <div class="actions r-actions-sec" style="display: none"
           onclick="{{$r_action_sec_callback}}" title="{{tr}}{{$r_action_sec_title}}{{/tr}}">
        <i class="fa {{$r_action_sec_icon}}"></i>
      </div>
    {{/if}}
    {{if $r_action_icon}}
      <div class="actions r-actions" onclick="{{$r_action_callback}}" title="{{tr}}{{$r_action_title}}{{/tr}}">
        <i class="fa {{$r_action_icon}}"></i>
      </div>
    {{/if}}
  </div>
</div>
