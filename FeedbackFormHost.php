<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/11/17
 * Time: 8:06 AM
 */
?>

<?php
ini_set('display_errors', '1');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FeedbackFormHost.php</title>
    <!-- Import jQuery -->
    <script
            src="https://code.jquery.com/jquery-3.2.1.js"
            integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE="
            crossorigin="anonymous"></script>
    <!--
    Existing portal styles.
    -->
    <style>
        .box, table {
            box-shadow: 1px 2px 4px 0 rgba(66,66,66,.4);
            background-color: #FFF;
        }
        table tbody tr th, table tbody tr td {
            border-top: 1px solid #e2e2e1;
            padding: 10px 10px;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
    </style>
</head>
<body>

<?php include("FeedbackForm.php") ?>
<?php include("FeedbackQuestion.php") ?>
<?php include("FeedbackFormMenuItem.php") ?>

<h1>This is the test host for the feedback form.</h1>

<p>
   The menu item is rendered below.
</p>
<?php
    $menu_item = new \StearnsConnect\FeedbackFormMenuItem('Please Provide Some Feedback');
    $menu_item->setIsFlashy(true);
    echo $menu_item->render();
?>

<?php
    $questions = array(
        new \StearnsConnect\FeedbackQuestion("one", "Overall Customer Experience"),
        new \StearnsConnect\FeedbackQuestion("two", "Customer Portal Experience"),
        new \StearnsConnect\FeedbackQuestion("three","Service from the Stearns Bank Loan Team"),
        new \StearnsConnect\FeedbackQuestion("four","How likely are you to recommend Stearns Bank to a friend?"),
    );
    $ff = new \StearnsConnect\FeedbackForm($questions, 7);
    // Set the base URL.
    // ie. http://192.168.110.40/stony-moon/ (*with* the trailing slash)
    $ff->setBaseUrl("./"); // Set the base URL for relative links.
    $ff->setFormHandler("FeedbackFormHandler.php");
    $ff->setIsFlashy(true);
    $ff->setReferenceId('abcd1234f');
    echo $ff->render();
?>

</body>
</html>

