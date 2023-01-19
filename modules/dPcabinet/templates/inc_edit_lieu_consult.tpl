{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete ajax=$ajax}}

<script>
  Main.add(function() {
    InseeFields.initCPVille('edit-lieu', 'cp', 'ville');
  });
</script>

<form name="edit-lieu" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close.curry())">
  {{mb_key object=$lieu}}
  {{mb_class object=$lieu}}
  {{mb_field object=$lieu field=_prat_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$lieu}}

    <tr>
      <th>{{mb_label object=$lieu field="label"}}</th>
      <td>{{mb_field object=$lieu field="label"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$lieu field="adresse"}}</th>
      <td>{{mb_field object=$lieu field="adresse"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$lieu field="cp"}}</th>
      <td>{{mb_field object=$lieu field="cp"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$lieu field="ville"}}</th>
      <td>{{mb_field object=$lieu field="ville"}}</td>
    </tr>

    {{if $can->admin}}
      <tr>
        <th>{{mb_label object=$lieu field="active"}}</th>
        <td>{{mb_field object=$lieu field="active"}}</td>
      </tr>
    {{/if}}

    {{mb_include module=system template=inc_form_table_footer object=$lieu options_ajax="Control.Modal.close"}}
  </table>
</form>
