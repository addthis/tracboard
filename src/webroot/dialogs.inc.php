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
?>

<div class="dialog-wrapper"></div>
<div id="add-ticket-dlg">
  <div class="content-outer">
    <div class="content-inner">
      <label>Type: <?php echo buildTypeChoiceMarkup() ?></label>
      <label>Priority: <?php echo buildPriorityChoiceMarkup() ?></label>
      <label>To: <?php echo buildOwnerChoiceMarkup() ?></label>
      <div class="clearer"></div>
      <label>Component: <?php echo buildComponentChoiceMarkup($trac) ?></label>
      <label>Scope: <?php echo buildScopeChoiceMarkup() ?></label>
      <div class="clearer"></div>
      <label>Blocking: <input id="ticket-blocking" type="text" size="14"></input></label>
      <label>Blocked by: <input id="ticket-blocked-by" type="text" size="14"></input></label>
      <div class="clearer"></div>
      <textarea id="ticket-summary" name="ticketSummary" class="ticket-summary" cols="35" rows="3" style="width: 100%;"></textarea><div class="clearer"></div>
      <div style="text-align: right; padding-top: 10px;">
        <input type="button" id="ticket-create-in-trac" name="openInTrac" value="Use Trac&apos;s Form..." />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="ticket-create" name="createTicket" value="Create Now" />&nbsp;&nbsp;<input type="button" id="ticket-cancel" name="ticketCancel" value="Cancel"/>
      </div>
    </div>
  </div>
</div>

<div id="edit-ticket-dlg">
  <div class="content-outer">
    <div class="content-inner">
      <div style="text-align: right; padding-bottom: 10px; border-bottom: 1px solid #ADADAD; width: 100%; margin-bottom: 10px;">
        Quick Actions:
        <span id="backlog-ticket-span" style="padding: 5px;"><input type="button" id="backlog-ticket" name="backlogTicket" value="Defer to Backlog"/></span>
        <span id="complete-ticket-span"style="padding: 5px;"><input type="button" id="complete-ticket" name="completeTicket" value="Mark Completed" /></span>
        <span id="reopen-ticket-span"style="padding: 5px;"><input type="button" id="reopen-ticket" name="reopenTicket" value="Reopen"/></span>
      </div>
      <label>Type: <?php echo buildTypeChoiceMarkup() ?></label>
      <label>Priority: <?php echo buildPriorityChoiceMarkup() ?></label>
      <label>To: <?php echo buildOwnerChoiceMarkup() ?></label>
      <div class="clearer"></div>
      <label>Component: <?php echo buildComponentChoiceMarkup($trac) ?></label>
      <label>Scope: <?php echo buildScopeChoiceMarkup() ?></label>
      <div class="clearer"></div>
      <label>Blocking: <input id="ticket-blocking" type="text" size="14"></input></label>
      <label>Blocked by: <input id="ticket-blocked-by" type="text" size="14"></input></label>
      <div class="clearer"></div>
      <textarea name="summary" id="ticket-summary" cols="35" rows="3" style="width: 100%;"></textarea><div class="clearer"></div>
      <div style="text-align: right; padding-top: 10px;">
        <input type="button" id="ticket-edit" name="editTicket" value="Done"/>&nbsp;&nbsp;<input type="button" id="ticket-cancel" name="ticketCancel" value="Cancel" />
      </div>
    </div>
  </div>
</div>