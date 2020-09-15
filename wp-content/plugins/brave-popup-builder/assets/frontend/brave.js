var brave_isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;
var brave_isTab = window.matchMedia("screen and (max-width: 1200px) and (min-width: 760px)").matches;
var brave_currentDevice = brave_isMobile ? 'mobile' : 'desktop';

function brave_number_padding(num) {  if(num){ var theNum = parseInt(num, 10); return theNum < 10 ? parseInt("0"+theNum, 10) : theNum } }

function brave_change_slide(elementID, goto, slideWidth){
   var sliderMargin = -(goto * slideWidth)+'px'
   var theCarousel = document.getElementById('brave_carousel__slides-'+elementID);
   var theCarouselNav = document.getElementById('brave_carousel__navs-'+elementID);
   
   if(theCarousel){
      theCarousel.style.marginLeft = sliderMargin;
      if(theCarouselNav){
         var allCarouselNavs = theCarouselNav.querySelectorAll(":scope li");
         for (var i = 0; i < allCarouselNavs.length; i++) { allCarouselNavs[i].classList.remove('slide__nav__active');  }
         document.getElementById('brave_carousel__nav-'+elementID+'_'+goto).classList.add('slide__nav__active');         
      }
   }
}

function brave_autochange_slide(elementID){
   var theSlider = document.getElementById('brave_carousel__slides-'+elementID);
   var currentslide = document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide;
   currentslide = parseInt(currentslide, 10)
   var slideWidth = parseInt(theSlider.dataset.width, 10);
   var totalSlides = parseInt(theSlider.dataset.totalslides, 10);

   var carouselHovered = theSlider.dataset.hovered;
   if(carouselHovered === 'true'){ return }
   //console.log(elementID, currentslide, slideWidth, totalSlides);
   

   brave_change_slide(elementID, currentslide, slideWidth);

   if(totalSlides === (currentslide+1)){
      document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide = 0;
   }else{
      document.querySelector('#brave_carousel__navs-'+elementID).dataset.currentslide = currentslide + 1;
   }
}

function brave_carousel_pause(elementID, resume=false){
   var theSlider = document.getElementById('brave_carousel__slides-'+elementID);
   if(theSlider){
      theSlider.dataset.hovered = true;
      if(resume){
         theSlider.dataset.hovered = false;
      }
   }
}
function brave_toggle_item(elementID, itemClass){
   var theToggleElm = document.getElementById('brave_carousel__slides-'+elementID);
   var selectedItem = theToggleElm.getElementsByClassName(itemClass);
   var allToggleDesc = theToggleElm.querySelectorAll('bravepopup_carousel__slide__content');
   for (var i = 0; i < allToggleDesc.length; i++) { allToggleDesc[i].classList.remove('brave__toggle__active');  }
   if(selectedItem[0]){ 
      if(selectedItem[0].classList.contains('brave__toggle__active')){
         selectedItem[0].classList.remove('brave__toggle__active');  
      }else{
         selectedItem[0].classList.add('brave__toggle__active');  
      }
   };
}
function brave_countdown(elementID, theDate, theHour, theMins, hideDays, auto=null) {
   if(!theDate) { return null}

   //console.log(theDate, theHour, theMins, hideDays, auto);
   
   var dateString = theDate;
   var time = {hour: theHour, minutes: theMins};
   var brave_endDate;
   if(!auto){
       var parts = dateString.split('/');
       var brave_day = parseInt(parts[0], 10);
       var brave_month = parseInt(parts[1], 10) - 1;
       var brave_year = parseInt(parts[2], 10);
       brave_endDate =  new Date(brave_year, brave_month, brave_day);
       brave_endDate.setHours(time.hour);
       brave_endDate.setMinutes(time.minutes);
       brave_endDate.setSeconds(0);
   }else{
       if(auto){
         brave_endDate =  auto;
       }

   }

   var brave_startDate = new Date().getTime();

   var brave_days, brave_hours, brave_minutes, brave_seconds;
   var timeRemaining = parseInt(((brave_endDate.getTime() - brave_startDate) / 1000), 10);
   
   //console.log( brave_endDate.getTime(), brave_startDate, timeRemaining);
   
   if (timeRemaining >= 0) {
       if(!hideDays){
         brave_days = parseInt(timeRemaining / 86400);
         timeRemaining = (timeRemaining % 86400);
       }
       
       brave_hours = parseInt(timeRemaining / 3600);
       timeRemaining = (timeRemaining % 3600);
       
       brave_minutes = parseInt(timeRemaining / 60);
       timeRemaining = (timeRemaining % 60);
       
       brave_seconds = parseInt(timeRemaining);
   }

   var daySpan = document.getElementById('brave_rem_days-'+elementID);
   var hourSpan = document.getElementById('brave_rem_hours-'+elementID);
   var minSpan = document.getElementById('brave_rem_minutes-'+elementID);
   var secSpan = document.getElementById('brave_rem_seconds-'+elementID);

   if(daySpan){ daySpan.innerHTML = timeRemaining >= 0 ? brave_days : 0}
   if(hourSpan){ hourSpan.innerHTML = timeRemaining >= 0 ? brave_hours : 0}
   if(minSpan){ minSpan.innerHTML = timeRemaining >= 0 ? brave_minutes : 0}
   if(secSpan){ secSpan.innerHTML = timeRemaining >= 0 ? brave_seconds : 0}



}


function brave_select_imageField(fieldID, optionIndex, multi=false){
   var optionField = document.getElementById('brave_form_field'+fieldID+'_opt-'+optionIndex);
   if(optionField){
      if(!multi){
         var allOptsFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('input');
         for (var i = 0; i < allOptsFields.length; i++) { allOptsFields[i].checked = false;  document.getElementById('brave_form_field'+fieldID+'_opt-'+i).classList.remove('formfield__inner__image--selected');  }
      }
      var optionFieldInput = optionField.querySelector('input');
      if(optionFieldInput && (optionFieldInput.checked === false)){   optionFieldInput.checked = true;  optionField.classList.add('formfield__inner__image--selected'); 
      }else if(optionFieldInput && (optionFieldInput.checked === true)){ optionFieldInput.checked = false; optionField.classList.remove('formfield__inner__image--selected'); }
   }
}
function brave_form_rating_unhover(fieldID){
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--hovered'); 
   }
}
function brave_form_rating_hover(fieldID, optionIndex){
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--hovered');  
      if(i <= (optionIndex-1)){
         allRatingFields[i].classList.add('formfield__inner__ratings--hovered'); 
      }
   }
}

function brave_form_rate(fieldID, optionIndex, smiley=false){
   document.getElementById('brave_form_field'+fieldID).dataset.rated = optionIndex;
   var allRatingFields = document.getElementById('brave_form_field'+fieldID).querySelectorAll('.formfield__inner__ratings_star, .formfield__inner__ratings_number, .formfield__inner__ratings_smiley');
   for (var i = 0; i < allRatingFields.length; i++) { 
      allRatingFields[i].classList.remove('formfield__inner__ratings--selected');
      if(!smiley && (i <= (optionIndex-1))){    allRatingFields[i].querySelector('input').checked = true; allRatingFields[i].classList.add('formfield__inner__ratings--selected');   }
      
      if(smiley && (i === (optionIndex-1))){    
         allRatingFields[i].querySelector('input').checked = true; 
         allRatingFields[i].classList.add('formfield__inner__ratings--selected');   
      }
   }
}
function brave_form_goBack(formID, totalSteps){
   var currentStep = document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step;
   var formBackButton = document.querySelector('#brave_form_'+formID+' .brave_form_stepBack');
   var goto = currentStep - 1;
   document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step = goto;


   //Change The Popup and Form Height if specified
   brave_form_changeHeight(formID, goto);

   if(goto === 0 ){   
      formBackButton.classList.add('brave_form_stepBack--hide');
   }else{
      formBackButton.classList.remove('brave_form_stepBack--hide');
   }

   // //Go to Prev Step
   var allformSteps = document.querySelectorAll('#brave_form_'+formID+' .brave_form_fields_step');
   for (var i = 0; i < allformSteps.length; i++) {   
      if(i ===goto){
         allformSteps[i].classList.add('brave_form_fields_step--show'); 

      }else{
         allformSteps[i].classList.remove('brave_form_fields_step--show');   
      }
   }
   if(goto === (totalSteps - 1) ){
      document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.remove('brave_form_button--hide');   
   }else{
      document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.add('brave_form_button--hide'); 
   }
}

