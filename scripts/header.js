document.getElementById("accountButton").addEventListener('mousedown', function() {
    console.log("Hola")
    document.getElementById("accountButton").classList.add("accountButtonPressed");
});

document.getElementById("accountButton").addEventListener('mouseup', function() {
    console.log("Hola")
    document.getElementById("accountButton").classList.remove("accountButtonPressed");
});