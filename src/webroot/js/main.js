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

function refreshAllColumns(onTicketEdited, onTicketAdded) {
    $(".column").each(function() {
        refreshColumn($(this).attr("id"), onTicketEdited, onTicketAdded);
    });
}

function refreshColumn(columnId, onTicketEdited, onTicketAdded) {
    var col = $("#" + columnId);
    
    // Set the UI for mid-loading...
    setAsLoading("#" + columnId + " .column-body");
    col.find(".column-count").fadeOut();
    //col.find(".column-controls").fadeOut();
    
    // Set up the ajax call...
    var columnUrl = "get.php";
    if (columnId == "backlog") {
        columnUrl += "?a=backlogColumn"
    } else if (columnId == "not-started" || columnId == "in-dev" || columnId == "in-test" || columnId == "done") {
        columnUrl += "?a=phaseTicketColumn"
        columnUrl += "&phase=" + columnId;
        columnUrl += "&milestone=" + filters.milestone;
    } else {
        columnUrl += "?a=milestoneColumn";
        filters.milestone = columnId;
        columnUrl += "&milestone=" + filters.milestone;
    }
    columnUrl += "&pipeline=" + filters.pipeline;
    columnUrl += "&notypes=";
    var numExcluded = filters.excludedTypes.length;
    if (numExcluded > 0) {
        var i = 0; 
        for (i = 0; i < numExcluded; i++) {
            columnUrl += filters.excludedTypes[i];
            if (i < (numExcluded-1))
                columnUrl += ",";
        }
    }
    columnUrl += "&grouping=" + filters.grouping;
    columnUrl += "&coloring=" + filters.coloring;
    columnUrl += "&highlight=" + filters.highlightCond;
    columnUrl += "&display=" + filters.displayType;
    var index = getColumnIndex(columnId);
    columnUrl += "&expanded=" + filters.expanded[index];
    
    // Make the call and respond
    $.ajax({
        url: columnUrl,
        context: document.body,
        dataType: "html",
        success: function(data) {
            var selector = "#" + columnId;
            $(selector).replaceWith(data);
            dbglog("Column refreshed, hooking up events and doing stuff...")
            hookupColumnEvents(columnId, onTicketEdited, onTicketAdded);
            recolorTickets(true);
            fixColumnHeights();
        },
        error: function(xhr, status) {
            alert("Unable to load tickets: " + status);
        },
        complete: function() {
        }
    });

}

function setAsLoading(selector) {
    var html = '<div style="text-align: center; padding: 20px;"><img src="images/ajax-loader.gif"/></div>';
    $(selector).html(html);
}