function brave_form_gotoStep(formID, totalSteps, goto){
   //console.log('gotoStep', goto,totalSteps, goto === (totalSteps - 1));
   
   //Get The form Fields Values
   var braveForm = document.getElementById('brave_form_'+formID);
   //var formData = new FormData(braveForm);
   //var fieldsData = JSON.parse(brave_popup_formData[formID].fields);
   var fieldsData = brave_get_field_vals(braveForm, JSON.parse(brave_popup_formData[formID].fields))


   var stepFieldIDs = [];
   var currentStep = document.querySelector('#brave_form_'+formID+' .brave_form_fields_step'+(goto-1));
   if(currentStep){
      var allStepFields = currentStep.querySelectorAll('.brave_form_field');
      for (var i = 0; i < allStepFields.length; i++) { 
         if( !allStepFields[i].classList.contains('brave_form_field--step') ){
            stepFieldIDs.push(allStepFields[i].getAttribute('id').replace('brave_form_field', ''));
         }
      }
   }
   var filteredFieldData = {};
   stepFieldIDs.forEach(function(fieldID){
      if(fieldsData[fieldID]){ 
         filteredFieldData[fieldID] = fieldsData[fieldID];
      }
   })
   //console.log( fieldsData, filteredFieldData);

   // //Check if Form Has Errors
   var formErrors = [];
   Object.keys(filteredFieldData).forEach(function(fieldID){
      var fieldError = brave_validate_fields(fieldID, filteredFieldData[fieldID]);
      if(fieldError){ formErrors.push(fieldError); }
      document.querySelector('#brave_form_field'+fieldID).classList.remove('brave_form_field--hasError', 'brave_form_field--hasError-firstname', 'brave_form_field--hasError-lastname');
   });
   brave_display_form_error(formErrors);

   if(formErrors.length > 0){ return }

   //Change The Popup and Form Height if specified
   brave_form_changeHeight(formID, goto);

   // //Go to NextStep
   var allformSteps = document.querySelectorAll('#brave_form_'+formID+' .brave_form_fields_step');
   for (var i = 0; i < allformSteps.length; i++) {   
      if(i ===goto){
         document.querySelector('#brave_form_'+formID+' .brave_form_fields').dataset.step = goto;
         allformSteps[i].classList.add('brave_form_fields_step--show');   
         document.querySelector('#brave_form_'+formID+' .brave_form_fields').style.height = allformSteps[i].offsetHeight+'px';
         var formBackButton = document.querySelector('#brave_form_'+formID+' .brave_form_stepBack');
         if(formBackButton){ formBackButton.classList.remove('brave_form_stepBack--hide'); }
      }else{
         allformSteps[i].classList.remove('brave_form_fields_step--show');   
      }
   }
   //console.log(goto, totalSteps);
   
   if(goto === (totalSteps - 1) ){
      setTimeout(() => {
         document.querySelector('#brave_form_'+formID+' .brave_form_button').classList.remove('brave_form_button--hide');  
      }, 250);
   }

}

function brave_form_changeHeight(formID, goto){
   var changesFormHeight = brave_popup_formData[formID].changesFormHeight || false;
   if(changesFormHeight){
      var braveForm = document.getElementById('brave_element-'+formID);
      var parentPopupStep = braveForm.closest('.brave_popup__step');
      var initialHeight = brave_popup_formData[formID].heightData[0];
      var diffHeight = brave_popup_formData[formID].heightData[goto] - initialHeight;
      var newFormHeight = brave_popup_formData[formID].heightData[goto] || initialHeight;
      braveForm.style.height = (parseInt(newFormHeight, 10))+'px';

      if(parentPopupStep){
         var parentPopupHeight = parentPopupStep.dataset.height;
         //console.log(goto, parentPopupHeight, diffHeight, initialHeight, brave_popup_formData[formID].heightData[goto], brave_popup_formData[formID].heightData);
         parentPopupStep.querySelector('.brave_popup__step__inner').style.height =  (parseInt(parentPopupHeight, 10) + diffHeight)+'px';
      }
   }
   //console.log( changesFormHeight, newFormHeight, initialHeight, diffHeight);
}

function brave_get_field_vals(braveForm, fieldOpts){
   var fieldsData = fieldOpts;
   for( var i=0; i<braveForm.elements.length; i++ ){
      var fieldName = braveForm.elements[i].name.replace('[]', '');
      var fieldOpts = fieldsData[fieldName] || {};
      var fieldValue = braveForm.elements[i].value;
      //console.log(fieldName, fieldOpts, fieldValue);
      if(fieldOpts && fieldOpts.type && (fieldOpts.type ==='checkbox' || (fieldOpts.type ==='select' && fieldOpts.multi) || (fieldOpts.type ==='image' && fieldOpts.multi)) ){
         var checkedVal = document.querySelectorAll('#brave_form_field'+fieldName+' input:checked, #brave_form_field'+fieldName+' option:checked');
         fieldValue = Array.from(checkedVal).map(el => el.value);
         if(fieldOpts.required && (fieldValue.length === 0 || fieldValue.includes('none')) ){ fieldValue =''; }
      }
      if(fieldsData[fieldName]){
         fieldsData[fieldName].value = fieldValue;
      }
   }
   return fieldsData;
}

function brave_submit_form(event, settings){
   event.preventDefault();
   var braveForm = document.getElementById('brave_form_'+settings.formID);
   var fieldsData = brave_get_field_vals(braveForm, JSON.parse(settings.fields))

   //Check if has Errrors
   var formErrors = [];
   Object.keys(fieldsData).forEach(function(fieldID){
      var fieldError = brave_validate_fields(fieldID, fieldsData[fieldID]);
      if(fieldError){ formErrors.push(fieldError); }
      document.querySelector('#brave_form_field'+fieldID).classList.remove('brave_form_field--hasError', 'brave_form_field--hasError-firstname', 'brave_form_field--hasError-lastname');
   });
   brave_display_form_error(formErrors);

   var finalFieldsData = {};

   Object.keys(fieldsData).forEach(function(fieldID){  if(fieldsData[fieldID]) {  finalFieldsData[fieldID] = fieldsData[fieldID].value; }});

   console.log(formErrors);
   //console.log('FORM ERRORS: ', formErrors);

   if(formErrors.length === 0){
      //console.log('NO ERRORS Sending to Backend!');
      //SEND Data
      var ajaxurl = bravepop_global.ajaxURL;
      var security = document.getElementById('brave_form_security'+settings.formID).value;
      if(!security || !ajaxurl) { return console.log('Security Failed or Ajax URL Missing!!!!', security, ajaxurl); }
      var finalData = { formData: JSON.stringify(finalFieldsData), popupID: settings.popupID, formID: settings.formID, stepID: settings.stepID, device: settings.device, security: security, action: 'bravepop_form_submission' };
      var brave_form = document.getElementById('brave_form_'+settings.formID);
      var brave_login_loader = document.getElementById('brave_form_loading_'+settings.formID);
      if(brave_login_loader){  brave_login_loader.classList.add('brave_form_loading--show'); }
      if(brave_form){ brave_form.classList.add('brave_form_form--loading');  }

      //console.log(finalData);
      brave_ajax_send(ajaxurl, finalData, function(status, sentData){
         //Remove The sending Status
         if(brave_login_loader){  brave_login_loader.classList.remove('brave_form_loading--show'); }
         if(brave_form){ brave_form.classList.remove('brave_form_form--loading');  }

         var response = JSON.parse(sentData);
         console.log(status, response);

         localStorage.setItem('brave_popup_'+settings.popupID+'_formsubmitted', true);

         var braveFormSubmitEvent = new CustomEvent('brave_form_submitted', { detail: {popupId: parseInt(settings.popupID, 10), formId: settings.formID, formData: JSON.stringify(finalFieldsData)} });
         document.dispatchEvent(braveFormSubmitEvent);

         //Send Ga Event
         if(settings.track){
            var formTrackingSettings = JSON.parse(settings.track);
            if( formTrackingSettings && formTrackingSettings.enable && formTrackingSettings.eventCategory && formTrackingSettings.eventAction){
               brave_send_ga_event(formTrackingSettings.eventCategory, formTrackingSettings.eventAction, formTrackingSettings.eventLabel || '');
            }
         }

         //Perform Goal
         if(brave_popup_formData[settings.formID] && brave_popup_formData[settings.formID].goal){
            brave_complete_goal(settings.popupID, 'form');
         }

         //Show Content
         if(response.sent && response.primaryAction === 'content' && response.contentMessage ){
            if(brave_form){ brave_form.classList.add('brave_form_form--hide');  }
            document.getElementById('brave_form_custom_content'+settings.formID).innerHTML = response.contentMessage;
            //Auto Close Popup
            if(response.autoclose && response.autoclosetime){
               setTimeout(() => {
                  brave_close_popup(settings.popupID);
               }, parseInt(response.autoclosetime) * 1000);
            }
            //Also Download File
            if(response.download && response.downloadURL){
               var downloadURI = response.downloadURL; var filename =  response.downloadURL.substring( response.downloadURL.lastIndexOf('/')+1);
               var link = document.createElement('a');
               if (typeof link.download === 'string') {
                     document.body.appendChild(link); // Firefox requires the link to be in the body
                     link.download = filename;
                     link.href = downloadURI;
                     link.target = '_blank';
                     link.click();
                     document.body.removeChild(link); // remove the link when done
               } else {
                     location.replace(downloadURI);
               }
            }
         }

         //Redirect User
         if(response.sent && response.primaryAction === 'redirect' && response.redirectURL ){
            if(brave_form){ brave_form.classList.add('brave_form_form--hide');  }
            document.getElementById('brave_form_custom_content'+settings.formID).innerHTML = response.redirectMessage;
            var redirectTime = response.redirectAfter ? (response.redirectAfter *1000) : 6000;
            setTimeout(() => {
               window.location.href = response.redirectURL;
            }, redirectTime);
         }

         //Go to Step
         if(response.sent && response.primaryAction === 'step' && response.step && settings.popupID ){
            var formStep = parseInt(response.step, 10);
            formStep = formStep === 0 ? 0 : formStep - 1;
            brave_open_popup(settings.popupID, formStep);
         }


      });
   }

}

