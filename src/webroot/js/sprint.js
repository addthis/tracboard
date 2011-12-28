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
    console.log("Ticket " + ticketId + " was added.")
    console.log("TODO");
}

$(document).ready(function() {

    // Fill in any empty shells
    $(".column.empty-shell").each(function() {
        refreshColumn($(this).attr("id"), onTicketEdited, onTicketAdded);
    });

    // Respond to changes in any of the primary view filters by refreshing the page
    $("select[name=sprint],select[name=pipeline],select[name=grouping],select[name=coloring],select[name=display],select[name=highlight],select[name=typeview]").change(function() {
        var URL = window.location.href;
        URL = URL.split("?");
        forwardPath = URL[0]
        + "?milestone=" + $('select[name=sprint]').val()
        + "&pipeline=" + $('select[name=pipeline]').val()
        + "&grouping=" + $('select[name=grouping]').val()
        + "&coloring=" + $('select[name=coloring]').val()
        + "&highlight=" + $('select[name=highlight]').val()
        + "&display=" + $('select[name=display]').val()
        + "&expanded=";
        var numCols = 4;
        for (var i = 0; i < numCols; i++) {
            forwardPath += filters.expanded[i];
            if (i < (numCols-1)) {
                forwardPath += ",";
            }
        }
        forwardPath += "&notypes=";
        var view = $('select[name=typeview]').val();
        if (view == "just stories") {
            forwardPath += "defect,task,feature";
        } else if (view == "no bugs") {
            forwardPath += "defect";
        } else if (view == "all") {
            forwardPath += "";
        }
        document.location = forwardPath;
    });

});
