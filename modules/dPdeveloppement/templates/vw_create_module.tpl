{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="create-module-form" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="developpement" />
  <input type="hidden" name="dosql" value="do_create_module" />
  <table class="main form">
    <tr>
      <th colspan="2" class="title">
        Création d'un module
      </th>
    </tr>
    <tr>
      <th><label for="namespace">Espace de nom</label></th>
      <td>
        <input type="text" name="namespace_prefix" class="str notNull" readonly value="Ox" size="3" />

        <span>\</span>

        <select name="namespace_category">
          <option value="Mediboard" selected>Mediboard</option>
          <option value="Core">Core</option>
          <option value="Import">Import</option>
          <option value="Interop">Interop</option>
          <option value="Erp">Erp</option>
          <option value="AppFine\Server">AppFine\Server</option>
          <option value="AppFine\Client">AppFine\Client</option>
        </select>

        <span>\</span>

        <input type="text" name="namespace" class="str notNull"/>
      </td>
    </tr>
    <tr>
      <th><label for="name_canonical">Nom canonique</label></th>
      <td><input type="text" name="name_canonical" class="str notNull pattern|[a-zA-Z0-9_]*"/></td>
    </tr>
    <tr>
      <th><label for="name_short">Nom court</label></th>
      <td><input type="text" name="name_short" class="str notNull" /></td>
    </tr>
    <tr>
      <th><label for="name_long">Nom long</label></th>
      <td><input type="text" name="name_long" class="str notNull"/></td>
    </tr>
    <tr>
      <th><label for="trigramme">{{tr}}CModule.trigramme{{/tr}}</label></th>
      <td><input type="text" name="trigramme" class="str notNull"/></td>
    </tr>
    <tr>
      <th><label for="mod_category">{{tr}}CModule-mod_category{{/tr}}</label></th>
      <td>
        <select name="mod_category">
          {{foreach from=$categories_color item=_color key=_category}}
            <option data-color="#{{$_color}}" value="{{$_category}}">{{tr}}CModule.mod_category.{{$_category}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="mod_package">{{tr}}CModule-mod_package{{/tr}}</label></th>
      <td>
        <select name="mod_package">
          {{foreach from=$package_list item=_package}}
            <option value="{{$_package}}">{{tr}}CModule.mod_package.{{$_package}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="license">Licence</label></th>
      <td>
        <select name="license" class="notNull">
          {{foreach from=$licenses item=_license_value key=_license}}
            <option name="{{$_license}}">{{$_license}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        <button type="button" class="submit"
                onclick="this.form.onsubmit();">
          Créer le module
        </button>
      </td>
    </tr>
  </table>
</form>
