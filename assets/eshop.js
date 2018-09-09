
jQuery(function ($) {
  function enableCheckoutInput(){
	$("button,input").prop('disabled', false);
  }

  function disableCheckoutInput(){
    $("button,input").prop('disabled', true);
  }

  /*
  function disableCheckoutInput(){
	  $('input').each(function(){
		  $(this).attr('disabled', 'disabled');
	  });
  }
  */

  function removeErrorFromPaymentMethods(){

  }

  //Payment methods
  $('#checkout-form').on('change','.payment-method',function(e){
	//We disable all input fields to prevent clicking during the ajax call
	disableCheckoutInput();
	var paymentMethod=$(this).val();

	$.ajax({
	  type: 'POST',
	  dataType: 'html',
      url: '/shop/checkout/change-payment-method',
	  data: {'paymentMethod':paymentMethod},
	  error: showAjaxError,
	  success:function(data){
         $('#payment-method-pane').html(data);
      }
   });
	$(".payment-methods-error").empty();
	enableCheckoutInput();
  });

  //Cart Pane
  $('#checkout-form').on('change','.ArticleOld-qty',function(e){
	//We disable all input fields to prevent clicking during the ajax call
	disableCheckoutInput();

	var ArticleId=$(this).attr('id').substr(8);
	var qty;
	if($(this).is(":checked")) {
	  qty=1;
	}
	else{
	  qty=0;
	}

	$.ajax({
	  type: 'POST',
	  dataType: 'html',
      url: '/shop/checkout/update-cart-item',
	  data: {'ArticleId':ArticleId, 'qty':qty},
	  error: showAjaxError,
	  success:function(data){
         $('#cart-pane').html(data);
      }
   });
	enableCheckoutInput();
  });

});
