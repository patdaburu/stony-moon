var FeedbackForm =
{
    /**
     * the master data object containing all form data
     */
    all_forms: {},

    /**
     * all of the form IDs
     */
    form_ids: [],

    /**
     * all of the form meta-data objects, indexed by their IDs
     */
    form_metas: {},

    /**
     * Retrieve the data object for a given form.
     * @param form_id the ID of the form
     * @returns {*} the form data
     */
    getFeedbackFormData : function(form_id) {
        if(!this.all_forms[form_id])
        {
            this.all_forms[form_id] = {};
        }
        return this.all_forms[form_id];
    },

    /**
     * Retrieve the data object for a given question in a particular form.
     * @param form_id the ID of the form
     * @param question_id the ID of the question
     * @returns {rating: number, comment: string} the question data object
     */
    getQuestionData : function(form_id, question_id) {
        form_data = this.getFeedbackFormData(form_id);
        if(!form_data[question_id]){
            form_data[question_id] = {
                rating: null,
                comment: null
            };
        }
        return form_data[question_id];
    },

    /**
     * Set the rating for a question on a form.
     *
     * @param form_id the ID of the form
     * @param question_id the ID of the question
     * @param rating the rating
     * @param scale the rating scale
     */
    setRating : function(form_id, question_id, rating, scale){
        // Get the question data object.
        question_data = this.getQuestionData(form_id, question_id);
        question_data.rating = rating;
        // Update the stars to reflect the rating.
        for(i=1; i<=scale; i++) {
            id = form_id + ':' + question_id + ':star:' + i;
            el = document.getElementById(id);
            if(i <= rating) {
                el.className = 'feedback-form-rating-star-on';
            } else {
                el.className = 'feedback-form-rating-star-off';
            }
        }
    },

    /**
     * Set the comment value for a question on a form.
     *
     * @param form_id the ID of the form
     * @param question_id the ID of the question
     */
    setComment : function(form_id, question_id){
        question_data = this.getQuestionData(form_id, question_id);
        id = form_id + ':' + question_id + ':comment';
        txtArea = document.getElementById(id);
        question_data.comment = txtArea.value;
    },

    /**
     * Submit the form.
     *
     * @param form_id the ID of the form
     */
    submit : function (form_id) {
        // Get the meta-data for this form.
        form_meta = this.form_metas[form_id];
        form_handler = form_meta.base_url + "FeedbackFormHandler.php";
        form_data = this.getFeedbackFormData(form_id);
        form_data_json = JSON.stringify(form_data);
        console.log('I will submit the following to ' + form_handler + ' ...');
        console.log(JSON.stringify(form_data));

        $.ajax({
            type: 'POST',
            url: form_handler,
            data: form_data_json,
            success: function(data, textStatus, jQxhr) {
                console.log(data);
            },
            error: function(jqXhr, textStatus, errorThrown) {
                console.log('error!');
                console.log(jqXhr);
                console.log(textStatus);
                console.log(errorThrown);
            },
            dataType: 'json'
        });
    },

    /**
    show : function(form_id) {

    },
     */

    /**
     * This is the initial load logic for feedback forms.
     */
    load : function() {
        // Get all of the form meta-data elements on the page.
        meta_els = document.getElementsByClassName('feedback-form-metadata');
        // Loop through...
        for(i=0; i<meta_els.length; i++) {
            // ...parse it.
            meta = JSON.parse(meta_els[i].innerText);
            // Add it to the collections.
            FeedbackForm.form_ids.push(meta.form_id);
            FeedbackForm.form_metas[meta.form_id] = meta;
        }
        // Create local variables for the form IDs and associated meta data objects.
        form_ids = FeedbackForm.form_ids;
        metas = FeedbackForm.form_metas;
        for(form_id_idx=0; form_id_idx<form_ids.length; form_id_idx++) {
            form_id = form_ids[form_id_idx];
            meta = metas[form_id];
            // If Javascript isn't inline...
            if(meta.no_inline_js) {
                for (j = 0; j < meta.question_ids.length; j++) {
                    question_id = meta.question_ids[j];
                    // Attach onclick event handlers to the ratings elements.
                    for (rating = 1; rating <= meta.scale; rating++) {
                        rating_el_types = ['rating', 'star'];
                        for (ret_idx = 0; ret_idx < rating_el_types.length; ret_idx++) {
                            rating_el_id = meta.form_id + ':' + question_id + ':' + rating_el_types[ret_idx] + ':' + rating;
                            rating_el = document.getElementById(rating_el_id);
                            rating_el.onclick = (function (form_id, question_id, rating, scale) {
                                return function () {
                                    FeedbackForm.setRating(form_id, question_id, rating, scale);
                                }
                            })(meta.form_id, question_id, rating, meta.scale);
                        }
                    }
                    // Attach onkeyup event handlers for the comment elements.
                    comment_el_id = meta.form_id + ':' + question_id + ':comment';
                    comment_el = document.getElementById(comment_el_id);
                    comment_el.onkeyup = (function(form_id, question_id){
                        return function() {
                            FeedbackForm.setComment(form_id, question_id);
                        }
                    })(meta.form_id, question_id);
                }
                // Attach an onclick handler for the submit button.
                submit_button_id = meta.form_id + ':submit';
                submit_button = document.getElementById(submit_button_id);
                submit_button.onclick = (function(form_id, submit_button){
                    return function() {
                        FeedbackForm.submit(form_id);
                    }
                })(form_id, submit_button);
                // Attach an onclick handler for the button that opens the form.
                open_button_id = meta.form_id + ':open';
                open_button = document.getElementById(open_button_id);
                open_button.onclick = (function(form_id, submit_button){
                    return function() {
                        console.log("Thank you for clicking");
                    }
                })(form_id, submit_button);
            } /* if(!meta.no_inline_js) */
        }
    }
};

if (window.addEventListener) // W3C standard
{
    window.addEventListener('load', FeedbackForm.load, false); // NB **not** 'onload'
}
else if (window.attachEvent) // Microsoft
{
    window.attachEvent('onload', FeedbackForm.load);
}



