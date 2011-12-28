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

function populateNextStorylane() {

    // Take the next two stories to be populated, do em, and queue up again when the second is done 
    var story1 = storiesToBePopulated.shift();
    if (story1 != null) {
        dbglog("Popped story off of queue...story id " + story1.storyId);
        populateSwimlaneTickets(story1.milestone, story1.pipeline, story1.set, story1.storyId, function() {
            setTimeout(function() {
                populateNextStorylane();
            }, 0);
        });
    }
}

/**
* Make all groups of tickets, and the head ticket, the same height
*/
function reHeightSwimlane(set, headTicketId) {
    var targetSwimlane = '.storylane-row[set="' + set + '"]';
    dbglog("Updating height of columns in swimlane (swimlane is " + targetSwimlane + ")");
    $(targetSwimlane + ' .storylane-ticketcell[story-id="' + headTicketId + '"], ' + targetSwimlane + ' .storylane-storycell[story-id="' + headTicketId + '"]').equalHeights(100,10000);
}

function populateSwimlaneTickets(milestone, pipeline, set, headTicketId, callback) {
    dbglog("Populating swimlane tickets: " + pipeline + " / " + milestone + ", " + set + " set, head story " + headTicketId);
    var targetSwimlane = '.storylane-row[set="' + set + '"]';
    var targetTicketArea = targetSwimlane + ' .storylane-tickets[story-id="' + headTicketId + '"]';
    setAsLoading(targetTicketArea);

    // Set up the ajax call...
    var ticketUrl = "get.php";
    ticketUrl += "?a=populateSwimlane"
    ticketUrl += "&display=" + "cards";
    ticketUrl += "&milestone=" + milestone;
    ticketUrl += "&pipeline=" + pipeline;
    ticketUrl += "&set=" + set;
    ticketUrl += "&headid=" + headTicketId;
    ticketUrl += "&external=" + ((currFilters.includeExternal == "yes") ? "true" : "false");
    ticketUrl += "&completed=" + ((currFilters.includeCompleted == "yes") ? "true" : "false");
    
    // Make the call and respond
    $.ajax({
        url: ticketUrl,
        context: document.body,
        dataType: "html",
        success: function(data) {
    
            // Populate the lane
            $(targetTicketArea).empty();
            $(targetTicketArea).append(data);
            
            // Hookup events on all cards within the lane
            hookupCardEvents(targetSwimlane + " .ticket", onTicketEdited);
            
            // Set up the height of the swimlane correctly
            reHeightSwimlane(set, headTicketId);
            
            // Allow tickets to be dropped into other ticket groups
            dbglog("Making ticket cells droppable...");
            $(targetTicketArea + ' .storylane-ticketcell.storylane-ticketcell-phase').droppable({
                accept: function(draggable) {
                    return true;
                    /*
                    if (draggable.attr("ticket-blocking").indexOf($(this).attr("story-id")) >= 0)
                        return true;
                    else 
                        return false;
                    */
                },
                tolerance: 'pointer',
                drop: function(evt, ui) {

                    // Ticket was dropped into a phase cell...which one, and for which storylane?
                    var helper = ui.helper;
                    var ticket = ui.draggable;
                    var ticketId = ticket.attr("ticket-id");
                    var currPhase = ticket.parents(".storylane-ticketcell").attr("phase");
                    var newPhase = $(this).attr("phase");
                    var currStoryId = ticket.parents(".storylane-ticketcell").attr("story-id");
                    var newStoryId = $(this).attr("story-id");
                    dbglog("Attempting to drop ticket " + ticketId + ":");
                    dbglog("    phase " + currPhase + " -> " + newPhase);
                    dbglog("    story " + currStoryId + " -> " + newStoryId);

                    $(this).removeClass("droptarget-middrag");

                    // Is this ticket changing parent stories?
                    if (newStoryId != currStoryId) {
                        
                        // Yep...
                        if (newStoryId != "0" && currStoryId != "0") {
                            
                            // changing dependency from one story to another
                            dbglog("   this is a story change in ticket parent...");
                            var msg = "You are changing the parent story of this ticket from " + currStoryId + " to " + newStoryId + ". Is that really what you want to do?";
                            if (confirm(msg)) {
                                dbglog("   changing ticket parent");
                                
                                // change ticket parent, put it in the new swimlane row
                                changeTicketSwimlaneAndPhase(ticketId, currStoryId, newStoryId, currPhase, newPhase);
                                reHeightSwimlane(set, currStoryId);
                                hookupCardEvents('.ticket[ticket-id="' + ticketId + '"]', onTicketEdited);
                            } else {
                                dbglog("   nope, user cancelled");
                                helper.trigger("mouseup");
                            }
                        } else if (newStoryId != "0" && currStoryId == "0") {

                            // Making a ticket a dependency of another
                            dbglog("   making the ticket a blocker...");
                            var msg = "You are making this ticket a dependency of " + newStoryId + ". Is that really what you want to do?";
                            if (confirm(msg)) {
                                dbglog("   adding as blocker...");
                                changeTicketSwimlaneAndPhase(ticketId, currStoryId, newStoryId, currPhase, newPhase);
                                reHeightSwimlane(set, currStoryId);
                                hookupCardEvents('.ticket[ticket-id="' + ticketId + '"]', onTicketEdited);
                            } else {
                                dbglog("   nope, user cancelled");
                                helper.trigger("mouseup");
                            }
                        } else if (newStoryId == "0" && currStoryId != "0") {
                            
                            // Making a ticket NOT a dependency of another
                            dbglog("   removing the ticket as a blocker...");
                            var msg = "This ticket will NO LONGER be a dependency of " + currStoryId + ". Is that really what you want to do?";
                            if (confirm(msg)) {
                                dbglog("   adding as blocker...");
                                changeTicketSwimlaneAndPhase(ticketId, currStoryId, newStoryId, currPhase, newPhase);
                                reHeightSwimlane(set, currStoryId);
                                hookupCardEvents('.ticket[ticket-id="' + ticketId + '"]', onTicketEdited);
                            } else {
                                dbglog("   nope, user cancelled");
                                helper.trigger("mouseup");
                            }
                        }
                    } else {
                        
                        // Nope, just a simple phase change

                        // Just don't re-drop it into the same phase its already in
                        if (currPhase == newPhase) {
                            dbglog("   nope, not allowed (smae phase)");
                            helper.trigger("mouseup");
                        } else {
                            changeTicketPhase(ticketId, newPhase, true, '.storylane-ticketcell[phase="' + newPhase + '"][story-id="' + newStoryId + '"]');
                            reHeightSwimlane(set, currStoryId);
                            hookupCardEvents('.ticket[ticket-id="' + ticketId + '"]', onTicketEdited);
                        }
                    }
                },
                over: function(evt, ui) {
                    var ticket = ui.draggable;
                    dbglog("Ticket " + ticket.attr("ticket-id") + " dragging over...");
                    $(this).addClass("droptarget-middrag");
                },
                out: function(evt, ui) {
                    var ticket = ui.draggable;
                    dbglog("Ticket " + ticket.attr("ticket-id") + " dragged out...");
                    $(this).removeClass("droptarget-middrag");
                }
            });
            
            // Update the colors of all tickets in the swimlane
            colorSwimlaneTickets(set, headTicketId);

            // Update the swimlane summary
            updateSwimlaneProgressMsg(set, headTicketId);
            
            // If there's a callback, call it
            if (callback) callback();
            
        },
        error: function(xhr, status) {
            alert("Unable to load tickets: " + status);
        },
        complete: function() {
        }
    });
    
}

