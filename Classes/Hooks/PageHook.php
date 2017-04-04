<?php

namespace R3H6\GhostContent\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use R3H6\GhostContent\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * hook to display the evaluation results in the page module
 *
 * Class pageHook
 * @package Clickstorm\CsSeo\Hook
 */
class PageHook
{
    const EXT_KEY = 'ghost_content';

    /**
     * @var \R3H6\GhostContent\Configuration\ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;

    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * Load the necessary css
     *
     * This will only be done when the referenced record is available
     *
     * @return void
     */
    protected function loadCss()
    {
        $addCssFile = '../typo3conf/ext/ghost_content/Resources/Public/StyleSheet/Ghost.css';
        $this->getPageRenderer()->addCssFile($addCssFile);
    }

    /**
     * Load the necessary javascript
     *
     * This will only be done when the referenced record is available
     *
     * @return void
     */
    protected function loadJavascript()
    {
        $addRequireJsFile = '../typo3conf/ext/ghost_content/Resources/Public/JavaScript/Ghost.js';

        $this->getPageRenderer()->loadRequireJsModule($addRequireJsFile);
    }


    /**
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        if (!isset($this->pageRenderer)) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }
        return $this->pageRenderer;
    }

    /**
     * Add sys_notes as additional content to the footer of the page module
     *
     * @param array $params
     * @param PageLayoutController $parentObject
     * @return string
     */
    public function render(array $params, PageLayoutController $parentObject)
    {
        if ((int)$parentObject->MOD_SETTINGS['function'] === 1) {

            $this->loadCss();
            $this->loadJavascript();

            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($parentObject);
            // \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($GLOBALS['BE_USER']);

            $validColPos = GeneralUtility::intExplode(',', $parentObject->activeColPosList, true);
            $validColPos = array_merge($validColPos, GeneralUtility::intExplode(',', $this->extensionConfiguration->get('whiteList'), true));

            $ghosts = $this->findGhosts($parentObject->pageinfo['uid'], $parentObject->current_sys_language, $validColPos);
            foreach ($ghosts as &$ghost) {
                $urlParameters = [
                    'edit' => [
                        'tt_content' => [
                            $ghost['uid'] => 'edit',
                        ],
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI') . '#element-tt_content-' . $ghost['uid'],
                ];
                $ghost['openAction'] = BackendUtility::getModuleUrl('record_edit', $urlParameters) . '#element-tt_content-' . $row['uid'];
                $ghost['deleteAction'] = BackendUtility::getLinkToDataHandlerAction('&cmd[tt_content]['.$ghost['uid'].'][delete]=1');
                $ghost['uniqId'] = StringUtility::getUniqueId();
            }

            $view = $this->getView();
            $view->assign('ghosts', $ghosts);

            return $view->render();
        }
    }

    /**
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function getView()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setFormat('html');
        $view->getRequest()->setControllerExtensionName(self::EXT_KEY);

        $absoluteResourcesPath = ExtensionManagementUtility::extPath(self::EXT_KEY) . 'Resources/';
        $layoutPaths = [$absoluteResourcesPath . 'Private/Layouts/'];
        $partialPaths = [$absoluteResourcesPath . 'Private/Partials/'];

        $view->setLayoutRootPaths($layoutPaths);
        $view->setPartialRootPaths($partialPaths);
        $view->setTemplatePathAndFilename($absoluteResourcesPath . 'Private/Templates/PageHook.html');

        return $view;
    }

    protected function findGhosts($pageUid, $language, array $validColPos)
    {
        $where = 'pid='.(int) $pageUid;
        $where .= ' AND colPos NOT IN('.join(',', $validColPos).')';
        $where .= ' AND deleted=0'; // Only enable field we have to take care off in be mode!
        $where .= ' AND sys_language_uid='.$language;

        $records =  $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tt_content', $where);

        if ($GLOBALS['BE_USER']->workspace) {
            foreach ($records as &$record) {
                BackendUtility::workspaceOL('tt_content', $record);
                // $this->pageRepository->init(true);
                // $this->pageRepository->versioningPreview = true;
                // $this->pageRepository->versioningWorkspaceId = $GLOBALS['BE_USER']->workspace;
                // $this->pageRepository->versionOL('tt_content', $record);
                // if (is_array($record)) {
                //     $records[$key] = $record;
                // }
            }
        }

        return array_filter($records);
    }

    /**
     * @return TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
