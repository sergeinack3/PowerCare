{{*
* @package Mediboard\System
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=size value="18px"}}
{{mb_default var=mbd_size value=$size}}
{{mb_default var=img_style value=""}}

{{assign var=lang value='Ox\Core\CAppUI::pref'|static_call:"LOCALE":"fr"}}
<img src="modules/{{$mod_name}}/images/iconographie/{{$lang}}/icon.png" alt=""
     style="height:{{$mbd_size}}; width:{{$mbd_size}}; {{$img_style}};">
