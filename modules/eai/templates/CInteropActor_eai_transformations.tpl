{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=transformation_rule ajax=true}}

<table class="main tbl">
  <tr>
    <th> {{mb_title class=CTransformation field=standard}} </th>
    <th> {{mb_title class=CTransformation field=domain}} </th>
    <th> {{mb_title class=CTransformation field=profil}} </th>
    <th> {{mb_title class=CTransformation field=message}} </th>
    <th> {{mb_title class=CTransformation field=transaction}} </th>
    <th> {{mb_title class=CTransformation field=version}} </th>
    <th> {{mb_title class=CTransformation field=extension}} </th>
    <th> {{mb_title class=CTransformationRule field=action_type}} </th>
    <th> {{mb_title class=CTransformationRule field=xpath_source}} </th>
    <th> {{mb_title class=CTransformationRule field=xpath_target}} </th>
  </tr>

  <tbody id="transformations">
  {{mb_include template="inc_list_transformations_lines"}}
  </tbody>
</table>
