<?php

namespace R3H6\GhostContent\Wrapper;

use TYPO3\CMS\Backend\RecordList\AbstractRecordList;

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

/**
 * RecordList
 */
class RecordList extends AbstractRecordList
{
    public function setId($id)
    {
        $this->id = (int) $id;
    }
}