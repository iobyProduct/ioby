(function ($) {
  Drupal.behaviors.ioby_gratuity = {
    attach: function (context, settings) {
      
      // hide gratuity fields by default
      if(!$('#iobyproject-donation-form input#edit-donation').val()) {
        $('#iobyproject-donation-form .form-item-gratuity-value').hide('fast');
        $('#iobyproject-donation-form .form-item-gratuity').hide('fast');
      }
      
      var gratuity;
      $('#iobyproject-donation-form input#edit-donation').keyup(function() {
        if(this.value){
          // show gratuity
          $('#iobyproject-donation-form .form-item-gratuity-value').show('fast');
          $('#iobyproject-donation-form .form-item-gratuity').show('fast');
        } else {
          // hide gratuity
          $('#iobyproject-donation-form .form-item-gratuity-value').hide('fast');
          $('#iobyproject-donation-form .form-item-gratuity').hide('fast');
        }
        
        // calculate gratuity and set to gratuity field
        if(!isNaN(this.value) && this.value > 0){
          gratuity = this.value * 0.2;
          gratuity = gratuity.toFixed(0);
          if (gratuity > 10) {
            gratuity = 10;
          }
        } else {
          gratuity = '';
        }
        $('#iobyproject-donation-form input#edit-gratuity-value').val(gratuity);
      });
      
      $('#iobyproject-donation-form input#edit-gratuity').change(function() {
        if($(this).is(':checked')) {
          $('#iobyproject-donation-form .form-item-gratuity-value').removeClass('disabled');
        } else {
          $('#iobyproject-donation-form .form-item-gratuity-value').addClass('disabled');
        }
      });
      
    }
  }
})(jQuery);
