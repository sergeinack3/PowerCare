{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("filterGestePerop");

    GestePerop.userAutocomplete(form);
    GestePerop.functionAutocomplete(form);
    GestePerop.groupAutocomplete(form);

    GestePerop.loadGestesPerop(form);
  });
</script>

<table class="main layout">
  <tr>
    <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next()); GestePerop.loadGestesPerop(getForm('filterGestePerop'));"></td>
    <td>
      <fieldset class="me-margin-top-8">
        <legend><i class="fas fa-filter"></i> {{tr}}filters{{/tr}}</legend>
        <form name="filterGestePerop" method="get">
          <input type="hidden" name="page" value="{{$page}}"/>
          <table class="form me-no-box-shadow">
            <tr>
              <th><label for="keywords">{{tr}}Keywords{{/tr}}</label></th>
              <td>
                <input type="text" name="keywords" value="{{$keywords}}"/>
              </td>

              <th>{{mb_label object=$filtre field=function_id}}</th>
              <td>
                {{mb_field object=$filtre field=function_id hidden=1
                onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false);\$V(this.form.current_group, 0, false);"}}
                <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}"/>
                <button type="button"
                        onclick="GestePerop.eraseInput(this.form.elements.function_id, this.form.elements.function_id_view);"
                        title="{{tr}}common-action-Erase{{/tr}}">
                  <i class="fas fa-eraser"></i>
                </button>
              </td>
              <th>{{tr}}CGestePerop-Current group{{/tr}}</th>
              <td>
                <input type="hidden" name="current_group" value="0"/>
                <input type="checkbox" name="_current_group" onchange="$V(this.form.current_group, this.checked ? 1 : 0);
          $V(this.form.function_id, '', false); $V(this.form.function_id_view, '', false);
          $V(this.form.user_id, '', false); $V(this.form.user_id_view, '', false);"/>
              </td>
            </tr>
            <tr>
              <th>{{mb_label object=$filtre field=categorie_id}}</th>
              <td>{{mb_field object=$filtre field=categorie_id options=$evenement_categories}}</td>

              <th>{{mb_label object=$filtre field=user_id}}</th>
              <td colspan="3">
                {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false);
          if (this.form.function_id_view) {
            \$V(this.form.function_id_view, '', false);
          } \$V(this.form.current_group, 0, false);"}}
                <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}"/>
                <button type="button" onclick="GestePerop.eraseInput(this.form.elements.user_id, this.form.elements.user_id_view);"
                        title="{{tr}}common-action-Erase{{/tr}}">
                  <i class="fas fa-eraser"></i>
                </button>
              </td>
            </tr>

            <tr>
              <td class="button" colspan="6">
                <button type="button" onclick="GestePerop.loadGestesPerop(this.form);">
                  <i class="fas fa-search"></i> {{tr}}Search{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
    </td>
  </tr>
</table>

<div id="list_gestes_perop"></div>
