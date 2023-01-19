{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=hide_selector value='0'}}

<script>
  Main.add(function() {
    CsARR.initializeViewSearch('search-csarr-tabs');
  });
</script>

<ul id="search-csarr-tabs" class="control_tabs">
  {{foreach from=$profiles key=_type item=_profile}}
    <li>
      <a href="#profile-{{$_type}}"{{if !$_profile.codes|@count}} class="empty"{{/if}}>
        {{$_profile.user}} ({{$_profile.codes|@count}})
      </a>
    </li>
  {{/foreach}}
  <li>
    <a href="#search-csarr">{{tr}}common-search{{/tr}}</a>
  </li>
</ul>

{{foreach from=$profiles key=_type item=_profile}}
  <div id="profile-{{$_type}}" style="display: none;" data-user_id="{{$_profile.user_id}}">
    {{mb_include module=ssr template=csarr/inc_search_results codes=$_profile.codes user_profile=$_profile.user
                 container_id="profile-$_type" object_class=$object_class object_id=$object_id}}
  </div>
{{/foreach}}

<div id="search-csarr" style="display: none;">
  {{mb_include module=ssr template=csarr/inc_search_filters}}
  <div id="search-csarr-results">
    {{mb_include module=ssr template=csarr/inc_search_results
                 container_id="search-csarr-results" object_class=$object_class object_id=$object_id}}
  </div>
</div>