function brave_display_form_error(formErrors){
      console.log(formErrors);
      
      //Display The Errors
      if(formErrors.length > 0){
         formErrors.forEach(function(error){
            if(error.fieldType && error.fieldType ==='name'){
               if(error.firstname){
                  document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError-firstname');
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error--firstname').innerHTML = error.message;
               }
               if(error.lastname){
                  document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError-lastname');
                  document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error--lastname').innerHTML = error.message;
               }
   
            }else{
               document.querySelector('#brave_form_field'+error.id).classList.add('brave_form_field--hasError');
               document.querySelector('#brave_form_field'+error.id+' .brave_form_field_error').innerHTML = error.message;
            }
         });
      }
}


function brave_validate_fields(fieldID, field){
   if(!field){ return }

   if(!field.value && field.required) { 
      return {id: fieldID, type: 'required', message: bravepop_global.field_required}
   }
   if(field.required && (field.type==='input' || field.type==='textarea' || field.type==='date' ) ) { 
      if(!field.value.trim()){
         return {id: fieldID, type: 'required', message: bravepop_global.field_required}
      }
   }

   if(field.type==='input' && field.validation === 'name' && field.required) { 
      if(!field.value[0] || !field.value[1]){
         return {id: fieldID, type: 'required', fieldType: 'name', message: bravepop_global.field_required, firstname: !field.value[0] ? true : false, lastname:  !field.value[1] ? true : false}
      }
   }

   if(field.value && field.required && field.type==='select' && field.value === 'none') { 
      return {id: fieldID, type: 'required', message: bravepop_global.field_required}
   }

   //Check HTML or Empty Value
   if(field.value && ((field.type==='input' && field.validation === 'text') || field.type==='textarea' || field.type==='date'  ) ) { 

      if(brave_hasHTML(field.value)){
         return {id: fieldID, type: 'ho_html', message: bravepop_global.no_html_allowed};
      }
   }

   //Validate number
   if(field.value && field.type==='input' && field.validation === 'number' ) { 
      if(brave_isNumber(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_number};
      }
   }
   //Validate url
   if(field.value && field.type==='input' && field.validation === 'url' ) { 
      if(brave_isURL(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_url};
      }
   }

   //Validate email
   if(field.value && field.type==='input' && field.validation === 'email' ) { 
      if(brave_isEmail(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_email};
      }
   }

   //Validate Date
   if(field.value && field.type==='date' ) { 
      if(brave_isDate(field.value) === false){
         return {id: fieldID, type: 'invalid', message: bravepop_global.invalid_date};
      }
   }

}

function brave_hasHTML(value) {
   var doc = new DOMParser().parseFromString(value, "text/html");
   return Array.from(doc.body.childNodes).some(node => node.nodeType === 1);
}

function brave_isNumber(value) {
   var numberFormat = RegExp(/^[a-zA-Z]+$/);
   return numberFormat.test(value) ? false : true;
}

function brave_isEmail(value) {
   var mailformat = RegExp(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,8})+$/);
   return mailformat.test(value) ? true : false;
}

function brave_isURL(value) {
   var urlFormat = RegExp(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
   return urlFormat.test(value) ? true : false;
}

function brave_isDate(value) {
   var dateFormat = RegExp(/^\d{2}([./-])\d{2}\1\d{4}$/);
   var matchedDate = dateFormat.test(value);
   return matchedDate && matchedDate[0] ? true : false;
}


function brave_ajax_send(ajaxurl, ajaxData, callbackFunction){
   //console.log(ajaxData);
   var array = [];
   Object.keys(ajaxData).forEach(function(element) {
      array.push(  encodeURIComponent(element) + "=" + encodeURIComponent(ajaxData[element]) ) 
   });
   var dataToSend = array.join("&");
   
   var request = new XMLHttpRequest();
   request.open('POST', ajaxurl, true);
   request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded' );
   request.onload = function () {  
      if (this.status >= 200 && this.status < 400) { 
         callbackFunction('success', this.response);
      } else {   
         callbackFunction('error', this.response);
      }  
   };
   //request.onerror = function(error) {  console.log(error);   };
   request.send(dataToSend);
}


function brave_action_step(popupID, currentStep, stepIndex){
   //console.log(popupID, stepIndex);
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+stepIndex+' .brave_popup__step__'+brave_currentDevice)
   var noMobileContent = selectedPopupStep && selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+currentStep+' .brave_popup__step__'+currentDevice)
   selectedPopupStep.dataset.open = 'false';
   brave_open_popup(popupID, stepIndex);
   
}