function updateSwimlaneProgressMsg(set, headTicketId) {
    
    // Count up the total as well as the complete tickets
    var laneSelector = '.storylane-row[set="' + set + '"][story-id="' + headTicketId + '"]';
    var numTotal = $(laneSelector + " .storylane-tickets .ticket").length;
    var numComplete = $(laneSelector + ' .ticket[status="closed"]').length;
    $(laneSelector + " #storycell-header-progress").text(numComplete + "/" + numTotal);
    $(laneSelector + " #storycell-header-progress").attr("title", numComplete + " out of " + numTotal + " sub-tickets completed");
}

// from one to another, in the specified new phase, leaving other dependencies alone, and updating the ui
function changeTicketSwimlaneAndPhase(ticketId, currStoryId, newStoryId, oldPhase, newPhase) {
    
    // Remove the ticket from the current column, add it to the same phase in the target story's lane
    var ticket = $('.ticket[ticket-id="' + ticketId + '"]');
    ticket.detach();
    var targetSelector = '.storylane-ticketcell[story-id="' + newStoryId + '"][phase="' + newPhase + '"]';
    $(targetSelector).css("height", "");
    ticket.appendTo(targetSelector);
    
    // Make the update-dependency call
    var call = "get.php?a=replaceTicketDependency";
    call += "&id=" + ticketId;
    call += "&old=" + currStoryId;
    call += "&new=" + newStoryId;
    $.ajax({
        dataType: "json",
        url: call,
        success: function(data) {
        },
        error: function(xrd, status) {
            alert("ERROR: " + status);
            // TODO move the ticket back...
        },
        complete: function(data) {
        }
    });

    // Make the update-phase call if needed
    if (oldPhase != newPhase) {
        changeTicketPhase(ticketId, newPhase, false);
    }
}

