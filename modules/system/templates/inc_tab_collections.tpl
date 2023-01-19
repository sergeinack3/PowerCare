{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td class="narrow">

      <script type="text/javascript">
        Main.add(function () {
          Control.Tabs.create('tabs-backs-{{$form_uid}}', false, {
            afterChange: function (container) {
              container.previous("form").onsubmit();
            }
          });
        });
      </script>

      <!-- Création des tabs -->
      <ul id="tabs-backs-{{$form_uid}}" class="control_tabs_vertical" style="width: 30em;">
        {{foreach from=$object_select->_back key=backName item=backObjects}}
          {{assign var=backSpec value=$object_select->_backSpecs.$backName}}
          {{assign var=count value=$count_obj.$backName}}
          {{if $count}}
            <li>
              <a href="#back-{{$backName}}-{{$form_uid}}">
                {{tr}}{{$backSpec->_initiator}}-back-{{$backName}}{{/tr}} ({{$count}})
              </a>
            </li>
          {{/if}}
        {{/foreach}}
      </ul>
    </td>

    <!-- Contenu des tabs -->
    <td>
      <div id="table_backs_{{$form_uid}}">

        {{foreach from=$object_select->_back key=back_name item=back_objects}}
          <form name="filter_back_{{$back_name}}_{{$form_uid}}" method="get"
                onsubmit="return Url.update(this, 'back-{{$back_name}}-{{$form_uid}}')">
            <input type="hidden" name="m" value="{{$m}}"/>
            <input type="hidden" name="a" value="ajax_vw_collection"/>
            <input type="hidden" name="start" value="{{$start}}" onchange="this.form.onsubmit()"/>
            <input type="hidden" name="object_class" value="{{$object_select->_class}}"/>
            <input type="hidden" name="object_id" value="{{$object_select->_id}}"/>
            <input type="hidden" name="back_ref_name" value="{{$back_name}}"/>
            <input type="hidden" name="form_uid" value="{{$form_uid}}"/>
          </form>
          <div id="back-{{$back_name}}-{{$form_uid}}">
          </div>
        {{/foreach}}

      </div>
    </td>
  </tr>
</table>
