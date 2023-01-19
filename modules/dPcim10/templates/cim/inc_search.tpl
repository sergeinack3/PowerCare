{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    CIM.initializeViewSearch('search-cim-tabs');
  });
</script>

<ul id="search-cim-tabs" class="control_tabs">
  {{foreach from=$profiles key=_type item=_profile}}
    <li>
      <a href="#profile-{{$_type}}"{{if !$_profile.codes|@count}} class="empty"{{/if}}>
        {{tr}}Profile.{{$_type}}{{/tr}}
        {{$_profile.user}} ({{$_profile.codes|@count}})
      </a>
    </li>
  {{/foreach}}
  <li>
    <a href="#search-cim">{{tr}}common-search{{/tr}}</a>
  </li>
</ul>

{{foreach from=$profiles key=_type item=_profile}}
  <div id="profile-{{$_type}}" style="display: none;" data-user_id="{{$_profile.user_id}}">
    {{mb_include module=cim10 template=cim/inc_search_filters user_profile=$_profile.user_id tags=$_profile.tags
                 container_id="search-favoris-"|cat:$_profile.user_id|cat:"-results"}}
    <div id="search-favoris-{{$_profile.user_id}}-results">
      {{mb_include module=cim10 template=cim/inc_search_results codes=$_profile.codes user_profile=$_profile.user
                   container_id="profile-$_type" object_class=$object_class object_id=$object_id}}
    </div>
  </div>
{{/foreach}}

<div id="search-cim" style="display: none;">
  {{mb_include module=cim10 template=cim/inc_search_filters container_id="search-cim-results"}}
  <div id="search-cim-results">
    {{mb_include module=cim10 template=cim/inc_search_results container_id="search-cim-results"
                 object_class=$object_class object_id=$object_id}}
  </div>
</div>