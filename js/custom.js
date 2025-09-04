(function ($) {

  "use strict";

  // MENU
  $('.navbar-collapse a').on('click', function () {
    $(".navbar-collapse").collapse('hide');
  });

  // CUSTOM LINK
  $('.smoothscroll').click(function () {
    var el = $(this).attr('href');
    var elWrapped = $(el);
    var header_height = $('.navbar').height();

    scrollToDiv(elWrapped, header_height);
    return false;

    function scrollToDiv(element, navheight) {
      var offset = element.offset();
      var offsetTop = offset.top;
      var totalScroll = offsetTop - navheight;

      $('body,html').animate({
        scrollTop: totalScroll
      }, 300);
    }
  });

  /* ================================
     SHOW/HIDE TICKET OVERLAY BLUR
  ================================== */

  const ticketSection = document.querySelector(".ticket-section");
const openTicketBtns = document.querySelectorAll(".openTicket");

if (openTicketBtns.length && ticketSection) {
  // Loop through all matching buttons and attach click event
  openTicketBtns.forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      ticketSection.style.display = "block";
    });
  });

  // Optional: click outside the form to close
  ticketSection.addEventListener("click", function (e) {
    if (e.target === ticketSection) {
      ticketSection.style.display = "none";
    }
  });
}

})(window.jQuery);
