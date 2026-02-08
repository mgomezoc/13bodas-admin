$(document).ready(function() {
    $("#submit_rsvp").click(function() { 
       
	    var proceed = true;
        //simple validation at client's end
        //loop through each field and we simply change border color to red for invalid fields		
		$("#rsvp_form .form-group input[required], #rsvp_form .form-group textarea[required]").each(function(){
			$(this).css('background-color',''); 
			if(!$.trim($(this).val())){ //if this field is empty 
				$(this).css('background-color','#FFDEDE'); //change border color to #FFDEDE   
				proceed = false; //set do not proceed flag
			}
			//check invalid email
			var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
			if($(this).attr("type")=="email" && !email_reg.test($.trim($(this).val()))){
				$(this).css('background-color','#FFDEDE'); //change border color to #FFDEDE   
				proceed = false; //set do not proceed flag				
			}	
		});
       
        if(proceed) //everything looks good! proceed...
        {
			//get input field values data to be sent to server
            post_data = {
	
				'user_name'		: $('input[name=name]').val(), 
				'guest'		    : $('input[name=guest]:checked').val(),
                'guests'		: $('input[name=guests]').val(),
				'attending'	    : $('input[name=attending]:checked').val(),	
				'msg'			: $('textarea[name=message]').val()
			};
            
            //Ajax post data to server
            $.post('php/sendmail.php', post_data, function(response){  
				if(response.type == 'error'){ //load json data from server and output message     
					output = '<div class="alert alert-danger">'+response.text+'</div><br>';
				}else{
				    output = '<h6 class="text-center">'+response.text+'</h6>';
				//reset values in all input fields
				$("#rsvp_form  input[required=true], #rsvp_form textarea[required=true]").val(''); 										
				$("#rsvp_form .form-group").slideUp(); //hide form after success
				$('html, body').animate({scrollTop: $("#rsvp_form .form-group").offset().top-150}, 2000);
				}
				$("#rsvp_form #contact_results").hide().html(output).slideDown();
            }, 'json');
        }
    });
    
    //reset previously set border colors and hide all message on .keyup()
    $("#rsvp_form  input[required=true], #rsvp_form textarea[required=true]").keyup(function() { 
        $(this).css('background-color','');
        $("#result").slideUp();
    });
});


					
