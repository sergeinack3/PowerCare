{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$actor->_id}}
  <div class="small-error">{{tr}}CInteropActor-msg-None actor{{/tr}}</div>
  {{mb_return}}
{{/if}}

<script>
  document.getElementById("menu_receiver_type").setAttribute("class", "special");
</script>

{{mb_include module=eai template=inc_summary_actor}}

<div class="small-info">{{tr}}CMessageSupported-msg-Explication mode easy{{/tr}}</div>

<div id="exchanges">
  {{foreach from=$all_messages key=_domain item=_families}}
    <fieldset>
      <legend>{{tr}}{{$_domain}}{{/tr}} -
        <span class="compact">{{tr}}{{$_domain}}-desc{{/tr}}</span>
      </legend>

      <ul style="list-style: none;">
        {{foreach from=$_families item=_family}}
          {{assign var=_family_name value=$_family|getShortName}}
          <li>
            <div style="background-color: ; padding : 5px;">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-toggle-off" style="font-size: large;" value="0"
                   onclick="InteropActor.checkCategory('{{$_family_name}}', null, '{{$actor->_guid}}', this)"></i>
              </a>

              {{tr}}{{$_family_name}}{{/tr}} -
              <span class="compact" style="color : black;">{{tr}}{{$_family_name}}-desc{{/tr}}</span>

              {{if $_family->_categories && !"none"|array_key_exists:$_family->_categories}}
                <a href="#">
                  <i class="fa fa-arrow-circle-down fa-2x" style="float :right;"
                     onclick="InteropActor.showCategory('{{$_family_name}}');"></i>
                </a>
              {{/if}}
            </div>
          </li>

          <div style="padding-left: 30px; padding-right : 30px; display: none;" class="category_{{$_family_name}}">
            <ul style="list-style: none;">
              {{foreach from=$_family->_categories key=_category_name item=_messages_supported}}
                {{if $_category_name != "none"}}
                  <li>
                    <a href="#" style="text-decoration: none;">
                      <i class="fa fa-toggle-off" value="0" style="font-size: large;"
                         onclick="InteropActor.checkCategory('{{$_family_name}}', '{{$_category_name}}', '{{$actor->_guid}}', this)">
                      </i>
                    </a>
                    {{tr}}{{$_category_name}}{{/tr}} (<em>{{$_category_name}})</em>
                  </li>
                {{/if}}
              {{/foreach}}
            </ul>
          </div>
        {{/foreach}}
      </ul>
    </fieldset>
  {{/foreach}}
</div>

<button type="button" class="fa fa-chevron-circle-right" style="margin-top: 10px; float: right;">
  <a href="#source" style="text-decoration: none;">{{tr}}Next{{/tr}}</a>
</button>