document.getElementById("toogleSideBarButton").addEventListener('click', function() {
    document.getElementById("leftBar").classList.toggle("open");
    toogleSideBar();
});

function toogleSideBar() {
    var lcOptions = document.getElementsByClassName("lcOption");
    console.log(lcOptions);
    lcOptions = Array.from(lcOptions);
    lcOptions.forEach(lcOption => {
        if (document.getElementById("leftBar").classList.contains("open")) {
            lcOption.style.display="block";
            document.getElementById("toogleSideBarButton").src = "../../resources/imgs/collapse.png";
        } else {
            lcOption.style.display="none";
            document.getElementById("toogleSideBarButton").src = "../../resources/imgs/display.png";
        }
    });
}