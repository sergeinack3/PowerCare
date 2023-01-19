{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=CMediusers}}

<script>
  Main.add(function() {
    let form = getForm('search-medecin');

    CMediusers.showMedecinsAnnuaire(form);
  })
</script>

<form name="search-medecin" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-search-medecins')">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="a" value="viewDoctorsDirectory"/>
  {{if $mediuser->_id}}
    <input type="hidden" name="user_id" value="{{$mediuser->_id}}"/>
  {{/if}}

  <table class="main form">
    <tr>
      <th class="title" colspan="6">
        {{tr}}CMedecin-search-fields{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        <label for="rpps">{{tr}}CMedecin-rpps{{/tr}}</label>
      </th>
      <td>
        <input name="rpps" type="text" size="10" {{if $mediuser->rpps}}value="{{$mediuser->rpps}}"{{/if}}/>
      </td>

      <th>
        <label for="nom">{{tr}}CMedecin-nom{{/tr}}</label>
      </th>
      <td>
        <input name="nom" type="text" size="20" {{if $mediuser->_user_last_name}}value="{{$mediuser->_user_last_name}}"{{/if}}/>
      </td>

      <th>
        <label for="prenom">{{tr}}CMedecin-prenom{{/tr}}</label>
      </th>
      <td>
        <input name="prenom" type="text" size="20" {{if $mediuser->_user_first_name}}value="{{$mediuser->_user_first_name}}"{{/if}}/>
      </td>
    </tr>

    <tr>
      <th>
        <label for="cp">{{tr}}CMedecin-cp{{/tr}}</label>
      </th>
      <td>
        <input name="cp" type="text" size="5" {{if $mediuser->_user_cp}}value="{{$mediuser->_user_cp}}"{{/if}}/>
      </td>

      <th>
        <label for="ville">{{tr}}CMedecin-ville{{/tr}}</label>
      </th>
      <td>
        <input name="ville" type="text" size="20" {{if $mediuser->_user_ville}}value="{{$mediuser->_user_ville}}"{{/if}}/>
      </td>

      <th>
        <label for="disciplines">{{tr}}CMedecin-disciplines{{/tr}}</label>
      </th>
      <td>
        <input name="disciplines" type="text" size="20"/>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="6">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-search-medecins"></div>
