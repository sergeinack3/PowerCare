{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshRegistre = function (page, date_min, date_max) {
    new Url("maternite", "ajax_vw_registre")
      .addNotNullParam("page", page)
      .addNotNullParam("_date_min", date_min)
      .addNotNullParam("_date_max", date_max)
      .requestUpdate("registre_area");
  };

  alterRank = function (naissance_id, num_naissance, page, date_min, date_max) {
    var form = getForm("modifyNaissance");
    $V(form.naissance_id, naissance_id);
    $V(form.num_naissance, num_naissance);
    onSubmitFormAjax(form, refreshRegistre.curry(page, date_min, date_max));
  };

  toggleEditNumNaissance = function (button, naissance_id) {
    $("view_num_naissance_" + naissance_id).hide();
    $("edit_num_naissance_" + naissance_id).show();

    button.setStyle({"visibility": "hidden"});
  };

  Main.add(function () {
    refreshRegistre(null, '{{$naissance->_date_min}}', '{{$naissance->_date_max}}');
  });
</script>

<form name="modifyNaissance" method="post">
  {{mb_class object=$naissance}}
  {{mb_key   object=$naissance}}
  {{mb_field object=$naissance field=num_naissance hidden=1}}
</form>

<form name="registreNaissance" method="get" action="?">
  <table class="form">
    <tr>
      <th class="title" colspan="10">{{tr}}CNaissance-Birth registry|pl{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CUserLog-_date_min-court{{/tr}}</th>
      <td>{{mb_field object=$naissance field="_date_min" form="registreNaissance" register=true}} </td>
    </tr>
    <tr>
      <th>{{tr}}CUserLog-_date_max-court{{/tr}}</th>
      <td>{{mb_field object=$naissance field="_date_max" form="registreNaissance" register=true}} </td>
    </tr>
    <tr>
      <td colspan="10"></td>
    </tr>
    <tr>
      <td colspan="10" class="button">
        <button type="button" onclick="refreshRegistre(null, $V(this.form._date_min), $V(this.form._date_max));"
                class="search me-primary">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="registre_area" class="me-padding-0"></div>