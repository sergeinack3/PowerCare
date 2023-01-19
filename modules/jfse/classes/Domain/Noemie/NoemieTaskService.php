<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CSQLDataSource;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Jfse\ApiClients\NoemieClient;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePayment;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * A service that handle the planned tasks that import the Noemie payments, and invoices acknowledgements.
 *
 * The class does not extend AbstractService because it does not make direct calls to the Jfse API
 */
final class NoemieTaskService
{
    protected const DEFAULT_USERS_PACE = 10;

    protected const NOEMIE_MUTEX_NAME = 'Jfse-task-process_payments_users';
    protected const NOEMIE_USER_PACE_CACHE_KEY       = 'Jfse-task-processUsersPayments-users_pace';
    protected const NOEMIE_PROCESSED_USERS_CACHE_KEY = 'Jfse-task-processUsersPayments-processed_users';

    protected const ACKS_MUTEX_NAME = 'Jfse-task-processInvoicesAcknowledgements';
    protected const ACKS_USER_PACE_CACHE_KEY       = 'Jfse-task-processInvoicesAcknowledgements-users_pace';
    protected const ACKS_PROCESSED_USERS_CACHE_KEY = 'Jfse-task-processInvoicesAcknowledgements-processed_users';

    protected const PROCESSED_USERS_LIST_CACHE_TTL = 43200;
    protected const USERS_PACE_CACHE_TTL           = 86400;

    protected const LOCALE_MUTEX_ERROR     = 'NoemieTaskService-error-mutex_error';
    protected const LOCALE_CACHE_GET_ERROR = 'NoemieTaskService-error-cache_get_error';
    protected const LOCALE_CACHE_SET_ERROR = 'NoemieTaskService-error-cache_set_error';
    protected const LOCALE_NO_USERS_ERROR  = 'NoemieTaskService-error-no_users_found';

    /** @var NoemieService The Noemie service, that will import the payments or acknowledgements for a user */
    protected $service;

    /** @var LayeredCache The cache instance used to store the data that needs to persists between executions of the tasks */
    protected $cache;

    /** @var CMbMutex The instance of the distributed mutex */
    protected $mutex;

    /** @var ImportProcessReport The report that will be returned at the end of the process */
    protected $report;

    /** @var int The total number of users for which the tasks must be executed */
    protected $users_count;

    /** @var array The list of users processed in previous executions of the task */
    protected $processed_users_list;

    /** @var int The number of user to process for each execution of the task */
    protected $users_pace;

    /** @var CJfseUser[] The list of users to process in the current execution of the task */
    protected $users;

    /**
     * NoemieTaskService constructor.
     *
     * If the instance of the cache cannot be retrieved, an exception will be thrown
     *
     * @param NoemieClient|null $client
     *
     * @throws CouldNotGetCache
     */
    public function __construct(LayeredCache $cache = null, NoemieClient $client = null)
    {
        $this->cache = $cache ?? LayeredCache::getCache(LayeredCache::INNER_DISTR);

        $this->service = new NoemieService($client);
    }

    /**
     * @param int|null               $user_pace
     * @param DateTimeImmutable|null $start
     * @param DateTimeImmutable|null $end
     *
     * @return ImportProcessReport
     * @throws InvalidArgumentException
     */
    public function processNoemiePayments(
        ?int $user_pace = null,
        ?DateTimeImmutable $start = null,
        ?DateTimeImmutable $end = null
    ): ImportProcessReport {
        $this->report = new ImportProcessReport(ImportProcessReport::IMPORT_TYPE_NOEMIE);
        $this->users_pace = $user_pace;

        try {
            $this->acquireMutex(self::NOEMIE_MUTEX_NAME);
        } catch (Exception $e) {
            $this->report->setFatalError(self::LOCALE_MUTEX_ERROR);

            return $this->report;
        }

        /* Getting the total number of jfse users linked to mediusers */
        $this->countLinkedUsers();

        if ($this->users_count) {
            try {
                /* Get the list of jfse users for which the payments have been processed today */
                $this->getProcessedUsersListFromCache(self::NOEMIE_PROCESSED_USERS_CACHE_KEY);
                /* Get the number of users to process for each iteration */
                $this->getUsersPaceFromCache(self::NOEMIE_USER_PACE_CACHE_KEY);
            } catch (Exception $e) {
                $this->report->setFatalError(self::LOCALE_CACHE_GET_ERROR, $e->getMessage());

                return $this->report;
            }

            /* If all the users have been processed, the list is reinitialized */
            if ($this->users_count <= count($this->processed_users_list)) {
                $this->processed_users_list = [];
            }

            /* Get the list of jfse users to process in the current execution */
            $this->getUsersToProcess();

            /* Start a timer, used to reduce or increase the number of users processed by each call,
             * depending on the duration of the whole process */
            $this->report->startTimer();

            foreach ($this->users as $user) {
                try {
                    [$start, $end] = $this->getDatesImportPayments($user, $start, $end);
                } catch (Exception $e) {
                    $start = null;
                }

                try {
                    $this->service->processPaymentsForUser($user, $start, $end);
                    $this->report->incrementSuccessCounter();
                } catch (Exception $e) {
                    $this->report->incrementErrorsCounter();
                }
            }

            /* Stop the timer and update the pace for the next execution, depending on the duration */
            $this->report->stopTimer();

            try {
                if (!$user_pace) {
                    $this->updateUserPaceInCache(self::NOEMIE_USER_PACE_CACHE_KEY);
                }

                $this->updateProcessedUsersListInCache(self::NOEMIE_PROCESSED_USERS_CACHE_KEY);
            } catch (Exception $e) {
                $this->report->incrementErrorsCounter();
                $this->report->setMessage(self::LOCALE_CACHE_SET_ERROR);
            }
        } else {
            $this->report->setFatalError(self::LOCALE_NO_USERS_ERROR);
        }

        $this->mutex->release();

        return $this->report;
    }

