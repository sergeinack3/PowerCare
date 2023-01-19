{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 class="code-title">
  <span class="cim10-code">
    {{if $code->_chapter}}
      {{$code->_chapter}}
    {{/if}}
    {{$code->code}}
  </span>
  &mdash; {{$code->libelle}}
  {{mb_include module=cim10 template=cim/inc_favori callback="CIM.showCode.bind(CIM, '`$code->code`')"}}
</h2>
{{if $code->notes|@count}}
  <dl class="cim10-note me-color-black-high-emphasis">
    <dt>
      {{tr}}CNoteCim10.type.note{{if $code->notes|@count > 1}}|pl{{/if}}{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$code->notes|smarty:nodefaults item=note}}
          <li>
            {{$note}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}

{{if $code->descr|@count}}
  <dl class="cim10-note me-color-black-high-emphasis">
    <dt>
      {{tr}}CNoteCim10.type.description{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$code->descr|smarty:nodefaults item=description}}
          <li>
            {{$description}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}

{{if $code->glossaire|@count}}
  <dl class="cim10-note me-color-black-high-emphasis">
    <dt>
      {{tr}}CNoteCim10.type.glossaire{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$code->glossaire|smarty:nodefaults item=glossaire}}
          <li>
            {{$glossaire|smarty:nodefaults}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}

{{if $code->indir|@count}}
  <dl class="cim10-note me-color-black-high-emphasis">
    <dt>
      {{tr}}CNoteCim10.type.indir{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$code->indir|smarty:nodefaults item=indir}}
          <li>
            {{$indir}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}

{{assign var=has_inclusions value=$code->include|@count}}
{{assign var=has_exclusions value=$code->_exclude|@count}}
{{if $has_inclusions ||$has_exclusions}}
  <table class="layout cim10-note">
    <tr>
      {{if $has_inclusions}}
        <td{{if $has_exclusions}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half me-color-black-high-emphasis">
            <dt>
              {{tr}}CNoteCim10.type.inclusions{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$code->include|smarty:nodefaults item=inclusion}}
                  <li>
                    {{$inclusion}}
                  </li>
                {{/foreach}}
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}

      {{if $has_exclusions}}
        <td{{if $has_inclusions}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half me-color-black-high-emphasis">
            <dt>
              {{tr}}CNoteCim10.type.exclusions{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$code->_exclude item=exclusion}}
                  <li>
                    <a class="cim10-code" href="#" onclick="CIM.showCode('{{$exclusion->code}}');">
                      {{$exclusion->code}}
                    </a>
                    <span class="cim10-note-code-label">
                      &mdash; {{$exclusion->libelle}}
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

{{assign var=has_parents value=$code->_levelsSup|@count}}
{{assign var=has_children value=$code->_levelsInf|@count}}
{{if $has_parents || $has_children}}
  <table class="layout cim10-note">
    <tr>
      {{if $has_parents}}
        <td{{if $has_children}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half me-color-black-high-emphasis">
            <dt>
              {{tr}}CNoteCim10.type.ancestors{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$code->_levelsSup item=parent}}
                  <li>
                    <a class="cim10-code" href="#" onclick="CIM.showCode('{{$parent->code}}');">
                      {{$parent->code}}
                    </a>
                    <span class="cim10-note-code-label">
                      &mdash; {{$parent->libelle}}
                    </span>
                  </li>
                {{/foreach}}
              </ul>
            </dd>
          </dl>
        </td>
      {{/if}}

      {{if $has_children}}
        <td{{if $has_parents}} class="halfPane"{{/if}}>
          <dl class="cim10-note-half me-color-black-high-emphasis">
            <dt>
              {{tr}}CNoteCim10.type.children{{/tr}}:
            </dt>
            <dd>
              <ul>
                {{foreach from=$code->_levelsInf item=child}}
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