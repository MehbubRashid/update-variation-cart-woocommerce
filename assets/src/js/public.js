(function($){
    "use strict";

    $(document).ready(function(){
        

    });

    var textToHTML= function (str) {

        var dom = document.createElement('div');
        dom.innerHTML = str;
        return dom;
    
    };

    $(document).on('click', '.uvcw-edit', function(){
        var htmlContent = $(this).closest('.woocommerce-cart-form__cart-item').find('.uvcw-popup-source').text();

        Swal.fire({
            html: textToHTML(htmlContent),
            showClass: {
                popup: 'uvcw-swal'
            },
            didOpen: function(elem){
                $(elem).find('.variations_form').wc_variation_form();

                $(elem).find('.woocommerce-variation-add-to-cart .single_add_to_cart_button').text(uvcw.update);
            },
            showConfirmButton: false,
            showCloseButton: true,
            scrollbarPadding: false,
            target: '.site-content'
        })
    });

})(jQuery);