/**
* Puts a ticket into a new phase.  Optionally, also moves the ticket into the specified target div.
*/
function changeTicketPhase(ticketId, newPhase, moveCard, targetSelector) {

    if (moveCard) {
        
        // We're going to append the card to this div, and remove the div's explicit height so it grows properly
        var ticket = $('.ticket[ticket-id="' + ticketId + '"]');
        ticket.detach();
        $(targetSelector).css("height", "");
        ticket.appendTo(targetSelector);
    }
    
    // Make the update call
    var call = "get.php?a=changeTicketPhase";
    call += "&id=" + ticketId;
    call += "&phase=" + newPhase;
    $.ajax({
        dataType: "json",
        url: call,
        success: function(data) {
        },
        error: function(xrd, status) {
            alert("ERROR: " + status);
            // TODO move the ticket back...
        },
        complete: function(data) {
        }
    });
}

function refreshSwimlane(pipeline, milestone, set, headTicketId) {
    dbglog("Refreshing swimlane " + pipeline + "/" + milestone + " in set " + set);
    var storyCellBodySelector = '.storylane-storycell[story-id="' + headTicketId + '"] .storycell-body'; 
    var ticketsSelector = '.storylane-tickets[story-id="' + headTicketId + '"]'; 
    setAsLoading(storyCellBodySelector);
    setAsLoading(ticketsSelector);
    
    // Set up the ajax call to get the story ticket...
    var ticketCardUrl = "get.php?a=ticketCard&id=" + headTicketId;
    ticketCardUrl += "&grouping=" + currFilters.grouping;
    ticketCardUrl += "&highlight=" + currFilters.highlightCond;
    ticketCardUrl += "&display=" + currFilters.displayType;
    $.ajax({
        url: ticketCardUrl,
        context: document.body,
        dataType: "html",
        success: function(data) {
    
            $(storyCellBodySelector).empty();
            $(storyCellBodySelector).append(data);
            hookupCardEvents('.ticket[ticket-id="' + headTicketId + '"]', onTicketEdited);
            
            // Now populate the swimlane...
            populateSwimlaneTickets(milestone, pipeline, set, headTicketId);
        },
        error: function(xhr, status) {
            alert("Unable to load tickets: " + status);
        },
        complete: function() {
        }
    });
}

function createSwimlanes(set, filters, callback) {
    
    // Set up the ajax call...
    var ticketUrl = "get.php";
    ticketUrl += "?a=createSwimlanes"
    ticketUrl += "&pipeline=" + filters.pipeline;
    ticketUrl += "&milestone=" + filters.milestone;
    ticketUrl += "&display=" + filters.display;
    ticketUrl += "&set=" + set;
    
    // Make the call and respond
    $.ajax({
        url: ticketUrl,
        context: document.body,
        dataType: "html",
        success: function(data) {
    
            // Populate the stories column; the start of the swimlanes
            var selector = "#" + set;
            $(selector).empty();
            $(selector).append(data);
            //$(selector).replaceWith(data);
            hookupCardEvents('.ticket', onTicketEdited);

            // Now queue up all the stories so each's row/swimlane can be filled in
            $('.storylane-row[set="' + set + '"] .storylane-storycell').each(function() {
                var o = {
                    storyId: $(this).attr("story-id"),
                    set: set,
                    pipeline: filters.pipeline,
                    milestone: filters.milestone
                };
                dbglog("Adding swimlane to queue: " + o.set + " / " + o.storyId);
                storiesToBePopulated.push(o);
                //populateSwimlaneTickets(filters.milestone, filters.pipeline, set, $(this).attr("story-id"));
            });
            populateNextStorylane();
            populateNextStorylane();

            // We're done, call any callbacks
            if (callback) callback();
        },
        error: function(xhr, status) {
            alert("Unable to load tickets: " + status);
        },
        complete: function() {
        }
    });
}

