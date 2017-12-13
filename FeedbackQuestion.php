<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/11/17
 * Time: 11:51 AM
 */

namespace StearnsConnect;


/**
 * This class represents a single question.
 * @package StearnsConnect
 */
class FeedbackQuestion
{
    /**
     * @var string $id uniquely identifies this question
     */
    private $id;

    /**
     * @var string $text the question text
     */
    private $text;

    /**
     * FeedbackQuestion constructor.
     * @param string $text the question text
     */
    function __construct($id, $text) {
        $this->id = $id;
        $this->text = $text;
    }

    /**
     * Get the question's identifier.
     * @return string the question's identifier
     */
    function getId(){
        return $this->id;
    }

    /**
     * Get the question text.
     * @return string the question text
     */
    function getText() {
        return $this->text;
    }
}