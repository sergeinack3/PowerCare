{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="8">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$mediuser->_guid}}')">{{$mediuser}}</span>
    </th>
  </tr>

  <tr>
    <th class="narrow">{{tr}}CMedecin-rpps{{/tr}}</th>
    <th class="narrow">{{tr}}CMedecin-nom{{/tr}}</th>
    <th class="narrow">{{tr}}CMedecin-prenom{{/tr}}</th>
    <th class="narrow">{{tr}}CMedecin-cp{{/tr}}</th>
    <th class="narrow">{{tr}}CMedecin-ville{{/tr}}</th>
    <th>{{tr}}CMedecin-disciplines{{/tr}}</th>
    <th class="narrow">{{tr}}CMediusers-update{{/tr}}</th>
    <th class="narrow">{{tr}}Unlink{{/tr}}</th>
  </tr>

  {{foreach from=$medecins item=_medecin}}
    <tr>
      <td><span onmouseover="ObjectTooltip.createEx(this, '{{$_medecin->_guid}}')">{{$_medecin->rpps}}</span></td>
      <td><span onmouseover="ObjectTooltip.createEx(this, '{{$_medecin->_guid}}')">{{$_medecin->nom}}</span></td>
      <td>{{$_medecin->prenom}}</td>
      <td>{{$_medecin->cp}}</td>
      <td>{{$_medecin->ville}}</td>
      <td>{{$_medecin->disciplines}}</td>

      <td>
        <form name="update-medecin-mediuser-{{$_medecin->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="m" value="mediusers"/>
          <input type="hidden" name="dosql" value="do_mediusers_aed"/>
          <input type="hidden" name="del" value="0"/>
          <input type="hidden" name="user_id" value="{{$mediuser->_id}}"/>
          {{if $_medecin->nom}}<input type="hidden" name="_user_last_name" value="{{$_medecin->nom}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_first_name" value="{{$_medecin->prenom}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_cp" value="{{$_medecin->cp}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_ville" value="{{$_medecin->ville}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_sexe" value="{{$_medecin->sexe}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_email" value="{{$_medecin->email}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_phone" value="{{$_medecin->tel}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="_user_adresse" value="{{$_medecin->adresse}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="rpps" value="{{$_medecin->rpps}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="adeli" value="{{$_medecin->adeli}}"/>{{/if}}
          {{if $_medecin->nom}}<input type="hidden" name="mssante_address" value="{{$_medecin->mssante_address}}"/>{{/if}}

          <button class="edit" type="submit">{{tr}}Update{{/tr}}</button>
        </form>
      </td>

      <td id="unlink-medecin-mediuser-{{$_medecin->_id}}">
        <form name="unlink-medecin-mediuser-{{$_medecin->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.refresh();}});">
          <input type="hidden" name="m" value="mediusers"/>
          <input type="hidden" name="dosql" value="do_link_or_unlink_mediuser_medecin"/>
          <input type="hidden" name="user_id" value="{{$mediuser->_id}}"/>
          <input type="hidden" name="medecin_id" value="{{$_medecin->_id}}"/>
          <input type="hidden" name="link" value="0"/>

          <button class="unlink notext" type="submit">{{tr}}Unlink{{/tr}}</button>
        </form>
      </td>
    </tr>

    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CMedecin.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>