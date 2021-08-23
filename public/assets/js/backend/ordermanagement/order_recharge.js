define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ordermanagement/order_recharge/index' + location.search,
                    add_url: 'ordermanagement/order_recharge/add',
                    edit_url: 'ordermanagement/order_recharge/edit',
                    del_url: 'ordermanagement/order_recharge/del',
                    multi_url: 'ordermanagement/order_recharge/multi',
                    import_url: 'ordermanagement/order_recharge/import',
                    table: 'order_recharge',
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
                        {field: 'recharge_money', title: __('Recharge_money')},
                        {field: 'recharge_type', title: __('Recharge_type')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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