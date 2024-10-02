/**
 * I'm a tab (frontend window).
 *
 * As a tab, I should have a tabID.
 * This tabID should be set in my tab_id element, and/or my `sessionStorage.tabID`.
 *
 * I should check both and retrieve my tabID from one of them, prioritizing the tab_id element.
 *
 * Then I should use that tabID (might be empty, but shouldn't be),
 * and send a POST request to the backend, with the request data containing my tabID.
 *
 * I'm expecting the request response to contain an authTabID (authentic tab identifier) for me.
 *
 * I will set both my tab_id element and my `sessionStorage.tabID` to equal the authTabID.
 */

$(document).ready(function () {
    // https://stackoverflow.com/questions/11896160/any-way-to-identify-browser-tab-in-javascript
    var tabID =
        sessionStorage.tabID && sessionStorage.closedLastTab !== "2"
            ? sessionStorage.tabID
            : (sessionStorage.tabID = tabID = $("#tab_id").val());

    sessionStorage.closedLastTab = "2";

    // $(window).on("unload beforeunload", function () {
    //     sessionStorage.closedLastTab = "1";
    // });

    $(window).on("beforeunload", function () {
        sessionStorage.closedLastTab = "1";
    });

    $("#tab_id_demo").text(tabID);

    requestAndSetTabID(tabID);

    $("#new_tab_button").on("click", function () {
        window.open("./", "_blank");
    });
});

function requestAndSetTabID(tabID) {
    $.ajax({
        type: "POST",
        url: "backend/tabid.php",
        data: { tab_id: tabID },
        success: function (responseJSON) {
            authTabID = responseJSON["tab_id"];
            sessionStorage.tabID = authTabID;

            $("#tab_id").val(authTabID);
            $("#tab_id_demo").text(authTabID);
        },
        dataType: "JSON",
    });
}
