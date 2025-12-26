const btnAddNewInfluencer = document.getElementById('btnAddNewInfluencer'); // button add new influence
const modalAddNewIflencerClose = document.getElementById('modalAddNewIflencerClose'); // button close modal
const modalAddNewIflencerSave = document.getElementById('modalAddNewIflencerSave'); // button save modal


btnAddNewInfluencer.addEventListener("click",function(event){
    if(event.target.tagName==="BUTTON"){
        document.querySelector("#modalAddNewIflencer").style.display = "flex";
        //addNewInfluence();
    }
})

modalAddNewIflencerSave.addEventListener("click",function(event){
    if(event.target.tagName==="BUTTON"){
        addNewInfluence();
        document.querySelector("#modalAddNewIflencer").style.display = "none";
    }
})

modalAddNewIflencerClose.addEventListener("click",function(event){
    if(event.target.tagName==="BUTTON"){
        document.querySelector("#modalAddNewIflencer").style.display = "none";
    }
})


function addNewInfluence(){
    // add new influence
    const newInfluenceName = document.querySelector("#newInfluenceName").value;
    const newInfluenceDescription = document.querySelector("#newInfluenceDescription").value;
    const newInfluenceImage = document.querySelector("#newInfluenceImage").value;
    const newInfluenceLink = document.querySelector("#newInfluenceLink").value;
    
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const html = "";
            alert("New influencer added");
            document.querySelector(".influencers").insertAdjacentHTML("beforeend", html); // add new influence to list
        }
    const data = "name="+newInfluenceName+"&description="+newInfluenceDescription+"&image="+newInfluenceImage+"&link="+newInfluenceLink;
    xhttp.open("POST", "influencer_save.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");    
    xhttp.send(data);
    }
}