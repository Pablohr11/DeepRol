document.getElementById("toogleSideBarButton").addEventListener('click', function() {
    document.getElementById("leftBar").classList.toggle("open");
    toogleSideBar();
});

function toogleSideBar() {
    var lcOptions = document.getElementsByClassName("lcOption");
    var lbButtons = document.getElementsByClassName("lbButton");
    console.log(lcOptions);
    lcOptions = Array.from(lcOptions);
    lbButtons = Array.from(lbButtons);
    lcOptions.forEach(lcOption => {
        if (document.getElementById("leftBar").classList.contains("open")) {
            lcOption.style.display="grid";
            document.getElementById("toogleSideBarButton").src = "../../resources/imgs/collapse.png";
        } else {
            lcOption.style.display="none";
            document.getElementById("toogleSideBarButton").src = "../../resources/imgs/display.png";
        }
    });
    lbButtons.forEach(lbButton => {
        if (document.getElementById("leftBar").classList.contains("open")) {
            lbButton.style.display="none";
        } else {
            lbButton.style.display="block";
        }
    });
}