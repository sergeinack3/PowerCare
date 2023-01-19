{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=naissance ajax=1}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}

{{if !$dossier->admission_id}}
  {{mb_include module=maternite template=inc_dossier_mater_admission_choix_sejour}}

  {{mb_return}}
{{/if}}

{{if !$print}}
  <script>
    Naissance.reloadNaissances = DossierMater.refresh;
  </script>
{{/if}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      <button type="button" class="add not-printable" style="float: left;"
              onclick="Naissance.edit(0, null, '{{$dossier->admission_id}}')">Naissance
      </button>

      Naissances
    </th>
  </tr>
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{mb_label class=CNaissance field=rang}} / {{mb_label class=CNaissance field=date_time}}</th>
    <th class="category">{{tr}}CPatient{{/tr}}</th>
    <th class="category">{{tr}}CSejour{{/tr}}</th>
  </tr>
  {{foreach from=$grossesse->_ref_naissances item=_naissance}}
    {{assign var=sejour_enfant value=$_naissance->_ref_sejour_enfant}}
    {{assign var=enfant value=$sejour_enfant->_ref_patient}}
    <tr>
      <td>
        <button type="button" class="edit notext not-printable" onclick="Naissance.editResumeSejour('{{$_naissance->_id}}')"></button>
      </td>
      <td>
        {{if $_naissance->date_time}}
          Le {{$_naissance->date_time|date_format:$conf.date}} à {{$_naissance->date_time|date_format:$conf.time}}
        {{else}}
          {{$_naissance}}
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$enfant->_guid}}')">{{$enfant}} ({{$enfant->_age}})</span>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_enfant->_guid}}')">{{$sejour_enfant->_shortview}}</span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CNaissance.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
