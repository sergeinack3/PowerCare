{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=exam_comp}}

<script>
  searchExams = function (form) {
    new Url('board', 'viewExamComp')
      .addFormData(form)
      .addParam('list', 1)
      .requestUpdate('list_exams');
  };

  purgeDate = function (element) {
    if ($V(element)) {
      var form = element.form;
      if (element.name == '_date_min') {
        $V(form.date, '');
        $V(form.date_da, '');
      }
      else {
        $V(form._date_min, '');
        $V(form._date_min_da, '');
      }
    }
  };
</script>

<form name="filters-exams_comp" action="?" method="get" onsubmit="return checkForm(this)">
  <table class="form">
    <tr>
      <th class="title" colspan="2">Recherche d'examens complémentaires</th>
    </tr>
    <tr>
      <th>Entrée du séjour</th>
      <td>{{mb_field object=$filter field="_date_min" form="filters-exams_comp" register=true onchange="purgeDate(this);"}} </td>
    </tr>
    <tr>
      <th>
        {{mb_label class=COperation field=date}}
      </th>
      <td>{{mb_field class=CPlageOp field="date" form="filters-exams_comp" register=true value=$date canNull="true" onchange="purgeDate(this);"}} </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="search" onclick="searchExams(this.form);">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="list_exams">
  {{mb_include module=board template=vw_list_exams_comp}}
</div>