function brave_init_popup(popupID, popupData ){
   //console.log(popupID, popupData);

   brave_responsiveness(popupID, popupData);

   //If is in Preview Mode force load the popup skinpping all the conditions
   if(popupData.forceLoad){
      return brave_load_popup(popupID, popupData);  
   }

   var triggerType = popupData.settings && popupData.settings.trigger && popupData.settings.trigger.triggerType ? popupData.settings.trigger.triggerType.split(',') : ['load'];

   //Do not load if utm keywords dont match
   var utmKeywords = [];
   var containsKeyword = false;
   if(popupData.settings && popupData.settings.placement && popupData.settings.placement.utm  && popupData.settings.placement.utmKeywords){
      utmKeywords = popupData.settings.placement.utmKeywords.split(',');
   } 
   if(utmKeywords.length > 0){
      var currentURL = window.location.href;
      utmKeywords.forEach(function(key){
         if(currentURL.includes(key)){   containsKeyword = true;  }
      })
      if(containsKeyword === false){
         return console.log('Does Not Match UTM Keywords');
      }
   }

   //Do not load if Popup is not in Scheduled Dates
   if(popupData.schedule && popupData.schedule.active && popupData.schedule.type){
      if(popupData.schedule.type === 'days' && popupData.schedule.days.length > 0){
         var currentDay = new Date().getDay();
         if(!popupData.schedule.days.includes(currentDay)){
            return console.log('Popup Not Scheduled for Today!');
         }
      }
      if(popupData.schedule.type === 'dates' && popupData.schedule.dates.length > 0){
         var thecurrentDate = new Date().getTime();
         var dateInRange = false;
         popupData.schedule.dates.forEach(function(date){
            var theTimes = []; var timeTypes = ['start', 'end'];
            timeTypes.forEach(function(val){
               var theDate = date[val].date.split('/');
               var theHour = date[val].time && date[val].time.hour ? date[val].time.hour : '00';
               var theMinutes = date[val].time && date[val].time.minutes ? date[val].time.minutes : '00';
               theTimes.push(new Date(theDate[2]+'/'+theDate[1]+'/'+theDate[0]+' '+theHour+':'+theMinutes+':00').getTime());
            })
            if(theTimes[0] < thecurrentDate && theTimes[1] > thecurrentDate){
               dateInRange = true;
            }
         });
         if(!dateInRange){
            return console.log('Popup Not Scheduled these dates!');
         }
      }
   }

   //Do not Load if Device Settings doesnt Match
   if(popupData.settings && popupData.settings.audience && popupData.settings.audience.devices && popupData.settings.audience.devices === "desktop" && brave_currentDevice === 'mobile'){   return console.log('Device Settings doesnt Match');  }
   if(popupData.settings && popupData.settings.audience && popupData.settings.audience.devices && popupData.settings.audience.devices === "mobile" && brave_currentDevice === 'desktop'){   return console.log('Device Settings doesnt Match');  }

   //Do not load if Opened n Times already
   var popupOpenCount = localStorage.getItem('brave_popup_'+popupID+'_viewed');
   var popupRepeatCountType = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCountType || 'lifetime';
   var repeatCount = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCount ? parseInt(popupData.settings.frequency.repeatCount, 10) : 3;
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeat){  
      if(popupRepeatCountType === 'lifetime' && parseInt(popupOpenCount, 10) >= repeatCount){
         return console.log('Already Viewed '+repeatCount+' Times. Hiding..');  
      }
      var popupViewStat = localStorage.getItem('brave_popup_'+popupID+'_view_stat'); popupViewStat = popupViewStat ? JSON.parse(popupViewStat) : [];
      var thecurrentDate = new Date(); var thecurrentDay = thecurrentDate.getDate(); var thecurrentMonth = thecurrentDate.getMonth(); var thecurrentYear = thecurrentDate.getFullYear();
      var popupViewDayCount = 0; var popupViewMonthCount = 0;
      if(popupViewStat){
         popupViewStat.forEach(function(stat){
            var statDate = new Date(stat); var statDay = statDate.getDate(); var statMonth = statDate.getMonth(); var statYear = statDate.getFullYear();
            if((statDay === thecurrentDay) && (statMonth === thecurrentMonth) && (statYear === thecurrentYear)){   popupViewDayCount = popupViewDayCount + 1;   }
            if((statMonth === thecurrentMonth) && (statYear === thecurrentYear)){   popupViewMonthCount = popupViewMonthCount + 1;   }
         })
      }    
      if(popupRepeatCountType === 'day' && (parseInt(popupViewDayCount, 10) >= repeatCount)){
         return console.log('Already Viewed '+repeatCount+' Times for today. Hiding..');  
      }
      if(popupRepeatCountType === 'month' && (parseInt(popupViewMonthCount, 10) >= repeatCount) ){
         return console.log('Already Viewed '+repeatCount+' Times for this month. Hiding..');  
      }
   }

   //Do not load if Closed n Times already
   var popupCloseStat = localStorage.getItem('brave_popup_'+popupID+'_closed') ? JSON.parse(localStorage.getItem('brave_popup_'+popupID+'_closed')) : null;
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.close && popupCloseStat && popupCloseStat.closed){   
      var popupCloseCount = popupData.settings.frequency.closeCount ? popupData.settings.frequency.closeCount : 2; 
      var popupCloseFor = popupData.settings.frequency.closeFor ? parseInt(popupData.settings.frequency.closeFor, 10) * 86400 * 1000 : 86400 * 1000 * 30;
      var userCloseCount = parseInt(popupCloseStat.closed, 10);
      var userCloseDate = parseInt(popupCloseStat.closeTime, 10);
      var currentDate = new Date().getTime();
      var closeTimeDiff = currentDate - userCloseDate;
      console.log('##### CLOSING STATS',userCloseCount, popupCloseFor,closeTimeDiff, closeTimeDiff >= popupCloseFor, userCloseCount >= popupCloseCount);
      if(closeTimeDiff <= popupCloseFor && (userCloseCount >= popupCloseCount )){
         return console.log('Close Time Settings Do not Match');
      }
   }
   //Do not Load if a Form Submitted in this Popup
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.formSubmit && localStorage.getItem('brave_popup_'+popupID+'_formsubmitted')){
      return console.log('Form in this Popup already Submitted! Hiding..');
   }

   //Do not show if goal is complete
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.goalComplete && localStorage.getItem('brave_popup_'+popupID+'_goal_complete')){
      return console.log('Popup Goal Already Completed..');
   }

   //Do not Load if Popups Countdown Timer has ended
   if(popupData.settings && popupData.settings.frequency && popupData.settings.frequency.timerEnded && popupData.timers.length > 0 && popupData.timers[0].ended){
      return console.log('Popup Countdown Timer Ended! Hiding..');
   }

   //Do not Load if a certain popup was not viewed/goaled/closed before
   if(popupData.settings && popupData.settings.filters && popupData.settings.filters.popups_before && popupData.settings.filters.popups.length > 0){
      var popupFilterFulfilled = false;
      popupData.settings.filters.popups.forEach(function (popup) {
         if(!popupFilterFulfilled && popup.id && popup.action){
            var popupActionMatched = localStorage.getItem('brave_popup_'+popup.id+'_'+popup.action);
            if(popupActionMatched){  popupFilterFulfilled = true;  }
         }
      })
      if(!popupFilterFulfilled){
         return console.log('A selected Popup was not viewed before! Hiding..');
      }
   }

   if(triggerType.includes('load')){   brave_load_popup(popupID, popupData, 'load');   }

   if(triggerType.includes('exit')){
      if(brave_currentDevice === 'mobile'){
         if(!popupData.settings.trigger.exitMobileFallback || (popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type === 'load')){
            brave_load_popup(popupID, popupData,'load');
         }
         if(popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type === 'time'){
            var exitMobileDelay = popupData.settings.trigger.exitMobileFallback.time || 2000;
            setTimeout(() => {    brave_load_popup(popupID, popupData , 'time');  }, (exitMobileDelay * 1000));
         }
      }else{
         document.addEventListener("mouseout", function(evt){
            if(evt.toElement === null && evt.relatedTarget === null) {
                brave_load_popup(popupID, popupData, 'exit');
            }
         });
      }
   }

   if(triggerType.includes('scroll') || (brave_isMobile && popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type  && popupData.settings.trigger.exitMobileFallback.type ==='scroll')){
      document.addEventListener("scroll", function(evt){
         var h = document.documentElement,  b = document.body, st = 'scrollTop', sh = 'scrollHeight';
         var scrollPercent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;
         if( brave_isMobile && (popupData.settings.trigger.exitMobileFallback && popupData.settings.trigger.exitMobileFallback.type && popupData.settings.trigger.exitMobileFallback.type ==='scroll')){
            var exitMobielScroll = popupData.settings.trigger.exitMobileFallback.scroll || 15;
            if(scrollPercent >= exitMobielScroll){   brave_load_popup(popupID, popupData, 'scroll');   }
         }
         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrolltype === 'twenty'){
            if(scrollPercent >= 20){   brave_load_popup(popupID, popupData, 'scroll');   }
         }
         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrolltype === 'half'){
            if(scrollPercent >= 50){   brave_load_popup(popupID, popupData, 'scroll');   }
         }
         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrolltype === 'end'){
            if(scrollPercent >= 95){   brave_load_popup(popupID, popupData,  'scroll');   }
         }
         if(popupData.settings && popupData.settings.trigger.scrolltype && popupData.settings.trigger.scrollto && popupData.settings.trigger.scrolltype === 'custom'){
            var scrolltoElms = document.querySelectorAll(popupData.settings.trigger.scrollto);
            if(scrolltoElms){
               scrolltoElms.forEach(function(scrolltoElm, index){
                  if(scrolltoElm && brave_is_in_view(scrolltoElm)){
                      brave_load_popup(popupID, popupData, 'scroll');  
                  }
               });
            }
         }
      });
   }

   if(triggerType.includes('click') && popupData.settings && popupData.settings.trigger.clickElements){
      var clickElms = document.querySelectorAll(popupData.settings.trigger.clickElements);
      //console.log(clickElms);
      
      if(clickElms){
         clickElms.forEach(function(clickElm, index){
            clickElm.addEventListener("click", function(evt){
               evt.preventDefault();
               brave_load_popup(popupID, popupData, 'click');
            });
         });
      }
   }

   if(triggerType.includes('time') && popupData.settings && popupData.settings.trigger.time && (popupData.settings.trigger.time.hours || popupData.settings.trigger.time.minutes || popupData.settings.trigger.time.seconds)){
      var triggerHours = popupData.settings.trigger.time.hours ? parseInt(popupData.settings.trigger.time.hours, 10) : 0;
      var triggerMinutes = popupData.settings.trigger.time.minutes ? parseInt(popupData.settings.trigger.time.minutes, 10) : 0;
      var triggerSeconds = popupData.settings.trigger.time.seconds ? parseInt(popupData.settings.trigger.time.seconds, 10) : 0;

      var totalTriggerDelay = (triggerHours * 3600) + (triggerMinutes * 60) + (triggerSeconds);
      totalTriggerDelay = totalTriggerDelay * 1000;
      //console.log(triggerHours, triggerMinutes, triggerSeconds, totalTriggerDelay);
      
      setTimeout(() => {
          brave_load_popup(popupID, popupData , 'time');
      }, totalTriggerDelay);

   }


   
}

