jQuery(function ($) {
    // Testdaten in das Anzeigeformular einfügen
    function addTestdaten() {
        $('#Address_firstname').val('Klaus')
        $('#Address_lastname').val('Mergen')
        $('#Address_street1').val('Andeler Weg 1a')
        $('#Address_postcode').val('54470')
        $('#Address_city').val('Bernkastel-Kues')
    }

    $('#a-address-test-data').click(function () {
        addTestdaten()
        return false
    })
    //Ende Testdaten einfügen
})

