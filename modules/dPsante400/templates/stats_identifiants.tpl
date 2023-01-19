{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>

      <table class="tbl">
        <tr>
          <th class="title" colspan="10">Statistiques de synchronisation par type d'objets</th>
        </tr>
        <tr>
          <th>Type d'objet</th>
          <th>Nombre d'objets</th>
          <th>Nombre d'identifiants</th>
          <th>Nombre d'identifiants/objet</th>
        </tr>
        {{foreach from=$stats item=stat}}
          <tr>
            <td>{{tr}}{{$stat.object_class}}{{/tr}}</td>
            <td>{{$stat.nbObjects}}</td>
            <td>{{$stat.nbID400s}}</td>
            <td>{{$stat.average|string_format:"%.2f"}}</td>
          </tr>
        {{/foreach}}

        <tr>
          <th>Total sur toute la classe</th>
          <td><strong>{{$statTotal.nbObjects}}</strong></td>
          <td><strong>{{$statTotal.nbID400s}}</strong></td>
          <td><strong>{{$statTotal.average|string_format:"%.2f"}}</strong></td>
        </tr>

      </table>

    </td>
  </tr>
</table>