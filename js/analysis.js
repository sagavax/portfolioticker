const newAnalysis = document.getElementById("newAnalysis"); //newAnalysis
const modalNewAnalysisSave = document.getElementById("modalNewAnalysisSave");
const modalNewAnalysisClose = document.getElementById("modalNewAnalysisClose");


newAnalysis.addEventListener('click', function(e) {
        document.getElementById("modalNewAnalysis").style.display = 'flex';
});


modalNewAnalysisSave.addEventListener('click', function(e) {
        saveTheAnalysis();
        document.getElementById("modalNewAnalysis").style.display = 'none';
        const html ="<div class='analysis'><p><b>" + document.getElementById("ticker").value + "</b></p><p>" + document.getElementById("analysis_text").value + "</p></div><br>";
        document.getElementById("analysis").innerHTML += html;
        //reloadAnalysis();
});

modalNewAnalysisClose.addEventListener('click', function(e) {
        document.getElementById("modalNewAnalysis").style.display = 'none';
});

function saveTheAnalysis() {
    console.log("saveTheAnalysis");
    if(document.getElementById("ticker").value=="") {
        alert("Missing ticker");
        return;
    } 
    if(document.getElementById("analysis_text").value =="" ) {
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

function reloadAnalysis() {
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("portfolio").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "analysis_get_list.php", true);
    xhttp.send();
}