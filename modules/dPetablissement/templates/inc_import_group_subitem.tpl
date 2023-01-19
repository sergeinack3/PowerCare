{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  ignoreAll = function (btn) {
    btn.up('div').select('input.user_bind').each(function (input) {
      $V(input, '');
      var id = input.id;
      id = id.replace('_value_', '_view_');
      $V($(id), '');
    });
  };

  findAllObjects = function (btn) {
    btn.up('div').select('input.user_bind').each(function (input) {
      $V(input, input.get('found'));
      var id = input.id;
      id = id.replace('_value_', '_view_');
      $V($(id), $(id).get('found'));
    });
  }
</script>

{{if $allow_create}}
  <button class="new" type="button" onclick="this.up('div').select('select').each(function(select){$V(select, '__create__');})">Tout créer</button>
{{/if}}

{{if $class == 'CUser'}}
  <button class="lookup" type="button" onclick="findAllObjects(this)">Tout retrouver</button>
  <button class="cancel" type="button" onclick="ignoreAll(this)">Tout ignorer</button>
{{else}}
  <button class="lookup" type="button" onclick="this.up('div').select('select').each(function(select){select.selectedIndex=0;})">Tout retrouver</button>
  <button class="cancel" type="button" onclick="this.up('div').select('select').each(function(select){$V(select, '__ignore__');})">Tout ignorer</button>
{{/if}}


<table class="main tbl">
  <tr>
    <th class="category" style="width: 50%;">Présent dans le fichier</th>
    <th class="category">Présent en base</th>
  </tr>
  {{foreach from=$objects item=_object key=_key}}
    <tr>
      <td>{{$_object.values.$field}}</td>
      <td style="padding: 1px;">
        {{if $class == 'CUser'}}
          <script>
            Main.add(function () {
              var view = $('user_id_autocomplete_view_{{$_key}}');

              var url = new Url("system", "ajax_seek_autocomplete");
              url.addParam("object_class", "CMediusers");
              url.addParam("view_field", '_view');
              url.addParam("input_field", 'user_id_autocomplete_view_{{$_key}}');
              url.autoComplete(view, null, {
                minChars:           3,
                method:             "get",
                select:             "view",
                dropdown:           true,
                afterUpdateElement: function (field, selected) {
                  var value = $('user_id_autocomplete_value_{{$_key}}');
                  $V(value, selected.get('guid').replace('CMediusers', 'CUser'));
                }
              });
            });
          </script>

          <input type="text" class="autocomplete" id="user_id_autocomplete_view_{{$_key}}" name="user_id_autocomplete_view_{{$_key}}" data-found="{{$_object.similar}}" size="40" />
          <input type="hidden" class="user_bind" id="user_id_autocomplete_value_{{$_key}}" name="fromdb[{{$_key}}]"
                 data-found="{{if $_object.similar}}{{$_object.similar->_guid}}{{/if}}" value=""/>

        {{else}}
          <select name="fromdb[{{$_key}}]" style="width: 30em; margin: 0;">
            {{foreach from=$_object.similar item=_similar}}
              <option value="{{$_similar->_guid}}">{{$_similar}}</option>
            {{/foreach}}

            {{if $allow_create}}
              <option value="__create__"> &mdash; Créer (renommé si déjà présent) &mdash; </option>
            {{/if}}

            <option value="__ignore__" selected> &mdash; Ignorer &mdash; </option>

            {{if $all_objects|@count}}
              <optgroup label="Autres">
                {{foreach from=$all_objects item=_object_item}}
                  <option value="{{$_object_item->_guid}}">{{$_object_item}} (#{{$_object_item->_id}})</option>
                {{/foreach}}
              </optgroup>
            {{/if}}
          </select>
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>
