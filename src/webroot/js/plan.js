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

function onTicketEdited(ticketId) {
    console.log("Ticket " + ticketId + " was edited.")
    
    // Refresh the column this ticket is in
    var ticket = $('.ticket[ticket-id="' + ticketId + '"]');
    var columnId = ticket.parents(".column").attr("id");
    refreshColumn(columnId, onTicketEdited, onTicketAdded);
}

function onTicketAdded(ticketId) {
    var columnId = $('.ticket[ticket-id="' + ticketId + '"]').attr("ticket-milestone");
    console.log("Ticket " + ticketId + " was added to milestone: " + columnId);
    addTicketToColumn(ticketId, columnId, onTicketEdited, onTicketAdded);
}

$(document).ready(function() {

    // Respond to changes in any of the primary view filters by refreshing the page
    $("select[name=pipeline],select[name=grouping],select[name=coloring],select[name=display],select[name=highlight],select[name=typeview]").change(function() {
        refreshPage();
    });

    // Fill in any empty shells
    $(".column.empty-shell").each(function() {
        refreshColumn($(this).attr("id"), onTicketEdited, onTicketAdded);
    });

    // Hook up add-view handling
    $("input[name=add-view]").click(function() {
        var view = $("select[name=view-to-add]").val();
        views.push(view);
        filters.expanded.push(0);
        refreshPage();
    });

});

function removeColumn(columnId) {
    var col = $("#" + columnId);
    col.fadeOut();
    for(var i in views) {
        if (views[i] == columnId) { 
            views.splice(i, 1);
            filters.expanded.splice(i,1);
        }
    }
    refreshPage();
}

function refreshPage() {
    var URL = window.location.href;
    URL = URL.split("?");
    var refreshUrl = URL[0]
    + "?pipeline=" + $('select[name=pipeline]').val()
    + "&grouping=" + $('select[name=grouping]').val()
    + "&coloring=" + $('select[name=coloring]').val()
    + "&display=" + $('select[name=display]').val()
    + "&highlight=" + $('select[name=highlight]').val()
    + "&views=";
    var numViews = views.length;
    for (var i = 0; i < numViews; i++) {
        refreshUrl += views[i];
        if (i < (numViews-1)) refreshUrl += ",";
    }
    refreshUrl += "&expanded=";
    var numExpanded = filters.expanded.length;
    for (var i = 0; i < numExpanded; i++) {
        refreshUrl += filters.expanded[i];
        if (i < (numExpanded-1)) refreshUrl += ",";
    }
    refreshUrl += "&notypes=";
    var view = $('select[name=typeview]').val();
    if (view == "just stories") {
        refreshUrl += "defect,task,feature";
    } else if (view == "no bugs") {
        refreshUrl += "defect";
    } else if (view == "all") {
        refreshUrl += "";
    }
    document.location = refreshUrl;
}

/**
* Puts a ticket into a new milestone, and makes sure the ticket shows up the column for that new milestone.
*/
function changeTicketMilestone(ticketId, newMilestone) {

    // Set the column refresh while we're doing this
    //var columnId = newMilestone;
    //columnLoading(columnId);

    // Make the update call
    var moveTicketCall = "get.php?a=moveTicket";
    moveTicketCall += "&id=" + ticketId;
    moveTicketCall += "&milestone=" + newMilestone;
    $.ajax({
        dataType: "json",
        url: moveTicketCall,
        success: function(data) {
            addTicketToColumn(ticketId, newMilestone, onTicketEdited, onTicketAdded);
        },
        error: function(xrd, status) {
            alert("ERROR: " + status);
        },
        complete: function(data) {
        }
    });
}