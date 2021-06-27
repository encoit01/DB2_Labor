<?php
function handler($error, $msg, $zahl) {
    if($zahl == 1) {
        include("./helpers/includes.php");
    }else{
        include("../helpers/includes.php");
    }
    if($error == true) {
        ?>
        <div class="errorBox">
            <?php echo($msg) ?>
        </div>
        <?php
    }
}
?>