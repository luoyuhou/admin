define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ordermanagement/order_detail/index' + location.search,
                    add_url: 'ordermanagement/order_detail/add',
                    edit_url: 'ordermanagement/order_detail/edit',
                    del_url: 'ordermanagement/order_detail/del',
                    multi_url: 'ordermanagement/order_detail/multi',
                    import_url: 'ordermanagement/order_detail/import',
                    table: 'order_detail',
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
                        {field: 'o_id', title: __('O_id')},
                        {field: 'event', title: __('Event'), operate: 'LIKE'},
                        {field: 'coupon', title: __('Coupon'), operate: 'LIKE'},
                        {field: 'additional', title: __('Additional'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});