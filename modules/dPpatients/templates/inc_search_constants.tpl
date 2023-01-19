{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=i value=0}}
{{foreach from=$releves item=_releve}}
  {{if $i%2 == 0}}<tr class="toto">{{/if}}
  <td id="display_releve_{{$_releve->_id}}">
    {{mb_include module=dPpatients template=inc_search_releve_fieldset}}
  </td>
  {{assign var=i value=$i+1}}
  {{if $i%2 == 0}}</tr>{{/if}}
{{/foreach}}