// Sets up the dialog to create a new ticket in a swimlane, a feature/task/bug
function newSwimlaneTicketDlg(pipeline, milestone, set, storyId, status) {
    dbglog("Going to add ticket to swimlane: " + pipeline + " / " + milestone + ", story id = " + storyId);
    
    // We'll set the component of the new ticket to the component of the parent story
    var dfltComponent = "TBD";
    if (storyId != 0) {
        dfltComponent = $('.ticket[ticket-id="' + storyId + '"]').attr("ticket-component");
    }
    dbglog("New ticket component will be: " + dfltComponent);

    // We'll set the default owner of the new ticket to the owner of the parent story, if any
    var dfltOwner = " ";
    if (storyId != 0) {
        dfltOwner = $('.ticket[ticket-id="' + storyId + '"]').attr("ticket-owner");
    }
    dbglog("New ticket owner will be: " + dfltOwner);

    // Ok go for it
    addTicketDlg(pipeline, milestone, status, "task", "normal", dfltOwner, dfltComponent, "n/a", (storyId == 0) ? "" : ""+storyId, "", function(ticketId) {
        dbglog("Ticket " + ticketId + " added to swimlane");
        populateSwimlaneTickets(milestone, pipeline, set, storyId);     
    });
}

function colorSwimlaneTickets(set, headTicketId) {
    var coloring = $('select[name=coloring]').val();
    if (coloring != "uniform") {
        var selector;
        if (headTicketId != 0) {
            selector = '.storylane-row[set="' + set + '"][story-id="' + headTicketId + '"] .ticket';
        } else {
            selector = '.storylane-row[set="' + set + '"] .ticket';
        }
        colorTickets(selector, "ticket-" + coloring, false);
    } else {
        // No colors
    }
    updateColorKey("colorkey-container", {onItemToggleFuncName: "onShowHideToggle", onItemExclusiveToggleFuncName: "onShowHideExclusiveToggle"});
}

function onShowHideExclusiveToggle(val, showExclusively) {
    dbglog("Key item '" + val + "' toggled, new exclusively-visible state is '" + showExclusively + "'");
    var thisTicketSelector = '.ticket[ticket-' + currFilters.coloring + '="' + val + '"]';   
    var otherTicketSelector = '.ticket[ticket-' + currFilters.coloring + '!="' + val + '"]';   
    var allTicketSelector = '.ticket';   

    // Show all swimlanes while we are adjusting visibility of stuff
    $(".storylane-row").each(function() { $(this).show(); });

    // Update ticket display
    if (showExclusively == "true") {
        $(thisTicketSelector).show();
        $(otherTicketSelector).hide();
    } else {
        dbglog("Showing all tickets...");
        //$(thisTicketSelector).show(); 
        //$(otherTicketSelector).show();
        $('.ticket').show(); 
    }
    
    // Now finalize the swimlanes, each either displaying or not
    showHideSwimlanesPerContents();
}

/**
* Handles a click on an individual color-key item's show/hide toggle.  We show or hide all tickets with the specified value for 
* the current coloring filter.
*
* @param val The value of the coloring attribute that is beign toggled
* @param show Whether we are showing, or hiding, the tickets
*/
function onShowHideToggle(val, show) {
    dbglog("Key item '" + val + "' toggles, new visible state is '" + show + "'");

    // Show all swimlanes while we are adjusting visibility of stuff
    $(".storylane-row").each(function() { $(this).show(); });
    
    // All tickets that have this value set get enabled or disabled
    var ticketSelector = '.ticket[ticket-' + currFilters.coloring + '="' + val + '"]';   
    dbglog("Adjusting items matching '" + ticketSelector + "'");
    $(ticketSelector).each(function () {
        if (show == "true") $(this).show();
        else $(this).hide();
    });
    
    // Now finalize the swimlanes, each either displaying or not
    showHideSwimlanesPerContents();
}

