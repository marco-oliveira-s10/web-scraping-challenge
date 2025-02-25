/*!
 * Admin Dashboard Custom JavaScript
 */

// Close any open menu dropdowns when clicking outside
$(document).on('click', function(e) {
    if ($(e.target).closest('.dropdown-menu').length === 0 && 
        $(e.target).closest('.dropdown-toggle').length === 0) {
        $('.dropdown-menu').removeClass('show');
    }
});

// Toggle the side navigation
$("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
        $('.sidebar .collapse').collapse('hide');
    }
});

// Close any open menu when window resizes
$(window).resize(function() {
    if ($(window).width() < 768) {
        $('.sidebar .collapse').collapse('hide');
    }
    
    // Toggle the side navigation when window is resized below 480px
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
        $("body").addClass("sidebar-toggled");
        $(".sidebar").addClass("toggled");
        $('.sidebar .collapse').collapse('hide');
    }
});

// Prevent the content wrapper from scrolling when the fixed side navigation hovered over
$('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
        var e0 = e.originalEvent,
            delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
    }
});

// Scroll to top button appear
$(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
        $('.scroll-to-top').fadeIn();
    } else {
        $('.scroll-to-top').fadeOut();
    }
});

// Smooth scrolling using jQuery easing
$(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
        scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
});

// Initialize tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Auto-dismiss alerts after 5 seconds
window.setTimeout(function() {
    $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
        $(this).remove();
    });
}, 5000);

// Confirm deletion actions
$('.delete-confirm').on('click', function(e) {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        e.preventDefault();
    }
});

// Handle the modal form submission with loading state
$('.modal form').on('submit', function() {
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    submitBtn.prop('disabled', true);
    
    // We'll simulate the form delay for demonstration purposes
    setTimeout(function() {
        submitBtn.html(originalText);
        submitBtn.prop('disabled', false);
    }, 3000);
});