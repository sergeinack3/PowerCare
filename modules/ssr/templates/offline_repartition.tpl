{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <th colspan="2">
      <big>{{tr var1=$date|date_format:$conf.date}}ssr-planning_repartition{{/tr}}</big>
    </th>
  </tr>
  <tr>
    <td>
       <script>
        Main.add(Control.Tabs.create.curry('tabs-plateaux', true));
      </script>
      <ul id="tabs-plateaux" class="control_tabs">
        {{foreach from=$plateaux item=_plateau}}
          <li>
            <a href="#{{$_plateau->_guid}}">
              {{$_plateau}}
            </a>
          </li>
        {{/foreach}}
      </ul>

      {{foreach from=$plateaux item=_plateau}}
        <table id="{{$_plateau->_guid}}" class="main" style="border-spacing: 4px; border-collapse: separate; width: auto;">
          <tr>
            {{foreach from=$_plateau->_ref_techniciens item=_technicien}}
              <td style="width: 150px;">
                <table class="tbl">
                {{assign var=technicien_id value=$_technicien->_id}}
                <tr>
                  <th id="technicien-{{$technicien_id}}">
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_technicien->_fwd.kine_id}}
                  </th>
                </tr>
                {{assign var=conge value=$_technicien->_ref_conge_date}}
                {{if $conge->_id}} 
                <tr>
                  <td class="ssr-kine-conges">
                    <strong onmouseover="ObjectTooltip.createEx(this, '{{$conge->_guid}}')">
                      {{$conge}}
                    </strong>
                  </td>
                </tr>
                {{/if}}
                <tbody id="sejours-technicien-{{$_technicien->_id}}">
                  {{foreach from=$sejours.$technicien_id item=_sejour}}
                    {{mb_include module=ssr template=inc_sejour_draggable remplacement=0 sejour=$_sejour}}
                  {{foreachelse}}
                  <tr>
                    <td class="empty">{{tr}}CSejour.none{{/tr}}</td>
                  </tr>
                  {{/foreach}}  
                  {{if count($replacements.$technicien_id)}}
                    <tr>
                      <th>{{tr}}CReplacement{{/tr}}s</th>
                    </tr>
                  {{/if}}
                  {{foreach from=$replacements.$technicien_id item=_replacement}}
                    <tr>
                      <td>
                        {{assign var=conge value=$_replacement->_ref_conge}}
                        {{assign var=replaced  value=$conge->_ref_user}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$conge->_guid}}')">
                          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$replaced}}
                        </span>
                      </td>
                    </tr>
                    {{mb_include module=ssr template=inc_sejour_draggable remplacement=1 sejour=$_replacement->_ref_sejour}}
                  {{/foreach}}
                  </tbody>
                </table>
              </td>
            {{foreachelse}}
              <td style="width: 150px;" class="text empty">{{tr}}CPlateauTechnique-back-techniciens.empty{{/tr}}</td>
            {{/foreach}}
          </tr> 
        </table>
      {{foreachelse}}
      <div class="small-warning">
        {{tr}}CGroups-back-plateaux_techniques.empty{{/tr}}
      </div>
      {{/foreach}}
    </td>
  </tr>  
</table>