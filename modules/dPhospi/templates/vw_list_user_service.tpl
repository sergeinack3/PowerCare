{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <form name="CAffectationUserService-new" method="post"
        onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.loadListUsersService('{{$service->_id}}')}})">
    {{mb_key   object=$affectation_user}}
    {{mb_class object=$affectation_user}}
    <input type="hidden" name="service_id" value="{{$service->_id}}" />
    <input type="hidden" name="date" value="" />
    {{mb_field object=$affectation_user field=user_id onchange="this.form.onsubmit();" hidden=true}}
    <label>
      <input type="text" name="_user_view" class="autocomplete" value="" placeholder="Ajouter un utilisateur"
             onclick="\$V(this, '');" />
    </label>
    <script>
      Main.add(function () {
        var form = getForm("CAffectationUserService-new");
        var url = new Url('mediusers', 'ajax_users_autocomplete');
        url.addParam('input_field', '_user_view');
        url.autoComplete(form._user_view, null, {
          minChars:           0,
          method:             'get',
          select:             'view',
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            $V(form._user_view, selected.down('.view').innerHTML);
            $V(form.user_id, selected.getAttribute('id').split('-')[2]);
          }
        });
      });
    </script>
  </form>
</div>

<table class="main tbl">
  <tr>
    <th colspan="2">{{tr}}CAffectationUserService.all{{/tr}} pour le service {{$service->_view}}</th>
  </tr>
  {{foreach from=$affectations_users item=_affectation}}
    <tr>
      <td class="narrow">
        <form name="{{$_affectation->_guid}}" method="post"
              onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Infrastructure.loadListUsersService('{{$_affectation->service_id}}')}})">
          {{mb_key object=$_affectation}}
          {{mb_class object=$_affectation}}
          <input type="hidden" name="del" value="1" />
          <button class="trash notext" type="button"
                  onclick="confirmDeletion(this.form, {typeName:'cette affectation d\'utilisateur'},
                    {onComplete: function() {Infrastructure.loadListUsersService('{{$_affectation->service_id}}')}})">

          </button>
        </form>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_ref_user->_guid}}')">
          {{$_affectation->_ref_user->_view}}
        </span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CAffectationUserService.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>