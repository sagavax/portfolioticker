const search_asset = document.querySelector('input[name="search_asset"]');



search_asset.addEventListener('input', function() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        document.querySelector("#asset_list tbody").innerHTML = this.responseText;
    }

    xhttp.open("GET", "assets_search_table.php?query=" + search_asset.value);
    xhttp.send();
});