{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="title text" colspan="3">
    {{mb_include module=system template=inc_object_notes     }}
    {{mb_include module=system template=inc_object_idsante400}}
    {{mb_include module=system template=inc_object_history   }}
    {{mb_include module=system template=inc_object_uf}}
    {{if $object|instanceof:'Ox\Mediboard\Hospi\CAffectation'}}
      {{tr}}CAffectation{{/tr}} {{mb_include module=system template=inc_interval_datetime from=$object->entree to=$object->sortie}}
    {{else}}
      {{if $object->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
      {{/if}}
      {{tr}}CSejour{{/tr}} {{mb_include module=system template=inc_interval_date from=$object->entree to=$object->sortie}}
    {{/if}}
    {{if $app->_ref_user->isAdmin() && ('admin CBrisDeGlace enable_bris_de_glace'|gconf || 'admin CLogAccessMedicalData enable_log_access'|gconf)}}
      <a href="#" onclick="guid_access_medical('{{$object->_guid}}')" style="float:right;" >
        {{me_img src="planning.png" icon="paperclip me-primary"}}
      </a>
    {{/if}}
  </th>
</tr>
<tr {{if !$object->_alertes_ufs|@count}}style="display: none;"{{/if}}>
  <td colspan="3">
    {{mb_include module=hospi template=inc_alerte_ufs}}
  </td>
</tr>
<tr>
  <td rowspan="{{if $object|instanceof:'Ox\Mediboard\Hospi\CAffectation'}}3{{else}}2{{/if}}" style="width: 1px;">
    {{mb_include module=patients template=inc_vw_photo_identite mode=read patient=$patient size=50}}
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
      {{$patient}} {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
    </span>

    {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </td>
  <td>Du <strong>{{mb_value object=$object field=entree}}</strong></td>
</tr>
<tr>
  <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}</td>
  <td>Au <strong>{{mb_value object=$object field=sortie}}</strong></td>
</tr>

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour' && $object->_motif_complet}}
<tr>
  <td colspan="3" class="text">
    <strong>
      {{mb_value object=$object field=_motif_complet}}
    </strong>
  </td>
</tr>
{{/if}}

{{if $object|instanceof:'Ox\Mediboard\Hospi\CAffectation'}}
<tr>
  <td colspan="2" class="text">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
      <strong>
        {{if $sejour->_motif_complet}}
          {{mb_value object=$sejour field=_motif_complet}}
        {{else}}
          {{tr}}CSejour{{/tr}}
        {{/if}}
      </strong>
    </span>
  </td>
</tr>
<tr>
  <td colspan="2">
    Service {{$object->_ref_service}}

    {{if $object->praticien_id}}
     <br />
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_praticien}}
  {{/if}}
  </td>
  <td>
    {{if $object->lit_id}}
      Lit {{$object->_ref_lit}}
    {{else}}
      Couloir
    {{/if}}
  </td>
</tr>
{{/if}}

{{if $sejour->_couvert_c2s || $sejour->_couvert_ald}}
  <tr>
    <td colspan="3">
      {{if $sejour->_couvert_c2s}}C2S /{{/if}} {{if $sejour->_couvert_ald}}ALD{{/if}}
    </td>
  </tr>
{{/if}}

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
  <tr>
    <td class="text" colspan="2">
      {{if "dPplanningOp CSejour use_charge_price_indicator"|gconf != "no"}}
        <strong>{{mb_value object=$object field=charge_id}}</strong>
      {{else}}
        <strong>{{mb_value object=$object field=type}}</strong>
      {{/if}}

      {{assign var=rpu value=$sejour->_ref_rpu}}
      {{if $rpu && $rpu->_id}}
        <span class="texticon texticon-rpu" onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">RPU</span>
        {{if $rpu->mutation_sejour_id}}
          <span class="texticon texticon-mutation" onmouseover="ObjectTooltip.createEx(this, 'CSejour-{{$rpu->mutation_sejour_id}}');">Muta</span>
        {{/if}}
      {{/if}}
    </td>
    <td class="text">
      {{$object->_ref_group}}
    </td>
  </tr>

  {{if $object->rques}}
    <tr>
      <td class="text" colspan="3">
        <strong>{{mb_label object=$object field=rques}}</strong>
        <br />
        {{mb_value object=$object field=rques}}
      </td>
    </tr>
  {{/if}}
{{/if}}

{{if $object->etablissement_sortie_id}}
  <tr>
    <td colspan="4">
      {{mb_label object=$object field=etablissement_sortie_id}} :
      {{mb_value object=$object field=etablissement_sortie_id}}
    </td>
  </tr>
  {{if $object->destination}}
    <tr>
      <td colspan="4">
        {{mb_label object=$object field=destination}} :
        {{mb_value object=$object field=destination}}
      </td>
    </tr>
  {{/if}}
{{elseif $object->mode_sortie}}
  <tr>
    <td colspan="4">
      {{mb_label object=$object field=mode_sortie}} :
      {{mb_value object=$object field=mode_sortie}}
    </td>
  </tr>
{{/if}}

{{if "maternite"|module_active}}
  {{if $sejour->grossesse_id}}
    {{if $sejour->_ref_grossesse->_ref_naissances|@count}}
    <tr>
      <td colspan="3">
        Bébé{{if $sejour->_ref_grossesse->_ref_naissances|@count > 1}}s{{/if}} :
        <ul>
          {{foreach from=$sejour->_ref_grossesse->_ref_naissances item=_naissance}}
            <li>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_ref_sejour_enfant->_guid}}')">
                {{$_naissance->_ref_sejour_enfant}}
              </span>
            </li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
    {{/if}}
  {{elseif $sejour->_ref_naissance->_id}}
  {{assign var=sejour_maman value=$sejour->_ref_naissance->_ref_sejour_maman}}
  <tr>
    <td colspan="3">
      Maman :

      {{if $sejour_maman->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
      {{/if}}

      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_maman->_guid}}')">
        {{$sejour_maman}}
      </span>
    </td>
  </tr>
  {{/if}}
{{/if}}


{{if $affectations|@count}}
<tr>
  <td colspan="3">
    Affectations :
    <ul>
      {{foreach from=$affectations item=_affectation}}
        <li>
          {{if $_affectation->_id == $object->_id}}
            <strong>
          {{else}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_guid}}')">
          {{/if}}
          {{$_affectation}} {{mb_include module=system template=inc_interval_datetime from=$_affectation->entree to=$_affectation->sortie}}
          {{if $_affectation->_id == $object->_id}}
            </strong>
          {{else}}
            </span>
          {{/if}}
        </li>
      {{/foreach}}
    </ul>
  </td>
</tr>
{{/if}}
{{if $operations|@count}}
<tr>
  <td colspan="3">
    {{tr}}COperation|pl{{/tr}} :
    <ul>
      {{foreach from=$operations item=_operation}}
        <li>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')"
                {{if $_operation->annulee == 1}}style="background-color: #f88;"{{/if}}>
            {{tr var1=$_operation->_datetime|date_format:$conf.datetime}}COperation-Intervention of %s{{/tr}}

          {{if $_operation->annulee == 1}}
            <span class="category cancelled">
              &mdash; {{tr}}COperation-annulee{{/tr}}
            </span>
          {{/if}}

          </span>
        </li>
      {{/foreach}}
    </ul>
  </td>
</tr>
{{/if}}
