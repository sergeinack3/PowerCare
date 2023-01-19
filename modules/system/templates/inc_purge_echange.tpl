{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EchangePurge{{$source_class}}" action="?" method="get">
  <input name="source_class" type="hidden" value="{{$source_class}}"/>
  <table class="main form" style="table-layout: fixed;">
    <tr>
      <th>
        <label for="date_max">{{tr}}CEchangeFTP-_date_max{{/tr}}</label>
      </th>
      <td>
        <input class="date notNull" type="hidden" name="date_max" value="" />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('EchangePurge{{$source_class}}').date_max);
          });
        </script>
      </td>
    </tr>
    <tr>
      <th>
        <label for="max">Traitement max à effectuer</label>
      </th>
      <td>
        <input name="max" type="text" value="1000"/>
      </td>
    </tr>
    <tr>
      <th>
        <label for="delete">Supprimer les échanges antérieurs à 6 mois : </label>
      </th>
      <td>
        <input type="checkbox" name="delete" value="1" />
      </td>
    </tr>
    <tr>
      <th>
        <button type="button" class="change" onclick="Exchange.purge(true, '{{$source_class}}')">
          {{tr}}Purge-search{{/tr}}
        </button>
        <label><input type="checkbox" name="do_purge"/>{{tr}}Purge{{/tr}}</label>
        <label><input type="checkbox" name="auto"/>{{tr}}Auto{{/tr}}</label>
      </th>
      <td id="purge-echange-{{$source_class}}"></td>
    </tr>
  </table>
</form>