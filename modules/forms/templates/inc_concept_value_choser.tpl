{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
#comp-list button {
  margin: -1px;
}

#comp-list > tr .operand-b {
  visibility: hidden;
}

#comp-list > tr[data-comp='between'] .operand-b {
  visibility: visible;
}
</style>

<script type="text/javascript">
ValueFilter = {
  newRow: function() {
    var row = $("comp-list").down(".inactive");

    if (row) {
      row.show().removeClassName("inactive");
    }
  }
};
</script>

{{if $concept->_id}}
<table class="main tbl">
  <tr>
    <th class="category">
      {{$concept}}
    </th>
  </tr>

  <tbody id="comp-list">
    {{foreach from=0|range:0 item=i}}
    {{unique_id var=uid}}
    <tr {{if $i > 0}} style="display: none;" class="inactive" {{/if}} data-comp="eq" id="{{$uid}}">
      <td>
        {{*<button type="button" class="trash notext" onclick="this.up('tr').remove()">{{tr}}Delete{{/tr}}</button>*}}

        <select name="cv{{$concept->_id}}_{{$i}}_comp" style="width: 8em;" onchange="this.up('tr').writeAttribute('data-comp', $V(this))">
          <option value="{{if $spec|instanceof:'Ox\Core\FieldSpecs\CSetSpec'}}inSet{{else}}eq{{/if}}">=</option>
          {{if !$spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec' && !$spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec'}}
            <option value="lt">&lt;</option>
            <option value="lte">&lt;=</option>
            <option value="gt">&gt;</option>
            <option value="gte">&gt;=</option>
            <option value="contains">Contient</option>
            <option value="begins">Commence par</option>
            <option value="ends">Finit par</option> 
            {{if $spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec '||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec '||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec '||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CNumSpec '||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CFloatSpec'}}
              <option value="between">Entre X et Y (inclus)</option>
            {{/if}}
          {{/if}}
        </select>

        {{if $spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec'}}
          <label><input type="radio" name="cv{{$concept->_id}}_{{$i}}_a" value="1" checked /> {{tr}}Yes{{/tr}}</label>
          <label><input type="radio" name="cv{{$concept->_id}}_{{$i}}_a" value="0" /> {{tr}}No{{/tr}}</label>
        {{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CNumSpec '||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CFloatSpec'}}
          <script>
            Main.add(function(){
              $("{{$uid}}").select("input").invoke("addSpinner");
            });
          </script>
          <span class="operand-a">
            <input type="text" name="cv{{$concept->_id}}_{{$i}}_a" size="4" class="{{$spec}}" />
          </span>
          <span class="operand-b">
            <input type="text" name="cv{{$concept->_id}}_{{$i}}_b" size="4" class="{{$spec}}" />
          </span>
        {{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' ||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec' ||
                 $spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec'}}
          <script>
            Main.add(function(){
              $("{{$uid}}").select("input").each(Calendar.regField);
            });
          </script>
          <span class="operand-a">
            <input type="hidden" name="cv{{$concept->_id}}_{{$i}}_a" class="{{$spec}}" />
          </span>
          <span class="operand-b">
            <input type="hidden" name="cv{{$concept->_id}}_{{$i}}_b" class="{{$spec}}" />
          </span>
        {{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CEnumSpec'}}
          <fieldset>
            {{foreach from=$spec->_locales item=_locale key=_key name=_enum}}
              <label><input type="radio" name="cv{{$concept->_id}}_{{$i}}_a"   class="{{$spec}}" value="{{$_key}}" {{if $smarty.foreach._enum.first}} checked {{/if}} /> {{$_locale}}</label><br />
            {{/foreach}}
          </fieldset>
        {{else}}
          <input type="text" name="cv{{$concept->_id}}_{{$i}}_a" />
        {{/if}}
      </td>
    </tr>
    {{/foreach}}
  </tbody>

  {{*if !$spec|instanceof:'Ox\Core\FieldSpecs\CBoolSpec'}}
    <tr>
      <td>
        <button type="button" class="add notext compact" onclick="ValueFilter.newRow()"></button>
      </td>
    </tr>
  {{/if*}}
</table>
{{else}}
  <div class="empty">Aucun filtre par concept</div>
{{/if}}