/**
* Show or hide all swimlanes as needed, based on whether they have any visible tickets in them (show the lane) or not (don't show the lane).
*/
function showHideSwimlanesPerContents() {
    $(".storylane-row").each(function() {
        
        // Any visible tickets in this row?
        dbglog("Checking swimlane " + $(this).attr("set") + "/" + $(this).attr("story-id") + " for visible tickets...");
        if ($(this).find(".ticket:visible").length > 0) {
            dbglog("Found at least one visible ticket in this swimlane...showing");
            $(this).show(); 
        } else{
            dbglog("No visible tickets in this swimlane...hiding");
            $(this).hide(); 
        } 
    });
}

function defaultSwimFilters() {
    var filters = {};
    filters.pipeline = null;
    filters.milestone = null;
    filters.coloring = "uniform";
    filters.includeExternal = "yes";
    filters.includeCompleted = "yes";
    return filters; 
}

// Inspect the current page's url fragment, turn it into a filterset, using the default filters passed to us
function fragmentToFilters(defaultFilters) {
    var filters = defaultFilters;
    var fragment = document.location.hash;
    dbglog("Fragment is: " + fragment);
    if (fragment.length > 1) { 
        fragment = fragment.substr(1);
        var pairs = fragment.split('&');
        for(var i = 0; i < pairs.length; i++) {
            var pair = pairs[i];
            var key = pair.substr(0, pair.indexOf("="));
            var value = pair.substr(pair.indexOf("=") + 1);
            dbglog("KV: " + key + " = " + value);
            filters[key] = value;
        }
    }
    return filters;
}

function onTicketEdited(ticketId) {
    dbglog("ticket " + ticketId + " was edited...");
    var ticket = $('.ticket[ticket-id="' + ticketId + '"]');
    var ticketId = ticket.attr("ticket-id");
    if (ticket.attr("ticket-type") != "story") {

        // This ticket isn't a story, so just refresh it
        refreshTicketCard(ticketId, currFilters, onTicketEdited);
    } else {
        
        // This ticket is a story...so refresh it, as well as any swimlanes that it starts 
        refreshTicketCard(ticketId, currFilters, onTicketEdited);
        var swimlaneHeadId = ticket.parents('.storylane-row').attr('story-id');
        var pipeline = ticket.attr('ticket-pipeline');
        var milestone = ticket.attr('ticket-milestone');
        var set = ticket.parents('.storylane-row').attr('set');
        refreshSwimlane(pipeline, milestone, set, swimlaneHeadId);
    }
}
    
// Update any filter controls that are on this page, based on the passed filter set
function updateFilterControls(filters) {
    if (filters.pipeline != null) {
        $('select[name=pipeline]').val(filters.pipeline);
    } 
    if (filters.milestone != null) {
        $('select[name=milestone]').val(filters.milestone);
    }
    if (filters.coloring != null) {
        $('select[name=coloring]').val(filters.coloring);
    }
    $('input[name=show-completed]').attr("checked", (filters.includeCompleted == "yes"));
    $('input[name=show-external]').attr("checked", (filters.includeExternal == "yes"));
/*
    if (filters.includeCompleted) {
        $('input[name=show-completed]').attr("checked");
    } else {
        $('input[name=show-completed]').removeAttr("checked");
    }
    if (filters.includeExternal) {
        $('input[name=show-external]').attr("checked");
    } else {
        $('input[name=show-external]').removeAttr("checked");
    }
*/  
}

// Get the filters set by the current state of the filter controls
function filterControlsToFilters(defaultFilters) {
    var filters = defaultFilters;
    
    filters.pipeline = $('select[name=pipeline]').val();
    filters.milestone = $('select[name=milestone]').val();
    filters.coloring = $('select[name=coloring]').val();
    filters.includeCompleted = ($('input[name="show-completed"]').attr("checked") ? "yes" : "no");
    filters.includeExternal = ($('input[name="show-external"]').attr("checked") ? "yes" : "no");
    return filters;
}

