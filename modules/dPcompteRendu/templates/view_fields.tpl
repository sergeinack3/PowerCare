{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=fields ajax=$ajax}}

<script>
  Main.add(() => {
    Control.Tabs.create('fields_tabs');

    Fields.value_mode = window.fields.spanClass === 'value';
    Fields.max_sections = parseInt({{$max_sections}});

    let sections = Object.keys(window.fields.options);

    sections.each(function (_mode) {
      let section = $('section-' + _mode + '-0');

      Fields.empty[_mode] = true;
      Fields.fields[_mode] = window.fields.options[_mode];
      $H(Fields.fields[_mode]).each(function (field) {
        var vw_item = field[0];
        if (Object.isUndefined(field[1].field)) {
          vw_item += ' &gt;';
        }
        else {
          vw_item = vw_item.split(' - ')[1];
        }
        // On retire le préfixe SIH si nécessaire
        if (/^SIH.*/.test(_mode)) {
          vw_item = vw_item.replace(/^(SIH.*) - /, '');
        }
        section.insert("<option value='" + field[0] + "'>" + vw_item + "</option>");
      });

      section.selectedIndex = -1;

      var searchinput = $('searchinput-' + _mode);
      searchinput.focus();
      searchinput.select();
      searchinput.observe('keyup', Fields.search.bindAsEventListener(searchinput, _mode));
    });

    // Clone the structure because it's overwritten when clicking the option
    const current_item = JSON.parse(JSON.stringify(Fields.current_item));

    Object.keys(current_item).each((mode) => {
       Object.keys(current_item[mode]).each((select_id) => {
           let select = $(select_id);

           if (select) {
               $V(select, current_item[mode][select_id]);
               let curr_option = select.selectedOptions[0];

               if (curr_option) {
                   curr_option.click();
               }
           }
       });
    });

    if (sections.length === 1) {
      $('fields_tabs').hide();
    }
  });
</script>

<ul id="fields_tabs" class="control_tabs">
  {{foreach from=$sections item=_mode}}
      <li>
        <a href="#fields-{{$_mode}}">
          {{$_mode}}
        </a>
      </li>
  {{/foreach}}
</ul>

{{foreach from=$sections item=_mode}}
    <div id="fields-{{$_mode}}" style="display: none;">
      <span style="font-size: 12pt;">{{tr}}CCompteRendu-action-Search a field{{/tr}}</span> : <input type="text" id="searchinput-{{$_mode}}" autofocus />

      <table class="main">
        <tr id="classic-{{$_mode}}">
          {{foreach from=0|range:$max_sections item=i}}
            <td style="width: {{math equation=100/(x+1) x=$max_sections}}%;">
              <select id="section-{{$_mode}}-{{$i}}" size="15" class="select-field-doc" data-rank="{{$i}}" data-mode="{{$_mode}}"
                      {{if $i !== $max_sections}}
                        onchange="Fields.reloadItem(this)"
                      {{/if}}
                      ondblclick="Fields.insertHTML(this.value, this.options[this.selectedIndex].dataset.identifier, true);">
                {{if $i > 0}}
                  <option value="">{{tr}}CCompteRendu-action-Choose an item{{/tr}}</option>
                {{/if}}
              </select>
            </td>
          {{/foreach}}
        </tr>
        <tr id="search-{{$_mode}}" style="display: none;">
          <td>
            <select id="resultsearch-{{$_mode}}" size="15" class="select-field-doc"
                    ondblclick="Fields.insertHTML(this.value, '{{$_mode}}', true)">
            </select>
          </td>
        </tr>
      </table>
    </div>
{{/foreach}}
