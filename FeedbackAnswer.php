<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/13/17
 * Time: 10:43 AM
 */

namespace StearnsConnect;

/**
 * Class FeedbackAnswer
 * @package StearnsConnect
 */
class FeedbackAnswer
{

    private $id;
    private $rating;
    private $comment;

    function __construct($id, $rating, $comment) {
        $this->id = $id;
        $this->rating = $rating;
        $this->comment = $comment;
    }

    public function getId() {
        return $this->id;
    }

    public function getRating() {
        return $this->rating;
    }

    public function getComment() {
        return $this->comment;
    }

    /**
     * Convert the object to a simple associative array.
     * @return array the associative array.
     */
    public function toArray() {
        return array('id'=> $this->id, 'rating' => $this->rating, 'comment' => $this->comment);
    }
}