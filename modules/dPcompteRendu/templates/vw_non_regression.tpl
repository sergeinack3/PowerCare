{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  testCR = function(sens) {
    if (!sens) {
      sens = "left";
    }
    var form = getForm("compareRendus");

    form.target = "pdf_" + sens;
    $V(form.sens, sens);

    form.submit();
  };

  Main.add(function() {
    ViewPort.SetAvlHeight("pdf_left", 1);
    ViewPort.SetAvlHeight("pdf_right", 1);

    var form = getForm("compareRendus");

    var urlUsers = new Url("mediusers", "ajax_users_autocomplete");
    urlUsers.addParam("edit", "1");
    urlUsers.addParam("input_field", "user_id_view");
    urlUsers.autoComplete(form.user_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.user_id, id);
      }
    });

    var urlFunctions = new Url("mediusers", "ajax_functions_autocomplete");
    urlFunctions.addParam("edit", "1");
    urlFunctions.addParam("input_field", "function_id_view");
    urlFunctions.addParam("view_field", "text");
    urlFunctions.autoComplete(form.function_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.function_id, id);
      }
    });

    new Url("compteRendu", "autocomplete")
      .addParam("mode_store", "0")
      .addParam("type", "body")
      .autoComplete(form.keywords_modele, '', {
        method: "get",
        minChars: 2,
        dropdown: true,
        width: "250px",
        callback: function(input, query) {
          var form = input.form;
          return query +
            "&user_id="      + $V(form.user_id)      +
            "&function_id="  + $V(form.function_id)
        },
        afterUpdateElement: function(field, selected) {
          var form = field.form;
          $V(form.cr_id, selected.down("div.id").getText().trim());
          $V(form.keywords_modele, selected.down("div").getText().trim());
          testCR();
          testCR("right");
        }
      });
  });
</script>

<form name="compareRendus" method="get">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="raw" value="ajax_test_cr" />
  <input type="hidden" name="sens" />

  <table class="form">
    <tr>
      <th>{{mb_label object=$filtre field=user_id}}</th>
      <td>
        {{mb_field object=$filtre field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false); \$V(this.form.function_id_view, '', false);"}}
        <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}" />
      </td>
      <td>
        <input type="text" placeholder="&mdash; {{tr}}CCompteRendu-modele-one{{/tr}}" name="keywords_modele" class="autocomplete str" />
        <input type="hidden" name="cr_id" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filtre field=function_id}}</th>
      <td colspan="2">
        {{mb_field object=$filtre field=function_id hidden=1 onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false);"}}
        <input type="text" name="function_id_view" value="{{$filtre->_ref_function}}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="halfPane button">
        <button type="button" class="change notext" style="float: left" onclick="testCR('left');"></button>
        {{mb_include module=compteRendu template=inc_select_factory sens=left}}
      </td>
      <td class="button" class="button">
        <button type="button" class="change notext" style="float: right" onclick="testCR('right');"></button>
        {{mb_include module=compteRendu template=inc_select_factory sens=right}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div id="pdf_left" style="overflow: hidden;">
          <iframe name="pdf_left" style="width: 100%; height: 100%;"></iframe>
        </div>
      </td>
      <td>
        <div id="pdf_right" style="overflow: hidden;">
          <iframe name="pdf_right" style="width: 100%; height: 100%;"></iframe>
        </div>
      </td>
    </tr>
  </table>
</form>
