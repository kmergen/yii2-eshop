/* global jQuery */
// Eshop Javascript

const KMeshop = (($) => {
    // Public goes here

    const pub = {
        init: (options) => {
            $.extend(settings, options)
        }
    }

    //Private goes here
    const settings = {
        active: true
    }

    return pub
})(jQuery)

KMeshop.init()
