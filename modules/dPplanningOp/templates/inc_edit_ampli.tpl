{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editAmpli" method="post" onsubmit="return Ampli.submit(this);">
  {{mb_class object=$ampli}}
  {{mb_key object=$ampli}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ampli}}

    <tr>
      {{me_form_field mb_object=$ampli mb_field=libelle nb_cells=2}}
        {{mb_field object=$ampli field=libelle}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool mb_object=$ampli mb_field=actif nb_cells=2}}
        {{mb_field object=$ampli field=actif}}
      {{/me_form_bool}}
    </tr>

    <tr>
      {{me_form_field mb_object=$ampli mb_field=unite_rayons_x nb_cells=2}}
        {{mb_field object=$ampli field=unite_rayons_x emptyLabel=CAmpli.unite_rayons_x.}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$ampli mb_field=unite_pds nb_cells=2}}
        {{mb_field object=$ampli field=unite_pds emptyLabel=CAmpli.unite_pds.}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$ampli
          options="{typeName: '', objName: '`$ampli->_view`'}"
          options_ajax="Control.Modal.close"}}
    </tr>
  </table>
</form>
