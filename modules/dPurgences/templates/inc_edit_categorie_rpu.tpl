{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCategorieRPU" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$categorie_rpu}}
  {{mb_key   object=$categorie_rpu}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$categorie_rpu}}

    <tr>
      <th class="halfPane">
        {{mb_label object=$categorie_rpu field=motif}}
      </th>
      <td>
        {{mb_field object=$categorie_rpu field=motif}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$categorie_rpu field=actif typeEnum=checkbox}}
      </th>
      <td>
        {{mb_field object=$categorie_rpu field=actif typeEnum=checkbox}}
      </td>
    </tr>

    {{if $categorie_rpu->_id}}
      <tr>
        <th>
          {{mb_label object=$categorie_rpu field=_ref_icone}}
        </th>
        <td>
          {{mb_include module=files template=inc_named_file object=$categorie_rpu name=icone.jpg mode=edit size=20}}
        </td>
      </tr>
    {{/if}}

    {{assign var=libelle_cat value=$categorie_rpu->motif|smarty:nodefaults|JSAttribute}}
    {{mb_include module=system template=inc_form_table_footer object=$categorie_rpu
                 options="{typeName: 'la catégorie RPU', objName: '$libelle_cat'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>