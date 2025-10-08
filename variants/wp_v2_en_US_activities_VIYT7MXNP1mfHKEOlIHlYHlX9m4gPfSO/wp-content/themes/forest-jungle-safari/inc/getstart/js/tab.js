function forest_jungle_safari_open_tab(evt, cityName) {
    var forest_jungle_safari_i, forest_jungle_safari_tabcontent, forest_jungle_safari_tablinks;
    forest_jungle_safari_tabcontent = document.getElementsByClassName("tabcontent");
    for (forest_jungle_safari_i = 0; forest_jungle_safari_i < forest_jungle_safari_tabcontent.length; forest_jungle_safari_i++) {
        forest_jungle_safari_tabcontent[forest_jungle_safari_i].style.display = "none";
    }
    forest_jungle_safari_tablinks = document.getElementsByClassName("tablinks");
    for (forest_jungle_safari_i = 0; forest_jungle_safari_i < forest_jungle_safari_tablinks.length; forest_jungle_safari_i++) {
        forest_jungle_safari_tablinks[forest_jungle_safari_i].className = forest_jungle_safari_tablinks[forest_jungle_safari_i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

jQuery(document).ready(function () {
    jQuery( ".tab-sec .tablinks" ).first().addClass( "active" );
});