function brave_load_popup(popupID, popupData, triggerType='load'){
   var selectedPopup = document.getElementById('brave_popup_'+popupID);
   var popupLoadStatus = selectedPopup ? selectedPopup.dataset.loaded : 'false';
 
   if(popupLoadStatus === 'false'){
      //LOAD ASSETS
      //--------------------
         //Load Video Scripts
         if(popupData.hasYoutube){
            var YTtag = document.createElement('script'); YTtag.src = "https://www.youtube.com/iframe_api";
            var PageFirstScript_YT = document.getElementsByTagName('script')[0]; PageFirstScript_YT.parentNode.insertBefore(YTtag, PageFirstScript_YT);
         }
         if(popupData.hasVimeo){
            var VimTag = document.createElement('script'); VimTag.src = "https://player.vimeo.com/api/player.js";
            var PageFirstScript_Vim = document.getElementsByTagName('script')[0]; PageFirstScript_Vim.parentNode.insertBefore(VimTag, PageFirstScript_Vim);
         }
         
         selectedPopup.dataset.loaded = true;
   }

   //Open Popup
   //---------------------
   const step = popupData.forceLoad && popupData.forceStep? parseInt(popupData.forceStep, 10) - 1 : 0;
   if(triggerType === 'exit' || triggerType === 'scroll'){
      var triggerFulFilled = document.getElementById('brave_popup_'+popupID).dataset.triggerfulfilled;
      if(!triggerFulFilled){
         document.getElementById('brave_popup_'+popupID).dataset.triggerfulfilled = true;
         brave_open_popup(popupID, step);
      }
   }else{
      brave_open_popup(popupID, step);
   }

}


function brave_open_popup(popupID, step=0){
   var popupData = brave_popup_data[popupID];
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__'+brave_currentDevice)
   var noMobileContent = selectedPopupStep && selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   var selectedPopupStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__'+currentDevice);
   var popupStepOpen = selectedPopupStep ? selectedPopupStep.dataset.open : 'false';
   var stickyBar = selectedPopupStep && selectedPopupStep.dataset.layout === 'float' && selectedPopupStep.dataset.position === 'top_center' ? true : false;

   console.log('Opening ', popupID, step, popupStepOpen, stickyBar, noMobileContent, currentDevice);
   //Send Ajax View Stat Data
   if(window.location.href.includes('brave_popup') === false && !brave_popup_data[popupID].viewStatSent){ 
      var viewDate = new Date(); var viewYear = viewDate.getFullYear(); var viewMonth = brave_number_padding(viewDate.getMonth() + 1);  var viewDate = brave_number_padding(viewDate.getDate()); var goalIsFirstView = false;
      if(brave_popup_data[popupID] && brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.goalAction && 
      brave_popup_data[popupID].settings.goalAction.type === 'step' && brave_popup_data[popupID].settings.goalAction.step === 0 ){  goalIsFirstView = true;  } 
      var viewData = { popupID: popupID, date: viewYear+'-'+viewMonth+'-'+viewDate, goalIsFirstView: goalIsFirstView, pageURL: window.location, goalUTCTime: new Date().toUTCString(), security: bravepop_global.security, action: 'bravepop_ajax_popup_viewed' };
      brave_ajax_send(bravepop_global.ajaxURL, viewData, function(status, sentData){
         // console.log('Popup View Updated: ', status, JSON.parse(sentData));
         brave_popup_data[popupID].viewStatSent = true;
      });
   }
   //Set Popup Viewed Cookie
   var currentPopupStat = localStorage.getItem('brave_popup_'+popupID+'_viewed');
   localStorage.setItem('brave_popup_'+popupID+'_viewed', currentPopupStat ? parseInt(currentPopupStat, 10) + 1 : 1);
   var popupRepeat =popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeat
   var popupRepeatCountType = popupData.settings && popupData.settings.frequency && popupData.settings.frequency.repeatCountType || 'lifetime';
   if(popupRepeat && popupRepeatCountType !== 'lifetime'){
      var theViewStat = localStorage.getItem('brave_popup_'+popupID+'_view_stat'); theViewStat = theViewStat ? JSON.parse(theViewStat) : [];
      theViewStat.push(new Date().getTime());      
      localStorage.setItem('brave_popup_'+popupID+'_view_stat', JSON.stringify(theViewStat));
   }

   if(popupStepOpen !== 'false'){ return }
   if(!popupData){ return }

   //Stickybar - Add html top margin
   if(stickyBar){
      //console.log('Apply Sticky Margin');
      var popupHeight = selectedPopupStep.dataset.height;
      document.documentElement.style.setProperty('margin-top', popupHeight+'px', 'important');
   } 

   //LazyLoad Images
   var allImages = selectedPopupStep.querySelectorAll('img');
   for (var i = 0; i < allImages.length; i++) { 
      if(allImages[i].dataset.lazy){
         allImages[i].src = allImages[i].dataset.lazy;
      }
   }

   if(popupData.videoData && popupData.videoData[step] && popupData.videoData[step][currentDevice]){
      //console.log('HAS VIDEO!!!!!!!!!!!', popupData.videoData[step][currentDevice]);
      var videoObj = popupData.videoData[step][currentDevice];
      var videoType = videoObj.videoType ? videoObj.videoType : 'youtube';
      var videoURL = videoObj.videoUrl ? videoObj.videoUrl : 'youtube';
      var videoTracking = videoObj.action && videoObj.action.track && videoObj.action.trackData ? videoObj.action.trackData : null;
      var videoSettings = {id: videoObj.id, autoplay: videoObj.autoplay || false, controls: videoObj.controls || false, mute: videoObj.mute || false, tracking: videoTracking }
      brave_load_video(videoType, videoURL, videoSettings);
   }

   var hasAnimation = popupData.hasAnimation ?  popupData.hasAnimation : false;
   var advancedAnimation = popupData.advancedAnimation ?  popupData.advancedAnimation : false;
   var hasContAnim = popupData.hasContAnim ?  popupData.hasContAnim : false;
   var animationData = popupData.animationData ?  popupData.animationData : {};
   var selectedStep = document.getElementById('brave_popup_'+popupID+'__step__'+step);
   if(selectedStep){
      var allSteps = document.querySelectorAll('#brave_popup_'+popupID+' .brave_popup__step_wrap');
      if(allSteps){
         allSteps.forEach(function(popStep, index){
            popStep.classList.remove('brave_popup__step_wrap--show');
         });
      }
      var braveOpenEvent = new CustomEvent('brave_popup_open', { detail: {popupId: parseInt(popupID, 10), step: step} });
      document.dispatchEvent(braveOpenEvent);

      //Open Animation
      if(!advancedAnimation){
         brave_open_animation(popupID, step, currentDevice);
      }

      //Advanced Animation
      if(advancedAnimation && hasAnimation && animationData){
         selectedStep.classList.add('brave_popup__step_wrap--show');
         brave_animate_popup(animationData, popupID, step, 'load');
      }
      //Continious Animation
      if(hasContAnim && animationData && animationData[step][currentDevice] && animationData[step][currentDevice].elements){
         var initialDelay = (animationData[step][currentDevice].totalDuration || 0) + 1200;
         animationData[step][currentDevice].elements.forEach(function(element){
            //console.log(element);
            
            if(element && element.animation && element.animation.continious){
               //console.log(element.id,element.animation.continious);
               var theElement = element.id === 'popup' ? document.querySelector('#brave_popup_'+popupID+'__step__'+step+' .brave_popup__step__inner .brave_popupSections__wrap') : document.getElementById('brave_element-'+element.id);
               var elementID = element.id === 'popup' ? popupID : element.id;
               var contAnimType = element.animation.continious.preset || 'none';
               var contAnimDuration = element.animation.continious.duration || 500;
               var contAnimDelay = element.animation.continious.delay || 0;
               if(theElement){
                  setTimeout(() => {
                     theElement.classList.add('brave_element-'+elementID+'_contAnim'); 
                     if(contAnimDelay > 0){
                        setInterval(function() {
                           theElement.classList.add('brave_element-'+elementID+'_contAnim'); 
                           setTimeout(function() {
                              theElement.classList.remove('brave_element-'+elementID+'_contAnim');
                           }, contAnimDuration);
                        }, ((contAnimDelay > contAnimDuration) ? contAnimDelay : contAnimDuration + contAnimDelay))
                     }
                  }, initialDelay);
               }
            }

         })

      }

   }
   //Show Vertical Scrollbar if Necessary
   setTimeout(() => {
      var currentPopupDimension = selectedPopupStep.querySelector('.brave_popup__step__inner').getBoundingClientRect();
      if((window.innerHeight < currentPopupDimension.height) && selectedPopupStep.classList.contains('brave_popup__step--boxed') && selectedPopupStep.classList.contains('position_center')){
         selectedPopupStep.classList.add('brave_popup_exceeds_windowHeight');
         selectedPopupStep.classList.add('brave_popup_show_scrollbar');
         //document.body.classList.add('brave_HideBodyScroll-'+popupID);
      }
   }, 100);


   //Popup AutoClose
   if(popupData.close[0] && popupData.close[0][currentDevice] && popupData.close[0][currentDevice].autoClose && popupData.close[0][currentDevice].autoCloseDuration){
      setTimeout(() => {
         brave_close_popup(popupID, step)
      }, parseInt(popupData.close[0][currentDevice].autoCloseDuration) * 1000);
   }

   //Change the Popup Step open status
   selectedPopupStep.dataset.open = true;
   brave_popup_data[popupID].opened = new Date().getTime();

   //Complete Step Open Goal
   if((popupData.settings && !popupData.settings.goalAction) || (popupData.settings && popupData.settings.goalAction && popupData.settings.goalAction.type  && popupData.settings.goalAction.type==='step' && popupData.settings.goalAction.step !== undefined )){
      var goalStep = popupData.settings.goalAction && popupData.settings.goalAction.step ? popupData.settings.goalAction.step.toString().split(',') :['0'];
      if(goalStep.includes(step.toString()) && step !== 0){
         brave_complete_goal(popupID, 'view');
      }
   }

   //If Code element is set to goal, attach submit event listener to complete goal
   var allCodeElmGoals = document.querySelectorAll('.brave_element__code--goaled form');
   if(allCodeElmGoals.length > 0){
      for (var i = 0; i < allCodeElmGoals.length; ++i) {
         allCodeElmGoals[i].addEventListener('submit', function () {    brave_complete_goal(popupID, 'form');    })
       }
   }


}

