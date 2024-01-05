<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class BackendModuleController.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
#[Controller]
class BackendModuleController
{
    protected ModuleTemplate $view;
    protected DevlogEntryRepository $devlogEntryRepository;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(
        DevlogEntryRepository $devlogEntryRepository,
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->devlogEntryRepository = $devlogEntryRepository;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $pageId = (int) ($request->getQueryParams()['id'] ?? $request->getParsedBody()['id'] ?? 0);
        $this->view = $this->moduleTemplateFactory->create($request);
        $this->view->assign('formData', $request->getParsedBody());

        $this->assignSeverityLevels();
        $this->assignLoggedExtensions();
        $this->assignResults($request);
        $this->view->assignMultiple([
            'pageUid' => $pageId,
        ]);

        return $this->view->renderResponse('BackendModule');
    }

    protected function assignSeverityLevels(): void
    {
        $items = ['' => ''];
        foreach (\DMK\Mklog\Utility\SeverityUtility::getItems() as $id => $name) {
            $items[(string) $id] = $id.' - '.ucfirst(strtolower($name));
        }

        $this->view->assign('severitySelectOptions', $items);
    }

    protected function assignLoggedExtensions(): void
    {
        $extKeys = $this->devlogEntryRepository->getLoggedExtensions();

        $items = ['' => ''];

        /* @var $item \DMK\Mklog\Domain\Model\DevlogEntry */
        foreach ($extKeys as $extKey) {
            $items[$extKey] = $extKey;
        }

        $this->view->assign('extensionsSelectOptions', $items);
    }

    protected function assignResults(ServerRequestInterface $request): void
    {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $demand = new DevlogEntryDemand();
        $demand->setMaxResults(10000);
        $demand->setOrderBy('crdate', 'DESC');

        $severity = $parsedBody['severity'] ?? $queryParams['severity'] ?? null;
        if (MathUtility::canBeInterpretedAsInteger($severity)) {
            $demand->setSeverity($severity);
        }

        $extension = $parsedBody['extension'] ?? $queryParams['extension'] ?? null;
        if ($extension) {
            $demand->setExtensionWhitelist([$extension]);
        }

        $runId = $parsedBody['runId'] ?? $queryParams['runId'] ?? null;
        if ($runId) {
            $demand->setRunId($runId);
        }

        $term = $parsedBody['term'] ?? $queryParams['term'] ?? null;
        if ($term) {
            $demand->setTerm($term);
        }

        $pageId = $parsedBody['id'] ?? $queryParams['id'] ?? 0;
        if ($pageId) {
            $demand->setPid((int) $pageId);
        }

        $results = $this->devlogEntryRepository->findByDemand($demand);
        $this->view->assign('results', $results);

        $currentPage = $parsedBody['currentPage'] ?? $queryParams['currentPage'] ?? 1;
        $itemsPerPage = $parsedBody['itemsPerPage'] ?? $queryParams['itemsPerPage'] ?? 10;
        $this->assignItemPageOptions();
        $this->assignPagination($results, $currentPage, $itemsPerPage);

        $this->view->assignMultiple([
            'resultsCount' => count($results),
        ]);
    }

    protected function assignPagination(array $results, int $currentPage = 1, $itemsPerPage = 10): void
    {
        $paginator = new ArrayPaginator(
            $results,
            $currentPage,
            $itemsPerPage
        );
        $pagination = new SimplePagination(
            $paginator
        );

        $this->view->assignMultiple([
            'pagination' => $pagination,
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }

    protected function assignItemPageOptions(): void
    {
        $items = [
            10 => 10,
            25 => 25,
            50 => 50,
            100 => 100,
        ];

        $this->view->assignMultiple([
            'itemPageOptions' => $items,
        ]);
    }
}
