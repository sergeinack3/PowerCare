{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=id}}

{{mb_default var=label value=false}}
{{mb_default var=name value=$id}}

{{mb_default var=value value=10}}
{{mb_default var=min value=0}}
{{mb_default var=max value=50}}
{{mb_default var=step value=5}}
{{mb_default var=unit value=false}}


<style>
  input[type='range'] {
    position: relative;
    margin-left: 1em;
    width: 200px;
  }

  input[type='range']:after,
  input[type='range']:before {
    position: absolute;
    top: 10px;
    color: black;
  }

  input[type='range']:before {
    left: -11px;
    content: attr(min);
  }

  input[type='range']:after {
    right: -7px;
    content: attr(max);
  }
</style>

<script>
  updateRange = function(input) {
    var value = $V(input);

    {{if $unit}}
      value += ' %';
    {{/if}}

    input.next('output').innerHTML = value;
  }
</script>

<table class="main layout" style="max-width: 400px; display: inline-block;">
  <tr>
    {{if $label}}
      <td class="text">{{tr}}{{$label}}{{/tr}}</td>
    {{/if}}

    <td style="text-align: center;">
      <input type="range" name="{{$name}}" min="{{$min}}" max="{{$max}}" value="{{$value}}" step="{{$step}}"
             oninput="updateRange(this);" />
      <br />
      <output style="font-weight: bold;">{{$value}}{{if $unit}}&nbsp;{{$unit}}{{/if}}</output>
    </td>
  </tr>
</table>
