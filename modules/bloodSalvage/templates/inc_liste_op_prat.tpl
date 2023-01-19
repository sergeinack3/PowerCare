{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("selectPraticien").date, null, {noView: true});
  });
</script>

<form name="selectPraticien" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="op" value="0" />

  <table class="form">
    <tr>
      <th class="category" colspan="2">
        {{$date|date_format:date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </th>
    </tr>

    <tr>
      <th><label for="praticien_id" title="Praticien">Praticien</label></th>
      <td>
        <select name="praticien_id" onchange="this.form.submit()">
          <option value="">&mdash; {{tr}}CDiscipline-back-users.empty{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$praticien->_id}}
        </select>
      </td>
    </tr>
  </table>
</form>

{{mb_include module=bloodSalvage template=inc_details_op_prat}}
