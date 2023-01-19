{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=$ajax}}

<div class="small-{{if !$siblings|@count && !$patient->_ref_patient_links|@count}}info{{else}}warning{{/if}}">
  {{tr}}CPatient-Alert identity non qual{{/tr}}

  <br />
  <br />

  <strong>
    {{if !$siblings|@count && !$patient->_ref_patient_links|@count}}
      {{tr}}CPatient-Can validate identity{{/tr}}
    {{elseif $can_merge}}
      {{tr}}CPatient-Select identities for merge{{/tr}}
    {{else}}
      {{tr}}CPatient-Select identities for link{{/tr}}
    {{/if}}
  </strong>
</div>

<table id="manage_identity" class="main">
  <tr>
    <th class="title" colspan="2">
      {{$patient->_view}} [{{$patient->_IPP}}]
    </th>
  </tr>
  <tr>
    <td class="halfPane">
      <strong>{{mb_label object=$patient field=nom}}</strong>
      {{mb_value object=$patient field=nom}}
    </td>

    <td>
      <strong>{{mb_label object=$patient field=adresse}}</strong>
      {{mb_value object=$patient field=adresse}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>{{mb_label object=$patient field=prenom}}</strong>
      {{mb_value object=$patient field=prenom}}
    </td>

    <td>
      <strong>{{mb_label object=$patient field=ville}}</strong>
      {{mb_value object=$patient field=cp}}
      {{mb_value object=$patient field=ville}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>{{mb_label object=$patient field=nom_jeune_fille}}</strong>
      {{mb_value object=$patient field=nom_jeune_fille}}
    </td>

    <td>
      <strong>{{mb_label object=$patient field=tel}}</strong>
      {{mb_value object=$patient field=tel}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>{{mb_label object=$patient field=naissance}}</strong>
      {{mb_value object=$patient field=naissance}} <em>({{mb_value object=$patient field=_age}})</em>
    </td>

    <td>
      <strong>{{mb_label object=$patient field=tel2}}</strong>
      {{mb_value object=$patient field=tel2}}
    </td>
  </tr>

  <tr>
    <td>
      <strong>{{mb_label object=$patient field=sexe}}</strong>
      {{mb_value object=$patient field=sexe}}
    </td>

    <td>
      <strong>{{mb_label object=$patient field=tel_pro}}</strong>
      {{mb_value object=$patient field=tel_pro}}
    </td>
  </tr>

  <tr>
    <th class="title">
      {{tr}}CPatient-Doubloons suspected{{/tr}}
    </th>
    <th class="title">
      {{tr}}CPatient-Patients linked{{/tr}}
    </th>
  </tr>

  <tr>
    <td>
      <form name="getDoubloons" method="get" class="prepared">
        {{foreach from=$siblings item=_sibling}}
        {{mb_include module=patients template=inc_manage_identity_line _patient=$_sibling}}
        {{foreachelse}}
        <div class="empty">
          {{tr}}CPatient-No doubloon suspected{{/tr}}
          {{/foreach}}
      </form>
    </td>
    <td>
      <form name="getLinks" method="get" class="prepared">
        {{foreach from=$links item=_link}}
          {{mb_include module=patients template=inc_manage_identity_line _patient=$_link}}
          {{foreachelse}}
          <div class="empty">
            {{tr}}CPatient-back-patient_link1.empty{{/tr}}
          </div>
        {{/foreach}}
      </form>
    </td>
  </tr>

  <tr>
    <th class="title" colspan="2">
      {{tr}}Actions{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <form name="validateIdentity" method="post">
        {{mb_class object=$patient}}
        {{mb_key   object=$patient}}

        <input type="hidden" name="status" />

        <button type="button" class="big" style="display: inline-block;" onclick="IdentityValidator.validateIdentity();">
          {{tr}}CPatient-Validate identity{{/tr}}
        </button>

        {{if $can_merge}}
          <button type="button" class="big" style="display: inline-block;" disabled onclick="IdentityValidator.merge(null, 'PROV');">
            {{tr}}CPatient-Merge and qualify selected patients{{/tr}}
          </button>
        {{/if}}

        <button type="button" class="big" style="display: inline-block;" disabled onclick="IdentityValidator.link('PROV');">
          {{tr}}CPatient-Link and qualify selected patients{{/tr}}
        </button>
      </form>
    </td>
  </tr>
</table>
