<?php

$nav = "swim";
$scriptFiles = array("js/swim.js");
include("pagetop.inc.php");
include("dialogs.inc.php");
require_once("utils.inc.php");

// The filter-set controls
echo buildFilterSetMarkup($trac, TRUE);

function swimlaneHeader($name) {
?>
        <div class="swimlane-bg-main">
            <div class="swimlane-bg-storycolumn">
                <div class="swimlane-bg-storycolumn-header"><?php echo $name ?></div>
            </div>
            <div class="swimlane-bg-ticketsarea">
            </div>
    </div>
<?php
}

// The main container
?>

<div id="container">
    <div class="swimlane-container">
        <?php
        swimlaneHeader("Active Stories");
        ?>
        <div id="active" class="swimlane-lanes"></div>
    </div>
    <div class="swimlane-container">
        <?php
        swimlaneHeader("Completed Stories");
        ?>
        <div id="completed" class="swimlane-lanes swimlane-lanes-completed"></div>
    </div>
    <div class="swimlane-container">
        <?php
        swimlaneHeader("Storyless Tickets");
        ?>
        <div id="orphaned" class="swimlane-lanes swimlane-lanes-orphaned"></div>
    </div>
    <div class="clearer"></div>
</div>

</body>


<?php
include "footer.inc.php";

?>
