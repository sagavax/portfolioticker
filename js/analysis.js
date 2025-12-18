const newAnalysis = document.getElementById("newAnalysis"); //newAnalysis
const modalNewAnalysiskSave = document.getElementById("modalNewAnalysiskSave");
const modalNewAnalysiskClose = document.getElementById("modalNewAnalysiskClose");


newAnalysis.addEventListener('click', function(e) {
        document.getElementById("modalNewAnalysis").style.display = 'flex';
});


modalNewAnalysiskSave.addEventListener('click', function(e) {
        saveTheAnalysis();
        document.getElementById("modalNewAnalysis").style.display = 'none';
});

modalNewAnalysiskClose.addEventListener('click', function(e) {
        document.getElementById("modalNewAnalysis").style.display = 'none';
});

function saveTheAnalysis() {
    console.log("saveTheAnalysis");
    if(document.getElementById("ticker").value=="" || document.getElementById("analysis").innerText == '') {
        alert("Cannot be empty");
        return;
    }
    const ticker = document.getElementById("ticker").value;
    const analysis = document.getElementById("analysis_text").value;
    console.log(ticker, analysis);
    const xhttp = new XMLHttpRequest();
    xhttp.open("POST", "save_analysis.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    const data = "ticker=" + ticker + "&analysis=" + analysis;
    xhttp.send(data);    
}
