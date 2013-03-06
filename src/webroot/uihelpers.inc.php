<?php

require_once('config.inc.php');

define("TICKET_CARD", "1");
define("TICKET_ROW", "2");
define("TICKET_SWIMHEAD", "3");

function buildPriorityChoiceMarkup($dfltPriority = "normal") {
    $markup = '<select id="ticket-priority" name="ticket-priority" class="ticket-priority">';
    $priorities = array("low", "normal", "high", "blocker");
    foreach ($priorities as $p) {
        if($p==$dfltPriority)
            $markup .= '<option value="'.$p.'" selected="selected">'.$p.'</option>';
        else
            $markup .= '<option value="'.$p.'">'.$p.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildScopeChoiceMarkup($dfltScope = "medium") {
    $markup = '<select id="ticket-scope" name="ticketScope" class="ticket-scope">';
    $scopes = array("highest", "high", "medium", "low", "lowest", "n/a");
    foreach ($scopes as $s) {
        if($s==$dfltScope)
            $markup .= '<option value="'.$s.'" selected="selected">'.$s.'</option>';
        else
            $markup .= '<option value="'.$s.'">'.$s.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildTypeChoiceMarkup($dfltType = "story") {
    $markup = '<select id="ticket-type" name="ticketType" class="ticket-type">';
    $types = array("story", "epic", "feature", "task", "defect");
    foreach ($types as $t) {
        if($t==$dfltType)
            $markup .= '<option value="'.$t.'" selected="selected">'.$t.'</option>';
        else
            $markup .= '<option value="'.$t.'">'.$t.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildComponentChoiceMarkup($trac, $dfltComp = "TBD") {
    $markup = '<select id="ticket-component" name="ticket-component" class="ticket-component">';
    $components = $trac->getComponents();
    foreach ($components as $c) {
        if($c==$dfltComp)
            $markup .= '<option value="'.$c.'" selected="selected">'.$c.'</option>';
        else
            $markup .= '<option value="'.$c.'">'.$c.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildTypesMarkup($excludedTypes) {
    $markup = '<select name="typeview">';
    if(sizeof($excludedTypes) == 3 && $excludedTypes[0] == "defect" && $excludedTypes[1] == "feature" && $excludedTypes[2] == "task")
        $markup .= '<option value="just stories" selected="selected">just stories</option>';
    else
        $markup .= '<option value="just stories">just stories</option>';
    if(sizeof($excludedTypes) == 1 && $excludedTypes[0] == "defect")
        $markup .= '<option value="no bugs" selected="selected">no bugs</option>';
    else
        $markup .= '<option value="no bugs">no bugs</option>';
    if(sizeof($excludedTypes) == 0)
        $markup .= '<option value="all" selected="selected">all</option>';
    else
        $markup .= '<option value="all">all</option>';
    $markup .= '</select>';
    return $markup;
}

function buildGroupingMarkup($activeGrouping, $includeStatus=false) {
    $markup = '<select name="grouping">';
    $groupings = array("none", "owner", "priority", "component", "type", "status", "scope", );
    if ($includeStatus) {
        array_push($groupings, "status");
    }
    foreach ($groupings as $g) {
        if($g==$activeGrouping)
            $markup .= '<option value="'.$g.'" selected="selected">'.$g.'</option>';
        else
            $markup .= '<option value="'.$g.'">'.$g.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildColoringMarkup($dfltColoring = "uniform") {
    $markup = '<select title="The field that determines the color of tickets" name="coloring">';
    $colorings = array("uniform", "owner", "priority", "component", "type", "status", "scope");
    foreach ($colorings as $c) {
        if($c==$dfltColoring)
            $markup .= '<option value="'.$c.'" selected="selected">'.$c.'</option>';
        else
            $markup .= '<option value="'.$c.'">'.$c.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildDisplayTypeMarkup($displayType) {
    $markup = '<select name="display">';
    $displays = array("cards", "rows");
    $i = 1;
    foreach ($displays as $d) {
        if($i==$displayType)
            $markup .= '<option value="'.$i.'" selected="selected">'.$d.'</option>';
        else
            $markup .= '<option value="'.$i.'">'.$d.'</option>';
        $i++;
    }
    $markup .= '</select>';
    return $markup;
}

function buildHighlightMarkup($highlight) {
    $markup = '<select name="highlight">';
    $highlights = array("none", "blocking", "blocked");
    foreach ($highlights as $h) {
        if($h == $highlight)
            $markup .= '<option value="'.$h.'" selected="selected">'.$h.'</option>';
        else
            $markup .= '<option value="'.$h.'">'.$h.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildTicketColumnTicketsHtml($tickets, $title, $ticketDragDrop, $displayType, $groupBy = "none", $colorBy = "uniform", $highlightCond = "none") {
    $markup = "";
    if ($groupBy == "none") {
        foreach ($tickets as $ticket) {
            $markup .= buildTicketHtml($ticket, $ticketDragDrop, $displayType, getColorByFieldVal($ticket, $colorBy), getGroupByFieldVal($ticket, $groupBy), $highlightCond);
        }
    } else {

        // We're grouping...
        $groupedTickets = array();
        foreach ($tickets as $ticket) {

            // Put this ticket into a grouped list, based on the value of its group-by field
            $groupFieldVal = getGroupByFieldVal($ticket, $groupBy);
            if (!isset($groupedTickets[$groupFieldVal])) {
                $groupedTickets[$groupFieldVal] = array();
            }
            array_push($groupedTickets[$groupFieldVal], $ticket);
        }

        // Display as grouped
        foreach ($groupedTickets as $groupName => $groupTickets) {
            $markup .= '<div class="group" groupby-field-val="' . $groupName . '" >';
            $markup .= '<div class="groupName">' . $groupName . '&nbsp;' . '(' . sizeof($groupTickets) . ')' . '</div>';
            foreach ($groupTickets as $ticketToRender) {
                $markup .= buildTicketHtml($ticketToRender, $ticketDragDrop, $displayType, getColorByFieldVal($ticketToRender, $colorBy), getGroupByFieldVal($ticketToRender, $groupBy), $highlightCond);
            }
            $markup .= '<div class="clearer"></div>';
            $markup .= '</div>';
        }
    }
    return $markup;
}

/*
* @return NULL if none appropraite
*/
function getColorByFieldVal($ticket, $colorBy) {
    if ($colorBy == "priority") {
        return $ticket->priority;
    }
    if ($colorBy == "owner") {
        return $ticket->owner;
    }
    if ($colorBy == "type") {
        return $ticket->type;
    }
    if ($colorBy == "component") {
        return $ticket->component;
    }
    if ($colorBy == "scope") {
        return $ticket->scope;
    }
    if ($colorBy == "status") {
        return $ticket->status;
    }
    return NULL;
}

/*
* @return NULL if none appropraite
*/
function getGroupByFieldVal($ticket, $groupBy) {
    if ($groupBy == "priority") {
        return $ticket->priority;
    }
    if ($groupBy == "owner") {
        return $ticket->owner;
    }
    if ($groupBy == "scope") {
        return $ticket->scope;
    }
    if ($groupBy == "type") {
        return $ticket->type;
    }
    if ($groupBy == "status") {
        return $ticket->status;
    }
    if ($groupBy == "component") {
        return $ticket->component;
    }
    return NULL;
}

/**
* @phase in-test, in-dev, not-started,, node
*/
function buildMilestonePhaseColumn($trac, $pipeline, $milestone, $phase, $displayType, $grouping, $excludedTypes, $shellOnly, $coloring, $expanded, $highlightCond) {
    $markup = "";
    if ($phase == "not-started") {
        if (!$shellOnly) $tickets = $trac->getTicketsNotStarted($milestone, $pipeline, $excludedTypes);
        $markup .= buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, "Not Started", $phase, false, false, $displayType, $grouping, $coloring, false, $expanded, $highlightCond);
    } else if ($phase == "in-dev") {
        if (!$shellOnly) $tickets = $trac->getTicketsInDev($milestone, $pipeline, $excludedTypes);
        $markup .= buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, "In Dev", $phase, false, false, $displayType, $grouping, $coloring, false, $expanded, $highlightCond);
    } else if ($phase == "in-test") {
        if (!$shellOnly) $tickets = $trac->getTicketsInTest($milestone, $pipeline, $excludedTypes);
        $markup .= buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, "In Test", $phase, false, false, $displayType, $grouping, $coloring, false, $expanded, $highlightCond);
    } else if ($phase == "done") {
        if (!$shellOnly) $tickets = $trac->getTicketsCompleted($milestone, $pipeline, $excludedTypes);
        $markup .= buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, "Completed", $phase, false, false, $displayType, $grouping, $coloring, false, $expanded, $highlightCond);
    }
    return $markup;
}

function buildBacklogColumnHtml($trac, $pipeline, $grouping, $displayType, $excludedTypeVals, $shellOnly, $coloring, $expanded, $highlightCond = "none") {
    $statusVals = array("new", "reopened", "accepted", "assigned", "waiting_for_build", "in_test", "ready_for_test", "in_work");
    if (!$shellOnly) $tickets = $trac->queryTickets($statusVals, BACKLOG_MILESTONE, $pipeline, $excludedTypeVals);
    return buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, "Backlog", "backlog", true, true, $displayType, $grouping, $coloring, true, $expanded, $highlightCond);
}

function buildMilestoneColumnHtml($trac, $pipeline, $milestone, $displayType, $grouping, $excludedTypeVals, $shellOnly, $coloring, $expanded, $highlightCond = "none") {
    $statusVals = array("new", "accepted", "reopened", "assigned", "waiting_for_build", "in_test", "in_work", "ready_for_test");
    if (!$shellOnly) $tickets = $trac->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
    return buildTicketColumnHtml(isset($tickets) ? $tickets : NULL, $milestone, $milestone, true, true, $displayType, $grouping, $coloring, true, $expanded, $highlightCond);
}

function buildOwnerChoiceMarkup($dfltAssignee = " ") {
    $markup = '<select id="ticket-owner" name="ticketOwner" class="ticket-owner">';
    $assignees = array(" ");
    global $ASSIGNEES;
    $assignees = array_merge($assignees, $ASSIGNEES);
    foreach ($assignees as $a) {
        if($a==$dfltAssignee)
            $markup .= '<option value="'.$a.'" selected="selected">'.$a.'</option>';
        else
            $markup .= '<option value="'.$a.'">'.$a.'</option>';
    }
    $markup .= '</select>';
    return $markup;
}

function buildPipelineChoiceMarkup($dfltPipeline = "") {
    $markup = '<select title="The pipeline to view" name="pipeline">';
    global $PIPELINES;
    $pipelines = array();
    $pipelines = array_merge($pipelines, $PIPELINES);
    foreach ($pipelines as $p) {
        $markup .= '<option value="' . $p . '"';
        if ($dfltPipeline == $p) {
            $markup .= ' selected="selected"';
        }
        $markup .= '>' . $p . '</option>';
    }
    $markup .= '      </select>';
    return $markup;
}

// Generates markup for the view filters
function buildFilterSetMarkup($trac, $showMilestones = false) {
    $markup = '<div id="filterset">';
    $markup .= '  <ul>';

    // Pipeline
    $markup .= '    <li>Sprint: ' . buildPipelineChoiceMarkup() . '</li>';

    // Milestone
    if ($showMilestones) {
        $markup .= '      <li><select title="The milestone to view" name="milestone">';
        $milestones = $trac->getMilestones();
        foreach ($milestones as $ms) {
            $markup .= '<option value="'.$ms.'">'.$ms.'</option>';
            }
        $markup .= '      </select></li>';
    }

    // Color-by
    $markup .= '      <li>';
    $markup .= 'Coloring:&nbsp;';
    $markup .= buildColoringMarkup();
    $markup .= '      </li>';

    // Show completed
    $markup .= '      <li>';
    $markup .= '<input title="Include a column for tickets in the completed state" type="checkbox" name="show-completed">Show completed</input>';
    $markup .= '      </li>';
    $markup .= '      <li>';
    $markup .= '<input title="Include a column for open tickets in different milestones" type="checkbox" name="show-external">Show external</input>';
    $markup .= '      </li>';

    // End of primary options
    $markup .= '  </ul>';

    // Right-hand options
    $markup .= '  <div style="float:right">';
    $markup .= '  Admin:&nbsp;';
    $markup .= '  <a href="" title="Admin Trac milestones..." onclick="javascript:onViewUrl(\'http://' . TRAC_SERVER . '/admin/ticket/milestones\');return false;">milestones</a>';
    $markup .= '  /';
    $markup .= '  <a href="" title="Admin Trac components..." onclick="javascript:onViewUrl(\'http://' . TRAC_SERVER . '/admin/ticket/components\');return false;">components</a>';
    $markup .= '  &nbsp;&nbsp;';
    $markup .= '  <a title="Reload this view..." href="" onclick="javascript:refreshAllColumns();return false;">reload</img></a>';
    $markup .= '  </div>';
    $markup .= '<div class="clearer"></div>';

    // Coloring key
    $markup .= '<div id="colorkey-container" style="display:none"></div>';
    $markup .= '</div>';
    return $markup;
}

function buildSwimlaneStoryLane($set, $storyId, $storyPipeline, $storyMilestone, $ticketMatrix, $includeCompleted = false, $includeExternal = false) {
    $mappedTickets = array(array(), array(), array(), array());
    if (isset($ticketMatrix["new"])) $mappedTickets[0] = array_merge($mappedTickets[0], $ticketMatrix["new"]);
    if (isset($ticketMatrix["reopened"])) $mappedTickets[1] = array_merge($mappedTickets[1], $ticketMatrix["reopened"]);
    if (isset($ticketMatrix["accepted"])) $mappedTickets[1] = array_merge($mappedTickets[1], $ticketMatrix["accepted"]);
    if (isset($ticketMatrix["in_work"])) $mappedTickets[1] = array_merge($mappedTickets[1], $ticketMatrix["in_work"]);
    if (isset($ticketMatrix["assigned"])) $mappedTickets[1] = array_merge($mappedTickets[1], $ticketMatrix["assigned"]);
    if (isset($ticketMatrix["waiting_for_build"])) $mappedTickets[1] = array_merge($mappedTickets[1], $ticketMatrix["waiting_for_build"]);
    if (isset($ticketMatrix["ready_for_test"])) $mappedTickets[2] = array_merge($mappedTickets[2], $ticketMatrix["ready_for_test"]);
    if (isset($ticketMatrix["in_test"])) $mappedTickets[2] = array_merge($mappedTickets[2], $ticketMatrix["in_test"]);
    if (isset($ticketMatrix["completed"])) $mappedTickets[3] = array_merge($mappedTickets[3], $ticketMatrix["completed"]);
    if (isset($ticketMatrix["closed"])) $mappedTickets[3] = array_merge($mappedTickets[3], $ticketMatrix["closed"]);
    $totalTickets = 0;
    foreach($mappedTickets as $ticketsInState) {
        $totalTickets += sizeof($ticketsInState);
    }
    if ($includeCompleted && $includeExternal) {
        $ticketCellSizeClass = "ticketcell-5wide";
    } else if (($includeCompleted && !$includeExternal) || (!$includeCompleted && $includeExternal)) {
        $ticketCellSizeClass = "ticketcell-4wide";
    } else {
        $ticketCellSizeClass = "ticketcell-3wide";
    }
    $markup = "";

    // Loop through each status bucket, dumping all the tickets in that bucket into a cell (4 cells)
    // Accumulate "externals", tickets that are not completed that are actually in a different milestone
    $externals = array();
    $phases = array("not-started", "in-dev", "in-test", "completed");
    $statuses = array("new", "assigned", "ready_for_test", "closed");
    for($i=0; $i < 4; $i++) {
        $ticketsInState = $mappedTickets[$i];
        $bucketed = array();
        foreach ($ticketsInState as $ticket) {
            //echo "Ticket milestone is " . $ticket->milestone . " and story/active milestone is " . $storyMilestone;
            if ($ticket->milestone != $storyMilestone) {
                if ($ticket->status != "closed") {
                    array_push($externals, $ticket);
                } else {

                    // Don't care...this was completed in another sprint
                }
            } else {
                array_push($bucketed, $ticket);
            }
        }
        if ($includeCompleted || ($i != 3)) {
            $markup .= '<div class="storylane-ticketcell storylane-ticketcell-phase ' . $ticketCellSizeClass . '" story-id="' . $storyId . '" phase="' . $phases[$i] . '">';
            $markup .= '<div class="ticketcell-header">';
            $numInState = sizeof($bucketed);
            if ($totalTickets == 0) {
                $percentInState = 0;
            } else {
                $percentInState = round(($numInState / $totalTickets) * 100, 0);
            }
            $markup .= $numInState . '/' . $totalTickets . ' (' . $percentInState . ' %)';
            $markup .= '<span class="column-controls">';
            if ($set != "completed") { // We don't add tickets to completed stories...

                $title = ($set == "active") ? ("Add dependency to story " . $storyId) : ("Add new ticket (with no parent story) ");
                $markup .= '<span class="column-add-story column-control" title="' . $title . '" ';
                $markup .= 'onclick="javascript:newSwimlaneTicketDlg(\'' . $storyPipeline . '\', \'' . $storyMilestone . '\', \'' . $set . '\', \'' . $storyId . '\', \'' . $statuses[$i] . '\');">';
                $markup .= '</span>'; // add ticket
            }
            $markup .= '</span>'; // column-controls
            $markup .= '</div>';
            foreach ($bucketed as $ticket) {
                $markup .= buildTicketHtml($ticket, true, NULL, getColorByFieldVal($ticketEx, $coloring), getGroupByFieldVal($ticketEx, $grouping), $highlightCond);
            }
            $markup .= '</div>';
        }
    }

    // Now write in the externals
    if ($includeExternal) {
        $markup .= '<div class="storylane-ticketcell storylane-ticketcell-externals ' . $ticketCellSizeClass . '" story-id="' . $storyId . '">';
        $markup .= '<div class="ticketcell-header">';
        $markup .= sizeof($externals);
        $markup .= '</div>';
        foreach ($externals as $ticket) {
            $markup .= buildTicketHtml($ticket, true, $displayType, getColorByFieldVal($ticketEx, $coloring), getGroupByFieldVal($ticketEx, $grouping), $highlightCond);
        }
        $markup .= '</div>';
    }
    return $markup;
}

function buildSwimlaneStoryCell($pipeline, $milestone, $inSet, $headTicket) {
    $markup .= '<div class="storycell-header">';
    $markup .= '<span class="column-controls">';
    $refreshSwimlaneJs = "javascript:refreshSwimlane('" . $pipeline . "', '" . $milestone . "', '" . $inSet . "', '" . (($headTicket != NULL) ? $headTicket->id : "0") . "');return false;";
    $markup .= '<span class="column-reload column-control" title="Reload this swimlane" onclick="' . $refreshSwimlaneJs . '"></span>';
    $markup .= '</span>'; // column-controls
    $markup .= "&nbsp;";
    $markup .= '<span id="storycell-header-progress" style="float: right;">' . '</span>';
    $markup .= '</div>';
    $markup .= '<div class="storycell-body">';
    if ($headTicket) {
        $markup .= buildTicketHtml($headTicket, false, TICKET_CARD, getColorByFieldVal($headTicket, $coloring), getGroupByFieldVal($headTicket, $grouping), $highlightCond);
    } else {
        $markup .= "----";
    }
    $markup .= '</div>';
    return $markup;
}

function buildSwimlaneStoryCell_alt($pipeline, $milestone, $inSet, $headTicket) {
    if ($headTicket) {
        $markup .= '<div class="ticket ticket-swimlane-head">';

        $markup .= '<div class="header ticket-coloring-alt-dflt">';
        $markup .= $headTicket->id;
        $markup .= '<span id="storycell-header-progress" style="float: right;"></span>';
        $markup .= '<span>Owner:&nbsp;' . $headTicket->owner . '<br/></span>';
        $markup .= '<span>Priority:&nbsp;' . $headTicket->priority . '<br/></span>';
        $markup .= '<span>Scope:&nbsp;' . $headTicket->scope . '<br/></span>';
        $markup .= '</div>';

        $markup .= '<div class="body ticket-coloring-dflt">';
        $markup .= $headTicket->summary;
        $markup .= '</div>';

        $markup .= '<div class="footer ticket-coloring-alt-dflt">';
        $markup .= '<span class="triggers">';
        $markup .= '<span class="previewtrigger-outer"><a class="previewtrigger" href="" onclick="return false;">preview</a></span>';
        $markup .= "&nbsp;&nbsp;";
        $markup .= '<a class="edittrigger" title="Quick edit...">edit</a>';
        $markup .= "&nbsp;|&nbsp;";
        $markup .= '<a class="viewtrigger" title="Open in Trac..." target="_blank" href="' . $ticket->url . '">trac</a>';
        $markup .= "</div>";
        $markup .= "</span>";

        $markup .= '</div>';
    }
    return $markup;
}

function buildSwimlane($pipeline, $milestone, $inSet, $headTicket = NULL) {
    $markup .= '<div class="storylane-row" set="' . $inSet . '" story-id="' . (($headTicket) ? $headTicket->id : 0) . '">';
    $markup .= '<div class="storylane-storycell"';
    $markup .= ' story-id="' . (($headTicket) ? $headTicket->id : 0) . '"';
    $markup .= '>';
    $markup .= buildSwimlaneStoryCell($pipeline, $milestone, $inSet, $headTicket);
    $markup .= '</div>';
    $markup .= '<div class="storylane-tickets" story-id="' . (($headTicket) ? $headTicket->id : 0) . '">';
    $markup .= '</div>';
    $markup .= '</div>';
    return $markup;
}

function buildSwimlanes($pipeline, $milestone, $inSet, $headTickets) {
    $markup = "";
    if ($headTickets) {

        // We have a set of head tickets, build the swimlanes for those, with empty swimlane ticket areas -- those get filled in later
        foreach ($headTickets as $headTicket) {
            $markup .= buildSwimlane($pipeline, $milestone, $inSet, $headTicket);
        }
    } else {

        // No head tickets, so just build one empty-headed swimlane
        $markup .= buildSwimlane($pipeline, $milestone, $inSet);
    }
    return $markup;
}

function buildSwimColumnLayout() {
    $markup  = "";
    $markup .= buildSwimColumnBackground("stories", "stories");
    $markup .= buildSwimColumnBackground("not-started", "not-started");
    $markup .= buildSwimColumnBackground("in-dev", "in-dev");
    $markup .= buildSwimColumnBackground("in-test", "in-test");
    $markup .= buildSwimColumnBackground("completed", "completed");
    $markup .= '<div id="swimlanes">';
    $markup .= '</div>';
    return $markup;
}

function buildSwimColumnBackground($title, $columnId) {
    $markup = '<div class="column" id="' . $columnId . '">';

    $markup .= '<div class="column-header">';
    $markup .= '<span class="column-title">' . $title . '</span>';
    $markup .= '&nbsp;';
    $markup .= '<span class="column-count">(' . "--" . ')</span>';
    $markup .= '<span class="column-controls">';
    $markup .= '<span class="column-add-story column-control" title="Add new story/feature" onclick="javascript:addStoryDlg(\'' . $columnId . '\');"></span>';
    $markup .= '<span class="column-expander-compact column-control" title="Toggle width" onclick="javascript:toggleColumnWidth(this, \'' . $columnId . '\');return false;"></span>';
    $markup .= '<span class="column-reload column-control" title="Reload the tickets in this view" onclick="javascript:refreshColumn(\'' . $columnId . '\');return false;"></span>';
    $markup .= '</span>'; // column-controls
    $markup .= '</div>'; // column-header

    // Tickets
    $markup .= '<div class="column-body">';
    $markup .= '</div>'; // column-body

    // Done
    $markup .= '</div>';
    return $markup;
}

/*
* Renders a column of tickets.  The column will end up with the specified ID, to use as you see fit.
*
* @param columnId What to set as the id attribute of the column, for later lookups
* @param tickets can be null or unset if you just want a shell...
*/
function buildTicketColumnHtml($tickets, $title, $columnId, $closeControl, $ticketDragDrop, $displayType, $groupBy = "none", $colorBy = "uniform", $addControl = false, $expanded = false, $highlightCond = "none") {

    $markup = '<div class="column ' . (!isset($tickets) ? 'empty-shell ' : '') . ($expanded ? 'column-expanded' : '') . '" id="' . $columnId . '" >';

    $markup .= '<div class="column-header">';
    $markup .= '<span class="column-title">' . $title . '</span>';
    $markup .= '&nbsp;';
    //if (isset($tickets)) {

        // We have the tickets, show full header controls
        $markup .= '<span class="column-count">(' . sizeof($tickets) . ')</span>';
        $markup .= '<span class="column-controls">';
        if ($addControl) {
            $markup .= '<span class="column-add-story column-control" title="Add new story/feature"></span>';
        }
        $markup .= '<span class="column-expander-compact column-control" title="Toggle width" onclick="javascript:toggleColumnWidth(this, \'' . $columnId . '\');return false;"></span>';
        $markup .= '<span class="column-reload column-control" title="Reload the tickets in this view"></span>';
        if ($closeControl) {
            $markup .= '<span class="column-control column-closer" title="Remove this view" onclick="javascript:removeColumn(\'' . $columnId . '\');"></span>';
        }
        $markup .= '</span>'; // column-controls
/*
    } else {

        // No tickets, just show close control
        $markup .= '<span class="column-controls">';
        if ($closeControl) {
            $markup .= '<span class="column-control column-closer" title="Remove this view" onclick="javascript:removeColumn(\'' . $columnId . '\');"></span>';
        }
        $markup .= '</span>'; // column-controls
    }
*/
    $markup .= '</div>'; // column-header

    // Tickets
    $markup .= '<div class="column-body">';
    if (isset($tickets)) {
        $markup .= buildTicketColumnTicketsHtml($tickets, $title, $ticketDragDrop, $displayType, $groupBy, $colorBy, $highlightCond);
    }
    $markup .= '</div>'; // column-body

    // Done
    $markup .= '</div>';
    return $markup;
}

/**
*
* @param displayType TICKET_ROW, TICKET_CARD, TICKET_SWIMHEAD
*
* @return HTML for an individual ticket.  The top-level ticket contains attributes for ticket properties.
*/
function buildTicketHtml($ticket, $draggable = false, $displayType = TICKET_ROW, $colorByFieldVal = undefined, $groupByFieldVal = undefined, $highlightCond = "none") {

    // Render in default color
    $ticketColorClass = "ticket-coloring-dflt";
    $ticketColorClassAlt = "ticket-coloring-alt-dflt";

    // Main frame for ticket, including attributes for ticket properties as well as the values of the group-by and color-by fields
    $ticketClass = "ticket";
    if($draggable)
        $ticketClass .= " draggable";
    if($displayType == TICKET_CARD) {
        $ticketClass .= " ticket-card";
    } else if ($displayType == TICKET_ROW) {
        $ticketClass .= " ticket-row";
    } else if ($displayType == TICKET_SWIMHEAD) {
        $ticketClass .= " ticket-swimlane-head";
    } else {
        $ticketClass .= " ticket-card";
    }
    if ($highlightCond) {
        if ($highlightCond == "blocking") {
            if (strlen($ticket->blocking)) {
                $ticketClass .= " ticket-highlighted";
            }
        } else if ($highlightCond == "blocked") {
            if (strlen($ticket->blockedBy)) {
                $ticketClass .= " ticket-highlighted";
            }
        }
    }
    $ticketClass .= " " . $ticketColorClass;
    $markup = '<div class="' . $ticketClass . '"';
    $markup .= ' ticket-id="' . $ticket->id . '"';
    $markup .= ' ticket-type="' . $ticket->type . '"';
    $markup .= ' ticket-status="' . $ticket->status . '"';
    $markup .= ' ticket-owner="' . $ticket->owner . '"';
    $markup .= ' ticket-blocking="' . $ticket->blocking . '"';
    $markup .= ' ticket-blocked-by="' . $ticket->blockedBy . '"';
    $markup .= ' ticket-priority="' . $ticket->priority . '"';
    $markup .= ' ticket-scope="' . $ticket->scope . '"';
    $markup .= ' ticket-milestone="' . $ticket->milestone . '"';
    $markup .= ' ticket-component="' . $ticket->component . '"';
    if ($colorByFieldVal)
        $markup .= ' colorby-field-val="' . $colorByFieldVal . '"';
    if ($groupByFieldVal)
        $markup .= ' groupby-field-val="' . $groupByFieldVal . '"';
    $markup .= '>';

    // Header
    $headerClass = "header " . $ticketColorClassAlt;
    $markup .= '<div class="' . $headerClass . '">';
    $summaryTitle = $ticket->type . " #" . $ticket->id;
    $markup .= '<span class="summary" title="' . $summaryTitle . '">' . shortType($ticket->type) . '&nbsp;' . $ticket->id . '</span>';
    $priorityScopeTitle = $ticket->priority . " priority";
    $priorityScopeText = shortPri($ticket->priority);
    if (isset($ticket->scope) && ($ticket->scope != "n/a")) {
        $priorityScopeTitle .= ", " . $ticket->scope . " scope";
        $priorityScopeText .= "/" . shortScope($ticket->scope);
    }
    $markup .= '<span title="' . $priorityScopeTitle . '" class="priority-scope">' . $priorityScopeText . "</span>";
    $markup .= '</div>';

    // Body
    $markup .= '<div class="body">';
    $markup .= $ticket->summary . "<br/>";
    $markup .= '</div>';

    // Footer
    $footerClass = "footer " . $ticketColorClassAlt;
    $markup .= '<div class="' . $footerClass . '">';
    $markup .= '<span class="assignee" title="' . ((strlen($ticket->owner) > 2) ? ('Ticket owned by ' . $ticket->owner) : ('')) . '">' . strtoupper(substr($ticket->owner, 0, 5)) . "</span>";
    $markup .= '<span class="triggers">';
    $markup .= '<span class="previewtrigger-outer"><a class="previewtrigger" href="" onclick="return false;">(...)</a></span>';
    $markup .= "&nbsp;&nbsp;";
    $markup .= '<a class="edittrigger" title="Quick edit...">edit</a>';
    $markup .= "&nbsp;|&nbsp;";
    $markup .= '<a class="viewtrigger" title="Open in Trac..." target="_blank" href="' . $ticket->url . '">trac</a>';
    $markup .= "</div>";
    $markup .= "</span>";
    $markup .= '</div>';
    return $markup;
}

function niceTruncate($str, $maxChars) {
    if (strlen($str) > $maxChars) {
        $str = substr($str, 0, $maxChars-3) . "...";
    }
    return $str;
}

function shortPri($priority) {
    if ($priority == "high") {
        return "HI";
    } else if ($priority == "normal") {
        return "MED";
    } else if ($priority == "low") {
        return "LOW";
    } else if ($priority == "blocker") {
        return "BLOCK";
    }
    return $priority;
}

function shortScope($scope) {
    if ($scope == "high") {
        return "HI";
    } else if ($scope == "medium") {
        return "MED";
    } else if ($scope == "low") {
        return "LOW";
    } else if ($scope == "n/a") {
        return "n/a";
    }
    return $scope;
}

function shortType($type) {
    if ($type == "feature request") {
        return "FTR";
    } else if ($type == "feature dev") {
        return "FTR";
    } else if ($type == "defect") {
        return "BUG";
    } else if ($type == "feature") {
        return "FTR";
    } else if ($type == "task") {
        return "TASK";
    } else if ($type == "requirement") {
        return "REQ";
    } else if ($type == "story") {
        return "STORY";
    } else if ($type == "epic") {
        return "STORY";
    }
    return $type;
}

function buildTicketPreviewHtml($ticketEx) {
    $markup = '<div class="ticket-preview">';
    if ($ticketEx) {
        $markup .= "<div class='ticket-preview-title'>" . shortType($ticketEx->type) . "&nbsp;" . $ticketEx->id . "&nbsp;(" . shortPri($ticketEx->priority) . (($ticketEx->scope != "n/a" && isset($ticketEx->scope)) ? ("/" . shortScope($ticketEx->scope)) : "") . ")</div>";
        $markup .= '<div style="font-size: 11px;font-style: italic; ">';
        $markup .= "" . $ticketEx->milestone . "&nbsp;(" . $ticketEx->status . ")";
        $markup .= "<br/>";
        $markup .= "" . $ticketEx->component;
        $markup .= "<br/>";
        $markup .= "" . $ticketEx->reporter . "&nbsp;>&nbsp;" . $ticketEx->owner;
        $markup .= "<br/>";

        // Blocker?  blocked by?
        if (strlen($ticketEx->blocking) > 0) {
            $markup .= "Blocking: " . $ticketEx->blocking;
            $markup .= "<br/>";
        }
        if (strlen($ticketEx->blockedBy) > 0) {
            $markup .= "Blocked by: " . $ticketEx->blockedBy;
            $markup .= "<br/>";
        }

        // Summary and details
        $markup .= "<br/>";
        $markup .= '</div>';
        $markup .= "<div class='ticket-preview-heading'>Summary</div>";
        $markup .= niceTruncate($ticketEx->summary, 100);
        $markup .= "<br/>";
        $markup .= "<br/>";
        if (strlen($ticketEx->description) > 0) {
            $markup .= "<div class='ticket-preview-heading'>Description</div>";
            $markup .= niceTruncate(htmlspecialchars($ticketEx->description), 400);
            $markup .= "<br/>";
            $markup .= "<br/>";
        }

        // Comments & changes
        if (sizeof($ticketEx->changes) > 0) {
            $markup .= "<div class='ticket-preview-heading'>Recent Changes</div>";
            $markup .= "<div class='ticket-preview-changelog'>";
            if (sizeof($ticketEx->changes) > 3) {
                $max = 3;
            } else {
                $max = sizeof($ticketEx->changes);
            }
            for($i = 0; $i < $max; $i++) {
                $change = $ticketEx->changes[$i];
                if ($change->field == "comment") {

                    // Comment
                    if (strlen($change->newVal) > 0) {
                        $markup .= $change->who . ": " . niceTruncate($change->newVal, 100) . "<br/>";
                    }
                } else {

                    // Other change
                    if (strlen($change->newVal) > 0) {
                        $markup .= $change->who . ": " . $change->field . " " . "[" . niceTruncate($change->oldVal, 100) . "] -> [" . niceTruncate($change->newVal, 100) . "]" . "<br/>";
                    }
                }
            }
            $markup .= "</div>";
        }
    } else {
        $markup .= "Sorry, unable to load ticket details...";
    }
    $markup .= '</div>';
    return $markup;
}


