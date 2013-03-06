<?php
ob_start();

$nav = "sprint";
$scriptFiles = array("js/sprint.js");
include("pagetop.inc.php");
include("dialogs.inc.php");
require_once("utils.inc.php");

// Params
$pipeline = urlParam('pipeline', 'addthis', true, true);
$excludedTypesStr = urlParam('notypes', "");
if ($excludedTypesStr == "") {
    $excludedTypes = array();
} else {
    $excludedTypes = explode(",", $excludedTypesStr);
}
$grouping = urlParam('grouping', 'none', true, true);
$coloring = urlParam('coloring', "uniform", true, true);
$highlightCond = urlParam('highlight', "none", true, true);
$expandedStr = urlParam('expanded', "0,0,0,0", true, true);
$displayType = urlParam('display', "" + TICKET_CARD, true, true);
$milestone = urlParam('milestone', $milestone=$trac->getNextMilestone(), true, true);
$expanded = explode(",", $expandedStr);

?>

<script type="text/javascript">
    var filters = {
        pipeline: "<?php echo $pipeline ?>",
        milestone: "<?php echo $milestone ?>",
        excludedTypes: [
        <?php
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
        expanded: ["<?php $expanded[0] ?>", "<?php $expanded[1] ?>", "<?php $expanded[2] ?>", "<?php $expanded[3] ?>"]
    };

</script>

<div id="filterset">
    <ul>
        <li>Sprint:
            <?php echo buildPipelineChoiceMarkup($pipeline) ?>
            <select name="sprint">
                <?php
                $milestones = $trac->getMilestones();
                foreach ($milestones as $ms) {
                    if($ms==$milestone)
                        echo '<option value="'.$ms.'" selected="selected">'.$ms.'</option>';
                    else
                        echo '<option value="'.$ms.'">'.$ms.'</option>';
                }
                ?>
            </select></li>
      <li>Group: <?php echo buildGroupingMarkup($grouping) ?></li>
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
    </ul>
     <div style="float:right">
        <a class="page-reload" title="Reload this view..." href="" onclick="javascript:refreshAllColumns();return false;"><img src="/images/refresh.png"></img></a>
    </div>
    <div class="clearer"></div>
    <div id="colorkey-container" style="display:none">
</div>
</div>

<div id="container">

<?php
    echo buildMilestonePhaseColumn($trac, $pipeline, $milestone, "not-started", $displayType, $grouping, $excludedTypes, true, $coloring, $expanded[0], $highlightCond);
    echo buildMilestonePhaseColumn($trac, $pipeline, $milestone, "in-dev", $displayType, $grouping, $excludedTypes, true, $coloring, $expanded[1], $highlightCond);
    echo buildMilestonePhaseColumn($trac, $pipeline, $milestone, "in-test", $displayType, $grouping, $excludedTypes, true, $coloring, ($expanded[2] == "1"), $highlightCond);
    echo buildMilestonePhaseColumn($trac, $pipeline, $milestone, "done", $displayType, $grouping, $excludedTypes, true, $coloring, ($expanded[3] == "1"), $highlightCond);

?>
    <div class="clearer"></div>
</div>



<!--
<input type="button" id="fixHeights" value="fix heights" />
-->

<script type="text/javascript">
/*
    $("#fixHeights").click(function() {
        fixHeights();
    });
*/
</script>
</body>



<?php
include "footer.inc.php";

?>
