function hype_initHypeCounters() {
    ids = _hype_getHypeCountElements();

    jQuery.post(whm_hype_likeCount,
        {elements: ids},
        function (data) {
            counts = data["counts"];
            for (var type in counts) {
                for (var index in counts[type]) {
                    selector = '.hype-count[data-hype-type="' + type + '"][data-hype-identifier="' + counts[type][index]["identifier"] + '"]';
                    $(selector).html(counts[type][index]["countById"]);
                }
            }
        }
    );
}

function _hype_getHypeCountElements() {
    counters = jQuery(".hype-count");
    var ids = new Array();

    jQuery.each(counters, function (index, counter) {
        counterElement = jQuery(counter);
        identifier = counterElement.data('hype-identifier');
        type = counterElement.data('hype-type');

        ids.push({"type": type, "identifier": identifier});
    });

    return ids;
}

function hype_initOwnHypes() {
    ids = _hype_getHypeCountElements();

    jQuery.post(whm_hype_isLiked,
        {elements: ids},
        function (data) {
            matches = data["matches"];

            for (var type in matches) {
                for (var index in matches[type]) {
                    _hype_toggleStatus(type, matches[type][index]);
                    selector = '.hype-count[data-hype-type="' + type + '"][data-hype-identifier="' + matches[type][index] + '"]';
                    $(selector).addClass("hype-hyped");
                }
            }
        }
    );
    // user könnte über parameter reingegeben werden, dann könnte das System aus autark funktionieren
}

function hype_initHypeButtons() {
    hypeButtons = jQuery(".hype-button");
    hypeButtons.click(function () {
        hype_hype(jQuery(this).data('hype-type'), jQuery(this).data('hype-identifier'))
    });

    unhypeButtons = jQuery(".unhype-button");
    unhypeButtons.click(function () {
        hype_unhype(jQuery(this).data('hype-type'), jQuery(this).data('hype-identifier'))
    });
}

function hype_hype(type, identifier) {
    url = whm_hype_hype.replace('type', type).replace('identifier', identifier);

    jQuery.post(url, function (data) {
        _hype_toggleStatus(type, identifier);

        // increase counter
        selector = '.hype-count[data-hype-type="' + type + '"][data-hype-identifier="' + identifier + '"]';
        if (isNaN(parseInt($(selector).html()))) {
            $(selector).html("1");
        } else {
            $(selector).html(parseInt($(selector).html()) + 1);
        }
        $(selector).addClass("hype-hyped");

    });
}

function hype_unhype(type, identifier) {
    url = whm_hype_unhype.replace('type', type).replace('identifier', identifier);

    jQuery.post(url, function (data) {
        _hype_toggleStatus(type, identifier);

        // increase counter
        selector = '.hype-count[data-hype-type="' + type + '"][data-hype-identifier="' + identifier + '"]';
        $(selector).html(parseInt($(selector).html()) - 1);
        $(selector).removeClass("hype-hyped");
    });
}

function _hype_toggleStatus(type, identifier) {
    hypeSelector = '.hype-button[data-hype-type="' + type + '"][data-hype-identifier="' + identifier + '"]';
    $(hypeSelector).toggle();

    unhypeSelector = '.unhype-button[data-hype-type="' + type + '"][data-hype-identifier="' + identifier + '"]';
    $(unhypeSelector).toggle();
}

$(function () {
    hype_initHypeCounters();
    hype_initHypeButtons();
    hype_initOwnHypes();
});