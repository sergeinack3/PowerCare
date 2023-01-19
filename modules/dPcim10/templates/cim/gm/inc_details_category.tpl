{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 class="code-title">
  <span class="cim10-code">
    {{$category->code_long}}
  </span>
  &mdash; {{$category->libelle|smarty:nodefaults}}
  {{mb_include module=cim10 template=cim/inc_favori code=$category callback="CIM.showCode.bind(CIM, '`$category->code`')"}}
</h2>
{{if $category->_note}}
  <dl class="cim10-note">
    <dt>
      {{tr}}CNoteCim10.type.note{{/tr}}:
    </dt>
    <dd>
      {{$category->_note->content|smarty:nodefaults}}
    </dd>
  </dl>
{{/if}}

{{if $category->_text}}
  <div class="cim10-note">
    {{$category->_text->content|smarty:nodefaults}}
  </div>
{{/if}}

{{if $category->_coding_hints|@count}}
  <dl class="cim10-note">
    <dt>
      {{tr}}CNoteCim10.type.coding_hints{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$category->_coding_hints item=coding_hint}}
          <li>
            {{$coding_hint->content|smarty:nodefaults}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}

{{assign var=has_inclusions value=$category->_inclusions|@count}}
{{assign var=has_exclusions value=$category->_exclusions|@count}}
{{if $has_inclusions ||$has_exclusions}}
  <table class="layout cim10-note">
    <tr>
      {{if $has_inclusions}}
        <td{{if $has_exclusions}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.inclusions{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$category->_inclusions item=inclusion}}
                  <li>
                    {{$inclusion->content|smarty:nodefaults}}
                  </li>
                {{/foreach}}
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}

      {{if $has_exclusions}}
        <td{{if $has_inclusions}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.exclusions{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$category->_exclusions item=exclusion}}
                  <li>
                    {{$exclusion->content|smarty:nodefaults}}
                  </li>
                {{/foreach}}
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}
    </tr>
  </table>
{{/if}}

{{assign var=has_children value=false}}
{{if $category->_categories|@count || $category->_codes|@count}}
  {{assign var=has_children value=true}}
{{/if}}

{{if $category->parent_id || $has_children}}
  <table class="layout cim10-note">
    <tr>
      {{if $category->parent_id}}
        <td{{if $has_children}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.ancestors{{/tr}}:
            </dt>
            <dd>
              <ul>
                <li>
                  <a class="cim10-code" href="#" onclick="CIM.showCode('{{$category->_parent->code}}');">
                    {{$category->_parent->code_long}}
                  </a>
                  <span class="cim10-note-code-label">
                    &mdash; {{$category->_parent->libelle|smarty:nodefaults}}
                  </span>
                </li>
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}

      {{if $has_children}}
        <td{{if $category->parent_id}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.children{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$category->_categories item=child}}
                  <li>
                    <a class="cim10-code" href="#" onclick="CIM.showCode('{{$child->code}}');">
                      {{$child->code_long}}
                    </a>
                    <span class="cim10-note-code-label">
                      &mdash; {{$child->libelle|smarty:nodefaults}}
                    </span>
                  </li>
                {{/foreach}}
                {{foreach from=$category->_codes item=child}}
                  <li>
                    <a class="cim10-code" href="#" onclick="CIM.showCode('{{$child->code}}');">
                      {{$child->code_long}}
                    </a>
                    <span class="cim10-note-code-label">
                      &mdash; {{$child->libelle|smarty:nodefaults}}
                    </span>
                  </li>
                {{/foreach}}
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}
    </tr>
  </table>
{{/if}}

{{if $category->_introductions|@count}}
  {{foreach from=$category->_introductions item=introduction}}
    <div class="cim10-note">
      {{$introduction->content|smarty:nodefaults}}
    </div>
  {{/foreach}}
{{/if}}