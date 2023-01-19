{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 class="code-title">
  {{if $code->rare_disease}}
    <span class="circled cim10-rare-disease" title="{{tr}}CCodeCIM10GM-rare_disease-desc{{/tr}}">
      {{tr}}CCodeCIM10GM-rare_disease{{/tr}}
    </span>
  {{/if}}
  {{if $code->infectious}}
    <span class="circled cim10-infectious" title="{{tr}}CCodeCIM10GM-infectious-desc{{/tr}}">
      {{tr}}CCodeCIM10GM-infectious{{/tr}}
    </span>
  {{/if}}
  <span class="cim10-code">
    {{$code->code_long}}{{if $code->usage}}{{$code->_usage|smarty:nodefaults}}{{/if}}
  </span>
  &mdash; {{$code->libelle|smarty:nodefaults}}
  {{mb_include module=cim10 template=cim/inc_favori callback="CIM.showCode.bind(CIM, '`$code->code`')"}}
</h2>

{{if $code->_note}}
  <dl class="cim10-note">
    <dt>
      {{tr}}CNoteCim10.type.note{{/tr}}:
    </dt>
    <dd>
      {{$code->_note->content|smarty:nodefaults}}
    </dd>
  </dl>
{{/if}}

{{if $code->_text}}
  <div class="cim10-note">
    {{$code->_text->content|smarty:nodefaults}}
  </div>
{{/if}}

{{if $code->_definitions|@count}}
  {{foreach from=$code->_definitions item=definition}}
    <div class="cim10-note">
      {{$definition->content|smarty:nodefaults}}
    </div>
  {{/foreach}}
{{/if}}

{{if $code->sex_code || $code->age_low}}
  <table class="layout cim10-data">
    <tr>
      {{if $code->sex_code}}
        <th>
          {{tr}}CCodeCIM10GM-sex_code{{/tr}}:
        </th>
        <td>
          {{tr}}CCodeCIM10GM.sex_code.{{$code->sex_code}}{{/tr}}
        </td>
      {{/if}}
      {{if $code->age_low}}
        <th>
          {{tr}}CCodeCIM10GM-age{{/tr}}:
        </th>
        <td>
          <table class="sub-data">
            <tr>
              <th>
                {{tr}}CCodeCIM10GM-age_low{{/tr}}:
              </th>
              <td>
                {{$code->_age_low}}
              </td>
            </tr>
            <tr>
              <th>
                {{tr}}CCodeCIM10GM-age_high{{/tr}}:
              </th>
              <td>
                {{$code->_age_high}}
              </td>
            </tr>
          </table>
        </td>
      {{/if}}
    </tr>
  </table>
{{/if}}

{{if $code->_coding_hints|@count}}
  <dl class="cim10-note">
    <dt>
      {{tr}}CNoteCim10.type.coding_hints{{/tr}}:
    </dt>
    <dd>
      <ul>
        {{foreach from=$code->_coding_hints item=coding_hint}}
          <li>
            {{$coding_hint->content|smarty:nodefaults}}
          </li>
        {{/foreach}}
      </ul>
    </dd>
  </dl>
{{/if}}
{{assign var=has_inclusions value=$code->_inclusions|@count}}
{{assign var=has_exclusions value=$code->_exclusions|@count}}
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
                {{foreach from=$code->_inclusions item=inclusion}}
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
                {{foreach from=$code->_exclusions item=exclusion}}
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

{{assign var=has_children value=$code->_descendants|@count}}
<table class="layout cim10-note">
  <tr>
    <td{{if $has_children}} class="halfPane"{{/if}}>
      <dl class="cim10-note-half">
        <dt>
          {{tr}}CNoteCim10.type.ancestors{{/tr}}:
        </dt>
        <dd>
          <ul>
            <li>
              <a class="cim10-code" href="#" onclick="CIM.showCode('{{$code->_category->code}}');">
                {{$code->_category->code_long}}
              </a>
              <span class="cim10-note-code-label">
                &mdash; {{$code->_category->libelle|smarty:nodefaults}}
              </span>
            </li>
            {{if $code->_parent}}
              <li>
                <a class="cim10-code" href="#" onclick="CIM.showCode('{{$code->_parent->code}}');">
                  {{$code->_parent->code_long}}
                </a>
                <span class="cim10-note-code-label">
                  &mdash; {{$code->_parent->libelle|smarty:nodefaults}}
                </span>
              </li>
            {{/if}}
          </ul>
        </dd>
      </dl>
    </td>

    {{if $has_children}}
      <td class="halfPane">
        <dl class="cim10-note-half">
          <dt>
            {{tr}}CNoteCim10.type.children{{/tr}}:
          </dt>
          <dd>
            <ul>
              {{foreach from=$code->_descendants item=child}}
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