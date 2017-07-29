$(function () {

    function delayPics(picsArray) {
        document.onreadystatechange = function (e) {
            if ("complete" === document.readyState) {
                for (var i = 0; i < picsArray.length; i += 1) {
                    picsArray[i].src = picsArray[i].dataset.original;
                }
            }
        };
    }

    delayPics(document.getElementsByClassName("lazy"));

    $('img').lazyload({
        threshold: 200
    });


    history.replaceState(null, null, '/');

    $(function () {
        $('.date').mask('99.99.9999');
    });

    $('select[name="country"]').change(function () {
        var countryID = $(this).val();

        $.post("/?type=ajax&action=getCitySelect", {countryID: countryID})
            .done(function (data) {
                $('select[name="city"]').html(data);
            });
    });

    $('.item').hover(function () {
        $(this).find('span').show();
    }, function () {
        $(this).find('span').hide();
    });

    $('#single').click(function (e) {
        e.preventDefault();
        $('.allsearch').hide();
        $('.singlesearch').show();
        return false;
    });

    $('#all').click(function (e) {
        e.preventDefault();
        $('.allsearch').show();
        $('.singlesearch').hide();
        $('input[name="ids"]').val('');
        return false;
    });

    $('input[name="ids"]').change(function () {
        $('input[name="date"]').val('');
    });

    $('input[name="date"]').change(function () {
        $('input[name="ids"]').val('');
    });

    var age_from = $('select[name="age_from"]');
    var age_to = $('select[name="age_to"]');

    age_from.change(function () {
        if ($(this).val() > age_to.val()) {
            age_to.val($(this).val());
        }
    });

    age_to.change(function () {
        if ($(this).val() < age_from.val()) {
            age_from.val($(this).val());
        }
    });

});

(function (i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
    a = s.createElement(o),
        m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
})(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

ga('create', 'UA-85785110-1', 'auto');
ga('send', 'pageview');