{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_consult->_ref_consult_anesth && $_consult->_ref_consult_anesth->_id && $_consult->_etat_dhe_anesth}}
  {{if $_consult->_etat_dhe_anesth == "associe"}}
    <span class="texticon texticon-allergies-ok" title="{{tr}}CConsultation-_etat_dhe_anesth-associe{{/tr}}" style="float: right;">
      {{tr}}COperation-event-dhe{{/tr}}
    </span>
  {{elseif $_consult->_etat_dhe_anesth == "dhe_exist"}}
    <span class="texticon texticon-atcd" title="{{tr}}CConsultation-_etat_dhe_anesth-dhe_exist{{/tr}}" style="float: right">
      {{tr}}COperation-event-dhe{{/tr}}
    </span>
  {{elseif $_consult->_etat_dhe_anesth == "non_associe"}}
    <span class="texticon texticon-stup texticon-stroke" title="{{tr}}CConsultation-_etat_dhe_anesth-non_associe{{/tr}}"
          style="float: right;white-space: nowrap;">
      {{tr}}COperation-event-dhe{{/tr}}
    </span>
  {{/if}}
{{/if}}