<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/18/17
 * Time: 10:02 AM
 */

namespace StearnsConnect;

/**
 * Instances of this class render a menu item that is associated with a feedback form.
 *
 * @package StearnsConnect
 */
class FeedbackFormMenuItem
{
    /**
     * @var string the ID of the associated form
     */
    private $form_id;

    /**
     * @var string the text that appears
     */
    private $text;

    /**
     * @var bool Should the link flash to get a user's attention?
     */
    private $isFlashy = false;

    /**
     * FeedbackFormMenuItem constructor.
     * @param string $text the text that appears
     * @param null $form_id the ID of the associated form
     */
    function __construct($text='Provide Feedback', $form_id=null)
    {
        $this->form_id = is_null($form_id) ? 'feedback_form' : $form_id;
        $this->text = $text;
    }

    /**
     * Get the menu item's text.
     *
     * @return string the menu item's text
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Set the menu item's text.
     *
     * @param $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Should the form flash to get a user's attention?
     * @return bool
     */
    public function getIsFlashy() {
        return $this->isFlashy;
    }

    /**
     * Should the form flash to get a user's attention?
     * @param bool $isFlashy
     */
    public function setIsFlashy($isFlashy) {
        $this->isFlashy = $isFlashy;
    }

    /**
     * Render the menu item's HTML.
     *
     * @return string
     */
    function render(){
        // The CSS class applied to the span depends upon whether or not we're supposed to be flashing.
        $class_name = $this->isFlashy ? 'feedback-form-menu-item-flashing' : 'feedback-form-menu-item';
        // The ID of the element is based on the form's ID.
        $span_id = $this->form_id . '--menu-item';
        // Let's put the HTML together!
        $html = "<a id=\"{$span_id}\" class=\"{$class_name}\">{$this->text}</a>";
        // Simple, eh?
        return $html;
    }


}