{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editAutorisation" method="post" onsubmit="return AutorisationPermission.submit(this);">
  {{mb_class object=$autorisation_permission}}
  {{mb_key   object=$autorisation_permission}}

  {{mb_field object=$autorisation_permission field=praticien_id hidden=true}}
  {{mb_field object=$autorisation_permission field=sejour_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$autorisation_permission}}

    <tr>
      <th>
        {{mb_label object=$autorisation_permission field=debut}}
      </th>
      <td>
        {{mb_field object=$autorisation_permission field=debut form=editAutorisation register=true}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$autorisation_permission field=duree}}
      </th>
      <td>
        {{mb_field object=$autorisation_permission field=duree form=editAutorisation increment=true onchange=AutorisationPermission.calculFin()}}
        {{tr}}common-hour|pl{{/tr}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$autorisation_permission field=_fin}}
      </th>
      <td>
        {{mb_field object=$autorisation_permission field=_fin form=editAutorisation register=true onchange=AutorisationPermission.calculDuree()}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$autorisation_permission field=motif}}
      </th>
      <td>
        {{mb_field object=$autorisation_permission field=motif form=editAutorisation}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$autorisation_permission field=rques}}
      </th>
      <td>
        {{mb_field object=$autorisation_permission field=rques form=editAutorisation}}
      </td>
    </tr>

    {{mb_include module=system template=inc_form_table_footer object=$autorisation_permission
    options_ajax="{onComplete: function() { Control.Modal.close(); Control.Modal.refresh(); } }"}}
  </table>
</form>
