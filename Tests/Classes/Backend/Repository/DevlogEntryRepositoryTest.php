<?php

/*
 * Copyright notice
 *
 * (c) 2011-2023 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklog" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mklog\Backend\Repository;

/***************************************************************
 * Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Devlog entry repository test.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class DevlogEntryRepositoryTest extends \DMK\Mklog\Tests\BaseTestCase
{
    /**
     * Test the getEmptyModel method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetEmptyModelShouldBeBaseModelWithRightTable()
    {
        $model = $this->callInaccessibleMethod(
            $this->getDevlogEntryRepository(),
            'getEmptyModel'
        );
        self::assertInstanceOf(
            'DMK\\Mklog\\Domain\\Model\\DevlogEntry',
            $model
        );
        self::assertSame(
            'tx_mklog_devlog_entry',
            $model->getTablename()
        );
    }

    /**
     * Test the prepareGenericSearcher method.
     *
     * @group unit
     *
     * @test
     */
    public function testPrepareGenericSearcherShouldBeTheRightSearchdefConfig()
    {
        $repo = $this->getDevlogEntryRepository();
        $searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

        $that = $this; // workaround for php 5.3

        $searcher
            ->expects(self::once())
            ->method('search')
            ->with(
                $this->callback(
                    function ($fields) {
                        return is_array($fields) && empty($fields);
                    }
                ),
                $this->callback(
                    function ($options) use ($that, $repo) {
                        $tablename = $repo->getEmptyModel()->getTableName();
                        $that->assertTrue(is_array($options));

                        $that->assertArrayHasKey('searchdef', $options);
                        $that->assertTrue(is_array($options['searchdef']));

                        $sd = $options['searchdef'];
                        $that->assertArrayHasKey('usealias', $sd);
                        $that->assertSame($sd['usealias'], 1);
                        $that->assertArrayHasKey('basetable', $sd);
                        $that->assertSame($sd['basetable'], $tablename);
                        $that->assertArrayHasKey('basetablealias', $sd);
                        $that->assertSame($sd['basetablealias'], 'DEVLOGENTRY');
                        $that->assertArrayHasKey('wrapperclass', $sd);
                        $that->assertSame($sd['wrapperclass'], get_class($repo->getEmptyModel()));

                        $that->assertArrayHasKey('alias', $sd);
                        $that->assertTrue(is_array($sd['alias']));
                        $that->assertArrayHasKey('DEVLOGENTRY', $sd['alias']);
                        $that->assertTrue(is_array($sd['alias']['DEVLOGENTRY']));
                        $that->assertArrayHasKey('table', $sd['alias']['DEVLOGENTRY']);
                        $that->assertSame($sd['alias']['DEVLOGENTRY']['table'], $tablename);

                        return true;
                    }
                )
            )
            ->will(self::returnValue(new \ArrayObject()));

        self::assertInstanceOf('ArrayObject', $repo->findAll());
    }

    /**
     * Test the prepareGenericSearcher method.
     *
     * @group unit
     *
     * @test
     */
    public function testPrepareGenericSearcherShouldUseCollection()
    {
        $repo = $this->getDevlogEntryRepository();
        $searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

        $that = $this; // workaround for php 5.3
        $searcher
            ->expects(self::once())
            ->method('search')
            ->with(
                $this->callback(
                    function ($fields) {
                        return is_array($fields);
                    }
                ),
                $this->callback(
                    function ($options) use ($that) {
                        $that->assertTrue(is_array($options));

                        $that->assertArrayHasKey('collection', $options);
                        $that->assertEquals(
                            \Sys25\RnBase\Domain\Collection\BaseCollection::class,
                            $options['collection']
                        );

                        return true;
                    }
                )
            )
            ->will(self::returnValue(new \ArrayObject()));

        self::assertInstanceOf('ArrayObject', $repo->findAll());
    }

    /**
     * Test the getLatestRunIds method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetLatestRunIds()
    {
        $repo = $this->getDevlogEntryRepository();
        $searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

        $that = $this; // workaround for php 5.3
        $searcher
            ->expects(self::once())
            ->method('search')
            ->with(
                $this->callback(
                    function ($fields) {
                        return is_array($fields);
                    }
                ),
                $this->callback(
                    function ($options) use ($that) {
                        $that->assertTrue(is_array($options));

                        $that->assertArrayHasKey('collection', $options);
                        $that->assertFalse($options['collection']);

                        $that->assertArrayHasKey('what', $options);
                        $that->assertEquals(
                            'DEVLOGENTRY.run_id',
                            $options['what']
                        );

                        $that->assertArrayHasKey('groupby', $options);
                        $that->assertEquals(
                            'DEVLOGENTRY.run_id',
                            $options['groupby']
                        );

                        $that->assertArrayHasKey('orderby', $options);
                        $that->assertCount(1, $options['orderby']);
                        $that->assertEquals(
                            'DESC',
                            $options['orderby']['DEVLOGENTRY.run_id']
                        );

                        $that->assertArrayHasKey('limit', $options);
                        $that->assertEquals(
                            57,
                            $options['limit']
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    [
                        ['run_id' => '14780947042869'],
                        ['run_id' => '14780821110468'],
                        ['run_id' => '1478080061308'],
                    ]
                )
            );

        $ids = $repo->getLatestRunIds(57);

        $this->assertTrue(is_array($ids));
        $this->assertCount(3, $ids);
        $this->assertEquals('14780947042869', $ids[0]);
        $this->assertEquals('14780821110468', $ids[1]);
        $this->assertEquals('1478080061308', $ids[2]);
    }

    /**
     * Test the getLoggedExtensions method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetLoggedExtensions()
    {
        $repo = $this->getDevlogEntryRepository();
        $searcher = $this->callInaccessibleMethod($repo, 'getSearcher');

        $that = $this; // workaround for php 5.3
        $searcher
            ->expects(self::once())
            ->method('search')
            ->with(
                $this->callback(
                    function ($fields) {
                        return is_array($fields);
                    }
                ),
                $this->callback(
                    function ($options) use ($that) {
                        $that->assertTrue(is_array($options));

                        $that->assertCount(6, $options);

                        $that->assertArrayHasKey('what', $options);
                        $that->assertEquals(
                            'DEVLOGENTRY.ext_key',
                            $options['what']
                        );

                        $that->assertArrayHasKey('groupby', $options);
                        $that->assertEquals(
                            'DEVLOGENTRY.ext_key',
                            $options['groupby']
                        );

                        $that->assertArrayHasKey('orderby', $options);
                        $that->assertCount(1, $options['orderby']);
                        $that->assertEquals(
                            'DESC',
                            $options['orderby']['DEVLOGENTRY.ext_key']
                        );

                        $that->assertArrayHasKey('collection', $options);
                        $that->assertFalse($options['collection']);

                        $that->assertArrayHasKey('enablefieldsoff', $options);
                        $that->assertArrayHasKey('searchdef', $options);

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    [
                        ['ext_key' => 'rn_base'],
                        ['ext_key' => 'mklog'],
                        ['ext_key' => 'mkpostman'],
                    ]
                )
            );

        $keys = $repo->getLoggedExtensions();

        $this->assertTrue(is_array($keys));
        $this->assertCount(3, $keys);
        $this->assertEquals('rn_base', $keys[0]);
        $this->assertEquals('mklog', $keys[1]);
        $this->assertEquals('mkpostman', $keys[2]);
    }

    /**
     * Test the optimize method.
     *
     * @group unit
     *
     * @test
     */
    public function testOptimize()
    {
        self::markTestIncomplete();
    }

    /**
     * Creates the repo mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|DMK\Mklog\Backend\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        \tx_rnbase::load('tx_rnbase_util_SearchGeneric');
        $searcher = $this->getMock(
            'tx_rnbase_util_SearchGeneric',
            ['search']
        );

        $repo = $this->getMock(
            'DMK\\Mklog\\Backend\\Repository\\DevlogEntryRepository',
            ['getSearcher', 'getConnection', 'getEmptyModel']
        );

        $repo
            ->expects(self::any())
            ->method('getEmptyModel')
            ->will(self::returnValue($this->getModel(null, 'DMK\\Mklog\\Backend\\Model\\DevlogEntry')));
        $repo
            ->expects(self::any())
            ->method('getSearcher')
            ->will(self::returnValue($searcher));
        $repo
            ->expects(self::any())
            ->method('getConnection')
            ->will(self::returnValue($this->getDatabaseConnection()));

        return $repo;
    }
}
