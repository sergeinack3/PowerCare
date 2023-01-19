{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  toggleFields = function(checkbox) {
    $$('div#advanced_search_tooltip input.query_field').each(function(input) {
      input.checked = checkbox.checked;
    });
  }

  setRange = function(field) {
    if (field.checked) {
      $V(field.form.elements['query_range'], field.value);
    }
  }

  Main.add(function() {
    getForm('searchMessages').observe('keydown', function(event) {
      if (event.which == 13 || event.keyCode == 13) {
        $('searchButton').click();
        event.preventDefault();
      }
    });
  });
</script>

<form name="searchMessages" method="post" action="#" onsubmit="UserEmail.refreshList();">
  <div id="input_container" style="display: inline-block; background: #fff; margin-right: 0px; padding-left: 2px; padding-right: 2px; width: 600px; height: 16px; border: 1px solid #a5acb2;">
    <span style="opacity: 0.7; cursor: pointer; float: left;" onclick="ObjectTooltip.createDOM(this, 'advanced_search_tooltip', {duration: 0});">
      <i class="fa fa-cog fa-lg msgicon"></i>
    </span>
    <span style="opacity: 0.7; cursor: pointer; float: right;" onclick="$V($('searchMessages_keywords'), '');">
      <i class="fa fa-times fa-lg msgicon"></i>
    </span>
    <input type="text" name="keywords" value="{{$query}}" class="me-small" style="width: 560px; border: none;">
  </div>
  <button id="searchButton" type="button" style="margin-left: 0px;" onclick="this.form.onsubmit();">
    <i class="fa fa-search msgicon"></i>
    {{tr}}Search{{/tr}}
  </button>
</form>

<div id="advanced_search_tooltip" style="display: none;">
  <form name="searchOptions" method="post" action="?" onsubmit="return false;">
    <strong>{{tr}}CUserMail-query{{/tr}} :</strong>
    <ul style="list-style: none; padding-left: 5px; border-bottom: 1px solid #888;">
      <li>
        <input type="checkbox" name="query_subject" class="query_field"{{if array_key_exists('subject', $query_options) && $query_options.subject}} checked{{/if}}> {{tr}}CUserMail-subject{{/tr}}
      </li>
      <li>
        <input type="checkbox" name="query_from" class="query_field"{{if array_key_exists('from', $query_options) && $query_options.from}} checked{{/if}}> {{tr}}CUserMail-from{{/tr}}
      </li>
      <li>
        <input type="checkbox" name="query_to" class="query_field"{{if array_key_exists('to', $query_options) && $query_options.to}} checked{{/if}}> {{tr}}CUserMail-query-to{{/tr}}
      </li>
      <li>
        <input type="checkbox" name="query_body" class="query_field"{{if array_key_exists('body', $query_options) && $query_options.body}} checked{{/if}}> {{tr}}CUserMail-query-body{{/tr}}
      </li>
      <li>
        <input type="checkbox" name="query_all" class="query_field" onchange="toggleFields(this);"> {{tr}}CUserMail-query-all{{/tr}}
      </li>
    </ul>
    {{if 'range'|array_key_exists:$query_options}}
    <strong>{{tr}}CUserMail-query-range{{/tr}} :</strong>
    <input type="hidden" name="query_range" value="{{$query_options.range}}">
    <ul style="list-style: none; padding-left: 5px;">
      <li>
        <input type="radio" name="_query_range" value="selected" onchange="setRange(this);"{{if $query_options.range == 'selected'}} checked{{/if}}> {{tr}}CUserMail-query-range.actual{{/tr}}
      </li>
      <li>
        <input type="radio" name="_query_range" value="subfolders" onchange="setRange(this);"{{if $query_options.range == 'subfolders'}} checked{{/if}}> {{tr}}CUserMail-query-range.subfolders{{/tr}}
      </li>
      <li>
        <input type="radio" name="_query_range" value="all" onchange="setRange(this);"{{if $query_options.range == 'all'}} checked{{/if}}> {{tr}}CUserMail-query-range.all{{/tr}}
      </li>
    </ul>
    {{/if}}
  </form>
</div>
