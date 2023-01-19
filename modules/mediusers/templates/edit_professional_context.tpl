{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form}}

<div class="small-info">
  {{tr}}mediusers-msg-You are invited to fill your professional context.{{/tr}}
</div>

{{* Do not forget to add additional fields in CMediusers::$professional_context_fields in order to be able to store the object *}}
<form name="{{$form}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="mediusers" />
  <input type="hidden" name="dosql" value="do_store_professional_context" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$user colspan=2}}

    <tr>
      <th>{{mb_label object=$user field=spec_cpam_id}}</th>
      <td>{{mb_field object=$user field=spec_cpam_id options=$spec_cpam style='width: 250px;' class='notNull'}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$user field=secteur}}</th>
      <td>{{mb_field object=$user field=secteur emptyLabel='Choose' class='notNull'}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$user field=pratique_tarifaire}}</th>
      <td>{{mb_field object=$user field=pratique_tarifaire}}</td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="save">
          {{tr}}common-action-Save{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>