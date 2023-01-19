{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("filterProtocolesGestePerop");
    GestePerop.userAutocomplete(form);
    GestePerop.functionAutocomplete(form);
    GestePerop.loadProtocolesGestesPerop(form);
  });
</script>

<form name="filterProtocolesGestePerop" method="get">
  <input type="hidden" name="page" value="{{$page}}" />
  <fieldset class="me-margin-top-8">
    <legend><i class="fas fa-filter"></i> {{tr}}Filter{{/tr}}</legend>
    <table class="form me-no-box-shadow">
      <tr>
        <th>{{mb_label object=$filtre field=user_id}}</th>
        <td>
          {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false);
          if (this.form.function_id_view) {
            \$V(this.form.function_id_view, '', false);
          }"}}
          <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
          <button type="button" class="compact" title="{{tr}}common-action-Erase{{/tr}}"
                  onclick="GestePerop.eraseInput(this.form.elements.user_id, this.form.elements.user_id_view);">
            <i class="fas fa-eraser"></i>
          </button>
        </td>

        <th>{{mb_label object=$filtre field=function_id}}</th>
        <td>
          {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false);"}}
          <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
          <button type="button" class="compact" title="{{tr}}common-action-Erase{{/tr}}"
                  onclick="GestePerop.eraseInput(this.form.elements.function_id, this.form.elements.function_id_view);">
            <i class="fas fa-eraser"></i>
          </button>
        </td>

        <th><label for="keywords">{{tr}}Keywords{{/tr}}</label></th>
        <td>
          <input type="text" name="keywords" value="" />
        </td>
      </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="button" onclick="GestePerop.loadProtocolesGestesPerop(this.form);">
          <i class="fas fa-search" style="font-size: 1.2em;"></i> {{tr}}Filter{{/tr}}
        </button>
      </td>
    </tr>
  </table>
  </fieldset>
</form>

<div id="list_protocoles_gestes_perop" class="me-padding-left-4 me-padding-right-4"></div>
