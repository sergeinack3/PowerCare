{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{tr var1=$liaisons|@count}}CFacture-doublons nb %s{{/tr}}
<br/>

<table class="main tbl">
  <tr>
    <th>{{tr}}CFacture{{/tr}}</th>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th>{{tr}}CFacture-praticien_id{{/tr}}</th>
    <th>{{tr}}Date{{/tr}} {{tr}}Consultation{{/tr}}1</th>
    <th>{{tr}}Date{{/tr}} {{tr}}Consultation{{/tr}}2</th>
  </tr>
  {{foreach from=$liaisons item=liaison}}
    <tr>
      {{assign var=facture value=$liaison->_ref_facture}}
      <td>{{$facture->_view}}</td>
      <td>
        <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_patient->_guid}}')">
          {{$facture->_ref_patient->_view|truncate:30:"...":true}}</a>
      </td>
      <td>{{$facture->_ref_praticien->_view}}</td>
      <td>
        <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_consult->_guid}}')">
          {{$facture->_ref_first_consult->_date}}</a>
      </td>
      <td>
        <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_consult->_guid}}')">
          {{$facture->_ref_last_consult->_date}}</a>
      </td>
    </tr>
  {{/foreach}}
</table>
