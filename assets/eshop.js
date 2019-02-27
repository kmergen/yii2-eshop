"use strict";

/* global jQuery */
// Eshop Javascript

var KMeshop = function ($) {
    // Public goes here

    var pub = {
        init: function init(options) {
            $.extend(opt, options);
        }

        //Private goes here
    };var opt = {
        active: true
    };

    return pub;
}(jQuery);

KMeshop.init();