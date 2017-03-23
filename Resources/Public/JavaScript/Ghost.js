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

/**
 * Ghost
 */
define(['jquery', 'TYPO3/CMS/Backend/LayoutModule/DragDrop'], function ($, DragDrop) {
    'use strict';

    var refresh = function (parameters) {
        require(['TYPO3/CMS/Backend/AjaxDataHandler'], function (DataHandler) {
            DataHandler.process(parameters).done(function (result) {
                if (!result.hasErrors) {
                    self.location.reload(true);
                }
            });
        });
    }

    DragDrop.__ajaxAction = DragDrop.ajaxAction;
    DragDrop.ajaxAction = function($droppableElement, $draggableElement, parameters, $copyAction, $pasteAction){
        if ($draggableElement.hasClass('is-ghost')) {
            refresh(parameters);
        } else {
            DragDrop.__ajaxAction($droppableElement, $draggableElement, parameters, $copyAction, $pasteAction);
        }
    }
});
