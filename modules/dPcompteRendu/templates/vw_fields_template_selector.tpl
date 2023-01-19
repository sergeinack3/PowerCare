{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="compteRendu" script="field_selector" ajax=true}}

<form method="get">
  <label><input type="text" id="searchInput">
  <button type="submit" onclick="FieldSelector.searchField($V('searchInput'), '{{$class}}');return false;" class="search" >{{tr}}Search{{/tr}}</button>
  </label>
</form>

<table class="tbl" style="display:none;" id="FieldSearchResultTable" onchange="FieldSelector.autocomplete($V(this))"></table>
<table class="main" id="FieldSelectorTable">
  <tr>
    <th style="width: 30%;">{{tr}}Category{{/tr}}</th>
    <th style="width: 30%;">{{tr}}Field{{/tr}}</th>
    <th style="width: 30%;">{{tr}}subField{{/tr}}</th>
  </tr>
  <tr>
    <td id="section">
      <table class="tbl" id="sections">
        {{foreach from=$template->sections key=key item=_cat}}
          <tr class="lineSection">
            <td><a href="#a" onclick="FieldSelector.openSection('{{$key}}', this)">{{$key}} <span style="float:right;">&gt;&gt;&gt;</span></a></td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td style="vertical-align: top" id="section-field">
      {{foreach from=$template->sections key=key item=_cat}}
        <table id="section-{{$key}}" class="tbl section" style="display: none;">
          {{foreach from=$_cat key=keyF item=_field}}
            <tr>
              <td class="text">
                <a href="#b"
                   onclick="FieldSelector.openSubSection('{{$keyF}}', this);"
                   {{if array_key_exists("fieldHTML", $_field)}}ondblclick="insertField(this);" data-fieldHtml="{{$_field.fieldHTML}}"{{/if}}
                >
                  {{$keyF}} {{if !array_key_exists("fieldHTML", $_field)}}<span style="float:right;">&gt;&gt;&gt;</span>{{/if}}
                </a>
              </td>
            </tr>
          {{/foreach}}
        </table>
      {{/foreach}}
    </td>
    <td>
      {{foreach from=$template->sections key=key item=_cat}}
        {{foreach from=$_cat key=keyF item=_field}}
          <table id="subsection-{{$keyF}}" class="tbl subsection" style="display: none;">
          {{if !array_key_exists("view", $_field)}}
            {{foreach from=$_field key=keySub item=_subfield}}
              {{if is_array($_subfield) && array_key_exists("field", $_subfield)}}
                <tr><td class="text"><a href="#c" ondblclick="insertField(this)" data-fieldHtml="{{$_subfield.fieldHTML}}">{{$keySub}}</a></td></tr>
              {{/if}}
            {{/foreach}}
          {{/if}}
          </table>
        {{/foreach}}
      {{/foreach}}
    </td>
  </tr>
</table>