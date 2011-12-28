/**
 * Copyright 2011 Clearspring Technologies
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
* This file includes utils related to coloring tickets, generic to any particular mode or view.
*/

const MAX_COLOR_NUM = 19;

var colorMap = new Array();
var nextColorToUse = 1;
var numColorsUsed = 1;

/**
* Sets correct color classes on a ticket obejct.
*/
function setTicketColorClasses(ticketObj, colorNum) {
    var ticketColorClass = "ticket-coloring-" + colorNum;
    var ticketColorAltClass = "ticket-coloring-alt-" + colorNum;
    ticketObj.removeClass("ticket-coloring-dflt");
    ticketObj.find(".header").removeClass("ticket-coloring-alt-dflt");
    ticketObj.find(".footer").removeClass("ticket-coloring-alt-dflt");
    var i = 1;
    for (i = 1; i <= MAX_COLOR_NUM; i++) {
        if (ticketObj.hasClass("ticket-coloring-" + i)) {
            ticketObj.removeClass("ticket-coloring-" + i);
            ticketObj.find(".header").removeClass("ticket-coloring-alt-" + i);
            ticketObj.find(".footer").removeClass("ticket-coloring-alt-" + i);
        }
    }
    ticketObj.addClass(ticketColorClass);
    ticketObj.find(".header").addClass(ticketColorAltClass);
    ticketObj.find(".footer").addClass(ticketColorAltClass);
}

/**
* Colors all of the tickets matching a given selector by a specific attribute.  This will add to any existing set of coloring that has been done,
* unless explicitly told not to.  IOW, if you did it before, and got three colors assigned to each fo three values for the attribute, and then 
* run it again, it will start with the 4th color the next time it sees a new value for that attribute.  
*
* @param colorByAttr which ticket attribute to color by, or "uniform" if all should have the same colors
* @param resetColorSet Whether or not to start the coloring over from scratch
*/
function colorTickets(ticketSelector, byAttribute, resetColorSet) {
    dbglog("Coloring tickets selected with '" + ticketSelector + "', by attribute " + byAttribute);
    if (resetColorSet) {
        resetColoring();
    }
    $(ticketSelector).each(function() {
        dbglog("Checking coloring of ticket '" + $(this).attr("ticket-id"));
        var coloredAttrVal = $(this).attr(byAttribute);
        if (coloredAttrVal) {

            // For this value of the attribute, make sure we have a color identified
            if (colorMap[coloredAttrVal] == undefined) {
                dbglog("Assigning color " + nextColorToUse + " to items with attribute value '" + coloredAttrVal + "'");
                colorMap[coloredAttrVal] = nextColorToUse;
                if (nextColorToUse < MAX_COLOR_NUM) {
                    nextColorToUse++;
                } else {
                    dbglog("No more colors available...will use last color again (" + nextColorToUse + ")");
                }
            }

            // Now color the ticket appropriately
            setTicketColorClasses($(this), colorMap[coloredAttrVal]);
        }
    });
}

/**
* {
* onItemToggleFuncName: 
* onItemExclusiveToggleFuncName:
* }
*/

function onKeyItemToggle(val, onItemToggleFuncName) {
    var currHidingState = ($('.colorkey-item[attrval="' + val + '"]').attr("hiding-this-val") == "true");
    var newHidingState = !currHidingState;
    dbglog("Key item '" + val + "' clicked, new hiding state is '" + newHidingState + "', handler is '" + onItemToggleFuncName + "'");

    if (newHidingState) {
        $('.colorkey-item[attrval="' + val + '"]').addClass("inactive");
        $('.colorkey-item[attrval="' + val + '"]').attr("hiding-this-val", "true");

        $('.colorkey-item[attrval!="' + val + '"]').attr("showing-exclusively", "false");
    } else {
        $('.colorkey-item[attrval="' + val + '"]').removeClass("inactive");
        $('.colorkey-item[attrval="' + val + '"]').attr("hiding-this-val", "false");

        $('.colorkey-item[attrval!="' + val + '"]').attr("showing-exclusively", "false");
    }

    // Fix up our key titles
    updateKeyItemTitles();

    // Call the handler
    var js = onItemToggleFuncName + "('" + val + "', '" + !newHidingState + "')";
    dbglog("Invoking callback: " + js);
    eval(js);
}

