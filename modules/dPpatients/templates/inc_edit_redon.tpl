{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editRedon" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$redon}}
  {{mb_key   object=$redon}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$redon}}

    <tr>
      {{me_form_field mb_object=$redon mb_field=date_pose nb_cells=2}}
        {{mb_field object=$redon field=date_pose form=editRedon register=true}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$redon mb_field=date_retrait nb_cells=2}}
        {{mb_field object=$redon field=date_retrait form=editRedon register=true}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool mb_object=$redon mb_field=sous_vide nb_cells=2}}
        {{mb_field object=$redon field=sous_vide form=editRedon register=true}}
      {{/me_form_bool}}
    </tr>

    <tr>
      {{me_form_bool mb_object=$redon mb_field=actif nb_cells=2}}
        {{mb_field object=$redon field=actif form=editRedon register=true}}
      {{/me_form_bool}}
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$redon}}
  </table>
</form>