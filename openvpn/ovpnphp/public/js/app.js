function bootstrapui() {
    $.each(['button', 'a', 'span', 'li', '.progress-bar'], function (index, element) {
        $(element).filter('[title]').tooltip({
            container: '#content'
        });

        $(element).filter('[title]').bind('click', function () {
            $(element).filter('[title]').tooltip('hide');
        });
    });

    $('.admin-toolbar #search').bind('click', function () {
        $('#admin-filter').modal();
    });

    $('#admin-filter #filter').bind('click', function () {
        $('#admin-filter').modal('hide');
        $('#filter-form').submit();
    });
}

$(function () {
    bootstrapui();
});

$(document).ajaxComplete(function (event, xhr, settings) {
    bootstrapui();
});

// define String.prototype.contains if not exist (Chrome case)
if (!('contains' in String.prototype)) {
    String.prototype.contains = function (str, startIndex) {
        return -1 !== String.prototype.indexOf.call(this, str, startIndex);
    };
}

function ajaxRequest(url, fn, type) {
    $.ajax({
        url: url,
        type: type ? type : 'GET',
        dataType: 'html',
        async: true,
        success: function (response) {
            fn[0](response, fn[1]);
        },
        error: function (response) {
            fn[0](response.responseText, fn[1]);
        }
    });
}

function updateContent(data, params) {
    params.element.html(data);

    if (params.callback !== undefined) {
        params.callback();
    }
}

function appendContent(data, params) {
    params.element.append(data);

    if (params.callback !== undefined) {
        params.callback();
    }
}
