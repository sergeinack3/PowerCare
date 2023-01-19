{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback_no_regle value="function() {}"}}

<div class="small-info">
  <strong>{{tr}}Consultations.past.no_regle{{/tr}}:</strong>
  <ul>
    {{foreach from=$past_consults item=_consultation}}
      <li>
        <button type="button" class="edit notext" style="padding:0;margin:0;" title="{{tr}}CConsultation{{/tr}}"
                onclick="Consultation.editModal('{{$_consultation->_id}}', 'facturation', null, {{$callback_no_regle}});"></button>
        {{tr var1=$_consultation->_date|date_format:$conf.date var2=$_consultation->_ref_chir}}common-The %s by %s{{/tr}}
      </li>
    {{/foreach}}
  </ul>
</div>