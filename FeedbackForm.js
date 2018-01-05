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
     * @returns the question data object
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
        form_handler = form_meta.form_handler;
        if(form_meta.debug) {
            console.log(form_handler);
        }
        form_data = this.getFeedbackFormData(form_id);

        worst_rating = -1;
        question_ids = $.map(form_data, function(val, idx){
            rating = val.rating;
            if(rating && (worst_rating < 0 || rating < worst_rating)) {
                worst_rating = rating;
            }
            return val;
        });
        console.log('The worst rating is ' + worst_rating);

        // If no ratings were provided...
        if(worst_rating < 0) {
            // ...alert the user...
            alert('Please provide a rating.');
            // ...and continue.
            return;
        }


        // We'll need the value of the permission checkbox.
        permission_chkbox_id = form_id + '--permission';
        permission_chkbox = document.getElementById(permission_chkbox_id);

        // Construct the POST data.
        post_data = {
            "reference_id": form_meta.reference_id,
            "csrf_token": form_meta.csrf_token,
            "data" : form_data,
            "permission": permission_chkbox.checked
        };
        post_data_json = JSON.stringify(post_data);

        if(form_meta.debug) {
            console.log(post_data_json);
        }
        // Make the request.
        $.ajax({
            type: 'POST',
            url: form_handler,
            data: post_data_json,
            success: function(data, textStatus, jQxhr) {
                if(form_meta.debug) {
                    console.log(data);
                }
                // Reset the form.
                FeedbackForm.resetForm(form_id);
                // Hide the menu item (if there is one).
                /**
                menu_item_id = meta.form_id + '--menu-item';
                if(menu_item) { // (Consider the menu item may not have been used.)
                    FeedbackForm.hideElementById(menu_item_id);
                }
                 */
                // Hide the feedback button.
                open_button_id = form_meta.form_id + '--open';
                FeedbackForm.hideElementById(open_button_id);
                form_div_id = form_meta.form_id + '--container';
                FeedbackForm.hideElementById(form_div_id);
                // Show the "goodbye" button.
                goodbye_div_id = form_meta.form_id + '--goodbye';
                goodbye_div_jqid = "#"+form_meta.form_id + '--goodbye';
                FeedbackForm.setClassById(goodbye_div_id, 'feedback-form-goodbye');

                // Set the visibility of elements on the "goodbye" form based on the
                // rating.
                goodbye_language_div_id = form_meta.form_id + '--goodbye-language';
                goodbye_language_el = document.getElementById(goodbye_language_div_id);
                //goodbye_language_div_jqid = "#"+goodbye_language_div_id;
                goodbye_social_div_id = form_meta.form_id + '--goodbye-social-media';
                //goodbye_social_div_jqid = "#"+goodbye_social_div_id;
                goodbye_social_div_el = document.getElementById(goodbye_social_div_id);
                if(worst_rating <=3) {
                    FeedbackForm.setClassById(goodbye_language_div_id, 'feedback-form-goodbye-language');
                    FeedbackForm.setClassById(goodbye_social_div_id, 'feedback-form-hidden');
                } else {
                    FeedbackForm.setClassById(goodbye_language_div_id, 'feedback-form-hidden');
                    FeedbackForm.setClassById(goodbye_social_div_id, 'feedback-form-social-media');
                }


                // $(goodbye_div_jqid).fadeOut(5000);
            },
            error: function(jqXhr, textStatus, errorThrown) {
                console.log('error!');
                console.log(jqXhr);
                console.log(textStatus);
                console.log(errorThrown);
                alert('We`re sorry.  An error occurred while we were trying to save your responses.')
            },
            dataType: 'json'
        });
    },

    /**
     * Reset a form.
     * @param form_id the ID of the form
     */
    resetForm : function(form_id) {
        // Reset the actual form data object.
        this.all_forms[form_id] = {};
        meta = this.form_metas[form_id];
        // Reset the states of the controls.
        for (j = 0; j < meta.question_ids.length; j++) {
            question_id = meta.question_ids[j];
            // Attach onclick event handlers to the ratings elements.
            for (rating = 1; rating <= meta.scale; rating++) {
                rating_el_types = ['star'];
                for (ret_idx = 0; ret_idx < rating_el_types.length; ret_idx++) {
                    rating_el_id = meta.form_id + ':' + question_id + ':' + rating_el_types[ret_idx] + ':' + rating;
                    rating_el = document.getElementById(rating_el_id);
                    rating_el.className = 'feedback-form-rating-star-off'; // Turn the star "off".
                }
            }
            // Attach onkeyup event handlers for the comment elements.
            comment_el_id = meta.form_id + ':' + question_id + ':comment';
            comment_el = document.getElementById(comment_el_id);
            comment_el.value = ''; // Clear the value.
        }
    },

    /**
     * Show a form.
     * @param form_id the ID of the form.
     */
    show : function(form_id) {
        // Get the meta-data for this form.
        meta = this.form_metas[form_id];
        form_div_id = meta.form_id + '--container';
        form_div = document.getElementById(form_div_id);
        form_div.className = 'feedback-form-container';
        // If the main link is flashing, turn it off.
        open_button_id = meta.form_id + '--open';
        open_button = document.getElementById(open_button_id);
        open_button.className='feedback-form-link';
        // If the menu item is flashing, turn it off.
        menu_item_id = meta.form_id + '--menu-item';
        menu_item = document.getElementById(menu_item_id);
        if(menu_item) { // (Consider the menu item may not have been used.)
            menu_item.className = 'feedback-form-menu-item';
        }
        // Hide the running man assets.
        running_man_asset_ids = [meta.form_id + '--running-man', meta.form_id + '--feedback-bubble'];
        for(rm_idx=0; rm_idx<running_man_asset_ids.length; rm_idx++) {
            asset_id = running_man_asset_ids[rm_idx];
            FeedbackForm.hideElementById(asset_id);
        }
        // Hide the previous "Goodbye" element (if it's visible).
        FeedbackForm.hideElementById(meta.form_id + '--goodbye');
    },

    /**
     * Hide a form element.
     * @param element_id the ID of the element
     */
    hideElementById : function(element_id){
        FeedbackForm.setClassById(element_id, 'feedback-form-hidden')
    },

    /**
     * Set the class of a given element.
     * @param element_id
     * @param className
     */
    setClassById : function(element_id, className){
        el = document.getElementById(element_id);
        el.className = className;
    },

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
                    };
                })(form_id, submit_button);
                // Attach an onclick handler for the button that opens the form.  (jQuery introduced)
                open_button_id = meta.form_id + '--open';
                open_button = document.getElementById(open_button_id);
                open_button_jqid = '#'+open_button_id;
                $(open_button_jqid).click(function(event){
                    event.stopPropagation();
                    FeedbackForm.show(form_id);
                });
                // Attach onclick handlers to the "running man" assets.
                running_man_asset_ids = [meta.form_id + '--running-man', meta.form_id + '--feedback-bubble'];
                for(rm_idx=0; rm_idx<running_man_asset_ids.length; rm_idx++) {
                    asset_id = running_man_asset_ids[rm_idx];
                    asset_element = document.getElementById(asset_id);
                    asset_element_jqid = '#'+asset_id;
                    $(asset_element_jqid).click(function(event){
                        event.stopPropagation();
                        FeedbackForm.show(form_id);
                    });
                }
                //Attach an onClick handler for the menu item that opens the form.
                menu_item_id = meta.form_id + '--menu-item';
                menu_item = document.getElementById(menu_item_id);
                menu_item_jqid = '#'+menu_item_id;
                if(menu_item) { // (Consider the menu item may not have been added.)
                    $(menu_item_jqid).click(function (event) {
                        event.stopPropagation();
                        FeedbackForm.show(form_id);
                    });
                }
                // Attach an onclick handler for the form itself.
                form_div_id = meta.form_id + '--container';
                form_div = document.getElementById(form_div_id);
                form_div_jqid = '#'+form_div_id;
                $(form_div_jqid).click(function(event){
                    event.stopPropagation();
                });
                $(document).click(function(){
                    form_div.className = 'feedback-form-hidden';
                });
                // Attach an onClick handler for the "goodbye" form to prevent clicks from closing it.
                goodbye_div_id = meta.form_id + '--goodbye';
                goodbye_div_jqid = "#"+goodbye_div_id;
                $(goodbye_div_jqid).click(function(event) {
                    event.stopPropagation();
                });
                // Attach an onclick handler for the button that closes the "goodbye"
                // element.
                goodbye_close_div_id = meta.form_id + '--goodbye-close';
                goodbye_close_div_jqid = '#'+goodbye_close_div_id;
                $(goodbye_close_div_jqid).click(function(event){
                    event.stopPropagation();
                    goodbye_div_id = meta.form_id + '--goodbye';
                    goodbye_div_jqid = "#"+form_meta.form_id + '--goodbye';
                    FeedbackForm.setClassById(goodbye_div_id, 'feedback-form-hidden');
                    form_div.className = 'feedback-form-hidden';
                });
            } /* if(!meta.no_inline_js) */
        }
        // Dynamically resize text areas.
        jQuery.each(jQuery('textarea[data-autoresize]'), function() {
            var offset = this.offsetHeight - this.clientHeight;

            var resizeTextarea = function(el) {
                jQuery(el).css('height', 'auto').css('height', el.scrollHeight + offset);
            };
            jQuery(this).on('keyup input', function() { resizeTextarea(this); }).removeAttr('data-autoresize');
        });
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