// Callback takes one arg, the ticket id
function addTicketDlg(pipeline, milestone, status, type, priority, owner, component, scope, blocking, blockedBy, callback) {
    dbglog("Popping new ticket dialog: In " + pipeline + " / " + milestone);

    // Set up the dialog
    $("#add-ticket-dlg #ticket-type").val(type);
    $("#add-ticket-dlg #ticket-priority").val(priority);
    $("#add-ticket-dlg #ticket-owner").val(owner);
    $("#add-ticket-dlg #ticket-component").val(component);
    $("#add-ticket-dlg #ticket-scope").val(scope);
    if (blocking != "") {
        $("#add-ticket-dlg #ticket-blocking").val(blocking);
    } else {
        $("#add-ticket-dlg #ticket-blocking").val("");
    }
    if (blockedBy != "") {
        $("#add-ticket-dlg #ticket-blocked-by").val(blockedBy);
    } else {
        $("#add-ticket-dlg #ticket-blocked-by").val("");
    }
    $("#add-ticket-dlg #ticket-summary").val("");
    $("#add-ticket-dlg #ticket-cancel").unbind();
    $("#add-ticket-dlg #ticket-cancel").click(function() {
        $("#add-ticket-dlg").toggle();
    });
    $("#add-ticket-dlg #ticket-create-in-trac").unbind();
    $("#add-ticket-dlg #ticket-create-in-trac").click(function() {
        var url = "http://" + TRAC_SERVER + "/newticket?";
        url += "&pipeline=" + pipeline;
        url += "&milestone=" + milestone;
        url += "&status=" + status;
        url += "&type=" + encodeURIComponent($("#add-ticket-dlg #ticket-type").val());
        url += "&priority=" + encodeURIComponent($("#add-ticket-dlg #ticket-priority").val());
        url += "&owner=" + encodeURIComponent($("#add-ticket-dlg #ticket-owner").val());
        url += "&component=" + encodeURIComponent($("#add-ticket-dlg #ticket-component").val());
        url += "&scope=" + encodeURIComponent($("#add-ticket-dlg #ticket-scope").val());
        url += "&blocking=" + encodeURIComponent($("#add-ticket-dlg #ticket-blocking").val());
        url += "&blockedby=" + encodeURIComponent($("#add-ticket-dlg #ticket-blocked-by").val());
        url += "&summary=" + encodeURIComponent($("#add-ticket-dlg #ticket-summary").val());
        $("#add-ticket-dlg").toggle();
        window.open(url, "_blank");
        return false;
    });

    $("#add-ticket-dlg #ticket-create").unbind();
    $("#add-ticket-dlg #ticket-create").click(function() {
        var newTicketCall = "get.php?a=newTicket";
        newTicketCall += "&pipeline=" + pipeline;
        newTicketCall += "&milestone=" + milestone;
        newTicketCall += "&status=" + status;
        newTicketCall += "&type=" + encodeURIComponent($("#add-ticket-dlg #ticket-type").val());
        newTicketCall += "&priority=" + encodeURIComponent($("#add-ticket-dlg #ticket-priority").val());
        newTicketCall += "&owner=" + encodeURIComponent($("#add-ticket-dlg #ticket-owner").val());
        newTicketCall += "&component=" + encodeURIComponent($("#add-ticket-dlg #ticket-component").val());
        newTicketCall += "&scope=" + encodeURIComponent($("#add-ticket-dlg #ticket-scope").val());
        newTicketCall += "&blocking=" + encodeURIComponent($("#add-ticket-dlg #ticket-blocking").val());
        newTicketCall += "&blockedby=" + encodeURIComponent($("#add-ticket-dlg #ticket-blocked-by").val());
        newTicketCall += "&summary=" + encodeURIComponent($("#add-ticket-dlg #ticket-summary").val());
        $("#add-ticket-dlg").fadeOut();
        $(".dialog-wrapper").fadeOut();
        dbglog("Ticket creation URL: " + newTicketCall);
        $.ajax({
            dataType: "json",
            url: newTicketCall,
            success: function(data) {
                callback(data.ticketId);
            },
            error: function(xrd, status) {
                alert("ERROR: " + status);
            },
            complete: function(data) {
            }
        });
    });
    
    // Ok show it
    $("#add-ticket-dlg").toggle();
    $("#add-ticket-dlg").center();
    $("#add-ticket-dlg #ticket-summary").focus();
}

