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
 *
 * Overwrite default DragDrop ajax behavior.
 * After dropping a ghost-element we have to refresh the page in order to display it.
 */
define(['jquery',
        'TYPO3/CMS/Backend/LayoutModule/DragDrop',
        'TYPO3/CMS/Backend/Icons'
    ], function ($, DragDrop, Icons) {
    'use strict';

    var refresh = function ($draggableElement, parameters) {
        require(['TYPO3/CMS/Backend/AjaxDataHandler'], function (DataHandler) {

            Icons.getIcon('spinner-circle-light', Icons.sizes.small).done(function(icon) {
                $('[class*="icon-mimetypes"]', $draggableElement).replaceWith(icon);
            });

            DataHandler.process(parameters).done(function (result) {
                if (!result.hasErrors) {
                    self.location.reload(true);
                }
            });
        });
    }

    DragDrop.__ajaxAction = DragDrop.ajaxAction;
    DragDrop.ajaxAction = function($droppableElement, $draggableElement, parameters, $copyAction, $pasteAction){

        if ($draggableElement.hasClass('js-ghost')) {
            refresh($draggableElement, parameters);
        } else {
            DragDrop.__ajaxAction($droppableElement, $draggableElement, parameters, $copyAction, $pasteAction);
        }
    }
});