function brave_open_animation(popupID, step, currentDevice){
   var totalDuration = brave_popup_data[popupID].animationData[step][currentDevice].totalDuration;
   var openAnimData = brave_popup_data[popupID].animationData[step][currentDevice].elements ? brave_popup_data[popupID].animationData[step][currentDevice].elements : [];
   var hasAnimation = brave_popup_data[popupID].hasAnimation;
   var selectedStep = document.querySelector('#brave_popup_'+popupID+'__step__'+step);
   
   //console.log(popupID, step, currentDevice, openAnimData, selectedStep);

   var brave_animateElement = function(elementID, selectedStep, elementDom, step, animType) {
      selectedStep.classList.add('brave_popup__step_wrap--show');
      if(animType === 'text'){
         var selected_text_element =  document.querySelector('#brave_element-'+elementID+' .brave_element__text_inner');
         var selected_text_element_HTML = selected_text_element.innerHTML;
         var selected_text_element_content = selected_text_element.textContent;
         var newHTLArray = selected_text_element_content.toString().split('');
         var selected_text_element_clone = selected_text_element; 
         
         selected_text_element_clone.innerHTML ='';
         for (var i=0;i<=(newHTLArray.length - 1);i++) {
            (function(ind) {
               setTimeout(function(){
                  selected_text_element_clone.innerHTML = selected_text_element_clone.innerHTML+newHTLArray[ind];
               }, 1000 + (50 * ind));
            })(i);
         }
         setTimeout(function(){
            selected_text_element_clone.innerHTML = selected_text_element_HTML;
         }, 1000 + (50 * (newHTLArray.length -1)));
      }else{
         if(!elementDom.classList.contains('brave_element-'+elementID+'_'+step+'_openAnim')){ elementDom.classList.add('brave_element-'+elementID+'_'+step+'_openAnim'); }
      }
   }

   if(hasAnimation && openAnimData.length > 0){
      selectedStep.classList.add('brave_popup__step_wrap--show');
      openAnimData.forEach( function(element){
         if(element.animation && element.animation.load && element.animation.load.preset){
            var animType = element.animation.load.preset;
            var animDuration = element.animation.load.duration;
            var animDelay = element.animation.load.delay || 0;
            var elementID = element.id === 'popup' ? popupID : element.id;
            var elementDom = element.id === 'popup' ? selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popupSections__wrap') : selectedStep.querySelector('.brave_popup__step__'+currentDevice+' #brave_element-'+element.id);
            elementDom.style.opacity = 0;
            selectedStep.classList.remove('brave_popup__step_wrap--show');
            if(animDelay){
               // setTimeout(function(){    brave_animateElement(elementID, selectedStep, elementDom, step, animType);  }, animDelay);
               brave_animateElement(elementID, selectedStep, elementDom, step, animType);
            }else{
               brave_animateElement(elementID, selectedStep, elementDom, step, animType);
            }
            if(element.id === 'popup'){  animDelay = 0; } //Disable Popup Animation Delay
            setTimeout(() => { elementDom.style.opacity = ''; }, animDelay+ animDuration - 100);
            setTimeout(function(){
               elementDom.classList.remove('brave_element-'+elementID+'_'+step+'_openAnim');
            }, animDelay+ animDuration + 500);
         }
      })

   }else{
      selectedStep.classList.add('brave_popup__step_wrap--show');
   }

}

