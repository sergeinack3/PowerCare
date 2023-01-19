{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$patient->_bmr_bhre_status || !$patient->_bmr_bhre_status|@count}}
  {{mb_return}}
{{/if}}

{{assign var=bmr_bhre      value=$patient->_ref_bmr_bhre}}
{{assign var=bmr_bhre_show value=true}}

{{if $bmr_bhre && $bmr_bhre->_id && $bmr_bhre->bhre_contact_fin && ($bmr_bhre->bhre_contact_fin < $dnow)}}
  {{assign var=bmr_bhre_show value=false}}
{{/if}}


{{foreach from=$patient->_bmr_bhre_status key=_status item=_color}}
  <span class="texticon" title="{{tr}}CBMRBHRe.status.{{$_status}}-desc{{/tr}}"
        style="color: {{$_color}}; font-weight: bold; {{if ($_status == "BHReC") && !$bmr_bhre_show}}display: none;{{/if}}">
    {{tr}}CBMRBHRe.status.{{$_status}}{{/tr}}
  </span>
{{/foreach}}
