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

include "xmlrpc/xmlrpc.inc";
date_default_timezone_set('America/New_York');

class Ticket {

  public $id;       
  public $type;       
  public $summary;   
  public $url;   
  public $priority;   
  public $scope;   
  public $component;   
  public $owner;   
  public $status;   
  public $reporter;   
  public $milestone;   
  public $pipeline;   
  public $blocking;   
  public $blockedBy;   
}

class TicketChange {
  public $who;
  public $field;
  public $oldVal;
  public $newVal;
}

class TicketComment {
  public $who;
  public $comment;
}

class TicketEx extends Ticket {

  public $description;   
  public $changes;
  public $comments;
}

abstract class ITracLib {

  public abstract function changeTicketStatus($ticketId, $comment, $newStatus, $resolution);

  public abstract function changeTicketMilestone($ticketId, $newMilestone, $comment);

  public abstract function changeTicketSummary($ticketId, $newSummary);

  public abstract function updateTicketFields($ticketId, $newSummary, $newPriority, $newScope, $newComponent, $newType, $newOwner, $newBlocking, $newBlockedBy);

  public abstract function quickCreateTicket($summary, $pipeline, $ticketType = "feature", $newMilestone = "TBD", $priority = "normal", $scope = "n/a", $component="TBD", $blocking="", $status = "new", $owner=" ");

  public abstract function getTicketActions($ticketId);

  public abstract function getTicketChangelog($ticketId, $maxChanges = -1);
  
  public abstract function queryTicketsRaw($query);

  public abstract function queryTickets($statusVals, $milestone, $pipeline = NULL, $excludedTypeVals = NULL);

  public abstract function getTicketsNotStarted($milestone, $pipeline = NULL, $excludedTypeVals = NULL);

  public abstract function getTicketsInTest($milestone, $pipeline = NULL, $excludedTypeVals = NULL);

  public abstract function getTicketsInDev($milestone, $pipeline = NULL, $excludedTypeVals = NULL);

  public abstract function getTicketsInMilestone($milestone, $pipeline = NULL, $excludedTypeVals = NULL, $statusVals = NULL);

  public abstract function getNonBlockingTicketsInMilestone($milestone, $pipeline = NULL, $excludedTypeVals = NULL, $statusVals = NULL);
  
  public abstract function getDependentTickets($ticketId, $excludedTypeVals = NULL);

  public abstract function matrixTicketsByField($tickets, $fieldName);

  public abstract function getTicketsCompleted($milestone, $pipeline = NULL, $excludedTypeVals = NULL);
  
  public abstract function getTicket($ticketId, $extendedInfo = FALSE);

  public abstract function isFutureDatedMilestone($milestoneName);

  public abstract function isDatedMilestone($milestoneName);

  public abstract function getNextMilestone();
  
  public abstract function getMilestones($datedOnly = TRUE, $noPastDates = TRUE);

  public abstract function getComponents();

}

class TracLib extends ITracLib {

  private $tracBase;
  private $username;
  private $password;
  private $client;
  
  public function TracLib($tracHost, $tracXmlRpcPath, $tracUsername, $tracPassword) {
    $this->tracBase = "http://" . $tracHost;
    $this->username = $tracUsername;
    $this->password = $tracPassword;
    $this->client = new xmlrpc_client("/" . $tracXmlRpcPath, $tracHost, 80);
    $this->client->setCredentials($this->username, $this->password);
  }

