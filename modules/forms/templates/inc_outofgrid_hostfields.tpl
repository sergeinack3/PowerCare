{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  filterHostField = function (input, classe, _class) {
    var div = $$(classe + '-' + _class).first();
    var lis = div.select('li');

    lis.each(
      function (e) {
        e.show();
      }
    );

    var terms = $V(input);
    if (!terms) {
      return;
    }

    lis.each(
      function (e) {
        e.hide();
      }
    );

    terms = terms.split(",");
    lis.each(function (e) {
      terms.each(function (term) {
        e.select('span').each(function (span) {
          if (span.getText().like(term)) {
            e.show();
          }
        });
      });
    });
  };

  onFilterHostField = function (input, classe, _class) {
    if (input.value == "") {
      // Click on the clearing button
      filterHostField(input, classe, _class);
    }
  }
</script>

<table class="main layout">
  <tr>
    <td {{if !$ex_class->pixel_positionning}} style="width: 30%;" {{/if}}>
      <select id="forms-hostfields-select-{{$_group_id}}" onchange="toggleList(this, '{{$_group_id}}')" class="dont-lock">
        {{foreach from=$ex_class->_host_objects item=_object key=_class}}
          <option value="{{$_class}}">{{tr}}{{$_class}}{{/tr}}</option>
        {{/foreach}}
      </select>

      <input id="forms-hostfields-quicksearch-{{$_group_id}}" type="search" value="" size="35" placeholder="{{tr}}common-Quick search...{{/tr}}"
             class="dont-lock" onkeyup="filterHostField(this, '.hostfield-{{$_group_id}}', $V($('forms-hostfields-select-{{$_group_id}}')));" onsearch="onFilterHostField(this, '.hostfield-{{$_group_id}}', $V($('forms-hostfields-select-{{$_group_id}}')));" />

      <br />
      {{if !$ex_class->pixel_positionning}}
    </td>
    <td>
      {{/if}}
      {{foreach from=$ex_class->_host_objects item=_object key=_class name=_host_objects}}
        <div
          style="overflow-y: scroll; min-height: 140px; {{if !$ex_class->pixel_positionning}} max-height: 140px; {{else}} max-height: 600px; {{/if}} {{if $smarty.foreach._host_objects.first}} display: inline-block; {{else}} display: none; {{/if}}"
          class="hostfield-{{$_group_id}}-{{$_class}} hostfield-list-{{$_group_id}}" data-x="" data-y="">
          <ul>
            {{foreach from=$_object item=_spec key=_field}}
              <li>
                {{mb_include module=forms template=inc_ex_host_field_draggable ex_group_id=$_group_id host_object=$_object}}
              </li>
            {{/foreach}}
          </ul>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>