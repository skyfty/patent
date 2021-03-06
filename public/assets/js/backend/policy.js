define(['jquery', 'backend', 'table', 'form','template','angular','cosmetic'], function ($, Backend, Table, Form, Template,angular, Cosmetic) {
    var Controller = {
        //for index
        lands:{
            index:function($scope, $compile,$timeout, data) {
                $scope.searchFieldsParams = function(param) {
                    param.custom = {};
                    var branchSelect = $('[name="branch_select"]');
                    if (branchSelect.data("selectpicker")) {
                        var branchIds = branchSelect.selectpicker('val');
                        if (branchIds && branchIds.length > 0) {
                            param.custom['branch_model_id'] = ["in", branchIds];
                        }
                    }
                    return param;
                };
                var options = {
                    extend: {
                        index_url: 'policy/index',
                        add_url: 'policy/add',
                        del_url: 'policy/del',
                        multi_url: 'policy/multi',
                        summation_url: 'policy/summation',
                        table: 'policy',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __(' %s', row.name);
                            },
                            classname: 'btn btn-xs  btn-success btn-magic btn-addtabs btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'policy/view'
                        }
                    ]
                };
                Table.api.init(options);
                Form.api.bindevent($("div[ng-controller='index']"));
            }
        },
        viewscape:function($scope, $compile,$parse, $timeout){
            $scope.refreshRow = function(){
                $.ajax({url: "policy/index",dataType: 'json',
                    data:{
                        custom: {"policy.id":$scope.row.id}
                    },
                    success: function (data) {
                        if (data && data.rows && data.rows.length == 1) {
                            $scope.$apply(function(){
                                $parse("row").assign($scope, data.rows[0]);
                            });
                        }
                    }
                });
            };
        },
        scenery: {
            ordinal:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {policy_model_id:$scope.row.id};
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'ordinal/index',
                        add_url: 'ordinal/add',
                        del_url: 'ordinal/del',
                        summation_url: 'ordinal/summation',
                        table: 'ordinal',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'ordinal/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
            project:function($scope, $compile,$timeout, data){
                $scope.searchFieldsParams = function(param) {
                    param.custom = {policy_model_id:$scope.row.id};
                    return param;
                };

                Table.api.init({
                    extend: {
                        index_url: 'project/index',
                        add_url: 'project/add',
                        del_url: 'project/del',
                        summation_url: 'project/summation',
                        table: 'project',
                    },
                    buttons : [
                        {
                            name: 'view',
                            title: function(row, j){
                                return __('%s', row.idcode);
                            },
                            classname: 'btn btn-xs btn-success btn-magic btn-dialog btn-view',
                            icon: 'fa fa-folder-o',
                            url: 'project/view'
                        }
                    ]
                });
                $scope.fields = data.fields;
                angular.element("#tab-" +$scope.scenery.name).html($compile(data.content)($scope));
                $scope.$broadcast("shownTable");
            },
        },

        bindevent:function($scope){
            $('[name="row[species_model_id]"]').data("e-params",function(){
                var param = {};
                param.custom = {
                    "model": "policy",
                };
                return param;
            });
            Form.api.bindevent($("form[role=form]"), $scope.submit);

            require(['bootstrap-select', 'bootstrap-select-lang'], function () {
                $('[name="row[nationwide]"]').change(function(){
                    var val = $(this).val();
                    if (val == "yes") {
                        $('[data-field-name="location"]').hide().trigger("rate");
                    } else {
                        $('[data-field-name="location"]').show().trigger("rate");

                    }
                }).trigger("change");
            });
            if (Config.staff) $('[data-field-name="branch"]').hide().trigger("rate");
        },

        chart:function() {
            AngularApp.controller("chart", function($scope,$sce, $compile,$timeout) {
                $scope.stat = {};
                $scope.refresh = function(){
                    $.ajax({url: "policy/statistic",dataType: 'json',cache: false,
                        success: function (ret) {
                            $scope.$apply(function(){
                                $scope.stat = ret.data;
                            });
                        }
                    });
                };
                $scope.$on("refurbish", $scope.refresh);$scope.refresh(); $(".btn-refresh").on("click", $scope.refresh);
            });
        },
        api: {
        }
    };
    Controller.api = $.extend(Cosmetic.api, Controller.api);
    return $.extend(Cosmetic, Controller);
});