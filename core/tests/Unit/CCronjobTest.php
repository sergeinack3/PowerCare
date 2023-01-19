<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use DateInterval;
use DateTime;
use Exception;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\System\Cron\CCronJob;
use Ox\Tests\OxUnitTestCase;

class CCronjobTest extends OxUnitTestCase
{
  /**
   * Tests some cronjob expressions
   * Todo: Test more complex expressions (complicated because of time dependency, consider using a DateTime mockup interface)
   *
   * @throws Exception
   */
  public function testGetNextDate()
  {
    $date = new DateTime('now');

    $expressions = [
      '0 * * * * *' => [
        $date->format('Y-m-d H:i:00'),
        $date->add(new DateInterval('PT1M'))->format('Y-m-d H:i:00'),
        $date->add(new DateInterval('PT1M'))->format('Y-m-d H:i:00'),
        $date->add(new DateInterval('PT1M'))->format('Y-m-d H:i:00'),
        $date->add(new DateInterval('PT1M'))->format('Y-m-d H:i:00'),
      ],
    ];

    foreach ($expressions as $_expression => $_expected_results) {
      $_cronjob            = new CCronJob();
      $_cronjob->execution = $_expression;
      [,$_cronjob->_minute, $_cronjob->_hour, $_cronjob->_day, $_cronjob->_month, $_cronjob->_week] = explode(' ', $_expression);

      $_results = $_cronjob->getNextDate(5);

      foreach ($_results as $_k => $_result) {
        $this->assertEquals($_expected_results[$_k], $_result);
      }
    }
  }

  public function testStoreAndGenerateToken()
  {
    $params = "m=system\ntab=about";

    $cron                  = new CCronJob();
    $cron->name            = 'test';
    $cron->params          = $params;
    $cron->active          = '1';
    $cron->_generate_token = true;

    $this->assertNull($cron->store());
    $this->assertNull($cron->params);

    /** @var CViewAccessToken $token */
    $token = $cron->loadRefToken();
    $this->assertInstanceOf(CViewAccessToken::class, $token);
    $this->assertEquals($params, $token->params);
  }

  public function testStoreAndReuseToken()
  {
    $token          = new CViewAccessToken();
    $token->params  = "m=system\ntab=about";
    $token->label   = 'test';
    $token->user_id = CUser::get()->_id;
    $this->assertNull($token->store());

    $cron                  = new CCronJob();
    $cron->name            = 'test';
    $cron->params          = "token={$token->hash}";
    $cron->active          = '1';
    $cron->_generate_token = true;

    $this->assertNull($cron->store());
    $this->assertNull($cron->params);
    $this->assertEquals($token->_id, $cron->token_id);
  }
}
