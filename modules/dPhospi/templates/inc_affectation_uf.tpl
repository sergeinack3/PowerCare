{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=affectation_uf}}

<table class="form">
  <tr>
    <th colspan="5" class="title">
      {{tr}}{{$object->_class}}-uf-title-choice{{/tr}} '{{$object}}'
    </th>
  </tr>

  <tr>
    <th class="section" colspan="5">{{tr}}CUniteFonctionnelle{{/tr}}</th>
  </tr>
  {{foreach from=$affectations_uf item=_affectation_uf}}
    <tr>
      <td class="narrow">
        <form name="delete-{{$_affectation_uf->_guid}}" action="?m={{$m}}" method="post">
          <button type="button" onclick="AffectationUf.onDeletion(this.form, function() {Control.Modal.refresh()});" class="remove notext">{{tr}}Remove{{/tr}}</button>
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="del" value="1" />
          <input type="hidden" name="dosql" value="do_affectation_uf_aed" />
          <input type="hidden" name="affectation_uf_id" value="{{$_affectation_uf->_id}}" />
        </form>
      </td>
      <td>
        {{mb_value object=$_affectation_uf field=uf_id}}
      </td>
      <td>
        <strong>
          {{tr}}CUniteFonctionnelle.type.{{$_affectation_uf->_ref_uf->type}}{{/tr}}
        </strong>
          {{assign var=um_pmsi value=$_affectation_uf->_ref_uf->_ref_um}}

          {{if $um_pmsi && $um_pmsi->_id}}
            <br />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$um_pmsi->_guid}}')">{{tr}}CUniteFonctionnelle-Pmsi UM{{/tr}}</span>
          {{/if}}
      </td>
      <td class="text empty">
        {{assign var=uf_aff value=$_affectation_uf->_ref_uf}}
        {{if $uf_aff->date_debut && $uf_aff->date_fin}}
          {{tr}}date.From{{/tr}} {{mb_value object=$uf_aff field=date_debut}}
          {{tr}}date.To{{/tr}} {{mb_value object=$uf_aff field=date_fin}}
        {{elseif $uf_aff->date_debut}}
          {{tr}}date.From_long{{/tr}} {{mb_value object=$uf_aff field=date_debut}}
        {{elseif $uf_aff->date_fin}}
          {{tr}}date.To_long{{/tr}} {{mb_value object=$uf_aff field=date_fin}}
        {{/if}}
      </td>
      <td>{{mb_value object=$uf_aff field=type_sejour}}</td>
    </tr>
  {{/foreach}}

  <tr>
    <td colspan="5">
      <form name="create-CAffectationUniteFonctionnelle" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.refresh();}});">
        <input type="hidden" name="m" value="hospi" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_affectation_uf_aed" />
        <input type="hidden" name="object_class" value="{{$object->_class}}" />
        <input type="hidden" name="object_id" value="{{$object->_id}}" />
        <button class="add notext" type="submit" disabled id="create_aff_uf">
          {{tr}}Add{{/tr}}
        </button>
        <select name="uf_id" onchange="$('create_aff_uf').disabled = $V(this) ? '' : 'disabled';">
          <option value="">&mdash; {{tr}}CUniteFonctionnelle{{/tr}}</option>
          {{foreach from=$ufs item=_ufs key=type}}
            <optgroup label="{{tr}}CUniteFonctionnelle.type.{{$type}}{{/tr}}">
              {{foreach from=$_ufs item=uf}}
                {{assign var=uf_id value=$uf->_id}}
                <option value="{{$uf->_id}}" {{if isset($ufs_selected.$uf_id|smarty:nodefaults)}}disabled{{/if}}>
                  {{$uf->libelle}}
                  {{if $uf->date_debut && $uf->date_fin}}
                    {{tr}}date.From{{/tr}} {{mb_value object=$uf field=date_debut}}
                    {{tr}}date.To{{/tr}} {{mb_value object=$uf field=date_fin}}
                  {{elseif $uf->date_debut}}
                    {{tr}}date.From_long{{/tr}} {{mb_value object=$uf field=date_debut}}
                  {{elseif $uf->date_fin}}
                    {{tr}}date.To_long{{/tr}} {{mb_value object=$uf field=date_fin}}
                  {{/if}}
                </option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </form>
    </td>
  </tr>

  {{if $object|instanceof:'Ox\Mediboard\Mediusers\CMediusers' || $object|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}
    <tr>
      <th class="section" colspan="5">{{tr}}CUniteFonctionnelle.secondaire{{/tr}}</th>
    </tr>
    {{foreach from=$affectations_secondaire_uf item=_affectation_uf}}
      <tr>
        <td class="narrow">
          <form name="delete-{{$_affectation_uf->_guid}}" action="?m={{$m}}" method="post">
            <button type="button" onclick="AffectationUf.onDeletion(this.form, function() {Control.Modal.refresh()});" class="remove notext">{{tr}}Remove{{/tr}}</button>
            {{mb_key   object=$_affectation_uf}}
            {{mb_class object=$_affectation_uf}}
          </form>
        </td>
        <td>
          {{mb_value object=$_affectation_uf field=uf_id}}
        </td>
        <td>
          <strong>
            {{tr}}CUniteFonctionnelle.type.{{$_affectation_uf->_ref_uf->type}}{{/tr}}
          </strong>
        </td>
        <td class="text empty">
          {{assign var=uf_aff value=$_affectation_uf->_ref_uf}}
          {{if $uf_aff->date_debut && $uf_aff->date_fin}}
            {{tr}}date.From{{/tr}} {{mb_value object=$uf_aff field=date_debut}}
            {{tr}}date.To{{/tr}} {{mb_value object=$uf_aff field=date_fin}}
          {{elseif $uf_aff->date_debut}}
            {{tr}}date.To_long{{/tr}} {{mb_value object=$uf_aff field=date_fin}}
          {{elseif $uf_aff->date_fin}}
            {{tr}}date.From_long{{/tr}} {{mb_value object=$uf_aff field=date_fin}}
          {{/if}}
        </td>
        <td>{{mb_value object=$uf_aff field=type_sejour}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="5">
        <form name="create-CAffectationUfSecondaire" action="" method="post"
              onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.refresh();}});">
          {{mb_key   object=$affectation_uf_second}}
          {{mb_class object=$affectation_uf_second}}
          <input type="hidden" name="object_class" value="{{$object->_class}}" />
          <input type="hidden" name="object_id" value="{{$object->_id}}" />
          <button class="add notext" type="submit" {{if !is_array($ufs) || count($ufs) ==0 }}disabled{{/if}}>{{tr}}Add{{/tr}}</button>
          <select name="uf_id">
            <option value="">&mdash; {{tr}}CUniteFonctionnelle.secondaire{{/tr}}</option>
            {{foreach from=$ufs item=_ufs key=type}}
              <optgroup label="{{tr}}CUniteFonctionnelle.type.{{$type}}{{/tr}}">
                {{foreach from=$_ufs item=uf}}
                  {{assign var=uf_id value=$uf->_id}}
                  <option value="{{$uf->_id}}" {{if isset($ufs_secondaire_selected.$uf_id|smarty:nodefaults)}}disabled{{/if}}>
                    {{$uf->libelle}}
                    {{if $uf->date_debut && $uf->date_fin}}
                      {{tr}}date.From{{/tr}} {{mb_value object=$uf field=date_debut}}
                      {{tr}}date.To{{/tr}} {{mb_value object=$uf field=date_fin}}
                    {{elseif $uf->date_debut}}
                      {{tr}}date.To_long{{/tr}} {{mb_value object=$uf field=date_fin}}
                    {{elseif $uf->date_fin}}
                      {{tr}}date.From_long{{/tr}} {{mb_value object=$uf field=date_fin}}
                    {{/if}}
                  </option>
                {{/foreach}}
              </optgroup>
            {{/foreach}}
          </select>
        </form>
      </td>
    </tr>
  {{/if}}
</table>