    /**
     * @param int|null $user_pace
     *
     * @return ImportProcessReport
     * @throws InvalidArgumentException
     */
    public function processInvoiceAcknowledgements(?int $user_pace = null): ImportProcessReport
    {
        $this->report = new ImportProcessReport(ImportProcessReport::IMPORT_TYPE_ACKNOWLEDGEMENTS);
        $this->users_pace = $user_pace;

        try {
            $this->acquireMutex(self::ACKS_MUTEX_NAME);
        } catch (Exception $e) {
            $this->report->setFatalError(self::LOCALE_MUTEX_ERROR);

            return $this->report;
        }

        /* Getting the total number of jfse users linked to mediusers */
        $this->countLinkedUsers();

        if ($this->users_count) {
            try {
                /* Get the list of jfse users for which the payments have been processed today */
                $this->getProcessedUsersListFromCache(self::ACKS_PROCESSED_USERS_CACHE_KEY);
                /* Get the number of users to process for each iteration */
                $this->getUsersPaceFromCache(self::ACKS_USER_PACE_CACHE_KEY);
            } catch (Exception $e) {
                $this->report->setFatalError(self::LOCALE_CACHE_GET_ERROR, $e->getMessage());

                return $this->report;
            }

            /* If all the users have been processed, the list is reinitialized */
            if ($this->users_count <= count($this->processed_users_list)) {
                $this->processed_users_list = [];
            }

            /* Get the list of jfse users to process in the current execution */
            $this->getUsersToProcess();

            /* Start a timer, used to reduce or increase the number of users processed by each call,
             * depending on the duration of the whole process */
            $this->report->startTimer();

            foreach ($this->users as $user) {
                try {
                    $date_max = new DateTimeImmutable();
                    $date_min = $this->getOldestValidatedInvoiceDateForUser($user);
                } catch (Exception $e) {
                    $date_min = null;
                    $date_max = new DateTimeImmutable();
                }

                try {
                    $this->service->processInvoiceAcknowledgements($user, $date_min, $date_max);
                    $this->report->incrementSuccessCounter();
                } catch (Exception $e) {
                    $this->report->incrementErrorsCounter();
                }
            }

            /* Stop the timer and update the pace for the next execution, depending on the duration */
            $this->report->stopTimer();

            try {
                if (count($this->users) == $this->users_pace) {
                    $this->updateUserPaceInCache(self::ACKS_USER_PACE_CACHE_KEY);
                }
                $this->updateProcessedUsersListInCache(self::ACKS_PROCESSED_USERS_CACHE_KEY);
            } catch (Exception $e) {
                $this->report->incrementErrorsCounter();
                $this->report->setMessage(self::LOCALE_CACHE_SET_ERROR);
            }
        } else {
            $this->report->setFatalError(self::LOCALE_NO_USERS_ERROR);
        }

        $this->mutex->release();

        return $this->report;
    }

    /**
     * Acquire a distributed mutex with the given key.
     * Throw an exception if the mutex cannot be locked
     *
     * @param string $key
     *
     * @return void
     * @throws Exception
     */
    protected function acquireMutex(string $key): void
    {
        $this->mutex = CMbMutex::getDistributedMutex($key);

        if (!$this->mutex->lock(600)) {
            throw new Exception('Unable to lock mutex for cronjob');
        }
    }

