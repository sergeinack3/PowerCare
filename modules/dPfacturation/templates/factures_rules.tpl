{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <thead>
    <tr>
      <th class="title">
        {{tr}}CFacture.rules states{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action open cotation desc{{/tr}}">
        {{tr}}CFacture.rules action open cotation{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action close cotation desc{{/tr}}">
        {{tr}}CFacture.rules action close cotation{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action disable desc{{/tr}}">
        {{tr}}CFacture.rules action disable{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action send desc{{/tr}}">
        {{tr}}CFacture.rules action send{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action print desc{{/tr}}">
        {{tr}}CFacture.rules action print{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action close desc{{/tr}}">
        {{tr}}CFacture.rules action close{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action reopen desc{{/tr}}">
        {{tr}}CFacture.rules action reopen{{/tr}}
      </th>
      <th class="title" title="{{tr}}CFacture.rules action reverse desc{{/tr}}">
        {{tr}}CFacture.rules action reverse{{/tr}}
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td title="{{tr}}CFacture.rules state opened desc{{/tr}}">
        {{tr}}CFacture.rules state opened{{/tr}}
      </td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state cotation opened desc{{/tr}}">
        {{tr}}CFacture.rules state cotation opened{{/tr}}
      </td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state disabled desc{{/tr}}">
        {{tr}}CFacture.rules state disabled{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state partially acquitted desc{{/tr}}">
        {{tr}}CFacture.rules state partially acquitted{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state acquitted desc{{/tr}}">
        {{tr}}CFacture.rules state acquitted{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state has reminder desc{{/tr}}">
        {{tr}}CFacture.rules state has reminder{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state has deadline desc{{/tr}}">
        {{tr}}CFacture.rules state has deadline{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state sent desc{{/tr}}">
        {{tr}}CFacture.rules state sent{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state closed desc{{/tr}}">
        {{tr}}CFacture.rules state closed{{/tr}}
      </td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state closed with auto closed desc{{/tr}}">
        {{tr}}CFacture.rules state closed with auto closed{{/tr}}
      </td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state reversed desc{{/tr}}">
        {{tr}}CFacture.rules state reversed{{/tr}}
      </td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
    </tr>
    <tr>
      <td title="{{tr}}CFacture.rules state opened desc{{/tr}}">
        {{tr}}CFacture.rules state with echeances{{/tr}}
      </td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
      <td><div class="warning">{{tr}}CFacture.rules na{{/tr}}</div></td>
      <td><div class="error">{{tr}}CFacture.rules no{{/tr}}</div></td>
      <td><div class="small-success">{{tr}}CFacture.rules yes{{/tr}}</div></td>
    </tr>
  </tbody>
</table>

