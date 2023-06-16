(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const button = document.querySelector("#publish[value='Update']");
        if (button) {
            button.addEventListener('click', function (e) {
                var next = false;
                var fields = document.querySelectorAll('.cmb2_select');
                for (var i = 0; i < fields.length; i++) {
                    if (fields[i].value == 'text_Last_Name') {
                        next = true;
                    }
                }
                if (!next) {
                    e.preventDefault();
                    if (!document.getElementById('lnmne')) {
                        var preHTML =
                            document.querySelector('#wpcontent').innerHTML;
                        var $msg =
                            "<div id='lnmne' class='notice is-dismissible notice-error'><p><strong>Must you have to map Last_Name with any field.</strong></p><button type='button' class='notice-dismiss' onclick=\"this.closest('.notice').outerHTML='' \"></button></div>";
                        document
                            .querySelector('.wp-header-end')
                            .insertAdjacentHTML('afterend', $msg);
                    }
                }
            });
        }
    });
})(jQuery);
