define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'backend/upload'], function ($, undefined, Backend, Table, Form, Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            var course_id = Config['course_id'];
            Table.api.init({
                extend: {
                    index_url: 'course/courselist/index/ids/' + course_id + '/' + location.search,
                    add_url: 'course/courselist/add',
                    edit_url: 'course/courselist/edit',
                    del_url: 'course/courselist/del',
                    multi_url: 'course/courselist/multi',
                    import_url: 'course/courselist/import',
                    table: 'course_list',
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
                        // {field: 'pid', title: __('Pid')},
                        {field: 'course_id', title: __('Course_id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'path', title: __('Path'), formatter: Controller.api.formatter.thumb, operate: false},
                        {field: 'time', title: __('Time')},
                        {field: 'sort', title: __('Sort')},
                        {field: 'preview', title: __('Preview'), formatter: function (i, v) { return v['a.preview'] ? '<span style="color: green;">是</span>': '<span style="color: red;">否</span>'; }},
                        {field: 'url', title: __('Url'), operate: 'LIKE', formatter: Controller.api.formatter.url},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            var api_url = Config['api_url'];
            Controller.api.bindevent();
            $('#upload-path').on('click', function () {
                Upload(this, $('#c-path'), api_url);
            });
            $('#upload-url').on('click', function () {
                Upload(this, $('#c-url'), api_url);
            });
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