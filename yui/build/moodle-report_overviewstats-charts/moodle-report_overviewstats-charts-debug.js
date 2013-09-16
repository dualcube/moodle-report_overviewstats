YUI.add('moodle-report_overviewstats-charts', function (Y, NAME) {

M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.countries = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var chart = new Y.Chart({
            type: "bar",
            categoryKey: "country",
            verticalGridlines: true,
            dataProvider: data
        });

        Y.one("#chart_countries").setStyle("backgroundImage", "none");
        chart.render("#chart_countries");
    }
};
M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.enrolments = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var lastmonth = new Y.Chart({
            type: "combo",
            dataProvider: data.lastmonth,
            categoryKey: "date",
            horizontalGridlines: true,
            verticalGridlines: true,
            styles: {
                axes: {
                    date: {
                        label: {
                            rotation: -90
                        }
                    }
                }
            }

        });

        Y.one("#chart_enrolments_lastmonth").setStyle("backgroundImage", "none");
        lastmonth.render("#chart_enrolments_lastmonth");

        var lastyear = new Y.Chart({
            type: "combo",
            dataProvider: data.lastyear,
            categoryKey: "date",
            horizontalGridlines: true,
            verticalGridlines: true,
            styles: {
                axes: {
                    date: {
                        label: {
                            rotation: -90
                        }
                    }
                }
            }

        });

        Y.one("#chart_enrolments_lastyear").setStyle("backgroundImage", "none");
        lastyear.render("#chart_enrolments_lastyear");
    }
};
M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.langs = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var chart = new Y.Chart({
            type: "bar",
            categoryKey: "language",
            verticalGridlines: true,
            dataProvider: data
        });

        Y.one("#chart_langs").setStyle("backgroundImage", "none");
        chart.render("#chart_langs");
    }
};
M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.logins = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var perday = new Y.Chart({
            type: "combo",
            dataProvider: data.perday,
            categoryKey: "date",
            horizontalGridlines: true,
            verticalGridlines: true,
            styles: {
                axes: {
                    date: {
                        label: {
                            rotation: -90
                        }
                    }
                }
            }

        });

        Y.one("#chart_logins_perday").setStyle("backgroundImage", "none");
        perday.render("#chart_logins_perday");

    }
};
M.report_overviewstats = M.report_overviewstats || {};
M.report_overviewstats.charts = M.report_overviewstats.charts || {};
M.report_overviewstats.charts.modules = {

    /**
     * @method init
     * @param data
     */
    init: function(data) {
        var instances = new Y.Chart({
            type: "pie",
            categoryKey: "module",
            seriesKeys: ["count"],
            dataProvider: data.instances,
            legend: {
                position: "right",
                width: 300,
                height: 300,
                styles: {
                    hAlign: "center",
                    hSpacing: 4
                }
            },
            seriesCollection:[
                {
                    categoryKey: "module",
                    valueKey: "count"
                }
            ]
        });

        Y.one("#chart_modules_instances").setStyle("backgroundImage", "none");
        instances.render("#chart_modules_instances");

        var usage = new Y.Chart({
            type: "column",
            categoryKey: "indicator",
            dataProvider: data.usage,
            horizontalGridlines: true,
            styles: {
                axes: {
                    indicator: {
                        label: {
                            rotation: -90
                        }
                    }
                }
            }
        });

        Y.one("#chart_modules_usage").setStyle("backgroundImage", "none");
        usage.render("#chart_modules_usage");
    }
};


}, '@VERSION@', {"requires": ["base", "node", "charts", "charts-legend"]});