// Callback takes one arg, the ticket id
function editTicketDlg(milestone, status, id, type, priority, owner, component, scope, blocking, blockedBy, summary, callback) {
    dbglog("Popping edit ticket dialog for ticket " + id + " in milestone " + milestone);

    // Set up the dialog fields
    $("#edit-ticket-dlg #ticket-type").val(type);
    $("#edit-ticket-dlg #ticket-priority").val(priority);
    $("#edit-ticket-dlg #ticket-owner").val(owner);
    $("#edit-ticket-dlg #ticket-component").val(component);
    $("#edit-ticket-dlg #ticket-scope").val(scope);
    if (blocking != "") {
        $("#edit-ticket-dlg #ticket-blocking").val(blocking);
    } else {
        $("#edit-ticket-dlg #ticket-blocking").val("");
    }
    if (blockedBy != "") {
        $("#edit-ticket-dlg #ticket-blocked-by").val(blockedBy);
    } else {
        $("#edit-ticket-dlg #ticket-blocked-by").val("");
    }
    $("#edit-ticket-dlg #ticket-summary").val(summary);
    
    // Enable quick actions appropriately
    if (milestone != "backlog" && status != "closed") {
        $("#edit-ticket-dlg #backlog-ticket-span").show();
    } else {
        $("#edit-ticket-dlg #backlog-ticket-span").hide();
    }
    if (status == "closed") {
        $("#edit-ticket-dlg #complete-ticket-span").hide();
        $("#edit-ticket-dlg #reopen-ticket-span").show();
    } else {
        $("#edit-ticket-dlg #complete-ticket-span").show();
        $("#edit-ticket-dlg #reopen-ticket-span").hide();
    }
    
    // Hook up events
    $("#edit-ticket-dlg #complete-ticket").unbind();
    $("#edit-ticket-dlg #complete-ticket").click(function() {
        $("#edit-ticket-dlg").toggle();
        alert("TODO I'm working on it...");
    });
    $("#edit-ticket-dlg #reopen-ticket").unbind();
    $("#edit-ticket-dlg #reopen-ticket").click(function() {
        $("#edit-ticket-dlg").toggle();
        alert("TODO I'm working on it...");
    });
    $("#edit-ticket-dlg #backlog-ticket").unbind();
    $("#edit-ticket-dlg #backlog-ticket").click(function() {
        $("#edit-ticket-dlg").toggle();
        alert("TODO I'm working on it...");
    });
    $("#edit-ticket-dlg #ticket-cancel").unbind();
    $("#edit-ticket-dlg #ticket-cancel").click(function() {
        $("#edit-ticket-dlg").toggle();
    });
    $("#edit-ticket-dlg #ticket-edit").unbind();
    $("#edit-ticket-dlg #ticket-edit").click(function() {
        var call = "get.php?a=editTicket";
        call += "&id=" + id;
        call += "&status=" + status;
        call += "&type=" + encodeURIComponent($("#edit-ticket-dlg #ticket-type").val());
        call += "&priority=" + encodeURIComponent($("#edit-ticket-dlg #ticket-priority").val());
        call += "&owner=" + encodeURIComponent($("#edit-ticket-dlg #ticket-owner").val());
        call += "&component=" + encodeURIComponent($("#edit-ticket-dlg #ticket-component").val());
        call += "&scope=" + encodeURIComponent($("#edit-ticket-dlg #ticket-scope").val());
        call += "&blocking=" + encodeURIComponent($("#edit-ticket-dlg #ticket-blocking").val());
        call += "&blockedby=" + encodeURIComponent($("#edit-ticket-dlg #ticket-blocked-by").val());
        call += "&summary=" + encodeURIComponent($("#edit-ticket-dlg #ticket-summary").val());
        $("#edit-ticket-dlg").fadeOut();
        $(".dialog-wrapper").fadeOut();
        $("#edit-ticket-dlg #ticket-summary").val("");
        dbglog("Ticket edit URL: " + call);
        setAsLoading('.ticket[ticket-id="' + id + '"] .body');
        $.ajax({
            dataType: "json",
            url: call,
            success: function(data) {
                callback(data.ticketId);
            },
            error: function(xrd, status) {
                alert("ERROR: " + status);
            },
            complete: function(data) {
            }
        });
    });
    
    // Ok show it
    $("#edit-ticket-dlg").toggle();
    $("#edit-ticket-dlg").center();
    $("#edit-ticket-dlg #ticket-summary").focus();
}

/**
* Doesn't actually edit any ticket, just updates the display of the column to include this new ticket in the right place (group, color).
*/
function addTicketToColumn(ticketId, columnId, onTicketEdited, onTicketAdded) {
    var ticketCardUrl = "get.php?a=ticketCard&id=" + ticketId;
    ticketCardUrl += "&grouping=" + filters.grouping;
    ticketCardUrl += "&coloring=" + filters.coloring;
    ticketCardUrl += "&highlight=" + filters.highlightCond;
    ticketCardUrl += "&display=" + filters.displayType;
    $.ajax({
        url: ticketCardUrl,
        dataType: "html",
        success: function(data) {
        
            // Ok we have the HTML for the new tiucket
            var ticketHtml = data;
            
            // If we're not grouping, just stick it at the beginning of the column
            if (filters.grouping == "none") {
                $("#" + columnId + " .column-body").prepend($(ticketHtml).hide().fadeIn(1000));
            } else {
                
                // Tickets are grouped, so we need to put it in the right group...find that
                var newTicketGroupByFieldVal = $(ticketHtml).attr("groupby-field-val");
                var colSelector = "#" + columnId;
                var groupSelector = ".group[groupby-field-val='" + newTicketGroupByFieldVal + "']"; 
                if ($(colSelector + " " + groupSelector).length > 0) {
                    
                    // Found the group, add this ticket to it
                    $(colSelector + " " + groupSelector + " .groupName").after(ticketHtml);
                } else {
                    
                    // This ticket would be the first in this group, so create that group and put the ticket into it
                    //alert("Creating new group...");
                    groupHtml = '<div groupby-field-val="' + newTicketGroupByFieldVal + '" class="group">';
                    groupHtml += '<div class="groupName">' + newTicketGroupByFieldVal + '&nbsp;(1)</div>';
                    groupHtml += ticketHtml;
                    groupHtml += '</div>';
                    groupHtml += '<div class="clearer"></div>';
                    $("#" + columnId + " .column-body").prepend(groupHtml);
                }
            }
            
            // If we're coloring, recolor everything
            if (filters.coloring != "uniform") {
                recolorTickets(true);
            }
            
            // Increment the count of this column
            incrementCountDisplay("#" + columnId + " .column-header .column-count");
            
            // Make sure all column events are still working
            hookupColumnEvents(columnId, onTicketEdited, onTicketAdded);

            // reset style so positioning looks good
            $("#"+columnId).find(".ticket").attr("style","position:relative");
        }
    });
}

