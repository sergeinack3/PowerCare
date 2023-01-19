{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=count value=2}}

<table class="tbl">
  {{if $count > 3 }}
    <tr>
      {{mb_include module=system template=inc_pagination total=$count current=$page step=50 change_page='listPatients'}}
    </tr>
  {{/if}}
  <tr>
    <th class="category narrow">{{mb_label class=CPatient field="nom"}}</th>
    <th class="category">{{mb_label class=CPatient field="prenom"}}</th>
    <th class="category">{{mb_label class=CPatient field="sexe"}}</th>
    <th class="category">{{mb_label class=CPatient field="_age"}}</th>
    <th class="category">{{mb_label class=CPatient field="naissance"}}</th>
    <th class="category">{{mb_label class=CPatient field="rques"}}</th>
    {{if $conf.ref_pays == 1}}
      <th class="category">{{mb_label class=CPatient field="matricule"}}</th>
    {{else}}
      <th class="category">{{mb_label class=CPatient field="avs"}}</th>
    {{/if}}
    <th class="category">{{mb_label class=CPatient field="adresse"}}</th>
    <th class="category">{{mb_label class=CPatient field="ville"}}</th>
    <th class="category">{{mb_label class=CPatient field="tel"}}</th>
    <th class="category">{{mb_label class=CPatient field="tel2"}}</th>
    <th class="category">{{mb_label class=CPatient field="email"}}</th>
    <th class="category">{{mb_label class=CPatient field="medecin_traitant"}}</th>
  </tr>
  {{foreach from=$patients item=_patient}}
    <tr>
      <td onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}');">
        {{mb_value object=$_patient field="nom"}} {{if $_patient->nom_jeune_fille}}({{mb_value object=$_patient field="nom_jeune_fille"}}){{/if}}
      </td>
      <td>{{mb_value object=$_patient field="prenom"}}</td>
      <td>{{mb_value object=$_patient field="sexe"}}</td>
      <td>{{$_patient->_age}}</td>
      <td>{{mb_value object=$_patient field="naissance"}}</td>
      <td class="text">{{mb_value object=$_patient field="rques"}}</td>
      {{if $conf.ref_pays == 1}}
        <td>{{mb_value object=$_patient field="matricule"}}</td>
      {{else}}
        <td>{{mb_value object=$_patient field="avs"}}</td>
      {{/if}}
      <td>{{mb_value object=$_patient field="adresse"}}</td>
      <td>{{mb_value object=$_patient field="cp"}} {{mb_value object=$_patient field="ville"}}</td>
      <td>{{mb_value object=$_patient field="tel"}}</td>
      <td>{{mb_value object=$_patient field="tel2"}}</td>
      <td>{{mb_value object=$_patient field="email"}}</td>
      <td>
        {{assign var=medecin value=$_patient->_ref_medecin_traitant}}
        {{if $medecin->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}');">
            <strong>{{$medecin->_shortview}}</strong>
          </span>
        {{else}}
          <span class="empty">{{tr}}CPatient-No doctor{{/tr}}</span>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="13" class="empty">
        {{tr}}CPatient.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
