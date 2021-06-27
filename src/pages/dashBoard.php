<?php
session_start();
include("../helpers/dbConnection.php");

$error = false;
$message = "";
$getNewValue = false;
$result = [];
$connection = dbConnection();
$avg = 0;



if(isset($_POST['delete'])) {
    global $connection;
    $sql = "EXEC deleteGrade @id=:id";
    $build = $connection->prepare($sql);
    $build->execute(array(':id' =>  $_POST['delete']));
}

if(isset($_POST['update']) && !isset($_POST["updatedGrade"])) {
    try{
    $_SESSION["gradeIndex"] = $_POST['update'];
    ?>
    <div class="updateGrade">
        <form target="_self" method="post" name="newGrade">
            <div>
                <input placeholder="Neue Note" type="number" step="any" name="updatedGrade" id="updatedGrade" class="field2">
            </div>
            <div>
               <button type="submit" class="button2">Aktualisieren</button>
            </div>
        </form>
    </div>
<?php
    } catch (Exception $e) {
        $error = true;
        $message = "Überprüfen Sie Ihre Eingaben";
    }
}

if(isset($_POST["updatedGrade"])) {
    Try {
        global $connection, $result;
        $res = $_SESSION['allGrades'];
        $req = $res[$_SESSION['gradeIndex']];
        $sql = "EXEC addGrade @note=:note, @fach=:fach, @userId=:userId, @credits=:credits";
        $build = $connection->prepare($sql);
        $build->execute(array(
                ':note' => $_POST["updatedGrade"],
                ':fach' => $req['fach'],
                ':userId' => $req['nutzer'],
                ':credits' => $req['credits']
        ));
    }catch (Exception $e) {
        $error = true;
        $message = "Überprüfen Sie Ihre neu eingegebene Note";
    }
}

function getGrades() {
    try {
        //Get grade
        global $result, $connection, $error, $message;
        $sql = "EXECUTE getGrades @userId=:userId";
        $build = $connection->prepare($sql);
        $build->execute(array(':userId' => intval($_SESSION["userId"])));
        while ($grades = $build->fetch()) {
            array_push($result, [
                'note' => $grades["note"],
                'id' => $grades["id"],
                'durchgefallen' => $grades["durchgefallen"],
                'credits' => $grades["credits"],
                'fach' => $grades["fach"],
                'versuch' => $grades["versuch"],
                'nutzer' => $grades['nutzer']
            ]);
        }
        $_SESSION['allGrades'] = $result;
    } catch (Exception $e) {
        $error = true;
        $message = "Server-Error";
    }
}


function getAvg() {
    try {
        global $connection, $avg, $error, $message;
        $sql = "EXECUTE getAvg @userId=:userId";
        $build = $connection->prepare($sql);
        $build->execute(array(':userId' => intval($_SESSION["userId"])));
        $res = $build->fetch();
        $avg = round($res["durchschnitt"], 2);
    }catch (Exception $e) {
        $error = true;
        $message = "Server-Error";
    }
}

if(isset($_POST['fach']) && isset($_POST['note']) && isset($_POST['credits'])) {
    try {
        $data = array(
            'fach' => $_POST["fach"],
            'note' => $_POST["note"],
            'credits' => intval($_POST["credits"]),
            'userId' => intval($_SESSION["userId"])
        );
        //Set grade
        $sql = "EXEC addGrade @note=:note, @fach=:fach, @userId=:userId, @credits=:credits";
        $build = $connection->prepare($sql);
        $build->execute($data);
        $getNewValue = true;
    } catch (Exception $e) {
        $error = "true";
        $message = "Überprüfen Sie Ihre Eingaben bei der Notenvergabe";
    }
}


if (isset($_POST["logOut"])) {
    header("location: ../index.php");
}

include("../helpers/includes.php");
?>
<body>
    <div class="durch">
        <strong style="color: white";>Durchschnitt</strong>
    </div>
    <div class="logOut">
        <form target="_self" method="post">
            <button id="logOut" name="logOut" class="logOutButton">LogOut</button>
        </form>
    </div>
    <div class="logOut">
        <?php include("./logOut.php"); ?>
    </div>
    <div class="durch">
        <strong style="color: white;"> <?php
        getAvg();
        echo($avg)
        ?></strong>
    </div>
    <div class="display2">
        <form target="_self" method="post">
            <div class="flexChild">
                <input id="fach" name="fach" type="text" placeholder="fach" class="field">
            </div>
            <div class="flexChild">
                <input id="note" name="note" type="number" step="any" placeholder="note" class="field">
            </div>
            <div class="flexChild">
                <input id="credits" name="credits" type="number" placeholder="credits" class="field">
            </div>
            <div class="flexChild">
                <button type="submit" class="button"><strong>Note hinzufügen</strong></button>
            </div>
        </form>
    </div>
    <div class="display">
        <div class="divProp">
            <strong>FACH</strong>
        </div>
        <div class="divProp">
            <strong>Credits</strong>
        </div>
        <div class="divProp">
            <strong>Versuch</strong>
        </div>
        <div class="divProp">
            <strong>Note</strong>
        </div>
    </div>
    <?php
    getGrades();
    foreach ($result as $key => $item) { ?>
    <div class="display">
        <div class="divProp">
            <?php echo($item["fach"]) ?>
        </div>
        <div class="divProp">
            <?php echo($item["credits"]) ?>
        </div>
        <div class="divProp">
            <?php echo($item["versuch"]) ?>
        </div>
        <div class="<?php if($item["durchgefallen"] == 1) echo "divFail"; if($item["durchgefallen"] == 0) echo "divProp" ?>">
             <?php echo($item["note"]) ?>
        </div>
        <form target="_self" method="post">
            <div class="configFlex">
                <div class="config">
                    <button type="submit" class="button3" name="update" value=<?php echo $key; ?>>Update</button>
                </div>
                <div class="config">
                    <button type="submit" class="button4" style="background-color: #b76666" value="<?php echo $item["id"]; ?>" name="delete">Delete</button>
                </div>
            </div>
        </form>
    </div>
    <?php }
    include("../helpers/errorHandler.php");
    handler($error, $message, 2);
    ?>
</body>