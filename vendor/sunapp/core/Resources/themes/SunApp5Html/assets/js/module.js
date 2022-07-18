var listScrollbar;

$(function () {
    "use strict";

    if ($(".modal-dialog-scrollable .modal-body").length) {
        new PerfectScrollbar(".modal-dialog-scrollable .modal-body");
    }
    if ($(".sidebar-menu-list").length) {
        new PerfectScrollbar(".sidebar-menu-list", {
            suppressScrollX: true
        });
    }
    $(".compose-btn .btn").on("click", function (e) {
        $(".modal .modal-body input").val(""), $(".modal .modal-body .ql-editor")[0].innerHTML = "", $(".modal .modal-body .custom-file .custom-file-label")[0].innerHTML = ""
    }), $(".menu-toggle").on("click", function (e) {
        $(".app-content .sidebar-left").removeClass("show"), $(".app-content .app-content-overlay").removeClass("show")
    }), $(".sg-application .sidebar-close-icon").on("click", function () {
        $(".sidebar-left").removeClass("show"), $(".app-content-overlay").removeClass("show")
    }), $(".sidebar-toggle").on("click", function (e) {
        e.stopPropagation(), $(".app-content .sidebar-left").toggleClass("show"), $(".app-content .app-content-overlay").addClass("show")
    }), $(".app-content .app-content-overlay").on("click", function (e) {
        $(".app-content .sidebar-left").removeClass("show"), $(".app-content .app-content-overlay").removeClass("show")
    }), $(".sg-app-list .sg-user-list li").on("click", function (e) {
        $(".app-content .sg-app-details").toggleClass("show")
    }), $(".sg-application .list-group-messages a").on("click", function () {
        $(".sg-application .list-group-messages a").hasClass("active") && $(".sg-application .list-group-messages a").removeClass("active"), $(this).addClass("active")
    }), $(".go-back").on("click", function (e) {
        e.stopPropagation()
    }), 768 < $(window).width() && $(".app-content .app-content-overlay").hasClass("show") && $(".app-content .app-content-overlay").removeClass("show"), $(".sg-application .favorite i").on("click", function (e) {
        $(this).parent(".favorite").toggleClass("warning"), e.stopPropagation()
    }), $(".sg-user-list .vs-checkbox-con input").on("click", function (e) {
        e.stopPropagation()
    }), $(document).on("click", ".sg-app-list .selectAll input", function () {
        $(".user-action .vs-checkbox-con input").prop("checked", this.checked)
    }), $(".sg-application .mail-delete").on("click", function () {
        $(".sg-application .user-action .vs-checkbox-con input:checked").closest("li").remove(), $(".sg-application .selectAll input").prop("checked", "")
    }), $(".sg-application .mail-unread").on("click", function () {
        $(".sg-application .user-action .vs-checkbox-con input:checked").closest("li").removeClass("mail-read")
    }), $(".sg-app-list #sg-search").on("keyup", function () {
        var e = $(this).val().toLowerCase();
        "" != e ? ($(".sg-user-list .users-list-wrapper li").filter(function () {
            $(this).toggle(-1 < $(this).text().toLowerCase().indexOf(e))
        }), 0 == $(".sg-user-list .users-list-wrapper li:visible").length ? $(".sg-user-list .no-results").addClass("show") : $(".sg-user-list .no-results").hasClass("show") && $(".sg-user-list .no-results").removeClass("show")) : ($(".sg-user-list .users-list-wrapper li").show(), $(".sg-user-list .no-results").hasClass("show") && $(".sg-user-list .no-results").removeClass("show"))
    });
}), $(window).on("resize", function () {
    768 < $(window).width() && $(".app-content .app-content-overlay").hasClass("show") && ($(".app-content .sidebar-left").removeClass("show"), $(".app-content .app-content-overlay").removeClass("show"))
});
