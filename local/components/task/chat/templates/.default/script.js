function ReloadScript() {

    $('.perfectScrollbar').each(function () {
        ps = new PerfectScrollbar($(this)[0], {
            wheelPropagation: false
        });
        if ($(ps).hasClass('.chat__messages-list')) {
            $('.chat__messages-list')[0].scrollTop = $('.chat__messages-list')[0].scrollHeight;
        }
    });

    $('.select2-results').each(function () {
        ps = new PerfectScrollbar($(this)[0], {
            wheelPropagation: false
        });
    });

    let dropdown = $('.dropdown'),
        hide = true;
    dropdown.each((index, item) => {
        let btn = $(item).find('.dropdown__btn');
        btn.on('click', function (e) {
            $(this).parent().toggleClass('is-active');
            e.preventDefault()
        });

    });

    $(document).on('click', function (e) {
        if (!$(e.target).is('.dropdown') && !$(e.target).closest('.dropdown').length > 0) {
            $('.dropdown').removeClass('is-active')
        }
    });

    let chatMessagesToggler = $('.chat__messages-toggler'),
        chatSidebar = $('.chat__sidebar'),
        chatMessagesList = $('.chat__messages-list');

    chatMessagesToggler.on('click', function () {
        chatMessagesToggler.toggleClass('is-active');
        chatSidebar.toggleClass('is-closed');
        setTimeout(function () {
            ps.update()
        }, 300)
    });
    if (chatMessagesList.length > 0) {
        setTimeout(function () {
            chatMessagesList[0].scrollTop = chatMessagesList[0].scrollHeight;
        }, 100)
    }
}

function FindWord() {
    let word = $('.chat__list-search input').val();
    if ($(window).width() < '961') {
        baseUrl = window.location.href.split("?")[0];
        window.history.pushState('name', '', baseUrl);
    }
    $.ajax({
        type: "POST",
        data: { 'word': word },
        success: function (html) {
            $('#chat_window').html(html);
            ReloadScript();
            $('.chat__list-search input').focus().val(word);
        }
    });
}

$(document).ready(function () {

    // отправка сообщения
    $('body').on('click', '.btn.btn-green.btn-send', function () {
        var text = $('form.chat__input textarea').val(),
			id = $('.chat__list-item.is-active').attr('id'),
            parnter_id = $('.chat__list-item.is-active').data('user');
        id = parseInt(id.replace(/[^\d]/g, ''));
        if (text != '' || $('[type="file"]').val() != '') {
            if ($(this).attr('onclick') == 'return false;') return false;
            $(this).css({
                'cursor': 'auto',
                'background': 'grey',
            }).attr('onclick', 'return false;');

            var $that = $("form.chat__input"),
                formData = new FormData($that.get(0));
            $.ajax({
                type: 'POST',
                error: function (req, etext, error) {
                    console.error('Упс! Ошибочка: ' + etext + ' | ' + error);
                },
                contentType: false,
                processData: false,
                data: formData,
                success: function (html) {
                    if (html) {
                        $.ajax({
                            type: "POST",
                            data: { 'id': id, 'parnter_id': parnter_id },
                            success: function (html) {
                                $('#chat_window').html(html);
                                ReloadScript();
                            }
                        });
                        console.log(parnter_id, id);
                        BX.ajax({
                            url: '/local/components/taskme/chat/templates/.default/ajax.php',
                            method: 'POST',
                            data: { 'SEND': 'Y', 'sessid': BX.bitrix_sessid(), 'ID_TO': parnter_id, 'ID_FROM': id }
                        });
                    }
                }
            });
        }
    });
    // выбор чата
    $('body').on('click', '.chat__list-container .chat__list-item', function () {
        var id = $(this).attr('id'),
            parnter_id = $(this).data('user'),
            word = $('.chat__list-search input').val(),
            link = '/profile/chat/?id=';
        id = parseInt(id.replace(/[^\d]/g, ''));
        if (word != '') word = '&word=' + word;
        window.location.href = link + id + '_' + parnter_id + word;
        /*
        history.pushState({}, '', '?id=' + id + '_' + parnter_id);
        $.ajax({
            type: "POST",
            data: { 'id': id, 'parnter_id': parnter_id },
            success: function (html) {
                $('#chat_window').html(html);
                ReloadScript();
            }
        });
        */
    });
    // поиск

    var timer;
    var doneTypingInterval = 1000;
    var $input = $('.chat__list-search input');

    $('body').on('keyup input', '.chat__list-search input', function (event) {
        clearTimeout(timer);
        timer = setTimeout(FindWord, doneTypingInterval);
    });

    $('body').on('keydown input', '.chat__list-search input', function (event) {
        clearTimeout(timer);
    });

    // добавление файла
    $('body').on('change', '[type="file"]', function () {
        var file = $('[type="file"]')[0].files[0];
        if (file) {
            var size = this.files[0].size,
                name = this.files[0].name,
                ext = $(this).attr('ext'),
                max_size = $(this).attr('max');
            if (parseInt(max_size) < size) {
                // файл больше 5 мегабайт
                $('[type="file"]').val(null);
                return false;
            }
            var fileExtension = ext.split(',');
            if ($.inArray(name.split('.').pop().toLowerCase(), fileExtension) == -1) {
                // у файла неверный тип
                $('[type="file"]').val(null);
                return false;
            }
            $('#hidden_file').show();
            $('[data-dz-name]').text(file.name);
            $('[data-dz-size]').html('<strong>' + ((parseInt(file.size) * 0.000001).toFixed(1)) + '</strong> MB');
        }
    });
    // удаление файла
    $('body').on('click', '.svg-image-remove-red.svg-image-remove-red-dims', function () {
        $('[type="file"]').val(null);
        $('#hidden_file').hide();
    });

    $('body').on('click', '#backlinkRoot', function () {
        $('.chat__messages.is-active').removeClass('is-active');
        baseUrl = window.location.href.split("?")[0];
        window.history.pushState('name', '', baseUrl);
    });

});