  /**
  * NO WORKFLOW
  * @return boolean if success
  */
  public function changeTicketStatus($ticketId, $comment, $newStatus, $resolution) {
    if ($newStatus == "closed") {
      $attrs = array("status" => new xmlrpcval($newStatus, "string"), "resolution" => new xmlrpcval($resolution, "string"));
    } else {
      $attrs = array("status" => new xmlrpcval($newStatus, "string"));
    }
    $attrStruct = new xmlrpcval($attrs,"struct");
    $args = array();
    $args[0] = new xmlrpcval($ticketId, "int"); // id
    $args[1] = new xmlrpcval($comment, "string"); // comment
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.update", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * @return boolean if success
  */
  public function changeTicketMilestone($ticketId, $newMilestone, $comment) {
    $attrStruct = new xmlrpcval(array(
                                "milestone" => new xmlrpcval($newMilestone, "string"),
                    ), 
                  "struct");
    $args = array();
    $args[0] = new xmlrpcval($ticketId, "int"); // id
    $args[1] = new xmlrpcval($comment, "string"); // comment
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.update", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * @return boolean if success
  */
  public function changeTicketSummary($ticketId, $newSummary) {
    $attrStruct = new xmlrpcval(array(
                                "summary" => new xmlrpcval($newSummary, "string"),
                    ), 
                  "struct");
    $args = array();
    $args[0] = new xmlrpcval($ticketId, "int"); // id
    $args[1] = new xmlrpcval("Changing summary...", "string"); // comment
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.update", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Update a specific ticket's basic fields.  any parameter that is not set will not be touched.  to "empty" a field that is just a string value, 
  * like "blocking or "blockedby", set it to an empty string
  *
  * @return boolean if success 
  */
  public function updateTicketFields($ticketId, $newSummary, $newPriority, $newScope, $newComponent, $newType, $newOwner, $newBlocking, $newBlockedBy) {
    $attrs = array();
    if (isset($newSummary)) $attrs["summary"] = new xmlrpcval($newSummary, "string");
    if (isset($newSummary)) $attrs["summary"] = new xmlrpcval($newSummary, "string");
    if (isset($newPriority)) $attrs["priority"] = new xmlrpcval($newPriority, "string");
    if (isset($newScope)) $attrs["scope"] = new xmlrpcval($newScope, "string");
    if (isset($newComponent)) $attrs["component"] = new xmlrpcval($newComponent, "string");
    if (isset($newType)) $attrs["type"] = new xmlrpcval($newType, "string");
    if (isset($newOwner)) $attrs["owner"] = new xmlrpcval($newOwner, "string");
    if (isset($newBlocking)) $attrs["blocking"] = new xmlrpcval($newBlocking, "string");
    if (isset($newBlockedBy)) $attrs["blockedby"] = new xmlrpcval($newBlockedBy, "string");
    $attrStruct = new xmlrpcval($attrs, "struct");
    /*
    $attrStruct = new xmlrpcval(array(
                                "summary" => new xmlrpcval($newSummary, "string"),
                                "priority" => new xmlrpcval($newPriority, "string"),
                                "scope" => new xmlrpcval($newScope, "string"),
                                "component" => new xmlrpcval($newComponent, "string"),
                    ), 
                  "struct");
                  */
                  
                  
    $args = array();
    $args[0] = new xmlrpcval($ticketId, "int"); // id
    $args[1] = new xmlrpcval("Updating ticket info.", "string"); // comment
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.update", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * @return boolean if success
  */
  public function changeTicketParent($ticketId, $oldParent, $newParent) {
    $ticket = $this->getTicket($ticketId);
    $blocking = $ticket->blocking;

    // Figure out new blocker list (latenight)
    $newAr = array();
    if ($oldParent != "0") {
      $ar = explode(",", $blocking);
      foreach($ar as $id) {
        if ($id == $oldParent) {
          if ($newParent != "0") {
            array_push($newAr, $newParent);
          }
        } else {
          array_push($newAr, $id);
        }
      }
      $blocking = implode(",", $newAr);
    } else {
      if ($blocking == "") $blocking = $newParent;
      else $blocking .= "," . $newParent;
    }
    //echo "Setting blocking to: " . $blocking . "<br/>";
    $attrStruct = new xmlrpcval(array(
                                "blocking" => new xmlrpcval($blocking, "string")
                    ), 
                  "struct");
    $args = array();
    $args[0] = new xmlrpcval($ticketId, "int"); // id
    $args[1] = new xmlrpcval("Changing parent...", "string"); // comment
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.update", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * @return ticket ID or FALSE
  */
  public function quickCreateTicket($summary, $pipeline, $ticketType = "feature", $newMilestone = "TBD", $priority = "normal", $scope = "n/a", $component="TBD", $blocking="", $status = "new", $owner=" ") {
    $attrs = array(
        "milestone" => new xmlrpcval($newMilestone, "string"),
        "pipeline" => new xmlrpcval($pipeline, "string"),
      "type" => new xmlrpcval($ticketType, "string"),
      "priority" => new xmlrpcval($priority, "string"),
      "scope" => new xmlrpcval($scope, "string"),
      "component" => new xmlrpcval($component, "string"),
      "blocking" => new xmlrpcval($blocking, "string"),
      );
    if ($owner != " " && $owner != "") {
      $attrs["owner"] = new xmlrpcval($owner, "string");
    } 
    $attrStruct = new xmlrpcval($attrs, "struct");
    $args = array();
    $args[0] = new xmlrpcval($summary, "string"); // summary
    $args[1] = new xmlrpcval($summary, "string"); // description
    $args[2] = $attrStruct;
    $args[3] = new xmlrpcval(TRUE, "boolean"); // notify
    $msg = new xmlrpcmsg("ticket.create", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      $ticketId = $val->scalarval();
      if ($stauts != "new") {
        $this->changeTicketStatus($ticketId, "", $status, "completed");
      }
      return $ticketId;
    }
    return FALSE;
  }

  /**
  * @return array of actions that can be performed on this ticket
  */
  public function getTicketActions($ticketId) {
    $actions = array();
    $args = array(new xmlrpcval($ticketId, "int"));
    $msg = new xmlrpcmsg("ticket.getAvailableActions", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      echo $val->serialize() . "<br/>";
      $actionList =$val->structeach();      
      foreach ($actionList as $key => $val) {
        //echo $key . "<br/>";
        //array_push($actions, $key);
      }
    }
    return $actions;
  }

  /**
  * @return array of TicketChange objects
  */
  public function getTicketChangelog($ticketId, $maxChanges = -1) {
    $changes = array();
    $args = array(new xmlrpcval($ticketId, "int"));
    $msg = new xmlrpcmsg("ticket.changeLog", $args);
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      for ($i = $val->arraySize() - 1; $i >= 0; $i--) {
        $changeVal = $val->arraymem($i);
        $time = $changeVal->arraymem(0)->scalarval();
        $author = $changeVal->arraymem(1)->scalarval();
        $field = $changeVal->arraymem(2)->scalarval();
        $oldVal = $changeVal->arraymem(3)->scalarval();
        $newVal = $changeVal->arraymem(4)->scalarval();
        $permanent = $changeVal->arraymem(5)->scalarval();
        
        $change = new TicketChange();
        $change->who = $author;
        $change->field = $field;
        $change->oldVal = $oldVal;
        $change->newVal = $newVal;
        
        array_push($changes, $change);
      } 
    } else {
      echo "ERROR" . "<br/>";
      echo $this->client->Erna;
      echo $this->client->err;
    }
    return $changes;
  }
  
  /**
  * @return Array of Ticket objects
  */
  public function queryTicketsRaw($query) {
    $query .= "&max=1000";
//echo "Querying: " . $query . "<br/>";   
    $tickets = array();
    $msg = new xmlrpcmsg("ticket.query", array(new xmlrpcval($query, "string")));
    $ticketsVal = $this->client->send($msg, 1000)->value();
    if ($ticketsVal) {
      $numTickets = $ticketsVal->arraysize();
      for ($i = 0; $i < $numTickets; $i++) {
        $ticketNum = $ticketsVal->arraymem($i)->scalarval();
        $ticketStruct = $this->getTicketStruct($ticketNum);
        array_push($tickets, $this->parseTicket($ticketNum, $ticketStruct));
      }
    }
    return $tickets; 
  }

  /**
  * @return Array of Ticket objects
  */
  public function queryTickets($statusVals, $milestone, $pipeline = NULL, $excludedTypeVals = NULL) {
    $query = "milestone=" . $milestone;
    if ($pipeline) {
      $query = $query . "&pipeline=" . $pipeline;
    }
    if ($excludedTypeVals) {
      foreach ($excludedTypeVals as $excludedStatus) {
        $query = $query . "&type=!" . $excludedStatus;
      }
    }
    $query = $query . "&order=priority&desc=1";
    foreach ($statusVals as $status) {
      $query = $query . "&status=" . $status;
    }
    return $this->queryTicketsRaw($query);
  }

  /**
  * @return Array of Ticket objects
  */
  public function getTicketsNotStarted($milestone, $pipeline = NULL, $excludedTypeVals = NULL) {
    $statusVals = array("new", "assigned");
    return $this->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
  }

  /**
  * @return Array of Ticket objects
  */
  public function getTicketsInTest($milestone, $pipeline = NULL, $excludedTypeVals = NULL) {
    $statusVals = array("ready_for_test", "in_test");
    return $this->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
  }

  /**
  * @return Array of Ticket objects
  */
  public function getTicketsInDev($milestone, $pipeline = NULL, $excludedTypeVals = NULL) {
    $statusVals = array("in_work", "accepted", "waiting_for_build", "reopened");
    return $this->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
  }

  /**
  * @return Array of Ticket objects
  */
  public function getTicketsInMilestone($milestone, $pipeline = NULL, $excludedTypeVals = NULL, $statusVals = NULL) {
    if (!$statusVals) {
      $statusVals = array("in_work", "accepted", "waiting_for_build", "reopened", "ready_for_test", "closed", "in_test");
    }
    return $this->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
  }

  /**
  * @return Array of Ticket objects
  */
  public function getNonBlockingTicketsInMilestone($milestone, $pipeline = NULL, $excludedTypeVals = NULL, $statusVals = NULL) {
    $query = "milestone=" . $milestone;
    if ($pipeline) {
      $query = $query . "&pipeline=" . $pipeline;
    }
    if ($excludedTypeVals) {
      foreach ($excludedTypeVals as $excludedStatus) {
        $query = $query . "&type=!" . $excludedStatus;
      }
    }
    $query = $query . "&order=priority&desc=1";
    if (!$statusVals) {
      $statusVals = array("new", "assigned", "in_work", "accepted", "waiting_for_build", "reopened", "ready_for_test", "closed", "in_test");
    }
    foreach ($statusVals as $status) {
      $query = $query . "&status=" . $status;
    }
    $query = $query . "&blocking=";
    return $this->queryTicketsRaw($query);
  }
  
  /**
  * @return Array of Ticket objects
  */
  public function getDependentTickets($ticketId, $excludedTypeVals = NULL) {
    if ($ticketId == 0) {
      $query = "blocking=";
    } else {
      $query = "blocking=~" . $ticketId;
    }
    if ($excludedTypeVals) {
      foreach ($excludedTypeVals as $excludedStatus) {
        $query = $query . "&type=!" . $excludedStatus;
      }
    }
    $query = $query . "&order=priority&desc=1";
    return $this->queryTicketsRaw($query);
  }

  /**
  * @return A matrix of tickets by their state: not-started, in-dev, in-test, completed 
  */
  public function matrixTicketsByField($tickets, $fieldName) {
    $matrix = array();
    foreach($tickets as $ticket) {
      $fieldVal = $ticket->$fieldName;
      if (!isset($matrix[$fieldVal])) {
        $matrix[$fieldVal] = array();
      } 
      array_push($matrix[$fieldVal], $ticket);
    }
    return $matrix;
  }

  /**
  * @return Array of Ticket objects
  */
  public function getTicketsCompleted($milestone, $pipeline = NULL, $excludedTypeVals = NULL) {
    $statusVals = array("closed");
    return $this->queryTickets($statusVals, $milestone, $pipeline, $excludedTypeVals);
  }
  
  /**
  * @return A Ticket object, NULL if unavail
  */
  public function getTicket($ticketId, $extendedInfo = FALSE) {
    $ticketStruct = $this->getTicketStruct($ticketId);
    if ($ticketStruct) {
      return $this->parseTicket($ticketId, $ticketStruct, $extendedInfo);
    }
    return NULL;
  } 

  /**
  * Is the specified  milestone a dated milestone, in the future?
  *
  * @return TRUE if the milestone is one that has a date in the future.  FALSE if its in the past or not a date milestone.
  */
  public function isFutureDatedMilestone($milestoneName) {
    $now = time();
    if ($this->isDatedMilestone($milestoneName)) {
      $milestone = strtotime(substr($milestoneName, 0, 4) . "/" . substr($milestoneName, 4, 2) . "/" . substr($milestoneName, 6, 2));
      //echo "Milestone " . $milestoneName . " IS dated" . "<br/>";
      //echo "Now: " . $now . ", milestone: " . $milestone . "<br/>";
      return ($milestone > ($now - 604800)); // 1 week
    }
    return FALSE;
  }

  /**
  * Is the specified milestone a dated milestone?
  */
  public function isDatedMilestone($milestoneName) {
    if (strpos($milestoneName, "20") == 0) {
      //if (strlen($milestoneName) == 8) {
        return TRUE;
      //}
    }
    return FALSE;
  }

  public function getNextMilestone() {
    $milestones = $this->getMilestones(TRUE, TRUE);
    return $milestones[0];
  }
  
  /**
  * Gets all milestones in the system, with some criteria.
  * 
  * @return An array of milestone names
  */
  public function getMilestones($datedOnly = TRUE, $noPastDates = TRUE) {
    $milestoneNames = array();
    $msg = new xmlrpcmsg("ticket.milestone.getAll", array());
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      $numMilestones = $val->arraysize();
      for ($i = 0; $i < $numMilestones; $i++) {
        $milestoneName = $val->arraymem($i)->scalarval();
        $isDated = $this->isDatedMilestone($milestoneName);
        if (!$datedOnly || $isDated) {
          if ($noPastDates && $isDated && !$this->isFutureDatedMilestone($milestoneName))
            continue;
          array_push($milestoneNames, $milestoneName);
        }
      }
    }
    return $milestoneNames;
  } 

  /**
  * Gets all components in the system.
  * 
  * @return An array of component names
  */
  public function getComponents() {
    $componentNames = array();
    $msg = new xmlrpcmsg("ticket.component.getAll", array());
    $val = $this->client->send($msg, 1000)->value();
    if ($val) {
      $numComponents = $val->arraysize();
      for ($i = 0; $i < $numComponents; $i++) {
        array_push($componentNames, $val->arraymem($i)->scalarval());
      }
    }
    return $componentNames;
  } 

  /**
  * @return FALSE if error
  */
  private function getTicketStruct($ticketId) {
    $ticketStruct = NULL;
    $msg = new xmlrpcmsg("ticket.get", array(new xmlrpcval($ticketId, "int")));
    $ticketVal = $this->client->send($msg, 1000)->value();
    if ($ticketVal) {
      $ticketStruct = $ticketVal->arraymem(3);
    }
    return $ticketStruct;
  }

  /**
  * @return a Ticket or TicketEx object, depending
  */
  private function parseTicket($ticketNum, $ticketStruct, $extendedInfo = FALSE) {
    if ($extendedInfo) $ticket = new TicketEx();
    else $ticket = new Ticket();
    $ticket->id = $ticketNum;
    $ticket->summary = $ticketStruct->structmem("summary")->scalarval();
    $ticket->url = $this->tracBase . "/ticket/" . $ticket->id;
      $ticket->type = $ticketStruct->structmem("type")->scalarval();
      $ticket->priority = $ticketStruct->structmem("priority")->scalarval();
      if ($ticketStruct->structmem("scope")) {
      $ticket->scope = $ticketStruct->structmem("scope")->scalarval();
    } else {
      $ticket->scope = "n/a";
    }
      $ticket->component = $ticketStruct->structmem("component")->scalarval();
      if ($ticketStruct->structmem("owner")) {
      $ticket->owner = $ticketStruct->structmem("owner")->scalarval();
    } else {
      $ticket->owner = "none";
    }
      if ($ticketStruct->structmem("reporter")) {
      $ticket->reporter = $ticketStruct->structmem("reporter")->scalarval();
    } else {
      $ticket->reporter = "unknown";
    }
      if ($ticketStruct->structmem("milestone")) {
      $ticket->milestone = $ticketStruct->structmem("milestone")->scalarval();
    } else {
      $ticket->milestone = "TBD";
    }
      if ($ticketStruct->structmem("pipeline")) {
      $ticket->pipeline = $ticketStruct->structmem("pipeline")->scalarval();
    } else {
      $ticket->milestone = "TBD";
    }
      if ($ticketStruct->structmem("blocking")) {
      $ticket->blocking = $ticketStruct->structmem("blocking")->scalarval();
    } else {
      $ticket->blocking = "";
    }
      if ($ticketStruct->structmem("blockedby")) {
      $ticket->blockedBy = $ticketStruct->structmem("blockedby")->scalarval();
    } else {
      $ticket->blockedBy = "";
    }
      $ticket->status = $ticketStruct->structmem("status")->scalarval();
      if ($extendedInfo) {
      $ticket->description = $ticketStruct->structmem("description")->scalarval();
      
      // Get last few comments
      $ticket->changes = $this->getTicketChangelog($ticketNum);
      $ticket->comments = array();
      for($i = 0; $i < sizeof($ticket->changes); $i++) {
        if ($ticket->changes[$i]->field == "comment") {
          $comment = new TicketComment();
          $comment->who = $ticket->changes[$i]->who;
          $comment->comment = $ticket->changes[$i]->newVal;
          array_push($ticket->comments, $comment);
        }
      }     
    }
    return $ticket;
  }

}






