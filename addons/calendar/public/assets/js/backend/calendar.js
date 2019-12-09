define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'jquery-ui.min', 'fullcalendar', 'fullcalendar-lang'], function ($, undefined, Backend, Table, Form, Template, Fullcalendar) {

    var Controller = {
        index: function () {
            var $event = $.event,
                    $special = $event.special;
            if (typeof $special.draginit !== 'undefined') {
                $special.draginit.teardown();
                $special.draginit = $special.dragstart = $special.dragend = $special.drag = undefined;
            }
            var events = {
                url: "calendar/index",
                data: function () {
                    return {
                        type: $(".fc-my-button.fc-state-active").size() > 0 ? 'my' : 'all',
                        admin_id: $("#c-admin_id").size() > 0 ? $("#c-admin_id").val() : 0
                    };
                }
            };
            function rgb2hex(rgb) {
                if (/^#[0-9A-F]{6}$/i.test(rgb))
                    return rgb;

                rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
                function hex(x) {
                    return ("0" + parseInt(x).toString(16)).slice(-2);
                }
                return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
            }

            function append_calendar(data) {
                $('#calendar').fullCalendar('renderEvent', data);
            }

            function ini_events(ele) {
                ele.each(function () {
                    var eventObject = $(this).data();
                    $(this).data('eventObject', eventObject);
                    $(this).draggable({
                        zIndex: 1070,
                        revert: true,
                        revertDuration: 0
                    });

                });
            }

            function toggle_button() {
                $(".fc-all-button,.fc-my-button").removeClass("fc-state-active");
                $(this).addClass("fc-state-active");
                $('.selectpage').selectPageClear();
                $('#calendar').fullCalendar('refetchEvents');
            }

            ini_events($('#external-events div.external-event'));

            $('#calendar').fullCalendar({
                customButtons: {
                    all: {
                        text: __('All'),
                        click: toggle_button
                    },
                    my: {
                        text: __('My'),
                        click: toggle_button
                    },
                },
                header: {
                    left: 'prev,next today all,my',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                dayClick: function (date, jsEvent, view) {
                    //$(this).toggleClass('selected');
                },
                eventClick: function (calEvent, jsEvent, view) {
                    var that = this;
                    var status = $(this).hasClass("fc-completed") ? "normal" : "completed";
                    Fast.api.ajax({
                        url: "calendar/multi/ids/" + calEvent.id,
                        data: {params: "status=" + status}
                    }, function () {
                        $(that).removeClass("fc-completed fc-normal");
                        if (status == "completed") {
                            $(that).addClass("fc-completed");
                        }
                        return false;
                    });
                },
                events: events,
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                droppable: true,
                drop: function (date, allDay) {
                    var that = this;
                    var id = $(this).data('id');
                    var title = $(this).data('title');
                    Fast.api.ajax({
                        url: "calendar/add/ids/" + id,
                        data: {'row[starttime]': date.format(), 'row[endtime]': date.format()}
                    }, function (data, ret) {
                        append_calendar(data);
                        if ($('#drop-remove').is(':checked')) {
                            Fast.api.ajax({
                                url: "calendar/delevent/ids/" + id
                            }, function () {
                                $(that).remove();
                                return false;
                            });
                        }
                        return false;
                    }, function () {

                    });

                },
                eventDrop: function (event, delta, revertFunc, jsEvent) {
                    Fast.api.ajax({
                        url: "calendar/edit/ids/" + event.id,
                        data: {'row[starttime]': event.start.format(), 'row[endtime]': event.end ? event.end.format() : event.start.format()}
                    }, function (data) {
                        $('#calendar').fullCalendar('refetchEvents');
                        return false;
                    }, function () {
                        revertFunc();
                        return false;
                    });
                },
                eventDragStart: function (event, jsEvent) {
                    $("#calendarTrash").show();
                    $(".fc-toolbar .fc-button-group").hide();
                },
                eventDragStop: function (event, jsEvent) {
                    $(".fc-toolbar .fc-button-group").show();
                    var trashEl = jQuery('#calendarTrash');
                    var ofs = trashEl.offset();

                    var x1 = ofs.left;
                    var x2 = ofs.left + trashEl.outerWidth(true);
                    var y1 = ofs.top;
                    var y2 = ofs.top + trashEl.outerHeight(true);
                    if (jsEvent.pageX >= x1 && jsEvent.pageX <= x2 && jsEvent.pageY >= y1 && jsEvent.pageY <= y2) {
                        Fast.api.ajax({url: "calendar/del/ids/" + event.id}, function () {
                            $('#calendar').fullCalendar('removeEvents', event.id);
                            return false;
                        });
                    }
                    $("#calendarTrash").hide();
                },
                eventResize: function (event, delta, revertFunc) {
                    Fast.api.ajax({
                        url: "calendar/edit/ids/" + event.id,
                        data: {'row[starttime]': event.start.format(), 'row[endtime]': event.end ? event.end.format() : event.start.format()}
                    }, function (data) {
                        $('#calendar').fullCalendar('refetchEvents');
                        return false;
                    }, function () {
                        revertFunc();
                        return false;
                    });

                },
                eventAfterAllRender: function (view) {
                    if ($(".fc-all-button.fc-state-active,.fc-my-button.fc-state-active").size() == 0) {
                        $(".fc-toolbar").append('<div id="calendarTrash" class="calendar-trash"><i class="fa fa-trash-o"></i><b>' + __('Drag here to delete') + '</b></div>');
                        $(".fc-all-button").addClass("fc-state-active");
                        $(".fc-toolbar .fc-left").append('<form class="form-inline"><input type="text" id="c-admin_id" name="admin_id" placeholder="' + __('Please select a user') + '" class="form-control input-sm selectpage" /></form>');
                        $(".fc-toolbar .fc-left .selectpage").data("source", Config.admins);
                        Form.events.selectpage($(".fc-toolbar .fc-left form"));
                    }
                    $("a.fc-event[href]").attr("target", "_blank");
                }
            });

            var currColor = "#3c8dbc";
            $(document).on("click", "#color-chooser > li > a", function (e) {
                e.preventDefault();
                currColor = $(this).css("color");
                $("input[name='row[background]']").val(rgb2hex(currColor));
            });
            $(document).on("click", "input[name=type]", function (e) {
                var value = $(this).val();
                $("#daterange").toggle(value === 'calendar');
                $("#add-form").attr("action", value === 'calendar' ? "calendar/add" : "calendar/addevent");
            });
            $(document).on("change", "input[name='admin_id']", function () {
                $('#calendar').fullCalendar('refetchEvents');
            });
            $("#color-chooser li a:first").trigger("click");
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                if ($("input[name=type]:checked").val() == 'event') {
                    var event = $("<div />");
                    event.css({"background-color": data.background, "border-color": data.background, "color": "#fff"}).addClass("external-event");
                    event.data("id", data.id);
                    event.data("title", data.title);
                    event.data("background", data.background);
                    event.html(data.title);
                    $('#external-events').prepend(event);
                    ini_events(event);
                } else {
                    append_calendar(data);
                }
                $(this).trigger("reset");
                $("input[name=type]:checked").trigger("click");
            });
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