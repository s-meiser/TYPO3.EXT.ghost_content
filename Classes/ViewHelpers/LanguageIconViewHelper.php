<?php

namespace R3H6\GhostContent\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use R3H6\GhostContent\Wrapper\RecordList;

/**
 * LanguageIconViewHelper
 */
class LanguageIconViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Disable escaping of child nodes' output
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Disable escaping of this node's output
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Arguments initialization.
     */
    public function initializeArguments()
    {
        $this->registerArgument('record', 'array', 'Content record', true);
        $this->registerArgument('addAsAdditionalText', 'boolean', 'addAsAdditionalText', false, false);
    }

    public function render()
    {
        $record = $this->arguments['record'];
        $languageUid = (string) $record['sys_language_uid'];

        /** @var \R3H6\GhostContent\Wrapper\RecordList $dbList */
        $dbList = GeneralUtility::makeInstance(RecordList::class);
        $dbList->setId($record['pid']);
        $dbList->initializeLanguages();

        return $dbList->languageFlag($languageUid, $this->arguments['addAsAdditionalText']);
    }
}
