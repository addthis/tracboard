<?
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

include "interfacecache.inc.php";
include "traclib.inc.php";

$trac = new TracLib("trac.clearspring.local", "xmlrpc", "CHANGEME", "CHANGEME");
$trac = new InterfaceCache($trac, "/Users/will/projects/tracboard/src/webroot/cache");

/*
$changes = $trac->getTicketChangelog(15409);
echo "Num entries: " . sizeof($changes) . "<br/>";
for($i = 0; $i < sizeof($changes); $i++) {
	echo "Change: " . $i . "<br/>";
	$change = $changes[$i];
	echo "who: " . $change->who . "<br/>";
	echo "field: " . $change->field . "<br/>";
	echo "old val: " . $change->oldVal . "<br/>";
	echo "new val: " . $change->newVal . "<br/>";
}
*/


$components = $trac->getComponents();
echo "Num components: " . sizeof($components) . "<br/>";
foreach ($components as $component) {
	echo $component . "<br/>";
}

$milestones = $trac->getMilestones();
echo "Num milestones: " . sizeof($milestones) . "<br/>";
foreach ($milestones as $milestone) {
	echo $milestone . "<br/>";
}

$tickets = $trac->getTicketsCompleted("20110426", "addthis", TRUE);
echo "Num tickets: " . sizeof($tickets) . "<br/>";
foreach ($tickets as $ticket) {
	echo $ticket->summary . "<br/>";
}

/*
$trac->changeTicketMilestone(15196, "20100810", "changing the milestone...");
*/

/*
$actions = $trac->getTicketActions(15196);
foreach($actions as $action) {
	echo $action . "br/>";
}
*/

/*
$trac->quickCreateTicket("Scrumtrulescent test, created dynamically", "addthis");
*/

/*
$trac->changeTicketSummary(15196, "Scrumtrulescent new summary");
*/

/*
$statusVals = array("new", "assigned", "waiting_for_build", "in_test", "in_test", "ready_for_test");
$tickets = $trac->queryTickets($statusVals, "Backlog", "addthis", array("defect"));
echo "Num tickets: " . sizeof($tickets) . "<br/>";
foreach ($tickets as $ticket) {
	echo $ticket->summary . "<br/>";
}
*/

/*
$notypes = array("defect", "task", "feature");
$stories = $trac->getTicketsInMilestone("20110426", "addthis", $notypes);
echo "Num stories: " . sizeof($stories) . "<br/>";
foreach ($stories as $story) {
	echo $story->id . "(" . $story->type . "): " . $story->summary . "<br/>";
	$dependencies = $trac->getDependentTickets($story->id, NULL);
	echo "ALL DEPS<br/>";
	foreach ($dependencies as $dependency) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $dependency->summary . "<br/>";
	}
	$matrix = $trac->matrixTicketsByField($dependencies, "status");
	var_dump($matrix);	
}
*/




