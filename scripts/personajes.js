var addCharButton = document.getElementById("addCharButton");
var addCharVid = document.getElementById("background-video");
addCharButton.addEventListener('mouseenter', function() {
    addCharVid.play();
})
addCharButton.addEventListener('mouseleave', function() {
    addCharVid.pause();
})