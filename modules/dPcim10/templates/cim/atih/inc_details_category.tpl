{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 class="code-title">
  <span class="cim10-code">
    {{$category->code}}
  </span>
  &mdash; {{$category->libelle}}
  {{mb_include module=cim10 template=cim/inc_favori code=$category callback="CIM.showCode.bind(CIM, '`$category->code`')"}}
</h2>

{{assign var=has_parents value=$category->_parent}}
{{mb_ternary var=children test=$category->chapter value=$category->_categories other=$category->_children}}
{{assign var=has_children value=$children|@count}}
{{if $category->_parent || $has_children}}
  <table class="layout cim10-note">
    <tr>
      {{if $category->_parent}}
        <td{{if $has_children}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.ancestors{{/tr}}:
            </dt>
            <dd>
              <ul>
                <li>
                  <a class="cim10-code" href="#" onclick="CIM.showCode('{{$category->_parent->code}}');">
                    {{$category->_parent->code}}
                  </a>
                  <span class="cim10-note-code-label">
                    &mdash; {{$category->_parent->libelle}}
                  </span>
                </li>
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}

      {{if $has_children}}
        <td{{if $has_parents}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half">
            <dt>
              {{tr}}CNoteCim10.type.children{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$children item=child}}
                  <li>
                    <a class="cim10-code" href="#" onclick="CIM.showCode('{{$child->code}}');">
                      {{$child->code}}
                    </a>
                    <span class="cim10-note-code-label">
                      &mdash; {{$child->libelle}}
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