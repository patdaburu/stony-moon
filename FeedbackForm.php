<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/11/17
 * Time: 8:04 AM
 */

namespace StearnsConnect;

/**
 * Instances of this class render a feedback form.
 *
 * @package StearnsConnect
 */
class FeedbackForm
{
    /**
     * @var string the (in-memory) ID of the form
     */
    private $id;
    /**
     * @var array the questions presented in the form
     */
    private $questions = array();
    /**
     * @var int the ratings scale
     */
    private $scale;
    /**
     * @var string the template used to generate the HTML output
     */
    private $html_template;
    /**
     * @var string the contents of the Javascript file
     */
    private $js;
    /**
     * @var string the contents of the CSS file
     */
    private $css;
    /**
     * @var bool Is inline CSS forbidden?
     */
    private $no_inline_css = true;
    /**
     * @var bool Is inline Javascript forbidden?
     */
    private $no_inline_js = true;

    /**
     * @var string the base URL for relative links
     */
    private $baseUrl = './';

    /**
     * @var bool Should the form call attention to itself?
     */
    private $isFlashy = false;

    /**
     * @var bool Include debugging output?
     */
    private $debug = false;

    /**
     * @var string A reference ID for posts.
     */
    private $reference_id = null;

    /**
     * @var string the CSRF token
     */
    private $csrf_token = null;

    /**
     * @var string the name of the form handler
     */
    private $form_handler = "FeedbackFormHandler.php";

    /**
     * FeedbackForm constructor.
     * @param array $questions an array of FeedbackQuestion objects that define the form
     * @param int $scale the ratings scale
     * @param null $id the form's ID (to distinguish it from other forms, if any, that may reside on the same page)
     * @param bool $no_inline_css Is inline CSS forbidden?
     * @param bool $no_inline_js Is inline Javascript forbidden?
     */
    function __construct(array $questions,
                         $scale=7,
                         $id=null,
                         $no_inline_css=true,
                         $no_inline_js=true) {
        // Copy each question to the form's array.
        foreach($questions as $question){
            array_push($this->questions, $question);
        }
        // Set the scale.
        $this->scale = $scale;

        // Use the default ID if nothing was passed in.  Otherwise, whatever the caller gave us is the
        // ID.
        $this->id = (is_null($id) ? 'feedback_form' : $id);

        // Set the "inline" flags.
        $this->no_inline_css = $no_inline_css;
        $this->no_inline_js = $no_inline_js;

        // Retrieve the HTML template...
        $this->html_template = file_get_contents(dirname(__FILE__) . "/FeedbackForm.html");
        // ...and the CSS...
        $this->css = file_get_contents(dirname(__FILE__) . "/FeedbackForm.css");
        // ...plus the Javascript as well.
        $this->js = file_get_contents(dirname(__FILE__) . "/FeedbackForm.js");
    }

