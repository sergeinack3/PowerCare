{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Calendar.regField(getForm("changeDate").date, null, {noView: true, inline: true, container: null});
  });
</script>

<form name="changeDate" method="get" class="me-bloc-planning-date">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
</form>

<table class="tbl planningBloc">
  <tr>
    <th>Liste des spécialités</th>
  </tr>
  {{foreach from=$listSpec item=curr_spec}}
  <tr>
    <td class="plageop text" style="background-color: #{{$curr_spec->color}};">
      <div class="me-color-legend" style="background-color: #{{$curr_spec->color}};"></div>
      <strong>{{$curr_spec}}</strong>
    </td>
  </tr>
  {{/foreach}}
</table>
