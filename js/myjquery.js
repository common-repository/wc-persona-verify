jQuery(document).ready(function($) {
       
    $("#start_persona_verify").on("click", function(){
        var tempID=$("#hidden_tempID").text();
        console.log(tempID);
        if(tempID==''){
            alert("System Persona TempID is not set yet in admin settings!");
        }
        const client = new Persona.Client({
            templateId: $("#hidden_tempID").text(),
            environment: "sandbox",
            onLoad: (error) => {
                if (error) {
                    console.error(`Failed with code: ${error.code} and message ${error.message}`)
                }

                client.open();
            },
            onStart: (inquiryId) => {
                console.log('Started inquiry'+inquiryId);
                
            },
            onComplete: (inquiryId) => {
                console.log(`Sending finished inquiry ${inquiryId} to backend`);
                fetch(`/server-handler?inquiry-id=${inquiryId}`);
                
                $.post(persona_object.ajax_url, {           //POST request
                    _ajax_nonce: persona_object.nonce,      //nonce created by php
                    action: "receive_result",               //action
                    inquiryId: inquiryId
                }, function(data) {                         //callback
                    // alert(data.string);
                    if(data.success==true){
                        $('form.checkout').show();
                        $('div.woocommerce-form-coupon-toggle').show();
                        $('#persona_div').hide();
                        console.log('Successfully verified this user');
                    }else{
                        console.log('Some errors are occured while updating the database');
                    }       
                },'json');

                
            },
            onEvent: (name, meta) => {
                switch (name) {
                    case 'start':
                        console.log(`Received event: start`);
                        break;
                    default:
                        console.log(`Received event: ${name} with meta: ${JSON.stringify(meta)}`);
                }
            }
        });
        
    })
    
});
