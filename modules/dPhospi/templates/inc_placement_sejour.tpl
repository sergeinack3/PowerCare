{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var="which" value="first"}}
{{mb_default var=classe value=""}}

{{if ($which == "first")}}{{assign var=affectation value=$sejour->_ref_first_affectation}}{{/if}}
{{if ($which == "curr" )}}{{assign var=affectation value=$sejour->_ref_curr_affectation }}{{/if}}
{{if ($which == "last" )}}{{assign var=affectation value=$sejour->_ref_last_affectation }}{{/if}}

{{if isset($affectation|smarty:nodefaults) && $affectation->_id}}
  <div>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$affectation->_guid}}')" {{if $classe}}class="{{$classe}}"{{/if}}>
      {{$affectation->_view}}
    </span>
  </div>
{{else}}
  <div class="empty {{if $classe}}{{$classe}}{{/if}}">Non placé</div>
{{/if}}
