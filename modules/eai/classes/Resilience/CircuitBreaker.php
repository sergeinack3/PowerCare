<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Resilience;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Interop\Ftp\CircuitBreakerException;
use Ox\Interop\Ftp\ResponseAnalyser;
use Ox\Interop\Webservices\CSOAPLegacy;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use phpDocumentor\Reflection\Types\Mixed_;
use Throwable;

class CircuitBreaker
{
    /** @var int */
    public int $_max_retry;

    /**
     * @param CExchangeSourceAdvanced  $source
     * @param ClientInterface  $client
     * @param callable         $call
     * @param ResponseAnalyser $request_analyser
     *
     * @throws CMbException
     * @throws CircuitBreakerException
     */
    public function execute(
        CExchangeSourceAdvanced $source,
        ClientInterface $client,
        callable $call,
        ResponseAnalyser $request_analyser
    ) {
        //on vérifie l'état du circuit ( OUVERT / FERME )
        //si ouvert on continue l'execution de la fonction execute
        //si fermé throw une Exception
        $this->checkCircuitAvailable($source);

        // Création d'un context comprenant toutes les informations
        // Récupération de la réponse dans le context dans le cas ou tout s'est bien passé
        return $this->request($call);
    }


    /**
     * @param CExchangeSourceAdvanced $source
     *
     * @return bool
     * @throws Exception
     */
    public function checkCircuitAvailable(CExchangeSourceAdvanced $source): bool
    {
        $statistics = $source->loadRefLastStatistic();
        // Cas du premier appel, pas de précédente stats
        if (!isset($statistics->_id)) {
            return true;
        }
        // Cas du dernier call qui n'est pas en erreur
        if (($delay = $this->getDelay($source)) === 0) {
            return true;
        }
        // Le circuit est en erreur on va l'ouvrir
        $date_now   = CMbDT::dateTime();
        $date_delay = CMbDT::dateTime('+' . $delay . 'seconds', $statistics->last_verification_date);

        // Ouvert : $date_now < $date_delay
        if (($date_now < $date_delay) && ($statistics->failures < $this->_max_retry)) {
            $waiting = CMbDT::timeRelative($date_now, $date_delay);
            //message de bloquage temporaire de la source
            throw new CircuitBreakerException(
                CircuitBreakerException::WAITING,
                $waiting
            );
        } elseif ($statistics->failures >= $this->_max_retry) {
            //message de bloquage de la source
            throw new CircuitBreakerException(
                CircuitBreakerException::SOURCE_LOCK
            );
        }

        return false;
    }

    /**
     * @param CExchangeSourceAdvanced $source
     *
     * @return int
     */
    public function getDelay(CExchangeSourceAdvanced $source): int
    {
        $statistics = $source->loadRefLastStatistic();
        // Aucune erreur lors du dernier appel
        if ($statistics->failures == "0") {
            return 0;
        }

        // Récupération du délai de la stratégie paramétré sur la source
        $delayList = $this->unserializeRetryStrategy($source->retry_strategy);

        //récupération du maximum d'essais définit dans la stratégie
        $this->_max_retry = array_key_last($delayList);

        return $statistics->failures >= $this->_max_retry ? end($delayList) : $delayList[$statistics->failures];
    }

    public function unserializeRetryStrategy(string $retry_strategy): array
    {
        // $retry_strategy = "1|5 5|60 10|120 20|";
        $tab     = explode(" ", $retry_strategy);
        $res_tab = [];

        //transforme la chaine de caracteres ci-dessus en tableau
        //resulta attendu:
        //1=>"5"
        //5=>"60"
        //10=>"120"
        //20=>""
        foreach ($tab as $element) {
            $tmp_tab = explode("|", $element);
            $res_tab[$tmp_tab[0]] = $tmp_tab[1] ?? 0;
        }

        //vas créer un tableau basé sur le précedent en créant toutes les clés manquante entre 1 et 20
        //exemple:
        //1=>"5"
        //2=>"5"
        //3=>"5"
        //4=>"5"
        //5=>"60"
        //6=>"60"
        //ect ...
        $max_for    = array_key_last($res_tab);
        $last_value = "";
        for ($i = 0; $i <= $max_for; $i++) {
            if ($i === 0) {
                $res_tab[$i] = "0";
            }
            if (isset($res_tab[$i]) && $res_tab[$i] !== "") {
                $last_value = $res_tab[$i];
            } else {
                $res_tab[$i] = $last_value;
            }
        }

        //sort le tableau final avant de le retourner
        sort($res_tab);

        return $res_tab;
    }

    /**
     * @param callable $call
     *
     * @return Mixed_
     */
    public function request(callable $call)
    {
        return $call();
    }
}
