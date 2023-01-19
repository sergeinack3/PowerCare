{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=nb_week value=0}}
{{assign var=libelle value=""}}

{{foreach from=$slots item=_slot}}
  {{if $_slot->_nb_week != $nb_week}}
    <tr>
      <th class="section" colspan="4">
          {{tr var1=$_slot->_nb_week}}CPlageconsult-Week number %s-court{{/tr}}
      </th>
    </tr>
      {{assign var=nb_week value=$_slot->_nb_week}}
  {{/if}}
  {{assign var=libelle_plage value=$_slot->_ref_plageconsult->libelle}}
  {{if $libelle_plage && $libelle_plage!=$libelle}}
    <tr>
      <th class="category" colspan="4">
          {{tr var1=$libelle_plage}}CPlageconsult-Wording of the time slot : %s{{/tr}}
      </th>
    </tr>
    {{assign var=libelle value=$libelle_plage}}
  {{/if}}
  <tr>
    <td>{{$_slot->_date}}</td>
    <td>{{$_slot->_heure}}</td>
    <td>
      {{assign var=plage value=$_slot->_ref_plageconsult}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$plage->_ref_chir->_guid}}');">
        {{$plage->_ref_chir->_view}}
      </span>
    </td>
    <td>
      <button type="button" class="tick notext"
              onclick="Control.Modal.close();{{if $rdv}}CreneauConsultation.getDataForConsult('{{$_slot->_ref_plageconsult->date}}','{{$_slot->_ref_plageconsult->date|date_format:"%d/%m/%y"}}', '{{$_slot->_heure}}', '{{$_slot->plageconsult_id}}');{{else}}CreneauConsultation.modalPriseRDV(0, '{{$_slot->_ref_plageconsult->date}}', '{{$_slot->_heure}}', '{{$_slot->plageconsult_id}}');{{/if}}">
      </button>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="4">
      {{tr}}CPlageconsult-No free slot{{/tr}}
    </td>
  </tr>
{{/foreach}}