function updateFragmentFromFilters(filters) {
    dbglog("Updating fragment based on current filter set...");
    var fragment = "#";
    for(var prop in filters) {
        dbglog("prop: " + prop);
        if (prop.trim().length > 0) {
            fragment += (prop + "=" + filters[prop] + "&");
        }
    }
    document.location.hash = fragment;
}

function updateSwimlaneHeaders(filters) {
    var ticketCellSizeClass = "ticketcell-5wide";
    if (filters.includeCompleted == "yes" && filters.includeExternal == "yes") {
        ticketCellSizeClass = "ticketcell-5wide";
    } else if ((filters.includeCompleted == "yes" && filters.includeExternal != "yes") || (filters.includeCompleted != "yes" && filters.includeExternal == "yes")) {
        ticketCellSizeClass = "ticketcell-4wide";
    } else {
        ticketCellSizeClass = "ticketcell-3wide";
    }
    var markup = "";
    markup += '<div class="swimlane-bg-ticketcolumn ' + ticketCellSizeClass + '"><div class="swimlane-bg-ticketcolumn-header">Not Started</div></div>';
    markup += '<div class="swimlane-bg-ticketcolumn ' + ticketCellSizeClass + '"><div class="swimlane-bg-ticketcolumn-header">In-Dev</div></div>';
    markup += '<div class="swimlane-bg-ticketcolumn ' + ticketCellSizeClass + '"><div class="swimlane-bg-ticketcolumn-header">In-Test</div></div>';
    if (filters.includeCompleted == "yes") {
        markup += '<div class="swimlane-bg-ticketcolumn ' + ticketCellSizeClass + '"><div class="swimlane-bg-ticketcolumn-header">Completed</div></div>';
    }
    if (filters.includeExternal == "yes") {
        markup += '<div class="swimlane-bg-ticketcolumn ' + ticketCellSizeClass + '"><div class="swimlane-bg-ticketcolumn-header">External</div></div>';
    }
    $(".swimlane-bg-ticketsarea").empty();
    $(".swimlane-bg-ticketsarea").append(markup);
}

function updateSwimlanes(oldFilters, newFilters) {

    // Some changes require a reload, others we'll just do client side TODO
/*
    if (
        (currFilters.milestone != newFilters.milestone)
        ||
        (currFilters.pipeline != newFilters.pipeline)
        ) {
        currFilters = newFilters;
        updateFragment();   
        refreshAll();   
    }
*/
    updateSwimlaneHeaders(newFilters);
    setAsLoading("#active");
    setAsLoading("#completed");
    setAsLoading("#orphaned");
    createSwimlanes("active", newFilters, function() {
        createSwimlanes("completed", newFilters, function() {
            createSwimlanes("orphaned", newFilters);
        });
    });
}

var currFilters = {};
var storiesToBePopulated = [];

$(window).bind('hashchange', function() {
    dbglog("fragment changed...");

/*  
    // Figure out the new filters
    var newFilters = fragmentToFilters(defaultSwimFilters());

    // Update the filter pickers given current active filters
    updateFilterControls(newFilters);
    
*/  
});

$(document).ready(function() {

    // Figure out the filters we're using from our URL fragment
    currFilters = fragmentToFilters(defaultSwimFilters());

    // Update the filter pickers given current active filters, then re-adjust if the choices in those weren't valid (server-determined options)
    updateFilterControls(currFilters); 
    currFilters = filterControlsToFilters(currFilters);

    // Now update the fragment
    updateFragmentFromFilters(currFilters);
    
    // Fill in the stories
    updateSwimlanes(null, currFilters);

    // Respond to changes in any filters that require us to re-obtain tickets
    $("select[name=milestone],select[name=pipeline],input[name=show-completed],input[name=show-external]").change(function() {
        newFilters = filterControlsToFilters(currFilters);
        updateFragmentFromFilters(newFilters);
        resetColoring("colorkey-container");
        updateSwimlanes(currFilters, newFilters);
        currFilters = newFilters;
    });

    // Respond to changes in any filters that are just display changes
    $("select[name=coloring],select[name=display],select[name=highlight],select[name=typeview]").change(function() {
        newFilters = filterControlsToFilters(currFilters);
        updateFragmentFromFilters(newFilters);
        resetColoring("colorkey-container");
        updateSwimlanes(currFilters, newFilters); // TODO really, just do this client side...
        currFilters = newFilters;
    });

});
