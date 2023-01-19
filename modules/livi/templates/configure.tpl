{{*
 * @package Mediboard\Livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" action="?m=livi&amp;{{$actionType}}=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}
  
  <table class="form">
    <tr>
      <th class="title" colspan="2">Configuration</th>
    </tr>
    

    <tr>
      <td class="button" colspan="2">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