function onShowHideExclusiveToggle(val, showExclusively) {
    var thisTicketSelector = '.ticket[ticket-' + filters.coloring + '="' + val + '"]';   
    var otherTicketSelector = '.ticket[ticket-' + filters.coloring + '!="' + val + '"]';   
    var allTicketSelector = '.ticket';   

    // Show/hide tickets
    if (showExclusively == "true") {
        $(thisTicketSelector).show();
        $(otherTicketSelector).hide();
    } else {
        $('.ticket').show(); 
    }
}

/**
* Handles a click on an individual color-key item's show/hide toggle.  We show or hide all tickets with the specified value for 
* the current coloring filter.
*
* @param val The value of the coloring attribute that is beign toggled
* @param show Whether we are showing, or hiding, the tickets
*/
function onShowHideToggle(val, show) {
    
    // All tickets that have this value set get enabled or disabled
    var ticketSelector = '.ticket[ticket-' + filters.coloring + '="' + val + '"]';   
    dbglog("Adjusting items matching '" + ticketSelector + "'");
    $(ticketSelector).each(function () {
        if (show == "true") $(this).show();
        else $(this).hide();
    });
}

// TODO replace this with the coloring.js api 
function recolorTickets(reset) {
    if (reset) resetColoring();
    if (filters.coloring != "uniform") {
        var selector = ".column .ticket";
        colorTickets(selector, "ticket-" + filters.coloring, false);
    } else {
        // No coloring
    }
    updateColorKey("colorkey-container", {onItemToggleFuncName: "onShowHideToggle", onItemExclusiveToggleFuncName: "onShowHideExclusiveToggle"});
}

function incrementCountDisplay(selector) {
    var countObj = $(selector);
    var text = countObj.text();
    if (text) {
        var num = text.substr(1, text.length-2);
        num++;
        var newText = "(" + num + ")";
        countObj.text(newText);
    }
}

function refreshTicketCard(ticketId, filters, onEditCallback) {
    var ticketCardUrl = "get.php?a=ticketCard&id=" + ticketId;
    ticketCardUrl += "&grouping=" + filters.grouping;
    ticketCardUrl += "&coloring=" + filters.coloring;
    ticketCardUrl += "&highlight=" + filters.highlightCond;
    ticketCardUrl += "&display=" + filters.displayType; 
    $.ajax({
        url: ticketCardUrl,
        dataType: "html",
        success: function(data) {
    
            // Ok we have the HTML for the new tiucket
            var ticketHtml = data;
            
            // Replace the existing ticket's HTML with the new HTML
            $(".ticket[ticket-id=" + ticketId + "]").replaceWith(ticketHtml);

            // If we're coloring, recolor everything
            if (filters.coloring != "uniform") {
                var selector = ".ticket";
                colorTickets(selector, "ticket-" + filters.coloring, true);
            }
            
            // Make sure all column events are still working
            hookupCardEvents('.ticket[ticket-id=' + ticketId + ']', onEditCallback);
        }
    });
}

function onViewUrl(url) {
    $.fancybox({
        'titleShow': false,
        'autoScale': false,
        'transitionIn': 'fade',
        'transitionOut': 'none',
        'type': 'iframe',
        'href': url,
        'width': 1100,
        'height': 600
        });
}

