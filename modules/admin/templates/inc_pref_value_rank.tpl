{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=pref_readonly value=1}}
{{mb_default var=pref_context  value=1}}

{{if !$pref_readonly}}
  {{if $pref.user}}
    {{assign var=value value=$pref.user}}
  {{else}}
    {{assign var=value value=$pref.default}}
  {{/if}}
{{/if}}

<div>
  {{foreach from="|"|explode:$value item=_value}}
    <div data-token="{{$_value}}">
      {{if !$pref_readonly}}
        <img src="./images/icons/updown.gif" usemap="#map-{{$_value}}" />
        <map name="map-{{$_value}}">
          <area coords="0,0,10,7"  href="#1" onclick="moveToken{{$var}}(this)" />
          <area coords="0,8,10,14" href="#1" onclick="moveToken{{$var}}(this, 'down')" />
        </map>
      {{/if}}
      {{if $pref_context}}
        {{tr}}pref-{{$var}}-{{$_value}}{{/tr}}
      {{else}}
        {{$_value}}
      {{/if}}

      {{if !$pref_context && isset($libelles|smarty:nodefaults) && isset($libelles.$_value|smarty:nodefaults)}}
        <div class="compact text" style="font-size: 7pt">
          {{$libelles.$_value}}
        </div>
      {{/if}}
    </div>
  {{/foreach}}
</div>