function onKeyItemExclusiveToggle(val, onItemExclusiveToggleFuncName) {
    var currExclusiveShowState = ($('.colorkey-item[attrval="' + val + '"]').attr("showing-exclusively") == "true");
    var newExclusiveShowState = !currExclusiveShowState;
    dbglog("Key item '" + val + "' clicked for exclusive, new show-exclusive state is '" + newExclusiveShowState + "', handler is '" + onItemExclusiveToggleFuncName + "'");

    if (newExclusiveShowState) {

        // This one should be fully shown, all others should be hidden
        $('.colorkey-item[attrval="' + val + '"]').removeClass("inactive");
        $('.colorkey-item[attrval="' + val + '"]').attr("showing-exclusively", "true");
        $('.colorkey-item[attrval="' + val + '"]').attr("hiding-this-val", "false");

        $('.colorkey-item[attrval!="' + val + '"]').addClass("inactive");
        $('.colorkey-item[attrval!="' + val + '"]').attr("hiding-this-val", "true");
        $('.colorkey-item[attrval!="' + val + '"]').attr("showing-exclusively", "false");
    } else {

        // This one should be fully shown, as should all others
        $('.colorkey-item[attrval="' + val + '"]').attr("showing-exclusively", "false");
        $('.colorkey-item[attrval="' + val + '"]').removeClass("inactive");
        $('.colorkey-item[attrval="' + val + '"]').attr("hiding-this-val", "false");

        $('.colorkey-item[attrval!="' + val + '"]').removeClass("inactive");
        $('.colorkey-item[attrval!="' + val + '"]').attr("hiding-this-val", "false");
        $('.colorkey-item[attrval!="' + val + '"]').attr("showing-exclusively", "false");
    }

    // Fix up our key titles
    updateKeyItemTitles();

    // Call the handler
    var js = onItemExclusiveToggleFuncName + "('" + val + "', '" + newExclusiveShowState + "')";
    dbglog("Invoking callback: " + js);
    eval(js);
}

/**
* Updates the title attrs of all key items, based on their current state.
*/
function updateKeyItemTitles() {
    $('.colorkey-item-exclusivetoggle').each(function() {
        var attrVal = $(this).parent().attr("attrval");
        var showingVal = $(this).parent().attr("showing-exclusively");
        if (showingVal == "true") {
            $(this).attr("title", "Show everything");
        } else {
            $(this).attr("title", "Show only " + attrVal + "");
        }
    });
    $('.colorkey-item-maintoggle').each(function() {
        var attrVal = $(this).parent().attr("attrval");
        var showingVal = $(this).parent().attr("hiding-this-val");
        if (showingVal == "true") {
            $(this).attr("title", "Show " + attrVal + "");
        } else {
            $(this).attr("title", "Hide " + attrVal + "");
        }
    });
}

/**
* Updates the color key on the page using the currently mapped values.  The key is added to the specified container, if its present.  
* If there are no mapped colors, the container is not visible.  Toggling options are provided based on whether or not toggle handlers 
* are provided.
*
* @param keyContainerId The ID of the DOM element which wil contain the color key
* @param keyItemToggleHandlers The set of handlers for key item toggling options (see help elsewhere) 
*/
function updateColorKey(keyContainerId, keyItemToggleHandlers) {
    if (nextColorToUse > 1) {
        var key = '<div id="colorkey" style="float:left; margin:3px 5px 0 0">Key:</div>';
        key = key + '<div style="padding-top: 3px; padding-bottom: 3px; border: 1px solid #a0a0a0; float:left;" id="keywrap">';
        for (val in colorMap) {

            // Build up the item, with click handlers as needed
            var item = '<div class="colorkey-item ticket-coloring-alt-' + colorMap[val] + '" attrval="' + val + '" hiding-this-val="false" >';
            item += '<span ';
            var extraClass = "";
            if ((keyItemToggleHandlers != null) && (keyItemToggleHandlers.onItemToggleFuncName != null)) {

                // Make clickable for the basic item toggle
                item += ' onclick="';
                var jsCall = "onKeyItemToggle('" + val + "', '" + keyItemToggleHandlers.onItemToggleFuncName + "');";
                item += jsCall + '"';
                item += ' title="Toggle ' + val + '" ';
                extraClass = "colorkey-item-maintoggle";
            }
            item += ' class="ticket-coloring ticket-coloring-' + colorMap[val] + ' colorkey-item-inner ' + extraClass + '">';
            item += val;
            item += '</span>';
            if (keyItemToggleHandlers.onItemExclusiveToggleFuncName != null) {

                // Make secondary clickable area for exclusive toggling
                item += '<span ';
                item += 'onclick="';
                var jsCall = "onKeyItemExclusiveToggle('" + val + "', '" + keyItemToggleHandlers.onItemExclusiveToggleFuncName + "');";
                item += jsCall + '" title="Toggle others" ';
                item += ' class="ticket-coloring-alt-' + colorMap[val] + ' colorkey-item-inner colorkey-item-exclusivetoggle">';
                item += "*";
                item += '</span>';
            }
            item += '</div>';
            key += item;
        }
        key = key + '</div><div class="clearer"></div>';
        $("#" + keyContainerId).empty();
        $("#" + keyContainerId).append(key);
        $("#" + keyContainerId).show();

        // Finally, just set up the title correctly
        updateKeyItemTitles();
    } else {
        eraseColorKey(keyContainerId);
    }
}

/**
* Erases the color key.
*/
function eraseColorKey(keyContainerId) {
    $("#" + keyContainerId).empty();
    $("#" + keyContainerId).hide();
}

/**
* Removes the color key, and reset the coloring system for another run from scratch.
*/
function resetColoring(keyContainerId) {
    colorMap = new Array();
    nextColorToUse = 1;
    eraseColorKey();
}


