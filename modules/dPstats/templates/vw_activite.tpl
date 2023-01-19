{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

  Main.add(function () {
    var form1 = getForm("Form1"),
      form2 = getForm("Form2");

    Calendar.regField(form1.debutact);
    Calendar.regField(form1.finact);
    Calendar.regField(form2.debutact);
    Calendar.regField(form2.finact);
  });

</script>

<table class="main">
  <tr>
    <td>
      <form name="Form1" action="?" method="get" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPstats" />
        <table class="form">
          <tr>
            <th colspan="2" class="category">Autre graph</th>
          </tr>
          <tr>
            <th><label for="debutact" title="Date de début">Début:</label></th>
            <td><input type="hidden" name="debutact" class="notNull date" value="{{$debutact}}" /></td>
          </tr>
          <tr>
            <th><label for="finact" title="Date de fin">Fin:</label></th>
            <td><input type="hidden" name="finact" class="notNull date moreEquals|debutact" value="{{$finact}}" /></td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button class="search" type="submit">Go</button>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td>
      <form name="Form2" action="?" method="get" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPstats" />
        <table class="form">
          <tr>
            <th colspan="2" class="category">Autre graph</th>
          </tr>
          <tr>
            <th><label for="debutact" title="Date de début">Début:</label></th>
            <td><input type="hidden" name="debutact" class="notNull date" value="{{$debutact}}" /></td>
          </tr>
          <tr>
            <th><label for="finact" title="Date de fin">Fin:</label></th>
            <td><input type="hidden" name="finact" class="notNull date moreEquals|debutact" value="{{$finact}}" /></td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button class="search" type="submit">Go</button>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>