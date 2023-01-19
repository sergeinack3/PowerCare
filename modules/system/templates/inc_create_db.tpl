{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="form-create-dsn-{{$dsn}}" action="?" method="post" onsubmit="return DSN.create(this);">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="dosql" value="doCreateDsn"/>
  <input type="hidden" name="dsn" value="{{$dsn}}"/>

  <div class="small-info" style="white-space: normal;">
    Veuillez spécifier les identifiants de l'administrateur de base de données pour créer la base de données, et les
    droits nécessaires.
  </div>

  <table class="main form">
    <tr>
      <th class="narrow">
        <label for="master_host">
            {{tr}}config-db-dbname{{/tr}}
        </label>
      </th>
      <td class="narrow">
          {{$conf.db.$dsn.dbname}}
      </td>
    </tr>
    <tr>
      <th class="narrow">
        <label for="master_host">{{tr}}CSQLDataSource-master_host{{/tr}}</label>
      </th>
      <td class="narrow">
        <input name="master_host" type="text" value="{{$host}}"/>
      </td>
    </tr>
    <tr>
      <th class="narrow">
        <label for="master_user">{{tr}}CSQLDataSource-master_user{{/tr}}</label>
      </th>
      <td class="narrow">
        <input name="master_user" type="text" value="root"/>
      </td>
    </tr>
    <tr>
      <th>
        <label for="master_pass">{{tr}}CSQLDataSource-master_pass{{/tr}}</label>
      </th>
      <td>
        <input name="master_pass" type="password"/>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
        <button class="modify">
            {{tr}}config-dsn-create{{/tr}}
        </button>
      </td>
    </tr>
    <tr>
      <td colspan="2" id="config-dsn-create-{{$dsn}}"></td>
    </tr>
  </table>
</form>
