{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var="medecin" value=$object}}
<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400 object=$medecin}}
      {{mb_include module=system template=inc_object_history object=$medecin}}
      {{mb_include module=system template=inc_object_notes object=$medecin}}
      {{$medecin}}
    </th>
  </tr>
    {{mb_ternary var=disciplines test=$medecin->disciplines value=$medecin->disciplines other=$medecin->_mep_disciplines}}
    {{if $disciplines}}
      <tr>
        <td colspan="2">
          <strong>{{$disciplines}}</strong>
        </td>
      </tr>
    {{/if}}
    <tr>
    {{mb_ternary var=tel test=$medecin->tel value=$medecin->tel other=$medecin->_mep_tel}}
    {{mb_ternary var=adresse test=$medecin->adresse value=$medecin->adresse other=$medecin->_mep_adresse}}
      <td style="width: 50%;">
        {{mb_label object=$medecin field=tel}} :
        {{$tel}}
      </td>
      <td>{{$adresse}}</td>
    </tr>
    <tr>
    {{mb_ternary var=fax test=$medecin->fax value=$medecin->fax other=$medecin->_mep_fax}}
    {{mb_ternary var=cp test=$medecin->cp value=$medecin->cp other=$medecin->_mep_cp}}
    {{mb_ternary var=ville test=$medecin->ville value=$medecin->ville other=$medecin->_mep_ville}}
      <td>
        {{mb_label object=$medecin field="fax"}} :
        {{$fax}}
      </td>
      <td>{{$cp}} {{$ville}}</td>
    </tr>
    <tr>
    {{mb_ternary var=tel2 test=$medecin->portable value=$medecin->portable other=$medecin->_mep_tel2}}
    {{mb_ternary var=email test=$medecin->email value=$medecin->email other=$medecin->_mep_email}}
      <td>
        {{mb_label object=$medecin field="portable"}} :
        {{$tel2}}
      </td>
      <td>
        {{mb_label object=$medecin field="email"}} :
        {{$email}}
          {{if $medecin->_mep_mssante_emails}}
                {{foreach from=$medecin->_mep_mssante_emails item=_address}}
                <p>{{$_address}}</p>
              {{/foreach}}
          {{/if}}
      </td>
    </tr>
    <tr>
    {{mb_ternary var=adeli test=$medecin->adeli value=$medecin->adeli other=$medecin->_mep_adeli}}
      <td>
        {{mb_label object=$medecin field="rpps"}} :
        {{mb_value object=$medecin field="rpps"}}
      </td>
      <td>
        {{mb_label object=$medecin field="adeli"}} :
        {{$adeli}}
      </td>
    </tr>
  {{if $medecin->orientations}}
    <tr>
      <td colspan="2">
        {{mb_value object=$medecin field="orientations"}}
      </td>
    </tr>
  {{/if}}
  {{if $medecin->complementaires}}
    <tr>
      <td colspan="2">
        {{mb_value object=$medecin field="complementaires"}}
      </td>
    </tr>
  {{/if}}
  {{if $object->_can->edit && ($object->_ref_module->_can->admin || !"dPpatients CMedecin edit_for_admin"|gconf)}}
    <tr>
      <td colspan="2" class="button">
        {{mb_script module="dPpatients" script="medecin" ajax="true"}}
        <button type="button" class="edit" onclick="Medecin.editMedecin('{{$medecin->_id}}')">
          {{tr}}Modify{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
</table>
