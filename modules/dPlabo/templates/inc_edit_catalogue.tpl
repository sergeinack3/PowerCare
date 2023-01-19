{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCatalogue" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$catalogue}}
  {{mb_key   object=$catalogue}}
  {{mb_field object=$catalogue field=_locked hidden=true}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$catalogue}}

    {{if !$catalogue->_locked}}
    <tr>
      <th>{{mb_label object=$catalogue field=function_id}}</th>
      <td>
        <select name="function_id" {{if $catalogue->pere_id}}disabled style="display: none;"{{/if}}>
          <option value="">&mdash; {{tr}}common-all|f|pl{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$functions selected=$catalogue->function_id}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$catalogue field=pere_id}}</th>
      <td>
        <select name="pere_id" onchange="Catalogue.checkRefFunction(this.value);">
          <option value="">&mdash; Catalogue racine</option>
          {{assign var="selected_id" value=$catalogue->pere_id}}
          {{assign var="exclude_id" value=$catalogue->_id}}
          {{foreach from=$listCatalogues item="_catalogue"}}
          {{if !$_catalogue->_locked}}
          {{mb_include module=labo template=options_catalogues}}
          {{/if}}
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{/if}}

    <tr>
      <th>{{mb_label object=$catalogue field=identifiant}}</th>
      <td>{{mb_field object=$catalogue field=identifiant}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$catalogue field=libelle}}</th>
      <td>{{mb_field object=$catalogue field=libelle}}</td>
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$catalogue
                 options="{typeName: \$T('CCatalogueLabo'), objName: '`$catalogue->_view`'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $catalogue->_id}}
  <!-- Liste des exmanens du catalogue sélectionné -->
  {{assign var="examens" value=$catalogue->_ref_examens_labo}}
  {{assign var="examen_id" value=""}}
  {{mb_include module=labo template=list_examens}}
{{/if}}