    /**
     * @param string $key
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getProcessedUsersListFromCache(string $key): array
    {
        $this->processed_users_list = $this->cache->get($key, [], self::PROCESSED_USERS_LIST_CACHE_TTL);

        return $this->processed_users_list;
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function updateProcessedUsersListInCache(string $key): bool
    {
        $this->processed_users_list = array_merge($this->processed_users_list, array_keys($this->users));

        return $this->cache->set($key, $this->processed_users_list, self::PROCESSED_USERS_LIST_CACHE_TTL);
    }


    /**
     * @param string $key
     *
     * @return int
     * @throws InvalidArgumentException
     */
    protected function getUsersPaceFromCache(string $key): int
    {
        if (is_null($this->users_pace)) {
            $this->users_pace = $this->cache->get($key, self::DEFAULT_USERS_PACE, self::USERS_PACE_CACHE_TTL);
        }

        return $this->users_pace;
    }


    /**
     * @param string $key
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function updateUserPaceInCache(string $key): bool
    {
        if ($this->report->getProcessDuration() > 240) {
            $this->users_pace -= 5;
        } elseif ($this->report->getProcessDuration() > 180) {
            $this->users_pace --;
        } elseif ($this->report->getProcessDuration() < 30) {
            $this->users_pace += 5;
        } elseif ($this->report->getProcessDuration() < 120) {
            $this->users_pace++;
        }

        if ($this->users_pace < 1) {
            $this->users_pace = 1;
        } elseif ($this->users_pace > 30) {
            /* Ensure that the user pace doesn't get to big */
            $this->users_pace = 30;
        }

        return $this->cache->set($key, $this->users_pace, self::USERS_PACE_CACHE_TTL);
    }

    /**
     * @return int
     */
    protected function countLinkedUsers(): int
    {
        try {
            $this->users_count = (new CJfseUser())->countList([
                'mediuser_id' => 'IS NOT NULL'
            ]);

            if (is_null($this->users_count)) {
                $this->users_count = 0;
            }
        } catch (Exception $e) {
            $this->users_count = 0;
        }

        return (int)$this->users_count;
    }

    /**
     * @return CJfseUser[]
     */
    public function getUsersToProcess(): array
    {
        try {
            $where = [
                'mediuser_id' => 'IS NOT NULL'
            ];

            if (count($this->processed_users_list)) {
                $where['jfse_user_id'] = CSQLDataSource::prepareNotIn($this->processed_users_list);
            }

            $this->users = (new CJfseUser())->loadList($where, 'jfse_id ASC', "0, {$this->users_pace}");

            if ($this->users === null) {
                $this->users = [];
            }
        } catch (Exception $e) {
            $this->users = [];
        }

        return $this->users;
    }

    /**
     * @param CJfseUser              $user
     * @param DateTimeImmutable|null $start
     * @param DateTimeImmutable|null $end
     *
     * @return array
     */
    public function getDatesImportPayments(CJfseUser $user, ?DateTimeImmutable $start, ?DateTimeImmutable $end): array
    {
        if (!$end) {
            /* If the user has payment that have been processed, we use the date of the last one,
             * for reducing the number of payments returned by Jfse.
             * Otherwise, we get all the payments */
            $end = new DateTimeImmutable();
            $start = CJfsePayment::getLastPaymentDateForUser($user);

            if ($start) {
                /* For determining the period, we use the last payment date, minus one month */
                $start = $start->sub(new DateInterval('P1M'));

                if ($start == false) {
                    $start = null;
                }
            }
        }

        return [$start, $end];
    }

    /**
     * @param CJfseUser $user
     *
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    public function getOldestValidatedInvoiceDateForUser(CJfseUser $user): ?DateTimeImmutable
    {
        try {
            $invoice = (new CJfseInvoice())->loadList([
            'jfse_user_id' => " = {$user->_id}",
            'status'       => " = '" . InvoiceStatusEnum::VALIDATED()->getValue() . "'"
            ], 'creation ASC', '0, 1');

            if (!is_array($invoice) || !count($invoice)) {
                throw new Exception('No invoice found');
            }

            /** @var CJfseInvoice $invoice */
            $invoice = reset($invoice);

            $date = new DateTimeImmutable($invoice->creation);

            /* Checks if the given date is older than 6 months */
            $interval = $date->diff(new DateTimeImmutable());
            if ($interval->days > 180) {
                $date->sub(new DateInterval('P6M'));
            }
        } catch (Exception $e) {
            $date = (new DateTimeImmutable())->sub(new DateInterval('P1M'));
        }

        return $date;
    }
}
