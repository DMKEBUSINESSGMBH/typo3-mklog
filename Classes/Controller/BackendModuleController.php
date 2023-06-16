<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Controller;

use DMK\Mklog\Domain\Model\DevlogEntryDemand;
use DMK\Mklog\Domain\Repository\DevlogEntryRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class BackendModuleController.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class BackendModuleController
{
    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var DevlogEntryRepository
     */
    protected $devlogEntryRepository;

    /**
     * @var StandaloneView
     */
    protected $view;

    public function __construct(
        DevlogEntryRepository $devlogEntryRepository,
        ModuleTemplate $moduleTemplate,
        StandaloneView $view
    ) {
        $this->moduleTemplate = $moduleTemplate;
        $this->devlogEntryRepository = $devlogEntryRepository;
        $this->view = $view;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->view->setTemplateRootPaths(['EXT:mklog/Resources/Private/Templates/']);
        $this->view->setPartialRootPaths(['EXT:mklog/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:mklog/Resources/Private/Layouts/']);
        $this->view->setTemplate('BackendModule');

        $this->view->assign('formData', $request->getParsedBody());

        $this->assignLatestRunsSelectOptions();
        $this->assignSeverityLevels();
        $this->assignLoggedExtensions();
        $this->assignResults($request);

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    public function assignLatestRunsSelectOptions(): void
    {
        $latestRuns = $this->devlogEntryRepository->getLatestRunIds();

        $items = ['' => ''];

        foreach ($latestRuns as $id) {
            $items[$id] = strftime('%d.%m.%y %H:%M:%S', substr($id, 0, 10));
        }

        $this->view->assign('latestRunsSelectOptions', $items);
    }

    public function assignSeverityLevels(): void
    {
        $items = [];
        $items[''] = '';
        foreach (\DMK\Mklog\Utility\SeverityUtility::getItems() as $id => $name) {
            $items[$id] = $id.' - '.ucfirst(strtolower($name));
        }

        $this->view->assign('severitySelectOptions', $items);
    }

    public function assignLoggedExtensions(): void
    {
        $extKeys = $this->devlogEntryRepository->getLoggedExtensions();

        $items = ['' => ''];

        /* @var $item \DMK\Mklog\Domain\Model\DevlogEntry */
        foreach ($extKeys as $extKey) {
            $items[$extKey] = $extKey;
        }

        $this->view->assign('extensionsSelectOptions', $items);
    }

    public function assignResults(ServerRequestInterface $request): void
    {
        $demand = new DevlogEntryDemand();
        $demand->setMaxResults(50);
        $demand->setOrderBy('crdate', 'DESC');

        $severity = $request->getParsedBody()['severity'] ?? null;
        if ($severity) {
            $demand->setSeverity($severity);
        }

        $extension = $request->getParsedBody()['extension'] ?? null;
        if ($extension) {
            $demand->setExtensionWhitelist([$extension]);
        }

        $runId = $request->getParsedBody()['runId'] ?? null;
        if ($runId) {
            $demand->setRunId($runId);
        }

        $term = $request->getParsedBody()['term'] ?? null;
        if ($term) {
            $demand->setTerm($term);
        }

        $this->view->assign('results', $this->devlogEntryRepository->findByDemand($demand));

        $demand->setDoCount(true);
        $this->view->assign('resultsCount', $this->devlogEntryRepository->findByDemandRaw($demand)->fetchOne());
    }
}