    /**
     * Set the base URL.
     * @param $baseUrl string the base URL for relative links
     */
    public function setBaseUrl($baseUrl){
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get the base URL.
     * @return string the base URL for relative links
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * Get the value of the debugging flag.
     * @return bool
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * Set the value of the debugging flag.
     * @param bool $debug Are we debugging?
     */
    public function setDebug($debug) {
        $this->debug = $debug;
    }

    /**
     * Get the name of the form handler file.  (This value is appended to
     * the base URL to get the full path to the handler.)
     * @return string
     */
    public function getFormHandler(){
        return $this->form_handler;
    }

    /**
     * Set the name of the form handler file.  (This value is appended to
     * the base URL to get the full path to the handler.)
     * @param $form_handler
     */
    public function setFormHandler($form_handler){
        $this->form_handler = $form_handler;
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
     * Set a reference ID that can be used when handling form submission.
     * @param $reference_id
     */
    public function setReferenceId($reference_id){
        $this->reference_id = $reference_id;
    }

    /**
     * Get a reference ID that will be used when handling form submission.
     * @return string
     */
    public function getReferenceId() {
        return $this->reference_id;
    }

    /**
     * Set the CSRF token that can be submitted with the form data.
     * @param $token
     */
    public function setCsrfToken($token){
        $this->csrf_token = $token;
    }

    /**
     * Get the CSRF token that can be submitted with the form data.
     * @return string
     */
    public function getCsrfToken() {
        return $this->csrf_token;
    }

    /**
     * Render the form's HTML.
     *
     * @return string
     */
    function render(){

        // Create the metadata object.
        $meta = array(
            "form_id"=>$this->id,
            "reference_id"=>$this->reference_id,
            "csrf_token"=>$this->csrf_token,
            "scale"=>$this->scale,
            "no_inline_js"=>$this->no_inline_js,
            "question_ids"=>array(),
            "base_url"=>$this->baseUrl,
            "debug"=>$this->debug,
            "form_handler"=>$this->baseUrl . $this->form_handler);

        // Configure the general HTML from the template.
        $html = $this->html_template;

        // Perform replacements on all the tokens in the HTML.
        $html = preg_replace('/\[form-id\]/i', $this->id, $html);
        // If inline CSS is forbidden, we'll just remove the CSS tag.
        // Otherwise we'll replace it with the CSS.
        $html = preg_replace(
            '/\[css\]/i',
            $this->no_inline_css ?
                '<link rel="stylesheet" type="text/css" href="' . $this->baseUrl . 'FeedbackForm.css">' :
                '<style type="text/css">' . $this->css . '</style>',
            $html);
        $html = preg_replace(
            '/\[js\]/i',
            $this->no_inline_js ?
                '<script type="text/javascript" src="'. $this->baseUrl . 'FeedbackForm.js"></script>' :
                '<script>' .$this->js . '</script>',
            $html);
        // Replace the base URL tokens in the HTMl with the actual base URL.
        $html = preg_replace('/\[base-url\]/i', $this->baseUrl, $html);

        // Construct the HTML for the questions.
        $questions = '';
        foreach($this->questions as $question) {
            // Append information to the meta-data object.
            array_push($meta['question_ids'], $question->getId());
            $questions .= $this->question_to_html($question);
        }
        $html = preg_replace('/\[questions\]/i', $questions, $html);

        // Render the button to open the form.
        $link_class = $this->isFlashy ? 'feedback-form-link-flashing' : 'feedback-form-link';
        // NOTE:  The circular feedback form link has been hidden.  It can be shown again by removing the div
        // that wraps it.
        $html .= "
        <div class=\"feedback-form-hidden\">
            <div class=\"{$link_class}\" id=\"{$this->id}--open\">
                <img src=\"{$this->baseUrl}FeedbackFormLink.svg\" class=\"feedback-form-link-img\"/>
            </div>
        </div>
        ";

        // Add the "Running Man" elements.
        $running_man_class = $this->isFlashy ? 'feedback-form-running-man' : 'feedback-form-hidden';
        $feedback_bubble_class = $this->isFlashy ? 'feedback-form-feedback-bubble' : 'feedback-form-hidden';
        $html .= "
        <div id=\"{$this->id}--running-man\" class=\"{$running_man_class}\">
            <img src=\"{$this->baseUrl}runningman.png\">
        </div>
        <div id=\"{$this->id}--feedback-bubble\" class=\"{$feedback_bubble_class}\">
            <img src=\"{$this->baseUrl}feedbackbubble.png\">
        </div>
        ";

        // Construct the metadata element.
        $meta_html = '<div id="feedback-form-metadata" class="feedback-form-metadata">' .
            json_encode($meta) . '</div>';
        // Replace the meta tag in the HTML template.
        $html = preg_replace('/\[meta\]/i', $meta_html, $html);
        // That's that.
        return $html;
    }

    /**
     * Construct the HTML for a given feedback question.
     *
     * @param FeedbackQuestion $feedbackQuestion the feedback question
     * @return string the HTML
     */
    private function question_to_html($feedbackQuestion) {
        // Start the HTML.
        $html  = '<table class="feedback-form-question" border="0"><tbody>';
        $html .= '<tr class="feedback-form-tr"><td colspan="' . $this->scale . '" class="feedback-form-question-text-cell">';
        $html .= '<p class="feedback-form-question-text">' . $feedbackQuestion->getText() . "</p>";
        $html .= '</td></tr>';

        // Add the rating numbers.
        $html .= '<tr>';
        foreach(range(1, $this->scale) as $rating){
            $id = $this->id . ':' . $feedbackQuestion->getId() . ':rating:' . $rating;
            // Let's figure out what the inline "onclick" handlers are going to look like.
            $onclick = $this->no_inline_js ?
                '' :
                "onclick=\"FeedbackForm . setRating('{$this->id}', '{$feedbackQuestion->getId()}', {$rating}, {$this->scale})\"";
            $html .= "
                <td id=\"{$id}\"
                    class=\"feedback-form-rating-number\" $onclick>{$rating}</td>
            ";
        }
        $html .= '</tr>';

        // Add the stars.
        $html .= '<tr>';
        foreach(range(1, $this->scale) as $rating) {
            $id = $this->id . ':' . $feedbackQuestion->getId() . ':star:' . $rating;
            // Let's figure out what the inline "onclick" handlers are going to look like.
            $onclick = $this->no_inline_js ?
                '' :
                "onclick=\"FeedbackForm . setRating('{$this->id}', '{$feedbackQuestion->getId()}', {$rating}, {$this->scale})\"";
            $html .= "
                <td id=\"{$id}\"
                    class=\"feedback-form-rating-star-off\" $onclick>&starf;</td>
            ";
        }
        $html .= '</tr>';

        // Add the Comment field.
        $html .= '<tr class="feedback-form-tr"><td colspan="' . $this->scale . '" class="feedback-form-comments-cell">';
        $html .= '<table border="0" width="100%" class="feedback-form-comments-table"><tbody>';
        $html .= '<tr>';
        $html .= '<td class="feedback-form-comments-label">Comments</td>'; // the label
        $onkeyup = $this->no_inline_js ?
            '' :
            "onkeyup=\"FeedbackForm . setComment('{$this->id}', '{$feedbackQuestion->getId()}')\"";

        $html .= "
            <td class=\"feedback-form-comments-input-cell\">
            <textarea maxlength=\"250\" data-autoresize rows=\"1\" class=\"feedback-form-comments-input\" 
                id=\"{$this->id}:{$feedbackQuestion->getId()}:comment\" {$onkeyup}
                placeholder='(Optional)'
                ></textarea></td>
        "; // the text area
        $html .= '</tr>';
        $html .= '</tbody></table>';
        $html .= '</td></tr>';
        $html .= '</tbody></table>';
        return $html;
    }
}

