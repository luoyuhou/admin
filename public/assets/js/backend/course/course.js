define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/upload'], function ($, undefined, Backend, Table, Form, Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'course/course/index' + location.search,
                    add_url: 'course/course/add',
                    edit_url: 'course/course/edit',
                    del_url: 'course/course/del',
                    multi_url: 'course/course/multi',
                    import_url: 'course/course/import',
                    table: 'course',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'd.username', title: __('Admin_id'), formatter: Table.api.formatter.search},
                        {field: 'a.title', title: __('Title'), operate: 'LIKE'},
                        {field: 'a.path', title: __('Path'), formatter: Controller.api.formatter.thumb, operate: false},
                        {field: 'a.status', title: __('Status'), formatter: Table.api.formatter.status},
                        {field: 'a.public', title: __('Public'), formatter: function (i, v) { return v['a.public'] ? '<span style="color: green;">免费</span>': '<span style="color: red;">收费</span>'; }},
                        {field: 'a.type', title: __('Type'), formatter: function (i, v) {
                                let config = Config.type;
                                let arr = v['a.type'].split(',')
                                let type = [];
                                for (let j = 0; j < arr.length; j++) {
                                    for (let i = 0; i < config.length; i++) {
                                        let item = config[i];
                                        if (item.id == arr[j] && !type.includes(item.name)) {
                                            type.push(item.name)
                                        }
                                    }
                                }
                                return type.join(',')
                            }},
                        {field: 'a.price', title: __('Price'), formatter: function (i, v) { return !v['a.price'] ? v['a.price'] + ' 元': (v['a.price'] / 100).toFixed(2) + ' 元'}},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [
                            {
                                name: 'detail',
                                title:__('Courselist'),
                                text: '',
                                icon: 'fa fa-list',
                                classname: 'btn btn-xs btn-info btn-dialog',
                                // classname: 'btn btn-xs btn-info',
                                url: 'course/courselist/index'
                            }
                        ], events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            var btn = $('#faupload');
            btn.mouseover(function () {
                $(this).prev().css('display', 'block');
            });
            btn.mouseout(function () {
                $(this).prev().css('display', 'none');
            });
            btn.click(function () {
                Upload(this, $('#c-url'), Config['api_url'], $('#preview'));
            })
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                thumb: function (value, row, index) {
                    if (row.path) {
                        return '<a href="' + Config['api_url'] + row.path + '" target="_blank"><img src="'+ Config['api_url'] + row.path + '" alt="" style="max-height:90px;max-width:120px"></a>';
                    } else {
                        return '<img src="' + Fast.api.fixurl("ajax/icon") + '" alt="" style="max-height:90px;max-width:120px">';
                    }
                },
                url: function (value, row, index) {
                    console.log('url', Config['api_url']);
                    return '<a href="' + Config['api_url'] + row.url + '" target="_blank" class="label bg-green">' + row.url + '</a>';
                },
                filename: function (value, row, index) {
                    return '<div style="width:180px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + Table.api.formatter.search.call(this, value, row, index) + '</div>';
                },
            }
        }
    };
    return Controller;
});