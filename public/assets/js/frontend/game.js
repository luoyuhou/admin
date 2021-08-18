define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        index: function () {
            let modelMap = Config._gameModel;
            let gameModelList = $('#gameModel').children();
            let gameNameList = $('#gameName').children();
            for (let i = 0; i < gameModelList.length; i++) {
                $(gameModelList[i]).on('click', function () {
                    let _ele = $(this);
                    $('#form-type').val(_ele.data('id'));
                    _ele.addClass('active').siblings().removeClass('active');
                })
            }
            for (let j = 0; j < gameNameList.length; j++) {
                $(gameNameList[j]).on('click', function () {
                    let _ele = $(this);
                    $('#form-name').val(_ele.data('id'));
                    _ele.addClass('active').siblings().removeClass('active');
                })
            }
            $('#fm-submit').on('click', function () {
                $.ajax({
                    type: "GET",
                    url: "game/getGameInfoList",
                    data: $('#fm').serialize(),
                    success: (data) => {
                        console.log('success data', data);
                        render(data);
                    },
                    error: (e) => {
                        console.log('e', e.message);
                        render([]);
                    }
                });
            });

            let formatField = ['createtime'];

            function loading() {
                $('#game-list').empty().append('<div class="game-loading" />');
            }

            function render(data) {
                let body = $('#game-list');
                body.empty();
                if (!data.length) {
                    body.append('<div class="order-no-list" />');
                    return;
                }
                var str = '';
                var tid = '';
                data.forEach((row) => {
                    str += '<tr>';
                    for (var k in row) {
                        if (k === "id") {
                            tid = row[k];
                        }
                        str += `<td>${format(k, row[k])}</td>`;
                    }
                    str += '<td><button data-id="' + tid + '"><i class="fa fa-angle-double-right" /></button></td></tr>';
                });
                body.append(str);
            }


            function format(key, val) {
                if (key === 'game_type') {
                    var t = modelMap.find((v) => {
                        if (v.id === val) {
                            return true;
                        }
                    });
                    return t && t.title || '未知模式';
                }

                if (!formatField.includes(key)) {
                    return val || '-';
                }

                var datetime = new Date();
                datetime.setTime(val * 1000);
                var year = datetime.getFullYear();
                var month = datetime.getMonth() + 1 < 10 ? "0" + (datetime.getMonth() + 1) : datetime.getMonth() + 1;
                var date = datetime.getDate() < 10 ? "0" + datetime.getDate() : datetime.getDate();
                var hour = datetime.getHours()< 10 ? "0" + datetime.getHours() : datetime.getHours();
                var minute = datetime.getMinutes()< 10 ? "0" + datetime.getMinutes() : datetime.getMinutes();
                var second = datetime.getSeconds()< 10 ? "0" + datetime.getSeconds() : datetime.getSeconds();
                return year + "-" + month + "-" + date + " " + hour + ":" + minute + ":" + second;
            }
        }
    };
    return Controller;
});
