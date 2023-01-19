{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCategoryGroup" action="" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_key   object=$categorie_groupe_patient}}
  {{mb_class object=$categorie_groupe_patient}}

  {{mb_field object=$categorie_groupe_patient field=group_id hidden=true}}
  {{mb_field object=$categorie_groupe_patient field=type     hidden=true}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$categorie_groupe_patient}}
    <tr>
      <th>{{mb_label object=$categorie_groupe_patient field=nom}}</th>
      <td>{{mb_field object=$categorie_groupe_patient field=nom}}</td>
    </tr>
    <tr>
     {{mb_include module=system template=inc_form_table_footer object=$categorie_groupe_patient options="{typeName: 'la catégorie', objName: '`$categorie_groupe_patient->_view`'}" options_ajax="Control.Modal.close"}}
    </tr>
  </table>
</form>