function brave_close_popup(popupID, step=0, gotoStep=false){
   var selectedStep = document.getElementById('brave_popup_'+popupID+'__step__'+step);
   var selectedPopupStep = selectedStep.querySelector('.brave_popup__step__'+brave_currentDevice);
   var noMobileContent = selectedPopupStep.dataset.nomobilecontent === 'true' ? true : false;
   var currentDevice = noMobileContent ? 'desktop' : brave_currentDevice;
   
   if(selectedStep){
      var exitAnimation = '';
      var currentPopupCloseStat = localStorage.getItem('brave_popup_'+popupID+'_closed') ? JSON.parse(localStorage.getItem('brave_popup_'+popupID+'_closed')) : {};
      var newCloseStat = {closed: currentPopupCloseStat.closed ? currentPopupCloseStat.closed + 1 : 1, closeTime: new Date().getTime()}
      localStorage.setItem('brave_popup_'+popupID+'_closed', JSON.stringify(newCloseStat));
      //Close Animation
      var exitAnimation = selectedStep.querySelector('.brave_popup__step__'+currentDevice).dataset.exitanimtype || '';
      var exitAnimationDuration = selectedStep.dataset.exitanimlength ? parseFloat(selectedStep.dataset.exitanimlength, 10) : 0.5;
      
      //Set the Popup Open status to False 
      var selectedPopupStep = selectedStep.querySelector('.brave_popup__step__'+currentDevice);
      selectedPopupStep.dataset.open = 'false';

      //Stop All Videos
      if(brave_popup_videos && Object.keys(brave_popup_videos).length > 0){
         Object.keys(brave_popup_videos).forEach(function(playerID){
            if(playerID.includes('youtube') && brave_popup_videos[playerID]){   brave_popup_videos[playerID].stopVideo();  }
            if(playerID.includes('vimeo') && brave_popup_videos[playerID]){   brave_popup_videos[playerID].pause();  }
         });
      }

      var braveCloseEvent = new CustomEvent('brave_popup_close', { detail: {popupId: parseInt(popupID, 10), step: step} });
      document.dispatchEvent(braveCloseEvent);

      //Play Exit Animation
      if(exitAnimation){
         selectedStep.querySelector('.brave_popupSections__wrap').classList.add('brave_element-'+popupID+'_'+step+'_exitAnim');
         setTimeout(() => { selectedStep.classList.remove('brave_popup__step_wrap--show');}, (exitAnimationDuration * 1000));
         setTimeout(() => { selectedStep.querySelector('.brave_popupSections__wrap').classList.remove('brave_element-'+popupID+'_'+step+'_exitAnim'); }, (exitAnimationDuration * 1000)+500);
         if(selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popup__step__overlay')){
            setTimeout(() => { 
               selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popup__step__overlay').classList.add('brave_popup__step__overlay--hide');
            }, (exitAnimationDuration > 0.3 ? ((exitAnimationDuration * 1000) - 200) : 200));
            setTimeout(() => { selectedStep.querySelector('.brave_popup__step__'+currentDevice+' .brave_popup__step__overlay').classList.remove('brave_popup__step__overlay--hide'); }, (exitAnimationDuration * 1000)+500);
         }
      }else{
         selectedStep.classList.add('brave_popup__step_wrap--hide');
         setTimeout(() => { selectedStep.classList.remove('brave_popup__step_wrap--show');}, 500);
         setTimeout(() => { selectedStep.classList.remove('brave_popup__step_wrap--hide');}, 800);
      }
      
      //If has gotoStep Command, go there
      if(gotoStep !== false && gotoStep >=0){
         //First set the Taget Step's Open status to false
         var targetStep = document.getElementById('brave_popup_'+popupID+'__step__'+gotoStep);
         targetStep.querySelector('.brave_popup__step__desktop').dataset.open = false;
         targetStep.querySelector('.brave_popup__step__mobile').dataset.open = false;
         //Then Open the Popup
         brave_open_popup(popupID, gotoStep);
      }
      var stickyBar = selectedPopupStep.dataset.layout === 'float' && selectedPopupStep.dataset.position === 'top_center' ? true : false;
      if(stickyBar){
         var newHeight = document.querySelector('body.admin-bar') ? '32px' : '0px';
         document.documentElement.style.setProperty('margin-top', newHeight, 'important');
      }
      
   }
}

function brave_send_ga_event(eventCategory, eventAction, eventLabel){
   //console.log('ga Event: ', eventCategory, eventAction, eventLabel);
   if ("ga" in window && eventCategory && eventAction) {
      var tracker = ga.getAll()[0];
      if (tracker){
         tracker.send('event', eventCategory, eventAction, eventLabel);
      }
   }
}


