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
</head>
<body>

<?php include("FeedbackForm.php") ?>
<?php include("FeedbackQuestion.php") ?>

<h1>This is the test host for the feedback form.</h1>


<?php
    $questions = array(
        new \StearnsConnect\FeedbackQuestion("one", "Overall Customer Experience"),
        new \StearnsConnect\FeedbackQuestion("two", "Customer Portal Experience"),
        new \StearnsConnect\FeedbackQuestion("three","Service from the Stearns Bank Loan Team"),
        new \StearnsConnect\FeedbackQuestion("four","How likely are you to recommend Stearns Bank to a friend?"),
    );
    $ff = new \StearnsConnect\FeedbackForm($questions, 7);
    echo $ff->render();
?>

</body>
</html>

