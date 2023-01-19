{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $domain->_is_master_ipp}}
  <div class="small-info">
    {{tr}}CDomain-master_ipp{{/tr}}
  </div>
{{/if}}

{{if $domain->_is_master_nda}}
  <div class="small-info">
    {{tr}}CDomain-master_nda{{/tr}}
  </div>
{{/if}}

{{if $domain->derived_from_idex}}
  <div class="small-info">
    {{tr}}CDomain-is_derived_from_idex{{/tr}}
  </div>
{{/if}}

<script>
  Main.add(function () {
    tabs = Control.Tabs.create('tabs-domaine-identification', true, {
      afterChange: function (name_div) {
        switch (name_div.id) {
          case 'CDomain':
            Domain.refreshCDomain('{{$domain->_id}}');
            break;

          case 'vw_list_group_domains':
            Domain.refreshListGroupDomains('{{$domain->_id}}');
            break;

          case 'vw_list_incrementer':
            Domain.refreshSuppressionIncrementerDomain('{{$domain->_id}}');
            break;

          case 'vw_list_incrementer_actor':
            Domain.refreshListIncrementerActor('{{$domain->_id}}');
            break;
          default :
        }
      }
    });
  });
</script>

<ul id="tabs-domaine-identification" class="control_tabs">
  <li><a href="#CDomain">{{tr}}CDomain{{/tr}}</a></li>
  <li><a href="#vw_list_group_domains">{{tr}}CGroups-back-group_domains{{/tr}}</a></li>
  <li><a href="#vw_list_incrementer"> {{mb_label object=$domain field="incrementer_id"}}</a></li>
  <li><a href="#vw_list_incrementer_actor">{{mb_label object=$domain field="actor_id"}} </a></li>
</ul>

<br />

{{if $domain->derived_from_idex}}
  {{mb_return}}
{{/if}}

<div id="CDomain" style="display: none;"></div>
<div id="vw_list_group_domains" style="display: none;"></div>
<div id="vw_list_incrementer" style="display: none;"></div>
<div id="vw_list_incrementer_actor" style="display: none;"></div>
