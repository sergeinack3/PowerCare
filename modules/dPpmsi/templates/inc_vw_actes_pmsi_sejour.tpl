{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $sejour->type == "ssr" && "ssr"|module_active}}
  {{mb_script module=ssr script=cotation_rhs ajax=true}}
  <script>
    Main.add(function() {
      CotationRHS.refresh('{{$sejour->_id}}');
    });
  </script>
{{/if}}

<table class="main layout">
  <tr>
    {{if $sejour->type != "ssr"}}
    <td class="halfPane">
      <fieldset>
        <legend>Diagnostics PMSI</legend>
        <div id="diags_pmsi"></div>
      </fieldset>
    </td>
    {{/if}}
    <td {{if $sejour->type == "ssr" && "ssr"|module_active}}colspan="2"{{/if}}>
      <fieldset>
        <legend>Diagnostics du dossier</legend>
        <div id="diags_dossier"></div>
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>Antécédents</legend>
        {{mb_include module=pmsi template=inc_vw_actes_pmsi_ant}}
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Traitements personnels</legend>
        {{mb_include module=pmsi template=inc_vw_actes_pmsi_trait}}
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <div id="export_CSejour_{{$sejour->_id}}"></div>
    </td>
  </tr>
</table>
{{mb_include module=pmsi template=inc_codage_actes subject=$sejour}}

{{if $sejour->type == "ssr" && "ssr"|module_active}}
  <fieldset>
    <legend>{{tr}}CRHS{{/tr}}</legend>
    <div id="cotation-rhs-{{$sejour->_id}}"></div>
  </fieldset>
{{/if}}