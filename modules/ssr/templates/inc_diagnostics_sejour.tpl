{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=PMSI ajax=true}}

<script>
  Main.add(function () {
    PMSI.loadDiagsDossier('{{$sejour->_id}}', '{{$rhs->_id}}', 1);
  });
</script>

<table class="main me-small">
  <tr>
    <th class="title me-border-radius-top" colspan="6">
      {{mb_include module=system template=inc_object_idsante400 object=$rhs}}
      {{mb_include module=system template=inc_object_history    object=$rhs}}
      {{if $rhs->FPP || $rhs->MMP || $rhs->AE || $rhs->DAS || $rhs->DAD}}
        <button type="button" class="duplicate notext compact" style="float: left;"
                onclick="CotationRHS.duplicate('{{$rhs->_id}}', '{{$rhs->sejour_id}}', 'diagnostics');">{{tr}}Duplicate{{/tr}}</button>
      {{/if}}
      {{tr}}PMSI.Diagnostics{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      <div id="diags_dossier_{{$rhs->_id}}"></div>
    </td>
  </tr>

  <tr>
    <td>
      {{mb_include module=ssr template="inc_diagnostics_rhs" sejour=$rhs->_ref_sejour}}
    </td>
  </tr>
</table>
