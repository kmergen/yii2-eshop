/* global jQuery */
// Eshop Javascript

const KMeshop = function ($) {
    // Public goes here

    const pub = {
        init: function (options) {
            $.extend(opt, options)
        }
    }

    //Private goes here
    const opt = {
        active: true,
    }

    return pub
}(jQuery)


KMeshop.init()
