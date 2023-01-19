{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <td colspan="7">
        {{mb_include module=system template=inc_pagination total=$total current=$page change_page='changePageCorrespondants' step=$step change_page_arg=$type}}
    </td>
  </tr>

  <tr>
    <th class="narrow">{{mb_title class=CMedecin field='rpps'}}</th>
    <th class="narrow">{{mb_title class=CMedecin field='nom'}}</th>
    <th class="narrow">{{mb_title class=CMedecin field='prenom'}}</th>
    <th class="narrow">{{mb_title class=CMedecin field='cp'}}</th>
    <th class="narrow">{{mb_title class=CMedecin field='ville'}}</th>
    <th>{{mb_title class=CMedecin field='disciplines'}}</th>
    <th class="narrow">{{tr}}CMediusers-medecin-use{{/tr}}</th>
  </tr>

    {{foreach from=$medecins item=_medecin}}
      <tr>
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_medecin->_guid}}');">
          {{mb_value object=$_medecin field='rpps'}}
        </span>
        </td>
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_medecin->_guid}}');">
          {{mb_value object=$_medecin field='nom'}}
        </span>
        </td>
        <td>
            {{mb_value object=$_medecin field='prenom'}}
        </td>
        <td>
            {{mb_value object=$_medecin field='cp'}}
        </td>
        <td>
            {{mb_value object=$_medecin field='ville'}}
        </td>
        <td class="compact text">
            {{mb_value object=$_medecin field='disciplines'}}
        </td>
        <td id="action-medecin-mediuser-{{$_medecin->_id}}">
            {{if $user_id}}
              <form name="link-mediuser-medecin-{{$_medecin->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
            var form = getForm('search-medecin');
            form.onsubmit();
          }});">
                <input type="hidden" name="m" value="mediusers"/>
                <input type="hidden" name="dosql" value="do_link_or_unlink_mediuser_medecin"/>
                <input type="hidden" name="user_id" value="{{$user_id}}"/>
                <input type="hidden" name="medecin_id" value="{{$_medecin->_id}}"/>

                  {{if $_medecin->user_id == $user_id}}
                    <input type="hidden" name="link" value="0"/>
                    <button class="unlink notext" type="submit">{{tr}}Unlink{{/tr}}</button>
                  {{else}}
                    <input type="hidden" name="link" value="1"/>
                    <button class="link notext" type="submit">{{tr}}Link{{/tr}}</button>
                  {{/if}}
              </form>

            {{else}}
              <button class="new notext" type="button" onclick="CMediusers.fillMediuserFields('{{$_medecin->_id}}');">{{tr}}common-create-from{{/tr}}</button>
            {{/if}}
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="7">
            {{tr}}CMedecin.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
</table>
