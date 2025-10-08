function scary_halloween_open_tab(evt, cityName) {
    var scary_halloween_i, scary_halloween_tabcontent, scary_halloween_tablinks;
    scary_halloween_tabcontent = document.getElementsByClassName("tabcontent");
    for (scary_halloween_i = 0; scary_halloween_i < scary_halloween_tabcontent.length; scary_halloween_i++) {
        scary_halloween_tabcontent[scary_halloween_i].style.display = "none";
    }
    scary_halloween_tablinks = document.getElementsByClassName("tablinks");
    for (scary_halloween_i = 0; scary_halloween_i < scary_halloween_tablinks.length; scary_halloween_i++) {
        scary_halloween_tablinks[scary_halloween_i].className = scary_halloween_tablinks[scary_halloween_i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

jQuery(document).ready(function () {
    jQuery( ".tab-sec .tablinks" ).first().addClass( "active" );
});