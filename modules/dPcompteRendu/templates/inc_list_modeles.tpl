{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-owner", true);
  });
</script>

<ul id="tabs-owner" class="control_tabs">
  {{foreach from=$modeles key=owner item=_modeles}}
    <li>
      <a href="#owner-{{$owner}}" {{if !$_modeles|@count}}class="empty"{{/if}}>
        {{$owners.$owner}} <small>({{$_modeles|@count}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$modeles key=owner item=_modeles}}
  <div id="owner-{{$owner}}" style="display: none;" class="me-margin-0 me-padding-0">
    {{mb_include template=inc_modeles modeles=$modeles.$owner}}
  </div>
{{/foreach}}
