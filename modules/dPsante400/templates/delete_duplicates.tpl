{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}

<script type="text/javascript">
  cleanIds = function (object_id, object_class, tag, id400, oButton) {
    oButton.disabled = true;
    var url = new Url('dPsante400', 'ajax_delete_duplicates');
    url.addParam('object_id', object_id);
    url.addParam('object_class', object_class);
    url.addParam('tag', tag);
    url.addParam('id400', id400);
    url.requestUpdate('systemMsg');
  }
</script>

<form name="filterFrm" action="?" method="get">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="tab" value="{{$tab}}"/>
  <input type="hidden" name="dialog" value="{{$dialog}}"/>

  <table class="form">
    <tr>
      <th class="title" colspan="6">
        Filtre
      </th>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field="object_class"}}</th>
      <td>
        <select name="object_class" class="str maxLength|25">
          <option value="">&mdash; Toutes les classes</option>
          {{foreach from=$listClasses item=curr_class}}
            <option value="{{$curr_class}}" {{if $curr_class == $filter->object_class}}selected="selected"{{/if}}>
              {{$curr_class}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>{{mb_label object=$filter field="_start_date" form="filterFrm" register=true}}</th>
      <td>{{mb_field object=$filter field="_start_date" form="filterFrm" register=true}}</td>
      <th>Nombre de résultats</th>
      <td><input type="text" name="limit_duplicates" value="{{$limit_duplicates}}"/></td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="object_id"}}</th>
      <td>
        <input name="object_id" class="ref" value="{{$filter->object_id}}"/>
        <button class="search" type="button" onclick="ObjectSelector.initFilter()">Chercher</button>
        <script type="text/javascript">
          ObjectSelector.initFilter = function () {
            this.sForm = "filterFrm";
            this.sId = "object_id";
            this.sClass = "object_class";
            this.onlyclass = "false";
            this.pop();
          }
        </script>
      </td>
      <th>{{mb_label object=$filter field="_end_date" form="filterFrm" register=true}}</th>
      <td>{{mb_field object=$filter field="_end_date" form="filterFrm" register=true}}</td>
      <th>Traiter les résultats</th>
      <td><input type="checkbox" name="do_delete"/></td>
    </tr>

    <tr>
      <td class="button" colspan="6">
        <button class="search" type="submit">Afficher</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl">
  <tr>
    <th></th>
    <th>{{mb_title object=$filter field=object_id}}</th>
    <th>{{mb_title object=$filter field=object_class}}</th>
    <th>{{mb_title object=$filter field=tag}}</th>
    <th>{{mb_title object=$filter field=id400}}</th>
    <th>Nb Ids</th>
    <th>{{mb_title object=$filter field=id_sante400_id}}</th>
  </tr>
  {{foreach from=$list item=_ids}}
    <tr>
      <td class="text">
        {{if $_ids.msg}}
          {{$_ids.msg|smarty:nodefaults}}
        {{else}}
          <button class="trash" type="button"
                  onclick="cleanIds('{{$_ids.object_id}}', '{{$_ids.object_class}}', '{{$_ids.tag}}', '{{$_ids.id400}}', this)">
            Nettoyer
          </button>
        {{/if}}
      </td>
      <td>{{$_ids.object_id}}</td>
      <td>{{tr}}{{$_ids.object_class}}{{/tr}}</td>
      <td>{{$_ids.tag}}</td>
      <td>{{$_ids.id400}}</td>
      <td>{{$_ids.total}}</td>
      <td class="text">{{$_ids.ids}}</td>
    </tr>
  {{/foreach}}
</table>