{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h3>{{tr}}BloodSugarDayReport-Table{{/tr}}</h3>
<table class="tbl">
  <tr class="title">
    <th></th>
    <th style="width: 15%">{{tr}}BloodSugarDayReport-Morning{{/tr}}<br>{{tr var1=0 var2=11}}BloodSugarDayReport-From %s to %s{{/tr}}</th>
    <th style="width: 15%">{{tr}}BloodSugarDayReport-Midday{{/tr}}<br>{{tr var1=11 var2=15}}BloodSugarDayReport-From %s to %s{{/tr}}</th>
    <th style="width: 15%">{{tr}}BloodSugarDayReport-Afternoon{{/tr}}<br>{{tr var1=15 var2=20}}BloodSugarDayReport-From %s to %s{{/tr}}</th>
    <th style="width: 15%">{{tr}}BloodSugarDayReport-Evening and night{{/tr}}<br>{{tr var1=20 var2=24}}BloodSugarDayReport-From %s to %s{{/tr}}</th>
  </tr>

  {{foreach from=$blood_sugar item=_blood_sugar}}
    {{assign var=constants value=$_blood_sugar->getConstants()}}
    {{* Date *}}
    <tr>
      <th></th>
      <th colspan="4" style="text-align: center;">{{$_blood_sugar->getDateString()|date_format:$conf.date}}</th>
    </tr>

    {{* Constantes *}}
    <tr>
      <td>
        {{tr}}CConstantesMedicales-_glycemie{{/tr}}
        ({{if $ref_unit_glycemie}}{{$ref_unit_glycemie}}{{else}}{{$constants->getBloodSugarUnit()}}{{/if}})
      </td>
      <td>{{mb_include module=patients template=followup_glycemie_constant_row constants=$constants->getMorning()}}</td>
      <td>{{mb_include module=patients template=followup_glycemie_constant_row constants=$constants->getMidday()}}</td>
      <td>{{mb_include module=patients template=followup_glycemie_constant_row constants=$constants->getAfternoon()}}</td>
      <td>{{mb_include module=patients template=followup_glycemie_constant_row constants=$constants->getEveningNight()}}</td>
    </tr>

    {{* Administrations *}}
    {{foreach from=$_blood_sugar->getAdministrations() item=_administrations}}
      {{assign var=prescription_line value=$_administrations->getPrescriptionLine()}}
      <tr>
        <td>{{$_administrations->getPrescriptionLine()}}</td>
        <td>{{mb_include module=patients template=followup_glycemie_administration_row administrations=$_administrations->getMorning()}}</td>
        <td>{{mb_include module=patients template=followup_glycemie_administration_row administrations=$_administrations->getMidday()}}</td>
        <td>{{mb_include module=patients template=followup_glycemie_administration_row administrations=$_administrations->getAfternoon()}}</td>
        <td>{{mb_include module=patients template=followup_glycemie_administration_row administrations=$_administrations->getEveningNight()}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