function hookupCardEvents(cardSelector, onEditCallback) {
    
    // Iterate all selected tickets
    $(cardSelector).each(function() {
        var ticket = $(this);
        var ticketId = ticket.attr("ticket-id");
        if (ticket.attr("ticket-evented") == "true") {
            dbglog("Ticket " + ticketId + " already has events attached.");
            return;
        }
        ticket.attr("ticket-evented", "true");
        dbglog("Hooking events up to ticket " + ticketId);
        
        // Set up trac-view click behavior
        var trigger = $(this).find('.viewtrigger')
        trigger.unbind();
        trigger.click(function() {
            window.open(trigger.attr("href"), '_blank');
            return false;
        });
                
        // Setup edit trigger behavior -- pops up the edit dialog and configures it
        var editTrigger = $(this).find(".edittrigger");
        dbglog("Hooking up edit trigger (" + editTrigger + ") on ticket " + ticketId);
        editTrigger.unbind();
        editTrigger.click(function() {
            dbglog("Popping edit dialog for ticket " + ticketId + "...");

            // Get info on this ticket
            var status = ticket.attr("ticket-status");
            var id = ticket.attr("ticket-id");
            var type = ticket.attr("ticket-type");
            var priority = ticket.attr("ticket-priority");
            var owner = ticket.attr("ticket-owner");
            var component = ticket.attr("ticket-component");
            var scope = ticket.attr("ticket-scope");
            var blocking = ticket.attr("ticket-blocking");
            var blockedBy = ticket.attr("ticket-blocked-by");
            var milestone = ticket.attr("ticket-milestone");
            var summary = ticket.find(".body").text();
            
            // Show the edit dlg
            editTicketDlg(milestone, status, id, type, priority, owner, component, scope, blocking, blockedBy, summary, onEditCallback);
            return false;
        });
    
        // Setup on-hover preview popups for all tickets
        var previewTrigger = $(this).find(".previewtrigger");
        var previewHref = 'get.php?a=ticketPreview&id=' + ticketId;
        dbglog("Attaching qtip to ticket " + ticketId);
        previewTrigger.qtip({
            content: {
                url: previewHref
            },
            position: {
                adjust: {
                    screen: true
                }
            },
            style: {
                padding: 0,
                padding: {
                    min: 350,
                    max: 350
                }
            },
            show: {
                effect: {
                    type: "fade"
                },
                solo: true,
                when: {
                    event: 'mouseover'
                },
                delay: 250 
            },
            hide: 'mouseout'
        });

        // Make dragging work
        if (ticket.hasClass("draggable")) {
            ticket.find(".header").css("cursor", "move");
            ticket.draggable({
                helper: 'clone',
                snap: '.column',
                snapMode: 'inner',
                opacity: .7,
                scrollSpeed: 50,
                revert: 'invalid',
                handle: '.header',
                start: function(evt, ui) {  
                    ticket.addClass("ticket-middrag");
                },
                drag: function(evt, ui) {
                    $(".ui-draggable-dragging").width(ticket.width());
                },
                stop: function(evt, ui) {
                    ticket.removeClass("ticket-middrag");
                }
            });
        }
    });
}

