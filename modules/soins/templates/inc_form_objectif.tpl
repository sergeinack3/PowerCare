{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editObjectif" method="post" onsubmit="onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$objectif_soin}}
  {{mb_key object=$objectif_soin}}
  {{mb_field object=$objectif_soin field=sejour_id hidden=true}}
  {{mb_field object=$objectif_soin field=date hidden=true}}
  {{mb_field object=$objectif_soin field=user_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$objectif_soin}}
    <tr>
      <th class="narrow">{{mb_label object=$objectif_soin field=libelle}}</th>
      <td>{{mb_field object=$objectif_soin field=libelle form="editObjectif" aidesaisie="validateOnBlur:0"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=statut}}</th>
      <td>{{mb_field object=$objectif_soin field=statut}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=objectif_soin_categorie_id}}</th>
      <td>
        <select name="objectif_soin_categorie_id">
        <option value="">&ndash; {{tr}}CObjectifSoinCategorie.None{{/tr}}</option>
          {{foreach from=$categories_objectif_soin item=_categorie}}
            <option value="{{$_categorie->_id}}" {{if $_categorie->_id == $objectif_soin->objectif_soin_categorie_id}}selected{{/if}}>
            {{$_categorie->libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=priorite}}</th>
      <td>{{mb_field object=$objectif_soin field=priorite}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=intervenants}}</th>
      <td class="text">{{mb_field object=$objectif_soin field=intervenants form="editObjectif" aidesaisie="validateOnBlur:0"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=moyens}}</th>
      <td class="text">{{mb_field object=$objectif_soin field=moyens form="editObjectif" aidesaisie="validateOnBlur:0"}}</td>
    </tr>
    <tr>
      <th></th>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=commentaire}}</th>
      <td class="text">{{mb_field object=$objectif_soin field=commentaire form="editObjectif" aidesaisie="validateOnBlur:0"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$objectif_soin field=delai}}</th>
      <td>
        {{mb_field object=$objectif_soin field=delai register=true form="editObjectif"}}
        {{mb_field object=$objectif_soin field=alerte typeEnum=checkbox}}
        {{mb_label object=$objectif_soin field=alerte}}
      </td>
    </tr>
    {{if $objectif_soin->_id}}
      <tr>
        <th>{{mb_label object=$objectif_soin field=resultat}}</th>
        <td>{{mb_field object=$objectif_soin field=resultat}}</td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        {{if $objectif_soin->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {
                    ajax:1,
                    objName:'{{$objectif_soin->libelle|smarty:nodefaults|JSAttribute}}'},
                    function() { Control.Modal.close();} );">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $objectif_soin->_id}}
  <div id="reevals-{{$objectif_soin->_guid}}">
    {{mb_include module=soins template=vw_list_reeval_objectif}}
  </div>
{{/if}}
