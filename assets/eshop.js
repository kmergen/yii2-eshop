"use strict";

/* global jQuery */
// Eshop Javascript

var KMeshop = function ($) {
    // Public goes here

    var pub = {
        init: function init(options) {
            $.extend(settings, options);
        }

        //Private goes here
    };var settings = {
        active: true
    };

    return pub;
}(jQuery);

KMeshop.init();