<?php

namespace R3H6\GhostContent\Hooks;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

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

/**
 * Hook to display not assigned content.
 */
class PageHook
{
    const EXT_KEY = 'ghost_content';
    const MODUL_FUNCTION_COLUMNS = 1;

    /**
     * @var \R3H6\GhostContent\Configuration\ExtensionConfiguration
     */
    protected $extensionConfiguration;

    protected $pageRenderer;

    public function __construct()
    {
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
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
        $addRequireJsFile = 'TYPO3/CMS/GhostContent/Ghost';

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
        if ((int)$parentObject->MOD_SETTINGS['function'] === self::MODUL_FUNCTION_COLUMNS) {


            $validColPos = GeneralUtility::intExplode(',', $parentObject->activeColPosList, true);
            $validColPos = array_merge($validColPos, GeneralUtility::intExplode(',', $this->extensionConfiguration->get('whiteList'), true));

            $ghosts = $this->findGhosts($parentObject->pageinfo['uid'], $validColPos, $parentObject->current_sys_language);

            if (empty($ghosts)) {
                return;
            }

            $this->loadCss();
            $this->loadJavascript();

            foreach ($ghosts as &$ghost) {
                $urlParameters = [
                    'edit' => [
                        'tt_content' => [
                            $ghost['uid'] => 'edit',
                        ],
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
                ];
                $ghost['openAction'] = BackendUtility::getModuleUrl('record_edit', $urlParameters);
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
        $view->setTemplatePathAndFilename($absoluteResourcesPath . 'Private/Templates/PageHook/Render.html');

        return $view;
    }

    protected function findGhosts($pageUid, array $validColPos, $languageUid)
    {
        $where = 'pid='.(int) $pageUid;
        $where .= ' AND colPos NOT IN('.join(',', $validColPos).')';
        $where .= ' AND sys_language_uid='.(int) $languageUid;
        $where .= ' AND deleted=0'; // Only enable field we have to take care off in be mode!
        return $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tt_content', $where);
    }

    /**
     * @return TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
