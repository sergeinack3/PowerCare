{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editLaboratoireAnapath" method="post" onsubmit="return LaboAnapath.submit(this);">
  {{mb_class object=$laboratoire_anapath}}
  {{mb_key object=$laboratoire_anapath}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$laboratoire_anapath}}

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=libelle nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=libelle}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool mb_object=$laboratoire_anapath mb_field=actif nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=actif}}
      {{/me_form_bool}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=adresse nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=adresse}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=cp nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=cp}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=ville nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=ville}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=tel nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=tel}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=fax nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=fax}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$laboratoire_anapath mb_field=mail nb_cells=2}}
        {{mb_field object=$laboratoire_anapath field=mail}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$laboratoire_anapath
          options="{typeName: '', objName: `$laboratoire_anapath->_view`}"
          options_ajax="Control.Modal.close"}}
    </tr>
  </table>
</form>