function brave_load_video(videoType, videoURL, videoSettings){
   //console.log(videoType, videoURL, videoSettings, videoSettings.tracking);

   var elmID = videoSettings.id.replace(/[^a-zA-Z0-9]+/g, '');
   if(videoType ==='custom'){
      brave_popup_videos['video_'+videoType+elmID] = document.getElementById('brave_video_custom_'+videoSettings.id);
      if(videoSettings.autoplay){
         brave_play_video(videoSettings.id, 'custom', videoSettings.tracking);
      }
   }

   if(videoType ==='youtube'){
      var youtube_regex = new RegExp(/^.*(youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\\&vi?=)([^#\\&\\?]*).*/);
      var parsed = videoURL.match(youtube_regex);
      var videoID = parsed && parsed[2] ? parsed[2]:null;
   
      if(!brave_popup_videos['video_'+videoType+elmID]){
         setTimeout(function() {
            brave_popup_videos['video_'+videoType+elmID] = new YT.Player('brave_video_iframe'+elmID, {
               videoId: videoID,
               playerVars: { 'autoplay': false, 'controls': videoSettings.controls ? videoSettings.controls : true, mute: videoSettings.mute ? videoSettings.mute : false  },
               events: {
                  'onReady': function(){
                     return videoSettings.autoplay && brave_play_video(videoSettings.id, 'youtube', videoSettings.tracking)
                  }
               }
            });
         }, 1000);
      }else{
         if(videoSettings.autoplay) { brave_play_video(videoSettings.id, 'youtube', videoSettings.tracking); }
      } 
   }

   if(videoType ==='vimeo'){
      var vimeo_regex = new RegExp(/https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/);
      var parsed = videoURL.match(vimeo_regex);
      var videoID = parsed && parsed[3] ? parsed[3] : null;
      
      if(!brave_popup_videos['video_'+videoType+elmID]){
         setTimeout(function() {
            brave_popup_videos['video_'+videoType+elmID] = new Vimeo.Player('brave_video_iframe'+elmID, {  id: videoID, background: videoSettings.controls ===false ? true : false });
            setTimeout(() => {
               if(videoSettings.mute){
                  brave_popup_videos['video_'+videoType+elmID].setVolume(0);
               }
               if(videoSettings.autoplay){
                  brave_play_video(videoSettings.id, 'vimeo', videoSettings.tracking);
               }
            }, 2000);
         }, 1000);
      }else{
         if(videoSettings.autoplay) { brave_play_video(videoSettings.id, 'youtube', videoSettings.tracking); }
      }
      
   }

}

function brave_play_video(elmentID, videoType, track=null, inline=false){
   //console.log('brave_play_video', elmentID, videoType, track );
   var elmID = elmentID.replace(/[^a-zA-Z0-9]+/g, '');
   if(!brave_popup_videos['video_'+videoType+elmID]){ return; }

   if(document.getElementById("brave_element-"+elmentID)){
      document.getElementById("brave_element-"+elmentID).classList.add('brave_element--video-show');
   }

   //Custom Video Playback
   if(videoType === 'custom' ){
      brave_popup_videos['video_'+videoType+elmID].muted = true; //Video doesnt autoplay if not muted
      brave_popup_videos['video_'+videoType+elmID].play();
   }
   //Youtube and Vimeo Playback
   if(videoType === 'youtube' || videoType === 'vimeo'){
      if(videoType === 'youtube' && brave_popup_videos['video_'+videoType+elmID]){   brave_popup_videos['video_'+videoType+elmID].playVideo();   }
      if(videoType === 'vimeo' && brave_popup_videos['video_'+videoType+elmID]){     brave_popup_videos['video_'+videoType+elmID].play().then(); }
   }

   //Track Video Playback
   if(inline && !track){
      var playButton = document.getElementById('brave_play_video-'+elmentID)
      if(playButton){
         var eventCategory = playButton.dataset.trackcategory || '';
         var eventAction = playButton.dataset.trackcategory || '';
         var eventLabel = playButton.dataset.tracklabel || '';
         track = {eventCategory: eventCategory, eventAction:eventAction, eventLabel: eventLabel}
      }
   }
   
   if(track && track.eventCategory && track.eventAction){
      brave_send_ga_event(track.eventCategory, track.eventAction, track.eventLabel || '');
   }
   
}

function brave_complete_goal(popupID, goalType='view', auto=false){
   if(window.location.href.includes('brave_popup') === false ){ 
      var goalDate = new Date(); var goalYear = goalDate.getFullYear(); var goalMonth = brave_number_padding(goalDate.getMonth() + 1);  var goalDay = brave_number_padding(goalDate.getDate()); 
      var goalData = { 
         popupID: popupID, 
         pageURL: window.location, 
         security: bravepop_global.security, 
         goalType: goalType, 
         //viewTime: brave_popup_data[popupID].opened, 
         views: localStorage.getItem('brave_popup_'+popupID+'_viewed') || 1, 
         goalTime: new Date().getTime(), 
         goalDate: goalYear+'-'+goalMonth+'-'+goalDay,
         goalUTCTime: new Date().toUTCString(), 
         device: brave_currentDevice, 
         auto: auto,
         action: 'bravepop_ajax_popup_complete_goal' 
      };

      brave_ajax_send(bravepop_global.ajaxURL, goalData, function(status, sentData){   console.log('Goal Complete!!!!!!', sentData);  });
      localStorage.setItem('brave_popup_'+popupID+'_goal_complete', true);
      var braveGoalCompletEvent = new CustomEvent('brave_goal_complete', { detail: {popupId: parseInt(popupID, 10), goalType: goalType} });
      document.dispatchEvent(braveGoalCompletEvent);
      if(brave_popup_data[popupID].settings && brave_popup_data[popupID].settings.notification && brave_popup_data[popupID].settings.notification.analyticsGoal){
         setTimeout(() => {
            //console.log('##### Send Goal Event to GA!');
            brave_send_ga_event('popup', 'goal', brave_popup_data[popupID].title+' ('+popupID+')' || popupID);
         }, 2000);
      }
   }
}

function brave_load_fonts(fontArray){
    if(fontArray.length > 0){
      WebFontConfig = {
         google: { families: fontArray }
       };
       (function() {
          if(!document.getElementById('bravePopu_webfontLoader')){
               var wf = document.createElement('script');
               wf.setAttribute("id", "bravePopu_webfontLoader");
               wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +'://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js';
               wf.type = 'text/javascript';
               wf.async = 'true';
               var s = document.getElementsByTagName('script')[0];
               s.parentNode.insertBefore(wf, s);
          }
       })();
    }
	//return fontArray;
};

function brave_is_in_view(elem) {
   var bounding = elem.getBoundingClientRect();
   return (
       bounding.top >= 0 &&
       bounding.left >= 0 &&
       bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
       bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
   );
};

window.addEventListener( 'DOMContentLoaded', brave_after_page_load );
function brave_after_page_load(){
   var allBraveFonts = []
   Object.keys(brave_popup_data).forEach((popID)=> {
      brave_popup_data[popID].fonts.forEach((fontName)=> {
         if(!allBraveFonts.includes(fontName)){
               allBraveFonts.push(fontName);
         }
      })
   });
   brave_load_fonts(allBraveFonts);
}

function brave_responsiveness(popupID, popupData){
   //If a step has no Mobile Content is set, make the Desktop Content mobile friendly and display that instead
   var stepsWithNoMobile = document.querySelectorAll('.brave_popup__step--mobile-noContent');
   if(brave_isTab){ stepsWithNoMobile = document.querySelectorAll('.brave_popup__step_wrap'); }
   for (var i = 0; i < stepsWithNoMobile.length; i++) {
      var desktopStep =  stepsWithNoMobile[i].querySelector('.brave_popup__step__desktop');
      var popupLayout = desktopStep.dataset.layout;
      var popupPosition = popupLayout === 'landing' ? 'top_center' :desktopStep.dataset.position;
      var popupWidth = parseInt(desktopStep.dataset.width, 10);
      var popupHeight = parseInt(desktopStep.dataset.height, 10);
      var windowWidth = document.body.clientWidth;  //window.innerWidth
      var windowHeight = window.innerHeight;
      
      if(brave_currentDevice === 'mobile' || brave_isTab){
         if((windowWidth < popupWidth) && (popupLayout == 'boxed' || popupLayout == 'landing' ) ){
            var scale =  desktopStep ? windowWidth/ desktopStep.dataset.width : 0;
            var tansformOrigin = popupPosition.includes('top') ? 'top' : 'center';
            desktopStep.querySelector('.brave_popup__step__inner').style.transform = 'scale('+((scale*95)/100)+')';
            desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = ((scale*95)/2)+'px '+tansformOrigin;
            if(windowWidth < popupWidth && windowHeight < popupHeight){
               desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = 'left top';
               desktopStep.querySelector('.brave_popup__step__inner').style.marginTop = 0;
               desktopStep.querySelector('.brave_popup__step__inner').style.top = 0;
            }
            if(popupPosition.includes('center') ){
               var widthRemainder = windowWidth - (popupWidth * (scale*95)/100) ;
               var heightRemainder = windowHeight - (popupHeight * (scale*95)/100) ;
               desktopStep.querySelector('.brave_popup__step__inner').style.left = widthRemainder > 0 ? (widthRemainder/2)+'px' : '0';
               //if(popupPosition === 'center'){ desktopStep.querySelector('.brave_popup__step__inner').style.top = heightRemainder > 0 ? (heightRemainder/2)+'px' : '0'; }
            }
            // if(windowHeight < (popupHeight * (scale*95)/100) ){
            //    console.log('Window Height vs Popup Height', windowHeight < (popupHeight * (scale*95)/100), windowHeight , (popupHeight * (scale*95)/100) );
            // }
         }
         if(brave_currentDevice === 'mobile' && ((290 < popupWidth) || windowHeight < popupHeight) && (popupLayout == 'boxed' ) && (popupPosition === 'bottom_right') ){
            desktopStep.querySelector('.brave_popup__step__inner').style.transform = 'scale(0.8)';
            desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = '100% bottom';
         }

      }
   }
   var allPopups = document.querySelectorAll('.brave_popup__step');
   for (var i = 0; i < allPopups.length; i++) {
      var aPopupHeight = allPopups[i].dataset.height ? parseInt(allPopups[i].dataset.height, 10) : 400;
      var aPopupPosition = allPopups[i].dataset.position; var aPopupLayout = allPopups[i].dataset.layout;
      if((window.innerHeight < aPopupHeight) && aPopupPosition.includes('center') && aPopupLayout === 'boxed' && !allPopups[i].classList.contains('brave_popup_show_scrollbar')){
         allPopups[i].classList.add('brave_popup_show_scrollbar', 'brave_popup_exceeds_windowHeight');
      }
   }


   //Embedded Popup Resonsiveness
   var selectedPopup = document.getElementById('brave_popup_'+popupID);
   if(brave_popup_data[popupID] && brave_popup_data[popupID].hasDesktopEmbed){  selectedPopup.classList.add('brave_popup--embedded'); selectedPopup.dataset.embedded ='true'}
   if(brave_isMobile && brave_popup_data[popupID] && brave_popup_data[popupID].hasMobileEmbed){  selectedPopup.classList.add('brave_popup--embedded'); selectedPopup.dataset.embedded ='true'}
   var theEmbbeddedPopup = document.getElementById('bravepopup_embedded_'+popupID);
   if(theEmbbeddedPopup && selectedPopup.dataset.embedded === 'true'){
      var parentElm = theEmbbeddedPopup.parentNode;
      //console.log('EMBEDDED POPUP!!!!',  parentElm);
     theEmbbeddedPopup.appendChild( document.getElementById('brave_popup_'+popupID));
      
      //If Embedded, make the Popup Responsive on desktop
      var allEmbeddedSteps = document.querySelectorAll('.brave_popup__step__desktop.brave_popup__step--embedded');
      for (var i = 0; i < allEmbeddedSteps.length; i++) {
         var desktopStep =  document.getElementById('brave_popup_'+popupID+'__step__'+i);
         //var embededStepNoMobile = desktopStep.classList.contains('brave_popup__step--mobile-noContent') ? true : false;
         if((!brave_isMobile || (brave_isMobile)) && desktopStep){
            var popupWidth = parseInt(allEmbeddedSteps[i].dataset.width, 10);
            var popupHeight = parseInt(allEmbeddedSteps[i].dataset.height, 10);
            var popup_parent_width = parentElm.clientWidth;
            
            if((popup_parent_width < popupWidth) ){
               var scale =  Math.min( popup_parent_width / allEmbeddedSteps[i].dataset.width);
               desktopStep.querySelector('.brave_popup__step__inner').style.transform = 'scale('+((scale*96)/100)+')';
               desktopStep.querySelector('.brave_popup__step__inner').style.transformOrigin = ((scale*96)/2)+'px center';
               desktopStep.querySelector('.brave_popup__step__inner').parentNode.classList.add('brave_popup__step__inner--scaled');
            }
         }
      }

   }
}

window.addEventListener( 'resize', brave_responsiveness );

//Click Open Popup
var braveFoundOpenElems = document.querySelectorAll('a[href^="#brave_open_popup_"]');
if(braveFoundOpenElems.length > 0){
   for (var i = 0; i < braveFoundOpenElems.length; i++) { 
      var rawOpenElmHref = braveFoundOpenElems[i].href; var braveOpenPopupID = rawOpenElmHref ? parseInt(rawOpenElmHref.replace(/[^0-9]/g,''), 10) : false;
      if(braveOpenPopupID){
         braveFoundOpenElems[i].addEventListener( 'click', function(event){
            event.preventDefault();
            brave_open_popup(braveOpenPopupID);
         } );
      }
   }
}