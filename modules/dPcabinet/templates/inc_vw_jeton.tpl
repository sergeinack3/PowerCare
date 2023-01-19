{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=nb value=0}}
{{math equation=x+2 x=$nb assign=nb_quotas}}
{{mb_default var=quotas value=$nb_quotas}}

{{if $nb < ($quotas - 1)}}
  {{* Vert   *}}
  {{assign var=color value="#0e0"}}
{{elseif $nb == ($quotas - 1)}}
  {{* Orange *}}
  {{assign var=color value="#fb0"}}
{{elseif $nb == $quotas}}
  {{* Rouge  *}}
  {{assign var=color value="#800"}}
{{else}}
  {{* noir   *}}
  {{assign var=color value="#000"}}
{{/if}}

<div title="{{$nb}}">
  {{assign var=mode_button value=1}}
  {{if $mode_button}}
    {{if $nb}}
      {{math equation="floor(x/10)" x=$nb assign=dizaines}}
      {{if $dizaines > 0}}
        {{foreach from=1|range:$dizaines item=i}}
          {{math equation="x*y" x="1" y=$i assign=margin}}
          <div class="jeton_dizaine" style="background: {{$color}}"></div>
        {{/foreach}}
      {{/if}}
      {{math equation="x-10*y" x=$nb y=$dizaines assign=reste}}
      {{if $reste > 0}}
        {{foreach from=1|range:$reste item=i}}
          {{math equation="x*y" x="1" y=$i assign=margin}}
          <div class="jeton_unite" style="background: {{$color}}"></div>
        {{/foreach}}
      {{/if}}
    {{/if}}
  {{else}}
    {{$nb}}
  {{/if}}
</div>