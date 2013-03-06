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

ob_start();

$nav = "plan";
$scriptFiles = array("js/plan.js");
include "pagetop.inc.php";
include "dialogs.inc.php";
require_once('config.inc.php');
require_once("utils.inc.php");

// Params
$pipeline = urlParam('pipeline', 'addthis', true, true);
$views = urlParam('views', "backlog," . $trac->getNextMilestone());
$excludedTypesStr = urlParam('notypes', "");
if ($excludedTypesStr == "") {
  $excludedTypes = array();
} else {
  $excludedTypes = explode(",", $excludedTypesStr);
}
$grouping = urlParam('grouping', 'none', true, true);
$coloring = urlParam('coloring', "uniform", true);
$highlightCond = urlParam('highlight', "none", true);
$expandedStr = urlParam('expanded', "1,0", true);
$displayType = urlParam('display', "" + TICKET_CARD, true);
$expanded = explode(",", $expandedStr);

// silly
$milestonesViewed = explode(",", $views);
$milestonesNotViewed = $trac->getMilestones();
array_push($milestonesNotViewed, "backlog");
foreach($milestonesViewed as $milestoneViewed) {

  // Make sure its not in the not-viewed list
  $i = 0;
  foreach($milestonesNotViewed as $milestoneNotViewed) {
    if ($milestoneNotViewed == $milestoneViewed) {

      // remove it from the not-viewed list
      array_splice($milestonesNotViewed, $i, 1);
    }
    $i++;
  }
}

?>

<script type="text/javascript">
  var filters = {
    pipeline: "<?php echo $pipeline ?>",
    excludedTypes: [
    <?
    $numVals = sizeof($excludedTypes);
    $i = 0;
    for($i = 0; $i < $numVals; $i++) {
      echo '"' . $excludedTypes[$i] . '"';
      if ($i < ($numVals-1)) echo ",";
    }
    ?>
    ],
    grouping: "<?php echo $grouping ?>",
    coloring: "<?php echo $coloring ?>",
    highlightCond: "<?php echo $highlightCond ?>",
    displayType: "<?php echo $displayType ?>",
    expanded: [
    <?
    $numVals = sizeof($expanded);
    $i = 0;
    for($i = 0; $i < $numVals; $i++) {
      echo '"' . $expanded[$i] . '"';
      if ($i < ($numVals-1)) echo ",";
    }
    ?>
    ]
  };
  var views =[
  <?php
    $numViews = sizeof($milestonesViewed);
    for($i = 0; $i < $numViews; $i++) {
      echo '"' . $milestonesViewed[$i] . '"';
      if ($i < ($numViews-1)) echo ",";
    }
  ?>
  ];
</script>

<div id="filterset">
  <ul>
    <li>Pipeline:
    <?php echo buildPipelineChoiceMarkup($pipeline) ?>
    </li>
   <li>Group: <?php echo buildGroupingMarkup($grouping, TRUE) ?></li>
    <li>Color: <?php echo buildColoringMarkup($coloring) ?></li>
    <li>Highlight: <?php echo buildHighlightMarkup($highlightCond) ?></li>
    <li>Show: <?php echo buildDisplayTypeMarkup($displayType) ?></li>
    <li>Detail:
    <select name="ticketStyle" >
      <option selected="yes" value="standard" >standard</option>
      <option value="compact">compact</option>
      <option value="simple">simple</option>
    </select>
  </li>
    <li>Types: <?php echo buildTypesMarkup($excludedTypes) ?></li>
  <li>

  </li>
  </ul>
   <div style="float:right">
    Admin:&nbsp;
    <a href="" title="Admin Trac milestones..." onclick="javascript:onViewUrl('http://<?php echo TRAC_SERVER ?>/admin/ticket/milestones');return false;">milestones</a>
    /
    <a href="" title="Admin Trac components..." onclick="javascript:onViewUrl('http://<?php echo TRAC_SERVER ?>/admin/ticket/components');return false;">components</a>
    &nbsp;
    &nbsp;
    <a title="Reload this view..." href="" onclick="javascript:refreshAllColumns();return false;">reload</img></a>
  </div>

  <div class="clearer"></div>
  <div id="colorkey-container" style="display:none"></div>
</div>


<div id="container">

<div id="plan-actionbar">
  <?php
  if (sizeof($milestonesNotViewed) > 0) {
  ?>
  Add view:
  <select name="view-to-add">
  <?php
      foreach ($milestonesNotViewed as $ms) {
          echo '<option value="'.$ms.'">'.$ms.'</option>';
      }
    ?>
  </select>
  <input type="button" name="add-view" value="Add" />
  <?php
  } else {
  ?>
  Viewing all future milestones...
  <?php
  }
  ?>
</div>

<?php
  $col = 0;
  foreach($milestonesViewed as $milestoneViewed) {
    if ($milestoneViewed == "backlog") {
        echo buildBacklogColumnHtml($trac, $pipeline, $grouping, $displayType, $excludedTypes, TRUE, $coloring, $expanded[$col], $highlightCond);
    } else {
        echo buildMilestoneColumnHtml($trac, $pipeline, $milestoneViewed, $displayType, $grouping, $excludedTypes, TRUE, $coloring, $expanded[$col], $highlightCond);
    }
    $col = $col + 1;
  }
?>
  <div class="clearer"></div>
</div>

</body>



<?php
include "footer.inc.php";

?>
