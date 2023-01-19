{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=route_creator ajax=true}}

<form name="create-route" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-create-route');">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="a" value="ajax_create_route"/>

  <table class="main form">
    <tr>
      <th colspan="3" class="category">{{tr}}dPdeveloppement-Api-Routing{{/tr}}</th>
    </tr>

    <tr>
      <th colspan="2">
        <label id="label-route-name" for="route-name" class="notNull">
          {{tr}}dPdeveloppement-Api-Route name{{/tr}}
        </label>
      </th>
      <td>
        <input type="text" id="route-name" name="route_name" size="50" placeholder="module_lorem_ipsum"
               onchange="RouteCreator.updateLabel(this.id)"
        />
      </td>
    </tr>

    <tr>
      <th>
        <label id="label-path" for="path" class="notNull">
          {{tr}}dPdeveloppement-Api-path{{/tr}}
        </label>
      </th>
      <td class="narrow">
        <label id="short-path" class="notNull">path</label>
      </td>
      <td>
        <input type="text" id="path" name="path" size="100" placeholder="/api/module/loremIpsum"
               onchange="RouteCreator.updateResult(this.value); RouteCreator.updateLabel(this.id);"
        />
      </td>
    </tr>

    <tr>
      <th colspan="2">{{tr}}dPdeveloppement-Api-Path result{{/tr}}</th>
      <td>
        <input type="text" size="100" readonly id="route-result"/>
      </td>
    </tr>

    <tr>
      <th>
        <label for="controller" id="label-controller" class="notNull">
          {{tr}}dPdeveloppement-Api-controller{{/tr}}
        </label>
      </th>
      <td class="narrow">
        <label id="short-controller" class="notNull">controller</label>
      </td>
      <td>
        <input type="text" id="controller" name="controller" size="100" placeholder="Ox\Application\Controllers\CModuleController::loremIpsum"
               onchange="RouteCreator.updateLabel(this.id);"
        />
      </td>
    </tr>

    <tr>
      <th>
        <label id="label-methods" class="notNull">
          {{tr}}dPdeveloppement-Api-methods{{/tr}}
        </label>
      </th>
      <td class="narrow">
        <label id="short-methods" class="notNull">
          methods
        </label>
      </td>
      <td>
        {{foreach from=$allowed_methods item=_method}}
          <label>
            <input type="checkbox" class="api-route-methods" name="methods[{{$_method}}]" value="{{$_method}}"
                   onchange="RouteCreator.updateLabelCheckbox(this);"
            />
            {{$_method}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>
        {{tr}}dPdeveloppement-Api-requirements{{/tr}}
        <button type="button" class="add notext" onclick="RouteCreator.addRequirement()">{{tr}}Add{{/tr}}</button>
      </th>
      <td class="narrow">requirements</td>
      <td id="td-requirements">
        <div id="requirements[0]">
          <button class="remove notext" type="button" onclick="RouteCreator.removeRequirement('requirements[0]');">
            {{tr}}Remove{{/tr}}
          </button>
          <input type="text" name="requirement_name[0]" placeholder="toto" onchange="RouteCreator.updateResultReq(this.value, 0);"/>
          <input type="text" name="requirement_type[0]" placeholder="\w+"/>
          <input type="hidden" id="requirement_old[0]"/>
        </div>
      </td>
    </tr>

    <tr>
      <th colspan="3" class="category">{{tr}}dPdeveloppement-Api-Defaults{{/tr}}</th>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-permission{{/tr}}</th>
      <td class="narrow">permission</td>
      <td>
        {{foreach from=$allowed_permissions item=_perm}}
          <label>
            <input type="radio" name="permission" value="{{$_perm}}"/>
            {{$_perm}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th colspan="3" class="category">{{tr}}dPdeveloppement-Api-Options{{/tr}}</th>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-description{{/tr}}</th>
      <td class="narrow">description</td>
      <td>
        <input type="text" name="description" size="100"/>
      </td>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-openapi{{/tr}}</th>
      <td class="narrow">openapi</td>
      <td>
        <label>
          <input type="radio" name="openapi" checked value="1"/>{{tr}}Yes{{/tr}}
        </label>
        <label>
          <input type="radio" name="openapi" value="0"/>{{tr}}No{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>
        {{tr}}dPdeveloppement-Api-parameters{{/tr}}
        <button type="button" class="add notext" onclick="RouteCreator.addFields(
          'parameters', 'parameters_name', 'parameters_type', 'foo', 'bar'
        )">{{tr}}Add{{/tr}}</button>
      </th>
      <td class="narrow">parameters</td>
      <td id="td-parameters">
        <div id="parameters[0]">
          <button type="button" class="remove notext" onclick="RouteCreator.removeParam('parameters[0]');">{{tr}}Remove{{/tr}}</button>
          <input type="text" name="parameters_name[0]" placeholder="foo"/>
          <input type="text" name="parameters_type[0]" placeholder="bar"/>
        </div>
      </td>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-accept{{/tr}}</th>
      <td class="narrow">accept</td>
      <td>
        {{foreach from=$allowed_accept item=_accept}}
          <label>
            <input type="checkbox" name="accept[{{$_accept}}]" value="{{$_accept}}"/>
            {{$_accept}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-body-required{{/tr}}</th>
      <td class="narrow">body required</td>
      <td>
        <label>
          <input type="radio" name="body_required" {{if $body_required}}checked{{/if}} value="1"/>{{tr}}Yes{{/tr}}
        </label>
        <label>
          <input type="radio" name="body_required" {{if !$body_required}}checked{{/if}} value="0"/>{{tr}}No{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>{{tr}}dPdeveloppement-Api-body-content type{{/tr}}</th>
      <td class="narrow">body content-type</td>
      <td>
        {{foreach from=$allowed_accept_body item=_accept}}
          <label>
            <input type="checkbox" name="content_type[{{$_accept}}]" value="{{$_accept}}"/>
            {{$_accept}}
          </label>
        {{/foreach}}
      </td>
    </tr>

    <tr>
      <th>
        {{tr}}dPdeveloppement-Api-responses{{/tr}}
        <button type="button" class="add notext" onclick="RouteCreator.addFields(
          'responses', 'response_name', 'response desc', '200', 'success'
        )">{{tr}}Add{{/tr}}</button>
      </th>
      <td class="narrow">responses</td>
      <td id="td-responses">
        <div id="responses[0]">
          <button type="button" class="remove notext" onclick="RouteCreator.removeParam('parameters[0]');">{{tr}}Remove{{/tr}}</button>
          <input type="text" name="response_name[0]" placeholder="200"/>
          <input type="text" name="response_desc[0]" placeholder="success"/>
        </div>
      </td>
    </tr>

    <tr>
      <td colspan="3" class="button">
        <button type="submit" class="change">{{tr}}dPdeveloppement-Action-Generate route{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-create-route"></div>
