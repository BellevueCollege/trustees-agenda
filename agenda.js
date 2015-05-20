
jQuery(document).ready(function(){

jQuery('.meeting_date').datepicker({
	
dateFormat : 'yy-mm-dd'

}); 

 jQuery('#post').submit(function(e){ // the form submit function

         jQuery('.meeting_date').each(function(){
         	//console.log("Hellp"+jQuery(this).val());
           if( jQuery(this).val() == '-1' || jQuery(this).val() == '' ){ // checks if empty or has a predefined string
             //insert error handling here. eg $(this).addClass('error');
             
             jQuery(this).addClass('error');
             //console.log(jQuery(this).next().next());
             jQuery(this).next().next().text("This is required field");            
             e.preventDefault(); //stop submit event
           }
           else
           {
           	jQuery(this).removeClass('error');
           	jQuery(this).next().next().text("");        
           }
         })
    });


});