/**
* Sets up the column after its been drawn, hooks up all events and such.  onEditCallback takes on arg, the ticket id.
*/
function hookupColumnEvents(columnId, onEditCallback, onAddCallback) {
    dbglog("Hooking up events on column " + columnId + "; onEdit: " + onEditCallback + ", onAdd: " + onAddCallback);
    var column = $(".column");
    
    // Hook up events on all cards in this column
    hookupCardEvents('#' + columnId + " .ticket", onEditCallback);

    // Hook up add-ticket control
    if (column.find(".column-add-story")) {
        column.find(".column-add-story").unbind();
        column.find(".column-add-story").click(function() {
            var pipeline = filters.pipeline;
            var milestone;
            var status = "new";
            if (columnId.indexOf("20") == 0) {
                
                // Dated milestone column...
                milestone = columnId;
            } else if (columnId == "backlog") {

                // Backlog column
                milestone = columnId;
            } else {
                
                // Status column...
                milestone = filters.milestone;
                status = columnId;
            }
            addTicketDlg(pipeline, milestone, status, "story", "normal", " ", "TBD", "n/a", "", "", onAddCallback);
        });
    }
    
    // Hookup column refresh
    if (column.find(".column-reload")) {
        column.find(".column-reload").unbind();
        column.find(".column-reload").click(function() {
            refreshColumn($(this).parents(".column").attr("id"), onEditCallback, onAddCallback);
        });
    }
    
    // Make this column droppable
    $('#' + columnId + '.column').droppable({
        accept: '.ticket',
        tolerance: 'pointer',
        drop: function(evt, ui) {
            
            // Ticket dropped into milestone column...
            $tkt = ui.draggable;
            var ticketId = $tkt.attr("ticket-id");
            var columnId = $(this).attr("id");
            //alert("Dropping ticket " + ticketId + " into column " + columnId);
            $(this).removeClass("column-bg");
            
            // As long as ticket isn't already in this milestone, do it
            var ticketMilestone = $tkt.attr("ticket-milestone");
            if (ticketMilestone.toLowerCase() != columnId.toLowerCase()) {
                $(".ticket[ticket-id=" + ticketId + "]").remove();
                changeTicketMilestone(ticketId, columnId);
            }
        },
        over: function(evt, ui) {
            $tkt = ui.draggable;
            $(this).addClass("column-bg");
        },
        out: function(evt, ui) {
            $tkt = ui.draggable;
            $(this).removeClass("column-bg");
        }
    });

}

function toggleColumnWidth(toggle, columnId) {
    var col = $("#" + columnId);
    var newExpanded = 0;
    if (col.hasClass("column-expanded")) {
        
        // Go smaller
        col.removeClass("column-expanded");
        $(toggle).removeClass("column-expander-expanded").addClass("column-expander-compacted");
        newExpanded = 0;
        col.attr("style","");
        fixColumnHeights();
    } else {
        
        // Go wider
        col.addClass("column-expanded");
        $(toggle).removeClass("column-expander-compacted").addClass("column-expander-expanded");
        newExpanded = 1;
        fixColumnHeights();
    }
    
    // Update the record of our state
    var index = getColumnIndex(columnId);
    filters.expanded[index] = newExpanded;
}

function getColumnIndex(columnId) {
    //alert("Looking for index of column with id " + columnId);
    var foundIndex = 0;
    $(".column").each(function (index) {
        //alert("Checking column " + index + ", id " + $(this).attr("id"));
        if ($(this).attr("id") == ("" + columnId)) {
            //alert("found");
            foundIndex = index;
            return false;
        }
    });
    return foundIndex;
}

function fixColumnHeights() {
  $(".column").equalHeights();
}

// center any DOM element to absolute middle of screen
jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ( $(window).height() - this.outerHeight() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.outerWidth() ) / 2+$(window).scrollLeft() + "px");
    return this;
}

function toggleColor(elem) {
    var coloredBy = $(elem).html();
    $(".ticket[colorby-field-val=" + coloredBy +"]").toggle();
    $(elem).parent().toggleClass("inactive");
    $(elem).toggleClass("inactive");
}

$(document).ready(function() {

    // Make sure we can log
    if (!window.console) {
        console = {};
    }

    // Cancel handling on the add/edit dialogs
    $("#add-story .cancel, #edit-story .cancel").unbind();
    $("#add-story .cancel, #edit-story .cancel").click(function() {
        $(".storySummary").val(""); 
        $(this).parent().parent().parent().parent().fadeOut();
        $(".dialog-wrapper").fadeOut();
    });

    // Handle ticket detail view chooser
    $("select[name=ticketStyle]").change(function() {
        var body = true;
        var header = true;
        var footer = true;
        if ($(this).val() == "compact") {
            body = false;
            header = true;
            footer = true;
        } else if ($(this).val() == "standard") {
            body = true;
            header = true;
            footer = true;
        } else if ($(this).val() == "simple") {
            body = true;
            header = false;
            footer = true;
        }
        if (header) 
            $(".ticket .header").attr("style", "display:block");
        else 
            $(".ticket .header").attr("style", "display:none");
        if (footer) 
            $(".ticket .footer").attr("style", "display:block");
        else 
            $(".ticket .footer").attr("style", "display:none");
        if (body) 
            $(".ticket .body").attr("style", "display:block");
        else 
            $(".ticket .body").attr("style", "display:none");
    });


});
