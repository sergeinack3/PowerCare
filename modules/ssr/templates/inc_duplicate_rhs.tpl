{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="duplicateRHS" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="do_duplicate_rhs" />
  <input type="hidden" name="part" value="{{$part}}" />
  {{mb_key object=$rhs}}

  <table class="main">
    <tr>
      <td>
        <fieldset>
          <legend>
            {{tr}}CRHS-Duplication params{{/tr}}
          </legend>

          {{mb_label object=$rhs field=_nb_weeks}} :
          {{mb_field object=$rhs field=_nb_weeks form=duplicateRHS increment=true min=1 max=$rhs->_nb_weeks}}
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        {{if $part === "dependances"}}
          {{mb_include module=ssr template=inc_dependances_rhs_charged}}
        {{elseif $part === "diagnostics"}}
          {{mb_include module=ssr template=inc_diagnostics_rhs readonly=1}}
        {{elseif $part === "activites"}}
          {{mb_include module=ssr template=inc_lines_rhs read_only=1 mode_duplicate=1}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>