{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('main_tab_group', true, {
      afterChange: function (container) {
        if (container === map) {
          var url = new Url('dPdeveloppement', 'vw_class_map');
          url.requestUpdate(container.id);
          return;
        }
      }
    });
  });
</script>

<style>
  #stat table tr:not(:first-of-type) th {
    text-align: left;
    width: 150px;
  }


  #stat table td {
    vertical-align: top;
  }

  #map table {
    margin-top: 15px;
  }

  #map table > tbody > tr:not(:first-of-type) td:hover {
    cursor: pointer;
  }
</style>

<ul id="main_tab_group" class="control_tabs">
  <li><a href="#stat">Statistiques</a></li>
  <li><a href="#module">Modules</a></li>
  <li><a href="#prefix">Prefix PSR-4</a></li>
  <li><a href="#map">Class Map</a></li>
</ul>
<br>
<div id="stat">
  <table class="tbl main">
    <tr>
      <th class="title" colspan="2">
        Informations
      </th>
    </tr>
    <tr>
      <th>Fichier</th>
      <td>{{$classmap_file}}</td>
    </tr>
    <tr>
      <th>Date de création</th>
      <td>{{$classmap_date|date_format:$conf.datetime}}</td>
    </tr>
    <tr>
      <th>Size</th>
      <td>{{$classmap_size}}</td>
    </tr>
    <tr>
      <th>Lines</th>
      <td>{{$classmap_line|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Glob (pattern)</th>
      <td>
        {{foreach from=$classmap_dirs item=_dir}}
          {{$_dir}}
          <br>
        {{/foreach}}
      </td>
    </tr>
  </table>

  <br>

  <table class="tbl main">
    <tr>
      <th class="title" colspan="2">
        Classes
      </th>
    </tr>
    <tr>
      <th>Classes</th>
      <td>{{$count_classe|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Parents</th>
      <td>{{$count_parent|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Children</th>
      <td>{{$count_children|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Interfaces</th>
      <td>{{$count_interface|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Traits</th>
      <td>{{$count_trait|number_format:0:'.':' '}}</td>
    </tr>
  </table>

  <br>


  <table class="tbl main">
    <tr>
      <th class="title" colspan="2">
        Namespaces
      </th>
    </tr>
    {{foreach from=$namespaces item=_count key=_namespace}}
      <tr>
        <th>Ox\{{$_namespace}}</th>
        <td>{{$_count|number_format:0:'.':' '}}</td>
      </tr>
    {{/foreach}}
  </table>
  <br>

  <table class="tbl main">
    <tr>
      <th class="title" colspan="2">
        References
      </th>
    </tr>
    <tr>
      <th>Fichier</th>
      <td>{{$classref_file}}</td>
    </tr>
    <tr>
      <th>Date de création</th>
      <td>{{$classref_date|date_format:$conf.datetime}}</td>
    </tr>
    <tr>
      <th>Size</th>
      <td>{{$classref_size}}</td>
    </tr>
    <tr>
      <th>Lines</th>
      <td>{{$classref_line|number_format:0:'.':' '}}</td>
    </tr>
    <tr>
      <th>Classes</th>
      <td>{{$classref_keys|number_format:0:'.':' '}}</td>
    </tr>
  </table>
</div>

<div id="module">
  <table class="tbl main">
    <thead>
    <th width="50%">Modules ({{$modules|@count}})</th>
    <th width="50%">Classes</th>
    </thead>
    {{foreach from=$modules item=_count key=_module}}
      <tr>
        <td>{{$_module}}</td>
        <td>{{$_count|number_format:0:'.':' '}}</td>
      </tr>
    {{/foreach}}
  </table>
  <br>
</div>


<div id="prefix">
  <table class="tbl main">
    <thead>
    <th width="50%">Prefix ({{$prefix|@count}})</th>
    <th width="50%">Path</th>
    </thead>
    {{foreach from=$prefix item=_prefix_path key=_prefix_name}}
      <tr>
        <td>{{$_prefix_name}}</td>
        <td>{{$_prefix_path}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>

<div id="map"><!-- ajax --></div>
