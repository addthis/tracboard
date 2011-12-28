<?php 
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

require_once ("header.inc.php");
require_once("uihelpers.inc.php");
require_once("utils.inc.php");

$action = urlParam('a', "");

if ($action == "ticketPreview") { // render ticket-preview HTML
	$ticketId = urlParam('id');
	$ticketEx = $trac->getTicket($ticketId, TRUE);
	$previewMarkup = buildTicketPreviewHtml($ticketEx);
	echo $previewMarkup;
} else if ($action == "ticketCard") { // render ticket card HTML
	$ticketId = urlParam('id');
	$grouping = urlParam('grouping');
	$coloring = urlParam('coloring');
	$displayType = urlParam('display');
	$highlightCond = urlParam('highlight');
	$ticketEx = $trac->getTicket($ticketId, TRUE);
	$markup = buildTicketHtml($ticketEx, TRUE, $displayType, getColorByFieldVal($ticketEx, $coloring), getGroupByFieldVal($ticketEx, $grouping), $highlightCond);
	echo $markup;
} else if ($action == "moveTicket") { // move the ticket to the new milestone
	$ticketId = urlParam('id');
	$milestone = urlParam('milestone');
	if ($milestone == "backlog") $milestone = BACKLOG_MILESTONE;
	$trac->changeTicketMilestone($ticketId, $milestone, "Updated milestone during planning");
	header("Content-Type: application/json");
	echo '{"status": "0"}';
} else if ($action == "changeTicketPhase") { // move the ticket to a new "phase"
	$ticketId = urlParam('id');
	$newPhase = urlParam('phase');
	$newStatus = "not-started";
	$resolution = "";
	if ($newPhase == "not-started") {
		$newStatus = "new";
	} else if ($newPhase == "in-dev") {
		$newStatus = "assigned";
	} else if ($newPhase == "in-test") {
		$newStatus = "ready_for_test";
	} else if ($newPhase == "completed") {
		$newStatus = "closed";
		$resolution = "completed";
	}
	$comment = "Changed via TracBoard";
	$trac->changeTicketStatus($ticketId, $comment, $newStatus, $resolution);
	header("Content-Type: application/json");
	echo '{"status": "0"}';
} else if ($action == "newTicket") { // create a new ticket
	$summary = urlParam('summary');
	$pipeline = urlParam('pipeline');
	$priority = urlParam('priority');
	$component = urlParam('component');
	$scope = urlParam('scope');
	$blocking = urlParam('blocking');
	$ticketType = urlParam('type');
	$owner = urlParam('owner');
	$status = urlParam('status');
	$milestone = urlParam('milestone');
	if ($milestone == "backlog") $milestone = BACKLOG_MILESTONE;
	$ticketId = $trac->quickCreateTicket($summary, $pipeline, $ticketType, $milestone, $priority, $scope, $component, $blocking, $status, $owner);
	header("Content-Type: application/json");
	echo '{"status": "0", "ticketId": "' . $ticketId . '"}';
} else if ($action == "editTicket") { // change the ticket's summary
	$ticketId = urlParam('id');
	$newType = urlParam('type', NULL);
    $newPriority = urlParam('priority', NULL);
    $newOwner = urlParam('owner', NULL);
    $newComponent = urlParam('component', NULL);
    $newScope = urlParam('scope', NULL);
    $newBlocking = urlParam('blocking', NULL);
    $newBlockedBy = urlParam('blockedby', NULL);
	$newSummary = urlParam('summary');
	$trac->updateTicketFields($ticketId, $newSummary, $newPriority, $newScope, $newComponent, $newType, $newOwner, $newBlocking, $newBlockedBy);
	header("Content-Type: application/json");
	echo '{"status": "0", "ticketId": "' . $ticketId . '"}';
} else if ($action == "replaceTicketDependency") { // change the ticket's blockers and blocking
	$ticketId = urlParam('id');
	$old = urlParam('old');
    $new = urlParam('new');
	$trac->changeTicketParent($ticketId, $old, $new);
	header("Content-Type: application/json");
	echo '{"status": "0", "ticketId": "' . $ticketId . '"}';
} else if ($action == "milestoneColumn") { // render a column of tickets in a given state within a milestone
	$milestone = urlParam('milestone'); 
	$pipeline = urlParam('pipeline');
	$excludedTypesStr = urlParam('notypes', "");
	if ($excludedTypesStr == "") {
		$excludedTypes = array();
	} else {
		$excludedTypes = explode(",", $excludedTypesStr);
	}
	$grouping = urlParam('grouping');
	$coloring = urlParam('coloring');
	$expanded = urlParam('expanded');
	$displayType = urlParam('display');
	$highlightCond = urlParam('highlight', "none");
    echo buildMilestoneColumnHtml($trac, $pipeline, $milestone, $displayType, $grouping, $excludedTypes, FALSE, $coloring, $expanded, $highlightCond);
} else if ($action == "backlogColumn") { // render a column of tickets in a backlog
	$pipeline = urlParam('pipeline');
	$excludedTypesStr = urlParam('notypes', "");
	if ($excludedTypesStr == "") {
		$excludedTypes = array();
	} else {
		$excludedTypes = explode(",", $excludedTypesStr);
	}
	$grouping = urlParam('grouping', "none");
	$coloring = urlParam('coloring', "uniform");
	$expanded = urlParam('expanded');
	$displayType = urlParam('display', TICKET_CARD);
	$highlightCond = urlParam('highlight', "none");
    echo buildBacklogColumnHtml($trac, $pipeline, $grouping, $displayType, $excludedTypes, FALSE, $coloring, $expanded, $highlightCond);
} else if ($action == "createSwimlanes") { // render a set of tickets
	$milestone = urlParam('milestone'); 
	$pipeline = urlParam('pipeline');
	$excludedTypesStr = urlParam('notypes', "");
	$set = urlParam('set', "active"); 
	if ($excludedTypesStr == "") {
		$excludedTypes = array();
	} else {
		$excludedTypes = explode(",", $excludedTypesStr);
	}
	$grouping = urlParam('grouping', "none");
	$coloring = urlParam('coloring', "uniform");
	$expanded = urlParam('expanded');
	$displayType = urlParam('display', TICKET_CARD);
	$highlightCond = urlParam('highlight', "none");
	$notypes = array("defect", "task", "feature");
	$headTickets = array();
	if ($set === "active") {
		$statusVals = array("in_work", "assigned", "new", "accepted", "waiting_for_build", "reopened", "ready_for_test", "in_test");
		$headTickets = $trac->getTicketsInMilestone($milestone, $pipeline, $notypes, $statusVals);
		if ($headTickets != null && sizeof($headtickets) == 0) {
			echo buildSwimlanes($pipeline, $milestone, $set, $headTickets);
		}
	} else if ($set == "completed") {
		$statusVals = array("closed");
		$headTickets = $trac->getTicketsInMilestone($milestone, $pipeline, $notypes, $statusVals);
		if ($headTickets != null && sizeof($headtickets) == 0) {
			echo buildSwimlanes($pipeline, $milestone, $set, $headTickets);
		}
	} else if ($set == "orphaned") {
		echo buildSwimlanes($pipeline, $milestone, $set, $headTickets);
	}	
} else if ($action == "populateSwimlane") { // render a set of tickets
	$milestone = urlParam('milestone'); 
	$pipeline = urlParam('pipeline');
	$set = urlParam('set');
	$includeCompleted = (urlParam('completed') == "true");
	$includeExternal = (urlParam('external') == "true");
	$headTicketId = 0;
	if ($set == "orphaned") {
		$tickets = $trac->getNonBlockingTicketsInMilestone($milestone, $pipeline, array("story"));
	} else {
		
		// active or completed, either way we're just getting dependencies
		$headTicketId = urlParam('headid');
		if ($headTicketId == "0") {
			
			// No tickets in this swimlane...its empty
			$tickets = array();
		} else {
			$headTicket = $trac->getTicket($headTicketId, FALSE);
			$tickets = $trac->getDependentTickets($headTicket->id, NULL);
		}
	}
	$ticketMatrix = $trac->matrixTicketsByField($tickets, "status");
	echo buildSwimlaneStoryLane($set, $headTicketId, $pipeline, $milestone, $ticketMatrix, $includeCompleted, $includeExternal);
} else if ($action == "phaseTicketColumn") { // render a column of tickets in a given phase within a milestone
	$milestone = urlParam('milestone'); 
	$pipeline = urlParam('pipeline');
	$excludedTypesStr = urlParam('notypes', "");
	if ($excludedTypesStr == "") {
		$excludedTypes = array();
	} else {
		$excludedTypes = explode(",", $excludedTypesStr);
	}
	$grouping = urlParam('grouping', "none");
	$coloring = urlParam('coloring', "uniform");
	$expanded = urlParam('expanded');
	$displayType = urlParam('display', TICKET_CARD);
	$phase = urlParam('phase', "not-started");
	$highlightCond = urlParam('highlight');
	echo buildMilestonePhaseColumn($trac, $pipeline, $milestone, $phase, $displayType, $grouping, $excludedTypes, FALSE, $coloring, $expanded, $highlightCond);
} else {
	echo "WTF action?"; 
}

?>