function init() {

    var inputs = document.querySelectorAll(".formInputContainer input");
    inputs.forEach((node) => {
        node.addEventListener('focus', function(){
            node.parentElement.querySelector("span").classList.add("focused"); 
        })
        node.addEventListener('blur', function(){
            if (node.value == "") {
                node.parentElement.querySelector("span").classList.remove("focused"); 
            }
        })
    });
}