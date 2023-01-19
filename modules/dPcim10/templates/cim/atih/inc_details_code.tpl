{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2 class="code-title">
  <span class="cim10-code">
    {{$code->code}}
  </span>
  &mdash; {{$code->libelle}}
  {{mb_include module=cim10 template=cim/inc_favori callback="CIM.showCode.bind(CIM, '`$code->code`')"}}
</h2>

<dl class="cim10-note wide">
  <dt>
    {{tr}}CCodeCIM10ATIH-type_mco{{/tr}}:
  </dt>
  <dd>
    {{tr}}CCodeCIM10ATIH.type_mco.{{$code->type_mco}}{{/tr}}
  </dd>
</dl>

<dl class="cim10-note wide">
  <dt>
    {{tr}}CCodeCIM10ATIH-ssr{{/tr}}:
  </dt>
  <dd>
    <table class="layout">
      <tr>
        <th class="quarterPane">
          {{tr}}CCodeCIM10ATIH-ssr_fppec{{/tr}}:
        </th>
        <td class="quarterPane">
          {{tr}}CCodeCIM10ATIH.ssr_fppec.{{$code->ssr_fppec}}{{/tr}}
        </td>
        <th class="quarterPane">
          {{tr}}CCodeCIM10ATIH-ssr_mmp{{/tr}}:
        </th>
        <td class="quarterPane lef">
          {{tr}}CCodeCIM10ATIH.ssr_mmp.{{$code->ssr_mmp}}{{/tr}}
        </td>
      </tr>
      <tr>
        <th class="quarterPane">
          {{tr}}CCodeCIM10ATIH-ssr_ae{{/tr}}:
        </th>
        <td class="quarterPane">
          {{tr}}CCodeCIM10ATIH.ssr_ae.{{$code->ssr_ae}}{{/tr}}
        </td>
        <th class="quarterPane">
          {{tr}}CCodeCIM10ATIH-ssr_das{{/tr}}:
        </th>
        <td class="quarterPane">
          {{tr}}CCodeCIM10ATIH.ssr_das.{{$code->ssr_das}}{{/tr}}
        </td>
      </tr>
    </table>
  </dd>
</dl>

<dl class="cim10-note wide">
  <dt>
    {{tr}}CCodeCIM10ATIH-type_psy{{/tr}}:
  </dt>
  <dd>
    {{tr}}CCodeCIM10ATIH.type_psy.{{$code->type_psy}}{{/tr}}
  </dd>
</dl>

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
                {{$code->_category->code}}
              </a>
              <span class="cim10-note-code-label">
                &mdash; {{$code->_category->libelle}}
              </span>
            </li>
            {{if $code->_parent}}
              <li>
                <a class="cim10-code" href="#" onclick="CIM.showCode('{{$code->_parent->code}}');">
                  {{$code->_parent->code}}
                </a>
                <span class="cim10-note-code-label">
                  &mdash; {{$code->_parent->libelle}}
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

