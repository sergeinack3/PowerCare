<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CRequest;
use Ox\Core\CRequestException;
use Ox\Tests\OxUnitTestCase;

/**
 * @group schedules
 */
class CRequestTest extends OxUnitTestCase
{
    /**
     * @dataProvider orderByProviderOK
     *
     * @param string $order_by
     */
    public function test_order_by_is_strictly_valid(string $order_by): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addOrder($order_by);

        $order = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("ORDER BY {$order_by}", $order);
    }

    /**
     * @dataProvider clauseProviderNOK
     *
     * @param string $order_by
     */
    public function test_order_by_is_strictly_invalid(string $order_by): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addOrder($order_by);

        $this->expectException(CRequestException::class);
        $request->makeSelect();
    }

    /**
     * @dataProvider orderByProviderOK
     * @dataProvider clauseProviderNOK
     *
     * @param string $order_by
     */
    public function test_order_by_non_strict(string $order_by): void
    {
        $request = new CRequest(false);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addOrder($order_by);

        $order = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("ORDER BY {$order_by}", $order);
    }

    /**
     * @dataProvider groupByProviderOK
     *
     * @param string $group_by
     */
    public function test_group_by_is_strictly_valid(string $group_by): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addGroup($group_by);

        $group = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("GROUP BY {$group_by}", $group);
    }

    /**
     * @dataProvider clauseProviderNOK
     *
     * @param string $group_by
     */
    public function test_group_by_is_strictly_invalid(string $group_by): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addGroup($group_by);

        $this->expectException(CRequestException::class);
        $request->makeSelect();
    }

    /**
     * @dataProvider groupByProviderOK
     * @dataProvider clauseProviderNOK
     *
     * @param string $group_by
     */
    public function test_group_by_non_strict(string $group_by): void
    {
        $request = new CRequest(false);
        $request->addSelect('*');
        $request->addTable('table');
        $request->addGroup($group_by);

        $group = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("GROUP BY {$group_by}", $group);
    }

    /**
     * @dataProvider limitProviderOK
     *
     * @param string $limit
     */
    public function test_limit_is_strictly_valid(string $limit): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->setLimit($limit);

        $limit_clause = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("LIMIT {$limit}", $limit_clause);
    }

    /**
     * @dataProvider clauseProviderNOK
     *
     * @param string $limit
     */
    public function test_limit_is_strictly_invalid(string $limit): void
    {
        $request = new CRequest(true);
        $request->addSelect('*');
        $request->addTable('table');
        $request->setLimit($limit);

        $this->expectException(CRequestException::class);
        $request->makeSelect();
    }

    /**
     * @dataProvider limitProviderOK
     * @dataProvider clauseProviderNOK
     *
     * @param string $limit
     */
    public function test_limit_non_strict(string $limit): void
    {
        $request = new CRequest(false);
        $request->addSelect('*');
        $request->addTable('table');
        $request->setLimit($limit);

        $limit_clause = explode("\n", $request->makeSelect())[2];
        $this->assertEquals("LIMIT {$limit}", $limit_clause);
    }

    public function checkGroupByProvider(): array
    {
        return [
            ['test test.column', true],
            ['`test test.column', true],
            ['test test`.column', true],
            ['`test.column', true],
            ['test`.column', true],
            ['test test.`column', true],
            ['test test.column`', true],
            ['test.`column', true],
            ['test.column`', true],
            ['`test``column`', true],
        ];
    }

    public function orderByProviderOK(): array
    {
        return [
            '<column>'                                                     => ['column'],
            '<column> ASC'                                                 => ['column ASC'],
            '<column> asc'                                                 => ['column asc'],
            '<column> DESC'                                                => ['column DESC'],
            '<column> desc'                                                => ['column desc'],
            '<table>.<column>'                                             => ['table.column'],
            '<table>.<column> ASC'                                         => ['table.column ASC'],
            '<table>.<column> asc'                                         => ['table.column asc'],
            '<table>.<column> DESC'                                        => ['table.column DESC'],
            '<table>.<column> desc'                                        => ['table.column desc'],
            '`<column>`'                                                   => ['`column`'],
            '`<column>` ASC'                                               => ['`column` ASC'],
            '`<column>` asc'                                               => ['`column` asc'],
            '`<column>` DESC'                                              => ['`column` DESC'],
            '`<column>` desc'                                              => ['`column` desc'],
            '`<table>`.`<column>`'                                         => ['`table`.`column`'],
            '`<table>`.`<column>` ASC'                                     => ['`table`.`column` ASC'],
            '`<table>`.`<column>` asc'                                     => ['`table`.`column` asc'],
            '`<table>`.`<column>` DESC'                                    => ['`table`.`column` DESC'],
            '`<table>`.`<column>` desc'                                    => ['`table`.`column` desc'],
            '`<table name>`.`<column>`'                                    => ['`table name`.`column`'],
            '`<table name>`.`<column>` ASC'                                => ['`table name`.`column` ASC'],
            '`<table name>`.`<column>` asc'                                => ['`table name`.`column` asc'],
            '`<table name>`.`<column>` DESC'                               => ['`table name`.`column` DESC'],
            '`<table name>`.`<column>` desc'                               => ['`table name`.`column` desc'],
            '-<column>'                                                    => ['-column'],
            '-<column> ASC'                                                => ['-column ASC'],
            '-<column> asc'                                                => ['-column asc'],
            '-<column> DESC'                                               => ['-column DESC'],
            '-<column> desc'                                               => ['-column desc'],
            '-<table>.<column>'                                            => ['-table.column'],
            '-<table>.<column> ASC'                                        => ['-table.column ASC'],
            '-<table>.<column> asc'                                        => ['-table.column asc'],
            '-<table>.<column> DESC'                                       => ['-table.column DESC'],
            '-<table>.<column> desc'                                       => ['-table.column desc'],
            '-`<column>`'                                                  => ['-`column`'],
            '-`<column>` ASC'                                              => ['-`column` ASC'],
            '-`<column>` asc'                                              => ['-`column` asc'],
            '-`<column>` DESC'                                             => ['-`column` DESC'],
            '-`<column>` desc'                                             => ['-`column` desc'],
            '-`<table>`.`<column>`'                                        => ['-`table`.`column`'],
            '-`<table>`.`<column>` ASC'                                    => ['-`table`.`column` ASC'],
            '-`<table>`.`<column>` asc'                                    => ['-`table`.`column` asc'],
            '-`<table>`.`<column>` DESC'                                   => ['-`table`.`column` DESC'],
            '-`<table>`.`<column>` desc'                                   => ['-`table`.`column` desc'],
            '-`<table name>`.`<column>`'                                   => ['-`table name`.`column`'],
            '-`<table name>`.`<column>` ASC'                               => ['-`table name`.`column` ASC'],
            '-`<table name>`.`<column>` asc'                               => ['-`table name`.`column` desc'],
            '-`<table name>`.`<column>` DESC'                              => ['-`table name`.`column` desc'],
            '-`<table name>`.`<column>` desc'                              => ['-`table name`.`column` desc'],
            '<table>.<column>     asc'                                     => ['table.column     asc'],
            '<table>.<column>   desc ,   <table>.<column>     asc,-column' => ['table.column   desc ,   table.column     asc,-column'],
        ];
    }

    public function groupByProviderOK(): array
    {
        return [
            'column'                => ['column'],
            '`column`'              => ['`column`'],
            'table.column'          => ['table.column'],
            '`table`.column'        => ['table.column'],
            'table.`column`'        => ['table.column'],
            '`table`.`column`'      => ['`table`.`column`'],
            '`table name`.`column`' => ['`table name`.`column`'],
            '`table name`.column'   => ['`table name`.column'],
            'multiple'              => ['`table`.`column`,       table.column, `column`,column'],
        ];
    }

    public function limitProviderOK(): array
    {
        return [
            '<count>'                         => ['10'],
            '<offset>, <count>'               => ['0, 10'],
            '<count> OFFSET <offset>'         => ['10 OFFSET 0'],
            '<count> offset <offset>'         => ['10 offset 0'],
            '<count>   offset       <offset>' => ['10   offset       0'],
            '<offset>   ,    <count>'         => ['0   ,    10'],
        ];
    }

    public function clauseProviderNOK(): array
    {
        return [
            '('                                => ['('],
            '1; SELECT 1;'                     => ['1; SELECT 1;'],
            'COALESCE(column, column, column)' => ['COALESCE(column, column, column)'],
            'column ASC;'                      => ['column ASC;'],
            ';column ASC'                      => [';column ASC'],
            'SLEEP(10)'                        => ['SLEEP(10)'],
            '/!**'                             => ['/!**'],
            'test test.column'                 => ['test test.column'],
            '`test test.column'                => ['`test test.column'],
            'test test`.column'                => ['test test`.column'],
            '`test.column'                     => ['`test.column'],
            'test`.column'                     => ['test`.column'],
            'test test.`column'                => ['test test.`column'],
            'test test.column`'                => ['test test.column`'],
            'test.`column'                     => ['test.`column'],
            'test.column`'                     => ['test.column`'],
            '`test``column`'                   => ['`test``column`'],
            '-`test``column`'                  => ['-`test``column`'],
            '-`test`.`column ASC'              => ['-`test`.`column ASC'],
            '-`test`.-`column`'                => ['-`test`.`-`column`'],
            '-`test`.-column'                  => ['-`test`.`-column'],
        ];
    }
}
