<?php

/*
 * Copyright notice
 *
 * (c) 2011-2022 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Utility;

/**
 * MK Log Severity Utility.
 *
 * Some code is taken from \TYPO3\CMS\Core\Log\LogLevel for backward compatibility
 *
 * @author  Ingo Renner
 * @author  Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
final class SeverityUtility
{
    /**
     * Emergency: system is unusable.
     *
     * You'd likely not be able to reach the system. You better have an SLA in
     * place when this happens.
     *
     * @var int
     */
    public const EMERGENCY = 0;
    /**
     * Alert: action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc.
     *
     * @var int
     */
    public const ALERT = 1;
    /**
     * Critical: critical conditions.
     *
     * Example: unexpected exception.
     *
     * @var int
     */
    public const CRITICAL = 2;
    /**
     * Error: error conditions.
     *
     * Example: Runtime error.
     *
     * @var int
     */
    public const ERROR = 3;
    /**
     * Warning: warning conditions.
     *
     * Examples: Use of deprecated APIs, undesirable things that are not
     * necessarily wrong.
     *
     * @var int
     */
    public const WARNING = 4;
    /**
     * Notice: normal but significant condition.
     *
     * Example: things you should have a look at, nothing to worry about though.
     *
     * @var int
     */
    public const NOTICE = 5;
    /**
     * Informational: informational messages.
     *
     * Examples: User logs in, SQL logs.
     *
     * @var int
     */
    public const INFO = 6;
    /**
     * Debug: debug-level messages.
     *
     * Example: Detailed status information.
     *
     * @var int
     */
    public const DEBUG = 7;

    /**
     * Reverse look up of log level to level name.
     *
     * @var array
     */
    private static $levels = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT => 'ALERT',
        self::CRITICAL => 'CRITICAL',
        self::ERROR => 'ERROR',
        self::WARNING => 'WARNING',
        self::NOTICE => 'NOTICE',
        self::INFO => 'INFO',
        self::DEBUG => 'DEBUG',
    ];

    /**
     * Resolves the name of a log level.
     *
     * @param int $level log level
     *
     * @return string log level name
     */
    public static function getName($level)
    {
        return self::$levels[$level];
    }

    /**
     * Returns a log level as a Psr\Log\Level-constant.
     *
     * @param int $level log level
     *
     * @return string log level name
     */
    public static function getPsrLevelConstant($level)
    {
        switch ($level) {
            case self::EMERGENCY:
                return \Psr\Log\LogLevel::EMERGENCY;
            case self::ALERT:
                return \Psr\Log\LogLevel::ALERT;
            case self::CRITICAL:
                return \Psr\Log\LogLevel::CRITICAL;
            case self::ERROR:
                return \Psr\Log\LogLevel::ERROR;
            case self::WARNING:
                return \Psr\Log\LogLevel::WARNING;
            case self::NOTICE:
                return \Psr\Log\LogLevel::NOTICE;
            case self::INFO:
                return \Psr\Log\LogLevel::INFO;
            case self::DEBUG:
            default:
                return \Psr\Log\LogLevel::DEBUG;
        }
    }

    /**
     * Returns all levels as array.
     *
     * @return array
     */
    public static function getItems()
    {
        return self::$levels;
    }

    /**
     * Returns all levels as array.
     *
     * @return array
     */
    public static function getTcaItems()
    {
        $levels = [];
        $levels[] = ['', ''];

        foreach (self::$levels as $id => $name) {
            $levels[] = [$id, $name];
        }

        return $levels;
    }
}
