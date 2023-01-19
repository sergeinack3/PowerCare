{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  Ce requêteur ne se base que sur <strong>l'extension française de la norme HL7 PAM</strong>
</div>

{{foreach from=$queries key=type item=values}}
  <fieldset>
    <legend>{{tr}}{{$type}}{{/tr}}</legend>
    
    <table class="main tbl">
    {{foreach from=$values key=name item=_value}}
      <tr>
        <th class="category" style="width: 20%">{{tr}}{{$type}}-{{$name}}{{/tr}}</th>
        <td>{{$_value}}</td>
      </tr>
    {{/foreach}}
    </table>
  </fieldset>
{{/foreach}}
